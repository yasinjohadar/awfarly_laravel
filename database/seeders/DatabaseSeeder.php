<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Roles
        $this->call(RolesAndPermissionsTableSeeder::class);

        // Permissions
        $this->call(PermissionsGroupsAndDataTableSeeder::class);

        // Settings
        $this->call(SettingsTableSeeder::class);

        // Languages
        $this->call(LanguagesTableSeeder::class);

        // Admin users
        $this->call(AdminUserTableSeeder::class);

        // Business Types
        $this->call(BusinessTypesTableSeeder::class);

        // Pages
        $this->call(PagesTableSeeder::class);
    }
}
