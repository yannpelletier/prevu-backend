<?php

namespace App\Policies;

use App\Product;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class ProductPolicy
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
     * Determine whether the user can update the product.
     *
     * @param  \App\User  $user
     * @param  \App\Product  $product
     * @return mixed
     */
    public function update(User $user, Product $product)
    {
        return $user->id === $product->user_id;
    }

    /**
     * Determine whether the user can destroy the product.
     *
     * @param  \App\User  $user
     * @param  \App\Product  $product
     * @return mixed
     */
    public function destroy(User $user, Product $product)
    {
        return $user->id === $product->user_id;
    }

    /**
     * Determine whether the user can view the original of the file
     *
     * @param  \App\User  $user
     * @param  \App\Product  $product
     * @return mixed
     */
    public function showOriginal(User $user, Product $product){
        return $user->id === $product->user_id;
    }
}
