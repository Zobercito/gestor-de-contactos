<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ResetDemoCommand extends Command
{
    protected $signature = 'demo:reset {--force : Ejecuta el reinicio aunque DEMO_MODE esté desactivado}';

    protected $description = 'Restaura los datos ficticios de la demostración (migraciones y seeders)';

    public function handle(): int
    {
        if (! config('demo.enabled') && ! $this->option('force')) {
            $this->warn('DEMO_MODE está desactivado. Usa --force si de verdad quieres reiniciar la base de datos.');

            return self::FAILURE;
        }

        $disk = Storage::disk(config('contacts.import.disk'));
        $disk->deleteDirectory(config('contacts.import.directory'));
        $disk->makeDirectory(config('contacts.import.directory'));

        $this->info('Reconstruyendo la base de datos con datos ficticios…');

        Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        $this->line(Artisan::output());
        $this->info('Demostración restaurada.');

        return self::SUCCESS;
    }
}
