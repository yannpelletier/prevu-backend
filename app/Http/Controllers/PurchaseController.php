<?php

namespace App\Http\Controllers;

use App\Events\ProductPurchased;
use App\Http\Requests\PurchaseRequest\PurchaseShowOriginalRequest;
use App\Http\Requests\PurchaseRequest\PurchaseStoreRequest;
use App\Http\Requests\PurchaseRequest\PurchaseZipRequest;
use App\Http\Resources\PurchaseResource;
use App\Product;
use App\Purchase;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

class PurchaseController extends Controller
{
    /**
     * Lists all the approved product purchases in a JSON list.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $user = Auth::user();
        $purchases = $user->purchases()->where('approved', 1)->get();
        return PurchaseResource::collection($purchases);
    }

    /**
     * Lets the current user buy multiple products at one from a single seller.
     *
     * @param Request $request Contains the product IDs and Stripe card token
     * @return \Illuminate\Http\JsonResponse The charge ID issued by Stripe
     */
    public function store(PurchaseStoreRequest $request)
    {
        $validated = $request->validated();
        $cardToken = $validated['card_token'];
        $productIds = $validated['product_ids'];

        $products = Product::findMany($productIds);
        $buyer = Auth::user();
        $purchases = [];
        $total = 0;

        // Validates that there are no duplicates or invalid product ids
        if (count($products) !== count($productIds)) {
            abort(400, trans('exceptions.purchases.buy_products'));
        }

        $seller = $products[0]->user;
        $currency = $products[0]->currency;
        foreach ($products as $product) {
            if ($product->user_id !== $seller->id) {
                abort(400, trans('exceptions.purchases.same_seller'));
            }
            if ($product->currency !== $currency) {
                abort(400, trans('exceptions.purchases.different_currency'));
            }
            if ($buyer->products->contains($product->id)) {
                abort(400, trans('exceptions.purchases.buy_own_products'));
            }

            if ($buyer->purchases()->where('product_id', $product->id)->where('approved', true)->first()) {
                abort(400, trans('exceptions.purchases.already_bought_product'));
            }
            $total += $product->price;
        }

        $applicationFees = config('constants.fees.transaction') * $total;
        $chargeId = $this->handleTransaction($total, $applicationFees, $currency, $cardToken, $seller);

        foreach ($products as $product) {
            $purchases[] = [
                'seller_id' => $product->user_id,
                'product_id' => $product->id,
                'name' => $product->name,
                'extension' => $product->extension,
                'description' => $product->description,
                'price' => $product->price,
                'currency' => $product->currency,
                'private_file_id' => $product->private_file_id,
                'charge_id' => $chargeId,
                'approved' => true,
            ];
        }
        $buyer->purchases()->createMany($purchases);

        event(new ProductPurchased($seller, $buyer, $products));

        return response()->json(['charge_id' => $chargeId]);
    }

    /**
     * Sends a payment using a provided credit card token to the seller's
     * Stripe account. The function will abort if any exception is raised.
     *
     * @param int $amount Amount in cents
     * @param int $applicationFees Fee percentage in cents
     * @param string $currency Currency
     * @param string $cardToken Stripe card token
     * @param User $seller Seller User object
     * @return string The charge ID issued by Stripe
     */
    private function handleTransaction(int $amount, int $applicationFees, string $currency, string $cardToken, User $seller): string
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret_key'));
        if ($seller->stripe_connect_id === '') {
            abort(400, trans('exceptions.purchases.seller_not_confirmed'));
        }
        try {
            $chargeInfo = [
                'amount' => ceil($amount),
                'currency' => $currency,
                'source' => $cardToken,
                'application_fee_amount' => ceil($applicationFees)
            ];
            $receiver = ["stripe_account" => $seller->stripe_connect_id];
            $charge = \Stripe\Charge::create($chargeInfo, $receiver);
            return $charge->id;
        } catch
        (\Stripe\Exception\CardException $e) {
            abort(400, trans('exceptions.purchases.card_declined'));
        } catch (\Stripe\Exception\RateLimitException $e) {
            abort(400, trans('exceptions.purchases.rate_limit'));
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            Log::error($e->getMessage());
            abort(400, trans('exceptions.invalid_request'));
        } catch (\Stripe\Exception\AuthenticationException $e) {
            abort(400, trans('exceptions.authentication'));
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            abort(400, trans('exceptions.internal_custom', ['code' => '73483']));
        } catch (\Stripe\Exception\ApiErrorException $e) {
            abort(400, trans('exceptions.internal_custom', ['code' => '73484']));
        } catch (\Exception $e) {
            abort(400, trans('exceptions.internal_custom', ['code' => '73485']));
        }
    }

    public function downloadZip(PurchaseZipRequest $request)
    {
        $purchaseIds = $request->get('ids');
        $purchases = Purchase::findMany($purchaseIds);

        $zipFilePath = Storage::disk('temporary')->path('/') . uniqid() . '.zip';
        $zip = new Filesystem(new ZipArchiveAdapter($zipFilePath));
        foreach ($purchases as $purchase) {
            $zip->put($purchase->fileName, Storage::get($purchase->getMainStoragePath()));
        }
        $zip->getAdapter()->getArchive()->close();

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    public function downloadOriginal(PurchaseShowOriginalRequest $request, Purchase $purchase)
    {
        return Storage::download($purchase->getMainStoragePath(), $purchase->fileName);
    }

    public function showOriginal(PurchaseShowOriginalRequest $request, Purchase $purchase)
    {
        return Storage::response($purchase->getMainStoragePath());
    }

    public function showThumbnail(PurchaseShowOriginalRequest $request, Purchase $purchase)
    {
        return Storage::response($purchase->getDirectoryStoragePath('private_thumbnails'));
    }
}
