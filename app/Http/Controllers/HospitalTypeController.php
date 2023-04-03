<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHospitalTypeRequest;
use App\Http\Requests\UpdateHospitalTypeRequest;
use App\Models\User;
use App\Repositories\HospitalTypeRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Flash;
use Response;

class HospitalTypeController extends AppBaseController
{
    /** @var HospitalTypeRepository $hospitalTypeRepository*/
    private $hospitalTypeRepository;

    public function __construct(HospitalTypeRepository $hospitalTypeRepo)
    {
        $this->hospitalTypeRepository = $hospitalTypeRepo;
    }

    /**
     * Display a listing of the HospitalType.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('hospital_type.index');
    }

    /**
     * Store a newly created HospitalType in storage.
     *
     * @param CreateHospitalTypeRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateHospitalTypeRequest $request): \Illuminate\Http\JsonResponse
    {
        $input = $request->all();

        $this->hospitalTypeRepository->create($input);

        return $this->sendSuccess('Hospital Type saved successfully.');
    }

    /**
     * Display the specified HospitalType.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
//    public function show($id)
//    {
//        $hospitalType = $this->hospitalTypeRepository->find($id);
//
//        if (empty($hospitalType)) {
//            Flash::error('Hospital Type not found');
//
////            return redirect(route('hospitalTypes.index'));
//        }
//
//        return view('hospital_types.show')->with('hospitalType', $hospitalType);
//    }

    /**
     * Show the form for editing the specified HospitalType.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id): \Illuminate\Http\JsonResponse
    {
        $hospitalType = $this->hospitalTypeRepository->find($id);

        if (empty($hospitalType)) {
            return $this->sendError('Hospital Type not found');
        }
        return $this->sendResponse($hospitalType, 'Hospital type retrieved successfully');
//        return view('hospital_types.edit')->with('hospitalType', $hospitalType);
    }

    /**
     * Update the specified HospitalType in storage.
     *
     * @param int $id
     * @param UpdateHospitalTypeRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, UpdateHospitalTypeRequest $request)
    {
        $hospitalType = $this->hospitalTypeRepository->find($id);

        if (empty($hospitalType)) {
            return $this->sendError('Hospital not found.');
        }

        $this->hospitalTypeRepository->update($request->all(), $id);

        return $this->sendSuccess('Hospital Type updated successfully.');
    }

    /**
     * Remove the specified HospitalType from storage.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $hospitalType = $this->hospitalTypeRepository->find($id);

        if (empty($hospitalType)) {
            return $this->sendError('Hospital Type not found.');
        }
        
        $models = [
          User::class,  
        ];
        
        $hospitalExist = canDelete($models , 'hospital_type_id',$id);
        
        if($hospitalExist){
            return $this->sendError('Hospital Type can\'t be deleted.');
        }

        $this->hospitalTypeRepository->delete($id);

        return $this->sendSuccess('Hospital Type deleted successfully.');
    }
}
