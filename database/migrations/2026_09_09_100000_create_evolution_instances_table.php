<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evolution_instances', function (Blueprint $table) {
            $table->id();
            $table->string('instance_name')->unique();
            $table->string('label')->nullable();
            $table->uuid('evolution_uuid')->nullable();
            $table->string('connection_status')->default('close');
            $table->string('owner_jid')->nullable();
            $table->string('profile_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('profile_pic_url')->nullable();
            $table->longText('qr_code')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_manual')->default(false);
            $table->boolean('rotation_enabled')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->json('metadata')->nullable();
            //per-instance override credentials — falls back to the global
            //whatsapp.* settings (see Settings::Get) when left empty
            $table->string('evolution_base_url')->nullable();
            $table->text('evolution_api_key')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('disconnected_at')->nullable();
            $table->timestamps();

            $table->index('connection_status');
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evolution_instances');
    }
};
