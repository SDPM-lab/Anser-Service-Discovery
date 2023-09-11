<?php

namespace SDPMlab\AnserServiceDiscovery\ServiceDiscovery;

use SDPMlab\AnserServiceDiscovery\Config\ServiceDiscoveryConfig;
use SDPMlab\AnserServiceDiscovery\ServiceDiscovery\Client;
use SDPMlab\Anser\Service\ServiceSettings;
use SDPMlab\Anser\Service\Action;
use Psr\Http\Message\ResponseInterface;
use SDPMlab\Anser\Service\ConcurrentAction;

class DiscoveryService
{
    /**
     * consul 實體
     *
     * @var \DCarbone\PHPConsulAPI\Consul
     */
    protected $consulClient;

    /**
     * 從Consul Server探索的可訪問服務
     *
     * @var array<string,array<\SDPMlab\Anser\Service\ServiceSettings>>
     */
    protected static array $services = [];

    protected static array $verifyServices = [null];

    public function __construct()
    {
        // init config
        if(file_exists(PROJECT_CONFIG."ServiceDiscovery.php")) {
            require_once PROJECT_CONFIG."ServiceDiscovery.php";
        } else {
            ServiceDiscoveryConfig::setDefaultConfig();
        }

        $this->consulClient = Client::getConsulClient();
    }

    /**
     * Request to Consul, get the service info.
     *
     * @param string $serviceName
     * @return \DCarbone\PHPConsulAPI\Health\ServiceEntriesResponse
     */
    public function getService($serviceName): \DCarbone\PHPConsulAPI\Health\ServiceEntriesResponse
    {
        $service = $this->consulClient->Health()->Service($serviceName, '', true);
        
        return $service;
    }

    /**
     * 集成可訪問服務至陣列
     *
     * @param array $services
     * @return void
     */
    public function setDiscoveryService(array $servicesGroup): void
    {
        $this->cleanServices();
        foreach ($servicesGroup as $serviceName) {

            $services =  $this->getService($serviceName)->getValue();

            if(count($services) > 1) {
                $this->setServices($services);
            } else {
                $this->setService($services);
            }
        }

        if (self::$services !== self::$verifyServices) {
            $this->cleanVerifyServices();
            self::$verifyServices = self::$services;

            // do 真實的service list 更新 ....
            var_dump("Service List執行更新開始");
        }
    }

    /**
     * 如服務回傳多組service的話則使用該方法
     *
     * @param array $services
     * @return void
     */
    public function setServices(array $services): void
    {
        foreach ($services as $service) {
            $serviceEntity        = $service->Service;
            $serviceTags          = $serviceEntity->Tags;
            $serviceName          = $serviceEntity->Service;
            $serviceAddress       = $serviceEntity->Address;
            $servicePort          = $serviceEntity->Port;
            $serviceSchemeIsHttp  = false;

            foreach ($serviceTags as $tag) {
                if (strpos($tag, 'http_scheme=') === 0) {
                    $serviceScheme = substr($tag, strlen('http_scheme='));
                    $serviceSchemeIsHttp = strtolower($serviceScheme) == 'http' ? false : true;
                    break;
                }
            }

            self::$services[$serviceName][] = [
                $serviceName,
                $serviceAddress,
                $servicePort,
                $serviceSchemeIsHttp
            ];

        }
    }

    /**
     * 如服務只回傳一組service的話則使用該方法
     *
     * @param array $service
     * @return void
     */
    public function setService($service): void
    {
        $serviceEntity        = $service[0]->Service;
        $serviceTags          = $serviceEntity->Tags;
        $serviceName          = $serviceEntity->Service;
        $serviceAddress       = $serviceEntity->Address;
        $servicePort          = $serviceEntity->Port;
        $serviceSchemeIsHttp  = false;

        foreach ($serviceTags as $tag) {
            if (strpos($tag, 'http_scheme=') === 0) {
                $serviceScheme = substr($tag, strlen('http_scheme='));
                $serviceSchemeIsHttp = strtolower($serviceScheme) == 'http' ? false : true;
                break;
            }
        }

        self::$services[$serviceName][] = [
            $serviceName,
            $serviceAddress,
            $servicePort,
            $serviceSchemeIsHttp
        ];
    }

    public function cleanServices(): void
    {
        self::$services = [];
    }

    public function cleanVerifyServices(): void
    {
        self::$verifyServices = [];
    }
}
