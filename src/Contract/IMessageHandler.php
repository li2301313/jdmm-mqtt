<?php

namespace Jdmm\Mqtt\Contract;

interface IMessageHandler
{
    public function handleAsync($message, $eventHandlers);
}