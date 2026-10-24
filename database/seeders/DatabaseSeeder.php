<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('Os dados fictícios de demonstração não são carregados em produção.');

            return;
        }

        $this->call(AutomotiveDetailingSeeder::class);
    }
}
