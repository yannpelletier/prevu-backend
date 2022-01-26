<?php

namespace App\Providers;

use App\Asset;
use App\Observers\AssetObserver;
use App\Observers\ProductObserver;
use App\Observers\StoreObserver;
use App\Observers\UserObserver;
use App\Observers\WatermarkObserver;
use App\Product;
use App\Store;
use App\User;
use App\Watermark;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Resource::withoutWrapping();
        Schema::defaultStringLength(191);
    }

    private function registerObservers()
    {
        User::observe(UserObserver::class);
        Store::observe(StoreObserver::class);
        Product::observe(ProductObserver::class);
        Asset::observe(AssetObserver::class);
        Watermark::observe(WatermarkObserver::class);
    }

    private function registerValidationRules()
    {
        Validator::extend('rgb', function ($attribute, $value, $parameters, $validator) {
            return preg_match(
                    '/^rgb\([0-9]{1,3},\s?[0-9]{1,3},\s?[0-9]{1,3}\)$/', $value
                ) === 1;
        });
        Validator::extend('rgba', function ($attribute, $value, $parameters, $validator) {
            return preg_match(
                    '/^rgba\([0-9]{1,3},\s?[0-9]{1,3},\s?[0-9]{1,3},\s?[01](\.[0-9]+)?\)$/', $value
                ) === 1;
        });
        Validator::extend('asset', function ($attribute, $value, $parameters, $validator) {
            return Asset::find($value) !== null;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerObservers();
        $this->registerValidationRules();
    }
}
