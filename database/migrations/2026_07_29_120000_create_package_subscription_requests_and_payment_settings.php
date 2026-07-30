<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePackageSubscriptionRequestsAndPaymentSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_subscription_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id');
            $table->unsignedBigInteger('package_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('advertiser_id')
                ->references('id')
                ->on('advertisers_users')
                ->onDelete('cascade');

            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
                ->onDelete('cascade');

            $table->index(['advertiser_id', 'status']);
        });

        $now = now();
        $settings = [
            [
                'name' => 'Payment WhatsApp',
                'key' => 'payment.whatsapp',
                'type' => 'payment',
                'value' => '963900000000',
                'value_type' => 'string',
                'description' => 'WhatsApp number for receiving payment receipts (digits only with country code).',
            ],
            [
                'name' => 'Payment Code',
                'key' => 'payment.code',
                'type' => 'payment',
                'value' => 'AWFARLY-PAY-TEST-0000-0000-0000',
                'value_type' => 'string',
                'description' => 'Long payment reference code shown to advertisers.',
            ],
            [
                'name' => 'Payment QR Image',
                'key' => 'payment.qr_image',
                'type' => 'payment',
                'value' => '',
                'value_type' => 'string',
                'description' => 'QR code image path for package payments.',
            ],
            [
                'name' => 'Payment Instructions',
                'key' => 'payment.instructions',
                'type' => 'payment',
                'value' => 'ادفع باستخدام الرمز أو مسح الـ QR، ثم أرسل وصل الدفع عبر واتساب، واضغط تأكيد الاشتراك.',
                'value_type' => 'string',
                'description' => 'Instructions shown on the package payment screen.',
            ],
        ];

        foreach ($settings as $setting) {
            $exists = DB::table('settings')->where('key', $setting['key'])->exists();
            if (!$exists) {
                DB::table('settings')->insert(array_merge($setting, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_subscription_requests');

        DB::table('settings')->whereIn('key', [
            'payment.whatsapp',
            'payment.code',
            'payment.qr_image',
            'payment.instructions',
        ])->delete();
    }
}
