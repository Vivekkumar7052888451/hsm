<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateIpdPrescriptionRequest;
use App\Http\Requests\UpdateIpdPrescriptionRequest;
use App\Models\IpdPrescription;
use App\Repositories\IpdPrescriptionRepository;
use Exception;
use Flash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Response;
use Throwable;

class IpdPrescriptionController extends AppBaseController
{
    /** @var IpdPrescriptionRepository */
    private $ipdPrescriptionRepository;

    public function __construct(IpdPrescriptionRepository $ipdPrescriptionRepo)
    {
        $this->ipdPrescriptionRepository = $ipdPrescriptionRepo;
    }

    /**
     * Display a listing of the IpdPrescription.
     *
     * @param  Request  $request
     * @return Response
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
    }

    /**
     * Store a newly created IpdPrescription in storage.
     *
     * @param  CreateIpdPrescriptionRequest  $request
     * @return JsonResponse
     */
    public function store(CreateIpdPrescriptionRequest $request)
    {
        $input = $request->all();
        $this->ipdPrescriptionRepository->store($input);
        $this->ipdPrescriptionRepository->createNotification($input);

        return $this->sendSuccess(__('messages.flash.IPD_Prescription_saved'));
    }

    /**
     * Display the specified IPD Prescription.
     *
     * @param  IpdPrescription  $ipdPrescription
     * @return array|string
     *
     * @throws Throwable
     */
    public function show(IpdPrescription $ipdPrescription)
    {
        if (! canAccessRecord(IpdPrescription::class, $ipdPrescription->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        return view('ipd_prescriptions.show_ipd_prescription_data', compact('ipdPrescription'))->render();
    }

    /**
     * Show the form for editing the specified IpdPrescription.
     *
     * @param  IpdPrescription  $ipdPrescription
     * @return JsonResponse
     */
    public function edit(IpdPrescription $ipdPrescription)
    {
        if (! canAccessRecord(IpdPrescription::class, $ipdPrescription->id)) {
            return $this->sendError(__('messages.flash.not_allow_access_record'));
        }

        $ipdPrescriptionData = $this->ipdPrescriptionRepository->getIpdPrescriptionData($ipdPrescription);

        return $this->sendResponse($ipdPrescriptionData, __('messages.flash.IPD_Prescription_retrieved'));
    }

    /**
     * Update the specified IpdPrescriptionItem in storage.
     *
     * @param  IpdPrescription  $ipdPrescription
     * @param  UpdateIpdPrescriptionRequest  $request
     * @return JsonResponse
     */
    public function update(IpdPrescription $ipdPrescription, UpdateIpdPrescriptionRequest $request)
    {
        $this->ipdPrescriptionRepository->updateIpdPrescriptionItems($request->all(), $ipdPrescription);

        return $this->sendSuccess(__('messages.flash.IPD_Prescription_updated'));
    }

    /**
     * Remove the specified IpdPrescriptionItem from storage.
     *
     * @param  IpdPrescription  $ipdPrescription
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(IpdPrescription $ipdPrescription)
    {
        if (! canAccessRecord(IpdPrescription::class, $ipdPrescription->id)) {
            return $this->sendError(__('messages.flash.ipd_prescription_not_found'));
        }

        $ipdPrescription->ipdPrescriptionItems()->delete();
        $ipdPrescription->delete();

        return $this->sendSuccess(__('messages.flash.IPD_Prescription_deleted'));
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getMedicineList(Request $request)
    {
        $chargeCategories = $this->ipdPrescriptionRepository->getMedicines($request->get('id'));

        return $this->sendResponse($chargeCategories, __('messages.flash.retrieve'));
    }
}
