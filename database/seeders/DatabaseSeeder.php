<?php

namespace Database\Seeders;

use ArtcoreSociety\TranslationImport\Commands\ImportTranslationsCommand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Call default seeders
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            PostSeeder::class,
        ]);

        // Remote setup
        if (! App::isLocal()) {
            Artisan::call(ImportTranslationsCommand::class);
        }
    }
}
