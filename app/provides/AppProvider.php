<?php

namespace App\provides;

use DI\Container;
use Psr\SimpleCache\CacheInterface;
use Redis;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;

class AppProvider
{
    public function register(Container $container)
    {
        $container->set(CacheInterface::class, function () {
            $redis = new Redis;
            $host = $_ENV['REDIS_HOST'] ?? '127.0.0.1';
            $port = $_ENV['REDIS_PORT'] ?? 6379;

            try {
                $redis->connect($host, $port);
            } catch (\Throwable $e) {
                throw $e;
            }

            $adapter = new RedisAdapter($redis);

            return new Psr16Cache($adapter);
        });
    }
}
