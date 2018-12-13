<?php

namespace MatthiasWeb\WPU\V4;

if (!class_exists('\\MatthiasWeb\\WPU\\V4\\LicenseManager')):

/**
 * The LicenseManager is responsible for saving and gettings licenses and activation ids from the database.
 * 
 * This is so we have a unified way to access them, and be a little backwards compatible if the database keys change or something similar.
 */
class LicenseManager
{
    /**
     * Get the license saved for a specific package.
     * 
     * @param string $pluginSlug The plugins slug,
     * @return string The license.
     */
    public static function getSavedLicense($pluginSlug)
    {
        return get_site_option('wpls_license_' . $pluginSlug, null);
    }

    /**
     * Save a license onto a package with a given slug,
     * 
     * @param string $pluginSlug The plugins slug,
     * @param string $licenseKey The license to save.
     */
    public static function saveLicense($pluginSlug, $licenseKey)
    {
        $client = WPLSController::getClient($pluginSlug);

        if (empty($licenseKey)) {
            delete_site_option('wpls_license_' . $pluginSlug);
            delete_site_option('envato_purchase_code_' . $client->config->envatoItemId);

            return;
        }

        update_site_option('wpls_license_' . $pluginSlug, $licenseKey);
        update_site_option('envato_purchase_code_' . $client->config->envatoItemId, $licenseKey);
    }

    /**
     * Check if a package has a license saved.
     * 
     * @param string $pluginSlug The plugin slug.
     * @return bool
     */
    public static function hasLicense($pluginSlug)
    {
        $result = static::getSavedLicense($pluginSlug);
        return !empty($result);
    }

    /**
     * Get the activation id saved for a specific package.
     * 
     * @param string $pluginSlug The plugins slug,
     * @return string The activation id.
     */
    public static function getSavedActivationId($pluginSlug)
    {
        return get_site_option('wpls_activation_id_' . $pluginSlug, null);
    }

    /**
     * Save an activation id onto a package with a given slug,
     * 
     * @param string $pluginSlug The plugins slug,
     * @param string $activationId The activation id to save.
     */
    public static function saveActivationId($pluginSlug, $activationId)
    {
        $client = WPLSController::getClient($pluginSlug);

        if (empty($activationId)) {
            delete_site_option('wpls_activation_id_' . $pluginSlug);

            return;
        }

        update_site_option('wpls_activation_id_' . $pluginSlug, $activationId, true);
    }

    /**
     * Check if a package has an activation id saved.
     * 
     * @param string $pluginSlug The plugin slug.
     * @return bool
     */
    public static function hasActivationId($pluginSlug)
    {
        $result = static::getSavedActivationId($pluginSlug);
        return !empty($result);
    }
}

endif;