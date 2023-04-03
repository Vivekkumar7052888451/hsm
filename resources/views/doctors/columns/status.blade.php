@if(Auth::user()->hasRole('Patient|Doctor|Case Manager|Nurse|Receptionist|Lab Technician|Pharmacist'))
    @if($row->doctorUser->status)
        <span class="badge bg-light-success">{{__('messages.common.active')}} </span>
    @else
        <span class="badge bg-light-danger">{{__('messages.common.de_active') }}</span>
    @endif
@else
    <label class="form-check form-switch">
        <input name="status" data-id="{{$row->id}}" class="form-check-input doctor-active-status cursor-pointer" type="checkbox"
               value="1" {{$row->doctorUser->status == 0 ? '' : 'checked'}} >
        <span class="switch-slider" data-checked="&#x2713;" data-unchecked="&#x2715;"></span>
    </label>
@endif
