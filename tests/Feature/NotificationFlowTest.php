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

        // If the driver is executed, our dummy sender returns true!
        // We will mock the driver slightly to ensure the method gets called, or just assert it throws no exceptions.
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
        $this->assertEquals('Async Database Message!', $unserialized['message']);
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
        $strategy = $this->app->make('notify.strategy.database');
        $strategy->process();

        // Let's verify our strategy engine upgraded the status to completed or failed
        // Since the actual driver is a dummy returning true without Exceptions, it completes.
        
        $postProcessingRow = DB::table('scheduled_notifications')->first();
        $this->assertNotEquals('pending', $postProcessingRow->status, 'The queue processor did not update the row status');
    }
}
