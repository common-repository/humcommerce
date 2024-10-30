<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.humcommerce.com
 * @since             1.0.0
 * @package           humcommerce
 *
 * @wordpress-plugin
 * Plugin Name:       HumCommerce
 * Plugin URI:        https://wordpress.org/plugins/humcommerce/
 * Description:       HumCommerce WordPress plugin to Record, Analyze & Convert your visitors.
 * Version:           3.0.9
 * Author:            HumCommerce
 * Author URI:        https://www.humcommerce.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       humcommerce
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HUMCOMMERCE_VERSION', '3.0.9' );

if ( ! defined( 'HUMCOMMERCE_HOST' ) ) {
	define( 'HUMCOMMERCE_HOST', 'https://app.humcommerce.com' );
}
if ( ! defined( 'HUMCOMMERCE_PLUGIN_PATH' ) ) {
	define( 'HUMCOMMERCE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'HUMCOMMERCE_PLUGIN_URL' ) ) {
	define( 'HUMCOMMERCE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}


require plugin_dir_path( __FILE__ ) . 'includes/class-humcommerce.php';

/**
 * Unschedule cron hook
 *
 * @since 3.0.0
 */
function uninstall_magic_plugin() {
	$timestamp = wp_next_scheduled( 'wp_magic_fetch_cron_hook' );
	wp_unschedule_event( $timestamp, 'wp_magic_fetch_cron_hook' );
}

register_deactivation_hook( __FILE__, 'uninstall_magic_plugin' );

/**
 * Install tables and do version upgrade
 *
 * @since 3.0.0
 */
function activate_humcommerce_magic() {
	$curr_version = get_option( 'humcommerce_magic_version', true );
	if ( false === $curr_version || version_compare( $curr_version, '3.0.0', '<' ) ) {
		Humcommerce::install_tables();

		$options = get_option( 'humcommerce_options' );
		if ( is_array( $options ) && isset( $options['si'] ) ) {
			update_option( 'humc_site', $options['si'], false );
			delete_option( 'humcommerce_options' );
		}
	}
	update_option( 'humcommerce_magic_version', HUMCOMMERCE_VERSION, false );
	$humc_active_dt = get_option( 'humcommerce_active_date' );
	if ( empty( $humc_active_dt ) ) {
		update_option( 'humcommerce_active_date', gmdate( 'd-m-Y' ) );
	}
}

add_action( 'admin_init', 'activate_humcommerce_magic' );

/**
 * Feedback form
 */

if ( ! function_exists( 'humc_analytics' ) ) {
	/**
	 * Helper function to access SDK.
	 *
	 * @return Analytics
	 */
	function humc_analytics() {
		global $humc_analytics;

		if ( ! isset( $humc_analytics ) ) {
			// Include Analytics SDK.
			require_once dirname( __FILE__ ) . '/analytics/start.php';

			$humc_analytics = ras_dynamic_init(
				array(
					'id'              => '25',
					'slug'            => 'humcommerce',
					'product_name'    => 'Humcommerce',
					'module_type'     => 'plugin',
					'version'         => HUMCOMMERCE_VERSION,
					'plugin_basename' => 'humcommerce/humcommerce.php',
					'plugin_url'      => HUMCOMMERCE_PLUGIN_URL,
				)
			);
		}

		return $humc_analytics;
	}

	// Init Analytics.
	humc_analytics();
	// SDK initiated.
	do_action( 'humc_analytics_loaded' );

}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_humcommerce() {
	$plugin = new Humcommerce();
	$plugin->run();

}

run_humcommerce();
