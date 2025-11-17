# Dynamic Pricing Core for WooCommerce

Implementing dynamic pricing rules in WooCommerce.

This implementation allows for:
* **Role-Based Pricing:** Apply a percentage discount for users with a specific capability (e.g., 'vip_customer').
* **Quantity-Based Pricing:** Apply a percentage discount if a user buys a certain quantity of a single item.

## Features

* **OOP Design:** Clean, maintainable, and extensible Object-Oriented PHP.
* **High Performance:** Hooks into WooCommerce efficiently and performs checks only when needed.
* **Rule Priority:** Built-in logic to ensure role-based discounts take priority over quantity discounts, preventing "double-dipping."
* **Price-Safe:** Stores the original product price upon "Add to Cart" to ensure discounts are always calculated from the correct base price, even on cart refreshes or updates.

---

## 🚀 Installation

Follow these steps to integrate this pricing core into your theme.

1.  **Copy the Folder:**
    Place the entire `wc-dynamic-pricing-` folder into your active theme's directory. The recommended location is the root of your theme.
    
    ```
    /wp-content/themes/your-theme/
    |
    +-- /wc-dynamic-pricing/
    |
    +-- functions.php
    +-- style.css
    +-- ... (other theme files)
    ```

2.  **Include the Loader:**
    Open your theme's `functions.php` file and add the following line of PHP to include the main loader file. This initializes the pricing engine.

    ```php
    // Load the custom dynamic pricing engine
    require_once( get_template_directory() . '/wc-dynamic-pricing/dynamic-pricing-core.php' );
    ```

That's it. The pricing engine is now active and will apply its default rules.

---

## ⚙️ Configuration

To change the pricing rules, you do not need to modify any hooks. Simply edit the private configuration properties at the top of the `includes/class-hussainas-dynamic-pricing.php` file.

```php
class Hussainas_Dynamic_Pricing {

	// --- Configuration Properties ---

	/**
	 * The user role or capability to check for role-based discounts.
	 * @var string
	 */
	private $capability_to_check = 'vip_customer';

	/**
	 * The discount percentage for the custom role (e.g., 0.20 for 20% off).
	 * @var float
	 */
	private $role_discount_rate = 0.20;

	/**
	 * The minimum quantity required in the cart for a bulk discount.
	 * @var int
	 */
	private $quantity_threshold = 3;

	/**
	 * The discount percentage for meeting the quantity threshold (e.g., 0.10 for 10% off).
	 * @var float
	 */
	private $quantity_discount_rate = 0.10;

	// --- End Configuration ---
	
	// ... (rest of the class logic)
}
