<?php
/*
Plugin Name: Checkout Address AutoFill For WooCommerce
Description: This plugin allows you to fill the checkout form automatically by using google address autocomplete api.
Version: 0.1
Author: zetamatic
Author URI: https://zetamatic.com/
Text Domain: checkout_address_autofill_for_woocommerce
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit;
// defining basename
define('WCGAAW_BASE', plugin_basename(__FILE__));

if ( ! class_exists( 'WC_GAAInstallCheck' ) ) { 
// Restrict installation without woocommerce
	class WC_GAAInstallCheck {
		static function install() {
		
		/**
		* Check if WooCommerce  are active
		**/
		if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )) {

				// Deactivate the plugin
				deactivate_plugins(__FILE__);

				// Throw an error in the wordpress admin console
				$error_message = __('This plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>  plugins to be active!', 'woocommerce');
				die($error_message);
			}
			else {
				$url = plugin_dir_url( __FILE__ ).'/assets/img/location.png';
				update_option('wc_af_location_image', $url);
				update_option('wc_af_image_height', 50);
				update_option('wc_af_image_width', 50);
			}
		}
	}
}
register_activation_hook( __FILE__, array('WC_GAAInstallCheck', 'install') );

require_once dirname( __FILE__ ) . '/checkout-address-autofill-template.php';

new WC_CheckoutAddressAutocomplete();
