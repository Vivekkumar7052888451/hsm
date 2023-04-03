<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateNoticeBoardRequest;
use App\Http\Requests\UpdateNoticeBoardRequest;
use App\Models\NoticeBoard;
use App\Repositories\NoticeBoardRepository;
use Exception;
use Flash;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class NoticeBoardController extends AppBaseController
{
    /** @var NoticeBoardRepository */
    private $noticeBoardRepository;

    public function __construct(NoticeBoardRepository $noticeBoardRepo)
    {
        $this->noticeBoardRepository = $noticeBoardRepo;
    }

    /**
     * Display a listing of the NoticeBoard.
     *
     * @param  Request  $request
     * @return Factory|View
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        return view('notice_boards.index');
    }

    /**
     * Store a newly created NoticeBoard in storage.
     *
     * @param  CreateNoticeBoardRequest  $request
     * @return JsonResponse
     */
    public function store(CreateNoticeBoardRequest $request)
    {
        $input = $request->all();
        $this->noticeBoardRepository->create($input);
        $this->noticeBoardRepository->createNotification();

        return $this->sendSuccess(__('messages.flash.notice_board_saved'));
    }

    /**
     * @param  NoticeBoard  $noticeBoard
     * @return Factory|RedirectResponse|Redirector|View
     */
    public function show(NoticeBoard $noticeBoard)
    {
        if (! canAccessRecord(NoticeBoard::class, $noticeBoard->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        return view('notice_boards.show')->with('noticeBoard', $noticeBoard);
    }

    /**
     * Show the form for editing the specified NoticeBoard.
     *
     * @param  NoticeBoard  $noticeBoard
     * @return JsonResponse
     */
    public function edit(NoticeBoard $noticeBoard)
    {
        if (! canAccessRecord(NoticeBoard::class, $noticeBoard->id)) {
            return $this->sendError(__('messages.flash.not_allow_access_record'));
        }

        return $this->sendResponse($noticeBoard, __('messages.flash.notice_board_retrieved'));
    }

    /**
     * @param  NoticeBoard  $noticeBoard
     * @param  UpdateNoticeBoardRequest  $request
     * @return JsonResponse
     */
    public function update(NoticeBoard $noticeBoard, UpdateNoticeBoardRequest $request)
    {
        $this->noticeBoardRepository->update($request->all(), $noticeBoard->id);

        return $this->sendSuccess(__('messages.flash.notice_board_updated'));
    }

    /**
     * Remove the specified NoticeBoard from storage.
     *
     * @param  NoticeBoard  $noticeBoard
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(NoticeBoard $noticeBoard)
    {
        if (! canAccessRecord(NoticeBoard::class, $noticeBoard->id)) {
            return $this->sendError(__('messages.flash.notice_board_not_found'));
        }

        $noticeBoard->delete();

        return $this->sendSuccess(__('messages.flash.notice_board_deleted'));
    }
}
