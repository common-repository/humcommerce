<?php
/**
 * Email template.
 *
 * @since      3.0.0
 *
 * @package    humcommerce
 * @subpackage humcommerce/admin
 */

?>
<p>Howdy,</p>

<p>HumCommerce Magic is done analyzing the recordings.</p>
<ul>
	<?php foreach ( $summary as $line ) : ?>
		<li><?php echo wp_kses_post( $line ); ?></li>
	<?php endforeach; ?>
</ul>


<p>Click <a href="<?php echo esc_url( admin_url( 'admin.php?page=recommended-recordings' ) ); ?>">here</a> to watch those recordings.</p>

<br>
<p>Regards,</p>
<p>All at <?php echo esc_html( $blog_name ); ?></p>
<p><?php echo esc_url( home_url() ); ?></p>
