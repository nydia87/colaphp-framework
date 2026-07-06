<?php

namespace ColaPHP\Framework\Driver;

class RedisConnect
{

    protected $config = [];

    protected $connected = false;

    protected $redis_client = null;

    public function init($config = [])
    {
        if ( !  extension_loaded('redis') ) {
            halt('extension not support: redis');
        }

        $redis_config = [
            'host' => $config['redis_config']['host'] ?? env('redis.host'),
            'port' => $config['redis_config']['port'] ?? env('redis.port'),
            'password' => $config['redis_config']['password'] ?? env('redis.password'),
            'select' => $config['redis_config']['select'] ?? env('redis.select'),
            'timeout' => $config['redis_config']['timeout'] ?? env('redis.timeout'),
            'persistent' => $config['redis_config']['persistent'] ?? env('redis.persistent')
        ];

        unset($config['redis_config']);
        $this->config = array_merge($config, $redis_config);
    }

    public function open($savePath, $sessName)
    {
        $this->redis_client = new \Redis();

        // 建立连接
        $func = $this->config['persistent'] ? 'pconnect' : 'connect';
        $this->connected = $this->redis_client->{$func}($this->config['host'], $this->config['port'], $this->config['timeout']);

        if ('' != $this->config['password']) {
            $this->redis_client->auth($this->config['password']);
        }

        if (0 != $this->config['select']) {
            $this->redis_client->select($this->config['select']);
        }

        return true;
    }

    public function isConnected()
    {
        return $this->connected;
    }
}