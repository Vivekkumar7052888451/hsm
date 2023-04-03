<?php

namespace App\Http\Controllers;
// use App\Http\Requests\CreateIpdPatientDepartmentRequest;
use App\Http\Requests\UpdateErDepartmentRequest;
use App\Models\EmergencyRoom;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Repositories\ErRoomRepository;
use Flash;

class EmergencyRoomController extends AppBaseController
{
    private $emergencyRoomRepo;
    public function __construct(ErRoomRepository $emergencyRoomRepo)
    {
        $this->emergencyRoomRepo = $emergencyRoomRepo;
    }
    public function index()
    {
        $statusArr = EmergencyRoom::STATUS_ARR;
        // dd($statusArr);
        return view('er.index',compact('statusArr'));  
    }
    public function create()
    {
        $data = $this->emergencyRoomRepo->getAssociatedData();
        return view('er.create', compact('data'));
    }
    public function store(Request $request)
    {
        $input = $request->except('_token');
        $this->emergencyRoomRepo->store($input);
        $this->emergencyRoomRepo->createNotification($input);
        Flash::success(__('messages.flash.emergency_room_saved'));

        return redirect(route('er.patient.index'));
    }
     public function getPatientCasesList(Request $request)
    {
        $patientCases = $this->emergencyRoomRepo->getPatientCases($request->get('id'));
        return $this->sendResponse($patientCases, __('messages.flash.retrieve'));
    }
    public function destroy(EmergencyRoom $erRoom)
    {
        if (! canAccessRecord(EmergencyRoom::class, $erRoom->id)) {
            return $this->sendError(__('messages.flash.ipd_patient_not_found'));
        }

        $this->emergencyRoomRepo->deleteErRoom($erRoom);

        return $this->sendSuccess(__('messages.flash.IPD_Patient_deleted'));
    }

     public function edit(EmergencyRoom $erRoom)
    {

        if (! canAccessRecord(EmergencyRoom::class, $erRoom->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $data = $this->emergencyRoomRepo->getAssociatedData();

        return view('er.edit', compact('data', 'erRoom'));
    }
      public function update(EmergencyRoom $erRoom, UpdateErDepartmentRequest $request)
    {
        $input = $request->all();
        $this->emergencyRoomRepo->updateErDepartment($input, $erRoom);
        Flash::success(__('messages.flash.er_updated'));
        return redirect(route('er.patient.index'));
    }
}
