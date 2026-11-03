<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Password admin diambil dari ADMIN_PASSWORD di .env.
        // Kalau kosong, dibuat acak dan ditampilkan SEKALI di terminal (tidak ada lagi password bawaan "password").
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD') ?: Str::password(14, symbols: false);

        $admin = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Admin HR', 'password' => $password, 'role' => 'admin', 'is_active' => true]
        );

        if ($admin->wasRecentlyCreated && ! env('ADMIN_PASSWORD')) {
            $this->command?->warn("Akun admin dibuat. Email: {$email} | Password: {$password}");
            $this->command?->warn('Simpan password ini sekarang, lalu ganti lewat menu Profil setelah login.');
        }

        foreach (['Engineering', 'Product', 'Marketing', 'Finance', 'Human Resources'] as $name) {
            Department::firstOrCreate(['name' => $name], ['code' => strtoupper(substr($name, 0, 3))]);
        }

        $this->call(AssessmentSeeder::class);
    }
}
