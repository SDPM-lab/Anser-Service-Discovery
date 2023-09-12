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
     * 從Consul Server探索的可訪問服務
     *
     * @var array<string,array<string,string>>
     */
    protected static array $services = [];

    /**
     * 用於比對的可訪問服務，參照$service屬性
     *
     * @var array
     */
    protected static array $verifyServices = [null];

    public function __construct()
    {
        // init config
        if(file_exists(PROJECT_CONFIG."ServiceDiscovery.php")) {
            require_once PROJECT_CONFIG."ServiceDiscovery.php";
        } else {
            ServiceDiscoveryConfig::setDefaultConfig();
        }
    }

    /**
     * 執行服務探索步驟
     * 於ServiceDiscoverWorker被呼叫
     *
     * @return void|null
     */
    public function doServiceDiscovery()
    {
        $serviceGroup = ServiceDiscoveryConfig::getDefaultServiceGroup();
        $this->setDiscoveryService($serviceGroup);

        if(!$this->isNeedToUpdateServiceList()){
            return;
        }

        var_dump("update Service");
        // do update anser-action service list

    }

    /**
     * Request to Consul, get the service info.
     *
     * @param string $serviceName
     * @return \DCarbone\PHPConsulAPI\Health\ServiceEntriesResponse
     */
    public function getService($serviceName): \DCarbone\PHPConsulAPI\Health\ServiceEntriesResponse
    {
            $service = Client::getConsulClient()->Health()->Service($serviceName, '', true);
        
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
            
            if(empty($services) || count($services) == 0){
                continue;
            }

            if(count($services) > 1) {
                $this->setServices($services);
            } else {
                $this->setService($services);
            }
            
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

    /**
     * 確認是否需要更新真實的服務列表
     * 如新一輪的服務探索取得的服務與原有不一致，則進行ServiceList更新
     * @return boolean
     */
    protected function isNeedToUpdateServiceList(): bool
    {
        if (self::$services !== self::$verifyServices) {
            $this->cleanVerifyServices();
            self::$verifyServices = self::$services;
            return true;
        }
        return false;
    }

    /**
     * 重置$service屬性
     *
     * @return void
     */
    protected function cleanServices(): void
    {
        self::$services = [];
    }

    /**
     * 重置$verifyServices屬性
     *
     * @return void
     */
    protected function cleanVerifyServices(): void
    {
        self::$verifyServices = [null];
    }
}
