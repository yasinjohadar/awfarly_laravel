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
        // Core system
        $this->call(RolesAndPermissionsTableSeeder::class);
        $this->call(PermissionsGroupsAndDataTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(LanguagesTableSeeder::class);
        $this->call(AdminUserTableSeeder::class);
        $this->call(BusinessTypesTableSeeder::class);
        $this->call(PagesTableSeeder::class);

        // Syria demo dataset
        $this->call(SyriaGeoSeeder::class);
        $this->call(CategoriesTableSeeder::class);
        $this->call(PackagesTableSeeder::class);
        $this->call(DemoUsersSeeder::class);
        $this->call(DemoContentSeeder::class);
        $this->call(ContactFormsSeeder::class);
    }
}
