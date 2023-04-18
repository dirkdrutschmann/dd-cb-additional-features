<?php

namespace CommonbookingsAdditionalFeatures;

class Statistikpage {
	private $common_bookings_additional_features_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'common_bookings_additional_features_add_statistik_page' ) );
	
	}

	public function common_bookings_additional_features_add_statistik_page() {
		add_options_page(
			'Common Bookings Statistik', // page_title
			'Common Bookings Statistik', // menu_title
			'manage_options', // capability
			'common-bookings-additional-features-statistik', // menu_slug
			array( $this, 'common_bookings_additional_features_create_statistik_admin_page' ) // function
		);
	}

	public function common_bookings_additional_features_create_statistik_admin_page() {
		$this->common_bookings_additional_features_options = get_option( 'common_bookings_additional_features_option_name' ); ?>

		<div class="wrap">
			<h2>Common Bookings Statistik</h2>
	
			 <?php settings_errors(); ?>

			 <form  id="update" method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
			 <input type="hidden" name="action" value="update_statistik">
			 <?php submit_button( 'Statistik updaten' ); ?>
			 </form>
			 </div>
    <?php       
	}
}
if ( is_admin() )
	$common_bookings_additional_features = new StatistikPage();






