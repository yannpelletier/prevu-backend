<?php

namespace Tests\Feature;

use App\Purchase;
use Carbon\Carbon;

class UserTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_get_sales_valid()
    {
        $this->confirmSellerAccount($this->sellers[0]);
        $productIds = $this->createProductIds($this->sellers[0], 5);
        $this->buyProductsById($this->buyers[0], $productIds);
        $purchases = [];
        foreach ($productIds as $productId) {
            $purchases[] = Purchase::where('product_id', $productId)->first();
        }

        $response = $this->actingAs($this->sellers[0])->get('/api/users/me/sales');
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $purchases[0]->id])
            ->assertJsonFragment(['id' => $purchases[1]->id])
            ->assertJsonFragment(['id' => $purchases[2]->id])
            ->assertJsonFragment(['id' => $purchases[3]->id])
            ->assertJsonFragment(['id' => $purchases[4]->id]);
    }

    public function test_get_seller_progression_valid()
    {
        $this->actingAs($this->sellers[0])->get('/api/users/me/seller-progression')
            ->assertStatus(200)
            ->assertJson(
                [
                    'added_products' => false,
                    'created_store' => false,
                    'completed_payouts_setup' => false
                ]
            );
        $this->confirmSellerAccount($this->sellers[0]);

        $this->actingAs($this->sellers[0])->get('/api/users/me/seller-progression')
            ->assertStatus(200)
            ->assertJson(
                [
                    'added_products' => false,
                    'created_store' => false,
                    'completed_payouts_setup' => true
                ]
            );

        $this->createProductIds($this->sellers[0], 1);

        $this->actingAs($this->sellers[0])->get('/api/users/me/seller-progression')
            ->assertStatus(200)
            ->assertJson(
                [
                    'added_products' => true,
                    'created_store' => false,
                    'completed_payouts_setup' => true
                ]
            );

        $this->getNewStore($this->sellers[0]);

        $this->actingAs($this->sellers[0])->get('/api/users/me/seller-progression')
            ->assertStatus(200)
            ->assertJson(
                [
                    'added_products' => true,
                    'created_store' => true,
                    'completed_payouts_setup' => true
                ]
            );
    }

    public function test_get_analytics_valid()
    {

        $this->confirmSellerAccount($this->sellers[0]);
        $productIds = $this->createProductIds($this->sellers[0], 8);
        $this->buyProductsById($this->buyers[0], $productIds);
        $purchases = [];
        foreach ($productIds as $productId) {
            $purchases[] = Purchase::where('product_id', $productId)->first();
        }

        $purchases[0]->created_at = Carbon::today()->subDays(400)->toDateString();
        $purchases[0]->price = 599;
        $purchases[0]->save();
        $purchases[1]->created_at = Carbon::today()->subDays(90)->toDateString();
        $purchases[1]->price = 780;
        $purchases[1]->save();
        $purchases[2]->created_at = Carbon::today()->subDays(28)->toDateString();
        $purchases[2]->price = 1000000;
        $purchases[2]->save();
        $purchases[3]->created_at = Carbon::today()->subDays(14)->toDateString();
        $purchases[3]->price = 1000;
        $purchases[3]->save();
        $purchases[4]->created_at = Carbon::today()->subDays(7)->toDateString();
        $purchases[4]->price = 1000;
        $purchases[4]->save();
        $purchases[5]->created_at = Carbon::today()->subDays(1)->toDateString();
        $purchases[5]->price = 1000;
        $purchases[5]->save();
        $purchases[6]->created_at = Carbon::today()->subDays(1)->toDateString();
        $purchases[6]->price = 1000;
        $purchases[6]->save();
        $purchases[7]->created_at = Carbon::now()->toDateString();
        $purchases[7]->price = 1000;
        $purchases[7]->save();


        $response = $this->actingAs($this->sellers[0])->json('GET', '/api/users/me/analytics');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'gross_volume' => [
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 780, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 1000000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1000, 0, 0, 0, 0, 0, 0, 1000, 0, 0, 0, 0, 0,
                    2000, 1000
                ]])
            ->assertJsonFragment(
                ['items_sold' => [
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                    0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0,
                    2, 1
                ],
                ]);

    }
}
