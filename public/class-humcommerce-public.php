<?php
/**
 * The public facing functionality of the plugin.
 *
 * @since 1.0.0
 *
 * @package humcommerce
 * @subpackage humcommerce/public
 */

/**
 * The public facing functionality of the plugin.
 *
 * @since 1.0.0
 *
 * @package humcommerce
 * @subpackage humcommerce/public
 */
class Humcommerce_Public {



	/**
	 * Add the JavaScript to the head for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function add_humcommerce_script_to_wp_head() {
		$site_id = get_option( 'humc_site' );

		if ( ! $site_id ) {
			return;
		}

		$host_url = rtrim( HUMCOMMERCE_HOST, '/' );

		?>
			<!-- HumCommerce Tracking code -->
			<script type="text/javascript">
				var _ha = _ha || [];

				_ha.push(["trackPageView"]);
				_ha.push(["enableLinkTracking"]);
				(function () {
					var u = '<?php echo esc_url( $host_url ); ?>';
					_ha.push(['setTrackerUrl', u + '/humdash.php']);
					_ha.push(['setSiteId', '<?php echo esc_html( $site_id ); ?>']);
					var d = document, g = d.createElement("script"), s = d.getElementsByTagName("script")[0];
					g.type = "text/javascript";
					g.async = true;
					g.defer = true;
					g.src = "<?php echo esc_url( $host_url ); ?>/sites/h-<?php echo esc_html( $site_id ); ?>.js";
					s.parentNode.insertBefore(g, s);
				})();
			</script>
			<!-- End of HumCommerce Code -->
			<?php

	}


}
