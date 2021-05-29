<?php
namespace Jdmm\Mqtt\Data;

class BaseEvent implements \JsonSerializable
{
    //topic name
    private $_name;
    
    //数据内容
    private $_payload;

    private $_clientID;

    private $_key;
    
    public function __construct(string $name = '', $payload = '', $clientId = '')
    {
        if($name) $this->_name = $name;
        if($payload) $this->_payload = $payload;
        if($clientId) $this->_clientID = $clientId;
    }

    public function commonKey($key) {
        return $this->$key;
    }

    public function toArray() {
        //设置msg的情况
        if($this->_payload) {
            return $this->_payload;
        }

        //根据方法获取的情况
        /*$data = [];
        $methods = get_class_methods($this);
        foreach($methods as $method) {
            if(substr($method, 0, 3) == 'get') {
                $key = lcfirst(substr($method, 3));
                $data[$key] = $this->$method();
            }
        }
        if($data) {
            $this->_payload = $data;
            return $data;
        }*/

        //自动赋值的情况
        $data = [];
        foreach($this as $key => $val) {
            if(in_array($key, ['_name', '_payload', '_key'])) {
                continue;
            }
            $data[$key] = $val;
        }
        if($data) {
            $this->_payload = $data;
            return $data;
        }
        return [];
    }

    public function jsonSerialize() {
        return $this->toArray();
    }

    public static function jsonUnSerialize($json) {
        if(!is_array($json) and !is_object($json)) {
            return $json;
        }
        $data = $json;

        $obj = new self();
        foreach($data as $key=>$val) {
            $obj->$key = $val;
        }
        return $obj;
    }
}