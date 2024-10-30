<?php
/**
 * No Recordings Email template.
 *
 * @since      3.0.6
 *
 * @package    humcommerce
 * @subpackage humcommerce/admin
 */

?>
<p>Howdy,</p>

<p>HumCommerce Magic is done analyzing the recordings.</p>
<p>
	<?php echo esc_html( $summary['message'] ); ?>
</p>

<br>
<p>Regards,</p>
<p>All at <?php echo esc_html( $blog_name ); ?></p>
<p><?php echo esc_url( home_url() ); ?></p>
