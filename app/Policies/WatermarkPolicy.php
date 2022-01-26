<?php

namespace App\Policies;

use App\User;
use App\Watermark;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\HandlesAuthorization;

class WatermarkPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create assets.
     *
     * @param  User  $user
     * @return mixed
     */
    public function store(User $user)
    {
        return $user === Auth::user();
    }

    /**
     * Determine whether the user can update the product.
     *
     * @param \App\User $user
     * @param Watermark $watermark
     * @return mixed
     */
    public function update(User $user, Watermark $watermark)
    {
        return $user->id === $watermark->user_id;
    }

    /**
     * Determine whether the user can delete the asset.
     *
     * @param User $user
     * @param Watermark $watermark
     * @return mixed
     */
    public function destroy(User $user, Watermark $watermark)
    {
        return $user->id === $watermark->user_id;
    }
}
