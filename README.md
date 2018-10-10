# wordpress-plugin-updater
The WordPress Plugin Integration for the WordPress License Server.

## Preamble
This is a forked repository of the original code of [Capevace/wordpress-plugin-updater](https://github.com/Capevace/wordpress-plugin-updater).
This forked repository is also published to packagist so it can be installed through composer. Please note the npm script `npm run replace`
that replaces the original `smoolabs`/`Capevace` namespaces with `MatthiasWeb` to avoid namespace conflicts. The Thanks goes to 
Capevace (original author), this repository simply adds some modifications to meet the needs for MatthiasWeb plugins.

## Usage
There's two ways you can integrate this and enable automatic updates for your own plugin.

### Using Composer
If you're already using Composer, you'll know what to do.

If not, you'll need to install Composer on your computer and run `composer init`. This will initialize composer in your packages root.

Once that is complete, run this:
```shell
composer require matthiasweb/wordpress-plugin-updater:dev-master
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
$client = \MatthiasWeb\WPU\V4\WPLSController::initClient('http://url-to-wpls.com', array(
    'name'      => 'Example Plugin Name',
    'version'   => '1.0.0',
    'path'      => __FILE__,
    'slug'      => 'example-plugin-slug'
));
```
Now, replace *Example Plugin Name* with your plugins name, *http://update-server-url.com* with the URL where you hosted the update server, *my-example-plugin* with your plugin slug (for example the plugin folders name) and *1.0.0* with your current plugins version.

That's all you have to do! The plugin will now receive automatic updates once you make them available on your server (of course, only if the user supplied a license)!

## Disabling functionality until License is entered
You may want to stop your buyers from using your plugin until they have entered their licenses. You can easily disable functionality like this:
```php
// Your Updater instance
$client = \MatthiasWeb\...;

if ($client->isActivated()) {
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
```

*Please make sure that this complies with Envato's rules on locking fieatures behind licenses! The plugin may not be accepted otherwise.*