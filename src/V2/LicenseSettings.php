<?php

namespace Smoolabs\V2;

if (!class_exists('\\Smoolabs\\V2\\LicenseSettings', false)) :

class LicenseSettings
{
    protected $name;
    protected $slug;
    
    public static $VUE_VERSION    = '2.5.16';
    public static $VUE_FILE       = 'vue-2.5.16.min.js';

    public function __construct($config)
    {
        $this->name = $config['name'];
        $this->slug = $config['slug'];

        $pluginFile = plugin_basename($config['path']);
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScriptsHook'), 99);
        add_action('after_plugin_row_' . $pluginFile, array($this, 'afterPluginRowHook'), 10, 2);
        add_filter('plugin_action_links_' . $pluginFile, array($this, 'addLicenseSettingsLink'));
    }
    
    /**
     * Enqueue VueJS if not already enqueued to the plugins.php page.
     * This can be ensured through the priority 99.
     */
    public function adminEnqueueScriptsHook($hook_suffix) {
        if ($hook_suffix === 'plugins.php') {
            if (!wp_script_is('vue', 'registered')) {
                $vueUrl = plugin_dir_url(realpath(trailingslashit(__DIR__) . '../../assets/js/'. self::$VUE_FILE));
                wp_register_script('vue', $vueUrl . self::$VUE_FILE, array(), self::$VUE_VERSION);
            }
            wp_enqueue_script('vue');
        }
    }

    public function afterPluginRowHook()
    {
        $license = self::getSavedLicense($this->slug);
        $translations = array(
            'Enter License'                                              => __('Enter License', 'smoolabs-updater'),
            'License or Envato Purchase Code'                            => __('License or Envato Purchase Code', 'smoolabs-updater'),
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

        $data = array(
            'translations' => $translations,
            'name'         => $this->name,
            'slug'         => $this->slug,
            'license'      => $license,
            'active'       => $license !== null && $license !== '' && $license !== false
        );
        
        $debug = defined('WP_DEBUG') && WP_DEBUG;
        ?>
        <script type="text/javascript">
            (function($, data) {
                <?php echo file_get_contents(__DIR__ . '../../../assets/js/credentials' . (WP_DEBUG ? '' : '-transpiled') . '.js'); ?>
            })(jQuery, <?php echo json_encode($data); ?>);
        </script>
        <?php
    }

    public function addLicenseSettingsLink($actions)
    {
        $noScriptTag = '<noscript> (You need to enable JavaScript to activate the plugin)</noscript>';

        if (!self::hasLicenseSaved($this->slug))
            $actions['wpls-enter-license'] = '<a href="#" id="enter-license-' . $this->slug . '">License Settings' . $noScriptTag . '</a>';
        else
            $actions['wpls-enter-license'] = '<a style="color: #3db634;" href="#" id="enter-license-' . $this->slug . '">Enter License' . $noScriptTag . '</a>';

        return $actions;
    }

    public static function getSavedLicense($pluginSlug)
    {
        return get_option('wpls_license_' . $pluginSlug);
    }

    public static function saveLicense($license, $pluginSlug)
    {
        if ($license === null || $license === '') {
            delete_option('wpls_license_' . $pluginSlug);
            return;
        }

        update_option('wpls_license_' . $pluginSlug, $license, true);
    }

    public static function hasLicenseSaved($pluginSlug)
    {
        return !empty(self::getSavedLicense($pluginSlug));
    }


    public static function getSavedActivationId($pluginSlug)
    {
        return get_option('wpls_activation_id_' . $pluginSlug);
    }

    public static function saveActivationId($activationId, $pluginSlug)
    {
        if ($activationId === null || $activationId === '') {
            delete_option('wpls_activation_id_' . $pluginSlug);
            return;
        }

        update_option('wpls_activation_id_' . $pluginSlug, $activationId, true);
    }

    public static function hasActivationIdSaved($pluginSlug)
    {
        return !empty(self::getSavedActivationId($pluginSlug));
    }
}

endif;