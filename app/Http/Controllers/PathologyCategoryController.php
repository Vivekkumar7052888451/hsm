<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePathologyCategoryRequest;
use App\Http\Requests\UpdatePathologyCategoryRequest;
use App\Models\PathologyCategory;
use App\Models\PathologyTest;
use App\Repositories\PathologyCategoryRepository;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PathologyCategoryController extends AppBaseController
{
    /** @var PathologyCategoryRepository */
    private $pathologyCategoryRepository;

    public function __construct(PathologyCategoryRepository $pathologyCategoryRepo)
    {
        $this->middleware('check_menu_access');
        $this->pathologyCategoryRepository = $pathologyCategoryRepo;
    }

    /**
     * Display a listing of the PathologyCategory.
     *
     * @param  Request  $request
     * @return Factory|View
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        return view('pathology_categories.index');
    }

    /**
     * Store a newly created PathologyCategory in storage.
     *
     * @param  CreatePathologyCategoryRequest  $request
     * @return JsonResponse
     */
    public function store(CreatePathologyCategoryRequest $request)
    {
        $input = $request->all();
        $this->pathologyCategoryRepository->create($input);

        return $this->sendSuccess(__('messages.flash.pathology_category_saved'));
    }

    /**
     * Show the form for editing the specified PathologyCategory.
     *
     * @param  PathologyCategory  $pathologyCategory
     * @return JsonResponse
     */
    public function edit(PathologyCategory $pathologyCategory)
    {
        if (! canAccessRecord(PathologyCategory::class, $pathologyCategory->id)) {
            return $this->sendError(__('messages.flash.not_allow_access_record'));
        }

        return $this->sendResponse($pathologyCategory, __('messages.flash.pathology_category_retrieved'));
    }

    /**
     * Update the specified PathologyCategory in storage.
     *
     * @param  PathologyCategory  $pathologyCategory
     * @param  UpdatePathologyCategoryRequest  $request
     * @return JsonResponse
     */
    public function update(PathologyCategory $pathologyCategory, UpdatePathologyCategoryRequest $request)
    {
        $input = $request->all();
        $this->pathologyCategoryRepository->update($input, $pathologyCategory->id);

        return $this->sendSuccess(__('messages.flash.pathology_category_updated'));
    }

    /**
     * Remove the specified PathologyCategory from storage.
     *
     * @param  PathologyCategory  $pathologyCategory
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(PathologyCategory $pathologyCategory)
    {
        if (! canAccessRecord(PathologyCategory::class, $pathologyCategory->id)) {
            return $this->sendError(__('messages.flash.pathology_category_not_found'));
        }

        $pathologyCategoryModels = [
            PathologyTest::class,
        ];
        $result = canDelete($pathologyCategoryModels, 'category_id', $pathologyCategory->id);
        if ($result) {
            return $this->sendError(__('messages.flash.pathology_category_cant_deleted'));
        }

        $pathologyCategory->delete();

        return $this->sendSuccess(__('messages.flash.pathology_category_deleted'));
    }
}
