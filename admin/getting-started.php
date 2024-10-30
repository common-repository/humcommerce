<?php
/**
 * Template that renders first page user sees after installing plugin
 *
 * @since 3.0.0
 * @package    HumCommerce
 * @subpackage admin
 */

?>
<script>
	jQuery("head").append("<style> html.wp-toolbar{ padding-top:0; } #wpadminbar,#adminmenumain,#wpfooter,.notice-error,.notice-warning,.notice-success,.notice-info,.update-nag, .updated, .error, .is-dismissible,.notice,.ask-for-usage-notice {display: none !important;} #wpcontent {margin-left: 0;} </style>");
</script>
<div class="humc-start-page-container">
	<div id="intro-section">
		<div id="magic-logo">
			<img src="<?php echo esc_url( plugins_url( '/images/humcmagic.png', __FILE__ ) ); ?>" alt="humcommerce logo"/>
		</div>
		<div id="branding-hero">
			<h1>WELCOME TO HUMCOMMERCE MAGIC</h1>
			<p class="humc-text">Session Recordings plugin by HumCommerce</p>
			<p class="humc-text">Thank you for choosing WordPress Session recordings plugin - The most powerful session
				recording plugin </p>
		</div>
	</div>
	<div id="section-video">
		<div class="column">
			<a target="_blank" href="https://www.humcommerce.com/humcommerce-magic-recordings">
				<img src="<?php echo esc_url( plugins_url( '/images/getting-started-video.jpg', __FILE__ ) ); ?>" alt="humcommerce logo"/>
			</a>
		</div>
	</div>
	<div id="setup-section">
		<p class="humc-text">
            Magic insights can help you improve your e-commerce store conversion rates by analyzing the customer behavior on your store and telling you exactly what is broken.
		</p>
		<br>
		<div id="setup-form">

			<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="humc_create_magic_account"/>
				<div class="form-group">
					<input type="checkbox" name="terms-conditions" required/>
					<label class="humc-text">
						I have read and agree to the  <a target="_blank" href="https://www.humcommerce.com/terms-of-use/">Terms of Use</a> and <a target="_blank" href="https://www.humcommerce.com/privacy-policy/">Privacy Policy</a>
					</label>

				</div>
				<br>
				<input type="hidden" name="_wp_nonce" value="<?php echo esc_attr( wp_create_nonce( 'humc_account_create' ) ); ?>"/>
				<button id="setup-button">SETUP WITH ONE CLICK</button>
			</form>

		</div>
	</div>
	<div id="features-section">
		<div class="features-head">
			<h1>Humcommerce Features &amp; Addons</h1>
			<p class="features-subtitle">Get Started with HumCommerce Magic and get these daily reports right here in your dashboard.</p>
		</div>
		<div class="features-seaction">
			<div class="features-content">
				<div class="features-icon">
					<img src="<?php echo esc_url( plugins_url( '/images/icons/automated-analysis.png', __FILE__ ) ); ?>" alt="Automated Analysis">
				</div>
				<div class="features-text">
					<h3 class="features-text-heading">Automated Anomaly Analysis</h3>
					<p>Get a list of real bottlenecks that are preventing your visitors from buying on your website.</p>
				</div>
			</div>
			<div class="features-content">
				<div class="features-icon">
					<img src="<?php echo esc_url( plugins_url( '/images/icons/automated-sign-in.png', __FILE__ ) ); ?>" alt="Automated sign up">
				</div>
				<div class="features-text">
					<h3 class="features-text-heading">Automated sign up</h3>
					<p>Get started without leaving your website. We will create an account for you automatically.</p>
				</div>
			</div>

		</div>
		<div class="features-seaction">

			<div class="features-content">
				<div class="features-icon">
					<img src="<?php echo esc_url( plugins_url( '/images/icons/false-click.png', __FILE__ ) ); ?>" alt="False Clicks">
				</div>
				<div class="features-text">
					<h3 class="features-text-heading">False Clicks</h3>
					<p>Go through the list of false clicks and get rid of misleading UI elements that look click-able but lead to nowhere and frustrate your users.</p>
				</div>
			</div>
			<div class="features-content">
				<div class="features-icon">
					<img src="<?php echo esc_url( plugins_url( '/images/icons/error-click.png', __FILE__ ) ); ?>" alt="False Clicks">
				</div>
				<div class="features-text">
					<h3 class="features-text-heading">Error Clicks</h3>
					<p>Eliminate the clicks that caused errors to appear on your website, creating a poor user experience for your visitors.</p>
				</div>
			</div>
		</div>

		<div class="features-seaction">
			<div class="features-content">
				<div class="features-icon">
					<img src="<?php echo esc_url( plugins_url( '/images/icons/abandon-cart.png', __FILE__ ) ); ?>" alt="Abandon Cart">
				</div>
				<div class="features-text">
					<h3 class="features-text-heading">Cart Abandonment</h3>
					<p>Dive straight into the recordings for visitors who added items to their cart but didn't buy. Look at other relevant data like region, cart value and errors that the visitor may have seen, to get the full picture.</p>
				</div>
			</div>
			<div class="features-content">
				<div class="features-icon">
					<img src="<?php echo esc_url( plugins_url( '/images/icons/session-recording.png', __FILE__ ) ); ?>" alt="Session Recordings">
				</div>
				<div class="features-text">
					<h3 class="features-text-heading">Visitor Session Recordings</h3>
					<p>Watch the important visitor recordings listed in your dashboard, that show you what needs to be fixed. Skip pauses, replay, pause, watch as many times as you like.</p>
				</div>
			</div>

		</div>


		<div class="features-seaction">
			<div class="features-content">
				<div class="features-icon">
					<img src="<?php echo esc_url( plugins_url( '/images/icons/dashboard.png', __FILE__ ) ); ?>" alt="WordPress Dashboard">
				</div>
				<div class="features-text">
					<h3 class="features-text-heading">WordPress Dashboard</h3>
					<p>You will be shown a list of 20 recordings for abandoned carts and  frustrated visitors. Data for up to 7 days is stored and can be viewed by you at any time.</p>
				</div>
			</div>

			<div class="features-content">
				<div class="features-icon">
					<img src="<?php echo esc_url( plugins_url( '/images/icons/updates.png', __FILE__ ) ); ?>" alt="Daily Update">
				</div>
				<div class="features-text">
					<h3 class="features-text-heading">Daily Update</h3>
					<p>View the list of problems you need to fix, right inside your WordPress dashboard. Your data is analyzed and updated once every 24 hours.</p>
				</div>
			</div>

		</div>

		<div class="features-seaction"  >

			<div class="features-content">
				<div class="features-icon">
					<img src="<?php echo esc_url( plugins_url( '/images/icons/email-summary.png', __FILE__ ) ); ?>" alt="Email Summary">
				</div>
				<div class="features-text">
					<h3 class="features-text-heading">Email Summary</h3>
					<p>Receive an email every day with a summary of the previous day's findings. Visit your WordPress Dashboard for details.</p>
				</div>
			</div>
			<div class="features-content">
				<div class="features-icon">
					<img src="<?php echo esc_url( plugins_url( '/images/icons/launch.png', __FILE__ ) ); ?>" alt="Single-click launch">
				</div>
				<div class="features-text">
					<h3 class="features-text-heading">Single-click launch</h3>
					<p>Click on the button ‘Setup with One Click’. HumCommerce will start recording sessions for all visitors who come to your website and display data within the next 24-48 hours.</p>
				</div>
			</div>

		</div>
	</div>
</div>
