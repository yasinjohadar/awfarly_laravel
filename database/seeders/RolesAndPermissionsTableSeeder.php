<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Prepare DB
        Schema::disableForeignKeyConstraints();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();

        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        //Create permissions
        $permissions = [];

        //Create Admins permissions
        $permissions['admins']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'admins.inquiry']);
        $permissions['admins']['add'] = Permission::create(['guard_name' => 'admin', 'name' => 'admins.add']);
        $permissions['admins']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'admins.edit']);
        $permissions['admins']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'admins.delete']);

        //Create Admins Roles And permissions
        $permissions['admins']['roles']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'admins.roles.inquiry']);
        $permissions['admins']['roles']['add'] = Permission::create(['guard_name' => 'admin', 'name' => 'admins.roles.add']);
        $permissions['admins']['roles']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'admins.roles.edit']);
        $permissions['admins']['roles']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'admins.roles.delete']);

        //Create Admin Customers permissions
        $permissions['customers']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'customers.inquiry']);
        $permissions['customers']['add'] = Permission::create(['guard_name' => 'admin', 'name' => 'customers.add']);
        $permissions['customers']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'customers.edit']);
        $permissions['customers']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'customers.delete']);

        //Create Admin Advertisers permissions
        $permissions['advertisers']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'advertisers.inquiry']);
        $permissions['advertisers']['add'] = Permission::create(['guard_name' => 'admin', 'name' => 'advertisers.add']);
        $permissions['advertisers']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'advertisers.edit']);
        $permissions['advertisers']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'advertisers.delete']);

        //Create Categories permissions
        $permissions['categories']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'categories.inquiry']);
        $permissions['categories']['add'] = Permission::create(['guard_name' => 'admin', 'name' => 'categories.add']);
        $permissions['categories']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'categories.edit']);
        $permissions['categories']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'categories.delete']);

        //Create Posts permissions
        $permissions['posts']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'posts.inquiry']);
        $permissions['posts']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'posts.edit']);
        $permissions['posts']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'posts.delete']);
        $permissions['posts']['reported'] = Permission::create(['guard_name' => 'admin', 'name' => 'posts.reported']);

        //Create Comments permissions
        $permissions['comments']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'comments.inquiry']);
        $permissions['comments']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'comments.edit']);
        $permissions['comments']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'comments.delete']);
        $permissions['comments']['reported'] = Permission::create(['guard_name' => 'admin', 'name' => 'comments.reported']);

        //Create Offers permissions
        $permissions['offers']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'offers.inquiry']);
        $permissions['offers']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'offers.edit']);
        $permissions['offers']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'offers.delete']);
        $permissions['offers']['reported'] = Permission::create(['guard_name' => 'admin', 'name' => 'offers.reported']);

        //Create Proposals permissions
        $permissions['proposals']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'proposals.inquiry']);
        $permissions['proposals']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'proposals.edit']);
        $permissions['proposals']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'proposals.delete']);
        $permissions['proposals']['reported'] = Permission::create(['guard_name' => 'admin', 'name' => 'proposals.reported']);

        //Create Packages permissions
        $permissions['packages']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'packages.inquiry']);
        $permissions['packages']['add'] = Permission::create(['guard_name' => 'admin', 'name' => 'packages.add']);
        $permissions['packages']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'packages.edit']);
        $permissions['packages']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'packages.delete']);

        //Create Promotions permissions
        $permissions['promotions']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'promotions.inquiry']);
        $permissions['promotions']['add'] = Permission::create(['guard_name' => 'admin', 'name' => 'promotions.add']);
        $permissions['promotions']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'promotions.edit']);
        $permissions['promotions']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'promotions.delete']);

        //Create Payments permissions
        $permissions['payments']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'payments.inquiry']);
        $permissions['payments']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'payments.edit']);
        $permissions['payments']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'payments.delete']);

        //Create Income permissions
        $permissions['income']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'income.inquiry']);

        //Create Languages permissions
        $permissions['languages']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'languages.inquiry']);
        $permissions['languages']['add'] = Permission::create(['guard_name' => 'admin', 'name' => 'languages.add']);
        $permissions['languages']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'languages.edit']);
        $permissions['languages']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'languages.delete']);

        //Create Requests permissions
        $permissions['requests']['contact']['us'] = Permission::create(['guard_name' => 'admin', 'name' => 'requests.contact.us']);
        $permissions['requests']['username']['change'] = Permission::create(['guard_name' => 'admin', 'name' => 'requests.username.change']);

        //Create Pages permissions
        $permissions['pages']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'pages.inquiry']);
        $permissions['pages']['add'] = Permission::create(['guard_name' => 'admin', 'name' => 'pages.add']);
        $permissions['pages']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'pages.edit']);
        $permissions['pages']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'pages.delete']);

        //Create Advertisements permissions
        $permissions['advertisements']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'advertisements.inquiry']);
        $permissions['advertisements']['add'] = Permission::create(['guard_name' => 'admin', 'name' => 'advertisements.add']);
        $permissions['advertisements']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'advertisements.edit']);
        $permissions['advertisements']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'advertisements.delete']);

        //Create Send Notifications, Emails And SMS permissions
        $permissions['send']['emails'] = Permission::create(['guard_name' => 'admin', 'name' => 'send.emails']);
        $permissions['send']['sms'] = Permission::create(['guard_name' => 'admin', 'name' => 'send.sms']);
        $permissions['send']['notifications'] = Permission::create(['guard_name' => 'admin', 'name' => 'send.notifications']);

        //Create Settings permissions
        $permissions['settings']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'settings.inquiry']);
        $permissions['settings']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'settings.edit']);

        //Create Backup permissions
        $permissions['export']['database'] = Permission::create(['guard_name' => 'admin', 'name' => 'export.database']);

        //Create Logs permissions
        $permissions['logs']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'logs.inquiry']);

        //Create Countries permissions
        $permissions['countries']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'countries.inquiry']);
        $permissions['countries']['add'] = Permission::create(['guard_name' => 'admin', 'name' => 'countries.add']);
        $permissions['countries']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'countries.edit']);
        $permissions['countries']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'countries.delete']);

        //Create Governorates permissions
        $permissions['governorates']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'governorates.inquiry']);
        $permissions['governorates']['add'] = Permission::create(['guard_name' => 'admin', 'name' => 'governorates.add']);
        $permissions['governorates']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'governorates.edit']);
        $permissions['governorates']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'governorates.delete']);

        //Create Cities permissions
        $permissions['cities']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'cities.inquiry']);
        $permissions['cities']['add'] = Permission::create(['guard_name' => 'admin', 'name' => 'cities.add']);
        $permissions['cities']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'cities.edit']);
        $permissions['cities']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'cities.delete']);

        //Create Business Types permissions
        $permissions['business']['types']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'business.types.inquiry']);
        $permissions['business']['types']['add'] = Permission::create(['guard_name' => 'admin', 'name' => 'business.types.add']);
        $permissions['business']['types']['edit'] = Permission::create(['guard_name' => 'admin', 'name' => 'business.types.edit']);
        $permissions['business']['types']['delete'] = Permission::create(['guard_name' => 'admin', 'name' => 'business.types.delete']);

        //chats permissions
        $permissions['chats']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'chats.inquiry']);

        //ratings permissions
        $permissions['ratings']['inquiry'] = Permission::create(['guard_name' => 'admin', 'name' => 'ratings.inquiry']);
        $permissions['ratings']['approve'] = Permission::create(['guard_name' => 'admin', 'name' => 'ratings.approve']);

        //statistics permissions
        $permissions['statistics']['payments'] = Permission::create(['guard_name' => 'admin', 'name' => 'statistics.payments']);
        $permissions['statistics']['packages'] = Permission::create(['guard_name' => 'admin', 'name' => 'statistics.packages']);
        $permissions['statistics']['proposals'] = Permission::create(['guard_name' => 'admin', 'name' => 'statistics.proposals']);
        $permissions['statistics']['users'] = Permission::create(['guard_name' => 'admin', 'name' => 'statistics.users']);
        $permissions['statistics']['requests'] = Permission::create(['guard_name' => 'admin', 'name' => 'statistics.requests']);
        $permissions['statistics']['reports'] = Permission::create(['guard_name' => 'admin', 'name' => 'statistics.reports']);
        /**************************************************************************************/

        //Create permissions to roles
        $permissionsRoles = [];

        //Set 'SuperAdministrator' role
        $permissionsRoles['SuperAdministrator'] = [
            //Create Admins permissions
            $permissions['admins']['inquiry'],
            $permissions['admins']['add'],
            $permissions['admins']['edit'],
            $permissions['admins']['delete'],

            //Create Roles And permissions
            $permissions['admins']['roles']['inquiry'],
            $permissions['admins']['roles']['add'],
            $permissions['admins']['roles']['edit'],
            $permissions['admins']['roles']['delete'],

            //Create Customers permissions
            $permissions['customers']['inquiry'],
            $permissions['customers']['add'],
            $permissions['customers']['edit'],
            $permissions['customers']['delete'],

            //Create Advertisers permissions
            $permissions['advertisers']['inquiry'],
            $permissions['advertisers']['add'],
            $permissions['advertisers']['edit'],
            $permissions['advertisers']['delete'],

            //Create Categories permissions
            $permissions['categories']['inquiry'],
            $permissions['categories']['add'],
            $permissions['categories']['edit'],
            $permissions['categories']['delete'],

            //Create Posts permissions
            $permissions['posts']['inquiry'],
            $permissions['posts']['edit'],
            $permissions['posts']['delete'],
            $permissions['posts']['reported'],

            //Create Comments permissions
            $permissions['comments']['inquiry'],
            $permissions['comments']['edit'],
            $permissions['comments']['delete'],
            $permissions['comments']['reported'],

            //Create Offers permissions
            $permissions['offers']['inquiry'],
            $permissions['offers']['edit'],
            $permissions['offers']['delete'],
            $permissions['offers']['reported'],

            //Create Proposals permissions
            $permissions['proposals']['inquiry'],
            $permissions['proposals']['edit'],
            $permissions['proposals']['delete'],
            $permissions['proposals']['reported'],

            //Create Packages permissions
            $permissions['packages']['inquiry'],
            $permissions['packages']['add'],
            $permissions['packages']['edit'],
            $permissions['packages']['delete'],

            //Create Promotions permissions
            $permissions['promotions']['inquiry'],
            $permissions['promotions']['add'],
            $permissions['promotions']['edit'],
            $permissions['promotions']['delete'],

            //Create Payments permissions
            $permissions['payments']['inquiry'],
            $permissions['payments']['edit'],
            $permissions['payments']['delete'],

            //Create Income permissions
            $permissions['income']['inquiry'],

            //Create Languages permissions
            $permissions['languages']['inquiry'],
            $permissions['languages']['add'],
            $permissions['languages']['edit'],
            $permissions['languages']['delete'],

            //Create Requests permissions
            $permissions['requests']['contact']['us'],
            $permissions['requests']['username']['change'],

            //Create Pages permissions
            $permissions['pages']['inquiry'],
            $permissions['pages']['add'],
            $permissions['pages']['edit'],
            $permissions['pages']['delete'],

            //Create Advertisements permissions
            $permissions['advertisements']['inquiry'],
            $permissions['advertisements']['add'],
            $permissions['advertisements']['edit'],
            $permissions['advertisements']['delete'],

            //Create Send Emails, SMS And Notifications permissions
            $permissions['send']['emails'],
            $permissions['send']['sms'],
            $permissions['send']['notifications'],

            //Create Settings permissions
            $permissions['settings']['inquiry'],
            $permissions['settings']['edit'],

            //Create Backup permissions
            $permissions['export']['database'],

            //Create Logs permissions
            $permissions['logs']['inquiry'],

            //Create Countries permissions
            $permissions['countries']['inquiry'],
            $permissions['countries']['add'],
            $permissions['countries']['edit'],
            $permissions['countries']['delete'],

            //Create Governorates permissions
            $permissions['governorates']['inquiry'],
            $permissions['governorates']['add'],
            $permissions['governorates']['edit'],
            $permissions['governorates']['delete'],

            //Create Cities permissions
            $permissions['cities']['inquiry'],
            $permissions['cities']['add'],
            $permissions['cities']['edit'],
            $permissions['cities']['delete'],

            //Create Business Types permissions
            $permissions['business']['types']['inquiry'],
            $permissions['business']['types']['add'],
            $permissions['business']['types']['edit'],
            $permissions['business']['types']['delete'],

            //chats permissions
            $permissions['chats']['inquiry'],

            //ratings permissions
            $permissions['ratings']['inquiry'],
            $permissions['ratings']['approve'],

            //statistics permissions
            $permissions['statistics']['payments'],
            $permissions['statistics']['packages'],
            $permissions['statistics']['proposals'],
            $permissions['statistics']['users'],
            $permissions['statistics']['requests'],
            $permissions['statistics']['reports'],
        ];

        /**************************************************************************************/

        //Create roles
        $roles = [];

        //Create 'SuperAdministrator' role
        $roles['SuperAdministrator'] = Role::create(['guard_name' => 'admin', 'name' => 'Super Administrator']);

        /**************************************************************************************/
        //Sync roles to the permissions

        //Create 'SuperAdministrator' role
        $roles['SuperAdministrator']->syncPermissions($permissionsRoles['SuperAdministrator']);
    }
}
