<?php

namespace App\Http\Controllers;

use App\Exports\PatientDiagnosisTestExport;
use App\Http\Requests\CreatePatientDiagnosisTestRequest;
use App\Http\Requests\UpdatePatientDiagnosisTestRequest;
use App\Models\PatientDiagnosisProperty;
use App\Models\PatientDiagnosisTest;
use App\Repositories\DoctorRepository;
use App\Repositories\PatientDiagnosisTestRepository;
use App\Repositories\PatientRepository;
use Barryvdh\DomPDF\Facade as PDF;
use Exception;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PatientDiagnosisTestController extends AppBaseController
{
    /**
     * @var PatientDiagnosisTestRepository
     */
    private $patientDiagnosisTestRepository;

    /**
     * @var PatientRepository
     */
    private $patientRepository;

    /**
     * @var DoctorRepository
     */
    private $doctorRepository;

    public function __construct(
        PatientDiagnosisTestRepository $patientDiagnosisTestRepository,
        PatientRepository $patientRepository,
        DoctorRepository $doctorRepository
    ) {
        $this->patientDiagnosisTestRepository = $patientDiagnosisTestRepository;
        $this->patientRepository = $patientRepository;
        $this->doctorRepository = $doctorRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return Application|Factory|View
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        return view('patient_diagnosis_test.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $patients = $this->patientRepository->getPatients();
        $doctors = $this->doctorRepository->getDoctors();
        $reportNumber = $this->patientDiagnosisTestRepository->getUniqueReportNumber();
        $diagnosisCategory = $this->patientDiagnosisTestRepository->getDiagnosisCategory();

        return view('patient_diagnosis_test.create',
            compact('patients', 'doctors', 'reportNumber', 'diagnosisCategory'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreatePatientDiagnosisTestRequest  $request
     * @return JsonResponse
     */
    public function store(CreatePatientDiagnosisTestRequest $request)
    {
        $input = $request->all();
        $this->patientDiagnosisTestRepository->store($input);

        return $this->sendSuccess(__('messages.flash.patient_diagnosis_saved'));
    }

    /**
     * @param  PatientDiagnosisTest  $patientDiagnosisTest
     * @return Application|Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(PatientDiagnosisTest $patientDiagnosisTest)
    {
        if (! canAccessRecord(PatientDiagnosisTest::class, $patientDiagnosisTest->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $patientDiagnosisTests = $this->patientDiagnosisTestRepository->getPatientDiagnosisTestProperty($patientDiagnosisTest->id);

        return view('patient_diagnosis_test.show', compact('patientDiagnosisTest', 'patientDiagnosisTests'));
    }

    /**
     * @param  PatientDiagnosisTest  $patientDiagnosisTest
     * @return Application|Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(PatientDiagnosisTest $patientDiagnosisTest)
    {
        if (! canAccessRecord(PatientDiagnosisTest::class, $patientDiagnosisTest->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $patients = $this->patientRepository->getPatients();
        $doctors = $this->doctorRepository->getDoctors();
        $patientDiagnosisTests = $this->patientDiagnosisTestRepository->getPatientDiagnosisTestProperty($patientDiagnosisTest->id);
        $diagnosisCategory = $this->patientDiagnosisTestRepository->getDiagnosisCategory();

        return view('patient_diagnosis_test.edit',
            compact('patientDiagnosisTests', 'patientDiagnosisTest', 'patients', 'doctors', 'diagnosisCategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdatePatientDiagnosisTestRequest  $request
     * @param  PatientDiagnosisTest  $patientDiagnosisTest
     * @return JsonResponse
     */
    public function update(UpdatePatientDiagnosisTestRequest $request, PatientDiagnosisTest $patientDiagnosisTest)
    {
        $this->patientDiagnosisTestRepository->updatePatientDiagnosis($request->all(), $patientDiagnosisTest);

        return $this->sendSuccess(__('messages.flash.patient_diagnosis_updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  PatientDiagnosisTest  $patientDiagnosisTest
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(PatientDiagnosisTest $patientDiagnosisTest)
    {
        if (! canAccessRecord(PatientDiagnosisTest::class, $patientDiagnosisTest->id)) {
            return $this->sendError(__('messages.flash.diagnosis_test_not_found'));
        }

        PatientDiagnosisProperty::wherePatientDiagnosisId($patientDiagnosisTest->id)->delete();
        $this->patientDiagnosisTestRepository->delete($patientDiagnosisTest->id);

        return $this->sendSuccess(__('messages.flash.patient_diagnosis_deleted'));
    }

    /**
     * @param  PatientDiagnosisTest  $patientDiagnosisTest
     */
    public function convertToPdf(PatientDiagnosisTest $patientDiagnosisTest)
    {
        if (! canAccessRecord(PatientDiagnosisTest::class, $patientDiagnosisTest->id)) {
            return Redirect::back();
        }

        $data = $this->patientDiagnosisTestRepository->getSettingList();
        $data['patientDiagnosisTest'] = $patientDiagnosisTest;
        $data['patientDiagnosisTests'] = $this->patientDiagnosisTestRepository->getPatientDiagnosisTestProperty($patientDiagnosisTest->id);

        $pdf = PDF::loadView('patient_diagnosis_test.diagnosis_test_pdf', $data);

        return $pdf->stream($patientDiagnosisTest->patient->user->full_name.'-'.$patientDiagnosisTest->report_number);
    }

    /**
     * @return BinaryFileResponse
     */
    public function patientDiagnosisTestExport()
    {
        $response = Excel::download(new PatientDiagnosisTestExport, 'patient-diagnosis-tests-'.time().'.xlsx');

        ob_end_clean();

        return $response;
    }
}
