<?php

namespace Smoolabs\WPU\V3;

/**
 * 
 */
class WPLSClient
{
    public $config;

    public function __construct(ClientConfig $config)
    {
        $this->config = $config;

        $this->setupPluginUpdateChecker();
    }

    public function isActivated()
    {
        return LicenseManager::hasActivationId($this->config->slug);
    }

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

    public function filterPUCQuery($queryArgs)
    {
        $activationId = LicenseManager::getSavedActivationId($this->config->slug);
        if (!empty($activationId)) {
            $queryArgs['activation'] = $activationId;
        }

        $queryArgs['site-meta'] = $this->getSiteMetadata();

        return $queryArgs;
    }

    public function getSiteUrl()
    {
        $url = untrailingslashit(get_site_url());
        // in case scheme relative url is passed ('//google.com')
        $url = trim($url, '/');
        $url = preg_replace('/^http(s)?:\/\//', '', $url);
        $url = preg_replace('/^www\./', '', $url);

        return $url;
    }

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
