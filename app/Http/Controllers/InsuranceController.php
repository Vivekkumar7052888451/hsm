<?php

namespace App\Http\Controllers;

use App\Exports\InsuranceExport;
use App\Http\Requests\CreateInsuranceRequest;
use App\Http\Requests\UpdateInsuranceRequest;
use App\Models\Insurance;
use App\Models\PatientAdmission;
use App\Repositories\InsuranceRepository;
use DB;
use Exception;
use Flash;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class InsuranceController
 */
class InsuranceController extends AppBaseController
{
    /** @var InsuranceRepository */
    private $insuranceRepository;

    public function __construct(InsuranceRepository $insuranceRepo)
    {
        $this->insuranceRepository = $insuranceRepo;
    }

    /**
     * Display a listing of the Insurance.
     *
     * @param  Request  $request
     * @return Factory|View
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        $data['statusArr'] = Insurance::STATUS_ARR;

        return view('insurances.index', $data);
    }

    /**
     * Show the form for creating a new Insurance.
     *
     * @return Factory|View
     */
    public function create()
    {
        return view('insurances.create');
    }

    /**
     * Store a newly created Insurance in storage.
     *
     * @param  CreateInsuranceRequest  $request
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function store(CreateInsuranceRequest $request)
    {
        $input = $request->all();
        $input['service_tax'] = removeCommaFromNumbers($input['service_tax']);
        $input['hospital_rate'] = removeCommaFromNumbers($input['hospital_rate']);
        $input['status'] = isset($input['status']) ? 1 : 0;
        try {
            DB::beginTransaction();
            $insurance = $this->insuranceRepository->store($input);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage());
        }

        return $this->sendSuccess(__('messages.flash.insurance_saved'));
    }

    /**
     * @param  Insurance  $insurance
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Insurance $insurance)
    {
        if (! canAccessRecord(Insurance::class, $insurance->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        if (getLoggedInUser()->hasRole('Patient')) {
            $insuranceHasPatient = PatientAdmission::wherePatientId(getLoggedInUser()->owner_id)->whereInsuranceId($insurance->id)->exists();
            if (! $insuranceHasPatient) {
                return Redirect::back();
            }
        }

        $diseases = $this->insuranceRepository->getInsuranceDisease($insurance->id);

        return view('insurances.show', compact('diseases', 'insurance'));
    }

    /**
     * @param  Insurance  $insurance
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Insurance $insurance)
    {
        if (! canAccessRecord(Insurance::class, $insurance->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $diseases = $this->insuranceRepository->getInsuranceDisease($insurance->id);

        return view('insurances.edit', compact('diseases', 'insurance'));
    }

    /**
     * Update the specified Insurance in storage.
     *
     * @param  Insurance  $insurance
     * @param  UpdateInsuranceRequest  $request
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function update(Insurance $insurance, UpdateInsuranceRequest $request)
    {
        $input = $request->all();
        $input['service_tax'] = removeCommaFromNumbers($input['service_tax']);
        $input['hospital_rate'] = removeCommaFromNumbers($input['hospital_rate']);
        $input['status'] = isset($input['status']) ? 1 : 0;
        try {
            DB::beginTransaction();
            $insurance = $this->insuranceRepository->update($insurance, $input);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage());
        }

        return $this->sendSuccess(__('messages.flash.insurance_updated'));
    }

    /**
     * Remove the specified Insurance from storage.
     *
     * @param  Insurance  $insurance
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(Insurance $insurance)
    {
        if (! canAccessRecord(Insurance::class, $insurance->id)) {
            return $this->sendError(__('messages.flash.insurance_not_found'));
        }

        $insuranceModel = [
            PatientAdmission::class,
        ];
        $result = canDelete($insuranceModel, 'insurance_id', $insurance->id);
        if ($result) {
            return $this->sendError(__('messages.flash.insurance_cant_deleted'));
        }
        try {
            $this->insuranceRepository->delete($insurance->id);

            return $this->sendSuccess(__('messages.flash.insurance_deleted'));
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @param  int  $id
     * @return JsonResponse
     */
    public function activeDeactiveInsurance($id)
    {
        $insurance = Insurance::findOrFail($id);
        $insurance->status = ! $insurance->status;
        $insurance->update(['status' => $insurance->status]);

        return $this->sendSuccess(__('messages.flash.insurance_updated'));
    }

    /**
     * @return BinaryFileResponse
     */
    public function insuranceExport()
    {
        $response = Excel::download(new InsuranceExport, 'insurances-'.time().'.xlsx');

        ob_end_clean();

        return $response;
    }
}
