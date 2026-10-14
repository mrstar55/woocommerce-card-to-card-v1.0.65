<?php
/**
 * WCCC Admin Bar Quick Access Node
 * Native WordPress & WooCommerce Enterprise Architecture
 *
 * @package WCCC\Admin
 * @version 1.0.0
 */

declare(strict_types=1);

namespace WCCC\Admin;

use WP_Admin_Bar;
use WCCC\Database\Schema;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AdminBar {

    /**
     * Singleton instance.
     *
     * @var AdminBar|null
     */
    private static ?AdminBar $instance = null;

    /**
     * Get singleton instance.
     *
     * @return AdminBar
     */
    public static function get_instance(): AdminBar {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_nodes' ), 90 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_bar_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_admin_bar_styles' ) );
    }

    /**
     * Enqueue minimal CSS for Admin Bar styling.
     */
    public function enqueue_admin_bar_styles(): void {
        if ( ! is_user_logged_in() || ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        // بررسی در محیط فرانت‌اند؛ جلوگیری از فراخوانی زودهنگام فیلترهای نوار ادمین قبل از اجرای کوئری اصلی وردپرس
        if ( ! is_admin() ) {
            if ( ! did_action( 'wp' ) && ! did_action( 'template_redirect' ) ) {
                return;
            }
            if ( function_exists( 'is_admin_bar_showing' ) && ! is_admin_bar_showing() ) {
                return;
            }
        }

        $font_source = get_option( 'wccc_admin_font_source', 'inherit' );
        $font_face_css = '';
        if ( 'vazirmatn' === $font_source ) {
            $font_regular_url = esc_url( WCCC_PLUGIN_URL . 'assets/fonts/vazirmatn.woff2' );
            $font_bold_url    = esc_url( WCCC_PLUGIN_URL . 'assets/fonts/vazirmatn-bold.woff2' );
            $font_face_css    = '
                @font-face {
                    font-family: "Vazirmatn";
                    font-style: normal;
                    font-weight: 400;
                    font-display: swap;
                    src: url("' . $font_regular_url . '") format("woff2");
                }
                @font-face {
                    font-family: "Vazirmatn";
                    font-style: normal;
                    font-weight: 700;
                    font-display: swap;
                    src: url("' . $font_bold_url . '") format("woff2");
                }
            ';
            $font_family_rule = '\'Vazirmatn\', -apple-system, BlinkMacSystemFont, Tahoma, sans-serif !important';
        } else {
            $font_family_rule = 'inherit, -apple-system, BlinkMacSystemFont, Tahoma, sans-serif !important';
        }

        $custom_css = $font_face_css . '
            #wp-admin-bar-wccc-pay-node > .ab-item {
                display: flex !important;
                align-items: center !important;
                gap: 6px !important;
                font-family: ' . $font_family_rule . ';
                font-weight: 700 !important;
                direction: rtl !important;
                font-feature-settings: "ss01" 1, "ss02" 1 !important;
                -webkit-font-feature-settings: "ss01" 1, "ss02" 1 !important;
            }
            #wp-admin-bar-wccc-pay-node .ab-icon.dashicons {
                font-family: dashicons !important;
                font-size: 16px !important;
                line-height: 1 !important;
                height: 16px !important;
                width: 16px !important;
                color: currentColor;
                display: inline-block;
                vertical-align: middle;
            }
            .wccc-admin-bar-pill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }
            .wccc-admin-bar-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                display: inline-block;
            }
            .wccc-admin-bar-dot.active {
                background-color: #10b981;
                box-shadow: 0 0 6px #10b981;
            }
            .wccc-admin-bar-dot.warning {
                background-color: #f59e0b;
                box-shadow: 0 0 6px #f59e0b;
            }
            .wccc-admin-bar-counter {
                background: #ef4444;
                color: #ffffff;
                font-size: 10px;
                font-weight: 800;
                padding: 1px 6px;
                border-radius: 9999px;
                margin-right: 6px;
                line-height: 1.4;
                display: inline-block;
                font-family: ' . $font_family_rule . ';
                font-feature-settings: "ss01" 1, "ss02" 1 !important;
            }
            .wccc-admin-bar-badge {
                background: rgba(255,255,255,0.15);
                color: #ffffff;
                font-size: 10px;
                font-weight: 700;
                padding: 1px 6px;
                border-radius: 4px;
                margin-right: 6px;
                line-height: 1.4;
                display: inline-block;
                font-family: ' . $font_family_rule . ';
                font-feature-settings: "ss01" 1, "ss02" 1 !important;
            }
            #wp-admin-bar-wccc-pay-node .ab-sub-wrapper {
                direction: rtl !important;
                text-align: right !important;
            }
            #wp-admin-bar-wccc-pay-node .ab-sub-wrapper .ab-item,
            #wp-admin-bar-wccc-pay-node .ab-sub-wrapper * {
                font-family: ' . $font_family_rule . ';
                font-feature-settings: "ss01" 1, "ss02" 1 !important;
                -webkit-font-feature-settings: "ss01" 1, "ss02" 1 !important;
            }
        ';
        wp_add_inline_style( 'admin-bar', $custom_css );
    }

    /**
     * Add WCCC nodes to WordPress Admin Bar.
     *
     * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
     */
    public function add_admin_bar_nodes( WP_Admin_Bar $wp_admin_bar ): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        global $wpdb;

        // 1. تعداد کارتهای بانکی فعال
        $cards_table = Schema::get_table_name( Schema::TABLE_CARDS );
        $active_cards_count = 0;
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$cards_table}'" ) === $cards_table ) {
            $active_cards_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$cards_table} WHERE is_active = 1" );
        }

        // 2. تعداد سفارش‌های منتظر تأیید / در انتظار پرداخت (استفاده از کش سبک ووکامرس برای جلوگیری از تداخل WP_Query و تگ‌های شرطی)
        $awaiting_orders_count = 0;
        if ( function_exists( 'wc_get_order_counts' ) ) {
            $order_counts          = wc_get_order_counts();
            $awaiting_orders_count = (int) ( $order_counts['wccc-awaiting'] ?? 0 ) + (int) ( $order_counts['wccc-review'] ?? 0 );
        } elseif ( function_exists( 'wc_orders_count' ) ) {
            $awaiting_orders_count = (int) wc_orders_count( 'wccc-awaiting' ) + (int) wc_orders_count( 'wccc-review' );
        } elseif ( function_exists( 'wc_get_orders' ) ) {
            $awaiting_orders = wc_get_orders( array(
                'status' => array( 'wc-wccc-awaiting', 'wccc-awaiting', 'wc-wccc-review', 'wccc-review' ),
                'return' => 'ids',
                'limit'  => 100,
            ) );
            $awaiting_orders_count = is_array( $awaiting_orders ) ? count( $awaiting_orders ) : 0;
        }

        // وضعیت سیستم: سبز اگر کارت فعال وجود دارد، زرد/هشدار اگر صفر کارت فعال باشد
        $has_active = $active_cards_count > 0;
        $dot_class  = $has_active ? 'active' : 'warning';
        $status_tip = $has_active ? __( 'سیستم فعال و کارت‌های بانکی متصل است', 'wccc' ) : __( 'هشدار: هیچ کارت بانکی فعالی در سیستم وجود ندارد', 'wccc' );

        $main_title = '<span class="wccc-admin-bar-pill" title="' . esc_attr( $status_tip ) . '">' .
                      '<span class="ab-icon dashicons dashicons-money-alt"></span>' .
                      '<span class="wccc-admin-bar-dot ' . esc_attr( $dot_class ) . '"></span>' .
                      '<span>' . esc_html__( 'کارت به کارت هوشمند', 'wccc' ) . '</span>';
        if ( $awaiting_orders_count > 0 ) {
            $main_title .= '<span class="wccc-admin-bar-counter">' . esc_html( (string) $awaiting_orders_count ) . '</span>';
        }
        $main_title .= '</span>';

        $base_url = admin_url( 'admin.php?page=wccc-pay' );

        // گره اصلی (Root Node)
        $wp_admin_bar->add_node( array(
            'id'    => 'wccc-pay-node',
            'title' => $main_title,
            'href'  => add_query_arg( 'tab', 'dashboard', $base_url ),
            'meta'  => array(
                'title' => esc_html__( 'سامانه کارت به کارت هوشمند', 'wccc' ),
            ),
        ) );

        // ۱. داشبورد مالی (Financial Dashboard)
        $wp_admin_bar->add_node( array(
            'id'     => 'wccc-pay-dashboard',
            'parent' => 'wccc-pay-node',
            'title'  => '📊 ' . esc_html__( 'داشبورد مالی و مانیتورینگ', 'wccc' ),
            'href'   => add_query_arg( 'tab', 'dashboard', $base_url ),
        ) );

        // ۲. سفارش‌های منتظر تایید (Awaiting Orders) با شمارنده پویا
        $orders_title = '📝 ' . esc_html__( 'سفارش‌های منتظر تایید', 'wccc' );
        if ( $awaiting_orders_count > 0 ) {
            $orders_title .= ' <span class="wccc-admin-bar-counter">' . esc_html( (string) $awaiting_orders_count ) . '</span>';
        }
        $wp_admin_bar->add_node( array(
            'id'     => 'wccc-pay-orders',
            'parent' => 'wccc-pay-node',
            'title'  => $orders_title,
            'href'   => add_query_arg( array( 'tab' => 'orders', 'status' => 'awaiting' ), $base_url ),
        ) );

        // ۳. کارت‌های بانکی (Bank Cards) با تعداد کارت‌های فعال
        $cards_title = '💳 ' . esc_html__( 'کارت‌های بانکی', 'wccc' ) .
                       ' <span class="wccc-admin-bar-badge">' . esc_html( sprintf( __( '%d فعال', 'wccc' ), $active_cards_count ) ) . '</span>';
        $wp_admin_bar->add_node( array(
            'id'     => 'wccc-pay-cards',
            'parent' => 'wccc-pay-node',
            'title'  => $cards_title,
            'href'   => add_query_arg( 'tab', 'cards', $base_url ),
        ) );

        // ۴. تنظیمات سیستم (Settings)
        $wp_admin_bar->add_node( array(
            'id'     => 'wccc-pay-settings',
            'parent' => 'wccc-pay-node',
            'title'  => '⚙️ ' . esc_html__( 'تنظیمات سیستم', 'wccc' ),
            'href'   => add_query_arg( 'tab', 'settings', $base_url ),
        ) );

        // ۵. وضعیت سلامت و وب‌هوک (System Health)
        $wp_admin_bar->add_node( array(
            'id'     => 'wccc-pay-health',
            'parent' => 'wccc-pay-node',
            'title'  => '🩺 ' . esc_html__( 'وضعیت سلامت و وب‌هوک', 'wccc' ),
            'href'   => add_query_arg( 'tab', 'health', $base_url ),
        ) );
    }
}

// Backward compatibility alias for global scope
class_alias( AdminBar::class, 'WCCC_Admin_Bar' );
