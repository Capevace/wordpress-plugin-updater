<?php

namespace Smoolabs\WPU\V3;

/**
 * 
 */
class LicenseManager
{
    public function init()
    {
        
    }

    public static function getSavedLicense($pluginSlug)
    {
        return get_option('wpls_license_' . $pluginSlug, null);
    }

    public static function saveLicense($pluginSlug, $licenseKey)
    {
        $client = WPLSController::instance()->clients[$pluginSlug];

        if (empty($licenseKey)) {
            delete_option('wpls_license_' . $pluginSlug);
            delete_option('envato_purchase_code_' . $client->config->envatoItemId);

            return;
        }

        update_option('wpls_license_' . $pluginSlug, $licenseKey);
        update_option('envato_purchase_code_' . $client->config->envatoItemId, $licenseKey);
    }

    public static function hasLicense($pluginSlug)
    {
        return !empty(static::getSavedLicense($pluginSlug));
    }

    public static function getSavedActivationId($pluginSlug)
    {
        return get_option('wpls_license_' . $pluginSlug, null);
    }

    public static function saveActivationId($pluginSlug, $activationId)
    {
        $client = WPLicenseServer::instance()->clients[$pluginSlug];

        if (empty($licenseKey)) {
            delete_option('wpls_activation_id_' . $pluginSlug);

            return;
        }

        update_option('wpls_activation_id_' . $pluginSlug, $activationId, true);
    }

    public static function hasActivationId($pluginSlug)
    {
        return !empty($this->getSavedActivationId($pluginSlug));
    }
}