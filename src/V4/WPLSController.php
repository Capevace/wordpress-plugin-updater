<?php

namespace MatthiasWeb\WPU\V4;

if (!class_exists('\\MatthiasWeb\\WPU\\V4\\WPLSController')):

/**
 * The WPLSController controls all the clients (plugins/themes) and lets them with a running WPLS instance.
 * It handles the activations, deactivations and all the other features.
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

    /**
     * Initialize the controller.
     * 
     * The controller is responsible for handling all the clients (plugins, themes).
     * It should only exist and be initialized once.
     */
    public static function init()
    {
        AjaxController::init();
        LicenseUIController::init();
    }

    /**
     * Initialize a new client and introduce it to the system.
     * 
     * This creates a client and returns it.
     * 
     * @param string $serverUrl The server url the instance is located on.
     * @param array $config The client config.
     * @return WPLSClient The initialized client.
     */
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
        return $client;
    }

    /**
     * Find a client by its plugin file.
     * 
     * @param string $pluginFile Plugin file path.
     * @return WPLSClient The client.
     */
    public static function fileBelongsToClient($pluginFile)
    {
        foreach (static::$clients as $slug => $client) {
            if ($client->config->file === $pluginFile)
                return $client;
        }

        return false;
    }

    /**
     * Get a client in the system by its slug.
     * 
     * @param string $slug The package slug.
     * @return WPLSClient The client.
     */
    public static function getClient($slug)
    {
        if (!array_key_exists($slug, static::$clients))
            return null;

        return static::$clients[$slug];
    }
}

WPLSController::init();

endif;
