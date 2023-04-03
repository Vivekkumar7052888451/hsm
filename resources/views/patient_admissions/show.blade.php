@extends('layouts.app')
@section('title')
    {{ __('messages.patient_admission.details') }}
@endsection
@section('header_toolbar')
    <div class="container-fluid">
        <div class="d-md-flex align-items-center justify-content-between mb-5">
            <h1 class="mb-0">{{__('messages.patient_admission.details')}}</h1>
            <div class="text-end mt-4 mt-md-0">
                <a class="btn btn-primary me-4"
                   href="{{ route('patient-admissions.edit',['patient_admission' => $patientAdmission->id])}}">{{ __('messages.common.edit') }}</a>
                <a href="{{ url()->previous() }}"
                   class="btn btn-outline-primary">{{ __('messages.common.back') }}</a>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="container-fluid">
        @include('flash::message')
        <div class="d-flex flex-column">
            <div class="card">
                <div class="card-body">
                    @include('patient_admissions.show_fields')
                </div>
            </div>
        </div>
    </div>
@endsection
