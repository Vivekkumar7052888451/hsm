<?php

namespace App\Http\Controllers;

use App\Exports\PaymentExport;
use App\Http\Requests\CreatePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
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

class PaymentController extends AppBaseController
{
    /** @var PaymentRepository */
    private $paymentRepository;

    public function __construct(PaymentRepository $paymentRepo)
    {
        $this->paymentRepository = $paymentRepo;
    }

    /**
     * Display a listing of the Payment.
     *
     * @param  Request  $request
     * @return Factory|View
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        return view('payments.index');
    }

    /**
     * Show the form for creating a new Payment.
     *
     * @return Factory|View
     */
    public function create()
    {
        $accounts = $this->paymentRepository->getAccounts();

        return view('payments.create', compact('accounts'));
    }

    /**
     * Store a newly created Payment in storage.
     *
     * @param  CreatePaymentRequest  $request
     * @return RedirectResponse|Redirector
     */
    public function store(CreatePaymentRequest $request)
    {
        $input = $request->all();
        $input['amount'] = removeCommaFromNumbers($input['amount']);
        $payment = $this->paymentRepository->create($input);

        Flash::success(__('messages.flash.payment_saved'));

        return redirect(route('payments.index'));
    }

    /**
     * @param  Payment  $payment
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|RedirectResponse
     */
    public function show(Payment $payment)
    {
        if (! canAccessRecord(Payment::class, $payment->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        return view('payments.show')->with('payment', $payment);
    }

    /**
     * Show the form for editing the specified Payment.
     *
     * @param  int  $id
     * @return Factory|View
     */
    public function edit($id)
    {
        if (! canAccessRecord(Payment::class, $id)) {
            return Redirect::back();
        }
        $payment = Payment::findOrFail($id);
        $accounts = $this->paymentRepository->getAccounts();

        return view('payments.edit', compact('accounts', 'payment'));
    }

    /**
     * Update the specified Payment in storage.
     *
     * @param  Payment  $payment
     * @param  UpdatePaymentRequest  $request
     * @return RedirectResponse|Redirector
     */
    public function update(Payment $payment, UpdatePaymentRequest $request)
    {
        $input = $request->all();
        $input['amount'] = removeCommaFromNumbers($input['amount']);
        $payment = $this->paymentRepository->update($input, $payment->id);

        Flash::success(__('messages.flash.payment_updated'));

        return redirect(route('payments.index'));
    }

    /**
     * Remove the specified Payment from storage.
     *
     * @param  Payment  $payment
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(Payment $payment)
    {
        $this->paymentRepository->delete($payment->id);

        return $this->sendSuccess(__('messages.flash.payment_deleted'));
    }

    /**
     * @return BinaryFileResponse
     */
    public function paymentExport()
    {
        $response = Excel::download(new PaymentExport, 'payments-'.time().'.xlsx');

        ob_end_clean();

        return $response;
    }

    /**
     * @param $payment
     * @return JsonResponse
     */
    public function showModal(Payment $payment)
    {
        if (! canAccessRecord(Payment::class, $payment->id)) {
            return $this->sendError(__('messages.flash.not_allow_access_record'));
        }

        $payment->load('account');
        $payment['amount'] = getCurrencyFormat($payment->amount);

        return $this->sendResponse($payment, __('messages.flash.payment_retrieved'));
    }
}
