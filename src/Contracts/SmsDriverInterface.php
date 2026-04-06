<?php

namespace Notifluxion\LaravelNotify\Contracts;

interface SmsDriverInterface extends DriverInterface
{
    /**
     * Define the sender ID / Number.
     *
     * @param string $sender
     * @return self
     */
    public function from(string $sender): self;
}
