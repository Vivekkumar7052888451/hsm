<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\OpdPatientDepartment;
use App\Repositories\OpdPatientDepartmentRepository;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VaccinatedController extends Controller
{
    /** @var OpdPatientDepartmentRepository */
    private $opdPatientDepartmentRepository;

    public function __construct(OpdPatientDepartmentRepository $opdPatientDepartmentRepo)
    {
        $this->opdPatientDepartmentRepository = $opdPatientDepartmentRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return Factory|View
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        return view('patient_vaccinated_list.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  OpdPatientDepartment  $opdPatientDepartment
     * @return Factory|View
     */
    public function show(OpdPatientDepartment $opdPatientDepartment)
    {
        return view('opd_patient_list.show', compact('opdPatientDepartment'));
    }
}
