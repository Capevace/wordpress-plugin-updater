<?php 

namespace Smoolabs\V3;

if (!class_exists('\\Smoolabs\\V3\\Plugin_Updater', false)) :

/**
 * The main plugin updater class. Used for interaction between updater and implementing plugin.
 */
class PluginUpdater 
{
	/**
	 * The plugin updater version.
	 * @var string
	 */
	public static $VERSION = '3.0.0';

	/**
	 * The slugs of installed plugins.
	 * @var array
	 */
	public static $installedPlugins = array();

	/**
	 * The name of the plugin.
	 * @var string
	 */
	protected $pluginName;

	/**
	 * The plugin version.
	 * @var string
	 */
	protected $pluginVersion;

	/**
	 * The absolute path to the plugin main file.
	 * @var string
	 */
	protected $pluginPath;

	/**
	 * The plugin basename of the absolute path.
	 * @var string
	 */
	protected $pluginFile;

	/**
	 * The plugin slug.
	 * @var string
	 */
	protected $pluginSlug;

	/**
	 * The URL pointing to the WordPress License Server instance.
	 * @var string
	 */
	protected $serverUrl;

	/**
	 * The cached activation state.
	 * @var null|bool
	 */
	protected $isActivated = null;

	/**
	 * The license settings instance. Handles the activation UI and saving the licenses.
	 * @var LicenseSettings
	 */
	protected $licenseSettings;

	/**
	 * The server communicator used for communication with the WPLS instance.
	 * @var ServerCommunicator
	 */
	protected $serverCommunicator;

	/**
	 * The class handling all ajax communication with UI.
	 * @var AjaxHandler
	 */
	protected $ajaxHandler;

	/**
	 * The class handling the fetching and displaying of announcements.
	 *
	 * @var AnnouncementService
	 */
	protected $announcementService;

	/**
	 * Initialize the plugin updater.
	 * @param array $config The configuration for the updater.
	 */
	public function __construct($config)
	{
		// Merge config with default one so all options are set.
		$defaultConfig = array(
			'name'         => 'WordPress License Server Plugin',
			'version'      => '0.0.0',
			'path'         => null,
			'slug'         => null,
			'serverUrl'    => null,
			'envatoItemId' => null
		);
		$config = array_merge($defaultConfig, $config);

		$this->pluginName    = $config['name'];
		$this->pluginVersion = $config['version'];
		$this->pluginPath    = $config['path'];
		$this->pluginFile    = plugin_basename($this->pluginPath);
		$this->pluginSlug    = $config['slug'];
		$this->serverUrl     = untrailingslashit($config['serverUrl']);

		// Add to global list of plugins using WPLS
		array_push(static::$installedPlugins, $this->pluginSlug);

		// Initialize Systems
		$this->licenseSettings     = new LicenseSettings($config);
		$this->serverCommunicator  = new ServerCommunicator($config);
		$this->announcementService = new AnnouncementService($config, $this->serverCommunicator);
		$this->ajaxHandler         = new AjaxHandler($config, $this->serverCommunicator);

		$update_checker = $this->setupPluginUpdateChecker();
	}

	/**
	 * Sets up the plugin update checker.
	 * @return Plugin_UpdateChecker The update checker.
	 */
	private function setupPluginUpdateChecker()
	{
		include_once __DIR__ . '/../../plugin-update-checker-4.4/plugin-update-checker.php';
		$update_checker = \Puc_v4_Factory::buildUpdateChecker(
			$this->serverUrl . '/api/v1/packages/' . $this->pluginSlug . '/metadata',
			$this->pluginPath,
			$this->pluginSlug
		);

		// Add query arg filter to add license and metadata to requests.
		$update_checker->addQueryArgFilter(array($this->serverCommunicator, 'filterPluginUpdateCheckerQuery'));

		return $update_checker;
	}

	/**
	 * Checks if the plugin has been activated or not.
	 * @return bool Activation state.
	 */
	public function isActivated()
	{
		// If value is not cached yet, cache it.
		if ($this->isActivated === null) {
			$this->isActivated = LicenseSettings::hasLicenseSaved($this->pluginSlug);
		}

		return $this->isActivated;
	}
}

endif;