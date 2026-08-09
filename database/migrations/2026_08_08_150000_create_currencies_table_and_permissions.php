<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateCurrenciesTableAndPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('symbol')->nullable();
            $table->decimal('exchange_rate', 15, 6)->default(1);
            $table->boolean('is_base')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('currencies')->insert([
            [
                'code' => 'SAR',
                'name_en' => 'Saudi Riyal',
                'name_ar' => 'ريال سعودي',
                'symbol' => 'ر.س',
                'exchange_rate' => 1,
                'is_base' => true,
                'is_active' => true,
                'is_visible' => true,
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'USD',
                'name_en' => 'US Dollar',
                'name_ar' => 'دولار أمريكي',
                'symbol' => '$',
                'exchange_rate' => 0.266667,
                'is_base' => false,
                'is_active' => true,
                'is_visible' => true,
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'EGP',
                'name_en' => 'Egyptian Pound',
                'name_ar' => 'جنيه مصري',
                'symbol' => 'ج.م',
                'exchange_rate' => 1,
                'is_base' => false,
                'is_active' => false,
                'is_visible' => false,
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'KWD',
                'name_en' => 'Kuwaiti Dinar',
                'name_ar' => 'دينار كويتي',
                'symbol' => 'د.ك',
                'exchange_rate' => 1,
                'is_base' => false,
                'is_active' => false,
                'is_visible' => false,
                'order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'AED',
                'name_en' => 'UAE Dirham',
                'name_ar' => 'درهم إماراتي',
                'symbol' => 'د.إ',
                'exchange_rate' => 1,
                'is_base' => false,
                'is_active' => false,
                'is_visible' => false,
                'order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Reset cached roles/permissions so newly created permissions are visible immediately
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $keys = ['currencies.inquiry', 'currencies.add', 'currencies.edit', 'currencies.delete'];
        $permissions = [];
        foreach ($keys as $key) {
            $permissions[] = Permission::firstOrCreate(['guard_name' => 'admin', 'name' => $key]);
        }

        $superAdmin = Role::where('guard_name', 'admin')->where('name', 'Super Administrator')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        $groupExists = DB::table('permissions_groups')->where('name', 'Currencies')->exists();
        if (!$groupExists) {
            $groupId = DB::table('permissions_groups')->insertGetId([
                'name' => 'Currencies',
                'is_allowed' => true,
                'is_active' => true,
            ]);

            $data = [
                ['group_id' => $groupId, 'name' => 'Currencies inquiry', 'key' => 'currencies.inquiry', 'is_allowed' => true],
                ['group_id' => $groupId, 'name' => 'Currencies add', 'key' => 'currencies.add', 'is_allowed' => true],
                ['group_id' => $groupId, 'name' => 'Currencies edit', 'key' => 'currencies.edit', 'is_allowed' => true],
                ['group_id' => $groupId, 'name' => 'Currencies delete', 'key' => 'currencies.delete', 'is_allowed' => true],
            ];

            foreach ($data as $row) {
                $row['is_active'] = true;
                DB::table('permissions_groups_data')->insert($row);
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
        Schema::dropIfExists('currencies');

        DB::table('permissions_groups_data')->whereIn('key', [
            'currencies.inquiry',
            'currencies.add',
            'currencies.edit',
            'currencies.delete',
        ])->delete();

        DB::table('permissions_groups')->where('name', 'Currencies')->delete();

        DB::table('permissions')->where('guard_name', 'admin')->whereIn('name', [
            'currencies.inquiry',
            'currencies.add',
            'currencies.edit',
            'currencies.delete',
        ])->delete();
    }
}
