<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/*
Plugin Name: WooCommerce Hutkigrosh Gateway
Plugin URI: https://github.com/esasby/hutkigrosh-wordpress4-woocommerce3-module
Description: Модуль для выставления счетов в систему ЕРИП через сервис ХуткiГрош
Version: 2.5.0
Author: ESAS
Author Email: n.mekh@hutkigrosh.by
Text Domain: woocommerce-hutkigrosh-payments
*/

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action('plugins_loaded', 'wc_hutkigrosh_gateway_init', 0);
function wc_hutkigrosh_gateway_init()
{
    // If the parent WC_Payment_Gateway class doesn't exist
    // it means WooCommerce is not installed on the site
    // so do nothing
    if (!class_exists('WC_Payment_Gateway')) return;
    // If we made it this far, then include our Gateway Class
    include_once('hg.php');
    // Now that we have successfully included our class,
    // Lets add it too WooCommerce
    add_filter('woocommerce_payment_gateways', 'hutkigrosh_add_payment_gateway');

    function hutkigrosh_add_payment_gateway($methods)
    {
        $methods[] = 'WC_HUTKIGROSH_GATEWAY';
        return $methods;
    }
}

// Add custom action links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'hutkigrosh_gateway_action_links');
function hutkigrosh_gateway_action_links($links)
{
    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_hutkigrosh_gateway') . '">' . __('settings', 'woocommerce-hutkigrosh-payments') . '</a>',
    );
    // Merge our new link with the default ones
    return array_merge($plugin_links, $links);
}

//Инициализация плагина и загрузка языкового пакета для плагина
add_action("init", "hutkigrosh_init");
function hutkigrosh_init()
{
    $res = load_plugin_textdomain("woocommerce-hutkigrosh-payments", false, basename(dirname(__FILE__)) . '/languages/');
}


/**
 * Custom text on the receipt page.
 */
add_action('wp_ajax_alfaclick', 'alfaclick_callback');
add_action('wp_ajax_nopriv_alfaclick', 'alfaclick_callback');
function alfaclick_callback()
{
    return WC_HUTKIGROSH_GATEWAY::get_instance()->alfaclick_callback();
}