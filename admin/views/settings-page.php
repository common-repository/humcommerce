<?php
/**
 * Settings page.
 *
 * @since      3.0.0
 *
 * @package    humcommerce
 * @subpackage humcommerce/admin
 */

?>
<div id="humcommerce-plugin-container">
	<div class="humcommerce-masthead">
		<div class="humcommerce-masthead__inside-container">
			<div class="humcommerce-masthead__logo-container">
				<img class="humcommerce-masthead__logo" src="<?php echo esc_url( $logo_url ); ?>" alt="humcommerce">
			</div>
		</div>
	</div>
	<div class="humcommerce-lower">
		<div class="humcommerce-boxes">

			<div class="humcommerce-box">
				<div class="icon32" id="icon-options-general"></div>
				<p>*Note:* If you are using HumCommerce plugin to integrate HumCommerce, do not insert the tracking code manually.</p>
				<form action="options.php" method="post">
					<?php
					settings_fields( 'humc_magic' );
					?>
					<table class="form-table" role="presentation">
						<tr class="form-group">
							<th><label>Site ID</label></th>
							<td>
								<input required  name='humc_site' size='40' type='number' value='<?php echo esc_attr( $site ); ?>' />
								(<a href="https://www.humcommerce.com/docs/find-site-id-humcommmerce-tool/#get_site_id" target="_blank">What is site ID?</a>)
							</td>
						</tr>
						<tr class="form-group">
							<th><label>Token</label></th>
							<td><input type="text" name="humc_token" value="<?php echo esc_attr( $token ); ?>"/></td>
						</tr>
					</table>

					<div class="form-group">
						<button name="Submit" type="submit" class="button button-primary" ><?php esc_html_e( 'Save Changes', 'humcommerce' ); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
