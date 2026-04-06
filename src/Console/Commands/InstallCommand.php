<?php

namespace Notifluxion\LaravelNotify\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Laravel Notification Library (publishes config and migrations)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting installation of Notification Library...');

        // Publish config
        $this->call('vendor:publish', [
            '--tag' => 'notify-config',
        ]);
        $this->info('Configuration published.');

        // Publish migrations
        $this->call('vendor:publish', [
            '--tag' => 'notify-migrations',
        ]);
        $this->info('Migrations published.');

        // Prompt for running migrations
        if ($this->confirm('Would you like to run the migrations now?')) {

            $this->call('migrate');
        }

        $this->info('Installation complete.');

        return Command::SUCCESS;
    }
}
