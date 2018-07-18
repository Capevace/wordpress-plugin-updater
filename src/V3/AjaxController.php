<?php

namespace Smoolabs\WPU\V3;

/**
 * 
 */
class AjaxController
{
    public static function init()
    {
        static::registerAjaxHooks();
    }

    protected static function registerAjaxHooks()
    {
        add_action(
            'wp_ajax_wpls_v3_activate', 
            '\Smoolabs\WPU\V3\AjaxController::handleActivationAjaxRequest'
        );
        add_action(
            'wp_ajax_wpls_v3_deactivate', 
            '\Smoolabs\WPU\V3\AjaxController::handleDeactivationAjaxRequest'
        );
        add_action(
            'wp_ajax_wpls_v3_dismiss_announcement',
            '\Smoolabs\WPU\V3\AjaxController::handleDismissAnnouncementAjaxRequest'
        );
    }

    public static function handleActivationAjaxRequest()
    {
        static::checkPermissisons();

        $license  = sanitize_text_field($_POST['license_key']);
        $slug     = sanitize_key($_POST['slug']);
        $response = ServerCommunicator::instance()->activateLicense($slug, $license);
        
        if (isset($response->activated) && $response->activated === true) {
            LicenseManager::saveLicense($license, $this->pluginSlug, $this->envatoItemId);
            LicenseManager::saveActivationId($response->activation_id, $this->pluginSlug);
        }

        wp_send_json($response);
        wp_die();
    }

    public static function handleDeactivationAjaxRequest()
    {
        static::checkPermissisons();

        $slug         = sanitize_key($_POST['slug']);
        $activationId = LicenseManager::getSavedActivationId($slug);
        $response     = ServerCommunicator::deactivateLicense($slug, $activationId);
        
        LicenseManager::saveLicense(null, $slug);
        LicenseSettings::saveActivationId(null, $slug);

        if (isset($response->deactivated) && $response->deactivated !== true) {
            wp_send_json(array(
                'deactivated' => false, 
                'error' => array(
                    'code' => 400, 
                    'message' => 'Please contact our support team and include this ID in your request: ' 
                        . $activationId
                )
            ));
            wp_die();
        }

        wp_send_json($response);
        wp_die();
    }

    public function handleDismissAnnouncementAjaxRequest()
    {
        static::checkPermissisons();

        $announcementId = sanitize_key($_POST['announcement_id']);
        
        //AnnouncementService::dismissAnnouncement($announcementId);

        wp_die();
    }

    protected static function checkPermissisons()
    {
        if (!current_user_can('activate_plugins')) {
            wp_send_json(array('error' => array('code' => 401, 'message' => 'You do not have the permissions to do that.')));
            wp_die();
        }
    }
}