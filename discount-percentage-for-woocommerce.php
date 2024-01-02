<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress or ClassicPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://racmanuel.dev
 * @since             1.0.1
 *
 * @wordpress-plugin
 * Plugin Name:       Discount Percentage for WooCommerce
 * Plugin URI:        hhttps://racmanuel.dev/plugins/discount-percentage-for-woocommerce
 * Description:       Plugin will Replace "Sale" badge on every sales product with percentage of discount.
 * Version:           1.0.1
 * Author:            Manuel Ramirez Coronel
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Tested up to:      6.4
 * Author URI:        https://racmanuel.dev/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       discount-percentage-for-woocommerce
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require __DIR__ . '/vendor/autoload.php';

/**
 * Check if WooCommerce is Active/Deactive
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // Yes, WooCommerce is enabled
    add_action('plugins_loaded', 'discount_percentage_for_woocommerce_plugin_init');
    add_filter('woocommerce_sale_flash', 'discount_percentage_for_woocommerce');
} else {
    // WooCommerce is NOT enabled!
    add_action('admin_notices', 'discount_percentage_for_woocommerce_plugin_notice');
}

/**
 * Load localization files
 */
function discount_percentage_for_woocommerce_plugin_init()
{
    load_plugin_textdomain('discount-percentage-for-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

function discount_percentage_for_woocommerce()
{
    global $product;
    global $html;

    if ($product->is_type('variable')) {
        $percentages = array();

        // Get all variation prices
        $prices = $product->get_variation_prices();

        // Loop through variation prices
        foreach ($prices['price'] as $key => $price) {
            // Only on sale variations
            if ($prices['regular_price'][$key] !== $price) {
                // Calculate and set in the array the percentage for each variation on sale
                $percentages[] = round(100 - (floatval($prices['sale_price'][$key]) / floatval($prices['regular_price'][$key]) * 100));
            }
        }
        // We keep the highest value
        $percentage = max($percentages) . '%';

    } elseif ($product->is_type('grouped')) {
        $percentages = array();

        // Get all variation prices
        $children_ids = $product->get_children();

        // Loop through variation prices
        foreach ($children_ids as $child_id) {
            $child_product = wc_get_product($child_id);

            $regular_price = (float) $child_product->get_regular_price();
            $sale_price = (float) $child_product->get_sale_price();

            if ($sale_price != 0 || !empty($sale_price)) {
                // Calculate and set in the array the percentage for each child on sale
                $percentages[] = round(100 - ($sale_price / $regular_price * 100));
            }
        }
        // We keep the highest value
        $percentage = max($percentages) . '%';

    } else {
        $regular_price = (float) $product->get_regular_price();
        $sale_price = (float) $product->get_sale_price();

        if ($sale_price != 0 || !empty($sale_price)) {
            $percentage = round(100 - ($sale_price / $regular_price * 100)) . '%';
        } else {
            return $html;
        }
    }
    return '<span class="onsale">' . esc_html__('Sale', 'discount-percentage-for-woocommerce') . ' ' . $percentage . '</span>';
}

function discount_percentage_for_woocommerce_plugin_notice()
{
    global $pagenow;
    if ($pagenow == 'plugins.php') {
        $class = 'notice notice-error';
        $message = __('Error has occurred, you need active WooCommerce for the Discount Percentage for WooCommerce Works.', 'discount-percentage-for-woocommerce');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }
}

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_discount_percentage_for_woocommerce() {

    if ( ! class_exists( 'Appsero\Client' ) ) {
      require_once __DIR__ . '/appsero/src/Client.php';
    }

    $client = new Appsero\Client( 'fda167fc-af73-4087-b921-5d1eade6887d', 'Discount Percentage for WooCommerce', __FILE__ );

    // Active insights
    $client->insights()->init();

}

appsero_init_tracker_discount_percentage_for_woocommerce();
