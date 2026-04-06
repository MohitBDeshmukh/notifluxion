<?php

namespace Notifluxion\LaravelNotify\Tests\Feature;

use Notifluxion\LaravelNotify\Tests\TestCase;

class NotificationCommandTest extends TestCase
{
    public function test_install_command_runs_successfully()
    {
        $this->artisan('notify:install')
             ->expectsConfirmation('Would you like to run the migrations now?', 'no')
             ->assertSuccessful();
    }
}
