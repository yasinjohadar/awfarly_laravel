<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_forms', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [
                'Enquiry',
                'Complaint',
                'Suggestion',
                'Payments',
                'Technical support',
                'In-app advertising',
                'Report a problem',
            ]);
            $table->string('name');
            $table->string('mobile');
            $table->string('whatsappMobile');
            $table->string('email')->nullable();
            $table->longText('message');
            $table->enum('status', [
                'read',
                'unread',
            ])->default('unread');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contact_forms');
    }
}
