<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\IssuedItem;
use App\Models\Item;
use App\Models\ItemStock;
use App\Repositories\ItemRepository;
use Exception;
use Flash;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ItemController extends AppBaseController
{
    /** @var ItemRepository */
    private $itemRepository;

    public function __construct(ItemRepository $itemRepo)
    {
        $this->middleware('check_menu_access');
        $this->itemRepository = $itemRepo;
    }

    /**
     * Display a listing of the Item.
     *
     * @param  Request  $request
     * @return Factory|View
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        return view('items.index');
    }

    /**
     * Show the form for creating a new Item.
     *
     * @return Factory|View
     */
    public function create()
    {
        $itemCategories = $this->itemRepository->getItemCategories();

        return view('items.create', compact('itemCategories'));
    }

    /**
     * Store a newly created Item in storage.
     *
     * @param  CreateItemRequest  $request
     * @return RedirectResponse|Redirector
     */
    public function store(CreateItemRequest $request)
    {
        $input = $request->all();
        $input['description'] = ! empty($request->description) ? $request->description : null;
        $this->itemRepository->create($input);
        Flash::success(__('messages.flash.item_saved'));

        return redirect(route('items.index'));
    }

    /**
     * Display the specified Item.
     *
     * @param  Item  $item
     * @return Factory|View
     */
    public function show(Item $item)
    {
        if (! canAccessRecord(Item::class, $item->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        return view('items.show', compact('item'));
    }

    /**
     * @param  Item  $item
     * @return \Illuminate\Contracts\Foundation\Application|Factory|\Illuminate\Contracts\View\View|RedirectResponse
     */
    public function edit(Item $item)
    {
        if (! canAccessRecord(Item::class, $item->id)) {
            Flash::error(__('messages.flash.not_allow_access_record'));

            return Redirect::back();
        }

        $itemCategories = $this->itemRepository->getItemCategories();

        return view('items.edit', compact('item', 'itemCategories'));
    }

    /**
     * Update the specified Item in storage.
     *
     * @param  Item  $item
     * @param  UpdateItemRequest  $request
     * @return RedirectResponse|Redirector
     */
    public function update(Item $item, UpdateItemRequest $request)
    {
        $input = $request->all();
        $input['description'] = ! empty($request->description) ? $request->description : null;
        $this->itemRepository->update($input, $item->id);
        Flash::success(__('messages.flash.item_updated'));

        return redirect(route('items.index'));
    }

    /**
     * Remove the specified Item from storage.
     *
     * @param  Item  $item
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(Item $item)
    {
        if (! canAccessRecord(Item::class, $item->id)) {
            return $this->sendError(__('messages.flash.item_not_found'));
        }

        $itemModel = [
            ItemStock::class, IssuedItem::class,
        ];
        $result = canDelete($itemModel, 'item_id', $item->id);
        if ($result) {
            return $this->sendError(__('messages.flash.item_cant_deleted'));
        }
        $item->delete();

        return $this->sendSuccess(__('messages.flash.item_deleted'));
    }

    /**
     * @param  Request  $request
     * @return int
     */
    public function getAvailableQuantity(Request $request)
    {
        $data = Item::whereId($request->id)->first();

        return $data->available_quantity;
    }
}
