<?php

namespace Notifluxion\LaravelNotify\Contracts;

interface WhatsappDriverInterface extends DriverInterface
{
    /**
     * Define the template name or business ID using this driver.
     *
     * @param string $template
     * @return self
     */
    public function template(string $template): self;
}
