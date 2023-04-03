<?php

namespace App\Http\Controllers;

use Anand\LaravelPaytmWallet\Facades\PaytmWallet;
use App\Http\Requests\CreatePaytmDetailRequest;
use App\Models\IpdPatientDepartment;
use App\Repositories\PatientPaytmRepository;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class PatientPaytmController extends Controller
{
    /**
     * @var PatientPaytmRepository
     */
    private $patientPaytmRepository;

    /**
     * PatientPaytmController constructor.
     *
     * @param PatientPaytmRepository $patientPaytmRepository
     */
    public function __construct(PatientPaytmRepository $patientPaytmRepository)
    {
        $this->patientPaytmRepository = $patientPaytmRepository;
    }

    public function initiate(Request $request)
    {
        $amount = $request->get('amount');
        $ipdNumber = $request->get('ipdNumber');

        if (strtolower(getCurrentCurrency()) != 'inr') {
            Flash::error(__('Paytm only supported indian currency.'));

            return redirect(route('patient.ipd'));
        }

        return view('patient_paytm.index', compact('amount','ipdNumber'));
    }

    public function payment(CreatePaytmDetailRequest $request)
    {
        $amount = $request->get('amount');
        $ipdNumber = $request->get('ipdNumber');
        $phone = $request->get('mobile');
        $ipdPatientId = IpdPatientDepartment::whereIpdNumber($ipdNumber)->first()->id;
        $orderId = $ipdPatientId.'|'.time();

        $payment = PaytmWallet::with('receive');

        $payment->prepare([
            'order'         => $orderId, // 1 should be your any data id
            'user'          => getLoggedInUserId(), // any user id
            'mobile_number' => $phone,
            'email'         => getLoggedInUser()->email, // your user email address
            'amount'        => $amount,
            'callback_url'  => route('patient.paytm.callback'), // callback URLs
        ]);

        return $payment->receive();
    }

    /**
     *
     */
    public function paymentCallback()
    {
        $paytmPaymentTransaction = PaytmWallet::with('receive');
        $response = $paytmPaymentTransaction->response();

        if ($response == "failed"){
            Flash::error(__('messages.flash.unable_to_process'));

            return redirect(route('patient.ipd'));
        }elseif ($response['RESPCODE'] == 01){
            $this->patientPaytmRepository->patientPaymentSuccess($response);

            Flash::success(__('messages.flash.your_payment_success'));

            return redirect(route('patient.ipd'));
        }

        $failureMsg = $response['RESPMSG'];

        Flash::error($failureMsg);

        return redirect(route('patient.ipd'));

    }

    public function failed()
    {
        Flash::error(__('messages.flash.unable_to_process'));

        return redirect(route('patient.ipd'));
    }
}
