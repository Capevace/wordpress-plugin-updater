<?php

namespace Smoolabs\V2;

if (!class_exists('\\Smoolabs\\V2\\AdminNotices', false)) :

class AdminNotices
{
    public function __construct()
    {
        add_action('admin_notices', array($this, 'renderAdminNotices'));
        //add_action('wp_ajax_wpls_hide_notice_' . $this->plugin_slug, array($this, 'hide_notice'));
    }

    public function renderAdminNotices()
    {
        
    }
}

endif;