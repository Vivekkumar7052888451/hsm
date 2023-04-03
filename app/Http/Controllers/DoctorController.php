<?php

namespace App\Http\Controllers;

use App\Exports\DoctorExport;
use App\Http\Requests\CreateDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Appointment;
use App\Models\BirthReport;
use App\Models\DeathReport;
use App\Models\Doctor;
use App\Models\EmployeePayroll;
use App\Models\InvestigationReport;
use App\Models\IpdPatientDepartment;
use App\Models\OperationReport;
use App\Models\PatientAdmission;
use App\Models\PatientCase;
use App\Models\Prescription;
use App\Models\Schedule;
use App\Repositories\DoctorRepository;
use Exception;
use Flash;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DoctorController extends AppBaseController
{
    /** @var DoctorRepository */
    private $doctorRepository;

    public function __construct(DoctorRepository $doctorRepo)
    {
        $this->doctorRepository = $doctorRepo;
    }

    /**
     * Display a listing of the Doctor.
     *
     * @param  Request  $request
     * @return Factory|View
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        $data['statusArr'] = Doctor::STATUS_ARR;

        return view('doctors.index', $data);
    }

    /**
     * Show the form for creating a new Doctor.
     *
     * @return Factory|View
     */
    public function create()
    {
        $doctorsDepartments = getDoctorsDepartments();
        $bloodGroup = getBloodGroups();

        return view('doctors.create', compact('doctorsDepartments', 'bloodGroup'));
    }

    /**
     * Store a newly created Doctor in storage.
     *
     * @param  CreateDoctorRequest  $request
     * @return RedirectResponse|Redirector
     */
    public function store(CreateDoctorRequest $request)
    {
        $input = $request->all();
        $input['status'] = isset($input['status']) ? 1 : 0;
        $doctor = $this->doctorRepository->store($input);
        Flash::success(__('messages.flash.doctor_save'));

        return redirect(route('doctors.index'));
    }

    /**
     * @param  Doctor  $doctor
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|RedirectResponse|Redirector
     */
    public function show(Doctor $doctor)
    {
        if (! canAccessRecord(Doctor::class, $doctor->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        if (getLoggedInUser()->hasRole('Doctor')) {
            if (! (getLoggedInUser()->owner_id == $doctor->id)) {
                Flash::error(__('messages.flash.not_allow_access_record'));

                return Redirect::back();
            }
        }

        $data = $this->doctorRepository->getDoctorAssociatedData($doctor->id);

        return view('doctors.show')->with($data);
    }

    /**
     * @param  Doctor  $doctor
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|RedirectResponse
     */
    public function edit(Doctor $doctor)
    {
        if (! canAccessRecord(Doctor::class, $doctor->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $user = $doctor->doctorUser;
        $doctorsDepartments = getDoctorsDepartments();
        $bloodGroup = getBloodGroups();

        return view('doctors.edit', compact('doctor', 'user', 'doctorsDepartments', 'bloodGroup'));
    }

    /**
     * Update the specified Doctor in storage.
     *
     * @param  Doctor  $doctor
     * @param  UpdateDoctorRequest  $request
     * @return RedirectResponse|Redirector
     */
    public function update(Doctor $doctor, UpdateDoctorRequest $request)
    {
        if (empty($doctor)) {
            Flash::error(__('messages.flash.doctor_not_found'));

            return redirect(route('doctors.index'));
        }
        $input = $request->all();
        $input['status'] = isset($input['status']) ? 1 : 0;
        $doctor = $this->doctorRepository->update($doctor, $input);
        Flash::success(__('messages.flash.doctor_update'));

        return redirect(route('doctors.index'));
    }

    /**
     * Remove the specified Doctor from storage.
     *
     * @param  Doctor  $doctor
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(Doctor $doctor)
    {
        if (! canAccessRecord(Doctor::class, $doctor->id)) {
            return $this->sendError(__('messages.flash.doctor_not_found'));
        }

        $doctorModels = [
            PatientCase::class, PatientAdmission::class, Schedule::class, Appointment::class, BirthReport::class,
            DeathReport::class, InvestigationReport::class, OperationReport::class, Prescription::class,
            IpdPatientDepartment::class,
        ];
        $result = canDelete($doctorModels, 'doctor_id', $doctor->id);
        $empPayRollResult = canDeletePayroll(EmployeePayroll::class, 'owner_id', $doctor->id,
            $doctor->user->owner_type);
        if ($result || $empPayRollResult) {
            return $this->sendError(__('messages.flash.doctor_cant_deleted'));
        }
        $doctor->user()->delete();
        $doctor->address()->delete();
        $doctor->delete();

        return $this->sendSuccess(__('messages.flash.doctor_delete'));
    }

    /**
     * @param  int  $id
     * @return JsonResponse
     */
    public function activeDeactiveStatus($id)
    {
        $doctor = Doctor::findOrFail($id);
        $status = ! $doctor->doctorUser->status;
        $doctor->doctorUser()->update(['status' => $status]);

        return $this->sendSuccess(__('messages.common.status_updated_successfully'));
    }

    /**
     * @return BinaryFileResponse
     */
    public function doctorExport()
    {
        $response = Excel::download(new DoctorExport, 'doctors-'.time().'.xlsx');

        ob_end_clean();

        return $response;
    }
}
