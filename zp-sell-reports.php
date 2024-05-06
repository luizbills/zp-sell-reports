<?php
/**
 * Plugin Name: ZodiacPress Sell Reports
 * Plugin URI: https://cosmicplugins.com/downloads/zodiacpress-woocommerce/
 * Description: Sell astrology birth reports with WooCommerce.
 * Version: 1.2.2.1
 * Author: Isabel Castillo
 * Author URI: https://isabelcastillo.com/
 * Text Domain: zp-sell-reports
 * Domain Path: /languages
 * WC requires at least: 3.2.4
 * WC tested up to: 3.4.2
 * License: GNU GPL v2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html

Copyright 2017-2018 Isabel Castillo

This file is part of "ZodiacPress Sell Reports with WooCommerce."

"ZodiacPress Sell Reports with WooCommerce" is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

"ZodiacPress Sell Reports with WooCommerce" is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with "ZodiacPress Sell Reports with WooCommerce". If not, see <http://www.gnu.org/licenses/>.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Do nothing if ZP is not activated
if ( ! in_array( 'zodiacpress/zodiacpress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		return;
}

// Do nothing if WC is not activated
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

if ( ! defined( 'ZP_SELL_REPORTS_VERSION' ) ) {
	define( 'ZP_SELL_REPORTS_VERSION', '1.2.2' );// @todo update
}

/**
* Enable license for easy updates
*/
function zpsr_enable_easy_updates() {
	if ( class_exists( 'ZP_License' ) && is_admin() ) {
		$zpsr_license = new ZP_License( __FILE__, 'ZodiacPress Sell Reports with WooCommerce', ZP_SELL_REPORTS_VERSION, 'Isabel Castillo' );
	}
}
add_action( 'plugins_loaded', 'zpsr_enable_easy_updates' );

/**
* Load plugin's textdomain
*/
function zpsr_load_textdomain() {
	load_plugin_textdomain( 'zp-sell-reports', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'zpsr_load_textdomain' );

$path = plugin_dir_path( __FILE__ );

// Register/load our JavaScript and CSS.
include_once $path . 'includes/scripts.php';

// Functions related to the report product.
include_once $path . 'includes/product.php';

// Ajax handler functions
include_once $path . 'includes/ajax-handlers.php';

include_once $path . 'includes/helper-functions.php';

// Handle the free preview of the birth report.
include_once $path . 'includes/class-zpsr-preview-report.php';
include_once $path . 'includes/preview.php';

// Manage the data for report orders while in the cart.
include_once $path . 'includes/wc-cart.php';

// Manage the data for report orders during checkout.
include_once $path . 'includes/wc-checkout.php';

// Manage completed report orders.
include_once $path . 'includes/wc-completed-orders.php';

if ( is_admin() ) {

	// Handles the Erase Tool to erase personal birth data.
	include_once $path . 'includes/admin-tools.php';

	include_once $path . 'includes/admin-settings.php';
}

/**
 * Fires when the plugin is activated.
 * Adds the manage_zodiacpress_settings capability to the WooCommerce shop_manager
 * to allow them to erase ZP birth data from orders.
 */
function zpsr_activate() {
	// Add plugin caps
	$shop_manager = get_role( 'shop_manager' );
	if ( null != $shop_manager ) {
		$shop_manager->add_cap( 'manage_zodiacpress_settings' );
	}
}
register_activation_hook( __FILE__, 'zpsr_activate' );
