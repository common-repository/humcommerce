<?php
/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since 1.0.0
 *
 * @package humcommerce
 * @subpackage humcommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    humcommerce
 * @subpackage humcommerce/includes
 */
class Humcommerce {

	const REC_TABLE = 'magic_recordings';

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Humcommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $humcommerce    The string used to uniquely identify this plugin.
	 */
	protected $humcommerce;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'HUMCOMMERCE_VERSION' ) ) {
			$this->version = HUMCOMMERCE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->humcommerce = 'humcommerce';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Install recordings table
	 */
	public static function install_tables() {
		global $wpdb;
		$table_name      = $wpdb->prefix . self::REC_TABLE;
		$charset_collate = $wpdb->get_charset_collate();

		$ddl = "CREATE TABLE IF NOT EXISTS {$table_name} (
					id INT(11) unsigned NOT NULL AUTO_INCREMENT,
					idloghsr INT(11) unsigned NOT NULL,
					idsitehsr INT(11) unsigned NOT NULL,
					cart_value FLOAT (8,2) default 0.00,
					location_country varchar (120),
					is_abandon_cart tinyint(1) NOT NULL default 0,
					recording_url varchar (255),
					recording_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
					dead_clicks TEXT NOT NULL,
					errors TEXT NOT NULL,
					PRIMARY KEY (`id`),
					KEY `magic_recording_date_idx` (`recording_date`)
				) $charset_collate";
		$wpdb->query( $ddl );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Humcommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Humcommerce_Settings. Defines settings.
	 * - Humcommerce_Admin. Defines all hooks for the admin area.
	 * - Humcommerce_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-humcommerce-loader.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-humc-utils.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-magic-api.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-humcommerce-admin.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-humcommerce-public.php';

		$this->loader = new Humcommerce_Loader();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Humcommerce_Admin( $this->get_humcommerce(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'activated_plugin', $plugin_admin, 'humcommerce_activation_redirect', 10, 1 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Humcommerce_Public();

		$this->loader->add_action( 'wp_head', $plugin_public, 'add_humcommerce_script_to_wp_head' );

		add_action( 'rest_api_init', array( $this, 'register_api_endpoints' ) );

	}

	/**
	 * Register rest endpoints
	 */
	public function register_api_endpoints() {

		register_rest_route(
			'humcommerce/v1',
			'verify-domain',
			array(
				'method'              => 'GET',
				'callback'            => array( $this, 'verify_domain' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Verify domain
	 *
	 * @param WP_REST_Request $request Rest request object.
	 */
	public function verify_domain( WP_REST_Request $request ) {

		if ( get_transient( '__humc_auth_nonce' ) !== $request->get_param( '_wp_nonce' ) ) {
			// nonce sent from app server does not match send 403.
			wp_die( 'Unauthorized request', 403 );
		}

		wp_die( 'ok', 200 );
	}



	/**
	 * Returns the plugin name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_humcommerce() {
		return $this->humcommerce;
	}

	/**
	 * Returns the plugin version.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Runs the plugin.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Returns the Loader instance.
	 *
	 * $since 1.0.0
	 *
	 * @return Humcommerce_Loader
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Returns plugin action links.
	 *
	 * @since 1.0.0
	 * @param string $links Links to be updated.
	 *
	 * @return array
	 */
	public function humcommerce_plugin_action_links( $links ) {
		$setting_url = 'admin.php?page=humcommerce-settings';
		$links[]     = '<a href="' . get_admin_url( null, $setting_url ) . '">Settings</a>';
		return $links;
	}
}
