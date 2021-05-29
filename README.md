# 消息队列

## 介绍
jdmmswoft/queue是一个基于swoft框架的消息队列组件

## 安装

```bash
composer require jdmmswoft/queue
```


## 使用

### 发布消息

要发布消息到消息队列，代码编写人员主要编写消息体，通过消息组件发布此消息体即可，具体步骤如下：

1.添加host到容器中,将容器的启动脚本start.bat，修改为
```bash
docker stop swoft-name
docker run ^
-it ^
--name swoft-name ^
--rm ^
-v %~dp0/src:/usr/app ^
-p 9082:80 ^
--add-host="CDH-01:192.168.2.164" ^
--add-host="CDH-02:192.168.2.165" ^
--add-host="CDH-03:192.168.2.166" ^
ldjbenben/swoole:4.4-php7.3-cli ^
php /usr/bin/swoftcli.phar run -b /usr/app/bin/swoft -w /usr/app/app,/usr/app/config

```
2.配置
bean.php
 ```php
'mmQueue' => [
    'type' => Jdmm\Queue\EventBus::KAFKA,
    'options' => [
        'metadataBrokerList' => 'CDH-01:9092,CDH-02:9092,CDH-03:9092', // Kafka服务器, 多台可以使用逗号分隔 
    ]
]
 ```

3.编写消息体类

```
<?php

namespace App\Event\Entity;

use Jdmm\Queue\Data\BaseEvent;

class SupplierCreateEvent extends BaseEvent // 必须继承BaseEvent
{
	 const EVENT_NAME = "supplier_create";
	 private $id;

	 /**
	  * @var int $id 供应商id
	  */
	 public function __construct(int $id)
	 {
		parent::__construct(self::EVENT_NAME);
	 }

	  public function getId(): int
	  {
		return $this->id;
	  }
}
```

4.发送消息
 ```php

/**
 * Class SupplierService
 *
 * @since 2.0
 *
 * @Service()
 */
class SupplierService implements SupplierInterface
{
	/**
	 * @Inject('mmQueue')
	 * @var BaseEventBus
	 */
	private $queue;
	
	/**
     * @param int   $id
     * @param mixed $type
     * @param int   $count
     *
     * @return array
     */
    public function create(int $id, $type, int $count = 10): array
    {
		...
		// 发布消息
		$this->queue->publish(new SupplierCreateEvent(100));
		...
    }
}
 ```


### 接收消息

1.在 \App\Queue\Handler 目录中创建一个事件监听类并实现接口 Jdmm\Queue\Contract\IEventHandler，已SupplierHandler为例
```php
<?php

namespace App\Event\Consumer\Handler;

use App\Queue\Event\SupplierCreateEvent;
use Jdmm\Queue\Contract\IEvent;
use Jdmm\Queue\Contract\IEventHandler;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * Class DemoConsumer
 * 
 * @Bean() // 注解必须要有
 *
 */
class SupplierHandler implements IEventHandler // 实现接口
{
    public function handleAsync(IEvent $event): bool
    {
	    /**
	     * @var SupplierCreateEvent $event
	     */  
	     var_dump($event);
	     echo "id: {$event->getID()}\n";
	     return true;
    }
}
```

2.配置Process, 在http服务中，增加一条任意key的配置项，指定为Jdmm\Queue\Process\MessageQueueProcess::class
bean.php
 ```php
'httpServer' => [
  'class' => HttpServer::class,
  'port' => 80,
  'listener' => [
      'rpc' => bean('rpcServer')
   ],
   'process' => [
      'message' => bean(Jdmm\Queue\Process\MessageQueueProcess::class)
   ]
]
 ```

3.添加host到容器中,将容器的启动脚本start.bat，修改为
```bash
docker stop swoft-name
docker run ^
-it ^
--name swoft-name ^
--rm ^
-v %~dp0/src:/usr/app ^
-p 9081:80 ^
--add-host="CDH-01:192.168.2.164" ^
--add-host="CDH-02:192.168.2.165" ^
--add-host="CDH-03:192.168.2.166" ^
ldjbenben/swoole:4.4-php7.3-cli ^
php /usr/bin/swoftcli.phar run -b /usr/app/bin/swoft -w /usr/app/app,/usr/app/config

```

4.配置消息队列
bean.php
```php
'mmQueue'  => [
    'type' => Jdmm\Queue\EventBus::KAFKA,
    'options' => [
        'metadataBrokerList' => 'CDH-01:9092,CDH-02:9092,CDH-03:9092',
        'handlers' => [
	        SupplierCreateEvent::EVENT_NAME => [
		        SupplierCreateHandler::class
	        ]
        ]
    ]
]

```
