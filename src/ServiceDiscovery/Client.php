<?php

namespace SDPMlab\AnserServiceDiscovery\ServiceDiscovery;

use SDPMlab\AnserServiceDiscovery\Config\ServiceDiscoveryConfig;

class Client
{
    protected static $client;

    protected static $consulClient;

    /**
     * Get Guzzle7 HTTP Client shared instance
     *
     * @return \GuzzleHttp\Client
     */
    public static function getHttpClient(): \GuzzleHttp\Client
    {
        if(!self::$client instanceof \GuzzleHttp\Client) {
            self::$client = new \GuzzleHttp\Client();
        }
        return self::$client;
    }

    public static function getConsulClient()
    {
        if(!self::$consulClient instanceof \DCarbone\PHPConsulAPI\Consul) {
            self::$consulClient = new \DCarbone\PHPConsulAPI\Consul(
                ServiceDiscoveryConfig::getConsulConfig()
            );
        }
        return self::$consulClient;
    }
}
