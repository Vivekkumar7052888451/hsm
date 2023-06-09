@if ($row->payment_type == 1)
    <a data-id="{{ $row->payment_type }}"
       class="badge bg-light-primary text-decoration-none">{{ __('messages.setting.stripe') }}</a>
@elseif ($row->payment_type == 2)
    <a data-id="{{ $row->payment_type }}"
       class="badge bg-light-primary text-decoration-none">{{ __('messages.setting.paypal') }}</a>
@elseif ($row->payment_type == 3)
    <a data-id="{{ $row->payment_type }}"
       class="badge bg-light-primary text-decoration-none">{{ __('messages.setting.razorpay') }}</a>
@elseif ($row->payment_type == 4)
    <a data-id="{{ $row->payment_type }}" class="badge bg-light-primary text-decoration-none">Cash</a>
@elseif ($row->payment_type == 5)
    <a data-id="{{ $row->payment_type }}"
       class="badge bg-light-primary text-decoration-none">{{ __('messages.paytm') }}</a>
@elseif ($row->payment_type == 6)
    <a data-id="{{ $row->payment_type }}"
       class="badge bg-light-primary text-decoration-none">{{ __('messages.setting.paystack') }}</a>
@else
    N/A
@endif
