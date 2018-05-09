<?php

namespace Yugo\SMSGateway\Interfaces;

interface SMS
{
    /**
     * Send single message to one number.
     *
     * @param string $destination
     * @param string $text
     *
     * @return void
     */
    public function send(array $destination, string $text);
}
