<?php

namespace Database\Seeders;

use App\Models\Users\Admins\Groups\Group;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PromotionPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // app(PermissionRegistrar::class)->forgetCachedPermissions();


        //Create promotion permissions
        $permissions['modal']['inquiry'] = Permission::updateOrcreate(['guard_name' => 'admin', 'name' => 'modal.inquiry']);
        $permissions['modal']['add'] = Permission::updateOrcreate(['guard_name' => 'admin', 'name' => 'modal.add']);
        $permissions['modal']['edit'] = Permission::updateOrcreate(['guard_name' => 'admin', 'name' => 'modal.edit']);
        $permissions['modal']['delete'] = Permission::updateOrcreate(['guard_name' => 'admin', 'name' => 'modal.delete']);
        $group = Group::where('name', 'Marketing Tools')->first();
        foreach ($permissions as $key => $permission) {
            foreach ($permission as $name => $data) {
                $group->permissions()->create([
                    'name'  => ucfirst($key) . ' ' . $name,
                    'key'  => $key . '.' . $name,
                    'is_allowed'    =>  true,
                    'is_active'    =>  true,
                ]);
            }
        }
    }
}
