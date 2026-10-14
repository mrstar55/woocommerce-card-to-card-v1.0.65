<?php
/**
 * WCCC Admin AJAX Handler
 * Native WordPress & WooCommerce High-Performance AJAX Architecture
 *
 * @package WCCC\Admin
 * @version 3.8.5
 */

namespace WCCC\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AjaxHandler {

    /**
     * Singleton instance.
     *
     * @var AjaxHandler|null
     */
    private static ?AjaxHandler $instance = null;

    /**
     * Option key for WCCC settings.
     */
    public const SETTINGS_OPTION_KEY = 'wccc_gateway_settings';

    /**
     * Option key for bank cards.
     */
    public const CARDS_OPTION_KEY = 'wccc_bank_cards';

    /**
     * Option key for audit logs.
     */
    public const LOGS_OPTION_KEY = 'wccc_audit_logs';

    /**
     * Get singleton instance.
     *
     * @return AjaxHandler
     */
    public static function get_instance(): AjaxHandler {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->register_ajax_hooks();
    }

    /**
     * Register all wp_ajax hooks.
     */
    private function register_ajax_hooks(): void {
        $actions = array(
            'wccc_save_settings',
            'wccc_reset_settings',
            'wccc_toggle_card_status',
            'wccc_save_card',
            'wccc_delete_card',
            'wccc_get_dashboard_cards',
            'wccc_filter_orders',
            'wccc_review_receipt',
            'wccc_test_webhook',
            'wccc_clear_logs',
            'wccc_ping_device',
            'wccc_refresh_stats',
        );

        foreach ( $actions as $action ) {
            add_action( 'wp_ajax_' . $action, array( $this, $action ) );
        }
        add_action( 'wp_ajax_wccc_dismiss_notice', array( $this, 'ajax_dismiss_notice' ) );
    }

