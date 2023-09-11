<?php
namespace Config;

use SDPMlab\AnserServiceDiscovery\Config\ServiceDiscoveryConfig;

ServiceDiscoveryConfig::setDefaultConfig([
    'Address' => 'host.docker.internal:8500', // [required]
    'Scheme' => 'http',                       // [optional] defaults to "http"
    'Datacenter' => '',                       // [optional]
    'HttpAuth' => '',                         // [optional]
    'WaitTime' => '',                         // [optional] amount of time to wait on certain blockable endpoints.  go time duration string format.
    'Token' => '',                            // [optional] default auth token to use
    'TokenFile' => '',                        // [optional] file containing auth token string
    'InsecureSkipVerify' => false,            // [optional] if set to true, ignores all SSL validation
    'CAFile' => '',                           // [optional] path to ca cert file, see http://docs.guzzlephp.org/en/latest/request-options.html#verify
    'CertFile' => '',                         // [optional] path to client public key.  if set, requires KeyFile also be set
    'KeyFile' => '',                          // [optional] path to client private key.  if set, requires CertFile also be set
    'JSONEncodeOpts' => 0,                    // [optional] php json encode opt value to use when serializing requests
    'ReloadTime' => 5
]);

ServiceDiscoveryConfig::setDefaultServiceGroup([
    'Product-Service1',
    'Order-Service1',
]);

?>