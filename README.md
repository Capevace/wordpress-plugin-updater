# wordpress-plugin-updater
The WordPress Plugin Integration for the WordPress License Server.

## Usage
There's two ways you can integrate this and enable automatic updates for your own plugin.

### Using Composer
If you're already using Composer, you'll know what to do.

If not, you'll need to install Composer on your computer and run `composer init`. This will initialize composer in your plugins root.

Once that is complete, run this:
```shell
composer require smoolabs/wordpress-plugin-updater
```
Composer will then install the integration into the ```vendor/``` folder.

To include the plugin files now, simply include the ```vendor/autoload.php``` file.
```php
<?php

require_once 'vendor/autoload.php';
```

### Without Composer
Download this repository as a .zip file and extract it somewhere into your plugin files.
Then just include the ```loader.php``` file.
```php
<?php

require_once '/path/to/updater/loader.php';
```

## Setup
There's only one thing you'll need to do, to enable the integration once you've included it into your project.

In your plugins main file, paste this code:
```php
$plugin_updater = new \Smoolabs\V1\PluginUpdater(
  'Example Plugin Name',
  'http://update-server-url.com',
  'my-example-plugin',
  __FILE__,
  '1.0.0'
);
```
Now, replace *Example Plugin Name* with your plugins name, *http://update-server-url.com* with the URL where you hosted the update server, *my-example-plugin* with your plugin slug (for example the plugin folders name) and *1.0.0* with your current plugins version.

That's all you have to do! The plugin will now receive automatic updates once you make them available on your server (of course, only if the user supplied a license)!

## Disabling functionality until License is entered
You may want to stop your buyers from using your plugin until they have entered their licenses. You can easily disable functionality like this:
```php
// Your Updater instance
$plugin_updater = new \Smoolabs\...;

if ($plugin_updater->is_activated()) {
  /* 
   * The User has activated the plugin.
   * Add your plugin functionality here.
   */
} else {
  /* 
   * The User has *NOT* activated the plugin.
   * Add activation messages etc here, for example on your plugin settings page.
   */
}
