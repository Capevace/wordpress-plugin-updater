<?php

namespace MatthiasWeb\WPU\V4;

if (!class_exists('\\MatthiasWeb\\WPU\\V4\\Translations')):

/**
 * The class responsible for translating labels in the UI.
 */
class Translations
{
    protected static $licenseUITranslations = null;

    /**
     * Get the translations for the license UI on the plugins page.
     * 
     * @return array The translations.
     */
    public static function getLicenseUITranslations()
    {
        if (static::$licenseUITranslations === null) {
            static::$licenseUITranslations = array(
                'Check for updates'                                          => __('Check for updates', 'plugin-update-checker'),
                'Enter License'                                              => __('Enter License', 'smoolabs-updater'),
                'Your license key:'                                          => __('Your license key:', 'smoolabs-updater'),
                'License or Envato Purchase Code'                            => __('License or Envato Purchase Code', 'smoolabs-updater'),
                'Where do I find my Envato purchase code?'                   => __('Where do I find my Envato purchase code?', 'smoolabs-updater'),
                'License Settings'                                           => __('License Settings', 'smoolabs-updater'),
                'Enter License or Envato Purchase Code'                      => __('Enter License or Envato Purchase Code', 'smoolabs-updater'),
                'Save'                                                       => __('Save', 'smoolabs-updater'),
                'Activate'                                                   => __('Activate', 'smoolabs-updater'),
                'Deactivate'                                                 => __('Deactivate', 'smoolabs-updater'),
                'Plugin successfully activated!'                             => __('Plugin successfully activated!', 'smoolabs-updater'),
                'Plugin could not be activated. The license key is invalid.' => __('Plugin could not be activated. The license key is invalid.', 'smoolabs-updater'),
                'Plugin could not be activated. An unknown error occurred.'  => __('Plugin could not be activated. An unknown error occurred.', 'smoolabs-updater'),
                'What\'s this?'                                              => __('What\'s this?', 'smoolabs-updater'),

                'To enable full functionality of this plugin, all you have to do is to enter the license that was provided to you during sale. If you bought the plugin using the Envato market, you\'ll need to enter the Envato purchase code.' => __('To enable full functionality of this plugin, all you have to do is to enter the license that was provided to you during sale. If you bought the plugin using the Envato market, you\'ll need to enter the Envato purchase code.', 'smoolabs-updater'),
                'Enter License'                                              => __('Enter License', 'smoolabs-updater'),
                'Are you sure you want to deactivate the plugin? This will free up the license to be used on a different site.' => __('Are you sure you want to deactivate the plugin? This will free up the license to be used on a different site.', 'smoolabs-updater'),
                'I allow the following data to be sent to our update servers: license key, site url, WordPress version, PHP version and package version. This data is required to provide license activation and update functionality.' => __('I allow the following data to be sent to our update servers: license key, site url, WordPress version, PHP version and package version. This data is required to provide license activation and update functionality.', 'smoolabs-updater'),
                'To use the extended funcionality of this plugin, you need to allow the required data to be sent to our servers. Don\'t worry, we don\'t share that data with anyone. But it is required to verify an activated license.' => __('To use the extended funcionality of this plugin, you need to allow the required data to be sent to our servers. Don\'t worry, we don\'t share that data with anyone. But it is required to verify an activated license.', 'smoolabs-updater'),
                'Please provide a license key.' => __('Please provide a license key.', 'smoolabs-updater'),
            );
        }

        return static::$licenseUITranslations;
    }
}

endif;