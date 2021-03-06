<?php

namespace App\Http\Controllers;

use App\Helper\Cart;
use App\Model\Character\Character;
use App\Model\Web\UserLog;
use App\Model\Web\Shop;
use App\Model\Web\ShopCategory;
use App\Model\Web\ShopItem;
use App\Model\Web\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShopController extends Controller
{
    /**
     * Show all shops.
     *
     * @return Response
     */
    public function index()
    {
        $shops = Shop::all();

        return view('shop.index', [
            'shops' => $shops
        ]);
    }

    /**
     * Show items shop.
     *
     * @param Request $request
     * @param Shop $shop
     * @param string $slug
     * @return Response
     */
    public function show(Request $request, Shop $shop, string $slug)
    {
        if (!$shop->is_active) {
            abort(404);
        }

        if ($slug !== $shop->slug) {
            return redirect()->route('shop.show', [$shop, $shop->slug]);
        }

        $categories = ShopCategory::all();
        $itemQuery = $shop->items();
        $canNotFind = false;
        $sortList = Shop::SORT_LIST;
        $saleTypes = Shop::SALE_TYPES;

        if ($request->input('title')) {
            $itemQuery->where('title', 'like', '%' . $request->input('title') . '%');
        }

        $itemQuery->whereBetween('price', [
            $request->input('price_start') ?? 0,
            $request->input('price_end') ?? PHP_INT_MAX
        ]);

        if ($request->input('category_id')) {
            $itemQuery->whereIn('category_id', $request->input('category_id'));
        }

        if ($request->input('sort_by')) {
            [$column, $direction] = explode('-', $request->input('sort_by'));
            $itemQuery->orderBy($column, $direction);
        }

        if ($request->input('sale_type')) {
            $itemQuery->where('sale_type', $request->input('sale_type'));
        }

        $items = $itemQuery->paginate(15);

        if ($items->isEmpty()) {
            $canNotFind = true;
            $items = $shop->items()->paginate(15);
        }

        return view('shop.show', [
            'shop' => $shop,
            'items' => $items,
            'categories' => $categories,
            'canNotFind' => $canNotFind,
            'sortList' => $sortList,
            'saleTypes' => $saleTypes
        ]);
    }

    /**
     * Show detail of item.
     *
     * @param Shop $shop
     * @param ShopItem $item
     * @param string $slug
     * @return Response
     */
    public function item(Shop $shop, ShopItem $item, string $slug)
    {
        if (!$shop->is_active) {
            abort(404);
        }

        if ($slug !== $item->slug) {
            return redirect()->route('shop.item', [$item, $item->slug]);
        }

        return view('shop.item.show', [
            'shop' => $shop,
            'item' => $item
        ]);
    }

    /**
     * Return view for cart.
     *
     * @param Cart $cart
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function cartShow(Cart $cart)
    {
        return view('shop.cart.show', [
            'cart' => $cart
        ]);
    }

    /**
     * Store item into cart.
     *
     * @param Request $request
     * @param ShopItem $item
     * @param Cart $cart
     * @return Response
     */
    public function cartStore(Request $request, ShopItem $item, Cart $cart)
    {
        if (!$item->shop->is_active) {
            abort(404);
        }

        if ($request->input('quantity') > 1) {
            $item->quantity = $request->input('quantity');
        }

        $cart->addItem($item);

        return redirect()->route('shop.cart');
    }

    /**
     * Destroy item from cart.
     *
     * @param Request $request
     * @param ShopItem $item
     * @param Cart $cart
     * @return Response
     */
    public function cartUpdate(Request $request, ShopItem $item, Cart $cart)
    {
        $cart->updateQuantity($item, $request->input('direction'));

        return redirect()->route('shop.cart');
    }

    /**
     * Destroy item from cart.
     *
     * @param ShopItem $item
     * @param Cart $cart
     * @return Response
     */
    public function cartDestroy(ShopItem $item, Cart $cart)
    {
        $cart->removeItem($item);

        return redirect()->route('shop.cart');
    }

    /**
     * Destroy item from cart.
     *
     * @param Request $request
     * @param Cart $cart
     * @return Response
     */
    public function cartBuy(Request $request, Cart $cart)
    {
        /** @var User $user */
        $user = auth()->user();

        if ($cart->isNotEmpty()) {
            if ($user->characters->isNotEmpty()) {
                /** @var Character $character */
                $character = Character::query()->find($request->input('character'));
                if ($character && $character->is_valid) {
                    if ($user->vote_point >= $cart->getTotalTtlVotePrice() && $user->cash_point >= $cart->getTotalTtlCsPrice()) {
                        $cart->items->each(function (ShopItem $item) use ($character) {
                            $character->sendItem($item->item_id, $item->quantity, true);
                        });

                        $user->vote_point -= $cart->getTotalTtlVotePrice();
                        $user->cash_point -= $cart->getTotalTtlCsPrice();
                        $user->save();

                        UserLog::buyShop($request, $cart);

                        $cart->clear();

                        session()->flash('success', trans('shop.cart.success', ['name' => $character->m_szName]));
                    } else {
                        session()->flash('error', trans('shop.cart.error.insufficient_balance'));
                    }
                } else {
                    session()->flash('error', trans('shop.cart.error.char_not_found'));
                }
            } else {
                session()->flash('error', trans('shop.cart.no_chars'));
            }
        } else {
            session()->flash('error', trans('shop.cart.error.empty_cart'));
        }

        return redirect()->route('shop.cart');
    }
}
