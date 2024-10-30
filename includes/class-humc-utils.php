<?php
/**
 * Has utility functions
 *
 * @since      3.0.0
 *
 * @package    humcommerce
 * @subpackage humcommerce/admin
 */

/**
 * The Utils functions of the plugin.
 *
 * Format Price
 *
 * @package    humcommerce
 * @subpackage humcommerce/admin
 */
class Humc_Utils {

	/**
	 * Do price formatting depending on environment.
	 * Check if woocommerce or EDD
	 *
	 * @param float $price Price to format.
	 * @return string
	 */
	public static function format_price( $price ) {
		if ( function_exists( 'wc_price' ) ) {
			$price = wc_price( $price );
		}
		return $price;
	}

}
