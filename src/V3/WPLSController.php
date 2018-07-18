<?php

namespace Smoolabs\WPU\V3;

use Util\Singleton;

if (!class_exists('\\Smoolabs\\WPU\\V3\\WPLicenseServer')):

include_once 'AjaxController.php';
include_once 'ClientConfig.php';
include_once 'LicenseManager.php';
include_once 'ServerCommunicator.php';
include_once 'Translations.php';
include_once 'LicenseUIController.php';
include_once 'WPLSClient.php';

/**
 * 
 */
class WPLSController
{
    /**
     * The registered servers and their clients in an assoc array.
     * @var array $servers
     */
    protected static $servers = array();

    /**
     * The registered clients in an assoc array, with their slug as key.
     * @var array $clients
     */
    public static $clients = array();

    public static function init()
    {
        AjaxController::init();
        LicenseUIController::init();
        // AnnouncementService init
        // LicenseManager init
        // ServerCommunicator
    }

    public static function initClient($serverUrl, $config)
    {
        $serverUrl = untrailingslashit($serverUrl);
        $config    = new ClientConfig($serverUrl, $config);
        $client    = new WPLSClient($config);

        // Add the client to the servers
        if (!array_key_exists($serverUrl, static::$servers)) {
            static::$servers[$serverUrl] = array($client);
        } else {
            $clients = static::$servers[$serverUrl];
            array_push($clients, $client);

            static::$servers[$serverUrl];
        }

        static::$clients[$config->slug] = $client;
    }

    public static function fileBelongsToClient($pluginFile)
    {
        foreach (static::$clients as $slug => $client) {
            if ($client->config->file === $pluginFile)
                return $client;
        }

        return false;
    }
}

WPLSController::init();

endif;