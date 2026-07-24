<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminsActionsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins_actions_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->mediumText('summary')->nullable();
            $table->json('data')->nullable();

            $table->string('type')->nullable();

            $table->enum('action', [
                'add',
                'edit',
                'delete',
                'inquiry',
                'login',
                'send',
                'approve',
                'decline',
                'refund',
                'cancel',
                'join',
                'call',
                'restart',
                'reset',
                'import',
                'export',
            ])->nullable();

            $table->timestamps();

            $table->foreign('admin_id')
                ->references('id')->on('admins_users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins_actions_logs');
    }
}
