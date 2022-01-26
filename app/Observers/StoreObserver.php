<?php


namespace App\Observers;


use App\Store;
use Illuminate\Support\Str;

class StoreObserver
{
    /**
     * Handle the product "created" event.
     *
     * @param  \App\Product  $product
     * @return void
     */
    public function created(Store $store)
    {
        // The slug must start with a letter to avoid collisions with store ids.
        // $store->slug = chr(rand(97,122)) . Str::random(8);
        // $store->save();
    }
}
