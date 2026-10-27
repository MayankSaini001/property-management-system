<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'mayank123@gmail.com'], 
            [
                'name' => 'Super Admin',
                'password' => 'Mayank@@12345',
                'role' => 'super_admin',
                'company_id' => null,
            ]
        );
    }
}