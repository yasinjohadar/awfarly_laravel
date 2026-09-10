<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateWhatsappPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Reset cached roles/permissions so newly created permissions are visible immediately
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $keys = ['whatsapp.inquiry', 'whatsapp.add', 'whatsapp.edit', 'whatsapp.delete'];
        $permissions = [];
        foreach ($keys as $key) {
            $permissions[] = Permission::firstOrCreate(['guard_name' => 'admin', 'name' => $key]);
        }

        $superAdmin = Role::where('guard_name', 'admin')->where('name', 'Super Administrator')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        $groupExists = DB::table('permissions_groups')->where('name', 'WhatsApp')->exists();
        if (!$groupExists) {
            $groupId = DB::table('permissions_groups')->insertGetId([
                'name' => 'WhatsApp',
                'is_allowed' => true,
                'is_active' => true,
            ]);

            $data = [
                ['group_id' => $groupId, 'name' => 'WhatsApp inquiry', 'key' => 'whatsapp.inquiry', 'is_allowed' => true],
                ['group_id' => $groupId, 'name' => 'WhatsApp add', 'key' => 'whatsapp.add', 'is_allowed' => true],
                ['group_id' => $groupId, 'name' => 'WhatsApp edit', 'key' => 'whatsapp.edit', 'is_allowed' => true],
                ['group_id' => $groupId, 'name' => 'WhatsApp delete', 'key' => 'whatsapp.delete', 'is_allowed' => true],
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
        DB::table('permissions_groups_data')->whereIn('key', [
            'whatsapp.inquiry',
            'whatsapp.add',
            'whatsapp.edit',
            'whatsapp.delete',
        ])->delete();

        DB::table('permissions_groups')->where('name', 'WhatsApp')->delete();

        DB::table('permissions')->where('guard_name', 'admin')->whereIn('name', [
            'whatsapp.inquiry',
            'whatsapp.add',
            'whatsapp.edit',
            'whatsapp.delete',
        ])->delete();
    }
}
