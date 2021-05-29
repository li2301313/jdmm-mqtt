<?php

namespace Jdmm\Mqtt;

use Jdmm\Mqtt\Contract\IMessageHandler;
use Jdmm\Mqtt\Data\BaseData;

class MessageHandler implements IMessageHandler
{
    public function handleAsync($message, $eventHandlers)
    {
        //try {
        $data = BaseData::jsonUnSerialize($message);

        if ($data) {
            // $data->setKey($message->key);
            foreach ($eventHandlers as $handler) {
                if (is_string($handler)) {
                    $obj = GlobalClass::get($handler);
                    $obj->handleAsync($data);
                } else {
                    $handler->handleAsync($data);
                }
            }
        }
            /*
        } catch (\Exception $e) {
            log('kafka-cumtomer-error : '. $e->getMessage());
        } catch (\Error $e) {
            log('kafka-customer-error: '. $e->getMessage());
        }
            */
    }
}