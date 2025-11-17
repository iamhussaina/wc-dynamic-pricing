<?php
/**
 * Handling dynamic pricing rules.
 *
 * This class contains all the logic for applying role-based
 * and quantity-based discounts to WooCommerce cart items.
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

/**
 * Class Hussainas_Dynamic_Pricing.
 *
 * Manages the hooks and logic for applying custom pricing.
 * All rules are configurable via the private properties below.
 */
class Hussainas_Dynamic_Pricing {

	// --- Configuration Properties ---

	/**
	 * The user role or capability to check for role-based discounts.
	 * Using a capability (e.g., 'manage_options') is more flexible than a role (e.g., 'administrator').
	 * Let's use a custom capability 'vip_customer' for this example.
	 *
	 * @var string
	 */
	private $capability_to_check = 'vip_customer';

	/**
	 * The discount percentage for the custom role (e.g., 0.20 for 20% off).
	 *
	 * @var float
	 */
	private $role_discount_rate = 0.20;

	/**
	 * The minimum quantity required in the cart for a bulk discount.
	 *
	 * @var int
	 */
	private $quantity_threshold = 3;

	/**
	 * The discount percentage for meeting the quantity threshold (e.g., 0.10 for 10% off).
	 *
	 * @var float
	 */
	private $quantity_discount_rate = 0.10;

	// --- End Configuration ---


	/**
	 * Constructor.
	 *
	 * Sets up the necessary hooks for WooCommerce to modify cart prices.
	 */
	public function __construct() {
		// This filter is crucial. It stores the original, non-discounted price
		// in the cart item data. This prevents re-calculation errors on cart refresh.
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'hussainas_store_original_price' ), 10, 2 );

		// This action runs before cart totals are calculated.
		// It's the perfect place to apply our dynamic price logic.
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'hussainas_apply_dynamic_pricing' ), 99, 1 );
	}

	/**
	 * Stores the original product price in the cart item data.
	 *
	 * When a product is added to the cart, we fetch its base price and store
	 * it in a custom meta field within the cart item. This ensures we
	 * always calculate discounts from the "real" price, not a
	 * potentially already-discounted price.
	 *
	 * @param array $cart_item_data The current cart item data.
	 * @param int   $product_id     The ID of the product being added.
	 * @return array The modified cart item data with the original price.
	 */
	public function hussainas_store_original_price( $cart_item_data, $product_id ) {
		$product = wc_get_product( $product_id );
		
		if ( $product ) {
			// For variable products, this correctly gets the variation's price.
			$cart_item_data['hussainas_original_price'] = (float) $product->get_price();
		}
		return $cart_item_data;
	}

	/**
	 * Applies the dynamic pricing rules to the cart.
	 *
	 * This is the main engine. It iterates through each cart item and
	 * checks our defined rules (role, quantity). It applies the *best*
	 * available discount, with role discounts taking priority.
	 *
	 * @param WC_Cart $cart The WooCommerce cart object, passed by the hook.
	 */
	public function hussainas_apply_dynamic_pricing( $cart ) {
		// Avoid running in the admin backend unless it's an AJAX request.
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		// Check if the cart is empty or the method doesn't exist (future-proofing).
		if ( $cart->is_empty() || ! is_callable( array( $cart, 'get_cart' ) ) ) {
			return;
		}
		
		// Check the user's capability once, not inside the loop.
		$is_eligible_for_role_discount = current_user_can( $this->capability_to_check );

		// Iterate through each item in the cart.
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			
			// Retrieve the original price we stored.
			if ( isset( $cart_item['hussainas_original_price'] ) ) {
				$original_price = (float) $cart_item['hussainas_original_price'];
			} else {
				// As a fallback, get the price from the product data.
				// This might be unreliable if other plugins are modifying prices.
				$original_price = (float) $cart_item['data']->get_price();
			}

			// Start with the original price.
			$final_price = $original_price;
			$has_discount_applied = false;

			// --- Rule 1: Role-Based Discount (Priority Rule) ---
			// We check this rule first. If it applies, we skip other discounts.
			if ( $is_eligible_for_role_discount ) {
				$final_price = $original_price * ( 1 - $this->role_discount_rate );
				$has_discount_applied = true;
			}

			// --- Rule 2: Quantity-Based Discount ---
			// Only apply this rule if a priority (role) discount has NOT been applied.
			if ( ! $has_discount_applied && $cart_item['quantity'] >= $this->quantity_threshold ) {
				$final_price = $original_price * ( 1 - $this->quantity_discount_rate );
				// No need to set $has_discount_applied = true here, as it's the last rule.
			}

			// Apply the final calculated price to the cart item.
			// This is safe because $cart_item['data'] is a cloned object
			// for this specific cart session and does not affect the
			// global product price.
			$cart_item['data']->set_price( $final_price );
		}
	}
}
