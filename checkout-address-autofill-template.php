<?php
/* *
* Author: zetamatic
* @package https://zetamatic.com/
*/

class WC_CheckoutAddressAutocomplete{

  public function __construct(){

    //adding filters
    add_filter( "plugin_action_links_".WCGAAW_BASE, array( $this, 'wcaf_settings_link' ));
    add_filter( 'woocommerce_checkout_get_value',array($this, 'clear_checkout_fields'));

    //adding actions
    add_action('admin_menu', array($this, 'wc_af_admin_menu'));
    add_action('admin_init', array($this, 'wc_af_plugin_settings'));
    add_action('wp_enqueue_scripts', array($this, 'wc_af_enqueue_script'));
    add_action('admin_enqueue_scripts', array($this, 'wc_af_admin_script'));
    add_action('woocommerce_before_checkout_billing_form', array($this, 'wcaf_custom_checkout_field'));
    add_action( 'update_option_wc_af_enable_use_location', array($this, 'wcaf_save_image'));
  }

  /**
  * Setting link on plugin page
  *
  * @param array
  * @return array
  *
  */
  public function wcaf_settings_link($links) {
    $settings_link = '<a href="'.admin_url('options-general.php?page=wc-af-options').'">Settings</a>';
    array_unshift( $links, $settings_link );
    return $links;
  }

  /**
  * Creating custom field and icon for autocomplete
  *
  * @param mixed
  * @return
  *
  */
  public function wcaf_custom_checkout_field( $checkout ) {
    if(get_option('wc_af_enable_key') == '1') {  
    //checking for autocomplete option is enable or not
      echo '<div id="wcaf_custom_checkout_field">';
        $height = get_option('wc_af_image_height');
        $width = get_option('wc_af_image_width');

        $height = !empty($height) ? $height : 50;
        $width  = !empty($width) ? $width : 50;
        
        if(get_option('wc_af_enable_use_location') == '1') { 
          //checking for current location option is enable or not
          echo '<img class="locimg" src="'.get_option('wc_af_location_image').'" onClick="geolocate()" style="width:'.$width.'px;height:'.$height.'px;">';//creating icon for using current location
        }

        woocommerce_form_field( 'my_field_name', array( //creatimg field for autocomplete
          'type'          => 'text',
          'class'         => array('my-field-class form-row-wide'),
          'label'         => __('Enter your address','checkout_address_autofill_for_woocommerce'),
          'placeholder'   => __('Search to Autocomplete','checkout_address_autofill_for_woocommerce')
        ), $checkout->get_value( 'my_field_name' ));
      echo '</div>';
    }
  }

  /**
  * Adding admin script
  *
  * @param
  * @return
  *
  */
  public function wc_af_admin_script(){
    $url = plugin_dir_url( __FILE__ ).'/assets/js/autofill.js';
    wp_register_script( 'wc-af-main', $url, array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'wc-af-main' );
    wp_enqueue_media();
  }

  /**
  * Funtion for including scripts and style
  *
  * @param
  * @return
  *
  */
  public function wc_af_enqueue_script(){
    $url = plugin_dir_url( __FILE__ ).'/assets/js/autofill.js';

    // getting api key from database
    $key = get_option('wc_af_api_key'); 

    // adding scripts
    wp_register_script( 'wc-af-main', $url, array('jquery'), '1.0.0', true );
    wp_register_script('wc-af-api', "https://maps.googleapis.com/maps/api/js?key=$key&libraries=places&callback=initAutocomplete", array(), '', true);
    wp_enqueue_script( 'wc-af-main' );
    wp_enqueue_script( 'wc-af-api' );
    
    //adding style
    if(get_option('wc_af_enable_hover')) {
      wp_enqueue_style( 'auto-fill-css',  plugin_dir_url( __FILE__ ) . 'assets/css/autofill.css', '1.0.0' );
    }
  }

  /**
  * function for clearing all the values of checkout fields
  *
  * @param string
  * @return
  *
  */
  public function clear_checkout_fields($input){
    return ''; // return blank field
  }

  /**
  * Function  for creating setting page in admin
  *
  * @param
  * @return
  *
  */
  public function wc_af_admin_menu() {
    add_options_page(__('Google Address Autocomplete for Woocommerce','gaafw'),__('Google Autocomplete','gaafw'), 'manage_options', 'wc-af-options',array($this, 'wc_af_admin_options') );
    do_action('admin_init');
  }

