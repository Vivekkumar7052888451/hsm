@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            <img src="{{ asset(getSuperAdminAppLogoUrl()) }}" class="logo" style="object-fit: cover" alt="{{ getSuperAdminAppName() }}">
        @endcomponent
    @endslot
    {{-- Body --}}
    <div>
        <h2>Hello!</h2>
        <p>A new hospital has been registered in your list of hospitals.</p>
        <br>
        <p><b>Hospital Name:</b> {{$hospital_name}}</p>
        <p><b>Hospital Email:</b> {{$hospital_email}}</p>
        <p><b>Hospital Contact:</b> {{$hospital_phone}}</p>
        <br>
        <p>Thanks & Regards,</p>
        <p>{{ getSuperAdminAppName() }}</p>
    </div>
    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            <h6>© {{ date('Y') }} {{ getSuperAdminAppName() }}.</h6>
        @endcomponent
    @endslot
@endcomponent
