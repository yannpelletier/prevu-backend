<?php

namespace App\Policies;

use App\Product;
use App\Purchase;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class PurchasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create products.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function store(User $user)
    {
        return $user === Auth::user();
    }

    /**
     * Determine whether the user can view the original of the file
     *
     * @param \App\User $user
     * @param Purchase $purchase
     * @return mixed
     */
    public function showOriginal(User $user, Purchase $purchase){
        return $user->id === $purchase->buyer_id;
    }
}
