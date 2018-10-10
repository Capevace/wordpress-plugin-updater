<?php

namespace MatthiasWeb\WPU\V4;

if (!class_exists('\\MatthiasWeb\\WPU\\V4\\AjaxController')):

/**
 * The AjaxController is responsible for managing incoming ajax requests, like activations.
 */
class AjaxController
{
    /**
     * Initialize the controller.
     */
    public static function init()
    {
        static::registerAjaxHooks();
    }

    /**
     * Register the ajax hooks.
     */
    protected static function registerAjaxHooks()
    {
        add_action(
            'wp_ajax_wpls_v4_activate', 
            '\MatthiasWeb\WPU\V4\AjaxController::handleActivationAjaxRequest'
        );
        add_action(
            'wp_ajax_wpls_v4_deactivate', 
            '\MatthiasWeb\WPU\V4\AjaxController::handleDeactivationAjaxRequest'
        );
        add_action(
            'wp_ajax_wpls_v4_dismiss_announcement',
            '\MatthiasWeb\WPU\V4\AjaxController::handleDismissAnnouncementAjaxRequest'
        );
    }

    /**
     * Handle an activation request.
     */
    public static function handleActivationAjaxRequest()
    {
        static::checkPermissisons();

        $license  = sanitize_text_field($_POST['license_key']);
        $slug     = sanitize_key($_POST['slug']);
        $response = WPLSApi::activateLicense($slug, $license);
        
        if (isset($response->activated) && $response->activated === true) {
            LicenseManager::saveLicense($slug, $license);
            LicenseManager::saveActivationId($slug, $response->activation_id);
        }

        wp_send_json($response);
        wp_die();
    }

    /**
     * Handle an deactivation request.
     */
    public static function handleDeactivationAjaxRequest()
    {
        static::checkPermissisons();

        $slug         = sanitize_key($_POST['slug']);
        $activationId = LicenseManager::getSavedActivationId($slug);
        $response     = WPLSApi::deactivateLicense($slug, $activationId);
        
        LicenseManager::saveLicense($slug, null);
        LicenseManager::saveActivationId($slug, null);

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

    /**
     * Handle an dismissal of an announcement ajax request.
     */
    public function handleDismissAnnouncementAjaxRequest()
    {
        static::checkPermissisons();

        $announcementId = sanitize_key($_POST['announcement_id']);
        
        //AnnouncementService::dismissAnnouncement($announcementId);

        wp_die();
    }

    /**
     * Check if the user is allowed to change the license settings.
     */
    protected static function checkPermissisons()
    {
        if (!current_user_can('activate_plugins')) {
            wp_send_json(array('error' => array('code' => 401, 'message' => 'You do not have the permissions to do that.')));
            wp_die();
        }
    }
}

endif;