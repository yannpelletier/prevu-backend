<?php

namespace App\Policies;

use App\Asset;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class AssetPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create assets.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function store(User $user)
    {
        return $user === Auth::user();
    }

    /**
     * Determine whether the user can delete the asset.
     *
     * @param  \App\User  $user
     * @param  \App\Asset  $asset
     * @return mixed
     */
    public function destroy(User $user, Asset $asset)
    {
        return $user->id === $asset->user_id;
    }
}
