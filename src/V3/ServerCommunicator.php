<?php

namespace Smoolabs\WPU\V3;

/**
 * 
 */
class ServerCommunicator
{
    public static function activateLicense($slug, $licenseKey)
    {
        $client    = WPLSController::$clients[$slug];
        $serverUrl = $client->config->serverUrl;
        $siteUrl   = $client->getSiteUrl();
        $siteMeta  = $client->getSiteMetadata();

        $response = static::httpPostRequest($serverUrl, 'api/v1/license/activate', array(
            'license'   => $licenseKey,
            'slug'      => $slug,
            'site'      => $siteUrl,
            'site-meta' => $siteMeta
        ));

        if (!$response) {
            return (object) array('activated' => false, 'error' => array('code' => 500, 'message' => 'An unknown error occurred.', 'response' => $response));
        }

        return $response;
    }

    public static function deactivateLicense($slug, $activationId)
    {
        $client    = WPLSController::$clients[$slug];
        $serverUrl = $client->config->serverUrl;

        $response = static::httpPostRequest($serverUrl, 'api/v1/activation/' . $activationId . '/deactivate');
        
        if (!$response) {
            return (object) array('deactivated' => false, 'error' => array('code' => 500, 'message' => 'An unknown error occurred.', 'response' => $response));
        }

        return $response;
    }

    public static function fetchAnnouncements($lastFetchTime, $packages)
    {
        $response = static::httpGetRequest('api/v1/announcements/newest', array(
            'after' => $lastFetchTime,
            'packages' => implode(',', $packages)
        ));
               
        return $response;
    }

    protected static function httpPostRequest($serverUrl, $path, $body = array())
    {
        $url = $serverUrl . '/' . $path;
        
        try {
            $response = wp_remote_post($url, array(
                'body'      => $body,
                'headers' => array(
                    'Accept' => 'application/json'
                ),
                'sslverify' => $this->unsafeDebugMode
            ));
            
            if(is_wp_error($response)) {
                var_dump($response);
                return false;
            }

            $data = json_decode($response['body']);

            return $data;
        } catch (Exception $e) {
            return false;
        }
    }

    protected static function httpGetRequest($serverUrl, $path, $query = array())
    {
        $url = $serverUrl . '/' . $path;
        $url = add_query_arg($query, $url);
        
        try {
            $response = wp_remote_get($url, array(
                'headers' => array(
                    'Accept' => 'application/json'
                ),
                'sslverify' => $this->unsafeDebugMode
            ));

            if(is_wp_error($response)) {
                return false;
            }

            $data = json_decode($response['body']);

            return $data;
        } catch (Exception $e) {
            return false;
        }
    }
}