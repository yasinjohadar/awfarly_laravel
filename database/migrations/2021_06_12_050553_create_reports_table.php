<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [
                'Sexually Inappropriate',
                'Abusive Content',
                'Misleading or Scam',
                'Offensive',
                'Violence',
                'Prohibited Content',
                'Spam',
                'False News',
                'Other',
            ])->default('Other');
            $table->string('user_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('reported_type');
            $table->unsignedBigInteger('reported_id');
            $table->longText('reason')->nullable();
            $table->enum('status', [
                'pending',
                'solved'
            ])->default('pending');
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
        Schema::dropIfExists('reports');
    }
}
