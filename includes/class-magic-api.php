<?php
/**
 * HumCommerce Magic
 *
 * @since      3.0.0
 *
 * @package    humcommerce
 * @subpackage humcommerce/includes
 */

/**
 * File responsible for all api calls to SASS service
 * Class Magic_Api
 *
 * @package     HumCommerce
 * @subpackage  Magic
 */
class Magic_Api {

	/** Singleton instance
	 *
	 * @var Magic_Api
	 */
	private static $instance = null;

	/**
	 * Magic_Api constructor.
	 */
	private function __construct() {    }

	/** Returns Singleton
	 *
	 * @return Magic_Api
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Magic_Api();
		}
		return self::$instance;
	}

	/** Get recordings api call
	 *
	 * @param int    $site site id.
	 * @param string $token authentication token.
	 * @param string $day date in format Y-m-d.
	 * @return array
	 */
	public function get_recordings( $site, $token, $day ) {

		$query = "&token_auth={$token}&idSite={$site}&day={$day}";
		$url   = HUMCOMMERCE_HOST . '/index.php?module=API&method=Magic.getReport&format=json2' . $query;

		$response = wp_remote_get( $url, array( 'timeout' => 10000 ) );
		try {
			$this->check_if_valid_response( $response );
			return json_decode( wp_remote_retrieve_body( $response ), true );
		} catch ( \Exception $e ) {
			return array(
				'error'   => true,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Response array will always have a error key, it can be true or false
	 *
	 * @param array $params array of details required to create account.
	 * @return array
	 */
	public function create_humcommerce_account( $params ) {

		$url      = HUMCOMMERCE_HOST . '/index.php?module=API&method=Magic.createUserForWp&format=json2';
		$response = wp_remote_post(
			$url,
			array(
				'body'    => $params,
				'timeout' => 10000,
			)
		);

		try {
			$this->check_if_valid_response( $response );
			return json_decode( wp_remote_retrieve_body( $response ), true );
		} catch ( \Exception $e ) {
			return array(
				'error'   => true,
				'message' => $e->getMessage(),
			);
		}
	}

	/**
	 * Handle all errors and return true only if everything worked
	 *
	 * @param array $response response from wp_remote_get.
	 * @throws \Exception Exception.
	 */
	private function check_if_valid_response( $response ) {

		if ( is_wp_error( $response ) ) {

			throw new \Exception( $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( 401 === $code ) {
			throw new \Exception( 'Your request is unauthorized.' );

		}

		if ( 404 === $code ) {
			throw new \Exception( 'That api request should not have gone to 404.' );

		}

		if ( $code >= 500 ) {

			throw new \Exception( 'Oops! Something went wrong on our server.' );

		}

		$json = wp_remote_retrieve_body( $response );
		$data = json_decode( $json, true );

		if ( ! isset( $data['error'] ) ) {
			throw new \Exception( 'We received an unexpected response from api. :' . $json );
		}

		if ( true === $data['error'] ) {
			throw new \Exception( $data['message'] );
		}

	}
}
