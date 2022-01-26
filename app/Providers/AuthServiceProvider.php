<?php

namespace App\Providers;

use App\Asset;
use App\Policies\AssetPolicy;
use App\Policies\PurchasePolicy;
use App\Policies\StorePolicy;
use App\Policies\WatermarkPolicy;
use App\Product;
use App\Purchase;
use App\Store;
use App\Watermark;
use Laravel\Passport\Passport;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Purchase::class => PurchasePolicy::class,
        Asset::class => AssetPolicy::class,
        Watermark::class => WatermarkPolicy::class,
        Store::class => StorePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes();
    }
}
