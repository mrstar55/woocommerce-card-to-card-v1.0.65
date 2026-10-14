<?php
/**
 * WCCC Admin Menu Controller
 * Native WordPress & WooCommerce Enterprise Architecture
 *
 * @package WCCC\Admin
 * @version 3.8.5
 */

namespace WCCC\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AdminMenu {

    /**
     * Singleton instance.
     *
     * @var AdminMenu|null
     */
    private static ?AdminMenu $instance = null;

    /**
     * Get singleton instance.
     *
     * @return AdminMenu
     */
    public static function get_instance(): AdminMenu {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu_pages' ) );
        add_action( 'current_screen', array( $this, 'ensure_admin_page_title' ) );
        add_filter( 'admin_title', array( $this, 'filter_admin_title' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Ensure global $title is never null or empty to prevent PHP 8.1+ strip_tags(null) deprecation notice.
     */
    public function ensure_admin_page_title(): void {
        global $title, $plugin_page;
        if ( ! empty( $plugin_page ) && ( 'wccc-dashboard' === $plugin_page || 'wccc-pay' === $plugin_page || false !== strpos( (string) $plugin_page, 'wccc' ) ) ) {
            if ( null === $title || '' === trim( (string) $title ) ) {
                $title = __( 'کارت به کارت هوشمند', 'wccc' );
            }
        }
    }

    /**
     * Filter admin title to ensure non-empty Persian title on WCCC pages.
     *
     * @param string|null $admin_title Admin title from get_admin_page_title().
     * @param string|null $title       Raw page title.
     * @return string
     */
    public function filter_admin_title( $admin_title, $title = '' ): string {
        global $plugin_page;
        if ( ! empty( $plugin_page ) && ( 'wccc-dashboard' === $plugin_page || 'wccc-pay' === $plugin_page || false !== strpos( (string) $plugin_page, 'wccc' ) ) ) {
            if ( null === $admin_title || '' === trim( (string) $admin_title ) ) {
                $admin_title = __( 'کارت به کارت هوشمند', 'wccc' );
            }
        }
        return (string) ( $admin_title ?: __( 'کارت به کارت هوشمند', 'wccc' ) );
    }

    /**
     * Register WordPress Top-level Menu Page.
     */
    public function register_menu_pages(): void {
        add_menu_page(
            __( 'کارت به کارت هوشمند', 'wccc' ),
            __( 'کارت به کارت هوشمند', 'wccc' ),
            'manage_woocommerce',
            'wccc-dashboard',
            array( $this, 'render_dashboard' ),
            'dashicons-money-alt',
            54
        );

        // Add hidden submenu alias for wccc-pay to support ?page=wccc-pay&tab=... seamlessly
        add_submenu_page(
            null,
            __( 'کارت به کارت هوشمند', 'wccc' ),
            __( 'کارت به کارت هوشمند', 'wccc' ),
            'manage_woocommerce',
            'wccc-pay',
            array( $this, 'render_dashboard' )
        );
    }

    /**
     * Enqueue Admin Assets on WCCC Dashboard page.
     *
     * @param string $hook_suffix The current admin page.
     */
    public function enqueue_admin_assets( string $hook_suffix ): void {
        if ( 'toplevel_page_wccc-dashboard' !== $hook_suffix && 'admin_page_wccc-pay' !== $hook_suffix ) {
            return;
        }

        // Enqueue Scoped Stylesheet.
        wp_enqueue_style(
            'wccc-admin-css',
            WCCC_PLUGIN_URL . 'assets/css/wccc-admin.css',
            array(),
            WCCC_VERSION
        );

        // Adaptive Admin Font Engine Dynamic Injection (Zero-traffic conditional font loading).
        $font_source = get_option( 'wccc_admin_font_source', 'inherit' );
        if ( 'vazirmatn' === $font_source ) {
            $vazir_regular_url = esc_url( WCCC_PLUGIN_URL . 'assets/fonts/vazirmatn.woff2' );
            $vazir_bold_url    = esc_url( WCCC_PLUGIN_URL . 'assets/fonts/vazirmatn-bold.woff2' );
            $custom_font_css   = "@font-face {\n"
                . "  font-family: 'Vazirmatn';\n"
                . "  font-style: normal;\n"
                . "  font-weight: 400;\n"
                . "  font-display: swap;\n"
                . "  src: url('{$vazir_regular_url}') format('woff2');\n"
                . "}\n"
                . "@font-face {\n"
                . "  font-family: 'Vazirmatn';\n"
                . "  font-style: normal;\n"
                . "  font-weight: 700;\n"
                . "  font-display: swap;\n"
                . "  src: url('{$vazir_bold_url}') format('woff2');\n"
                . "}\n"
                . ":root, .wccc-wrap {\n"
                . "  --wccc-font-sans: 'Vazirmatn', -apple-system, BlinkMacSystemFont, Tahoma, sans-serif !important;\n"
                . "}";
        } else {
            $custom_font_css = ":root, .wccc-wrap {\n"
                . "  --wccc-font-sans: inherit, -apple-system, BlinkMacSystemFont, Tahoma, sans-serif !important;\n"
                . "}";
        }
        wp_add_inline_style( 'wccc-admin-css', $custom_font_css );

        // Enqueue Vanilla Controller.
        wp_enqueue_script(
            'wccc-admin-js',
            WCCC_PLUGIN_URL . 'assets/js/wccc-admin.js',
            array(),
            WCCC_VERSION,
            true
        );

        // Check WooCommerce HPOS status.
        $is_hpos_active = false;
        if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
            $is_hpos_active = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        }

        // Localize script with nonces, endpoints, and Persian i18n messages.
        $localize_payload = array(
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'ajax_url'    => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'wccc_admin_nonce' ),
            'hposActive'  => $is_hpos_active,
            'hpos_active' => $is_hpos_active,
            'pluginUrl'   => WCCC_PLUGIN_URL,
            'plugin_url'  => WCCC_PLUGIN_URL,
            'i18n'        => array(
                'success'       => __( 'عملیات با موفقیت انجام شد.', 'wccc' ),
                'error'         => __( 'خطایی در انجام عملیات رخ داد.', 'wccc' ),
                'saving'        => __( 'در حال ذخیره‌سازی...', 'wccc' ),
                'saved'         => __( 'تنظیمات با موفقیت ذخیره شد.', 'wccc' ),
                'deleting'      => __( 'در حال حذف...', 'wccc' ),
                'deleted'       => __( 'کارت بانکی حذف شد.', 'wccc' ),
                'confirmDelete' => __( 'آیا از حذف این کارت بانکی اطمینان دارید؟', 'wccc' ),
                'approving'     => __( 'در حال تایید فیش...', 'wccc' ),
                'approved'      => __( 'سفارش با موفقیت تایید شد.', 'wccc' ),
                'rejecting'     => __( 'در حال رد کردن فیش...', 'wccc' ),
                'rejected'      => __( 'فیش سفارش رد شد.', 'wccc' ),
                'pinging'       => __( 'در حال ارسال پینگ...', 'wccc' ),
                'pingSuccess'   => __( 'پینگ با موفقیت انجام شد.', 'wccc' ),
                'copied'        => __( 'مورد نظر با موفقیت در حافظه کپی شد.', 'wccc' ),
                'networkError'  => __( 'خطای ارتباط با سرور. لطفاً اتصال را بررسی نمایید.', 'wccc' ),
            ),
        );

        wp_localize_script( 'wccc-admin-js', 'wcccAdmin', $localize_payload );
        wp_localize_script( 'wccc-admin-js', 'wccc_admin', $localize_payload );
    }

    /**
     * Render Master Dashboard View.
     */
    public function render_dashboard(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'شما اجازه دسترسی به این بخش را ندارید.', 'wccc' ) );
        }

        global $title;
        if ( empty( $title ) ) {
            $title = __( 'کارت به کارت هوشمند', 'wccc' );
        }

        // Check HPOS Status.
        $hpos_enabled = false;
        if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
            $hpos_enabled = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
        }

        // Prepare View Variables.
        $data = array(
            'hpos_enabled' => $hpos_enabled,
            'active_tab'   => isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'dashboard',
            'nonce'        => wp_create_nonce( 'wccc_admin_nonce' ),
            'settings'     => get_option( AjaxHandler::SETTINGS_OPTION_KEY, array() ),
        );

        // Load Master Layout Template.
        include WCCC_PLUGIN_DIR . 'includes/Admin/Views/layout.php';
    }
}

// Backward compatibility alias for global scope
class_alias( AdminMenu::class, 'WCCC_Admin_Menu' );
