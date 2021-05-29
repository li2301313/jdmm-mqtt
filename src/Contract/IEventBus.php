<?php

namespace Jdmm\Mqtt\Contract;

use Jdmm\Mqtt\Contract\IData;

interface IEventBus
{
    /**
     * 消息推送
     * @author wangc
     * @param \Jdmm\Mqtt\Contract\IData $data
     * @param array $conf
     * @return mixed
     */
    public function publish(IData $data, array $conf);

    /**
     * 消息订阅
     * @author wangc
     * @param string $topic
     * @param IEventHandler $handler
     * @return mixed
     */
    public function subscribe(string $topic, IEventHandler $handler);

    /**
     * 监听
     * @author wangc
     * @return mixed
     */
    public function startListen($conf);
}