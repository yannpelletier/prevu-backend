<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest\UserUpdateRequest;
use App\Http\Resources\SaleResource;
use App\Http\Resources\StoreResource;
use App\Http\Resources\UserResource;
use App\Services\StripeHelper;
use App\Store;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return UserResource::make($user);
    }

    public function getStripeLink()
    {
        $user = Auth::user();
        return response()->json(['link' => StripeHelper::getConnectionLink($user)]);
    }

    public function addStripeAccount(Request $request)
    {
        $request->validate([
            'stripe_authorization_code' => 'required',
        ]);
        $user = Auth::user();
        StripeHelper::addStripeAccount($user, $request->get('stripe_authorization_code'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return Authenticatable|null
     */
    public function update(UserUpdateRequest $request)
    {
        $validated = $request->validated();

        $user = Auth::user();
        $user->fill($validated);
        $user->save();

        return $user;
    }

    public function getSellerProgression()
    {
        $user = Auth::user();

        return [
            'added_products' => $user->products()->count() > 0,
            'created_store' => $user->store()->count() > 0,
            'completed_payouts_setup' => $user->confirmed
        ];
    }

    public function getStore()
    {
        $user = Auth::user();
        $store = Store::where('user_id', $user->id)->firstOrFail();
        return StoreResource::make($store);
    }

    public function getSales(Request $request)
    {
        return SaleResource::collection(Auth::user()->sales);
    }

    public function getAnalytics()
    {
        $user = Auth::user();
        return $user->analytics;
    }
}
