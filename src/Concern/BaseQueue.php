<?php

namespace Jdmm\Mqtt\Concern;


/**
 * Class Queue
 *
 * @package Jdmm\EmqxQueue
 */
abstract class BaseQueue
{
    const IOT = 'IoT';

    /**
     * @var array
     */
    protected $options;

    /**
     * @var string
     */
    protected $type = self::IOT;

    /**
     * @var BaseEventBus
     */
    protected $eventBus;

    /**
     * 调用不存在的方法时候调用
     * @author wangc
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (isset($this->eventBus)) {
            if ($this->eventBus->getOptions() == null) {
                $this->eventBus->setOptions($this->options);
            }
            if (empty($arguments)) {
                return call_user_func([$this->eventBus, $name]);
            } else {
                return call_user_func([$this->eventBus, $name], ...$arguments);
            }
        } else {
            throw new \Exception('property eventBus not set');
        }
    }
}