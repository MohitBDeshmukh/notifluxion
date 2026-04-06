<?php

namespace Notifluxion\LaravelNotify\Console\Commands;

use Illuminate\Console\Command;
use Notifluxion\LaravelNotify\Facades\Notify;
use Notifluxion\LaravelNotify\Notifications\GenericNotification;
use stdClass;

class TestLiveProvidersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:test-live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interactively test Live Notification APIs using real configurations';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        $this->info('🚀 Welcome to the Notifluxion Live Tester');
        $this->warn('Ensure your API keys are loaded into your .env or phpunit.xml before continuing.');

        $channel = $this->choice(
            'Which provider do you want to test?',
            ['SendGrid (Email)', 'SMTP (Email)', 'Twilio (SMS)', 'WhatsApp Cloud API'],
            0
        );

        $mode = $this->choice('How should we dispatch this?', ['Sync (Immediate)', 'Database (Async Queue)'], 0);
        $scheduleAt = null;

        if ($mode === 'Database (Async Queue)') {
            $this->laravel['config']->set('notify.queue.enabled', true);
            $this->laravel['config']->set('notify.queue.strategy', 'database');

            if ($this->confirm('Do you want to send this at a particular time in the future?')) {
                $minutes = (int)$this->ask('How many minutes from now?', 5);
                $scheduleAt = now()->addMinutes($minutes);
                $this->info("Routing to Database Queue... Scheduled to release exactly at: {$scheduleAt->toDateTimeString()}");
            } else {
                $this->info('Routing to Database Queue... (You must run `notify:work` to parse it.)');
            }
        } else {
            $this->laravel['config']->set('notify.queue.enabled', false);
        }

        $notifiable = new stdClass();
        $payloadArray = ['message' => 'Hello from Notifluxion CLI Testing Tool!'];

        try {
            $channelTag = 'email'; // defaults

            switch ($channel) {
                case 'SendGrid (Email)':
                    $notifiable->email = $this->ask('Enter target email address');
                    $this->laravel['config']->set('notify.default.email', 'sendgrid');
                    $this->info('Dispatching to SendGrid...');
                    $channelTag = 'email';
                    break;
                case 'SMTP (Email)':
                    $notifiable->email = $this->ask('Enter target email address');
                    $this->laravel['config']->set('notify.default.email', 'smtp');
                    $this->info('Dispatching to SMTP Base Mailer...');
                    $channelTag = 'email';
                    break;
                case 'Twilio (SMS)':
                    $notifiable->phone_number = $this->ask('Enter target phone number (e.g. +14155552671)');
                    $this->laravel['config']->set('notify.default.sms', 'twilio');
                    $this->info('Dispatching to Twilio SMS...');
                    $channelTag = 'sms';
                    break;
                case 'WhatsApp Cloud API':
                    $notifiable->whatsapp_number = $this->ask('Enter target WhatsApp number (e.g. 14155552671)');
                    $this->laravel['config']->set('notify.default.whatsapp', 'api');
                    $payloadArray['template'] = $this->ask('Enter your WhatsApp template name', 'hello_world');
                    $this->info('Dispatching to WhatsApp...');
                    $channelTag = 'whatsapp';
                    break;
            }

            // Create a proper serializable Universal Notification block
            $mockNotification = new GenericNotification($payloadArray, $channelTag);

            // Route it through the central Queue Manager securely
            Notify::send($notifiable, $mockNotification, $scheduleAt);

            $this->info('✅ Notification Dispatched Successfully!');

        } catch (\Exception $e) {
            $this->error('❌ Dispatch Failed!');
            $this->error($e->getMessage());
        }

        return Command::SUCCESS;
    }
}
