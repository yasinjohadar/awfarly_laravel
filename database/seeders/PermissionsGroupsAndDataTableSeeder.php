<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissionsGroupsAndDataTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('permissions_groups')->truncate();
        DB::table('permissions_groups_data')->truncate();
        Schema::enableForeignKeyConstraints();

        //create permissions groups
        DB::table('permissions_groups')->insert([
            ['name' => 'Admins', 'is_allowed' => true,],
            ['name' => 'Admins Roles', 'is_allowed' => true,],
            ['name' => 'Customers', 'is_allowed' => true,],
            ['name' => 'Advertisers', 'is_allowed' => true,],
            ['name' => 'Categories', 'is_allowed' => true,],
            ['name' => 'Posts', 'is_allowed' => true,],
            ['name' => 'Comments', 'is_allowed' => true,],
            ['name' => 'Offers', 'is_allowed' => true,],
            ['name' => 'Proposals', 'is_allowed' => true,],
            ['name' => 'Packages', 'is_allowed' => true,],
            ['name' => 'Promotions', 'is_allowed' => true,],
            ['name' => 'Payments', 'is_allowed' => true,],
            ['name' => 'Income', 'is_allowed' => true,],
            ['name' => 'Languages', 'is_allowed' => true,],
            ['name' => 'Requests', 'is_allowed' => true,],
            ['name' => 'Pages', 'is_allowed' => true,],
            ['name' => 'Advertisements', 'is_allowed' => true,],
            ['name' => 'Marketing Tools', 'is_allowed' => true,],
            ['name' => 'Settings', 'is_allowed' => true,],
            ['name' => 'Backup', 'is_allowed' => true,],
            ['name' => 'Logs', 'is_allowed' => true,],
            ['name' => 'Countries', 'is_allowed' => true,],
            ['name' => 'Governorates', 'is_allowed' => true,],
            ['name' => 'Business Types', 'is_allowed' => true,],
            ['name' => 'Chats', 'is_allowed' => true,],
            ['name' => 'Ratings', 'is_allowed' => true,],
            ['name' => 'Statistics', 'is_allowed' => true,],
            ['name' => 'Cities', 'is_allowed' => true,],
        ]);


        /**
         * create groups data (for the key to be used in permissions)
         * the Following group ids
         * Admins = 1
         * Admins Roles = 2
         * Customers = 3
         * Advertisers = 4
         * Categories = 5
         * Posts = 6
         * Comments = 7
         * Offers = 8
         * Proposals = 9
         * Packages = 10
         * Promotions = 11
         * Payments = 12
         * Income = 13
         * Languages = 14
         * Requests = 15
         * Pages = 16
         * Advertisements = 17
         * Marketing Tools = 18
         * Settings = 19
         * Backup = 20
         * Logs = 21
         * Countries = 22
         * Governorates = 23
         * BusinessTypes = 24
         * Chats = 25
         * Ratings = 26
         * Statistics = 27
         * Cities = 28
         */
        DB::table('permissions_groups_data')->insert([
            //Create Admins permissions
            ['group_id' => 1, 'name' => "Admins inquiry", 'key' => 'admins.inquiry', 'is_allowed' => true,],
            ['group_id' => 1, 'name' => "Admins add", 'key' => 'admins.add', 'is_allowed' => true,],
            ['group_id' => 1, 'name' => "Admins edit", 'key' => 'admins.edit', 'is_allowed' => true,],
            ['group_id' => 1, 'name' => "Admins delete", 'key' => 'admins.delete', 'is_allowed' => true,],

            //Create Admins Roles permissions
            ['group_id' => 2, 'name' => "Admins roles inquiry", 'key' => 'admins.roles.inquiry', 'is_allowed' => true,],
            ['group_id' => 2, 'name' => "Admins roles add", 'key' => 'admins.roles.add', 'is_allowed' => true,],
            ['group_id' => 2, 'name' => "Admins roles edit", 'key' => 'admins.roles.edit', 'is_allowed' => true,],
            ['group_id' => 2, 'name' => "Admins roles delete", 'key' => 'admins.roles.delete', 'is_allowed' => true,],

            //Create Customers permissions
            ['group_id' => 3, 'name' => "Customers inquiry", 'key' => 'customers.inquiry', 'is_allowed' => true,],
            ['group_id' => 3, 'name' => "Customers add", 'key' => 'customers.add', 'is_allowed' => true,],
            ['group_id' => 3, 'name' => "Customers edit", 'key' => 'customers.edit', 'is_allowed' => true,],
            ['group_id' => 3, 'name' => "Customers delete", 'key' => 'customers.delete', 'is_allowed' => true,],

            //Create Advertisers permissions
            ['group_id' => 4, 'name' => "Advertisers inquiry", 'key' => 'advertisers.inquiry', 'is_allowed' => true,],
            ['group_id' => 4, 'name' => "Advertisers add", 'key' => 'advertisers.add', 'is_allowed' => true,],
            ['group_id' => 4, 'name' => "Advertisers edit", 'key' => 'advertisers.edit', 'is_allowed' => true,],
            ['group_id' => 4, 'name' => "Advertisers delete", 'key' => 'advertisers.delete', 'is_allowed' => true,],

            //Create Categories permissions
            ['group_id' => 5, 'name' => "Categories inquiry", 'key' => 'categories.inquiry', 'is_allowed' => true,],
            ['group_id' => 5, 'name' => "Categories add", 'key' => 'categories.add', 'is_allowed' => true,],
            ['group_id' => 5, 'name' => "Categories edit", 'key' => 'categories.edit', 'is_allowed' => true,],
            ['group_id' => 5, 'name' => "Categories delete", 'key' => 'categories.delete', 'is_allowed' => true,],

            //Create Posts permissions
            ['group_id' => 6, 'name' => "Posts inquiry", 'key' => 'posts.inquiry', 'is_allowed' => true,],
            ['group_id' => 6, 'name' => "Posts edit", 'key' => 'posts.edit', 'is_allowed' => true,],
            ['group_id' => 6, 'name' => "Posts delete", 'key' => 'posts.delete', 'is_allowed' => true,],
            ['group_id' => 6, 'name' => "Reported posts", 'key' => 'posts.reported', 'is_allowed' => true,],

            //Create Comments permissions
            ['group_id' => 7, 'name' => "Comments inquiry", 'key' => 'comments.inquiry', 'is_allowed' => true,],
            ['group_id' => 7, 'name' => "Comments edit", 'key' => 'comments.edit', 'is_allowed' => true,],
            ['group_id' => 7, 'name' => "Comments delete", 'key' => 'comments.delete', 'is_allowed' => true,],
            ['group_id' => 7, 'name' => "Reported comments", 'key' => 'comments.reported', 'is_allowed' => true,],

            //Create Offers permissions
            ['group_id' => 8, 'name' => "Offers inquiry", 'key' => 'offers.inquiry', 'is_allowed' => true,],
            ['group_id' => 8, 'name' => "Offers edit", 'key' => 'offers.edit', 'is_allowed' => true,],
            ['group_id' => 8, 'name' => "Offers delete", 'key' => 'offers.delete', 'is_allowed' => true,],
            ['group_id' => 8, 'name' => "Reported offers", 'key' => 'offers.reported', 'is_allowed' => true,],

            //Create Proposals permissions
            ['group_id' => 9, 'name' => "Proposals inquiry", 'key' => 'proposals.inquiry', 'is_allowed' => true,],
            ['group_id' => 9, 'name' => "Proposals edit", 'key' => 'proposals.edit', 'is_allowed' => true,],
            ['group_id' => 9, 'name' => "Proposals delete", 'key' => 'proposals.delete', 'is_allowed' => true,],
            ['group_id' => 9, 'name' => "Reported proposals", 'key' => 'proposals.reported', 'is_allowed' => true,],

            //Create Packages permissions
            ['group_id' => 10, 'name' => "Packages inquiry", 'key' => 'packages.inquiry', 'is_allowed' => true,],
            ['group_id' => 10, 'name' => "Packages add", 'key' => 'packages.add', 'is_allowed' => true,],
            ['group_id' => 10, 'name' => "Packages edit", 'key' => 'packages.edit', 'is_allowed' => true,],
            ['group_id' => 10, 'name' => "Packages delete", 'key' => 'packages.delete', 'is_allowed' => true,],

            //Create Promotions permissions
            ['group_id' => 11, 'name' => "Promotions inquiry", 'key' => 'promotions.inquiry', 'is_allowed' => true,],
            ['group_id' => 11, 'name' => "Promotions add", 'key' => 'promotions.add', 'is_allowed' => true,],
            ['group_id' => 11, 'name' => "Promotions edit", 'key' => 'promotions.edit', 'is_allowed' => true,],
            ['group_id' => 11, 'name' => "Promotions delete", 'key' => 'promotions.delete', 'is_allowed' => true,],

            //Create Payments permissions
            ['group_id' => 12, 'name' => "Payments inquiry", 'key' => 'payments.inquiry', 'is_allowed' => true,],
            ['group_id' => 12, 'name' => "Payments edit", 'key' => 'payments.edit', 'is_allowed' => true,],
            ['group_id' => 12, 'name' => "Payments delete", 'key' => 'payments.delete', 'is_allowed' => true,],

            //Create Income permissions
            ['group_id' => 13, 'name' => "Income inquiry", 'key' => 'income.inquiry', 'is_allowed' => true,],

            //Create Languages permissions
            ['group_id' => 14, 'name' => "Languages inquiry", 'key' => 'languages.inquiry', 'is_allowed' => true,],
            ['group_id' => 14, 'name' => "Languages add", 'key' => 'languages.add', 'is_allowed' => true,],
            ['group_id' => 14, 'name' => "Languages edit", 'key' => 'languages.edit', 'is_allowed' => true,],
            ['group_id' => 14, 'name' => "Languages delete", 'key' => 'languages.delete', 'is_allowed' => true,],

            //Create Requests permissions
            ['group_id' => 15, 'name' => "Requests contact us", 'key' => 'requests.contact.us', 'is_allowed' => true,],
            ['group_id' => 15, 'name' => "Requests username change", 'key' => 'requests.username.change', 'is_allowed' => true,],

            //Create Pages permissions
            ['group_id' => 16, 'name' => "Pages inquiry", 'key' => 'pages.inquiry', 'is_allowed' => true,],
            ['group_id' => 16, 'name' => "Pages add", 'key' => 'pages.add', 'is_allowed' => true,],
            ['group_id' => 16, 'name' => "Pages edit", 'key' => 'pages.edit', 'is_allowed' => true,],
            ['group_id' => 16, 'name' => "Pages delete", 'key' => 'pages.delete', 'is_allowed' => true,],

            //Create Advertisements permissions
            ['group_id' => 17, 'name' => "Advertisements inquiry", 'key' => 'advertisements.inquiry', 'is_allowed' => true,],
            ['group_id' => 17, 'name' => "Advertisements add", 'key' => 'advertisements.add', 'is_allowed' => true,],
            ['group_id' => 17, 'name' => "Advertisements edit", 'key' => 'advertisements.edit', 'is_allowed' => true,],
            ['group_id' => 17, 'name' => "Advertisements delete", 'key' => 'advertisements.delete', 'is_allowed' => true,],

            //Create Marketing Tools permissions
            ['group_id' => 18, 'name' => "Send emails", 'key' => 'send.emails', 'is_allowed' => true,],
            ['group_id' => 18, 'name' => "Send sms", 'key' => 'send.sms', 'is_allowed' => true,],
            ['group_id' => 18, 'name' => "Send notifications", 'key' => 'send.notifications', 'is_allowed' => true,],

            //Create Settings permissions
            ['group_id' => 19, 'name' => "Settings inquiry", 'key' => 'settings.inquiry', 'is_allowed' => true,],
            ['group_id' => 19, 'name' => "Settings edit", 'key' => 'settings.edit', 'is_allowed' => true,],

            //Create Backup permissions
            ['group_id' => 20, 'name' => "Export database", 'key' => 'export.database', 'is_allowed' => true,],

            //Create Logs permissions
            ['group_id' => 21, 'name' => "Logs inquiry", 'key' => 'logs.inquiry', 'is_allowed' => true,],


            //Create Countries permissions
            ['group_id' => 22, 'name' => "Countries inquiry", 'key' => 'countries.inquiry', 'is_allowed' => true,],
            ['group_id' => 22, 'name' => "Countries add", 'key' => 'countries.add', 'is_allowed' => true,],
            ['group_id' => 22, 'name' => "Countries edit", 'key' => 'countries.edit', 'is_allowed' => true,],
            ['group_id' => 22, 'name' => "Countries delete", 'key' => 'countries.delete', 'is_allowed' => true,],

            //Create Governorates permissions
            ['group_id' => 23, 'name' => "Governorates inquiry", 'key' => 'governorates.inquiry', 'is_allowed' => true,],
            ['group_id' => 23, 'name' => "Governorates add", 'key' => 'governorates.add', 'is_allowed' => true,],
            ['group_id' => 23, 'name' => "Governorates edit", 'key' => 'governorates.edit', 'is_allowed' => true,],
            ['group_id' => 23, 'name' => "Governorates delete", 'key' => 'governorates.delete', 'is_allowed' => true,],

            //Create Business Types permissions
            ['group_id' => 24, 'name' => "Business Types inquiry", 'key' => 'business.types.inquiry', 'is_allowed' => true,],
            ['group_id' => 24, 'name' => "Business Types add", 'key' => 'business.types.add', 'is_allowed' => true,],
            ['group_id' => 24, 'name' => "Business Types edit", 'key' => 'business.types.edit', 'is_allowed' => true,],
            ['group_id' => 24, 'name' => "Business Types delete", 'key' => 'business.types.delete', 'is_allowed' => true,],

            //Create Business Types permissions
            ['group_id' => 25, 'name' => "Chats inquiry", 'key' => 'chats.inquiry', 'is_allowed' => true,],

            //Create Business Types permissions
            ['group_id' => 26, 'name' => "Ratings inquiry", 'key' => 'ratings.inquiry', 'is_allowed' => true,],
            ['group_id' => 26, 'name' => "Ratings Approve", 'key' => 'ratings.approve', 'is_allowed' => true,],

            //Create Statistics permissions
            ['group_id' => 27, 'name' => "Statistics Payments", 'key' => 'statistics.payments', 'is_allowed' => true,],
            ['group_id' => 27, 'name' => "Statistics Packages", 'key' => 'statistics.packages', 'is_allowed' => true,],
            ['group_id' => 27, 'name' => "Statistics Proposals", 'key' => 'statistics.proposals', 'is_allowed' => true,],
            ['group_id' => 27, 'name' => "Statistics Users", 'key' => 'statistics.users', 'is_allowed' => true,],
            ['group_id' => 27, 'name' => "Statistics Requests", 'key' => 'statistics.requests', 'is_allowed' => true,],
            ['group_id' => 27, 'name' => "Statistics Reports", 'key' => 'statistics.reports', 'is_allowed' => true,],

            //Create Cities permissions
            ['group_id' => 28, 'name' => "Cities inquiry", 'key' => 'cities.inquiry', 'is_allowed' => true,],
            ['group_id' => 28, 'name' => "Cities add", 'key' => 'cities.add', 'is_allowed' => true,],
            ['group_id' => 28, 'name' => "Cities edit", 'key' => 'cities.edit', 'is_allowed' => true,],
            ['group_id' => 28, 'name' => "Cities delete", 'key' => 'cities.delete', 'is_allowed' => true,],
        ]);
    }
}
