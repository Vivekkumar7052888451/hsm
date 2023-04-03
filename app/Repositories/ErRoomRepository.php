<?php
namespace App\Repositories;
use App;
use App\Models\Bed;
use App\Models\BedAssign;
use App\Models\BedType;
use App\Models\Category;
use App\Models\Doctor;
use App\Models\IpdPatientDepartment;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\PatientCase;
use App\Models\Setting;
use DB;
use Exception;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Models\EmergencyRoom;

class ErRoomRepository extends BaseRepository{

    protected $fieldSearchable = [
    	'patient_id',
        'er_number',
        'height',
        'weight',
        'bp',
        'symptoms',
        'notes',
        'admission_date',
        'case_id',
        'is_old_patient',
        'doctor_id',
        'bed_group_id',
        'bed_id',
	];


	public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

	public function model()
    {

        return EmergencyRoom::class;
    }


    public function getAssociatedData()
    {
        $data['patients'] = Patient::with('patientUser')->get()->where('patientUser.status', '=', 1)->pluck('patientUser.full_name',
            'id')->sort();
        $data['doctors'] = Doctor::with('doctorUser')->get()->where('doctorUser.status', '=', 1)->pluck('doctorUser.full_name',
            'id')->sort();
        $data['bedTypes'] = BedType::pluck('title', 'id')->toArray();
        natcasesort($data['bedTypes']);
        $data['erNumber'] = $this->model->generateUniqueErNumber();

        return $data;
    }


    public function store($input)
    {
        try {
            $input['is_old_patient'] = isset($input['is_old_patient']) ? true : false;
            $er_department = EmergencyRoom::create($input);
            $bedAssignData = [
                'bed_id' => $input['bed_id'],
                'patient_id' => $input['patient_id'],
                'case_id' => $er_department->patientCase->case_id,
                'assign_date' => $input['admission_date'],
                'ipd_patient_department_id' => $er_department->id,
                'status' => true,
            ];
            /** @var BedAssignRepository $bedAssign */
            // $bedAssign = App::make(BedAssignRepository::class);
            // $bedAssign->store($bedAssignData);
        } catch (Exception $e) {
        	echo $e->getMessage();
        	echo $e->getFile();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return true;
    }

    public function createNotification($input)
    {
        try {
            $patient = Patient::with('patientUser')->where('id', $input['patient_id'])->first();
            addNotification([
                Notification::NOTIFICATION_TYPE['IPD Patient'],
                $patient->user_id,
                Notification::NOTIFICATION_FOR[Notification::PATIENT],
                $patient->patientUser->full_name.' your IPD record has been created.',
            ]);
        } catch (Exception $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }
    
    public function deleteErRoom($erRoom_id)
    {
        $this->model->find($erRoom_id)->delete();
        return true;
    }

     public function updateErDepartment($input, $ipdPatientDepartment)
    {
        try {
            DB::beginTransaction();
            $input['is_old_patient'] = isset($input['is_old_patient']) ? true : false;
            $bedId = $ipdPatientDepartment->bed_id;

            /** @var IpdPatientDepartment $ipdPatientDepartment */
            $ipdPatientDepartment = $this->update($input, $ipdPatientDepartment->id);

            $bedAssignData = [
                'bed_id' => $input['bed_id'],
                'patient_id' => $input['patient_id'],
                'case_id' => $ipdPatientDepartment->patientCase->case_id,
                'assign_date' => $input['admission_date'],
                'status' => true,
            ];

            /** @var BedAssign $bedAssign */
            $bedAssignUpdate = BedAssign::whereBedId($bedId)->first();

            if (! empty($bedAssignUpdate)) {
                /** @var BedAssignRepository $bedAssign */
                $bedAssign = App::make(BedAssignRepository::class);
                $bedAssign->update($bedAssignData, $bedAssignUpdate);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return true;
    }

}
