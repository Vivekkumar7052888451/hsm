<?php

namespace App\Repositories;

use App;
use App\Mail\NotifyMailLiveConsultation;
use App\Models\Doctor;
use App\Models\IpdPatientDepartment;
use App\Models\LiveConsultation;
use App\Models\Notification;
use App\Models\OpdPatientDepartment;
use App\Models\Patient;
use App\Models\User;
use App\Models\UserTenant;
use App\Models\UserZoomCredential;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class LiveConsultationRepository
 */
class LiveConsultationRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'doctor_id',
        'patient_id',
        'consultation_title',
        'consultation_date',
        'consultation_duration_minutes',
        'type',
        'type_number',
        'description',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return LiveConsultation::class;
    }

    /**
     * @param $input
     * @return Collection
     */
    public function getTypeNumber($input)
    {
        if ($input['consultation_type'] == LiveConsultation::OPD) {
            return OpdPatientDepartment::where('patient_id', $input['patient_id'])->pluck('opd_number', 'id');
        }

        return IpdPatientDepartment::where('patient_id', $input['patient_id'])->pluck('ipd_number', 'id');
    }

    /**
     * @param  array  $input
     * @return bool
     *
     * @throws BindingResolutionException
     */
    public function store($input)
    {
        /** @var ZoomRepository $zoomRepo */
        $zoomRepo = App::makeWith(ZoomRepository::class, ['createdBy' => getLoggedInUserId()]);

        try {
            $input['created_by'] = getLoggedInUserId();
            $startTime = $input['consultation_date'];
            $input['consultation_date'] = Carbon::parse($startTime)->format('Y-m-d H:i:s');
            $zoom = $zoomRepo->create($input);
            $input['password'] = isset($zoom['data']['password']) ? $zoom['data']['password'] : \Str::random(6);
            $input['meeting_id'] = $zoom['data']['id'];
            $input['meta'] = $zoom['data'];

            $zoomModel = LiveConsultation::create($input);

            $userId = UserTenant::whereTenantId(getLoggedInUser()->tenant_id)->value('user_id');
            $hospitalDefaultAdmin = User::whereId($userId)->first();

            if(!empty($hospitalDefaultAdmin)){

                $hospitalDefaultAdminEmail = $hospitalDefaultAdmin->email;
                $doctor = Doctor::whereId($input['doctor_id'])->first();
                $patient = Patient::whereId($input['patient_id'])->first();

                $mailData = [
                    'consultation_title' => $input['consultation_title'],
                    'consultation_date' =>  $input['consultation_date'],
                    'consultation_duration_time' =>  $input['consultation_duration_minutes'],
                    'created_by' =>  getLoggedInUser()->full_name,
                    'created_for' => $doctor->user->full_name,
                    'patient_name' => $patient->user->full_name,
                    'doctor_name' => $doctor->user->full_name,
                    'patient_type' => $input['type'] == 0 ? 'OPD' : 'IPD',
                    'doctor_department' => $doctor->department->title,
                ];

                $mailData['zoom_redirect_url'] = $input['meta']['start_url'];

                Mail::to($doctor->user->email)
                    ->send(new NotifyMailLiveConsultation('emails.live_consultation_created_mail',
                        'New Live Consultation Created',
                        $mailData));

                $mailData['zoom_redirect_url'] = $input['meta']['join_url'];

                Mail::to($hospitalDefaultAdminEmail)
                    ->send(new NotifyMailLiveConsultation('emails.live_consultation_created_mail',
                        'New Live Consultation Created',
                        $mailData));

                Mail::to($patient->user->email)
                    ->send(new NotifyMailLiveConsultation('emails.live_consultation_created_mail',
                        'New Live Consultation Created',
                        $mailData));
            }

            return true;
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @param  array  $input
     * @param  LiveConsultation  $liveConsultation
     * @return bool
     */
    public function edit($input, $liveConsultation)
    {
        /** @var ZoomRepository $zoomRepo */
        $zoomRepo = App::make(ZoomRepository::class, ['createdBy' => $liveConsultation->created_by]);

        try {
            $zoomRepo->update($liveConsultation->meeting_id, $input);
            $zoom = $zoomRepo->get($liveConsultation->meeting_id, ['meeting_owner' => $liveConsultation->created_by]);
            $input['password'] = isset($zoom['data']['password']) ? $zoom['data']['password'] : \Str::random(6);
            $input['meta'] = $zoom['data'];
            $input['created_by'] = getLoggedInUserId();
            $input['created_by'] = $liveConsultation->created_by != getLoggedInUserId() ? $liveConsultation->created_by : getLoggedInUserId();
            $startTime = $input['consultation_date'];
            $input['consultation_date'] = Carbon::parse($startTime)->format('Y-m-d H:i:s');

            $zoomModel = $liveConsultation->update($input);

            return true;
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @param  array  $input
     * @return mixed
     */
    public function createUserZoom($input)
    {
        try {
            UserZoomCredential::updateOrCreate([
                'user_id' => getLoggedInUserId(),
            ], [
                'user_id' => getLoggedInUserId(),
                'zoom_api_key' => $input['zoom_api_key'],
                'zoom_api_secret' => $input['zoom_api_secret'],
            ]);

            return true;
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    /**
     * @param  array  $input
     */
    public function createNotification($input = [])
    {
        try {
            $patient = Patient::with('patientUser')->where('id', $input['patient_id'])->first();
            $doctor = Doctor::with('doctorUser')->where('id', $input['doctor_id'])->first();
            $userIds = [
                $doctor->user_id => Notification::NOTIFICATION_FOR[Notification::DOCTOR],
                $patient->user_id => Notification::NOTIFICATION_FOR[Notification::PATIENT],
            ];

            $adminUser = User::role('Admin')->first();
            $allUsers = $userIds + [$adminUser->id => Notification::NOTIFICATION_FOR[Notification::ADMIN]];
            $users = getAllNotificationUser($allUsers);

            foreach ($users as $key => $notification) {
                if ($notification == Notification::NOTIFICATION_FOR[Notification::PATIENT]) {
                    $title = $patient->patientUser->full_name.' your live consultation has been created by '.$doctor->doctorUser->full_name.'.';
                } else {
                    $title = $patient->patientUser->full_name.' live consultation has been booked.';
                }
                addNotification([
                    Notification::NOTIFICATION_TYPE['Live Consultation'],
                    $key,
                    $notification,
                    $title,
                ]);
            }
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }
}
