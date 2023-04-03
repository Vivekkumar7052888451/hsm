@extends('layouts.app')
@section('title')
    {{ __('messages.emergency.edit_emergency_room') }}
@endsection
@section('header_toolbar')
    <div class="container-fluid">
        <div class="d-md-flex align-items-center justify-content-between mb-7">
            <h1 class="mb-0">@yield('title')</h1>
            <a href="{{ route('er.patient.index') }}"
               class="btn btn-outline-primary">{{ __('messages.common.back') }}</a>
        </div>
    </div>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-column">
            <div class="row">
                <div class="col-12">
                    @include('layouts.errors')
                </div>
            </div>
            <div class="card">
               
                {{Form::hidden('patientCasesUrl',route('patient.cases.list'),['id'=>'editPatientCasesUrl','class'=>'patientCasesUrl'])}}
                {{Form::hidden('patientBedsUrl',route('patient.beds.list'),['id'=>'editPatientBedsUrl','class'=>'patientBedsUrl'])}}
                {{Form::hidden('isEdit',true,['class'=>'isEdit'])}}
                {{Form::hidden('ipdPatientCaseId',$erRoom->case_id,['id'=>'editIpdPatientCaseId','class'=>'editIpdPatientCaseId'])}}
                {{Form::hidden('ipdPatientBedId',$erRoom->bed_id,['id'=>'editIpdPatientBedId','class'=>'ipdPatientBedId'])}}
                {{Form::hidden('ipdPatientBedTypeId',$erRoom->bed_type_id,['id'=>'editIpdPatientBedTypeId','class'=>'ipdPatientBedTypeId'])}}
                <div class="card-body p-12">
                     {{Form::model($erRoom, ['route' => ['ipd.patient.update', $erRoom->id], 'method' => 'patch', 'id' => 'erDepartmentForm'])}} 

                   @include('er.edit_fields')

                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
{{--        let patientCasesUrl = "{{ route('patient.cases.list') }}";--}}
{{--        let patientBedsUrl = "{{ route('patient.beds.list') }}";--}}
{{--        let isEdit = true;--}}
{{--        let ipdPatientBedId = "{{ $ipdPatientDepartment->bed_id }}";--}}
{{--        let ipdPatientBedTypeId = "{{ $ipdPatientDepartment->bed_type_id }}";--}}
{{--    <script src="{{mix('assets/js/ipd_patients/create.js')}}"></script>--}}
