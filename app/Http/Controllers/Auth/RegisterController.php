<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\NotifyMailSuperAdminForRegisterHospital;
use App\Models\Department;
use App\Models\DoctorDepartment;
use App\Models\MultiTenant;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserTenant;
use App\Rules\ValidRecaptcha;
use Carbon\Carbon;
use DB;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => ['required', 'string', 'max:12', 'unique:users'],
            'hospital_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'email:filter', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['required', 'max:11'],
            'g-recaptcha-response' => ['required', new ValidRecaptcha],
        ], [
            'password.min' => 'The password must be at least 6 characters.',
            'g-recaptcha-response.recaptcha' => 'Captcha verification failed',
            'g-recaptcha-response.required' => 'The Google reCaptcha field is required',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $input['status'] = User::ACTIVE;
        $data['first_name'] = $data['hospital_name'];
//        $data['email_verified_at'] = Carbon::now();

        $adminRole = Department::whereName('Admin')->first();

        try {
            DB::beginTransaction();
            $input['phone'] = preparePhoneNumber($data, 'phone');
            $data['department_id'] = $adminRole->id;
            $data['status'] = User::ACTIVE;
            $user = User::create($data);
            $user->assignRole($adminRole);

            $tenant = MultiTenant::create([
                'tenant_username' => $data['username'], 'hospital_name' => $data['hospital_name'],
            ]);

            $user->update([
                'tenant_id' => $tenant->id,
            ]);

            UserTenant::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'last_login_at' => Carbon::now(),
            ]);

            DoctorDepartment::create([
                'tenant_id' => $tenant->id,
                'title' => 'Doctor',
            ]);

            /*
            $subscription = [
                'user_id'    => $user->id,
                'start_date' => Carbon::now(),
                'end_date'   => Carbon::now()->addDays(6),
                'status'     => 1,
            ];
            Subscription::create($subscription);
            */

            // creating settings and assigning the modules to the created user.
            session(['tenant_id' => $tenant->id]);
            Artisan::call('db:seed', ['--class' => 'SettingsTableSeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'AddSocialSettingTableSeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'DefaultModuleSeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'FrontSettingHomeTableSeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'FrontSettingTableSeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'AddAppointmentFrontSettingTableSeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'AddHomePageBoxContentSeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'AddDoctorFrontSettingTableSeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'FrontServiceSeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'GoogleRecaptchaSettingSeeder', '--force' => true]);

            // assign the default plan to the user when they registers.
            $subscriptionPlan = SubscriptionPlan::where('is_default', 1)->first();
            $trialDays = $subscriptionPlan->trial_days;
            $subscription = [
                'user_id' => $user->id,
                'subscription_plan_id' => $subscriptionPlan->id,
                'plan_amount' => $subscriptionPlan->price,
                'plan_frequency' => $subscriptionPlan->frequency,
                'starts_at' => Carbon::now(),
                'ends_at' => Carbon::now()->addDays($trialDays),
                'trial_ends_at' => Carbon::now()->addDays($trialDays),
                'status' => Subscription::ACTIVE,
                'sms_limit' => $subscriptionPlan->sms_limit,
            ];
            Subscription::create($subscription);

            $superAdmin = User::whereDepartmentId(10)->first();
            if(!empty($superAdmin)){
                $superAdminEmail = $superAdmin->email;

                $mailData = [
                    'hospital_name' => $data['hospital_name'],
                    'hospital_email' => $user->email,
                    'hospital_phone' => $user->phone,
                ];

                Mail::to($superAdminEmail)
                    ->send(new NotifyMailSuperAdminForRegisterHospital('emails.hospital_register_mail',
                        'Notify Mail For New Hospital Registered',
                        $mailData));
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($exception->getMessage());
        }

        return $user;
    }
}
