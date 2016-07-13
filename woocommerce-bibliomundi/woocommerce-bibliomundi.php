<?php
/**
 * Plugin Name: WooCommerce BiblioMundi
 * Description: Integração com a BiblioMundi
 * Author: Aires Gonçalves
 * Author URI: http://github.com/airesvsg
 * Version: 1.0.0
 * Plugin URI: http://github.com/airesvsg/bibliomundi
 * Text Domain: woocommerce-bibliomundi
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_BiblioMundi' ) ) :

class WC_BiblioMundi {

	const VERSION = '1.0.0';

	private static $instance = null;

	private function __construct() {
		if ( class_exists( 'WooCommerce' ) ) {
			$this->includes();
			$this->hooks();
		} else {
			add_action( 'admin_notices', array( $this, 'notice' ) );
		}
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function hooks() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_settings_page' ) );
	}

	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-bibliomundi' );
		load_textdomain( 'woocommerce-bibliomundi', untrailingslashit( plugin_dir_path( __FILE__ ) ) . "/languages/{$locale}.mo" );
	}

	public function add_settings_page( $settings ) {
		$settings[] = include 'includes/class-wc-settings-bibliomundi.php';
		return $settings;
	}

	private function includes() {
		require_once 'includes/class-wc-api-bibliomundi.php';
		require_once 'includes/class-wc-base-bibliomundi.php';
		require_once 'includes/class-wc-admin-bibliomundi.php';
		require_once 'includes/class-wc-notification-type-bibliomundi.php';
		require_once 'includes/class-wc-category-type-bibliomundi.php';
		require_once 'includes/class-wc-category-bibliomundi.php';
		require_once 'includes/class-wc-media-bibliomundi.php';
		require_once 'includes/class-wc-post-bibliomundi.php';
		require_once 'includes/class-wc-catalog-bibliomundi.php';
		require_once 'includes/class-wc-customer-bibliomundi.php';
		require_once 'includes/class-wc-order-bibliomundi.php';
		require_once 'includes/class-wc-checkout-bibliomundi.php';
		require_once 'includes/class-wc-cart-bibliomundi.php';
	}

	public function notice() {
		echo '<div class="error"><p>' . sprintf( __( 'WooCommerce BiblioMundi depends on %s to work!', 'woocommerce-bibliomundi' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">' . __( 'WooCommerce', 'woocommerce-bibliomundi' ) . '</a>' ) . '</p></div>';
	}

}

add_action( 'plugins_loaded', array( 'WC_BiblioMundi', 'get_instance' ) );

endif;