  /**
  * register admin settings
  *
  * @param
  * @return
  *
  */
  public function wc_af_plugin_settings() {
    register_setting( 'wc-af-settings-group', 'wc_af_api_key' );
    register_setting( 'wc-af-settings-group', 'wc_af_enable_key' );
    register_setting( 'wc-af-settings-group', 'wc_af_enable_use_location');
    register_setting( 'wc-af-settings-group', 'wc_af_location_image');
    register_setting( 'wc-af-settings-group', 'wc_af_image_height');
    register_setting( 'wc-af-settings-group', 'wc_af_image_width');
    register_setting( 'wc-af-settings-group', 'wc_af_enable_hover');
  }

  /**
  * Admin option page form
  *
  * @param
  * @return
  *
  */
  public function wc_af_admin_options() {
    if ( !current_user_can( 'manage_options' ) )  { // Checking user can manage or not
      wp_die( __( 'You do not have sufficient permissions to access this page.','gaafw' ) );
    }
  ?>

    <!-- Creating option page options -->
    <div class="wrap">
      <h1><?php echo __('Google Address Autocomplete for Woocommerce','checkout_address_autofill_for_woocommerce'); ?></h1>
    	<form method="post" action="options.php">
        <?php settings_fields( 'wc-af-settings-group' ); ?>
    		<?php do_settings_sections( 'wc-af-settings-group' ); ?>

        <table class="form-table">
          <!-- Enable Auto Fill -->
          <tr valign="top">
            <th scope="row"><?php echo __('Enable Checkout Address Autocomplete','checkout_address_autofill_for_woocommerce'); ?></th>
              <td>
                <input type="checkbox" name="wc_af_enable_key" value="1" <?php checked(1, get_option('wc_af_enable_key'), true); ?>>
              </td>
          </tr>
          
          <!-- Use Current Location -->
          <tr valign="top">
            <th scope="row"><?php echo __('Enable Use Current Location','checkout_address_autofill_for_woocommerce'); ?></th>
            <td>
              <input type="checkbox" name="wc_af_enable_use_location" value="1" <?php checked(1, get_option('wc_af_enable_use_location'), true); ?>>
            </td>
          </tr>
                
          <!-- Google Api key -->
          <tr valign="top">
    			 <th scope="row"><?php echo __('Enter Your Google API Key','checkout_address_autofill_for_woocommerce'); ?></th>
    			 <td>
              <input type="text" name="wc_af_api_key" value="<?php echo (get_option('wc_af_api_key')); ?>">
              <a href="https://cloud.google.com/maps-platform/" style="font-size:12px;"><?php echo __('Get your google api key from here','checkout_address_autofill_for_woocommerce'); ?>
            </td>
    			</tr>
          
          <!-- Upload image for location -->
          <tr valign="top">
            <th scope="row"><?php echo __('Upload Image For Location','checkout_address_autofill_for_woocommerce'); ?></th>
            <td>
              <p>
                <img class="image_logo" src="<?php echo  get_option( 'wc_af_location_image'); ?>" height="<?php echo get_option('wc_af_image_height'); ?>" width="<?php echo get_option('wc_af_image_width'); ?>"/>
                <input class="image_logo_url" type="hidden" name="wc_af_location_image" value="<?php echo  get_option( 'wc_af_location_image'); ?>">
                <input type="button" class="image_logo_upload button" value="Upload">
              </p>
            </td>
          </tr>
          
          <!-- Set height and width of image -->
          <tr valign="top">
            <th scope="row"><?php echo __('Location Image Size In px','checkout_address_autofill_for_woocommerce'); ?></th>
              <td>
                <label><?php echo __('Height', 'checkout_address_autofill_for_woocommerce'); ?></label>
                <input type="number" name="wc_af_image_height" value="<?php echo get_option('wc_af_image_height'); ?>"> <br />
                <label><?php echo __('Width', 'checkout_address_autofill_for_woocommerce'); ?></label>
                <input type="number" name="wc_af_image_width"  value="<?php echo get_option('wc_af_image_width'); ?>" >
              </td>
          </tr>
          
          <!-- On hover properties -->
          <tr valign="top">
            <th scope="row"><?php echo __('Enable Location Image Hover Effect','checkout_address_autofill_for_woocommerce'); ?></th>
            <td>
              <input type="checkbox" name="wc_af_enable_hover" value="1" <?php checked(1, get_option('wc_af_enable_hover'), true); ?>>
            </td>
          </tr>
        </table>
    		<?php submit_button(); ?>
    	</form>
    </div>
  <?php
  }
}
