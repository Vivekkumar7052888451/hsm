<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Medicine;
use App\Repositories\CategoryRepository;
use Exception;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class CategoryController extends AppBaseController
{
    /** @var CategoryRepository */
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepo)
    {
        $this->categoryRepository = $categoryRepo;
    }

    /**
     * Display a listing of the Category.
     *
     * @param  Request  $request
     * @return Factory|View
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        $data['statusArr'] = Category::STATUS_ARR;

        return view('categories.index', $data);
    }

    /**
     * Store a newly created Category in storage.
     *
     * @param  CreateCategoryRequest  $request
     * @return JsonResponse
     */
    public function store(CreateCategoryRequest $request)
    {
        $input = $request->all();
        $input['is_active'] = ! isset($input['is_active']) ? false : true;
        $this->categoryRepository->create($input);

        return $this->sendSuccess(__('messages.flash.medicine_category_saved'));
    }

    /**
     * @param  Category  $category
     * @return Application|Factory|\Illuminate\Contracts\View\View|RedirectResponse
     */
    public function show(Category $category)
    {
        if (! canAccessRecord(Category::class, $category->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $medicines = $category->medicines;

        return view('categories.show', compact('medicines', 'category'));
    }

    /**
     * Show the form for editing the specified Category.
     *
     * @param  Category  $category
     * @return JsonResponse
     */
    public function edit(Category $category)
    {
        if (! canAccessRecord(Category::class, $category->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        return $this->sendResponse($category, __('messages.flash.medicine_category_retrieved'));
    }

    /**
     * Update the specified Category in storage.
     *
     * @param  Category  $category
     * @param  UpdateCategoryRequest  $request
     * @return JsonResponse
     */
    public function update(Category $category, UpdateCategoryRequest $request)
    {
        $input = $request->all();
        $input['is_active'] = ! isset($input['is_active']) ? false : true;
        $this->categoryRepository->update($input, $category->id);

        return $this->sendSuccess(__('messages.flash.medicine_category_updated'));
    }

    /**
     * Remove the specified Category from storage.
     *
     * @param  Category  $category
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(Category $category)
    {
        if (! canAccessRecord(Category::class, $category->id)) {
            return $this->sendError(__('messages.flash.medicine_category_not_found'));
        }

        $medicineCategoryModel = [
            Medicine::class,
        ];
        $result = canDelete($medicineCategoryModel, 'category_id', $category->id);
        if ($result) {
            return $this->sendError(__('messages.flash.medicine_category_cant_deleted'));
        }
        $this->categoryRepository->delete($category->id);

        return $this->sendSuccess(__('messages.flash.medicine_category_deleted'));
    }

    /**
     * @param  int  $id
     * @return JsonResponse
     */
    public function activeDeActiveCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->is_active = ! $category->is_active;
        $category->save();

        return $this->sendSuccess(__('messages.flash.medicine_category_updated'));
    }
}
