<?php

namespace Notifluxion\LaravelNotify\Contracts;

interface EmailDriverInterface extends DriverInterface
{
    /**
     * Set the sender address logic.
     *
     * @param string $address
     * @param string|null $name
     * @return self
     */
    public function from(string $address, ?string $name = null): self;
}
