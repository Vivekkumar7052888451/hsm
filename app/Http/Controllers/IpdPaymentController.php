<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateIpdPaymentRequest;
use App\Http\Requests\UpdateIpdPaymentRequest;
use App\Models\IpdPayment;
use App\Repositories\IpdPaymentRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class IpdPaymentController extends AppBaseController
{
    /** @var IpdPaymentRepository */
    private $ipdPaymentRepository;

    public function __construct(IpdPaymentRepository $ipdPaymentRepo)
    {
        $this->ipdPaymentRepository = $ipdPaymentRepo;
    }

    /**
     * Display a listing of the IpdPayment.
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
     * Store a newly created IpdPayment in storage.
     *
     * @param  CreateIpdPaymentRequest  $request
     * @return JsonResponse
     */
    public function store(CreateIpdPaymentRequest $request)
    {
        $input = $request->all();

        $this->ipdPaymentRepository->store($input);

        return $this->sendSuccess(__('messages.flash.IPD_payment_saved'));
    }

    /**
     * Show the form for editing the specified Ipd Payment.
     *
     * @param  IpdPayment  $ipdPayment
     * @return JsonResponse
     */
    public function edit(IpdPayment $ipdPayment)
    {
        if (! canAccessRecord(IpdPayment::class, $ipdPayment->id)) {
            return $this->sendError(__('messages.flash.ipd_payment_not_found'));
        }

        return $this->sendResponse($ipdPayment, __('messages.flash.IPD_payment_retrieved'));
    }

    /**
     * Update the specified Ipd Payment in storage.
     *
     * @param  IpdPayment  $ipdPayment
     * @param  UpdateIpdPaymentRequest  $request
     * @return JsonResponse
     */
    public function update(IpdPayment $ipdPayment, UpdateIpdPaymentRequest $request)
    {
        $this->ipdPaymentRepository->updateIpdPayment($request->all(), $ipdPayment->id);

        return $this->sendSuccess(__('messages.flash.IPD_payment_updated'));
    }

    /**
     * Remove the specified IpdPayment from storage.
     *
     * @param  IpdPayment  $ipdPayment
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(IpdPayment $ipdPayment)
    {
        if (! canAccessRecord(IpdPayment::class, $ipdPayment->id)) {
            return $this->sendError(__('messages.flash.ipd_payment_not_found'));
        }

        $this->ipdPaymentRepository->deleteIpdPayment($ipdPayment->id);

        return $this->sendSuccess(__('messages.flash.IPD_payment_deleted'));
    }

    /**
     * @param  IpdPayment  $ipdPayment
     * @return Media
     */
    public function downloadMedia(IpdPayment $ipdPayment)
    {
        $media = $ipdPayment->getMedia(IpdPayment::IPD_PAYMENT_PATH)->first();
        ob_end_clean();
        if ($media != null) {
            $media = $media->id;
            $mediaItem = Media::findOrFail($media);

            return $mediaItem;
        }

        return '';
    }
}
