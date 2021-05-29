<?php

namespace Jdmm\Mqtt\Contract;


Interface IEventHandler
{
    public function handleAsync(IEvent $event): bool;
}