<?php

namespace Tests\Feature;

use App\Store;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;


class FeatureTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array
     */
    protected $sellers;
    /**
     * @var array
     */
    protected $buyers;
    /**
     * @var array
     */
    protected $unauthorizedUsers;

    protected const SELLER_CONNECT_ID = "acct_1FRtCjDQY5rNzOBX";
    public $mockConsoleOutput = false;

    public static function setUpBeforeClass(): void
    {
    }

    protected function setUp(): void
    {
        parent::setUp();

        $clientRepository = new ClientRepository();
        $client = $clientRepository->createPersonalAccessClient(
            null, 'Test Personal Access Client', "http://localhost"
        );

        DB::table('oauth_personal_access_clients')->insert([
            'client_id' => $client->id,
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ]);
        Storage::fake('assets');
        Storage::fake('temporary');
        Storage::fake('local');
        Storage::fake('s3');

        Mail::fake();
        Notification::fake();

        Queue::fake();

        $this->sellers = [
            factory(User::class)->create(),
            factory(User::class)->create(),
        ];
        $this->buyers = [
            factory(User::class)->create(),
            factory(User::class)->create(),
        ];
        $this->unauthorizedUsers = [
            factory(User::class)->create(),
            factory(User::class)->create(),
        ];
        foreach (array_merge($this->sellers, $this->buyers, $this->unauthorizedUsers) as $user) {
            $user->createToken('MyApp', []);
        }
    }

    protected function tearDown(): void
    {
        /*
        foreach ($this->sellers as $seller) {
            $seller->products()->delete();
            $seller->store()->delete();
            $seller->delete();
        }
        foreach ($this->buyers as $buyer) {
            $buyer->purchases()->delete();
            $buyer->delete();
        }
        foreach ($this->unauthorizedUsers as $unauthorizedUser) {
            $unauthorizedUser->delete();
        }*/
    }

    /**
     * Links a stripe account to a user in order to be able to receive payments.
     *
     * @param $user - The user with an unconfirmed seller account.
     */
    protected function confirmSellerAccount(User $user): void
    {
        $user->stripe_connect_id = self::SELLER_CONNECT_ID;
        $user->save();
    }

    protected function createProductIds(User $seller, int $productCount): array
    {
        $productIds = [];

        for ($i = 0; $i < $productCount; $i++) {
            $file = UploadedFile::fake()->image("test-picture-$i.jpg");

            $response = $this->actingAs($seller)->json('POST', '/api/products', [
                'file' => $file
            ]);

            array_push($productIds, (int)$response->decodeResponseJson()['id']);
        }

        return $productIds;
    }

    protected function buyProductsById(User $user, array $productIds, $cardToken = 'tok_visa'): TestResponse
    {
        return $this->actingAs($user)->json('POST', '/api/purchases', [
            'card_token' => $cardToken,
            'product_ids' => $productIds
        ]);
    }

    protected function getNewStore(User $user, string $slug = 'valid_slug'): Store
    {
        $storeCreationResponse = $this->actingAs($user)->post('/api/stores', ['slug' => $slug]);

        return Store::findOrFail($storeCreationResponse->decodeResponseJson()['id']);
    }
}
