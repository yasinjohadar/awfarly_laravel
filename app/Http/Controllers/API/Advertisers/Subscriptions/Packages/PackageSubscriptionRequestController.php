<?php

namespace App\Http\Controllers\API\Advertisers\Subscriptions\Packages;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use App\Models\Subscriptions\Packages\Package;
use App\Models\Subscriptions\Packages\PackageSubscriptionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PackageSubscriptionRequestController extends Controller
{
    /**
     * Shared payment details for all packages.
     */
    public function paymentInfo()
    {
        $qr = Settings::Get('payment.qr_image', '');

        if (is_string($qr) && $qr !== '' && !Str::startsWith($qr, ['http://', 'https://'])) {
            if (Str::startsWith($qr, 'uploads/')) {
                $qr = url('/image/' . $qr);
            } else {
                $qr = url('/' . ltrim($qr, '/'));
            }
        }

        return $this->apiResponse([
            'whatsapp' => Settings::Get('payment.whatsapp', '963900000000'),
            'code' => Settings::Get('payment.code', 'AWFARLY-PAY-TEST-0000-0000-0000'),
            'qrImageUrl' => $qr ?: null,
            'instructions' => Settings::Get(
                'payment.instructions',
                'ادفع باستخدام الرمز أو مسح الـ QR، ثم أرسل وصل الدفع عبر واتساب، واضغط تأكيد الاشتراك.'
            ),
        ]);
    }

    /**
     * Create a pending subscription request for admin review.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'packageId' => ['required', 'exists:packages,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $advertiser = Auth::guard('advertiser-api')->user();

        $package = Package::where('id', $data['packageId'])
            ->where('is_active', true)
            ->where('is_visible', true)
            ->first();

        if (!$package) {
            return $this->apiBadRequestResponse(
                __('api/advertisers/subscriptions/packages/packages.wrong-id')
            );
        }

        $existing = PackageSubscriptionRequest::where('advertiser_id', $advertiser->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return $this->apiBadRequestResponse(
                __('api/advertisers/subscriptions/packages/packages.pending-request-exists')
            );
        }

        $receiptPath = $request->hasFile('receipt')
            ? $request->file('receipt')->store('uploads/subscriptions/receipts', 'local')
            : null;

        $subscriptionRequest = PackageSubscriptionRequest::create([
            'advertiser_id' => $advertiser->id,
            'package_id' => $package->id,
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
            'receipt' => $receiptPath,
        ]);

        return $this->apiResponse([
            'message' => __('api/advertisers/subscriptions/packages/packages.request-sent'),
            'data' => [
                'id' => $subscriptionRequest->id,
                'status' => $subscriptionRequest->status,
                'packageId' => $subscriptionRequest->package_id,
            ],
        ]);
    }
}
