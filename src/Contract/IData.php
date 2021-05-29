<?php

namespace Jdmm\Mqtt\Contract;

interface IData
{
    public function commonKey($key);

    public function toArray();
}