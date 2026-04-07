<?php

namespace Notifluxion\LaravelNotify\Console\Commands;

use Illuminate\Console\Command;
use Notifluxion\LaravelNotify\Contracts\QueueStrategyInterface;

class WorkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:work';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the pending notifications queue stored in the database.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting Notification Queue Worker...');

        // Force reload core configurations since we are likely traversing via Testbench
        $xmlPath = getcwd() . '/phpunit.xml';
        if (file_exists($xmlPath)) {
            $xml = simplexml_load_file($xmlPath);
            if (isset($xml->php->env)) {
                foreach ($xml->php->env as $envAttribute) {
                    $name = (string)$envAttribute['name'];
                    $value = (string)$envAttribute['value'];
                    putenv("{$name}={$value}");
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
            
            // SMTP Overrides for standalone testing
            $this->laravel['config']->set('mail.default', $_ENV['MAIL_MAILER'] ?? app('config')->get('mail.default'));
            $this->laravel['config']->set('mail.mailers.smtp', [
                'transport' => 'smtp',
                'host' => $_ENV['MAIL_HOST'] ?? app('config')->get('mail.mailers.smtp.host'),
                'port' => $_ENV['MAIL_PORT'] ?? app('config')->get('mail.mailers.smtp.port'),
                'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? app('config')->get('mail.mailers.smtp.encryption'),
                'username' => $_ENV['MAIL_USERNAME'] ?? app('config')->get('mail.mailers.smtp.username'),
                'password' => $_ENV['MAIL_PASSWORD'] ?? app('config')->get('mail.mailers.smtp.password'),
            ]);
            $this->laravel['config']->set('mail.from.address', $_ENV['MAIL_FROM_ADDRESS'] ?? app('config')->get('mail.from.address'));
            $this->laravel['config']->set('mail.from.name', $_ENV['MAIL_FROM_NAME'] ?? app('config')->get('mail.from.name'));

            // Force override Testbench's 'testing' Sqlite DB connection to hit our real MySQL Sandbox cleanly
            $this->laravel['config']->set('database.default', $_ENV['DB_CONNECTION'] ?? app('config')->get('database.default'));
            $this->laravel['config']->set('database.connections.mysql.host', $_ENV['DB_HOST'] ?? app('config')->get('database.connections.mysql.host'));
            $this->laravel['config']->set('database.connections.mysql.port', $_ENV['DB_PORT'] ?? app('config')->get('database.connections.mysql.port'));
            $this->laravel['config']->set('database.connections.mysql.database', $_ENV['DB_DATABASE'] ?? app('config')->get('database.connections.mysql.database'));
            $this->laravel['config']->set('database.connections.mysql.username', $_ENV['DB_USERNAME'] ?? app('config')->get('database.connections.mysql.username'));
            $this->laravel['config']->set('database.connections.mysql.password', $_ENV['DB_PASSWORD'] ?? app('config')->get('database.connections.mysql.password'));
        }

        // Dynamically instantiate the queue strategy based on configuration
        $strategyName = app('config')->get('notify.queue.strategy') ?? 'database';
        $strategy = $this->laravel->make("notify.strategy.{$strategyName}");
        
        if (!$strategy instanceof QueueStrategyInterface) {
            $this->error('Failed to resolve Queue Strategy');
            return Command::FAILURE;
        }

        $this->info("Polling {$strategyName} for pending notifications...");
        
        try {
            $strategy->process();
            $this->info('✅ Queue completely processed!');
        } catch (\Exception $e) {
            $this->error('Failed processing queue: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
