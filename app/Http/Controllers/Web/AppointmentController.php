<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateWebAppointmentRequest;
use App\Mail\NotifyMailHospitalAdminForBookingAppointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Repositories\AppointmentRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Class AppointmentController
 */
class AppointmentController extends AppBaseController
{
    /** @var AppointmentRepository */
    private $appointmentRepository;

    public function __construct(AppointmentRepository $appointmentRepo)
    {
        $this->appointmentRepository = $appointmentRepo;
    }

    /**
     * Store a newly created appointment in storage.
     *
     * @param  CreateWebAppointmentRequest  $request
     * @return JsonResponse
     */
    public function store(CreateWebAppointmentRequest $request)
    {
        $input = $request->all();
        $input['opd_date'] = $input['opd_date'].$input['time'];
        $input['status'] = true;
        try {
            DB::beginTransaction();
            if ($input['patient_type'] == 2 && ! empty($input['patient_type'])) {
                $input['tenant_id'] = User::where('username', $input['hospital_username'])->first()->tenant_id;
                $this->appointmentRepository->create($input);

                $hospitalDefaultAdmin = User::where('username', $input['hospital_username'])->first();

                if(!empty($hospitalDefaultAdmin)){

                    $hospitalDefaultAdminEmail = $hospitalDefaultAdmin->email;
                    $doctor = Doctor::whereId($input['doctor_id'])->first();
                    $patient = Patient::whereId($input['patient_id'])->first();

                    $mailData = [
                        'booking_date' => Carbon::parse($input['opd_date'])->translatedFormat('g:i A') .' '.Carbon::parse($input['opd_date'])->translatedFormat('jS M, Y'),
                        'patient_name' =>  $patient->user->full_name,
                        'patient_email' =>  $patient->user->email,
                        'doctor_name' =>  $doctor->user->full_name,
                        'doctor_department' => $doctor->department->title,
                        'doctor_email' => $doctor->user->email,
                    ];

                    $mailData['patient_type'] = 'Old';

                    Mail::to($hospitalDefaultAdminEmail)
                        ->send(new NotifyMailHospitalAdminForBookingAppointment('emails.booking_appointment_mail',
                            'Notify Mail For Patient booked appointment',
                            $mailData));
                    Mail::to($doctor->user->email)
                        ->send(new NotifyMailHospitalAdminForBookingAppointment('emails.booking_appointment_mail',
                            'Notify Mail For Patient booked appointment',
                            $mailData));
                }

            }

            if ($input['patient_type'] == 1 && ! empty($input['patient_type'])) {
                $emailExists = User::whereEmail($input['email'])->exists();
                if ($emailExists) {
                    return $this->sendError('Email already exists, please select old patient.');
                }
                $this->appointmentRepository->createNewAppointment($input);
            }

            DB::commit();

            return $this->sendSuccess('Appointment saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->sendError($e->getMessage(), 404);
        }
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getDoctors(Request $request)
    {
        $id = $request->get('id');

        $doctors = $this->appointmentRepository->getDoctors($id);

        return $this->sendResponse($doctors, 'Retrieved successfully');
    }

    /**
     * @return JsonResponse
     */
    public function getDoctorList(Request $request)
    {
        $id = $request->get('id');
        $doctorArr = $this->appointmentRepository->getDoctorList($id);

        return $this->sendResponse($doctorArr, 'Retrieved successfully');
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getBookingSlot(Request $request)
    {
        $inputs = $request->all();
        $data = $this->appointmentRepository->getBookingSlot($inputs);

        return $this->sendResponse($data, 'Retrieved successfully');
    }

    /**
     * @param $email
     * @return JsonResponse
     */
    public function getPatientDetails($email)
    {
        /** @var Patient $patient */
        $patient = Patient::with('user')->get()->where('user.status', '=', 1)->where('user.email', $email)->first();
        $data = null;
        if ($patient != null) {
            $data = [
                $patient->id => $patient->user->full_name,
            ];
        }

        return $this->sendResponse($data, 'User Retrieved Successfully');
    }
}
