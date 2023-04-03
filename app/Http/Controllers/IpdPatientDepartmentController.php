<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateIpdPatientDepartmentRequest;
use App\Http\Requests\UpdateIpdPatientDepartmentRequest;
use App\Models\IpdCharge;
use App\Models\IpdPatientDepartment;
use App\Models\IpdPayment;
use App\Repositories\IpdBillRepository;
use App\Repositories\IpdPatientDepartmentRepository;
use Exception;
use Flash;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class IpdPatientDepartmentController extends AppBaseController
{
    /** @var IpdPatientDepartmentRepository */
    private $ipdPatientDepartmentRepository;

    public function __construct(IpdPatientDepartmentRepository $ipdPatientDepartmentRepo)
    {
        $this->ipdPatientDepartmentRepository = $ipdPatientDepartmentRepo;
    }

    /**
     * Display a listing of the IpdPatientDepartment.
     *
     * @param  Request  $request
     * @return Factory|View
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        $statusArr = IpdPatientDepartment::STATUS_ARR;

        return view('ipd_patient_departments.index', compact('statusArr'));
    }

    /**
     * Show the form for creating a new IpdPatientDepartment.
     *
     * @return Factory|View
     */
    public function create()
    {
        $data = $this->ipdPatientDepartmentRepository->getAssociatedData();
        return view('ipd_patient_departments.create', compact('data'));
    }

    /**
     * Store a newly created IpdPatientDepartment in storage.
     *
     * @param  CreateIpdPatientDepartmentRequest  $request
     * @return RedirectResponse|Redirector
     */
    public function store(CreateIpdPatientDepartmentRequest $request)
    {
        $input = $request->all();
        $this->ipdPatientDepartmentRepository->store($input);
        $this->ipdPatientDepartmentRepository->createNotification($input);
        Flash::success(__('messages.flash.IPD_Patient_saved'));

        return redirect(route('ipd.patient.index'));
    }

    /**
     * @param  IpdPatientDepartment  $ipdPatientDepartment
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|RedirectResponse
     */
    public function show(IpdPatientDepartment $ipdPatientDepartment)
    {
        if (! canAccessRecord(IpdPatientDepartment::class, $ipdPatientDepartment->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $doctors = $this->ipdPatientDepartmentRepository->getDoctorsData();
        $doctorsList = $this->ipdPatientDepartmentRepository->getDoctorsList();
        $medicineCategories = $this->ipdPatientDepartmentRepository->getMedicinesCategoriesData();
        $medicineCategoriesList = $this->ipdPatientDepartmentRepository->getMedicineCategoriesList();
        $ipdPatientDepartmentRepository = App::make(IpdBillRepository::class);
        $bill = $ipdPatientDepartmentRepository->getBillList($ipdPatientDepartment);
        $chargeTypes = IpdCharge::CHARGE_TYPES;
        asort($chargeTypes);
        $paymentModes = IpdPayment::PAYMENT_MODES;

        return view('ipd_patient_departments.show',
            compact('ipdPatientDepartment', 'doctors', 'doctorsList', 'chargeTypes', 'medicineCategories',
                'medicineCategoriesList', 'paymentModes', 'bill'));
    }

    /**
     * @param  IpdPatientDepartment  $ipdPatientDepartment
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|RedirectResponse
     */
    public function edit(IpdPatientDepartment $ipdPatientDepartment)
    {
        if (! canAccessRecord(IpdPatientDepartment::class, $ipdPatientDepartment->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $data = $this->ipdPatientDepartmentRepository->getAssociatedData();

        return view('ipd_patient_departments.edit', compact('data', 'ipdPatientDepartment'));
    }

    /**
     * Update the specified Ipd Diagnosis in storage.
     *
     * @param  IpdPatientDepartment  $ipdPatientDepartment
     * @param  UpdateIpdPatientDepartmentRequest  $request
     * @return RedirectResponse|Redirector
     */
    public function update(IpdPatientDepartment $ipdPatientDepartment, UpdateIpdPatientDepartmentRequest $request)
    {
        $input = $request->all();
        $this->ipdPatientDepartmentRepository->updateIpdPatientDepartment($input, $ipdPatientDepartment);
        Flash::success(__('messages.flash.IPD_Patient_updated'));

        return redirect(route('ipd.patient.index'));
    }

    /**
     * Remove the specified IpdPatientDepartment from storage.
     *
     * @param  IpdPatientDepartment  $ipdPatientDepartment
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(IpdPatientDepartment $ipdPatientDepartment)
    {
        
        if (! canAccessRecord(IpdPatientDepartment::class, $ipdPatientDepartment->id)) {
            return $this->sendError(__('messages.flash.ipd_patient_not_found'));
        }

        $this->ipdPatientDepartmentRepository->deleteIpdPatientDepartment($ipdPatientDepartment);

        return $this->sendSuccess(__('messages.flash.IPD_Patient_deleted'));
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getPatientCasesList(Request $request)
    {
        $patientCases = $this->ipdPatientDepartmentRepository->getPatientCases($request->get('id'));
        return $this->sendResponse($patientCases, __('messages.flash.retrieve'));
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getPatientBedsList(Request $request)
    {
        $patientBeds = $this->ipdPatientDepartmentRepository->getPatientBeds($request->get('id'),
            $request->get('isEdit'), $request->get('bedId'), $request->get('ipdPatientBedTypeId'));

        return $this->sendResponse($patientBeds, __('messages.flash.retrieve'));
    }
}
