<?php

namespace Smoolabs\WPU\V3;

/**
 * 
 */
class LicenseUIController
{
    public static function init()
    {
        add_action('admin_enqueue_scripts', '\Smoolabs\WPU\V3\LicenseUIController::adminEnqueueScriptsHook', 99);
        add_action('after_plugin_row', '\Smoolabs\WPU\V3\LicenseUIController::afterPluginRowHook');
        add_filter('plugin_action_links', '\Smoolabs\WPU\V3\LicenseUIController::pluginActionLinksHook', 10, 2);
    }

    public static function adminEnqueueScriptsHook($page)
    {
        if ($page === 'plugins.php') {
            if (!wp_script_is('vue-2.5.16', 'registered')) {
                $vueUrl = plugin_dir_url(realpath(trailingslashit(__DIR__) . '../../assets/js/'. self::$VUE_FILE));
                wp_register_script('vue-2.5.16', $vueUrl . self::$VUE_FILE, array(), self::$VUE_VERSION);
            }
            wp_enqueue_script('vue-2.5.16');
        }
    }

    public static function afterPluginRowHook($pluginFile)
    {
        $client = WPLSController::fileBelongsToClient($pluginFile);

        if (!$client)
            return;

        $debugMode          = defined('WP_DEBUG') && WP_DEBUG;
        $translations       = Translations::getLicenseUITranslations();
        $license            = LicenseManager::getSavedLicense($client->config->slug);
        $checkForUpdatesUrl = wp_nonce_url(
            add_query_arg(
                array('puc_check_for_updates' => 1, 'puc_slug' => $client->config->slug), 
                self_admin_url('plugins.php')
            ), 
            'puc_check_for_updates'
        );

        $data = array(
            'translations' => $translations,
            'name'         => $client->config->name,
            'slug'         => $client->config->slug,
            'license'      => $license,
            'active'       => !empty($license),
            'checkUrl'     => $checkForUpdatesUrl
        );

        ?>
            <script type="text/javascript">
                (function($, data) {
                    <?php echo file_get_contents(__DIR__ . '../../../assets/js/credentials' . (WP_DEBUG ? '' : '-transpiled') . '.js'); ?>
                })(jQuery, <?php echo json_encode($data); ?>);
            </script>
        <?php
    }

    public static function pluginActionLinksHook($actions, $pluginFile)
    {
        $client = WPLSController::fileBelongsToClient($pluginFile);

        if (!$client)
            return $actions;

        $noScriptTag = '<noscript> (You need to enable JavaScript to activate the plugin)</noscript>';

        if (!LicenseManager::hasLicense($client->config->slug))
            $actions['wpls-enter-license'] = '<a href="#" id="enter-license-' . $client->config->slug . '">License Settings' . $noScriptTag . '</a>';
        else
            $actions['wpls-enter-license'] = '<a style="color: #3db634;" href="#" id="enter-license-' . $client->config->slug . '">Enter License' . $noScriptTag . '</a>';

        return $actions;
    }
}
