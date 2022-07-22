<?php
/**
 * Plugin Name: Discount Percentage for WooCommerce
 * Plugin URI:  https://racmanuel.dev/plugins/discount-percentage-for-woocommerce
 * Description: Plugin will Replace "Sale" badge on every sales product with percentage of discount.
 * Version:     1.0.0
 * Author:      Manuel Ramirez Coronel
 * Author URI:  https://racmanuel.dev
 * Text Domain: discount-percentage-for-woocommerce
 * Domain Path: /languages
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package     Discount Percentage for WooCommerce
 * @author      Manuel Ramirez Coronel
 * @copyright   2022
 * @license     GPLv2 or later
 *
 * @wordpress-plugin
 *
 * Prefix:      discount_percentage_for_woocommerce
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is Active/Deactive
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // Yes, WooCommerce is enabled
    register_activation_hook(__FILE__, 'discount_percentage_for_woocommerce_activate');
} else {
    // WooCommerce is NOT enabled!
    add_action('admin_notices', 'discount_percentage_for_woocommerce_plugin_notice');
    register_deactivation_hook(__FILE__, 'discount_percentage_for_woocommerce_deactivate');

}

/**
 * Activate the plugin.
 */
function discount_percentage_for_woocommerce_activate()
{
    add_action('plugins_loaded', 'discount_percentage_for_woocommerce_plugin_init');
    add_filter('woocommerce_sale_flash', 'discount_percentage_for_woocommerce');
}

/**
 * Deactivation hook.
 */
function discount_percentage_for_woocommerce_deactivate()
{
    remove_filter('woocommerce_sale_flash', 'discount_percentage_for_woocommerce');
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
