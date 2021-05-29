<?php

namespace Jdmm\Mqtt;

class GlobalClass
{
    private static $instance = [];

    /**
     * 实例化类
     * @author wangc
     * @param $className
     * @return mixed
     */
    public static function get($className)
    {
        if(empty(self::$instance[$className])) {
            self::$instance[$className] = new $className;
        }
        return self::$instance[$className];
    }
}