<?php

namespace Smoolabs\V2;

use Smoolabs\V2\Parsedown\Parsedown;

if (!class_exists('\\Smoolabs\\V2\\Announcement', false)) :

class Announcement
{
    protected static $parsedown = null;
    
    public $id;
    public $title;
    public $type;
    public $content;
    public $packages;
    public $updated_at;

    public function render(string $renderingPluginSlug)
    {
        $time       = strtotime($this->updated_at);
        $dateString = date(get_option('date_format'), $time);
        $timeString = date(get_option('time_format'), $time);

        $dateLabel = 'by {packages} on {date} at {time}';
        $dateLabel = str_replace('{packages}', $this->packagesListLabel(), $dateLabel);
        $dateLabel = str_replace('{date}', $dateString, $dateLabel);
        $dateLabel = str_replace('{time}', $timeString, $dateLabel);

        ?>
        <div class="notice notice-<?php echo $this->type; ?> wpls-announcement" style="position: relative;" id="wpls-announcement-<?php echo $this->id; ?>">
            <input type="hidden" name="action" value="wp_ajax_wpls_dismiss_announcement_<?php echo $renderingPluginSlug; ?>">
            <input type="hidden" name="announcement_id" value="<?php echo $this->id; ?>">

            <h3 style="margin-bottom: 0px;">
                <?php echo $this->title; ?>
                <span style="color: #8d8d8d;font-weight: normal;font-size: 10px;">
                    <?php echo $dateLabel; ?>
                </span>    
            </h3>
            
            <div style="padding: 5px 0px 5px;">
                <?php echo $this->content; ?>
            </div>

            <button type="submit" class="notice-dismiss wpls-announcement-dismiss" id="dismiss-button-<?php echo $this->id; ?>">
                <span class="screen-reader-text">Hide notice.</span>
            </button>
            <script>
                (function($) {
                    var announcement = $('#wpls-announcement-<?php echo $this->id; ?>');

                    console.log(announcement.find('button.wpls-announcement-dismiss'));
                    announcement.find('button.wpls-announcement-dismiss').click(function(e) {
                        e.preventDefault();

                        announcement.fadeOut(200);
                        $.ajax({
                            url: ajaxurl,
                            method: 'post',
                            data: {
                                action: 'wpls_dismiss_announcement_<?php echo $renderingPluginSlug; ?>',
                                announcement_id: '<?php echo $this->id; ?>'
                            }
                        });
                    });
                })(jQuery);
            </script>
        </div>
        <?php
    }

    protected function packagesListLabel()
    {
        $label = '';

        foreach ($this->packages as $index => $package) {
            if ($index === 0)
                $label .= $package->name;
            else if ($index < count($this->packages) - 1)
                $label .= ', ' . $package->name;
            else
                $label .= ' and ' . $package->name;
        }

        return $label;
    }

    public static function create($announcementData)
    {
        $announcementData = static::validateData($announcementData);

        if (!$announcementData)
            return null;

        $announcement = new static;

        $announcement->id         = $announcementData->id;
        $announcement->title      = $announcementData->title;
        $announcement->type       = $announcementData->type;
        $announcement->content    = $announcementData->content;
        $announcement->packages   = $announcementData->packages;
        $announcement->updated_at = $announcementData->updated_at;

        return $announcement;
    }

    protected static function validateData($announcementData)
    {
        if (static::$parsedown === null)
            static::$parsedown = new Parsedown();

        if (
            !property_exists($announcementData, 'id')
            || !property_exists($announcementData, 'title')
            || !property_exists($announcementData, 'type')
            || !property_exists($announcementData, 'content')
            || !property_exists($announcementData, 'packages')
            || !property_exists($announcementData, 'updated_at')
        ) {
            return false;
        }

        // Sanitize
        // TODO: proper sanitization (do we even need this?)
        $announcementData->id         = (string) $announcementData->id;
        $announcementData->title      = (string) $announcementData->title;
        $announcementData->type       = (string) $announcementData->type;
        $announcementData->content    = static::$parsedown->text((string) $announcementData->content);
        $announcementData->updated_at = (string) $announcementData->updated_at;

        if (!is_array($announcementData->packages))
            $announcementData->packages = array();

        return $announcementData;
    }
}

endif;