<?php
/* *
* Author: zetamatic
* @package https://zetamatic.com/
*/

class WC_CheckoutAddressAutocomplete {

  protected $option_name = 'wcaf_options';

  public function __construct() {
	//adding filters
	add_filter( "plugin_action_links_". WCGAAW_BASE, array( $this, 'wcaf_settings_link' ) );
  if (get_option('wc_af_prohibit_address_clear') != '1') {
    add_filter('woocommerce_checkout_get_value', array( $this, 'clear_checkout_fields' ));
  }

	//adding actions
	add_action( 'admin_menu', array( $this, 'wc_af_admin_menu' ) );
	add_action( 'admin_init', array( $this, 'wc_af_plugin_settings' ) );
	add_action( 'wp_enqueue_scripts', array( $this, 'wc_af_enqueue_script' ) );
	add_action( 'admin_enqueue_scripts', array( $this, 'wc_af_admin_script' ) );

	if( get_option( 'wc_af_show_below_for_bill' ) == '1' ) :
	  add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'wcaf_custom_checkout_field' ) );
	else :
	  add_action( 'woocommerce_before_checkout_billing_form', array( $this, 'wcaf_custom_checkout_field' ) );
	endif;


	if( get_option( 'wc_af_show_below_for_ship' ) == '1' ) :
	  add_action( 'woocommerce_after_checkout_shipping_form', array( $this, 'wcaf_custom_checkout_field_for_shipping_form' ) );
	else :
	  add_action( 'woocommerce_before_checkout_shipping_form', array( $this, 'wcaf_custom_checkout_field_for_shipping_form' ) );
	endif;

	add_action( 'update_option_wc_af_enable_use_location', array( $this, 'wcaf_save_image' ) );

  }


