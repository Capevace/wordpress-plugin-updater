<?php 

namespace Mateffy;

if (!class_exists('\\Mateffy\\Plugin_Updater_v1', false)) :

class Plugin_Updater_v1
{
	private $plugin_path;
	private $plugin_file;
	private $plugin_slug;
	private $updater_url;

	public function __construct($name, $updater_url, $plugin_slug, $plugin_path)
	{
		$this->name        = $name;
		$this->updater_url = untrailingslashit($updater_url);
		$this->plugin_slug = $plugin_slug;
		$this->plugin_path = $plugin_path;
		$this->plugin_file = plugin_basename($plugin_path);
		//add_filter('plugin_action_links_' . $this->plugin_file, array($this, 'display_credential_ui'));

		add_action('admin_print_scripts-plugins.php', array($this, 'print_scripts'));
		add_action('after_plugin_row_' . $this->plugin_file, array($this, 'after_plugin_row'), 10, 2);
		add_action('wp_ajax_mpu_save_license_' . $this->plugin_slug, array($this, 'save_license'));
		add_action('wp_ajax_mpu_validate_license_' . $this->plugin_slug, array($this, 'ajax_validate_license'));
		add_action('admin_notices', array($this, 'admin_notices'));
		add_filter('plugin_action_links_' . $this->plugin_file, array($this, 'filter_action_links'));

		$this->includes();
		$this->setup_updater();
	}

	public function print_scripts()
	{
		wp_register_script(
			'mateffy-plugin-updater-v1', 
			plugin_dir_url(__FILE__) . '/assets/js/credentials.js', 
			array('jquery'),
			'v1'
		);

		wp_enqueue_script('mateffy-plugin-updater-v1');
	}

	public function after_plugin_row($plugin_file, $plugin_data)
	{
		?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				mateffyPluginUpdater100.setupLicenseUI({
					slug: '<?php echo $this->plugin_slug; ?>',
					url: '<?php echo $this->updater_url; ?>',
					license: '<?php echo $this->get_license(); ?>'
				});
			});
		</script>

		<?php
	}

	public function save_license()
	{
		$license = sanitize_text_field($_POST['license_key']);

		$this->set_license($license);

		wp_send_json(array(
			'success' => true
		));
		wp_die();
	}

	public function ajax_validate_license()
	{
		$license = sanitize_text_field($_GET['license_key']);
		$valid   = $this->validate_license($license);

		wp_send_json(array(
			'valid' => $valid
		));
		wp_die();
	}

	public function filter_action_links($actions)
	{
		if (!empty($this->get_license()))
			$actions['enter-license'] = '<a href="#" id="enter-license-' . $this->plugin_slug . '">License Settings</a>';
		else
			$actions['enter-license'] = '<a style="color: #3db634;" href="#" id="enter-license-' . $this->plugin_slug . '">Enter License</a>';

		return $actions;
	}

	private function setup_updater()
	{
		$update_checker = \Puc_v4_Factory::buildUpdateChecker(
			$this->updater_url . '/?action=get_metadata&slug=' . $this->plugin_slug,
			$this->plugin_path,
			$this->plugin_slug
		);

		$update_checker->addQueryArgFilter(array($this, 'update_check_filter'));
	}

	private function includes()
	{
		include_once 'plugin-update-checker-4.4/plugin-update-checker.php';
	}

	public function admin_notices()
	{

		if (!$this->has_license())
			return;

		$activate_url = admin_url('plugins.php#enter-license-' . $this->plugin_slug);

		?>
			<?php var_dump($this->has_license()); ?>
			<div class="notice notice-info is-dismissible">
				<p>
					To completely utilize your copy of <i><?php echo $this->name; ?></i>, please 
					<a href="<?php echo $activate_url; ?>">activates</a> it using the license provided during purchase.
				</p>
			</div>
		<?php
	}

	public function update_check_filter($query_args)
	{
		$license_key = $this->get_license();
		if (!empty($license_key)) {
			$query_args['license_key'] = $license_key;
		}
		return $query_args;
	}

	private function has_license()
	{
		return !empty($this->get_license());
	}

	private function get_license()
	{
		return get_option('mpu_license_' . $this->plugin_slug);
	}

	private function set_license($license)
	{
		update_option('mpu_license_' . $this->plugin_slug, $license);
	}

	private function validate_license($license_key)
	{
		try {
			$response = self::http_request(
				$this->updater_url . '/validate.php?license_key=' . $license_key . '&slug=' . $this->plugin_slug
			);

			$data = json_decode($response);

			return property_exists($data, 'valid') && $data->valid === true;
		} catch (Exception $e) {
			return false;
		}
	}

	private static function http_request($url)
	{
		if (!function_exists('curl_init')){
			return false;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_REFERER, site_url());
		curl_setopt($ch, CURLOPT_USERAGENT, 'MateffyPluginUpdater');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$output = curl_exec($ch);
		curl_close($ch);
		
		return $output;
	}


}

endif;