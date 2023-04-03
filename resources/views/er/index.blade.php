@extends('layouts.app')
@section('title')
    {{ __('messages.emergency.er_title') }}
@endsection
@section('css')
{{--    <link rel="stylesheet" href="{{ asset('assets/css/sub-header.css') }}">--}}
@endsection
@section('content')

    <div class="container-fluid">
        <div class="d-flex flex-column">
            @include('flash::message')

            {{Form::hidden('erRoomUrl',url('er'),['id'=>'erRUrl'])}}
            {{Form::hidden('patientUrl',url('patients'),['id'=>'indexIpdDepartmentPatientUrl'])}}
            {{Form::hidden('doctorUrl',url('doctors'),['id'=>'indexIpdDepartmentDoctorUrl'])}}
            {{Form::hidden('bedUrl',url('beds'),['id'=>'indexIpdDepartmentBedUrl'])}}
            {{ Form::hidden('ipdPatientLang', __('messages.delete.er_room_delete'), ['id' => 'ipdPatientLang']) }}

            <livewire:emergency-room-table/>
            
        </div>
        {{--@include('er.templates.templates')--}}
    </div>
@endsection

