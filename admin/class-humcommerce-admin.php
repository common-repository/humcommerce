<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    humcommerce
 * @subpackage humcommerce/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    humcommerce
 * @subpackage humcommerce/admin
 */
class Humcommerce_Admin {

	/**
	 * The name of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $humcommerce    The name of this plugin.
	 */
	private $humcommerce;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $humcommerce       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $humcommerce, $version ) {

		$this->humcommerce = $humcommerce;
		$this->version     = $version;

		$this->maybe_create_scheduled_event();

		$this->api = \Magic_Api::get_instance();

		if ( isset( $_GET['page'] ) && $_GET['page'] === 'recommended-recordings' ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'load_recording_css' ) );
			add_action( 'admin_notices', array( $this, 'remove_other_admin_notices' ), 1 );
		}
		add_action( 'admin_init', array( $this, 'register_humc_settings' ) );
		add_action( 'wp_magic_fetch_cron_hook', array( $this, 'get_recordings_cron_exec' ) );
		add_action( 'admin_menu', array( $this, 'add_report_page' ) );
		add_action( 'admin_post_humc_create_magic_account', array( $this, 'create_humcommerce_account' ) );
		add_action( 'admin_post_report_magic_error', array( $this, 'report_error' ) );

	}

	/**
	 * Report error back to humcommerce support
	 */
	public function report_error() {

		if ( ! check_admin_referer( 'humc_report_error' ) ) {
			wp_die( 'Unauthorized request', 401 );
		}

		$error = isset( $_POST['humc_error'] ) ? sanitize_text_field( wp_unslash( $_POST['humc_error'] ) ) : false;

		if ( false === $error ) {
			$url = admin_url( 'admin.php?page=recommended-recordings' );
			wp_safe_redirect( $url );
			exit();
		}

		$site = home_url();
		$user = wp_get_current_user();

		$subject = sprintf( 'Error while setting up magic on %s', $site );
		$body    = sprintf( '%s reported following error : %s', $user->user_email, $error );

		wp_mail( 'support@humcommerce.com', $subject, $body );

		$url = admin_url( 'admin.php?page=recommended-recordings' );
		wp_safe_redirect( $url );
		exit();
	}

	/**
	 * Register site and token settings.
	 */
	public function register_humc_settings() {
		register_setting( 'humc_magic', 'humc_site' );
		register_setting( 'humc_magic', 'humc_token' );
	}

	/**
	 * Add menu
	 */
	public function add_report_page() {
		add_menu_page(
			'Must watch Customer recordings',
			'HumCommerce',
			'read',
			'recommended-recordings',
			array( $this, 'create_report_page' ),
			esc_url( plugins_url( 'images/icon.png', __FILE__ ) )
		);

		add_submenu_page(
			'recommended-recordings',
			'Must watch Customer recordings',
			'Recordings',
			'read',
			'recommended-recordings',
			array( $this, 'create_report_page' ),
			10
		);
	}

	/**
	 * Options page callback.
	 *
	 * @since 1.0.0
	 */
	public function create_settings_page() {
		// Set class property.
		$site  = get_option( 'humc_site' );
		$token = get_option( 'humc_token' );

		$email        = get_option( 'admin_email' );
		$logo_url     = plugins_url( '/images/logo.png', __FILE__ );
		$setting_hook = __FILE__;
		include_once plugin_dir_path( __FILE__ ) . 'views/settings-page.php';
	}

	/**
	 * Load css
	 */
	public function load_recording_css() {
		wp_enqueue_style( $this->humcommerce, plugin_dir_url( __FILE__ ) . 'css/recording-table.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->humcommerce . '-css', plugin_dir_url( __FILE__ ) . 'css/humcommerce-admin.css', array(), $this->version, 'all' );
		wp_enqueue_script( 'bootstrap_js', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array(), $this->version, false );
		wp_enqueue_script( 'register_table', plugin_dir_url( __FILE__ ) . 'js/recording-table.js', array(), $this->version, false );
	}

	/**
	 * Remove all other plugin notices except Our plugin Notices
	 */
	public function remove_other_admin_notices() {
		global $wp_filter;
		remove_all_actions( 'all_admin_notices' );
		if ( isset( $wp_filter['admin_notices'] ) ) {
			foreach ( $wp_filter['admin_notices']->callbacks as $index => $filter ) {
				foreach ( $filter as $f ) {
					$str = _wp_filter_build_unique_id( 'admin_notices', $f['function'], 10 );
					if ( strpos( $str, 'humcommerce' ) === false && strpos( $str, 'ask_for_usage' ) === false ) {
						remove_action( 'admin_notices', $f['function'], $index );
					}
				}
			}
		}
	}

	/**
	 * Render recordings list or getting started page
	 */
	public function create_report_page() {
		include plugin_dir_path( __FILE__ ) . 'views/mascot.php';

		if ( $this->is_magic_setup() ) {
			$this->render_report();
			return;
		}
		$this->render_getting_started();

	}

	/**
	 * Check if magic is setup
	 *
	 * @return bool
	 */
	private function is_magic_setup() {
		$site  = get_option( 'humc_site' );
		$token = get_option( 'humc_token' );

		if ( $site && $token ) {
			return true;
		}
		return false;

	}

	/**
	 * Render magic table
	 */
	private function render_report() {
		require_once plugin_dir_path( __FILE__ ) . '/class-magic-report.php';

		$table = new Magic_Report();
		$table->prepare_items();
		?>
		<div class="wrap">
			<div class="humc-logo">
				<img src="<?php echo esc_url( plugins_url( '/images/humcmagiclogo.png', __FILE__ ) ); ?>">
			</div>
		</div>
		<div class="wrap humc-recordings">
			<h1 class="humc-recordings-table-heading " style="display: none;">Magic Reports</h1>
			<?php $table->display(); ?>
		</div>

		<?php
	}

	/**
	 * Render getting started
	 */
	private function render_getting_started() {
		require_once plugin_dir_path( __FILE__ ) . '/getting-started.php';
	}

	/**
	 * Schedule cron event for fetching recordings
	 */
	public function maybe_create_scheduled_event() {
		$tommorrow_nine = strtotime( gmdate( 'Y-m-d' ) ) + ( 12 * HOUR_IN_SECONDS );
		if ( ! wp_next_scheduled( 'wp_magic_fetch_cron_hook' ) ) {
			wp_schedule_event( $tommorrow_nine, 'daily', 'wp_magic_fetch_cron_hook' );
		}
	}

	/**
	 * Fetch recordings
	 */
	public function get_recordings_cron_exec() {

		$token         = get_option( 'humc_token' );
		$idsite        = get_option( 'humc_site' );
		$day           = gmdate( 'Y-m-d', strtotime( 'yesterday' ) );
		$processed_rec = 'processed_rec' . $day;
		set_transient( $processed_rec, 1, 60 * 60 * 24 * 7 );

		$data = $this->api->get_recordings( $idsite, $token, $day );

		if ( isset( $data['estimatedRevenueLost'] ) && $data['estimatedRevenueLost'] ) {
			$transient_name = 'estimated_Revenue_Lost' . $day;
			set_transient( $transient_name, $data['estimatedRevenueLost'], 60 * 60 * 24 * 7 );
		}

		if ( $data['error'] ) {
			error_log( $data['message'] ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return;
		}
		if ( count( $data['reportChunks'] ) === 0 ) {
			$summary = array(
				'error'   => true,
				'message' => sprintf( 'We looked at all visitor recordings. There were no errors on the site.%s No need to watch any session recordings today.', "\n" ),
			);
			$this->notify_via_email( $summary );
			return;
		}

		foreach ( $data['reportChunks'] as $report ) {
			$this->insert_recordings( $report );
		}
		if ( count( $data['reportChunks'] ) > 0 ) {
			$summary = $this->get_summary( $data['reportChunks'] );
			$this->notify_via_email( $summary );
		}

	}

	/** Generate summary to send in mail
	 *
	 * @param array $data recording array.
	 * @return array
	 */
	private function get_summary( $data ) {
		$abandon_carts = 0;
		$no_errors     = 0;
		$no_clicks     = 0;
		$revenue_lost  = 0;

		foreach ( $data as $rec ) {

			if ( $rec['isAbandonCart'] ) {
				$abandon_carts++;
				$revenue_lost += $rec['cartValue'];
			}

			if ( count( $rec['deadClicks'] ) ) {
				$no_clicks++;
			}

			if ( count( $rec['errors'] ) ) {
				$no_errors++;
			}
		}
		$revenue_lost = \Humc_Utils::format_price( $revenue_lost );

		return array(
			sprintf( 'Estimated revenue lost %s from %s abandoned cart', $revenue_lost, $abandon_carts ),
			sprintf( '%s users saw some kind of errors. (More about this in your admin area)', $no_errors ),
			sprintf( '%s users clicked around expecting something would happen but nothing happened', $no_clicks ),
		);

	}

	/**
	 * Notify admin via email about new recordings
	 *
	 * @param array $summary array of messages to send in email.
	 */
	private function notify_via_email( $summary ) {
		$email     = get_option( 'admin_email' );
		$blog_name = get_option( 'blogname' );

		ob_start();
		if ( isset( $summary['error'] ) && $summary['error'] ) {
			include_once plugin_dir_path( __FILe__ ) . '/views/no-recordings-email.php';
			/* translators: New email address notification email subject. %s: Site title. */
			$subject = sprintf( __( 'No Session Recordings to watch today for %s' ), $blog_name );
		} else {
			include_once plugin_dir_path( __FILe__ ) . '/views/recording-email.php';
			/* translators: New email address notification email subject. %s: Site title. */
			$subject = sprintf( __( 'Recommended Session Recordings for %s' ), $blog_name );
		}
		$content = ob_get_contents();
		ob_end_clean();

		wp_mail( $email, $subject, $content, array( 'Content-Type:text/html' ) );
	}

	/**
	 * Insert recording in database table
	 *
	 * @param array $row recording row.
	 */
	private function insert_recordings( $row ) {
		global $wpdb;

		$clicks  = wp_json_encode( $row['deadClicks'] );
		$errors  = wp_json_encode( $row['errors'] );
		$abandon = (int) $row['isAbandonCart'];

		$rec_date   = strtotime( $row['recording_date'] );
		$rec_date   = gmdate( 'Y-m-d H:i:s', $rec_date );
		$table_name = $wpdb->prefix . \Humcommerce::REC_TABLE;

		$bind = array(
			$row['idloghsr'],
			$row['idsitehsr'],
			$row['cartValue'],
			$row['location_country'],
			$abandon,
			$row['sessionReplayUrl'],
			$rec_date,
			$clicks,
			$errors,
		);

		$sql = "INSERT INTO `$table_name` 
     (`idloghsr`,`idsitehsr`,`cart_value`,`location_country`,`is_abandon_cart`,`recording_url`,`recording_date`,`dead_clicks`,`errors`)
      VALUES (%d,%d,%f,%s,%d,'%s','%s','%s','%s')";

		$wpdb->query( $wpdb->prepare( $sql, $bind ) ); // phpcs:ignore WordPress.DB
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->humcommerce, plugin_dir_url( __FILE__ ) . 'css/humcommerce-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

	}


	/**
	 * We are not going to use WordPress nonce because it is user dependent.
	 * The nonce created by admin session won't match with nonce created publicly
	 */
	public function create_humcommerce_account() {

		$result = isset( $_POST['_wp_nonce'] ) ? wp_verify_nonce( sanitize_key( $_POST['_wp_nonce'] ), 'humc_account_create' ) : false;

		if ( ! $result ) {
			wp_die( 'Unauthorized request. Go back, refresh the page and try again', 401 );
		}

		// This nonce will expire in 120 seconds so the whole request must complete within 120 seconds.
		$nonce = substr( wp_hash( time() . '|', 'nonce' ), -12, 10 );
		set_transient( '__humc_auth_nonce', $nonce, 120 );

		$email     = get_option( 'admin_email' );
		$domain    = get_site_url();
		$blog_name = get_option( 'blogname' );

		$params = array(
			'email'    => $email,
			'domain'   => $domain,
			'blogName' => $blog_name,
			'nonce'    => $nonce,
		);

		$response = $this->api->create_humcommerce_account( $params );

		if ( $response['error'] ) {
			wp_die( $this->get_error_html( $response['message'] ) ); //phpcs:ignore
		}
			update_option( 'humc_site', $response['site'], false );
			update_option( 'humc_token', $response['token'], false );
			$site_name   = get_bloginfo( 'name' );
			$user        = wp_get_current_user();
			$link        = filter_var( 'https://www.humcommerce.com/', FILTER_SANITIZE_URL );
			$humcom_link = '<a href="' . $link . '" target="_blank">Humcommerce</a>';

			$subject       = sprintf( 'Humcommerce configured on %s', $site_name );
			$email_content = sprintf( 'Hi there,<br><br>Thanks for integrating HumCommerce Magic. We are analyzing your traffic to give you meaningful insights.<br><br>We have built HumCommerce Magic to help you identify & solve your website problems.<br><br>From now on, you\'ll get automated reports every 24 hours about your customer experience.<br>You can see the reasons which caused frustration to users, replay the problem sessions and identify website changes to improve sales.<br><br>All the best,<br>Karthik Magapu<br>CEO <br>%s', $humcom_link );
			wp_mail( $user->user_email, $subject, $email_content, array( 'Content-Type:text/html' ) );

			$url = admin_url( 'admin.php?page=recommended-recordings' );
			wp_safe_redirect( $url );
			exit();

	}

	/**
	 * Get error html
	 *
	 * @param string $message variable is used inside error.php.
	 */
	private function get_error_html( $message ) {
		ob_start();
		include_once plugin_dir_path( __FILe__ ) . '/views/error.php';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}


	/**
	 * Redirects to settings page after user activates the plugin.
	 *
	 * @param string $plugin Name of plugin activated.
	 *
	 * @since 2.1.13
	 */
	public function humcommerce_activation_redirect( $plugin ) {
		if ( plugin_basename( 'humcommerce/humcommerce.php' ) === $plugin ) {
			wp_safe_redirect( admin_url( 'admin.php?page=recommended-recordings' ) );
			exit();
		}
	}

}
