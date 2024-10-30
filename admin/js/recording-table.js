/**
 * Recording Table JavaScript.
 *
 * @package    humcommerce
 * @subpackage humcommerce/admin
 */

jQuery( document ).ready(
	function() {
		jQuery( '[data-toggle="popover"]' ).popover();

		jQuery( '[data-toggle="tooltip"]' ).tooltip();
		jQuery( 'body' ).on(
			'click',
			function (e) {
				if (jQuery( e.target ).data( 'toggle' ) !== 'popover' && jQuery( e.target ).parents( '[data-toggle="popover"]' ).length === 0
				&& jQuery( e.target ).parents( '.popover.in' ).length === 0) {
					((jQuery( '[data-toggle="popover"]' ).popover( 'hide' ).data( 'bs.popover' ) || {}).inState || {}).click = false;
				}
			}
		);
	}
);
