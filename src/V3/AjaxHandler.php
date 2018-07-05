<?php

namespace Smoolabs\V3;

if (!class_exists('\\Smoolabs\\V3\\AjaxHandler', false)) :

class AjaxHandler
{
    protected $serverCommunicator;
    protected $pluginSlug;
    protected $supportUrl;

    public function __construct($config, $serverCommunicator)
    {
        $this->serverCommunicator = $serverCommunicator;
        $this->pluginSlug         = $config['slug'];
        $this->envatoItemId       = $config['envatoItemId'];
        $this->supportUrl         = $config['serverUrl'] . '/support/activation';

        add_action(
            'wp_ajax_wpls_v3_activate_' . $this->pluginSlug, 
            array($this, 'handleActivationAjaxRequest')
        );
        add_action(
            'wp_ajax_wpls_v3_deactivate_' . $this->pluginSlug, 
            array($this, 'handleDeactivationAjaxRequest')
        );
        add_action(
            'wp_ajax_wpls_v3_dismiss_announcement_' . $this->pluginSlug,
            array($this, 'handleDismissAnnouncementAjaxRequest')
        );
    }

    public function handleActivationAjaxRequest()
    {
        $this->checkPermissisons();

        $license  = sanitize_text_field($_POST['license_key']);
        $response = $this->serverCommunicator->activateLicense($license);
        
        if (isset($response->activated) && $response->activated === true) {
            LicenseSettings::saveLicense($license, $this->pluginSlug, $this->envatoItemId);
            LicenseSettings::saveActivationId($response->activation_id, $this->pluginSlug);
        }

        wp_send_json($response);
        wp_die();
    }

    public function handleDeactivationAjaxRequest()
    {
        $this->checkPermissisons();
        
        $activationId = LicenseSettings::getSavedActivationId($this->pluginSlug);
        $response = $this->serverCommunicator->deactivateLicense($activationId);
      
        LicenseSettings::saveLicense(null, $this->pluginSlug, $this->envatoItemId);
        LicenseSettings::saveActivationId(null, $this->pluginSlug);
        
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
        $this->checkPermissisons();

        $announcementId = $_POST['announcement_id'];
        AnnouncementService::dismissAnnouncement($announcementId);

        wp_die();
    }

    protected function checkPermissisons()
    {
        if (!current_user_can('activate_plugins')) {
            wp_send_json(array('error' => array('code' => 401, 'message' => 'You do not have the permissions to do that.')));
            wp_die();
        }
    }
}

endif;