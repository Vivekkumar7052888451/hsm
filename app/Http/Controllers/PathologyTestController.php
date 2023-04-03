<?php

namespace App\Http\Controllers;

use App\Exports\PathologyTestExport;
use App\Http\Requests\CreatePathologyTestRequest;
use App\Http\Requests\UpdatePathologyTestRequest;
use App\Models\Charge;
use App\Models\PathologyTest;
use App\Repositories\PathologyTestRepository;
use Exception;
use Flash;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PathologyTestController extends AppBaseController
{
    /** @var PathologyTestRepository */
    private $pathologyTestRepository;

    public function __construct(PathologyTestRepository $pathologyTestRepo)
    {
        $this->middleware('check_menu_access');
        $this->pathologyTestRepository = $pathologyTestRepo;
    }

    /**
     * Display a listing of the PathologyTest.
     *
     * @param  Request  $request
     * @return Factory|View
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        return view('pathology_tests.index');
    }

    /**
     * Show the form for creating a new PathologyTest.
     *
     * @return Factory|View
     */
    public function create()
    {
        $data = $this->pathologyTestRepository->getPathologyAssociatedData();

        return view('pathology_tests.create', compact('data'));
    }

    /**
     * Store a newly created PathologyTest in storage.
     *
     * @param  CreatePathologyTestRequest  $request
     * @return RedirectResponse|Redirector
     */
    public function store(CreatePathologyTestRequest $request)
    {
        $input = $request->all();
        $input['standard_charge'] = removeCommaFromNumbers($input['standard_charge']);
        $input['unit'] = ! empty($input['unit']) ? $input['unit'] : null;
        $input['subcategory'] = ! empty($input['subcategory']) ? $input['subcategory'] : null;
        $input['method'] = ! empty($input['method']) ? $input['method'] : null;
        $input['report_days'] = ! empty($input['report_days']) ? $input['report_days'] : null;
        $this->pathologyTestRepository->create($input);
        Flash::success(__('messages.flash.pathology_test_saved'));

        return redirect(route('pathology.test.index'));
    }

    /**
     * @param  PathologyTest  $pathologyTest
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|RedirectResponse
     */
    public function show(PathologyTest $pathologyTest)
    {
        if (! canAccessRecord(PathologyTest::class, $pathologyTest->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        return view('pathology_tests.show', compact('pathologyTest'));
    }

    /**
     * @param  PathologyTest  $pathologyTest
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|RedirectResponse
     */
    public function edit(PathologyTest $pathologyTest)
    {
        if (! canAccessRecord(PathologyTest::class, $pathologyTest->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $data = $this->pathologyTestRepository->getPathologyAssociatedData();

        return view('pathology_tests.edit', compact('pathologyTest', 'data'));
    }

    /**
     * Update the specified PathologyTest in storage.
     *
     * @param  PathologyTest  $pathologyTest
     * @param  UpdatePathologyTestRequest  $request
     * @return RedirectResponse|Redirector
     */
    public function update(PathologyTest $pathologyTest, UpdatePathologyTestRequest $request)
    {
        $input = $request->all();
        $input['standard_charge'] = removeCommaFromNumbers($input['standard_charge']);
        $input['unit'] = ! empty($input['unit']) ? $input['unit'] : null;
        $input['subcategory'] = ! empty($input['subcategory']) ? $input['subcategory'] : null;
        $input['method'] = ! empty($input['method']) ? $input['method'] : null;
        $input['report_days'] = ! empty($input['report_days']) ? $input['report_days'] : null;
        $this->pathologyTestRepository->update($input, $pathologyTest->id);
        Flash::success(__('messages.flash.pathology_test_updated'));

        return redirect(route('pathology.test.index'));
    }

    /**
     * Remove the specified PathologyTest from storage.
     *
     * @param  PathologyTest  $pathologyTest
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(PathologyTest $pathologyTest)
    {
        if (! canAccessRecord(PathologyTest::class, $pathologyTest->id)) {
            return $this->sendError(__('messages.flash.pathology_test_not_found'));
        }

        $pathologyTest->delete();

        return $this->sendSuccess(__('messages.flash.pathology_test_deleted'));
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getStandardCharge($id)
    {
        $standardCharges = Charge::where('charge_category_id', $id)->value('standard_charge');

        return $this->sendResponse($standardCharges, __('messages.flash.Standard_charge_retrieved'));
    }

    /**
     * @return BinaryFileResponse
     */
    public function pathologyTestExport()
    {
        $response = Excel::download(new PathologyTestExport, 'pathology-tests-'.time().'.xlsx');

        ob_end_clean();

        return $response;
    }

    public function showModal(PathologyTest $pathologyTest)
    {
        if (! canAccessRecord(PathologyTest::class, $pathologyTest->id)) {
            return $this->sendError(__('messages.flash.not_allow_access_record'));
        }

        $pathologyTest->load(['pathologycategory', 'chargecategory']);

        return $this->sendResponse($pathologyTest, __('messages.flash.pathology_test_retrieved'));
    }
}
