<?php

namespace Tests\Feature;

use App\Product;
use App\Purchase;

class PurchaseTest extends FeatureTestCase
{
    // TODO: Test that purchase fails with unauthenticated user

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_make_purchase_multiple_products_valid()
    {
        $this->confirmSellerAccount($this->sellers[0]);
        $productIds = $this->createProductIds($this->sellers[0], 5);

        $purchaseResponse = $this->buyProductsById($this->buyers[0], $productIds);

        $purchaseResponse
            ->assertStatus(200)
            ->assertJsonStructure([
                'charge_id'
            ]);

        $chargeId = $purchaseResponse->decodeResponseJson()['charge_id'];

        $purchases = Purchase::where('charge_id', $chargeId)->get();

        // Access the originals
        foreach ($purchases as $purchase) {
            $productOriginalResponse = $this->actingAs($this->buyers[0])->get("/api/purchases/{$purchase->id}/original");

            $productOriginalResponse
                ->assertStatus(200)
                ->assertHeader('content-type', 'image/jpeg');
        }
    }

    public function test_make_purchase_unconfirmed_seller_invalid()
    {
        $productIds = $this->createProductIds($this->sellers[0], 5);

        $purchaseResponse = $this->buyProductsById($this->buyers[0], $productIds);

        $purchaseResponse
            ->assertStatus(400)
            ->assertJson(['message' => trans('exceptions.purchases.seller_not_confirmed')]);
    }

    public function test_make_purchase_already_bought_products_invalid()
    {
        $this->confirmSellerAccount($this->sellers[0]);
        $productIds = $this->createProductIds($this->sellers[0], 5);

        $this->buyProductsById($this->buyers[0], $productIds);
        $secondPurchaseResponse = $this->buyProductsById($this->buyers[0], $productIds);

        $secondPurchaseResponse
            ->assertStatus(400)
            ->assertJson(['message' => trans('exceptions.purchases.already_bought_product')]);

    }

    public function test_make_purchase_deleted_products_invalid()
    {
        $productIds = $this->createProductIds($this->sellers[0], 5);
        $product = Product::findOrFail($productIds[0]);
        $product->delete();

        $purchaseResponse = $this->buyProductsById($this->buyers[0], $productIds);

        $purchaseResponse
            ->assertStatus(400)
            ->assertJson(['message' => trans('exceptions.purchases.buy_products')]);
    }

    public function test_make_purchase_as_seller_invalid()
    {
        $this->confirmSellerAccount($this->sellers[0]);
        $productIds = $this->createProductIds($this->sellers[0], 5);

        $purchaseResponse = $this->buyProductsById($this->sellers[0], $productIds);

        $purchaseResponse
            ->assertStatus(400)
            ->assertJson(['message' => trans('exceptions.purchases.buy_own_products')]);

    }

    public function test_make_purchase_nonexistent_product_invalid()
    {
        $this->confirmSellerAccount($this->sellers[0]);

        $purchaseResponse = $this->buyProductsById($this->buyers[0], [1000000]);

        $purchaseResponse
            ->assertStatus(400)
            ->assertJson(['message' => trans('exceptions.purchases.buy_products')]);
    }

    public function test_make_purchase_payment_declined_invalid()
    {
        $this->confirmSellerAccount($this->sellers[0]);
        $productIds = $this->createProductIds($this->sellers[0], 5);

        $purchaseResponse = $this->buyProductsById($this->buyers[0], $productIds, 'tok_chargeDeclined');

        $purchaseResponse
            ->assertStatus(400)
            ->assertJson(['message' => trans('exceptions.purchases.card_declined')]);
    }

    public function test_access_original_unauthorized_user_invalid()
    {
        $this->confirmSellerAccount($this->sellers[0]);
        $productIds = $this->createProductIds($this->sellers[0], 5);

        $chargeId = $this->buyProductsById($this->buyers[0], $productIds)->decodeResponseJson()['charge_id'];

        $purchases = Purchase::where('charge_id', $chargeId)->get();

        // Try to access the originals as an authorized user
        foreach ($purchases as $purchase) {
            $productOriginalResponse = $this->actingAs($this->unauthorizedUsers[0])->get("/api/purchases/{$purchase->id}/original");

            $productOriginalResponse
                ->assertStatus(403)
                ->assertJson(['message' => trans('exceptions.access_denied')]);
        }
    }

    public function test_download_zip_valid()
    {
        $this->confirmSellerAccount($this->sellers[0]);
        $productIds = $this->createProductIds($this->sellers[0], 8);

        $chargeId = $this->buyProductsById($this->buyers[0], $productIds)->decodeResponseJson()['charge_id'];

        $purchases = Purchase::where('charge_id', $chargeId)->get();
        $purchaseIds = [];

        foreach ($purchases as $purchase) {
            array_push($purchaseIds, $purchase->id);
        }

        $downloadZipRequest = $this->actingAs($this->buyers[0])->json('GET', '/api/purchases/zip', [
            'ids' => $purchaseIds
        ]);

        $downloadZipRequest
            ->assertStatus(200)
            ->assertHeader('content-type', 'application/zip');
    }

    public function test_download_zip_unauthorized_user_invalid()
    {
        $this->confirmSellerAccount($this->sellers[0]);
        $productIds = $this->createProductIds($this->sellers[0], 8);

        $chargeId = $this->buyProductsById($this->buyers[0], $productIds)->decodeResponseJson()['charge_id'];

        $purchases = Purchase::where('charge_id', $chargeId)->get();
        $purchaseIds = [];

        foreach ($purchases as $purchase) {
            array_push($purchaseIds, $purchase->id);
        }

        $downloadZipRequest = $this->actingAs($this->unauthorizedUsers[0])->json('GET', '/api/purchases/zip', [
            'ids' => $purchaseIds
        ]);

        $downloadZipRequest
            ->assertStatus(403)
            ->assertJson(['message' => trans('exceptions.access_denied')]);
    }

}
