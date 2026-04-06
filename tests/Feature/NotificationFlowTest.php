<?php

namespace Notifluxion\LaravelNotify\Tests\Feature;

use Notifluxion\LaravelNotify\Tests\TestCase;
use Notifluxion\LaravelNotify\Facades\Notify;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use stdClass;

class NotificationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    public function test_it_dispatches_notification_using_sync_strategy()
    {
        // Enforce Sync strategy 
        $this->app['config']->set('notify.queue.enabled', false);
        $this->app['config']->set('notify.default.email', 'smtp');

        // Create a dummy notifiable and notification payload
        $user = new stdClass();
        $user->id = 1;
        $user->email = 'user@example.com';

        $payload = ['message' => 'Hello Real World!'];

        // Mock the SMTP driver to prevent real network connections
        $mockDriver = \Mockery::mock(\Notifluxion\LaravelNotify\Drivers\Email\SmtpDriver::class);
        $mockDriver->shouldReceive('send')->once()->andReturn(true);
        Notify::extend('smtp', function() use ($mockDriver) { return $mockDriver; });

        Notify::send($user, $payload);
        
        // If it got here on Sync mode without exception, the dispatch worked.
        $this->assertTrue(true);
    }

    public function test_it_dispatches_notification_using_database_strategy()
    {
        // Enforce Database Queue strategy 
        $this->app['config']->set('notify.queue.enabled', true);
        $this->app['config']->set('notify.queue.strategy', 'database');
        $this->app['config']->set('notify.default.email', 'sendgrid');

        $user = new stdClass();
        $user->id = 55;
        
        $payload = ['message' => 'Async Database Message!'];
        
        // Action: Push the notification
        Notify::send($user, $payload);

        // Assertion: Ensure the payload was intercepted and stored in scheduled_notifications
        $this->assertDatabaseHas('scheduled_notifications', [
            'notifiable_id' => 55,
            'driver' => \Notifluxion\LaravelNotify\Drivers\Email\SendGridDriver::class,
            'status' => 'pending'
        ]);
        
        // Assert payload exists
        $dbRow = DB::table('scheduled_notifications')->first();
        $this->assertNotNull($dbRow);
        
        $unserialized = unserialize($dbRow->notification);
        $notificationData = is_array($unserialized) ? $unserialized['notification'] : $unserialized;
        $this->assertEquals('Async Database Message!', $notificationData['message']);
    }

    public function test_database_strategy_can_process_queue()
    {
        // Enforce Database Queue strategy 
        $this->app['config']->set('notify.queue.enabled', true);
        $this->app['config']->set('notify.queue.strategy', 'database');
        
        // Send a notification first
        $user = new stdClass();
        $user->id = 100;
        Notify::send($user, ['test' => 'processing']);

        // Verify it is pending
        $this->assertDatabaseHas('scheduled_notifications', ['status' => 'pending']);

        // Actually trigger the queue processor!
        // First mock the SendGrid target so it passes smoothly
        $mockDriver = \Mockery::mock(\Notifluxion\LaravelNotify\Drivers\Email\SmtpDriver::class);
        $mockDriver->shouldReceive('send')->once()->andReturn(true);
        $this->app->instance(\Notifluxion\LaravelNotify\Drivers\Email\SmtpDriver::class, $mockDriver);
        
        $strategy = $this->app->make('notify.strategy.database');
        $strategy->process();

        // Let's verify our strategy engine upgraded the status to completed or failed
        // Since the actual driver is a dummy returning true without Exceptions, it completes.
        
        $postProcessingRow = DB::table('scheduled_notifications')->first();
        $this->assertNotEquals('pending', $postProcessingRow->status, 'The queue processor did not update the row status');
    }
}
