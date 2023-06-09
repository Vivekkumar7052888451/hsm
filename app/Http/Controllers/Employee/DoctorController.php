<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

class DoctorController extends Controller
{
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
        return view('employees.doctors.index');
    }

    /**
     * @param  int  $id
     * @return Factory|RedirectResponse|Redirector|View
     */
    public function show($id)
    {
        $doctor = Doctor::findOrFail($id);

        return view('employees.doctors.show')->with('doctor', $doctor);
    }
}
