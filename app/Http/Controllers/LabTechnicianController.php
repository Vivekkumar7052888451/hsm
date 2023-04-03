<?php

namespace App\Http\Controllers;

use App\Exports\LabTechnicianExport;
use App\Http\Requests\CreateLabTechnicianRequest;
use App\Http\Requests\UpdateLabTechnicianRequest;
use App\Models\EmployeePayroll;
use App\Models\LabTechnician;
use App\Repositories\LabTechnicianRepository;
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

class LabTechnicianController extends AppBaseController
{
    /** @var LabTechnicianRepository */
    private $labTechnicianRepository;

    public function __construct(LabTechnicianRepository $labTechnicianRepo)
    {
        $this->labTechnicianRepository = $labTechnicianRepo;
    }

    /**
     * Display a listing of the LabTechnician.
     *
     * @param  Request  $request
     * @return Factory|View
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        $data['statusArr'] = LabTechnician::STATUS_ARR;

        return view('lab_technicians.index', $data);
    }

    /**
     * Show the form for creating a new LabTechnician.
     *
     * @return Factory|View
     */
    public function create()
    {
        $bloodGroup = getBloodGroups();

        return view('lab_technicians.create', compact('bloodGroup'));
    }

    /**
     * Store a newly created LabTechnician in storage.
     *
     * @param  CreateLabTechnicianRequest  $request
     * @return RedirectResponse|Redirector
     */
    public function store(CreateLabTechnicianRequest $request)
    {
        $input = $request->all();
        $input['status'] = isset($input['status']) ? 1 : 0;
        $labTechnician = $this->labTechnicianRepository->store($input);

        Flash::success(__('messages.flash.lab_technician_saved'));

        return redirect(route('lab-technicians.index'));
    }

    /**
     * @param  LabTechnician  $labTechnician
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|RedirectResponse
     */
    public function show(LabTechnician $labTechnician)
    {
        if (! canAccessRecord(LabTechnician::class, $labTechnician->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $payrolls = $labTechnician->payrolls;

        return view('lab_technicians.show', compact('labTechnician', 'payrolls'));
    }

    /**
     * @param  LabTechnician  $labTechnician
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|RedirectResponse
     */
    public function edit(LabTechnician $labTechnician)
    {
        if (! canAccessRecord(LabTechnician::class, $labTechnician->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $user = $labTechnician->user;
        $bloodGroup = getBloodGroups();

        return view('lab_technicians.edit', compact('labTechnician', 'user', 'bloodGroup'));
    }

    /**
     * Update the specified LabTechnician in storage.
     *
     * @param  LabTechnician  $labTechnician
     * @param  UpdateLabTechnicianRequest  $request
     * @return RedirectResponse|Redirector
     */
    public function update(LabTechnician $labTechnician, UpdateLabTechnicianRequest $request)
    {
        $labTechnician = $this->labTechnicianRepository->update($labTechnician, $request->all());

        Flash::success(__('messages.flash.lab_technician_updated'));

        return redirect(route('lab-technicians.index'));
    }

    /**
     * Remove the specified LabTechnician from storage.
     *
     * @param  LabTechnician  $labTechnician
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(LabTechnician $labTechnician)
    {
        if (! canAccessRecord(LabTechnician::class, $labTechnician->id)) {
            return $this->sendError(__('messages.flash.lab_technician_not_found'));
        }

        $empPayRollResult = canDeletePayroll(EmployeePayroll::class, 'owner_id', $labTechnician->id,
            $labTechnician->user->owner_type);
        if ($empPayRollResult) {
            return $this->sendError(__('messages.flash.lab_technician_cant_deleted'));
        }
        $labTechnician->user()->delete();
        $labTechnician->address()->delete();
        $labTechnician->delete();

        return $this->sendSuccess(__('messages.flash.lab_technician_deleted'));
    }

    /**
     * @param  int  $id
     * @return JsonResponse
     */
    public function activeDeactiveStatus($id)
    {
        $labTechnician = LabTechnician::findOrFail($id);
        $status = ! $labTechnician->user->status;
        $labTechnician->user()->update(['status' => $status]);

        return $this->sendSuccess(__('messages.common.status_updated_successfully'));
    }

    /**
     * @return BinaryFileResponse
     */
    public function labTechnicianExport()
    {
        $response = Excel::download(new LabTechnicianExport, 'lab-technicians-'.time().'.xlsx');

        ob_end_clean();

        return $response;
    }
}
