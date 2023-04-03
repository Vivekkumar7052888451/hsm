@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            <img src="{{ asset(getSuperAdminAppLogoUrl()) }}" class="logo" style="object-fit: cover" alt="{{ getSuperAdminAppName() }}">
        @endcomponent
    @endslot
    {{-- Body --}}
    <p><b>Hello Dr.{{$doctor_name}},</b></p>
    <p>This is just to remind you that your appointment with {{$patient_name}} is within next one hour.</p>
    <p>Patient Problem: {{$problem}}</p>
    <p>Appointment Time: {{ \Carbon\Carbon::parse($appointment_date)->translatedFormat('jS M, Y g:i A') }}</p>
    <br>
    <p>Thanks & Regards,</p>
    <p>{{ getSuperAdminAppName() }}</p>
    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            <h6>© {{ date('Y') }} {{ getSuperAdminAppName() }}.</h6>
        @endcomponent
    @endslot
@endcomponent
