<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateBedTypeRequest;
use App\Http\Requests\UpdateBedTypeRequest;
use App\Models\Bed;
use App\Models\BedType;
use App\Models\IpdPatientDepartment;
use App\Repositories\BedTypeRepository;
use Exception;
use Flash;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Class BedTypeController
 */
class BedTypeController extends AppBaseController
{
    /** @var BedTypeRepository */
    private $bedTypeRepository;

    public function __construct(BedTypeRepository $bedTypeRepo)
    {
        $this->bedTypeRepository = $bedTypeRepo;
    }

    /**
     * Display a listing of the Bed_Type.
     *
     * @param  Request  $request
     * @return Factory|View
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        return view('bed_types.index');
    }

    /**
     * Store a newly created Bed_Type in storage.
     *
     * @param  CreateBedTypeRequest  $request
     * @return JsonResponse
     */
    public function store(CreateBedTypeRequest $request)
    {
        $input = $request->all();

        $bedType = $this->bedTypeRepository->create($input);

        return $this->sendSuccess(__('messages.flash.bed_type_saved'));
    }

    /**
     * @param  BedType  $bedType
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function show(BedType $bedType)
    {
        if (! canAccessRecord(BedType::class, $bedType->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $beds = $bedType->beds;

        return view('bed_types.show', compact('bedType', 'beds'));
    }

    /**
     * Show the form for editing the specified Bed_Type.
     *
     * @param  BedType  $bedType
     * @return JsonResponse
     */
    public function edit(BedType $bedType)
    {
        if (! canAccessRecord(BedType::class, $bedType->id)) {
            return $this->sendError(__('messages.flash.not_allow_access_record'));
        }

        return $this->sendResponse($bedType, __('messages.flash.bed_type_retrieved'));
    }

    /**
     * Update the specified Bed_Type in storage.
     *
     * @param  BedType  $bedType
     * @param  UpdateBedTypeRequest  $request
     * @return JsonResponse
     */
    public function update(BedType $bedType, UpdateBedTypeRequest $request)
    {
        $input = $request->all();
        $bedType = $this->bedTypeRepository->update($input, $bedType->id);

        return $this->sendSuccess(__('messages.flash.bed_type_updated'));
    }

    /**
     * Remove the specified Bed_Type from storage.
     *
     * @param  BedType  $bedType
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(BedType $bedType)
    {
        if (! canAccessRecord(BedType::class, $bedType->id)) {
            return $this->sendError(__('messages.flash.bed_type_not_found'));
        }

        $bed = Bed::whereBedType($bedType->id)->exists();
        $ipdPatientDepartment = IpdPatientDepartment::whereBedTypeId($bedType->id)->exists();

        if ($bed || $ipdPatientDepartment) {
            return $this->sendError(__('messages.flash.bed_type_cant_deleted'));
        }

        $this->bedTypeRepository->delete($bedType->id);

        return $this->sendSuccess(__('messages.flash.bed_type_deleted'));
    }
}
