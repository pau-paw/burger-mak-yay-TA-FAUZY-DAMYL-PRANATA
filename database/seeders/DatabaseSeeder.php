<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // No public default password. Demo menus are explicitly opt-in.
        $this->command?->info('Gunakan app:create-admin dan DemoMenuSeeder bila diperlukan.');
    }
}
