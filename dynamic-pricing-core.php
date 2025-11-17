<?php
/**
 * Main loader for the Dynamic Pricing Core.
 *
 * This file should be included from the active theme's functions.php file.
 * It handles the initialization of the dynamic pricing logic.
 *
 * @package HussainasDynamicPricing
 * @version     1.0.0
 * @author      Hussain Ahmed Shrabon
 * @license     GPL-2.0-or-later
 * @link        https://github.com/iamhussaina
 * @textdomain  hussainas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define a constant for the directory path for easier inclusion.
define( 'HUSSAINAS_PRICING_CORE_PATH', __DIR__ . '/' );

/**
 * Checks if WooCommerce is active before proceeding.
 *
 * We cannot proceed if the core dependency (WooCommerce) is missing.
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true ) ) {
	
	// Since this is not a plugin, we cannot use admin notices.
	// We will simply log an error if WP_DEBUG is on and exit loading.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'Hussainas Dynamic Pricing Core: WooCommerce is not active. The pricing engine will not load.' );
	}
	return;
}

/**
 * Loads the main pricing class file.
 *
 * We check if the class exists first to prevent conflicts
 * if this structure is loaded multiple times by mistake.
 */
if ( ! class_exists( 'Hussainas_Dynamic_Pricing_Engine' ) ) {
	$class_file = HUSSAINAS_PRICING_CORE_PATH . 'includes/class-hussainas-dynamic-pricing.php';
	
	if ( file_exists( $class_file ) ) {
		require_once $class_file;
	} else {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'Hussainas Dynamic Pricing Core: The class file (class-hussainas-dynamic-pricing.php) is missing.' );
		}
		return;
	}
}

/**
 * Initializes the pricing engine.
 *
 * This function creates the instance of our main class,
 * which in turn registers all necessary WordPress/WooCommerce hooks.
 *
 * @return void
 */
function hussainas_load_pricing() {
	new Hussainas_Dynamic_Pricing();
}
// We hook into 'init' to ensure all plugins (like WooCommerce) are loaded.
add_action( 'init', 'hussainas_load_pricing' );
