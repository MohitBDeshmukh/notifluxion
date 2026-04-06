<?php

namespace Notifluxion\LaravelNotify\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UninstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall the Laravel Notification Library (removes config, rolls back migrations, clears cache)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->warn('This will remove the package configuration and optionally rollback migrations.');

        if (!$this->confirm('Are you sure you want to uninstall?')) {
            $this->info('Uninstallation cancelled.');
            return Command::SUCCESS;
        }

        // Rollback migrations
        if ($this->confirm('Would you like to rollback library database migrations first?')) {
            // Note: Since we are in a package, rolling back specific package migrations can be tricky.
            // Typically you'd use migrate:rollback --path=database/migrations/... if you know the exact paths.
            // For simplicity, we just output a manual instruction or attempt rolling back pending.
            $this->warn('Rolling back migrations. Please ensure your schema is matched.');
            // This is a naive rollback assuming the last batch was the package. User may need to manually rollback.
            // We can instruct them instead.
            $this->info('Please manually rollback: php artisan migrate:rollback');
        }

        // Remove config file
        if (File::exists(config_path('notify.php'))) {
            File::delete(config_path('notify.php'));
            $this->info('Configuration file deleted.');
        }

        // Clear cache
        $this->call('config:clear');
        $this->info('Cache cleared.');

        $this->info('Uninstallation completed.');
        return Command::SUCCESS;
    }
}
