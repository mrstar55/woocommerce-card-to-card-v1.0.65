<?php
/**
 * Plugin Name: سامانه مدیریت پرداخت کارت به کارت ووکامرس (WCCC)
 * Plugin URI: https://github.com/mrstar55/wccc-plugin-V01
 * Description: افزونه جامع و سازمانی مدیریت پرداخت‌های کارت به کارت در ووکامرس با قابلیت همگام‌سازی خودکار پیامک‌های بانکی، احراز هویت رمزنگاری‌شده دستگاه پوز/اندروید و پشتیبانی کامل از ذخیره‌ساز سفارش با عملکرد بالا (HPOS).
 * Version: 1.0.65
 * Author: مجتبی وفادار
 * Author URI: https://nabdan.ir
 * Text Domain: wccc
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 9.5
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

declare(strict_types=1);

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use WCCC\Database\Schema;
use WCCC\Orders\CustomStatuses;
use WCCC\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // دسترسی مستقیم غیرمجاز است.
}

// ۱. ثوابت اصلی افزونه
if ( ! defined( 'WCCC_VERSION' ) ) {
	define( 'WCCC_VERSION', '1.0.65' );
}
define( 'WCCC_DB_VERSION', '1.0.0' );
define( 'WCCC_PLUGIN_FILE', __FILE__ );
define( 'WCCC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCCC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WCCC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WCCC_MIN_PHP_VERSION', '7.4.0' );
define( 'WCCC_MIN_WC_VERSION', '6.0.0' );

// ۲. اتولودر استاندارد PSR-4
spl_autoload_register( static function ( string $class_name ) {
    $prefix   = 'WCCC\\';
    $base_dir = WCCC_PLUGIN_DIR . 'includes/';
    $len      = strlen( $prefix );

    if ( strncmp( $prefix, $class_name, $len ) !== 0 ) {
        return;
    }

    $relative_class = substr( $class_name, $len );
    $file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

// ۳. سازگاری رسمی با HPOS و بلوک‌های ووکامرس
add_action( 'before_woocommerce_init', static function () {
    if ( class_exists( FeaturesUtil::class ) ) {
        FeaturesUtil::declare_compatibility( 'custom_order_tables', WCCC_PLUGIN_FILE, true );
        FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', WCCC_PLUGIN_FILE, true );
    }
} );

// ۴. هوک‌های فعال‌سازی و غیرفعال‌سازی
register_activation_hook( WCCC_PLUGIN_FILE, static function () {
    if ( version_compare( PHP_VERSION, WCCC_MIN_PHP_VERSION, '<' ) ) {
        deactivate_plugins( WCCC_PLUGIN_BASENAME );
        wp_die( esc_html__( 'افزونه کارت به کارت ووکامرس (WCCC) نیازمند پی‌اچ‌پی نسخه ۷.۴ یا بالاتر است.', 'wccc' ) );
    }

    Schema::migrate();
    CustomStatuses::register_statuses();
    flush_rewrite_rules();
} );

register_deactivation_hook( WCCC_PLUGIN_FILE, static function () {
    flush_rewrite_rules();
} );

// ۵. بارگذاری افزونه پس از ووکامرس
add_action( 'plugins_loaded', static function () {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', static function () {
            echo '<div class="notice notice-error"><p>' . esc_html__( 'سامانه کارت به کارت ووکامرس (WCCC): برای استفاده از این افزونه، ابتدا باید افزونه ووکامرس فعال گردد.', 'wccc' ) . '</p></div>';
        } );
        return;
    }

    Plugin::instance();
}, 5 );
