<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'END User',
            'email' => 'end@gmail.com',
            'password' => bcrypt('end@123'),
            'role' => 'end_user',
        ]);

        User::factory()->create([
            'name' => 'SFQ User',
            'email' => 'sfq@gmail.com',
            'password' => bcrypt('sfq@123'),
            'role' => 'sfq_user',
        ]);

        User::factory()->create([
            'name' => 'Driver User',
            'email' => 'driver@gmail.com',
            'password' => bcrypt('driver@123'),
            'role' => 'driver',
        ]);

        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@gmail.com',
            'password' => bcrypt('john@123'),
            'role' => 'driver',
        ]);

        User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@gmail.com',
            'password' => bcrypt('jane@123'),
            'role' => 'driver',
        ]);

        User::factory()->create([
            'name' => 'Robert Brown',
            'email' => 'robert@gmail.com',
            'password' => bcrypt('robert@123'),
            'role' => 'driver',
        ]);
    }
}
