<?php
/*
Plugin Name: Checkout Address AutoFill For WooCommerce
Plugin URI: https://zetamatic.com/?utm_src=checkout-address-autofill-for-woocommerce
Description: This plugin allows you to fill the checkout form automatically by using google's address autocomplete API.
Version: 1.0.3
Author: zetamatic
Author URI: https://zetamatic.com/?utm_src=checkout-address-autofill-for-woocommerce
Text Domain: checkout_address_autofill_for_woocommerce
Tested up to: 5.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit;
// defining basename
define( 'WCGAAW_BASE', plugin_basename( __FILE__ ) );
define( 'WCGAAW_PLUGIN_PATH', dirname(__FILE__) );
define( 'WCGAAW_PLUGIN_VERSION', '1.0.3' );

if ( ! class_exists( 'WC_GAAInstallCheck' ) ) {
	//Restrict installation without woocommerce
	class WC_GAAInstallCheck {
		static function install() {
		/**
		* Check if WooCommerce  are active
		**/
			if ( ! class_exists( 'WooCommerce' ) ) {
				apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
				// Deactivate the plugin
				deactivate_plugins(__FILE__);

				// Throw an error in the wordpress admin console
				$error_message = __( 'This plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>  plugins to be active!', 'woocommerce' );
				die($error_message);
			}
			else {
				if(class_exists('WC_GAAInstallCheckPro')) {
            require(WCGAAW_PLUGIN_PATH . "/inc/plugin-activation-error.php");
            exit;
        }
				//updating default options
				$url = plugin_dir_url( __FILE__ ).'/assets/images/location.png';
				update_option( 'wc_af_location_image', $url );
				update_option( 'wc_af_image_height', 50 );
				update_option( 'wc_af_image_width', 50 );
			}
		}
	}
}

register_activation_hook( __FILE__, array( 'WC_GAAInstallCheck', 'install' ) );

if(get_option("wcgaaw_disable_pro_notice") != "YES"){
	add_action( 'admin_notices', 'wcgaaw_download_pro_plugin' );
}
add_action( 'wp_ajax_wcgaaw_hide_pro_notice', 'wcgaaw_hide_pro_notice' );

define( 'WCGAAW_PLUGIN_NAME', 'Checkout Address AutoFill For WooCommerce' );
function wcgaaw_download_pro_plugin() {
	$class = 'notice notice-warning is-dismissible wcgaaw-notice-buy-pro';
	$plugin_url = 'https://zetamatic.com/downloads/checkout-address-autofill-for-woocommerce-pro/';
	$message = __( 'Glad to know that you are already using our '.WCGAAW_PLUGIN_NAME.'. Do you want to activate the map location picker feature? Then please visit <a href="'.$plugin_url.'?utm_src='.WCGAAW_PLUGIN_NAME.'" target="_blank">here</a> for custom fields.', 'checkout_address_autofill_for_woocommerce' );
	$dont_show = __( "Don't show this message again!", 'checkout_address_autofill_for_woocommerce' );
	printf( '<div class="%1$s"><p>%2$s</p><p><a href="javascript:void(0);" class="wcgaaw-hide-pro-notice">%3$s</a></p></div>
	<script type="text/javascript">
		(function () {
			jQuery(function () {
				jQuery("body").on("click", ".wcgaaw-hide-pro-notice", function () {
					jQuery(".wcgaaw-notice-buy-pro").hide();
					jQuery.ajax({
						"type": "post",
						"dataType": "json",
						"url": ajaxurl,
						"data": {
							"action": "wcgaaw_hide_pro_notice"
						},
						"success": function(response){
						}
					});
				});
			});
		})();
		</script>', esc_attr( $class ), $message, $dont_show );
}
function wcgaaw_hide_pro_notice() {
  update_option("wcgaaw_disable_pro_notice", "YES");
  echo json_encode(["status" => "success"]);
  wp_die();
}

//Get required files
require_once dirname( __FILE__ ) . '/checkout-address-autofill-template.php';

new WC_CheckoutAddressAutocomplete();
