<?php

namespace SDPMlab\AnserServiceDiscovery\Config;

class ServiceDiscoveryConfig
{
    /**
     * 已完成實例化的Consul設定
     *
     * @var \DCarbone\PHPConsulAPI\Config
     */
    protected static $consulConfig;

    /**
     * 需要被探索的服務名稱
     *
     * @var array<string>
     */
    protected static array $defaultServiceGroup;

    /**
     * Consul Server IP Address and port
     *
     * @var string
     */
    protected static $Address = 'http://localhost:8500';

    /**
     * HTTP Scheme [http or https]
     *
     * @var string
     */
    protected static $Scheme = 'http';

    /**
     * Consul Server DataCenter
     *
     * @var string
     */
    protected static $Datacenter = '';

    /**
     * Consul Server HttpAuth
     *
     * @var string
     */
    protected static $HttpAuth = '';

    /**
     * Amount of time to wait on certain blockable endpoints.  go time duration string format.
     *
     * @var string
     */
    protected static $WaitTime = '0s';

    /**
     * Default auth token to use
     *
     * @var string
     */
    protected static $Token = '';

    /**
     * File containing auth token string
     *
     * @var string
     */
    protected static $TokenFile = '';

    /**
     * If set to true, ignores all SSL validation
     *
     * @return bool
     */
    protected static bool $InsecureSkipVerify = false;

    /**
     * Path to ca cert file, see http://docs.guzzlephp.org/en/latest/request-options.html#verify
     *
     * @var string
     */
    protected static $CAFile = '';

    /**
     * Path to client public key.  if set, requires KeyFile also be set
     *
     * @var string
     */
    protected static $CertFile = '';

    /**
     * Path to client private key.  if set, requires CertFile also be set
     *
     * @var string
     */
    protected static $KeyFile = '';

    /**
     * PHP json encode opt value to use when serializing requests
     *
     * @var integer
     */
    protected static $JSONEncodeOpts = 0;

    /**
     * 請求間隔
     *
     * @var integer
     */
    public static $ReloadTime = 10;


    public function __construct()
    {

    }

    /**
     * consul config
     *
     * @param array $config
     * @return void|null
     */
    public static function setDefaultConfig(array $config = null)
    {
        if (is_null($config)) {
            return;
        }

        foreach($config as $key => $value) {
            $confName = ucfirst($key);
            if (property_exists(__CLASS__, $confName)) {
                self::$$confName = $value;
            }
        }
    }

    /**
     * Get the consul config (unrealized)
     *
     * @return array<string,mixed>
     */
    public static function getDefaultConfig()
    {
        $config = [
            'Address'            => self::$Address,                          // [required]
            'Scheme'             => self::$Scheme,                           // [optional] defaults to "http"
            'Datacenter'         => self::$Datacenter,                       // [optional]
            'HttpAuth'           => self::$HttpAuth,                         // [optional]
            'WaitTime'           => self::$WaitTime,                         // [optional] amount of time to wait on certain blockable endpoints.  go time duration string format.
            'Token'              => self::$Token,                            // [optional] default auth token to use
            'TokenFile'          => self::$TokenFile,                       // [optional] file containing auth token string
            'InsecureSkipVerify' => self::$InsecureSkipVerify,               // [optional] if set to true, ignores all SSL validation
            'CAFile'             => self::$CAFile,                           // [optional] path to ca cert file, see http://docs.guzzlephp.org/en/latest/request-options.html#verify
            'CertFile'           => self::$CertFile,                         // [optional] path to client public key.  if set, requires KeyFile also be set
            'KeyFile'            => self::$KeyFile,                          // [optional] path to client private key.  if set, requires CertFile also be set
            'JSONEncodeOpts'     => self::$JSONEncodeOpts,                   // [optional] php json encode opt value to use when serializing requests
        ];

        foreach ($config as $key => $value) {
            if($value == '' || is_null($value) || !isset($config[$key])) {
                if(is_bool($value)) {
                    continue;
                }
                unset($config[$key]);
            }
        }

        return $config;
    }

    /**
     * Get Consul API Config shared instance
     *
     * @return \DCarbone\PHPConsulAPI\Config
     */
    public static function getConsulConfig(): \DCarbone\PHPConsulAPI\Config
    {
        if(!self::$consulConfig instanceof \DCarbone\PHPConsulAPI\Config) {
            $config = self::getDefaultConfig();

            $config["HttpClient"] = \SDPMlab\AnserServiceDiscovery\ServiceDiscovery\Client::getHttpClient();
    
            self::$consulConfig = new \DCarbone\PHPConsulAPI\Config($config);
        }
        
        return self::$consulConfig;
    }

    /**
     * 設定需被訪問的服務名稱
     *
     * @param array<string> $defaultServiceGroup
     * @return void
     */
    public static function setDefaultServiceGroup(array $defaultServiceGroup)
    {
        self::$defaultServiceGroup = $defaultServiceGroup;
    }

    /**
     * 取得已設定將被訪問的服務名稱
     *
     * @return array
     */
    public static function getDefaultServiceGroup(): array
    {
        return self::$defaultServiceGroup;
    }
}
