<?php

namespace Jdmm\Mqtt\Adapter;

use Jdmm\Mqtt\Contract\IData;
use Jdmm\Mqtt\Concern\BaseEventBus;
use Jdmm\Mqtt\phpMQTT;
use Swlib\SaberGM;
use Swlib\Http\ContentType;

/**
 * Class EmqxAdapter
 *
 * @package Jdmm\EmqxQueue\Adapter
 *
 * options:
 *      metadataBrokerList: string 集群地址如 "192.168.1.2:9092,192.168.1.3:9092"
 *      handlers: 主题监听回调数组 ["topic1" => Handler1:class]
 *
 * @since 1.0.0
 */
class EmqxAdapter extends BaseEventBus
{
    //连接信息
    private $conf;

    //消息
    private $topics;

    /**
     * 发布消息
     * @author wangc
     * @param IData $data
     * @param array $conf
     * @return bool
     */
    public function publish(IData $data, array $conf): bool
    {
        //获取配置
        // $conf = $this->_getConf();
        //连接服务器
        // $conn = $this->_getConnect($conf);
        $data->project = $conf['name'];

        /*if($conn->connect(true, NULL, $conf['username'], $conf['password'])){
            $topic = $this->_getTopic($data->commonKey('name'));
            $conn->publish($topic, json_encode($data),$conf['qos'],$conf['retain']);
            $conn->close();
        } else {
            echo "Connect Time out!\r\n";
        }*/
        //改用http发送
        $postData = [
            'topic' => $this->_getTopic($data->commonKey('name')),
            "payload" => json_encode($data,JSON_UNESCAPED_UNICODE),
            "qos" => $conf['qos'],
            "retain" => $conf['retain'],
            "clientid" => $conf['clientID']
        ];
        $options = [
            // 'json' => 'json',
            'headers' => [
                'Content-Type' => ContentType::JSON
            ],
            'timeout' => 2,
            'auth' =>[
                'username' => $conf['username'],
                'password' => $conf['password']
            ]
        ];
        // $url = '192.168.3.199:8081/api/v4/mqtt/publish';
        $url = $conf['server'].':'.$conf['port'].'/api/v4/mqtt/publish';
        SaberGM::post($url,$postData,$options);
        // $result = SaberGM::post($url,$postData,$options);
        // $body = $result->getParsedJsonArray();
        // $statusCode = $result->getStatusCode();
        return true;
    }

    /**
     * 开启监听进程
     * @author wangc
     * @param $conf
     * @return mixed|void
     */
    public function startListen($conf)
    {

        //获取配置
        // $conf = $this->_getConf();
        $this->conf = $conf;
        //连接服务器
        $conn = $this->_getConnect($this->conf);

        if(!$conn->connect(true, NULL, $this->conf['username'], $this->conf['password'])) {
            exit(1);
        }
        $topics = [];
        foreach ($this->options['handlers'] as $key => $val){
            $topics[$key] = [
                'qos' => $this->conf['qos'],
                'function' => function ($topic, $msg) use($val) {
                    $eventHandlers = $val;
                    if ($eventHandlers) {
                        $this->getMessageHandler()->handleAsync($msg, $eventHandlers);
                    }
                }
            ];
        }

        if(empty($topics)){
            $conn->close();
        }
        $conn->subscribe($topics, 0);

        while($conn->proc()){

        }

        $conn->close();
    }

    /**
     * 获取配置
     * @author wangc
     * @return \RdKafka\Conf
     */
    /*private function _getConf() {
        if(!$this->conf) {
            $this->conf = [
                'server' => config('mqtt.server'),
                'port' => config('mqtt.port'),
                'clientID' => config('mqtt.clientID').time(),
                'qos' => config('mqtt.qos'),
                'debug' => config('mqtt.debug'),
                'keepalive' => config('mqtt.keepalive'),
                'cafile' => config('mqtt.cafile'),
                'username' => config('mqtt.username'),
                'password' => config('mqtt.password'),
                'name' => config('mqtt.name')
            ];
            if(!empty(config('mqtt.cafile'))){
                $this->conf['cafile'] = __DIR__.'/'.config('mqtt.cafile');
            }
        }
        return $this->conf;
    }*/

    /**
     * 连接到mqtt服务器
     * @author wangc
     * @param $conf
     * @return phpMQTT
     */
    private function _getConnect($conf) {
        return new phpMQTT($conf['server'], $conf['port'], $conf['clientID'], $conf['keepalive'], $conf['debug'], $conf['cafile']);
    }

    private function _getTopic($name) {
        if(empty($this->topics[$name])) {
            $this->topics[$name] = $name;
        }
        return $this->topics[$name];
    }
}