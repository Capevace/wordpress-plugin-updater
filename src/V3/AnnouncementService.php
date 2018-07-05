<?php

namespace Smoolabs\V3;

if (!class_exists('\\Smoolabs\\V3\\AnnouncementService', false)) :

class AnnouncementService
{
    protected static $noticesAlreadyRendered = false;
    protected static $announcementServersQueried = array();

    protected $serverCommunicator;
    protected $serverUrl;
    protected $pluginSlug;
    protected $pluginPath;

    public function __construct($config, $serverCommunicator)
    {
        $this->serverCommunicator = $serverCommunicator;
        $this->serverUrl          = untrailingslashit($config['serverUrl']);
        $this->pluginSlug         = $config['slug'];
        $this->pluginPath         = $config['path'];

        register_activation_hook($this->pluginPath, array($this, 'onActivation'));
        register_deactivation_hook($this->pluginPath, array($this, 'onDeactivation'));

        add_action('wpls_announcements_check', array($this, 'maybeUpdateAnnouncements'));
        add_action('admin_notices', array($this, 'renderAdminNotices'));
        //add_action('wp_ajax_wpls_hide_notice_' . $this->plugin_slug, array($this, 'hide_notice'));
    }

    public function onActivation()
    {
        if (!wp_next_scheduled('wpls_announcements_check')) {
            wp_schedule_event(time(), 'daily', 'wpls_announcements_check');
        }
    }

    public function onDeactivation()
    {
        wp_clear_scheduled_hook('wpls_announcements_check');
    }

    public function renderAdminNotices()
    {
        // This is so they only get rendered once!
        if (static::$noticesAlreadyRendered)
            return;
        static::$noticesAlreadyRendered = true;

        $announcements = array_values(static::getGlobalAnnouncements());

        // Sort Announcements by creation time
        usort($announcements, function ($a, $b) {
            $a_time = $a->updated_at;
            $b_time = $b->updated_at;

            if ($a_time < $b_time) return 1;
            else if ($a_time > $b_time) return -1;
            else return 0;
        });

        ?>
        <div class="wpls-announcements">
            <?php

            foreach ($announcements as $announcement) {
                $announcement->render($this->pluginSlug);
            }

            ?>
        </div>
        <?php
    }

    public function maybeUpdateAnnouncements()
    {
        if (in_array($this->serverUrl, static::$announcementServersQueried)) {
            static::setLastFetchTime($this->pluginSlug, date('Y-m-d H:i:s T'));
            return;
        }

        array_push(static::$announcementServersQueried, $this->serverUrl);

        $this->updateAnnouncements();
        static::setLastFetchTime($this->pluginSlug, date('Y-m-d H:i:s T'));
    }

    public function updateAnnouncements()
    {
        $lastFetchTime    = static::getEarliestFetchTime();
        $packages         = PluginUpdater::$installedPlugins;
        $newAnnouncements = $this->serverCommunicator->fetchAnnouncements($lastFetchTime, $packages);

        if (!$newAnnouncements)
            return;

        $announcements = static::getGlobalAnnouncements();
        $dismissedAnnouncements = static::getDismissedAnnouncementIds();
        
        foreach ($newAnnouncements as $announcementData) {
            $announcement = Announcement::create($announcementData);

            if (!in_array($announcement->id, $dismissedAnnouncements))
                $announcements[$announcement->id] = $announcement;
        }

        static::setGlobalAnnouncements($announcements);
    }

    public static function dismissAnnouncement($announcementId)
    {
        $announcements = static::getGlobalAnnouncements();

        // If that announcement actually exists remove it and add the id to the dismissed
        if (array_key_exists($announcementId, $announcements)) {
            unset($announcements[$announcementId]);
            static::setGlobalAnnouncements($announcements);

            $dismissedAnnouncements = static::getDismissedAnnouncementIds();
            array_push($dismissedAnnouncements, $announcementId);
            static::setDismissedAnnouncementIds($dismissedAnnouncements);

            return true;
        }

        return false;
    }

    public static function getEarliestFetchTime()
    {
        $earliest_time = date('Y-m-d H:i:s T');

        foreach (PluginUpdater::$installedPlugins as $pluginSlug) {
            $time = static::getLastFetchTime($pluginSlug);

            if ($time < $earliest_time)
                $earliest_time = $time;
        }

        return $earliest_time;
    }

    public static function setLastFetchTime($pluginSlug, $fetchTime)
    {
        if ($fetchTime === null) {
            delete_option('wpls_last_announcement_fetch_time_' . $pluginSlug);
            return;
        }

        return update_option('wpls_last_announcement_fetch_time_' . $pluginSlug, $fetchTime, false);
    }

    public static function getLastFetchTime($pluginSlug)
    {
        return get_option('wpls_last_announcement_fetch_time_' . $pluginSlug, date('Y-m-d H:i:s T'));
    }

    public static function setGlobalAnnouncements($announcements)
    {
        if ($announcements === null || count($announcements) === 0) {
            delete_option('wpls_announcements');
            return;
        }

        update_option('wpls_announcements', $announcements, false);
    }

    public static function getGlobalAnnouncements()
    {
        return get_option('wpls_announcements', array());
    }

    public static function setDismissedAnnouncementIds($announcements)
    {
        if ($announcements === null || count($announcements) === 0) {
            delete_option('wpls_dismissed_announcements');
            return;
        }

        update_option('wpls_dismissed_announcements', $announcements, false);
    }

    public static function getDismissedAnnouncementIds()
    {
        return get_option('wpls_dismissed_announcements', array());
    }
}

endif;