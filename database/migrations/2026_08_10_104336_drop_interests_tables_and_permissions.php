<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DropInterestsTablesAndPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('advertiser_interests');
        Schema::dropIfExists('customer_interests');
        Schema::dropIfExists('interests');

        DB::table('permissions_groups_data')->whereIn('key', [
            'interests.inquiry',
            'interests.add',
            'interests.edit',
            'interests.delete',
        ])->delete();

        DB::table('permissions_groups')->where('name', 'Interests')->delete();

        DB::table('permissions')->where('guard_name', 'admin')->whereIn('name', [
            'interests.inquiry',
            'interests.add',
            'interests.edit',
            'interests.delete',
        ])->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('interests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_interest_id')->nullable();
            $table->unsignedSmallInteger('order')->nullable();
            $table->string('name_en');
            $table->string('name_ar');
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parent_interest_id')
                ->on('interests')->references('id')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::create('customer_interests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('interest_id');
            $table->unsignedBigInteger('customer_id');
            $table->timestamps();

            $table->foreign('interest_id')
                ->on('interests')->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('customer_id')
                ->on('customers_users')->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::create('advertiser_interests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('interest_id');
            $table->unsignedBigInteger('advertiser_id');
            $table->timestamps();

            $table->foreign('interest_id')
                ->on('interests')->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('advertiser_id')
                ->on('advertisers_users')->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $keys = ['interests.inquiry', 'interests.add', 'interests.edit', 'interests.delete'];
        $permissions = [];
        foreach ($keys as $key) {
            $permissions[] = Permission::firstOrCreate(['guard_name' => 'admin', 'name' => $key]);
        }

        $superAdmin = Role::where('guard_name', 'admin')->where('name', 'Super Administrator')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        $groupExists = DB::table('permissions_groups')->where('name', 'Interests')->exists();
        if (!$groupExists) {
            $groupId = DB::table('permissions_groups')->insertGetId([
                'name' => 'Interests',
                'is_allowed' => true,
                'is_active' => true,
            ]);

            $data = [
                ['group_id' => $groupId, 'name' => 'Interests inquiry', 'key' => 'interests.inquiry', 'is_allowed' => true],
                ['group_id' => $groupId, 'name' => 'Interests add', 'key' => 'interests.add', 'is_allowed' => true],
                ['group_id' => $groupId, 'name' => 'Interests edit', 'key' => 'interests.edit', 'is_allowed' => true],
                ['group_id' => $groupId, 'name' => 'Interests delete', 'key' => 'interests.delete', 'is_allowed' => true],
            ];

            foreach ($data as $row) {
                $row['is_active'] = true;
                DB::table('permissions_groups_data')->insert($row);
            }
        }
    }
}