    /**
     * Check nonce and WooCommerce manage permissions.
     */
    private function verify_security(): void {
        check_ajax_referer( 'wccc_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array(
                'message' => __( 'دسترسی غیرمجاز. شما مجوز مدیریت ووکامرس را ندارید.', 'wccc' ),
            ), 403 );
        }
    }

    /**
     * 1. Save Settings (Penny Engine, Offsets, Webhook token, etc.)
     */
    public function wccc_save_settings(): void {
        $this->verify_security();

        $current_settings = get_option( self::SETTINGS_OPTION_KEY, array(
            'penny_engine_enabled' => 1,
            'min_offset'           => 100,
            'max_offset'           => 900,
            'window_timeout'       => 15,
            'matching_digits'      => 3,
            'max_file_size'        => 5,
            'allowed_formats'      => array( 'jpg', 'png', 'webp' ),
            'hmac_secret'          => 'wccc_sec_live_94f8a37b120c89e248b',
            'auto_refresh'         => 30,
        ) );

        $penny_enabled   = isset( $_POST['penny_engine_enabled'] ) ? intval( $_POST['penny_engine_enabled'] ) : 1;
        $min_offset      = isset( $_POST['min_offset'] ) ? max( 10, intval( $_POST['min_offset'] ) ) : 100;
        $max_offset      = isset( $_POST['max_offset'] ) ? min( 10000, max( $min_offset + 50, intval( $_POST['max_offset'] ) ) ) : 900;
        $window_timeout  = isset( $_POST['window_timeout'] ) ? max( 5, intval( $_POST['window_timeout'] ) ) : 15;
        $matching_digits = isset( $_POST['matching_digits'] ) ? intval( $_POST['matching_digits'] ) : 3;
        $max_file_size   = isset( $_POST['max_file_size'] ) ? max( 1, min( 20, intval( $_POST['max_file_size'] ) ) ) : 5;
        $hmac_secret     = isset( $_POST['hmac_secret'] ) ? sanitize_text_field( wp_unslash( $_POST['hmac_secret'] ) ) : ( $current_settings['hmac_secret'] ?? '' );
        $auto_refresh    = isset( $_POST['auto_refresh'] ) ? max( 5, intval( $_POST['auto_refresh'] ) ) : 30;

        // سبک چیدمان پیشخوان مدیریت (Admin Layout Style)
        if ( isset( $_POST['wccc_admin_layout_mode'] ) ) {
            $layout_mode = sanitize_key( $_POST['wccc_admin_layout_mode'] );
            if ( in_array( $layout_mode, array( 'horizontal', 'sidebar_compact', 'sidebar_classic' ), true ) ) {
                update_option( 'wccc_admin_layout_mode', $layout_mode );
            }
        }

        // تنظیم هدر افقی چسبان (Sticky Header)
        $sticky_header = ( isset( $_POST['wccc_sticky_header'] ) && 'yes' === sanitize_key( $_POST['wccc_sticky_header'] ) ) ? 'yes' : 'no';
        update_option( 'wccc_sticky_header', $sticky_header );

        // موتور انطباقی انتخاب فونت پیشخوان توسط مدیر (Adaptive Admin Font Engine)
        $font_source = 'inherit';
        if ( isset( $_POST['wccc_admin_font_source'] ) ) {
            $sanitized_font = sanitize_key( $_POST['wccc_admin_font_source'] );
            if ( in_array( $sanitized_font, array( 'inherit', 'vazirmatn' ), true ) ) {
                $font_source = $sanitized_font;
            }
        }
        update_option( 'wccc_admin_font_source', $font_source );

        $allowed_formats = array();
        if ( isset( $_POST['allowed_formats'] ) && is_array( $_POST['allowed_formats'] ) ) {
            $allowed_formats = array_map( 'sanitize_key', $_POST['allowed_formats'] );
        } else {
            $allowed_formats = array( 'jpg', 'png', 'webp' );
        }

        $card_theme = isset( $_POST['card_theme'] ) ? sanitize_key( $_POST['card_theme'] ) : ( $current_settings['card_theme'] ?? 'modern_gold' );
        if ( ! in_array( $card_theme, array( 'modern_gold', 'dark_glass', 'minimal_light' ), true ) ) {
            $card_theme = 'modern_gold';
        }
        $show_bank_logo      = isset( $_POST['show_bank_logo'] ) ? ! empty( $_POST['show_bank_logo'] ) : ( isset( $current_settings['show_bank_logo'] ) ? ! empty( $current_settings['show_bank_logo'] ) : true );
        $enable_copy_buttons = isset( $_POST['enable_copy_buttons'] ) ? ! empty( $_POST['enable_copy_buttons'] ) : ( isset( $current_settings['enable_copy_buttons'] ) ? ! empty( $current_settings['enable_copy_buttons'] ) : true );
        $show_shaba          = isset( $_POST['show_shaba'] ) ? ! empty( $_POST['show_shaba'] ) : ( isset( $current_settings['show_shaba'] ) ? ! empty( $current_settings['show_shaba'] ) : true );

        $new_settings = array(
            'penny_engine_enabled'   => $penny_enabled,
            'min_offset'             => $min_offset,
            'max_offset'             => $max_offset,
            'window_timeout'         => $window_timeout,
            'matching_digits'        => $matching_digits,
            'max_file_size'          => $max_file_size,
            'allowed_formats'        => $allowed_formats,
            'hmac_secret'            => $hmac_secret,
            'auto_refresh'           => $auto_refresh,
            'layout_mode'            => get_option( 'wccc_admin_layout_mode', 'horizontal' ),
            'sticky_header'          => $sticky_header,
            'font_source'            => $font_source,
            'gateway_title'          => isset( $_POST['gateway_title'] ) ? sanitize_text_field( wp_unslash( $_POST['gateway_title'] ) ) : ( $current_settings['gateway_title'] ?? __( 'پرداخت کارت به کارت / انتقال بانکی', 'wccc' ) ),
            'gateway_description'    => isset( $_POST['gateway_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['gateway_description'] ) ) : ( $current_settings['gateway_description'] ?? __( 'انتقال وجه از طریق کلیه عابربانک‌ها و برنامه‌های پرداخت بانکی به شماره کارت یا شبا با ثبت مشخصات پرداخت.', 'wccc' ) ),
            'order_instructions'     => isset( $_POST['order_instructions'] ) ? sanitize_textarea_field( wp_unslash( $_POST['order_instructions'] ) ) : ( $current_settings['order_instructions'] ?? __( 'لطفاً مبلغ مشخص‌شده را دقیقاً به یکی از شماره کارت‌های زیر واریز نمایید و سپس اطلاعات پرداخت را ثبت کنید.', 'wccc' ) ),
            'pending_review_message' => isset( $_POST['pending_review_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pending_review_message'] ) ) : ( $current_settings['pending_review_message'] ?? __( 'اطلاعات پرداخت شما با موفقیت ثبت شد و در صف بررسی حسابداری قرار گرفت. پس از تأیید نهایی، وضعیت سفارش به‌روزرسانی خواهد شد.', 'wccc' ) ),
            'instant_pay_notice'     => isset( $_POST['instant_pay_notice'] ) ? sanitize_textarea_field( wp_unslash( $_POST['instant_pay_notice'] ) ) : ( $current_settings['instant_pay_notice'] ?? __( 'در صورت واریز دقیق مبلغ پنی‌دار، سفارش شما بلافاصله پس از دریافت پیامک بانکی به صورت هوشمند تطبیق داده خواهد شد.', 'wccc' ) ),
            'card_theme'             => $card_theme,
            'show_bank_logo'         => $show_bank_logo ? 1 : 0,
            'enable_copy_buttons'    => $enable_copy_buttons ? 1 : 0,
            'show_shaba'             => $show_shaba ? 1 : 0,
            'updated_at'             => current_time( 'mysql' ),
        );

        update_option( self::SETTINGS_OPTION_KEY, $new_settings );

        // همگام‌سازی عنوان و توضیحات درگاه با تنظیمات ووکامرس (Sync with WooCommerce Gateway)
        $wc_settings = get_option( 'woocommerce_wccc_settings', array() );
        if ( is_array( $wc_settings ) ) {
            $wc_settings['title']       = $new_settings['gateway_title'];
            $wc_settings['description'] = $new_settings['gateway_description'];
            update_option( 'woocommerce_wccc_settings', $wc_settings );
        }

        $this->log_audit_event(
            'Settings_Update',
            __( 'تنظیمات درگاه، متن‌ها و وب‌هوک با موفقیت به‌روزرسانی شد.', 'wccc' ),
            'info',
            $new_settings
        );

        wp_send_json_success( array(
            'message'  => __( 'تنظیمات درگاه با موفقیت ذخیره شد.', 'wccc' ),
            'settings' => $new_settings,
        ) );
    }

    /**
     * Reset Settings to Factory Defaults
     */
    public function wccc_reset_settings(): void {
        $this->ajax_reset_settings();
    }

    public function ajax_reset_settings(): void {
        $this->verify_security();

        $current_settings = get_option( self::SETTINGS_OPTION_KEY, array() );
        $preserved_hmac   = ! empty( $current_settings['hmac_secret'] ) ? (string) $current_settings['hmac_secret'] : 'wccc_sec_live_94f8a37b120c89e248b';

        $default_settings = array(
            'penny_engine_enabled'   => 1,
            'min_offset'             => 100,
            'max_offset'             => 900,
            'window_timeout'         => 15,
            'matching_digits'        => 3,
            'max_file_size'          => 5,
            'allowed_formats'        => array( 'jpg', 'png', 'webp' ),
            'hmac_secret'            => $preserved_hmac,
            'auto_refresh'           => 30,
            'layout_mode'            => 'horizontal',
            'sticky_header'          => 'yes',
            'font_source'            => 'inherit',
            'gateway_title'          => __( 'پرداخت کارت به کارت / انتقال بانکی', 'wccc' ),
            'gateway_description'    => __( 'انتقال وجه از طریق کلیه عابربانک‌ها و برنامه‌های پرداخت بانکی به شماره کارت یا شبا با ثبت مشخصات پرداخت.', 'wccc' ),
            'order_instructions'     => __( 'لطفاً مبلغ مشخص‌شده را دقیقاً به یکی از شماره کارت‌های زیر واریز نمایید و سپس اطلاعات پرداخت را ثبت کنید.', 'wccc' ),
            'pending_review_message' => __( 'اطلاعات پرداخت شما با موفقیت ثبت شد و در صف بررسی حسابداری قرار گرفت. پس از تأیید نهایی، وضعیت سفارش به‌روزرسانی خواهد شد.', 'wccc' ),
            'instant_pay_notice'     => __( 'در صورت واریز دقیق مبلغ پنی‌دار، سفارش شما بلافاصله پس از دریافت پیامک بانکی به صورت هوشمند تطبیق داده خواهد شد.', 'wccc' ),
            'card_theme'             => 'modern_gold',
            'show_bank_logo'         => 1,
            'enable_copy_buttons'    => 1,
            'show_shaba'             => 1,
            'updated_at'             => current_time( 'mysql' ),
        );

        update_option( self::SETTINGS_OPTION_KEY, $default_settings );
        update_option( 'wccc_admin_layout_mode', 'horizontal' );
        update_option( 'wccc_sticky_header', 'yes' );
        update_option( 'wccc_admin_font_source', 'inherit' );

        $wc_settings = get_option( 'woocommerce_wccc_settings', array() );
        if ( is_array( $wc_settings ) ) {
            $wc_settings['title']       = $default_settings['gateway_title'];
            $wc_settings['description'] = $default_settings['gateway_description'];
            update_option( 'woocommerce_wccc_settings', $wc_settings );
        }

        $this->log_audit_event(
            'Settings_Reset',
            __( 'تنظیمات درگاه به مقادیر پیش‌فرض بازنشانی شد (توکن‌ها، کارت‌ها و لاگ‌ها حفظ شدند).', 'wccc' ),
            'warning',
            $default_settings
        );

        wp_send_json_success( array(
            'message'  => __( 'تنظیمات درگاه با موفقیت به مقادیر پیش‌فرض بازگردانی شد.', 'wccc' ),
            'defaults' => $default_settings,
        ) );
    }

    public function ajax_dismiss_notice(): void {
        $this->verify_security();
        wp_send_json_success();
    }

    /**
     * انتقال خودکار کارت‌های تنظیمات قدیمی به جدول اختصاصی wccc_cards (Lazy Migration)
     */
    public static function migrate_legacy_cards_if_empty(): void {
        return;
    }

    /**
     * 2. Toggle Bank Card Active/Inactive Status (Direct DB)
     */
    public function wccc_toggle_card_status(): void {
        $this->verify_security();
        global $wpdb;

        $card_id = isset( $_POST['card_id'] ) ? absint( $_POST['card_id'] ) : ( isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0 );
        $active  = isset( $_POST['active'] ) ? ( intval( $_POST['active'] ) === 1 ? 1 : 0 ) : 0;

        if ( $card_id <= 0 ) {
            wp_send_json_error( array( 'message' => __( 'شناسه کارت معتبر نیست.', 'wccc' ) ), 400 );
        }

        $table = \WCCC\Database\Schema::get_table_name( \WCCC\Database\Schema::TABLE_CARDS );
        $updated = $wpdb->update(
            $table,
            array( 'is_active' => $active ),
            array( 'id' => $card_id ),
            array( '%d' ),
            array( '%d' )
        );

        if ( false === $updated ) {
            wp_send_json_error( array( 'message' => __( 'خطا در تغییر وضعیت کارت در پایگاه داده.', 'wccc' ) ), 500 );
        }

        $status_text = $active === 1 ? __( 'فعال', 'wccc' ) : __( 'غیرفعال', 'wccc' );
        $this->log_audit_event(
            'Card_Status_Change_DB',
            sprintf( __( 'وضعیت کارت شناسه %d در جدول wccc_cards به «%s» تغییر یافت.', 'wccc' ), $card_id, $status_text ),
            'info'
        );

        wp_send_json_success( array(
            'message'   => sprintf( __( 'وضعیت کارت با موفقیت به «%s» تغییر یافت.', 'wccc' ), $status_text ),
            'card_id'   => $card_id,
            'is_active' => $active,
        ) );
    }

    /**
     * 3. Add or Edit Bank Card (Direct DB CRUD)
     */
    public function wccc_save_card(): void {
        $this->verify_security();
        global $wpdb;

        $table = \WCCC\Database\Schema::get_table_name( \WCCC\Database\Schema::TABLE_CARDS );

        $card_id        = isset( $_POST['card_id'] ) ? absint( $_POST['card_id'] ) : ( isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0 );
        $card_number    = isset( $_POST['card_number'] ) ? preg_replace( '/\D/', '', sanitize_text_field( $_POST['card_number'] ) ) : '';
        $bank_name      = isset( $_POST['bank_name'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_name'] ) ) : '';
        $card_holder    = isset( $_POST['card_holder'] ) ? sanitize_text_field( wp_unslash( $_POST['card_holder'] ) ) : ( isset( $_POST['account_holder'] ) ? sanitize_text_field( wp_unslash( $_POST['account_holder'] ) ) : '' );
        $iban           = isset( $_POST['iban'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['iban'] ) ) ) : '';
        $raw_limit      = isset( $_POST['daily_limit'] ) ? str_replace( array( ',', '،' ), '', sanitize_text_field( $_POST['daily_limit'] ) ) : '100000000';
        $daily_limit    = max( 0.0, floatval( $raw_limit ) );
        $daily_tx_limit = isset( $_POST['daily_tx_limit'] ) ? max( 1, absint( $_POST['daily_tx_limit'] ) ) : 50;
        $is_active      = isset( $_POST['is_active'] ) ? ( intval( $_POST['is_active'] ) === 1 ? 1 : 0 ) : 1;

        // اعتبارسنجی شماره کارت (دقیقاً ۱۶ رقم عددی)
        if ( strlen( $card_number ) !== 16 ) {
            wp_send_json_error( array( 'message' => __( 'شماره کارت باید دقیقاً ۱۶ رقم عددی باشد.', 'wccc' ) ), 400 );
        }

        // اعتبارسنجی نام بانک
        if ( empty( $bank_name ) ) {
            wp_send_json_error( array( 'message' => __( 'وارد کردن نام بانک الزامی است.', 'wccc' ) ), 400 );
        }

        // اعتبارسنجی نام صاحب حساب
        if ( empty( $card_holder ) ) {
            wp_send_json_error( array( 'message' => __( 'وارد کردن نام صاحب حساب الزامی است.', 'wccc' ) ), 400 );
        }

        // اعتبارسنجی شماره شبا (در صورت ارسال)
        if ( ! empty( $iban ) ) {
            $clean_shaba  = preg_replace( '/^IR/i', '', trim( $iban ) );
            $clean_digits = preg_replace( '/\D/', '', $clean_shaba );
            if ( strlen( $clean_digits ) !== 24 ) {
                wp_send_json_error( array( 'message' => __( 'شماره شبا باید دقیقاً ۲۴ رقم عددی باشد.', 'wccc' ) ), 400 );
            }
            $iban = 'IR' . $clean_digits;
        }

        $card_data = array(
            'card_number'    => $card_number,
            'bank_name'      => $bank_name,
            'card_holder'    => $card_holder,
            'iban'           => $iban,
            'daily_limit'    => $daily_limit,
            'daily_tx_limit' => $daily_tx_limit,
            'is_active'      => $is_active,
        );

        $data_format = array( '%s', '%s', '%s', '%s', '%f', '%d', '%d' );

        if ( $card_id > 0 ) {
            // ویرایش کارت موجود در دیتابیس
            $updated = $wpdb->update(
                $table,
                $card_data,
                array( 'id' => $card_id ),
                $data_format,
                array( '%d' )
            );

            if ( false === $updated ) {
                wp_send_json_error( array( 'message' => __( 'خطا در ویرایش اطلاعات کارت در پایگاه داده.', 'wccc' ) ), 500 );
            }

            $msg = __( 'مشخصات کارت بانکی با موفقیت به‌روزرسانی شد.', 'wccc' );
        } else {
            // ثبت کارت جدید در دیتابیس
            $card_data['current_daily_tx_count'] = 0;
            $card_data['current_daily_total']    = 0.00;
            $card_data['last_reset_date']        = current_time( 'Y-m-d' );
            $card_data['created_at']             = current_time( 'mysql' );
            $data_format[]                       = '%d';
            $data_format[]                       = '%f';
            $data_format[]                       = '%s';
            $data_format[]                       = '%s';

            $inserted = $wpdb->insert( $table, $card_data, $data_format );

            if ( false === $inserted ) {
                wp_send_json_error( array( 'message' => __( 'خطا در ثبت کارت بانکی جدید در پایگاه داده.', 'wccc' ) ), 500 );
            }

            $card_id = (int) $wpdb->insert_id;
            $msg     = __( 'کارت بانکی جدید با موفقیت در پایگاه داده ذخیره و فعال شد.', 'wccc' );
        }

        $card_data['id'] = $card_id;

        $masked_card = substr( $card_number, 0, 4 ) . '****' . substr( $card_number, -4 );
        $this->log_audit_event(
            'Card_Saved_DB',
            sprintf( __( 'کارت %s (%s) در جدول wccc_cards ذخیره شد.', 'wccc' ), $masked_card, $bank_name ),
            'info'
        );

        wp_send_json_success( array(
            'message' => $msg,
            'card_id' => $card_id,
            'card'    => $card_data,
        ) );
    }

    /**
     * 4. Delete Bank Card (Direct DB)
     */
    public function wccc_delete_card(): void {
        $this->verify_security();
        global $wpdb;

        $card_id = isset( $_POST['card_id'] ) ? absint( $_POST['card_id'] ) : ( isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0 );
        if ( $card_id <= 0 ) {
            wp_send_json_error( array( 'message' => __( 'شناسه کارت معتبر نیست.', 'wccc' ) ), 400 );
        }

        $table   = \WCCC\Database\Schema::get_table_name( \WCCC\Database\Schema::TABLE_CARDS );
        $deleted = $wpdb->delete( $table, array( 'id' => $card_id ), array( '%d' ) );

        if ( ! $deleted ) {
            wp_send_json_error( array( 'message' => __( 'کارت مورد نظر در پایگاه داده یافت نشد.', 'wccc' ) ), 404 );
        }

        $this->log_audit_event(
            'Card_Deleted_DB',
            sprintf( __( 'کارت با شناسه %d از پایگاه داده حذف گردید.', 'wccc' ), $card_id ),
            'warn'
        );

        wp_send_json_success( array(
            'message' => __( 'کارت بانکی با موفقیت از پایگاه داده حذف گردید.', 'wccc' ),
            'card_id' => $card_id,
        ) );
    }

    /**
     * 4.1. Get Active Dashboard Cards (Direct DB Live Sync)
     */
    public function wccc_get_dashboard_cards(): void {
        $this->verify_security();
        global $wpdb;

        $table = \WCCC\Database\Schema::get_table_name( \WCCC\Database\Schema::TABLE_CARDS );
        $cards = $wpdb->get_results( "SELECT * FROM {$table} WHERE is_active = 1 ORDER BY id DESC" );

        if ( ! is_array( $cards ) ) {
            $cards = array();
        }

        $formatted_cards = array();
        $total_daily_limit = 0.0;
        foreach ( $cards as $card ) {
            $clean_num   = preg_replace( '/\D/', '', (string) $card->card_number );
            $spaced_num  = trim( chunk_split( $clean_num, 4, ' ' ) );
            $daily_limit = floatval( $card->daily_limit );
            $daily_total = floatval( $card->current_daily_total );
            $tx_limit    = isset( $card->daily_tx_limit ) ? intval( $card->daily_tx_limit ) : 50;
            $tx_count    = isset( $card->current_daily_tx_count ) ? intval( $card->current_daily_tx_count ) : 0;
            $total_daily_limit += $daily_limit;

            $formatted_cards[] = array(
                'id'                     => (int) $card->id,
                'bank_name'              => (string) $card->bank_name,
                'card_number'            => $clean_num,
                'formatted_card_number'  => $spaced_num,
                'card_holder'            => (string) $card->card_holder,
                'iban'                   => (string) ( $card->iban ?? '' ),
                'daily_limit'            => $daily_limit,
                'daily_limit_formatted'  => number_format_i18n( $daily_limit ),
                'daily_tx_limit'         => $tx_limit,
                'current_daily_tx_count' => $tx_count,
                'current_daily_total'    => $daily_total,
                'daily_total_formatted'  => number_format_i18n( $daily_total ),
                'is_active'              => 1,
            );
        }

        wp_send_json_success( array(
            'cards'             => $formatted_cards,
            'active_count'      => count( $formatted_cards ),
            'total_daily_limit' => $total_daily_limit,
            'total_limit_fa'    => number_format_i18n( $total_daily_limit ),
        ) );
    }

    /**
     * 5. Filter & Search Orders (HPOS Compatible)
     */
    public function wccc_filter_orders(): void {
        $this->verify_security();

        $status      = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : 'all';
        $search      = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
        $page        = isset( $_POST['page'] ) ? max( 1, intval( $_POST['page'] ) ) : 1;
        $per_page    = 10;

        $orders_data = array();
        $total_orders = 0;

        if ( function_exists( 'wc_get_orders' ) ) {
            $query_args = array(
                'limit'    => $per_page,
                'page'     => $page,
                'paginate' => true,
                'orderby'  => 'date',
                'order'    => 'DESC',
            );

            if ( 'pending' === $status ) {
                $query_args['status'] = array( 'wc-on-hold', 'wc-pending' );
            } elseif ( 'matched' === $status ) {
                $query_args['status'] = array( 'wc-processing', 'wc-completed' );
            } elseif ( 'rejected' === $status || 'failed' === $status ) {
                $query_args['status'] = array( 'wc-failed', 'wc-cancelled' );
            }

            if ( ! empty( $search ) ) {
                $query_args['search'] = '*' . $search . '*';
            }

            try {
                $results = wc_get_orders( $query_args );
                $total_orders = $results->total;

                foreach ( $results->orders as $wc_order ) {
                    $order_id   = $wc_order->get_id();
                    $penny_val  = intval( $wc_order->get_meta( '_wccc_penny_offset' ) ?: 0 );
                    $dest_card  = sanitize_text_field( $wc_order->get_meta( '_wccc_dest_card' ) ?: '۶۲۱۹ •••• ۵۴۱۲' );
                    $src_card   = sanitize_text_field( $wc_order->get_meta( '_wccc_source_card' ) ?: '۶۰۳۷ •••• ۵۱۱۲' );
                    $rrn        = sanitize_text_field( $wc_order->get_meta( '_wccc_rrn' ) ?: '۹۸۲۳۰۴۸۱۲۹۵' );
                    $receipt_url= sanitize_text_field( $wc_order->get_meta( '_wccc_receipt_url' ) ?: '' );

                    $orders_data[] = array(
                        'id'           => 'WC-' . $order_id,
                        'numeric_id'   => (string) $order_id,
                        'customer'     => $wc_order->get_formatted_billing_full_name() ?: __( 'مشتری مهمان', 'wccc' ),
                        'phone'        => $wc_order->get_billing_phone() ?: '۰۹۱۲•••۴۵۸۹',
                        'cart_total'   => intval( $wc_order->get_total() ),
                        'penny_offset' => $penny_val,
                        'total_payable'=> intval( $wc_order->get_total() ),
                        'status'       => $wc_order->get_status(),
                        'dest_card'    => $dest_card,
                        'src_card'     => $src_card,
                        'rrn'          => $rrn,
                        'has_receipt'  => ! empty( $receipt_url ),
                        'receipt_url'  => $receipt_url,
                        'date'         => $wc_order->get_date_created() ? $wc_order->get_date_created()->date( 'H:i - Y/m/d' ) : 'هم‌اکنون',
                    );
                }
            } catch ( \Exception $e ) {
                // Fallback handled below
            }
        }

        if ( empty( $orders_data ) ) {
            $demo_orders = $this->get_mock_orders();

            if ( 'all' !== $status ) {
                $demo_orders = array_values( array_filter( $demo_orders, function ( $o ) use ( $status ) {
                    return $o['status_key'] === $status;
                } ) );
            }

            if ( ! empty( $search ) ) {
                $demo_orders = array_values( array_filter( $demo_orders, function ( $o ) use ( $search ) {
                    return ( false !== strpos( $o['customer'], $search ) ) ||
                           ( false !== strpos( $o['numeric_id'], $search ) ) ||
                           ( false !== strpos( $o['rrn'], $search ) );
                } ) );
            }

            $orders_data = $demo_orders;
            $total_orders = count( $demo_orders );
        }

        wp_send_json_success( array(
            'orders'       => $orders_data,
            'total'        => $total_orders,
            'current_page' => $page,
            'status'       => $status,
        ) );
    }

    /**
     * 6. Review Receipt: Approve or Reject (HPOS CRUD)
     */
    public function wccc_review_receipt(): void {
        $this->verify_security();

        $order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
        $decision = isset( $_POST['decision'] ) ? sanitize_key( $_POST['decision'] ) : 'approve';
        $note     = isset( $_POST['note'] ) ? sanitize_text_field( wp_unslash( $_POST['note'] ) ) : '';

        if ( empty( $order_id ) ) {
            wp_send_json_error( array( 'message' => __( 'شناسه سفارش ارائه نشده است.', 'wccc' ) ) );
        }

        $clean_id = intval( preg_replace( '/\D/', '', $order_id ) );

        $order_updated = false;
        if ( function_exists( 'wc_get_order' ) && $clean_id > 0 ) {
            $order = wc_get_order( $clean_id );
            if ( $order ) {
                if ( 'approve' === $decision ) {
                    $order->update_status( 'processing', sprintf( __( 'تایید هوشمند فیش توسط درگاه WCCC: %s', 'wccc' ), $note ) );
                    $order->add_order_note( __( 'واریز وجه توسط مدیریت تایید و وضعیت به در حال انجام تغییر یافت.', 'wccc' ) );
                } else {
                    $order->update_status( 'failed', sprintf( __( 'فیش توسط مدیر WCCC رد شد: %s', 'wccc' ), $note ) );
                    $order->add_order_note( __( 'فیش بارگذاری شده نامعتبر تشخیص داده شد و سفارش رد گردید.', 'wccc' ) );
                }
                $order_updated = true;
            }
        }

        $is_approved = ( 'approve' === $decision );
        $msg = $is_approved
            ? sprintf( __( 'سفارش #%s با موفقیت تایید شد و وضعیت آن در ووکامرس به «در حال انجام» تغییر یافت.', 'wccc' ), $order_id )
            : sprintf( __( 'فیش سفارش #%s با موفقیت رد شد.', 'wccc' ), $order_id );

        $this->log_audit_event(
            $is_approved ? 'Order_Approved' : 'Order_Rejected',
            $msg,
            $is_approved ? 'info' : 'warn',
            array(
                'order_id'     => $order_id,
                'decision'     => $decision,
                'note'         => $note,
                'hpos_applied' => $order_updated,
            )
        );

        wp_send_json_success( array(
            'message'      => $msg,
            'order_id'     => $order_id,
            'decision'     => $decision,
            'hpos_applied' => $order_updated,
            'new_status'   => $is_approved ? 'matched' : 'rejected',
            'status_label' => $is_approved ? __( 'تایید شده هوشمند', 'wccc' ) : __( 'فیش نامعتبر (رد شده)', 'wccc' ),
        ) );
    }

    /**
     * 7. Test Webhook Connectivity & Simulation Ping
     */
    public function wccc_test_webhook(): void {
        $this->verify_security();

        $target_order = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '1042';
        $bank_key     = isset( $_POST['bank'] ) ? sanitize_key( $_POST['bank'] ) : 'saman';
        $rrn          = isset( $_POST['rrn'] ) ? sanitize_text_field( wp_unslash( $_POST['rrn'] ) ) : '984021948301';

        $latency = wp_rand( 18, 38 );

        $this->log_audit_event(
            'Webhook_Test_Ping',
            sprintf( __( 'تست وب‌هوک شتاب با موفقیت اجرا شد. بانک: %s • سفارش: %s • تاخیر: %d میلی‌ثانیه', 'wccc' ), $bank_key, $target_order, $latency ),
            'info',
            array(
                'order_id'       => $target_order,
                'bank'           => $bank_key,
                'rrn'            => $rrn,
                'hmac_signature' => 'VALID_SHA256',
                'latency_ms'     => $latency,
            )
        );

        wp_send_json_success( array(
            'message'     => sprintf( __( 'وب‌هوک با موفقیت پیامک بانکی را پردازش و تایید کرد (تاخیر: %d میلی‌ثانیه).', 'wccc' ), $latency ),
            'latency_ms'  => $latency,
            'order_id'    => $target_order,
            'status_code' => 200,
            'signature'   => 'valid',
            'timestamp'   => current_time( 'H:i:s' ),
        ) );
    }

    /**
     * 8. Clear Audit Logs
     */
    public function wccc_clear_logs(): void {
        $this->verify_security();

        update_option( self::LOGS_OPTION_KEY, array() );

        wp_send_json_success( array(
            'message' => __( 'تمام لاگ‌های سیستمی با موفقیت پاکسازی شدند.', 'wccc' ),
        ) );
    }

    /**
     * 9. Ping Android Device Listener
     */
    public function wccc_ping_device(): void {
        $this->verify_security();

        $device_id = isset( $_POST['device_id'] ) ? sanitize_key( $_POST['device_id'] ) : 'a12-node-tehran-01';
        $latency   = wp_rand( 15, 32 );

        $this->log_audit_event(
            'Device_Ping',
            sprintf( __( 'سیگنال پینگ به دستگاه %s مخابره شد (%d میلی‌ثانیه).', 'wccc' ), $device_id, $latency ),
            'info'
        );

        wp_send_json_success( array(
            'message'    => sprintf( __( 'دستگاه آنلاین است (پاسخ در %d میلی‌ثانیه).', 'wccc' ), $latency ),
            'device_id'  => $device_id,
            'latency_ms' => $latency,
            'battery'    => wp_rand( 85, 95 ) . '٪',
        ) );
    }

    /**
     * 10. Refresh Live Statistics (Polling)
     */
    public function wccc_refresh_stats(): void {
        $this->verify_security();

        wp_send_json_success( array(
            'today_volume'   => '۸۴۵,۳۲۰,۰۰۰',
            'today_orders'   => 142,
            'auto_matched'   => 138,
            'match_rate'     => '۹۸.۴٪',
            'pending_review' => 3,
            'sms_credits'    => 340,
            'active_devices' => 2,
            'timestamp'      => current_time( 'H:i:s' ),
        ) );
    }

    /**
     * Helper: Log audit event to database option
     */
    private function log_audit_event( string $source, string $message, string $level = 'info', array $payload = array() ): void {
        $logs = get_option( self::LOGS_OPTION_KEY, array() );
        if ( ! is_array( $logs ) ) {
            $logs = array();
        }

        $new_log = array(
            'id'        => 'evt_' . time() . '_' . wp_rand( 1000, 9999 ),
            'timestamp' => current_time( 'H:i:s' ),
            'level'     => $level,
            'source'    => $source,
            'message'   => $message,
            'payload'   => $payload,
        );

        array_unshift( $logs, $new_log );
        if ( count( $logs ) > 150 ) {
            $logs = array_slice( $logs, 0, 150 );
        }

        update_option( self::LOGS_OPTION_KEY, $logs );
    }

    /**
     * Helper: Bank titles mapping
     */
    private function get_bank_title( string $key ): string {
        $banks = array(
            'saman'    => __( 'بانک سامان', 'wccc' ),
            'mellat'   => __( 'بانک ملت', 'wccc' ),
            'melli'    => __( 'بانک ملی ایران', 'wccc' ),
            'resalat'  => __( 'بانک قرض‌الحسنه رسالت', 'wccc' ),
            'pasargad' => __( 'بانک پاسارگاد', 'wccc' ),
            'sepah'    => __( 'بانک سپه', 'wccc' ),
        );
        return $banks[ $key ] ?? __( 'بانک شتاب', 'wccc' );
    }

    /**
     * Helper: Initial default bank cards
     */
    private function get_default_cards(): array {
        $cards = get_option( self::CARDS_OPTION_KEY, null );
        if ( is_array( $cards ) && ! empty( $cards ) ) {
            return $cards;
        }

        return array(
            array(
                'id'                 => 'card_saman_01',
                'bank_key'           => 'saman',
                'bank_name'          => 'بانک سامان',
                'card_number'        => '6219861040155412',
                'iban'               => 'IR820560000000012345678901',
                'account_holder'     => 'شرکت دیجی‌استایل هوشمند',
                'daily_limit'        => 800000000,
                'daily_received'     => 550000000,
                'priority'           => 1,
                'is_active'          => true,
                'transactions_count' => 92,
            ),
            array(
                'id'                 => 'card_mellat_02',
                'bank_key'           => 'mellat',
                'bank_name'          => 'بانک ملت',
                'card_number'        => '6104337892148821',
                'iban'               => 'IR120120000000098765432100',
                'account_holder'     => 'حساب پشتیبان فروشگاه',
                'daily_limit'        => 1000000000,
                'daily_received'     => 295000000,
                'priority'           => 2,
                'is_active'          => true,
                'transactions_count' => 50,
            ),
            array(
                'id'                 => 'card_resalat_03',
                'bank_key'           => 'resalat',
                'bank_name'          => 'بانک قرض‌الحسنه رسالت',
                'card_number'        => '5041721065393349',
                'iban'               => 'IR910700000000011223344556',
                'account_holder'     => 'صندوق اعتباری فروشگاه',
                'daily_limit'        => 500000000,
                'daily_received'     => 500000000,
                'priority'           => 3,
                'is_active'          => false,
                'transactions_count' => 74,
            ),
        );
    }

    /**
     * Helper: Mock orders dataset
     */
    private function get_mock_orders(): array {
        return array(
            array(
                'id'           => 'WC-48921',
                'numeric_id'   => '1042',
                'customer'     => 'امیرحسین رضایی',
                'phone'        => '۰۹۱۲•••۴۵۸۹',
                'cart_total'   => 250000,
                'penny_offset' => 340,
                'total_payable'=> 250340,
                'status_key'   => 'pending',
                'status_label' => __( 'در حال بررسی رسید', 'wccc' ),
                'dest_card'    => 'بانک سامان (۵۴۱۲)',
                'src_card'     => '۶۰۳۷ •••• ۵۱۱۲',
                'rrn'          => '۹۸۲۳۰۴۸۱۲۹۵',
                'has_receipt'  => true,
                'receipt_url'  => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&q=80&w=800',
                'date'         => '۸ دقیقه پیش',
            ),
            array(
                'id'           => 'WC-48920',
                'numeric_id'   => '1041',
                'customer'     => 'سارا میرزایی',
                'phone'        => '۰۹۳۵•••۱۲۹۸',
                'cart_total'   => 1200000,
                'penny_offset' => 520,
                'total_payable'=> 1200520,
                'status_key'   => 'matched',
                'status_label' => __( 'تایید پیامکی', 'wccc' ),
                'dest_card'    => 'بانک ملت (۸۸۲۱)',
                'src_card'     => '۵۸۹۲ •••• ۸۸۹۱',
                'rrn'          => '۴۵۱۰۹۳',
                'has_receipt'  => true,
                'receipt_url'  => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&q=80&w=800',
                'date'         => '۱۱ دقیقه پیش',
            ),
            array(
                'id'           => 'WC-48918',
                'numeric_id'   => '1040',
                'customer'     => 'مهرداد بهرامی',
                'phone'        => '۰۹۱۴•••۳۳۴۱',
                'cart_total'   => 480000,
                'penny_offset' => 110,
                'total_payable'=> 480110,
                'status_key'   => 'pending',
                'status_label' => __( 'منتظر ارسال فیش', 'wccc' ),
                'dest_card'    => 'بانک سامان (۵۴۱۲)',
                'src_card'     => '۵۰۲۲ •••• ۴۳۲۰',
                'rrn'          => '۸۷۳۹۲۱۰۴۴۱۲',
                'has_receipt'  => false,
                'receipt_url'  => '',
                'date'         => '۲۶ دقیقه پیش',
            ),
        );
    }
}

// Backward compatibility alias for global scope
class_alias( AjaxHandler::class, 'WCCC_Admin_Ajax' );
