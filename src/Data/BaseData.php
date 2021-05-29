<?php

namespace Jdmm\Mqtt\Data;

use Jdmm\Mqtt\Contract\IData;
use Jdmm\Mqtt\Contract\IEvent;

/**
 * Class BaseEvent
 * 消息队列消息体
 *
 * @package Jdmm\EmqxQueue\Data
 */
class BaseData implements IEvent, IData, \JsonSerializable
{
    /**
     * @var string 消息id
     */
    private $guid;

    /**
     * @var string 消息名称
     */
    private $name;

    /**
     * 消息创建时间
     * @var integer
     */
    private $time;

    private $key = '';

    /**
     * BaseEvent constructor.
     *
     * @param string $name
     * @param $payload
     * @param string $guid
     * @param int $time
     * @param string $key
     */
    public function __construct(string $name = '', $payload = '', string $key = '', string $guid = '', int $time = 0)
    {
        if (empty($guid)) {
            $guid = $this->GUID();
        }

        $this->setGuid($guid);
        $this->setTime($time);
        $this->setName($name);

        if($key) {
            $this->setKey($key);
        }
        if($payload) {
            $this->setPayload($payload);
        }
    }

    public function commonKey($key) {
        return $this->$key ?? null;
    }


    /*public function __call($name, $arguments)
    {
        if(substr($name, 0, 3) == 'get') {
            $payload = $this->commonKey('payload');
            return $payload->$name();
        }
    }*/

    private function GUID():string
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public function setGuid(string $guid) {
        $this->guid = $guid;
    }

    public function setTime($time = null) {
        if(!$time) $time = time();
        $this->time = $time;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function setPayload(BaseEvent $payload) {
        $this->payload = $payload;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param array $tracer
     */
    /*public function setTracer(array $tracer): void
    {
        $this->tracer = $tracer;
    }*/

    public function toArray($all = false)
    {
        if(!$all) {
            $payload = $this->commonKey('payload');
            if(!$payload) {
                return $payload;
            }
            if(!is_object($payload)) {
                return $payload;
            }

            return $payload->toArray();
        }

        $data = [
            'guid'  => $this->commonKey('guid'),
            'name'  => $this->commonKey('name'),
            'project'  => $this->commonKey('project'),
            'time'  => $this->commonKey('time'),
            'key'   => $this->commonKey('key'),
            'payload'  => $this->commonKey('payload'),
        ];
        return $data;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->toArray(true);
    }

    static public function jsonUnSerialize($json)
    {
        $data = json_decode($json, true);
        $obj = new self();
        foreach($data as $key => $val) {
            if($key == 'payload') {
                $val = BaseEvent::jsonUnSerialize($val);
            }
            $obj->$key = $val;
        }
        return $obj;
    }
}