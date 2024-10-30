<?php
/**
 * HumCommerce Magic table
 *
 * @since      3.0.0
 *
 * @package    humcommerce
 * @subpackage humcommerce/includes
 */

/**
 * Class Magic_Report
 */
class Magic_Report extends WP_List_Table {


	/**
	 * Magic_Report constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'recording',
				'plural'   => 'recordings',
				'ajax'     => false,
				'screen'   => 'magic-recordings',
			)
		);
	}

	/**
	 * Returns an array of column names for the table.
	 *
	 * @return string[] Array of column names keyed by their ID.
	 */
	public function get_columns() {
		return array(
			'location_country' => __( 'Country', 'humcommerce' ),
			'cart_value'       => __( 'Cart Value', 'humcommerce' ),
			'recording_reason' => __( 'Reason', 'humcommerce' ),
			'watch'            => __( 'Action', 'humcommerce' ),
		);
	}

	/**
	 * Override Display function of WordPress
	 */
	public function display() {
		$singular = $this->_args['singular'];

		$this->display_tablenav( 'top' );

		$this->screen->render_screen_reader_content( 'heading_list' );
		$date             = isset( $_GET['recording-date'] ) ? gmdate( 'd M Y', strtotime( sanitize_text_field( $_GET['recording-date'] ) ) ) : gmdate( 'd M Y', strtotime( '-1 days' ) );
		$transient_date   = isset( $_GET['recording-date'] ) ? gmdate( 'Y-m-d', strtotime( sanitize_text_field( $_GET['recording-date'] ) ) ) : gmdate( 'Y-m-d', strtotime( '-1 days' ) );
		$transient_name   = 'estimated_Revenue_Lost' . $transient_date;
		$estimate_revenue = get_transient( $transient_name );
		if ( ! empty( $estimate_revenue ) ) {

			$site_name        = get_bloginfo( 'name' );
			$estimate_revenue = Humc_Utils::format_price( $estimate_revenue );
			?>
			<div class="humc-text">
				Here's a summary of how <?php echo esc_html( $site_name ); ?> did on <?php echo esc_html( $date ); ?>. <strong>Estimated Revenue lost from abandoned carts is <?php echo $estimate_revenue; ?>.</strong>
                Please have a look at the recordings below. <i>Please note: All recordings are visible only in the <a href="https://www.humcommerce.com/go-pro" target="_blank">Pro Plan</a>. </i>
			</div>
			<?php
		}

		?>


		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<thead>
			<tr style="margin:1em;">
				<?php $this->print_column_headers(); ?>
			</tr>
			</thead>

			<tbody id="the-list"
				<?php
				if ( $singular ) {
					echo esc_attr__( " data-wp-lists=list:$singular" );
				}
				?>
			>
			<?php $this->display_rows_or_placeholder(); ?>
			</tbody>

		</table>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Generate the tbody element for the list table.
	 *
	 * @since 3.1.0
	 */
	public function display_rows_or_placeholder() {
		if ( $this->has_items() ) {
			$this->display_rows();
		} else {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . esc_attr__( $this->get_column_count() ) . '">';
			$this->no_items();
			echo '</td></tr>';
		}
	}

	/**
	 * Execute function if its First Time Installation
	 *
	 * @param bool $with_id Whether to set the ID attribute or not.
	 */
	public function print_column_headers( $with_id = true ) {
		if ( ! $this->isFirstTimeInstallation() ) {
			parent::print_column_headers( $with_id );
		}
	}

	/**
	 * Execute function if its First Time Installation
	 *
	 * @param string $which Top or Bottom.
	 */
	public function display_tablenav( $which ) {
		if ( ! $this->isFirstTimeInstallation() ) {
			parent::display_tablenav( $which );
		}
	}

	/**
	 * Pagination
	 *
	 * @param string $which Top or Bottom.
	 */
	public function pagination( $which ) {
		if ( 'bottom' !== $which ) {
			return;
		}
		if ( empty( $this->_pagination_args ) ) {
			return;
		}

		$total_items     = $this->_pagination_args['total_items'];
		$total_pages     = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		if ( 'top' === $which && $total_pages > 1 ) {
			$this->screen->render_screen_reader_content( 'heading_pagination' );
		}

		$output = '<span class="displaying-num">' . sprintf(
			/* translators: %s: Number of items. */
			_n( '%s item', '%s items', $total_items ),
			number_format_i18n( $total_items )
		) . '</span>';

		$current              = $this->get_pagenum();
		$removable_query_args = wp_removable_query_args();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( $removable_query_args, $current_url );

		$page_links = array();

		$total_pages_after = '</span></span>';

		$disable_first = false;
		$disable_last  = false;

		if ( 1 === $current ) {
			$disable_first = true;
		}

		if ( 2 === $current ) {
			$disable_first = true;
		}

		if ( $total_pages === $current ) {
			$disable_last = true;
		}

		if ( ( $total_pages - 1 ) === $current ) {
			$disable_last = true;
		}

		if ( $disable_first ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a class='first-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				__( 'First page' ),
				'&lsaquo;'
			);
		}

		if ( $total_pages <= 10 ) {
			for ( $i = 1; $i <= $total_pages; $i++ ) {
				$page_links[] .= sprintf(
					"<a class='button' href='%s' ><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
					esc_url( add_query_arg( 'paged', min( $total_pages, $i ), $current_url ) ),
					__( 'page ' . $i ),
					$i
				);
			}

			$page_links[] .= $total_pages_after;
		} else {
			for ( $i = 1; $i <= 10; $i++ ) {
					$page_links[] .= sprintf(
						"<a class='button' href='%s' ><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
						esc_url( add_query_arg( 'paged', min( $total_pages, $i ), $current_url ) ),
						__( 'page ' . $i ),
						$i
					);
			}
		}

		if ( $disable_last ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[] = sprintf(
				"<a  class='last-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				__( 'Last page' ),
				'&rsaquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class .= ' hide-if-js';
		}
		$active_flag = false;
		foreach ( $page_links as $key => $value ) {

			if ( strpos( htmlspecialchars_decode( $value ), $current_url ) !== false ) {
				$str     = $page_links[ $key ];
				$new_str = $str;
				if ( isset( $_GET['paged'] ) ) {
					if ( $_GET['paged'] === $key ) {
						$active_flag = true;
						$new_str     = str_replace( 'button', 'button active', $str );
					}
				}
				$page_links[ $key ] = $new_str;
			}
		}
		if ( ! $active_flag ) {
			$str           = $page_links[1];
			$new_str       = str_replace( 'button', 'button active', $str );
			$page_links[1] = $new_str;
		}
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
	}

	/**
	 * Function to Check first time installtion
	 */
	private function isFirstTimeInstallation() {
		global $wpdb;
		$table_name = $wpdb->prefix . Humcommerce::REC_TABLE;
		$result     = $wpdb->get_row( 'SELECT * FROM ' . $table_name . ' ORDER BY id DESC LIMIT 1' );
		return empty( $result );
	}
	/**
	 * Display when there are no items.
	 */
	public function no_items() {

		$dt = isset( $_GET['recording-date'] ) ? gmdate( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_GET['recording-date'] ) ) ) ) : $this->get_report_date();
		if ( $this->isFirstTimeInstallation() ) {
			?>
			<div style="text-align:center;">
				<h1>You have successfully set up HumCommerce and we have started collecting and analyzing your data. We'll send you an email at <?php echo bloginfo( 'admin_email' ); ?> as soon as your report is ready. (usually within 18-24
                    hours)</h1>
				<div class="humc-text">
                    Right now Magic is looking at your visitors. For rest of the day, it will analyze their experience. Once done, we will send you a mail that will contain visitor's recordings that need your attention.
				</div>
				<img src="<?php echo esc_url( plugins_url( '/images/humcmagic-video.png', __FILE__ ) ); ?>" alt="HumCommerce image"/>
			</div>
			<?php
			return;
		}
		?>

		<div style="text-align:center;">
			<h1> We looked at all visitor recordings for <?php echo esc_html( gmdate( 'd-m-Y', strtotime( $dt ) ) ); ?>. There were no errors on the site.<br /> No need to watch any session recordings today. </h1>
			<img src="<?php echo esc_url( plugins_url( '/images/humcmagic-video.png', __FILE__ ) ); ?>" alt="HumCommerce image"/>
		</div>

		<?php
	}


	/**
	 * Get Dates Difference
	 *
	 * @param string $date1 Recording.
	 * @param string $date2 Recording.
	 */
	public function dateDiff( $date1, $date2 ) {
		$date1_ts = strtotime( $date1 );
		$date2_ts = strtotime( $date2 );
		$diff     = $date2_ts - $date1_ts;
		return round( $diff / 86400 );
	}
	/**
	 * Get Report Date
	 */
	private function get_report_date() {

		$processed_rec = 'processed_rec' . gmdate( 'Y-m-d', strtotime( '-1 DAYS' ) );
		$did_cron_run  = get_transient( $processed_rec );
		if ( $did_cron_run ) {
			return gmdate( 'Y-m-d', strtotime( '-1 DAYS' ) );
		}
		return gmdate( 'Y-m-d', strtotime( '-2 DAYS' ) );
	}

	/**
	 * Prepare list of recordings to be shown.
	 */
	public function prepare_items() {
		global $wpdb;

		$yesterday = $this->get_report_date();
		$date      = isset( $_GET['recording-date'] ) ? sanitize_key( $_GET['recording-date'] ) : $yesterday; // phpcs:ignore WordPress.Security

		// Don't let user manipulate date get param to go back more than 8 days.
		if ( ! strtotime( $date ) || strtotime( $date ) < strtotime( '-8 DAYS' ) ) {
			$date = $yesterday;
		}

		$table = $wpdb->prefix . \Humcommerce::REC_TABLE;

		$limit = $this->get_items_per_page( 'recordings_per_page' );
		$paged = $this->get_pagenum();

		$offset  = ( $paged - 1 ) * $limit;
		$res     = $wpdb->get_row( // phpcs:ignore WordPress.DB
			$wpdb->prepare( "SELECT count(*) as total FROM $table WHERE recording_date = '%s'", array( $date ) ), // phpcs:ignore WordPress.DB
			ARRAY_A
		);
		$total   = isset( $res['total'] ) ? $res['total'] : 0;
		$results = $wpdb->get_results( // phpcs:ignore WordPress.DB
			$wpdb->prepare(
				"SELECT * FROM $table WHERE recording_date='%s' LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB
				array( $date, $limit, $offset )
			),
			ARRAY_A
		);

		$this->items = $results;

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $limit,
			)
		);
	}

	/**
	 * Render country icon
	 *
	 * @param array $item Recording.
	 */
	public function column_location_country( $item ) {
		$countries    = include plugin_dir_path( __FILE__ ) . 'flags-country-list.php';
		$url          = HUMCOMMERCE_HOST . '/plugins/Morpheus/icons/dist/flags/' . $item['location_country'] . '.png';
		$country_code = $item['location_country'];
		?>
		<img height="32px" data-toggle="tooltip" data-placement="bottom" title="<?php echo esc_attr( $countries[ $country_code ] ); ?>" src="<?php echo esc_url( $url ); ?>"
			alt="<?php echo esc_attr( $item['location_country'] ); ?>"/>
		<?php
	}

	/**
	 * Display recording reason
	 *
	 * @param array $item recording.
	 */
	public function column_recording_reason( $item ) {
		$clicks          = json_decode( $item['dead_clicks'], true );
		$errors          = json_decode( $item['errors'], true );
		$is_abandoned    = (bool) $item['is_abandon_cart'];
		$text            = '';
		$popover_content = "<div class='humc-popover-content'>";

		if ( $is_abandoned ) :
			$text            .= 'Cart was abandoned. ';
			$popover_content .= '<span>Cart was abandoned.</span>';
		endif;

		if ( is_array( $errors ) && count( $errors ) ) :
			$popover_content .= "<h4 class='humc-popover-content-heading'>User saw following Errors:</h4><ul class='humc-popover-content-list'>";

			foreach ( $errors as $e ) :
				if ( substr( esc_html( $e['text'] ), -1 ) === '.' ) {
					$text .= esc_html( $e['text'] ) . ' ';
				} else {
					$text .= esc_html( $e['text'] ) . '. ';
				}
				$popover_content .= '<li>' . esc_html( $e['text'] ) . '</li>';
			endforeach;
			$popover_content .= '</ul>';
		endif;

		if ( is_array( $clicks ) && count( $clicks ) ) :
			$popover_content .= "<h4 class='humc-popover-content-heading'>User Experience DeadClicks:</h4><ul class='humc-popover-content-list'>";
			foreach ( $clicks as $e ) :
				$text            .= 'User clicked on' . esc_html( $e['key'] ) . ' ' . esc_html( $e['count'] ) . ' times but nothing happend. ';
				$popover_content .= '<li>User clicked on' . esc_html( $e['key'] ) . ' ' . esc_html( $e['count'] ) . ' times but nothing happend</li>';
			endforeach;
			$popover_content .= '</ul>';
		endif;
		$popover_content .= '</div>';
		if ( strlen( $text ) > 85 ) {
			?>
			<div class="rec-reason-txt">
				<?php echo esc_html( substr_replace( $text, '...', 85 ) ); ?>
			</div>
			<div class="rec-reason-info">
				<a title="Reasons Summery" data-html="true"  data-placement="left" data-toggle="popover" data-content="<?php echo esc_attr( $popover_content ); ?>">
					<i class="dashicons dashicons-info"></i>
				</a>
			</div>
			<?php
		} else {
			?>
			<span><?php echo esc_html( $text ); ?></span>
			<?php
		}
	}


	/**
	 * Display cart value
	 *
	 * @param array $item recording.
	 */
	public function column_cart_value( $item ) {

		$value = $item['cart_value'];
		if ( function_exists( 'wc_price' ) ) {
			$value = wc_price( $value );
		}
		echo $value;
	}

	/** Recording url
	 *
	 * @param array $item recording.
	 */
	public function column_watch( $item ) {
		$token    = get_option( 'humc_token' );
		$logo_url = plugins_url( '/images/play-button.png', __FILE__ );
		$url      = $item['recording_url'] . '&token_auth=' . $token;
		?>
		<a target="_blank" href="<?php echo esc_url( $url ); ?>">
			<img src="<?php echo esc_attr( $logo_url ); ?>" alt="Play recording"/>
		</a>
		<?php
	}

	/**
	 * Get First Recording Fetch Date
	 */
	private function getFirstRecordingFetchDate() {
		global $wpdb;
		$table_name = $wpdb->prefix . Humcommerce::REC_TABLE;
		$result     = $wpdb->get_row( 'SELECT `recording_date` FROM ' . $table_name . ' ORDER BY `recording_date` ASC LIMIT 1', ARRAY_A );
		return $result;
	}

	/**
	 * Display dates selector
	 *
	 * @param string $which nav.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}
		$selected = isset( $_GET['recording-date'] ) ? sanitize_key( $_GET['recording-date'] ) : $this->get_report_date(); // phpcs:ignore WordPress.Security.NonceVerification

		$active_dt = get_option( 'humcommerce_active_date' );

		$dt = $this->getFirstRecordingFetchDate();

		$active_dt = gmdate( 'Y-m-d' );

		if ( isset( $dt['recording_date'] ) ) {
			$active_dt = gmdate( 'Y-m-d', strtotime( $dt['recording_date'] ) );
		}

		$date_diff = $this->dateDiff( gmdate( 'Y-m-d', strtotime( $active_dt ) ), gmdate( 'Y-m-d' ) );
		$min_date  = ( $date_diff < 8 ) ? gmdate( 'Y-m-d', strtotime( '-' . $date_diff . ' days' ) ) : gmdate( 'Y-m-d', strtotime( '-8 days' ) );
		?>
		<form method="GET">

			<input type="hidden" name="page" value="recommended-recordings"/>
			<input type="date" id="humc-magic-date" name="recording-date" value="<?php echo esc_attr( $selected ); ?>"  max="<?php echo esc_attr( $this->get_report_date() ); ?>"  min="<?php echo esc_attr( $min_date ); ?>" onchange="this.form.submit()" class="form-control">
		</form>

		<?php
	}

	/** Get last 7 days
	 *
	 * @return array
	 */
	protected function get_date_range() {
		$dates = array();

		foreach ( range( 1, 7 ) as $d ) {
			$dates[] = strtotime( '-' . $d . ' DAYS' );
		}

		return $dates;
	}

}
