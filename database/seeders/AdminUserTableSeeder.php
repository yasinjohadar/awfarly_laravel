<?php

namespace Database\Seeders;

use App\Models\Users\Admins\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AdminUser::query()->delete();

        // Create admin
        AdminUser::create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'language_code' => 'ar',
            'password' => Hash::make('password'),
            'is_super_administrator' => true,
            'is_protected' => true,
        ]);

        //Assign roles to the first admin
        AdminUser::first()->syncRoles('Super Administrator');
    }
}
