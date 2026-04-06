<?php

namespace Notifluxion\LaravelNotify\Tests\Feature;

use Notifluxion\LaravelNotify\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class DatabaseIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    public function test_database_tables_are_created_successfully_in_mysql()
    {
        $this->assertTrue(Schema::hasTable('notifications'));
        $this->assertTrue(Schema::hasTable('notification_logs'));
        $this->assertTrue(Schema::hasTable('scheduled_notifications'));
    }

    public function test_can_insert_into_scheduled_notifications()
    {
        \DB::table('scheduled_notifications')->insert([
            'driver' => 'Notifluxion\LaravelNotify\Drivers\Email\SmtpDriver',
            'notification' => serialize(['test' => 'data']),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->assertDatabaseHas('scheduled_notifications', [
            'status' => 'pending'
        ]);
    }
}
