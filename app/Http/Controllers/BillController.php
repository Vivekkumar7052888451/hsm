<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Models\Bill;
use App\Models\Patient;
use App\Repositories\BillRepository;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use DB;
use Exception;
use Flash;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Response;

class BillController extends AppBaseController
{
    /** @var BillRepository */
    private $billRepository;

    public function __construct(BillRepository $billRepo)
    {
        $this->billRepository = $billRepo;
    }

    /**
     * Display a listing of the Bill.
     *
     * @param  Request  $request
     * @return Factory|View|Response
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        return view('bills.index');
    }

    /**
     * Show the form for creating a new Bill.
     *
     * @return Factory|View
     */
    public function create()
    {
        $data = $this->billRepository->getSyncList(false);

        return view('bills.create')->with($data);
    }

    /**
     * Store a newly created Bill in storage.
     *
     * @param  CreateBillRequest  $request
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function store(CreateBillRequest $request)
    {
        try {
            DB::beginTransaction();
            $input = $request->all();
            $patientId = Patient::with('patientUser')->whereId($input['patient_id'])->first();
            $birthDate = $patientId->patientUser->dob;
            $billDate = Carbon::parse($input['bill_date'])->toDateString();
            if (! empty($birthDate) && $billDate < $birthDate) {
                return $this->sendError(__('messages.flash.bill_date_smaller'));
            }
            $bill = $this->billRepository->saveBill($request->all());
            $this->billRepository->saveNotification($input);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($bill, __('messages.flash.bill_saved'));
    }

    /**
     * Display the specified Bill.
     *
     * @param  int  $id
     * @return Factory|View
     */
    public function show($id)
    {
        if (! canAccessRecord(Bill::class, $id)) {
            return Redirect::back();
        }

        $bill = Bill::with(['billItems.medicine', 'patient', 'patientAdmission'])->findOrFail($id);
        $admissionDate = Carbon::parse($bill->patientAdmission->admission_date);
        $dischargeDate = Carbon::parse($bill->patientAdmission->discharge_date);
        $bill->totalDays = $admissionDate->diffInDays($dischargeDate) + 1;

        return view('bills.show')->with('bill', $bill);
    }

    /**
     * @param  Bill  $bill
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Bill $bill)
    {
        if (! canAccessRecord(Bill::class, $bill->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $bill->billItems;
        $isEdit = true;
        $data = $this->billRepository->getSyncList($isEdit);
        $data['bill'] = $bill;

        return view('bills.edit')->with($data);
    }

    /**
     * Update the specified Bill in storage.
     *
     * @param  Bill  $bill
     * @param  UpdateBillRequest  $request
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function update(Bill $bill, UpdateBillRequest $request)
    {
        try {
            DB::beginTransaction();
            $input = $request->all();
            $patientId = Patient::with('patientUser')->whereId($input['patient_id'])->first();
            $birthDate = $patientId->patientUser->dob;
            $billDate = Carbon::parse($input['bill_date'])->toDateString();
            if (! empty($birthDate) && $billDate < $birthDate) {
                return $this->sendError('Bill date should not be smaller than patient birth date.');
            }
            $bill = $this->billRepository->updateBill($bill->id, $request->all());
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($bill, __('messages.flash.bill_updated'));
    }

    /**
     * Remove the specified Bill from storage.
     *
     * @param  Bill  $bill
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(Bill $bill)
    {
        $this->billRepository->delete($bill->id);

        return $this->sendSuccess(__('messages.flash.bill_deleted'));
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getPatientAdmissionDetails(Request $request)
    {
        $inputs = $request->all();
        $patientAdmissionDetails = $this->billRepository->patientAdmissionDetails($inputs);

        return $this->sendResponse($patientAdmissionDetails, __('messages.flash.bill_retrieved'));
    }

    /**
     * @param  Bill  $bill
     * @return \Illuminate\Http\Response
     */
    public function convertToPdf(Bill $bill)
    {
        if (! canAccessRecord(Bill::class, $bill->id)) {
            return Redirect::back();
        }

        $bill->billItems;
        $data = $this->billRepository->getSyncListForCreate($bill->id);
        $data['bill'] = $bill;
        $pdf = PDF::loadView('bills.bill_pdf', $data);

        return $pdf->stream('bill.pdf');
    }
}