/**
  * Creating custom field and icon for autocomplete
  *
  * @param mixed
  * @return empty
  *
  */
  public function wcaf_custom_checkout_field_for_shipping_form( $checkout ) {

	$label = __( 'Enter your ship address', 'checkout_address_autofill_for_woocommerce' );

	if( ! empty( get_option( 'wc_af_label_for_ship_field' ) ) ) :
	  $label = get_option( 'wc_af_label_for_ship_field' );
	endif;

	$height = ! empty( get_option( 'wc_af_image_height' ) ) ? get_option( 'wc_af_image_height' ).'px' : '50px';
	$width = ! empty( get_option( 'wc_af_image_width' ) ) ? get_option( 'wc_af_image_width' ).'px' : '50px';

	if( get_option( 'wc_af_enable_for_shipping' ) == '1' ) {
	  //checking for autocomplete option is enable or not
	  $html = '<div id="wcaf_custom_checkout_field">';

	  if( get_option( 'wc_af_enable_use_location' ) == '1' ) {

		//checking for current location option is enable or not
		$html .= '<img class="locimg" src="'.get_option( 'wc_af_location_image' ).'" onClick="shipping_geolocate()" style="width:'.$width.';height:'.$height.';">';//creating icon for using current location
	  }

	  $html .= woocommerce_form_field( 'shipping_autofill_checkout_field', array(
		  'type'          => 'text',
		  'class'         => array( 'ship-autofill-field form-row-wide' ),
		  'label'         => $label,
		  'placeholder'   => __( 'Search to Autocomplete', 'checkout_address_autofill_for_woocommerce' )
		), $checkout->get_value( 'shipping_autofill_checkout_field' ) );


	  $html .= '</div>';

	  echo $html;
	}
  }



  /**
  * Setting link on plugin page
  *
  * @param array
  * @return array
  *
  */
  public function wcaf_settings_link( $links ) {
	$settings_link = '<a href="'.admin_url( 'options-general.php?page=wc-af-options' ).'">Settings</a>';
	array_unshift( $links, $settings_link );
	return $links;
  }

  /**
  * Creating custom field and icon for autocomplete
  *
  * @param mixed
  * @return empty
  *
  */
  public function wcaf_custom_checkout_field( $checkout ) {

	$label = __( 'Enter your Billing address', 'checkout_address_autofill_for_woocommerce' );

	if( ! empty( get_option( 'wc_af_label_for_bill_field' ) ) ) :
	  $label = get_option( 'wc_af_label_for_bill_field' );
	endif;

	$height = ! empty( get_option( 'wc_af_image_height' ) ) ? get_option( 'wc_af_image_height' ).'px' : '50px';
	$width = ! empty( get_option( 'wc_af_image_width' ) ) ? get_option( 'wc_af_image_width' ).'px' : '50px';

	if( get_option( 'wc_af_enable_for_billing' ) == '1' ) {
	  //checking for autocomplete option is enable or not
	  $html = '<div id="wcaf_custom_checkout_field">';

	  if( get_option( 'wc_af_enable_use_location' ) == '1' ) {

		//checking for current location option is enable or not
		$html .= '<img class="locimg" src="'.get_option( 'wc_af_location_image' ).'" onClick="billing_geolocate()" style="width:'.$width.';height:'.$height.';">';//creating icon for using current location
	  }

	  $html .= woocommerce_form_field( 'autofill_checkout_field', array(
		  'type'          => 'text',
		  'class'         => array( 'my-field-class form-row-wide' ),
		  'label'         => $label,
		  'placeholder'   => __( 'Search to Autocomplete', 'checkout_address_autofill_for_woocommerce' )
		), $checkout->get_value( 'autofill_checkout_field' ) );


	  $html .= '</div>';

	  echo $html;
	}



  }

  /**
  * Adding admin script
  *
  * @param empty
  * @return mixed
  *
  */
  public function wc_af_admin_script() {
	if( isset( $_GET['page'] ) && $_GET['page'] == 'wc-af-options' ) {
	  //Add style for settings
	  wp_enqueue_style( 'caa-stylesheet', plugins_url( 'assets/css/autofill-address-settings.css', __FILE__ ) );

	//Add select2 for country selection
	  wp_enqueue_style( 'select2-style', plugins_url( 'assets/css/select2.css', __FILE__ ) );
	  wp_enqueue_script( 'wcgaa-select2', plugins_url( 'assets/js/select2.min.js', __FILE__ ) , array( 'jquery' ), '1.0.0', true );

	  $url = plugin_dir_url( __FILE__ ).'/assets/js/autofill.js';
	  wp_register_script( 'wc-af-main', $url, array( 'jquery', 'wcgaa-select2' ), WCGAAW_PLUGIN_VERSION, true );
	  wp_enqueue_script( 'wc-af-main' );
	  wp_enqueue_media();
	}
  }

  /**
  * Function for including scripts and style
  *
  * @param empty
  * @return mixed
  *
  */
  public function wc_af_enqueue_script() {
	$url = plugin_dir_url( __FILE__ ).'/assets/js/autofill.js';

	// getting api key from database
	$key = get_option( 'wc_af_api_key' );

	// adding scripts
	wp_register_script( 'wc-af-main', $url, array( 'jquery' ), WCGAAW_PLUGIN_VERSION, true );
	wp_register_script( 'wc-af-api', "https://maps.googleapis.com/maps/api/js?key=$key&libraries=places&callback=initAutocomplete", array(), '', true );

	wp_enqueue_script( 'wc-af-main' );
	wp_enqueue_script( 'wc-af-api' );


	wp_localize_script( 'wc-af-main', 'wcaf',
	  array(
		'autofill_for_shipping'         => get_option( 'wc_af_enable_for_shipping' ),
		'selectedCountry'               => get_option( 'wc_af_country' ),
		'enable_billing_company_name'   => get_option( 'wc_af_enable_company_name_for_bill' ),
		'enable_shipping_company_name'  => get_option( 'wc_af_enable_company_name_for_ship' ),
		'enable_billing_phone'          => get_option( 'wc_af_enable_phone_number_for_bill' ),
		'locationImage'                 => 'Location Image',
		'uploadImage'                   => 'Upload Image',
	  )
	);

	//adding style
	if( get_option( 'wc_af_enable_hover' ) ) {
	  wp_enqueue_style( 'auto-fill-css',  plugin_dir_url( __FILE__ ) . 'assets/css/autofill.css', WCGAAW_PLUGIN_VERSION );
	}

  }

  /**
  * function for clearing all the values of checkout fields
  *
  * @param string
  * @return string
  *
  */
  public function clear_checkout_fields( $input ) {
	return ''; // return blank field
  }

  /**
  * Function  for creating setting page in admin
  *
  * @param empty
  * @return empty
  *
  */
  public function wc_af_admin_menu() {
	add_options_page( __( 'Google Address Autocomplete for Woocommerce', 'checkout_address_autofill_for_woocommerce' ), __( 'Google Autocomplete', 'checkout_address_autofill_for_woocommerce' ), 'manage_options', 'wc-af-options', array( $this, 'wc_af_admin_options' ) );
  }

  /**
  * register admin settings
  *
  * @param empty
  * @return empty
  *
  */
  public function wc_af_plugin_settings() {
	register_setting( 'wc-af-settings-group', 'wc_af_api_key' );
	register_setting( 'wc-af-settings-group', 'wc_af_country' );
	register_setting( 'wc-af-settings-group', 'wc_af_enable_for_billing' );
	register_setting( 'wc-af-settings-group', 'wc_af_enable_for_shipping' );
	register_setting( 'wc-af-settings-group', 'wc_af_show_below_for_bill' );
	register_setting( 'wc-af-settings-group', 'wc_af_show_below_for_ship' );
	register_setting( 'wc-af-settings-group', 'wc_af_enable_use_location' );
	register_setting( 'wc-af-settings-group', 'wc_af_label_for_ship_field' );
	register_setting( 'wc-af-settings-group', 'wc_af_label_for_bill_field' );
	register_setting( 'wc-af-settings-group', 'wc_af_location_image' );
	register_setting( 'wc-af-settings-group', 'wc_af_image_height' );
	register_setting( 'wc-af-settings-group', 'wc_af_image_width' );
	register_setting( 'wc-af-settings-group', 'wc_af_enable_hover' );
	register_setting( 'wc-af-settings-group', 'wc_af_enable_company_name_for_bill' );
	register_setting( 'wc-af-settings-group', 'wc_af_enable_company_name_for_ship' );
  register_setting( 'wc-af-settings-group', 'wc_af_enable_phone_number_for_bill' );
	register_setting( 'wc-af-settings-group', 'wc_af_prohibit_address_clear' );
	register_setting( 'wc-af-settings-group', $this->option_name, array( $this, 'validate' ) );
  }

  public function validate( $input ) {
	$valid        = array();
	$output_array = array();

	if( isset( $_POST['wc_af_country'] ) ) {
	  foreach( $_POST['wc_af_country'] as $key => $post_arr ) {
		array_push( $output_array, sanitize_text_field( $post_arr ) );
	  }
	}

	$valid['wc_af_country'] = $output_array;
	return $valid;
  }

  /**
  * Admin option page form
  *
  * @param empty
  * @return empty
  *
  */
  public function wc_af_admin_options() {
	if ( ! current_user_can( 'manage_options' ) )  { // Checking user can manage or not
	  wp_die( __( 'You do not have sufficient permissions to access this page.', 'gaafw' ) );
	}
  ?>

	<!-- Creating option page options -->
	<div class="wrap">
	  <h1><?php echo __( 'Google Address Autocomplete for Woocommerce', 'checkout_address_autofill_for_woocommerce' ); ?></h1>
		<form method="post" action="options.php" id="checkout-address-autocomplete-form">
		<?php settings_fields( 'wc-af-settings-group' ); ?>
			<?php do_settings_sections( 'wc-af-settings-group' ); ?>


		<table class="form-table">

		  <!-- Google Api key -->
		  <tr valign="top">
		   <th scope="row"><?php echo __( 'Enter Your Google API Key', 'checkout_address_autofill_for_woocommerce' ); ?></th>
		   <td>
			  <input type="text" name="wc_af_api_key" value="<?php echo ( get_option( 'wc_af_api_key' ) ); ?>">
			  <a href="https://cloud.google.com/maps-platform/" style="font-size:12px;" target="_blank"><?php echo __( 'Get your google api key from here', 'checkout_address_autofill_for_woocommerce' ); ?></a>
			</td>
		  </tr>


		  <tr valign="top">
			<th colspan="2" scope="row"><h2 style="margin-bottom: 0;"><?php echo __( 'Billing Autocomplete Fields', 'checkout_address_autofill_for_woocommerce' ); ?></h2>
			  <hr>
			</th>
		  </tr>


		  <tr valign="top">
			<th scope="row">
			  <?php echo __( 'Enable for Billing', 'checkout_address_autofill_for_woocommerce' ); ?>
			</th>
			<td>
			  <input type="checkbox" name="wc_af_enable_for_billing" value="1" <?php checked( 1, get_option( 'wc_af_enable_for_billing' ), true ); ?>>
			  <p class="description" id="label_for_field_description">
				<?php echo __( 'Enable autocomplete.', 'checkout_address_autofill_for_woocommerce' ); ?>
			  </p>
			</td>
		  </tr>

		  <tr valign="top">
			<th scope="row">
			  <?php echo __( 'Auto Complete Field Label', 'checkout_address_autofill_for_woocommerce' ); ?>
			</th>
			<td>
			  <input type="text" name="wc_af_label_for_bill_field" value="<?php echo ( get_option( ' wc_af_label_for_bill_field' ) ); ?>">
			  <p class="description" id="label_for_field_description">
				<?php echo __( 'Enter the label of autocomplete field you want to show', 'checkout_address_autofill_for_woocommerce' ); ?>
			  </p>
			</td>
		  </tr>

		  <tr valign="top">
			<th scope="row">
			  <?php echo __( 'Show Autofill Below Address', 'checkout_address_autofill_for_woocommerce' ); ?>
			</th>
			<td>
			  <input type="checkbox" name="wc_af_show_below_for_bill" value="1" <?php checked( 1, get_option( 'wc_af_show_below_for_bill' ), true ); ?>>
			  <p class="description"><?php echo __( 'Check to show field below address. By default it is above address field', 'checkout_address_autofill_for_woocommerce' ); ?></p>
			</td>
		  </tr>

      <tr valign="top">
			<th scope="row" style="color:gray;">
			  <?php echo __('Enable Location Picker for Billing', 'checkout_address_autofill_for_woocommerce'); ?>
			</th>
			<td>
			  <input type="checkbox" value="1" disabled>
			  <p class="description">
				<?php echo __('Enable Location Picker.', 'checkout_address_autofill_for_woocommerce'); ?>
        To enable this download <a href="https://zetamatic.com/downloads/checkout-address-autofill-for-woocommerce-pro/" target="_blank">pro</a>.
			  </p>
			</td>
		  </tr>

		  <tr valign="top">
			<th scope="row">
			  <?php echo __( 'Allow Phone Number', 'checkout_address_autofill_for_woocommerce' ); ?>
			</th>
			<td>
			  <input type="checkbox" name="wc_af_enable_phone_number_for_bill" value="1" <?php checked( 1, get_option( 'wc_af_enable_phone_number_for_bill' ), true ); ?>>
			  <p class="description"><?php echo __( 'Check to autofill the Phone number field.', 'checkout_address_autofill_for_woocommerce' ); ?></p>
			</td>
		  </tr>

		  <tr valign="top">
			<th scope="row"><?php echo __( 'Allow Compay Name', 'checkout_address_autofill_for_woocommerce' ); ?></th>
			<td>
			  <input type="checkbox" name="wc_af_enable_company_name_for_bill" value="1" <?php checked( 1, get_option( 'wc_af_enable_company_name_for_bill' ), true ); ?>>
			  <p class="description"><?php echo __( 'Check to autofill the Company Name field.', 'checkout_address_autofill_for_woocommerce' ); ?></p>
			</td>
		  </tr>


		  <!-- Shipping Fields -->
		  <tr valign="top">
			<th colspan="2" scope="row"><h2 style="margin-bottom: 0;"><?php echo __( 'Shipping Autocomplete Fields', 'checkout_address_autofill_for_woocommerce' ); ?></h2>
			  <hr>
			</th>
		  </tr>
		  <tr valign="top">
			<th scope="row">
			  <?php echo __( 'Enable for Shipping', 'checkout_address_autofill_for_woocommerce' ); ?>
			</th>
			<td>
			  <input type="checkbox" name="wc_af_enable_for_shipping" value="1" <?php checked( 1, get_option( 'wc_af_enable_for_shipping' ), true ); ?>>
			  <p class="description" id="label_for_field_description">
				<?php echo __( 'Enable autocomplete.', 'checkout_address_autofill_for_woocommerce' ); ?>
			  </p>
			</td>
		  </tr>
		  <tr valign="top">
			<th scope="row">
			  <?php echo __( 'Auto Complete Field Label', 'checkout_address_autofill_for_woocommerce' ); ?>
			</th>
			<td>
			  <input type="text" name="wc_af_label_for_ship_field" value="<?php echo ( get_option( 'wc_af_label_for_ship_field' ) ); ?>">
			  <p class="description" id="label_for_field_description">
				<?php echo __( 'Enter the label of autocomplete field you want to show', 'checkout_address_autofill_for_woocommerce' ); ?>
			  </p>
			</td>
		  </tr>

		  <tr valign="top">
			<th scope="row">
			  <?php echo __( 'Show Autofill Below Address', 'checkout_address_autofill_for_woocommerce' ); ?>
			</th>
			<td>
			  <input type="checkbox" name="wc_af_show_below_for_ship" value="1" <?php checked( 1, get_option( 'wc_af_show_below_for_ship' ), true); ?>>
			  <p class="description"><?php echo __( 'Check to show field below address. By default it is above address field', 'checkout_address_autofill_for_woocommerce' ); ?></p>
			</td>
		  </tr>

      <tr valign="top">
      <th scope="row"  style="color:gray;">
        <?php echo __('Enable Location Picker for Shipping', 'checkout_address_autofill_for_woocommerce'); ?>
      </th>
      <td>
        <input type="checkbox" value="1" disabled>
        <p class="description">
        <?php echo __('Enable Location Picker.', 'checkout_address_autofill_for_woocommerce'); ?>
        To enable this download <a href="https://zetamatic.com/downloads/checkout-address-autofill-for-woocommerce-pro/" target="_blank">pro</a>.
        </p>
      </td>
      </tr>


		  <tr valign="top">
			<th scope="row"><?php echo __( 'Enable Company Name Autofill', 'checkout_address_autofill_for_woocommerce' ); ?></th>
			<td>
			  <input type="checkbox" name="wc_af_enable_company_name_for_ship" value="1" <?php checked( 1, get_option( 'wc_af_enable_company_name_for_ship' ), true ); ?>>
			  <p class="description"><?php echo __( 'Check to autofill the Compay name field.', 'checkout_address_autofill_for_woocommerce' ); ?></p>
			</td>
		  </tr>
		  <!-- Shipping Fields END -->


		  <tr valign="top">
			<th colspan="2" scope="row"><h2 style="margin-bottom: 0;"><?php echo __( 'Common fields for both Billing and Shipping Address', 'checkout_address_autofill_for_woocommerce' ); ?></h2>
			  <hr>
			</th>
		  </tr>

		  <!-- Use Current Location -->
		  <tr valign="top">
			<th scope="row"><?php echo __( 'Enable Use Current Location', 'checkout_address_autofill_for_woocommerce' ); ?></th>
			<td>
			  <input type="checkbox" name="wc_af_enable_use_location" value="1" <?php checked( 1, get_option( 'wc_af_enable_use_location' ), true); ?>>
			  <p class="description" id="wc_af_enable_use_location"><?php echo __( 'This option simply shows an option where user can use their current location easily', 'checkout_address_autofill_for_woocommerce' ); ?></p>
			</td>
		  </tr>

		  <tr valign="top">
			<th scope="row">
			  <?php echo __( 'Show Results From Country', 'checkout_address_autofill_for_woocommerce' ); ?>
			</th>
			<td>
			  <select class="wc_gaa_countries" name="wc_af_country[]" multiple="multiple">
			  <?php
				global $woocommerce;
				$countries_obj      = new WC_Countries();
				$countries          = $countries_obj->__get( 'countries' );
				$saved_country_list = get_option( 'wc_af_country' );

				if( is_array( $countries ) && ! empty( $countries ) ) {
				  foreach( $countries as $key => $country ) {
					if( is_array( $saved_country_list )
					  && ! empty( $saved_country_list ) ) {
					  if( in_array( $key, $saved_country_list ) ) {  ?>
						<option selected value="<?php echo $key; ?>"><?php echo $country; ?></option>
						<?php
					  } else { ?>
						<option value="<?php echo $key; ?>"><?php echo $country; ?></option>
						<?php
					  }
					} else { ?>
					<option value="<?php echo $key; ?>"><?php echo $country; ?></option>
					<?php
					}
				  }
				}
			  ?>
			  </select>
			</td>
		  </tr>

		  <!-- Upload image for location -->
		  <tr valign="top">
			<th scope="row"><?php echo __( 'Upload Image For Location', 'checkout_address_autofill_for_woocommerce' ); ?></th>
			<td>
			  <p>
				<img class="image_logo" src="<?php echo get_option( 'wc_af_location_image' ); ?>" height="<?php echo get_option( 'wc_af_image_height' ); ?>" width="<?php echo get_option( 'wc_af_image_width' ); ?>"/>
				<input class="image_logo_url" type="hidden" name="wc_af_location_image" value="<?php echo get_option( 'wc_af_location_image' ); ?>">
				<input type="button" class="image_logo_upload button" value="Upload">
			  </p>
			</td>
		  </tr>

		  <!-- Set height and width of image -->
		  <tr valign="top">
			<th scope="row"><?php echo __( 'Location Image Size In px', 'checkout_address_autofill_for_woocommerce' ); ?></th>
			  <td>
				<div class="image-dimension-wrapper">
				  <label><?php echo __( 'Height', 'checkout_address_autofill_for_woocommerce' ); ?> : </label>
				  <input type="number" class="image-dimension" name="wc_af_image_height" value="<?php echo get_option( 'wc_af_image_height' ); ?>">
				</div>

				<div class="image-dimension-wrapper">
				  <label><?php echo __( 'Width', 'checkout_address_autofill_for_woocommerce' ); ?> : </label>
				  <input type="number" name="wc_af_image_width"  value="<?php echo get_option( 'wc_af_image_width' ); ?>" >
				</div>
			  </td>
		  </tr>

		  <!-- On hover properties -->
		  <tr valign="top">
			<th scope="row"><?php echo __( 'Enable Location Image Hover Effect', 'checkout_address_autofill_for_woocommerce' ); ?></th>
			<td>
			  <input type="checkbox" name="wc_af_enable_hover" value="1" <?php checked( 1, get_option( 'wc_af_enable_hover' ), true); ?>>
			</td>
		  </tr>

      <!-- Disable auto clearing default address values-->
      <tr valign="top">
  			<th scope="row">
  			  <?php echo __('Disable auto clearing default address values', 'checkout_address_autofill_for_woocommerce'); ?>
  			</th>
  			<td>
  			  <input type="checkbox" name="wc_af_prohibit_address_clear" value="1" <?php checked(1, get_option('wc_af_prohibit_address_clear'), true); ?>>
  			  <p class="description" style="display: inline;"><?php echo __('This plugin overwrites Woocommerce feature that keeps filled in address values on page refresh to blank them. If you want to disable this feature and keep Woocommerce default behavior please check this.', 'checkout_address_autofill_for_woocommerce'); ?></p>
  			</td>
		  </tr>
		</table>
			<?php submit_button(); ?>
		</form>
	</div>
  <?php
  }
}
