<?php

namespace MatthiasWeb\WPU\V4;

if (!class_exists('\\MatthiasWeb\\WPU\\V4\\WPLSApi')):

/**
 * The WPLSApi is responsible for communicating with WPLS server instances.
 * 
 * It does the HTTP requests but provides wrapper methods.
 */
class WPLSApi
{
    /**
     * If this is set to true, the HTTP request ignores SSL certificate errors.
     * @var bool
     */
    protected static $unsafeDebugMode = true;

    /**
     * Activate a package with a license key.
     * 
     * @param string $slug The package slug.
     * @param string $licenseKey The license to activate with.
     * @return array Server/error response.
     */
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

    /**
     * Deactivate a package with an activation id created during activation.
     * 
     * @param string $slug The package slug.
     * @param string $activationId The activation id the package was activated with.
     * @return array Server/error response.
     */
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

    /**
     * Fetch all announcements from a server.
     */
    /*public static function fetchAnnouncements($lastFetchTime, $packages)
    {
        $response = static::httpGetRequest('api/v1/announcements/newest', array(
            'after' => $lastFetchTime,
            'packages' => implode(',', $packages)
        ));
               
        return $response;
    }*/

    /**
     * Issue an HTTP POST request to a WPLS instance.
     * 
     * @param string $serverUrl The servers url.
     * @param string $path The api path on the server.
     * @param array $body The body of the request.
     * @return array Server/error response.
     */
    protected static function httpPostRequest($serverUrl, $path, $body = array())
    {
        $url = $serverUrl . '/' . $path;

        try {
            $response = wp_remote_post($url, array(
                'body'      => $body,
                'headers' => array(
                    'Accept' => 'application/json'
                ),
                'sslverify' => static::$unsafeDebugMode
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

    /**
     * Issue an HTTP GET request to a WPLS instance.
     * 
     * @param string $serverUrl The servers url.
     * @param string $path The api path on the server.
     * @param array $query The url query of the request.
     * @return array Server/error response.
     */
    protected static function httpGetRequest($serverUrl, $path, $query = array())
    {
        $url = $serverUrl . '/' . $path;
        $url = add_query_arg($query, $url);
        
        try {
            $response = wp_remote_get($url, array(
                'headers' => array(
                    'Accept' => 'application/json'
                ),
                'sslverify' => static::$unsafeDebugMode
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

endif;