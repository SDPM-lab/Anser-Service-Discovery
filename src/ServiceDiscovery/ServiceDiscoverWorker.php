<?php

namespace SDPMlab\AnserServiceDiscovery\ServiceDiscovery;

use AnserGateway\Worker\WorkerRegistrar;
use AnserGateway\Autoloader;
use Workerman\Timer;
use Workerman\Worker;
use SDPMlab\AnserServiceDiscovery\Config\ServiceDiscoveryConfig;
use SDPMlab\AnserServiceDiscovery\ServiceDiscovery\DiscoveryService;

class ServiceDiscoverWorker extends WorkerRegistrar
{
    /**
     * DiscoveryService實體，運行服務探索主要方法
     *
     * @var \SDPMlab\AnserServiceDiscovery\ServiceDiscovery\DiscoveryService
     */
    protected $discoveryService;

    /**
     * 須被探索的服務
     *
     * @var array
     */
    protected array $discoveryServiceGroup;

    /**
     * 從Consul Server探索的可訪問服務
     *
     * @var array<string,array<\SDPMlab\Anser\Service\ServiceSettings>>
     */
    protected array $realDiscoveryService;

    public function __construct()
    {
        Autoloader::$instance->appRegister();
        Autoloader::$instance->composerRegister();
        $this->discoveryService      = new DiscoveryService();
        $this->discoveryServiceGroup = ServiceDiscoveryConfig::getDefaultServiceGroup();

    }

    public function initWorker(): Worker
    {
        $worker             = new Worker();
        $worker->name       = 'ServiceDiscovery';
        $worker->reloadable = false;

        $discoveryService      = $this->discoveryService;
        $discoveryServiceGroup = $this->discoveryServiceGroup;

        $worker->onWorkerStart = static function () use ($discoveryService, $discoveryServiceGroup) {
            Timer::add(
                ServiceDiscoveryConfig::$ReloadTime,
                static function () use ($discoveryService, $discoveryServiceGroup) {
                    $discoveryService->doServiceDiscovery();
                }
            );
        };

        return $worker;
    }
}
