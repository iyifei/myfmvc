<?php
/**
 * redis操作类
 * User: myf
 * Date: 2017/11/2
 * Time: 16:12
 */

namespace Myf\Libs;


class Redis
{

    private static $_instances = [];

    /**
     * 获取redis的单利
     * @param int $db
     * @param array $config 配置，可选，默认是读取redis.dsn
     * @return mixed|\Redis
     * @throws \Exception
     */
    public static function getInstance($db = null,$config=[])
    {
        if(empty($config)){
            $config = config("redis.dsn");
        }
        if(!isset($db)){
            $db = $config['db'];
        }
        $db = intval($db);
        if ($db < 0) {
            throw new \Exception("invalid db idx: $db");
        }
        if (array_key_exists($db, self::$_instances)) {
            $instance = self::$_instances[$db];
            // important: reset db idx
            $instance->select($db);
            return $instance;
        }
        $instance = new \Redis();
        $instance->connect($config['host'], $config['port']);
        $instance->auth($config['password']);
        $instance->select($db);
        self::$_instances[$db] = $instance;
        return $instance;
    }

}