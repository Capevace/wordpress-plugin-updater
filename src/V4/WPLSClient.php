<?php

namespace MatthiasWeb\WPU\V4;

if (!class_exists('\\MatthiasWeb\\WPU\\V4\\WPLSClient')):

/**
 * The WPLSClient is the class that each registered plugin receives.
 */
class WPLSClient
{
    public $config;

    public function __construct($config)
    {
        $this->config = $config;

        $this->setupPluginUpdateChecker();
    }

    /**
     * Check if the plugin has been activated with a license.
     * 
     * @return bool Is activated.
     */
    public function isActivated()
    {
        return LicenseManager::hasActivationId($this->config->slug);
    }

    /**
     * Sets up the PUC instance running in the background for updating functionality.
     */
    protected function setupPluginUpdateChecker()
    {
        include_once __DIR__ . '/../../plugin-update-checker-4.4/plugin-update-checker.php';
        $updateChecker = \Puc_v4_Factory::buildUpdateChecker(
            $this->config->serverUrl . '/api/v1/packages/' . $this->config->slug . '/metadata',
            $this->config->path,
            $this->config->slug
        );

        // Add query arg filter to add license and metadata to requests.
        $updateChecker->addQueryArgFilter(array($this, 'filterPUCQuery'));

        return $updateChecker;
    }

    /**
     * Filter used to add activation ids and metadata to an update query by PUC.
     * 
     * @param array $queryArgs
     * @return array
     */
    public function filterPUCQuery($queryArgs)
    {
        $activationId = LicenseManager::getSavedActivationId($this->config->slug);
        if (!empty($activationId)) {
            $queryArgs['activation'] = $activationId;
        }

        $queryArgs['site-meta'] = $this->getSiteMetadata();

        return $queryArgs;
    }

    /**
     * Get a sites url, normalized to a specific degree.
     * 
     * @return string The url.
     */
    public function getSiteUrl()
    {
        $url = untrailingslashit(get_site_url());
        // in case scheme relative url is passed ('//google.com')
        $url = trim($url, '/');
        $url = preg_replace('/^http(s)?:\/\//', '', $url);
        $url = preg_replace('/^www\./', '', $url);

        return $url;
    }

    /**
     * Get the sites metadata as JSON.
     * 
     * @return string The metadata as JSON.
     */
    public function getSiteMetadata()
    {
        $data = json_encode(array(
            'url' => get_site_url(),
            'wp_version' => get_bloginfo('version'),
            'package_version' => $this->config->version,
            'php_version' => phpversion()
        ));

        return $data;
    }
}

endif;
