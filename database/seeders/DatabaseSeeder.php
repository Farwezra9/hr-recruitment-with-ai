<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin HR',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        foreach (['Engineering', 'Product', 'Marketing', 'Finance', 'Human Resources'] as $name) {
            Department::create(['name' => $name, 'code' => strtoupper(substr($name, 0, 3))]);
        }

        $this->call(AssessmentSeeder::class);
    }
}
