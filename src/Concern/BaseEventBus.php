<?php

namespace Jdmm\Mqtt\Concern;

use Jdmm\Mqtt\Contract\IEventBus;
use Jdmm\Mqtt\Contract\IEventHandler;
use Jdmm\Mqtt\Contract\IMessageHandler;
use Jdmm\Mqtt\MessageHandler;

/**
 * Class BaseEventBus
 *
 * @package Jdmm\EmqxQueue\Concern
 */
abstract class BaseEventBus implements IEventBus
{
    /**
     * @var array
     */
    protected $options;

    /**
     * 消息处理逻辑
     * @var IMessageHandler
     */
    private $messageHandler;

    public abstract function startListen($conf);

    /**
     * @param $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function subscribe(string $topic, IEventHandler $handler)
    {
        $this->topics[$topic][] = $handler;
    }

    public function setMessageHandler(IMessageHandler $messageHandler) {
        $this->messageHandler = $messageHandler;
    }

    public function getMessageHandler(): IMessageHandler {
        if(empty($this->messageHandler)) {
            $this->messageHandler = new MessageHandler();
        }
        return $this->messageHandler;
    }

}