<?php

namespace Jdmm\Mqtt\Contract;


interface IEvent
{
    public function commonKey($key);

    public function toArray($all = false);
}