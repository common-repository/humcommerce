<?php
/**
 * Error template.
 *
 * @since      3.0.0
 *
 * @package    humcommerce
 * @subpackage humcommerce/admin
 */

?>
<h1>Oops! Something didn't work.</h1>
<p style="font-size:16px">
	We couldn't setup Magic for you because something unexpectedly failed.
	Please try again.
</p>
<p>
	OR
</p>
<div>
	<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'humc_report_error' ); ?>
		<input type="hidden" name="action" value="report_magic_error" />
		<input type="hidden" name="humc_error" value="<?php echo esc_html( $message ); ?>"/>
		<button class="button">Report this error</button>
	</form>
</div>


