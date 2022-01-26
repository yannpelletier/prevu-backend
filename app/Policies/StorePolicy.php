<?php

namespace App\Policies;

use App\Store;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StorePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the product.
     *
     * @param \App\User $user
     * @param Store $store
     * @return mixed
     */
    public function update(User $user, Store $store)
    {
        return $user->id === $store->user_id;
    }
}
