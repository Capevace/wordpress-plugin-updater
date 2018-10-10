<?php

namespace MatthiasWeb\WPU\V4;

if (!class_exists('\\MatthiasWeb\\WPU\\V4\\ClientConfig')):

/**
 * The ClientConfig is a class that just saves a config in a structured way. Nothing special here.
 */
class ClientConfig
{
    public $serverUrl;
    public $name;
    public $version;
    public $slug;
    public $path; // The full path to the package.
    public $file; // The basename of the package (package/package.php)
    public $envatoItemId;

    public function __construct($serverUrl, $config)
    {
        $this->serverUrl = untrailingslashit($serverUrl);

        $this->name = array_key_exists('name', $config)
            ? $config['name']
            : 'WPLS Plugin';

        $this->version = array_key_exists('version', $config)
            ? strval($config['version'])
            : '0.0.0';

        $this->envatoItemId = array_key_exists('envatoItemId', $config)
            ? strval($config['envatoItemId'])
            : '00000000';

        if (!array_key_exists('slug', $config)) {
            return new \WP_Error('invalid-config', 'You need to configure a "slug".');
        } else {
            $this->slug = trim($config['slug']);
        }

        if (!array_key_exists('path', $config)) {
            return new \WP_Error('invalid-config', 'You need to configure a "path".');
        } else {
            $this->path = untrailingslashit($config['path']);
            $this->file = plugin_basename($this->path);
        }
    }
}

endif;
