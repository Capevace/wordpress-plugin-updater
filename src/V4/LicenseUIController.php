<?php

namespace MatthiasWeb\WPU\V4;

if (!class_exists('\\MatthiasWeb\\WPU\\V4\\LicenseUIController')):

/**
 * The LicenseUIController is responsible for the UI on the main plugins page,
 */
class LicenseUIController
{
    protected static $VUE_FILE    = 'vue-2.5.16.min.js';
    protected static $VUE_VERSION = '2.5.16';

    /**
     * Initialize the controller and add the actions needed.
     */
    public static function init()
    {
        add_action('admin_enqueue_scripts', '\MatthiasWeb\WPU\V4\LicenseUIController::adminEnqueueScriptsHook', 99);
        add_action('after_plugin_row', '\MatthiasWeb\WPU\V4\LicenseUIController::afterPluginRowHook');
        add_filter('plugin_action_links', '\MatthiasWeb\WPU\V4\LicenseUIController::pluginActionLinksHook', 10, 2);
        add_filter('network_admin_plugin_action_links', '\MatthiasWeb\WPU\V4\LicenseUIController::pluginActionLinksHook', 10, 2);
    }

    /**
     * Enqueue vue if we're on the plugins page.
     * 
     * @hook admin_enqueue_scripts
     * @param string $page The page were currently on.
     */
    public static function adminEnqueueScriptsHook($page)
    {
        if ($page === 'plugins.php') {
            if (!wp_script_is('vue-2.5.16', 'registered')) {
                $vueUrl = plugin_dir_url(realpath(trailingslashit(__DIR__) . '../../assets/js/'. static::$VUE_FILE));
                wp_register_script('vue-2.5.16', $vueUrl . static::$VUE_FILE, array(), static::$VUE_VERSION);
            }
            wp_enqueue_script('vue-2.5.16');
        }
    }

    /**
     * Kickstart the license UI after the plugin row hook.
     * 
     * @hook after_plugin_row
     * @param string $pluginFile The plugins file path.
     */
    public static function afterPluginRowHook($pluginFile)
    {
        $client = WPLSController::fileBelongsToClient($pluginFile);

        if (!$client)
            return;

        $debugMode          = defined('WP_DEBUG') && WP_DEBUG;
        $translations       = Translations::getLicenseUITranslations();
        $license            = LicenseManager::getSavedLicense($client->config->slug);
        $email              = get_site_option('wpls_email_' . $client->config->slug, null);
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
            'newsletterPrivacy' => isset($client->config->newsletterPrivacy)
                ? $client->config->newsletterPrivacy
                : null,
            'email'        => $email,
            'active'       => !empty($license),
            'checkUrl'     => $checkForUpdatesUrl
        );

        ?>
            <script type="text/javascript">
                // Vue Code
                (function($, data) {
                    <?php echo file_get_contents(__DIR__ . '../../../assets/js/credentials' . (WP_DEBUG ? '' : '-transpiled') . '.js'); ?>
                })(jQuery, /* Vue model data */ <?php echo json_encode($data, JSON_PRETTY_PRINT); ?>);
            </script>
        <?php
    }

    /**
     * Add a "Enter License" link to the plugins actions.
     * 
     * @hook plugin_action_links
     * @param array $actions The plugin actions.
     * @param string $pluginFile The plugins file path.
     * @return array The updated actions.
     */
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

endif;