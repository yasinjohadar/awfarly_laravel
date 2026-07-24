<?php

namespace App\Http\Controllers\API\Advertisers\Subscriptions\Payments;

use App\Http\Controllers\Controller;
use App\Models\Subscriptions\Packages\Package;
use App\Models\Subscriptions\Payments\Google\GooglePurchases;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Imdhemy\Purchases\Events\AppStore\InitialBuy;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionPurchased;
use Imdhemy\Purchases\Events\GooglePlay\SubscriptionRenewed;
use Imdhemy\Purchases\Facades\Product;
use Imdhemy\Purchases\Facades\Subscription;
use Log;

class SubscriptionsPurchasedController extends Controller
{
    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     * @throws GuzzleException
     */
    public function addPurchase(Request $request)
    {
        $data = $request->only([
            'deviceOS',
            'verificationData',
            'productId',
            'purchaseID',
            'identifier',
            'transactionDate',
            'status',
            'source',
        ]);
        $this->apiValidate($data, [
            'deviceOS' => ['required', 'in:Android,IOS'],
            'productId' => ['required', 'exists:packages,product_id'],
            'purchaseID' => ['nullable'],
            'identifier' => ['nullable'],
            'transactionDate' => ['nullable'],
            'status' => ['nullable'],
            'source' => ['nullable'],
        ]);
        $name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';
        $has_package = Auth::guard('advertiser-api')->user()
            ->packages()
            ->where('is_ended', false)
            ->where('is_current', true)
            ->where('is_active', true)
            ->exists();
        if ($has_package) {
            return $this->apiResponse(['message' => __('api/advertisers/subscriptions/packages/packages.has-subscription')]);
        }
        DB::beginTransaction();
        try {
            if ($data['deviceOS'] === 'IOS') {
                $this->apiValidate($data, [
                    'verificationData' => ['required', 'string'],
                ]);
                $receiptResponse = Subscription::appStore()->receiptData($data['verificationData'])->verifyReceipt();
                $status = $receiptResponse->getStatus();
                if ($status->isValid()) {
                    $latest_receipt = $receiptResponse->getLatestReceiptInfo();
                    $purchase_date = $latest_receipt[0]->getPurchaseDate();
                    $expiry_date = Carbon::make($latest_receipt[0]->getExpiresDate()->toDateTime());
                    $transaction_id = $latest_receipt[0]->getOriginalTransactionId();
                    $sameHour = Carbon::make($purchase_date->toDateTime())->isSameHour();
                    if ($sameHour) {
                        $package_exists = Auth::guard('advertiser-api')->user()
                            ->packages()
                            ->select('advertiser_packages.*')
                            ->join('packages', function ($q) use ($data) {
                                $q->on('packages.id', 'advertiser_packages.package_id')
                                    ->where('packages.product_id', $data['productId']);
                            })
                            ->where('advertiser_packages.unique_identifier', $transaction_id)
                            ->where('advertiser_packages.is_current', true)
                            ->where('advertiser_packages.is_active', true)
                            ->first();
                        if (!$package_exists) {
                            $package = Package::where('product_id', $data['productId'])
                                ->first();

                            Auth::guard('advertiser-api')->user()
                                ->packages()
                                ->create([
                                    'unique_identifier' => $transaction_id,
                                    'receipt_data' => $data['verificationData'],
                                    'package_id' => $package->id,
                                    'starts_at' => $purchase_date->toDateTime(),
                                    'ends_at' => $expiry_date,
                                    'is_ended' => false,
                                    'is_current' => true,
                                    'is_active' => true,
                                ]);

                            Auth::guard('advertiser-api')->user()
                                ->update([
                                    'is_elite' => true,
                                    'allowed_posts_count' => $package->maximum_posts,
                                    'allowed_offers_count' => $package->maximum_offers,
                                ]);
                            Auth::guard('advertiser-api')->user()->deposit($package->maximum_points);

                            $name = $package->{$name_column};
                            $message = __('api/advertisers/subscriptions/packages/packages.package_purchased', ['name' => $name]);
                        } else {
                            $message = __('api/advertisers/subscriptions/packages/packages.already-subscribed');
                        }
                    } else {
                        $message = __('api/advertisers/subscriptions/packages/packages.old-transaction');
                    }
                } else {
                    return $this->apiBadRequestResponse(__('api/advertisers/subscriptions/packages/packages.transaction-failed'));
                }
            } else {

                if ($data['productId'] && $data['identifier']) {

                    /*$google_purchase = GooglePurchases::query()
                        ->where('unique_identifier', $data['identifier'])
                        ->where('product_id', $data['productId'])
                        ->whereBetween('created_at', [
                            Carbon::today()->startOfDay(),
                            Carbon::today()->endOfDay()
                        ])
                        ->first();*/
                    /*$receipt = Product::googlePlay()->id($data['productId'])->token($data['identifier'])->get();*/


                    $purchase_date = now();
                    $package = Package::where('product_id', $data['productId'])
                        ->first();

                    if ($package->subscription_type === 'daily') {
                        $ends_at = now()->addDays($package->duration);
                    } else if ($package->subscription_type === 'weekly') {
                        $ends_at = now()->addWeeks($package->duration);
                    } else if (in_array($package->subscription_type, ['monthly', 'two_months', 'three_months', 'six_months'])) {
                        $ends_at = now()->addMonths($package->duration);
                    } else {
                        $ends_at = now()->addYears($package->duration);
                    }

                    Auth::guard('advertiser-api')->user()
                        ->packages()
                        ->create([
                            'unique_identifier' => $data['identifier'],
                            'receipt_data' => $data['identifier'],
                            'package_id' => $package->id,
                            'starts_at' => $purchase_date,
                            'ends_at' => $ends_at,
                            'is_ended' => false,
                            'is_current' => true,
                            'is_active' => true,
                        ]);

                    Auth::guard('advertiser-api')->user()
                        ->update([
                            'is_elite' => true,
                            'allowed_posts_count' => $package->maximum_posts,
                            'allowed_offers_count' => $package->maximum_offers,
                        ]);
                    Auth::guard('advertiser-api')->user()->deposit($package->maximum_points);

                    /*Product::googlePlay()->id($data['productId'])->token($data['identifier'])->acknowledge();*/
                    $name = $package->{$name_column};
                    $message = __('api/advertisers/subscriptions/packages/packages.package_purchased', ['name' => $name]);

                } else {
                    $message = __('api/advertisers/subscriptions/packages/packages.transaction-failed');
                }
            }
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->apiExceptionResponse([__('api/advertisers/subscriptions/packages/packages.something-wrong'), $exception->getMessage()]);
        }
        DB::commit();
        return $this->apiResponse([
            'message' => $message
        ]);
    }

    /**
     * @param InitialBuy $event
     */
    public function handle(InitialBuy $event)
    {
        // The following data can be retrieved from the event
        $notification = $event->getServerNotification();
        $subscription = $notification->getSubscription();
        $provider = $subscription->getProvider();
        $uniqueIdentifier = $subscription->getUniqueIdentifier();
        $expirationTime = $subscription->getExpiryTime();
        $item_id = $subscription->getItemId();

        Log::debug('apple purchases', [
            'event' => $event,
            'subscription' => $subscription,
            'provider' => $provider,
            'uniqueIdentifier' => $uniqueIdentifier,
            'expirationTime' => $expirationTime,
            'item_id' => $item_id,
        ]);

        /*$name_column = App::currentLocale() === 'ar' ? 'name_ar' : 'name_en';
        DB::beginTransaction();
        try {

        } catch (Exception $e){
            DB::rollBack();
        }
        DB::commit();*/
    }
}
