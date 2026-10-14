<?php
/**
 * WCCC Order Status Transition Manager
 *
 * @package WCCC\Orders
 */

declare(strict_types=1);

namespace WCCC\Orders;

use WC_Order;

defined( 'ABSPATH' ) || exit;

class StatusTransitionManager {

    /**
     * راه‌اندازی و ثبت اکشن‌های AJAX برای تأیید و رد دستی در مدیریت
     */
    public static function init(): void {
        add_action( 'wp_ajax_wccc_quick_approve_order', array( __CLASS__, 'handle_ajax_approve' ) );
        add_action( 'wp_ajax_wccc_quick_reject_order', array( __CLASS__, 'handle_ajax_reject' ) );
    }

    /**
     * دریافت لیست تمام وضعیت‌های معتبر سفارش در ووکامرس جهت استفاده در تنظیمات (بدون پیشوند wc-)
     *
     * @return array<string, string>
     */
    public static function get_order_status_options(): array {
        $statuses = function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array();
        $options  = array();
        foreach ( $statuses as $status_key => $status_label ) {
            $clean_key             = 'wc-' === substr( $status_key, 0, 3 ) ? substr( $status_key, 3 ) : $status_key;
            $options[ $clean_key ] = $status_label;
        }
        return $options;
    }

    /**
     * انتقال خودکار سفارش پس از تطبیق موفق (مثلاً پیامک یا پنی انجین)
     * مدیر می‌تواند تعیین کند برای سفارش‌های فیزیکی/ترکیبی به چه وضعیتی برود و برای سفارش‌های صرفاً مجازی/دانلودی به چه وضعیتی
     *
     * @param WC_Order $order
     * @param string   $note
     * @return bool
     */
    public static function apply_auto_success( WC_Order $order, string $note = '' ): bool {
        $settings = get_option( 'woocommerce_wccc_card_to_card_settings', array() );

        // اگر needs_processing() برابر true باشد سفارش فیزیکی یا ترکیبی است، در غیر این صورت صرفاً مجازی/دانلودی است
        if ( method_exists( $order, 'needs_processing' ) && ! $order->needs_processing() ) {
            $target_status = ! empty( $settings['auto_success_virtual_status'] ) ? sanitize_key( (string) $settings['auto_success_virtual_status'] ) : 'completed';
        } else {
            $target_status = ! empty( $settings['auto_success_status'] ) ? sanitize_key( (string) $settings['auto_success_status'] ) : 'processing';
        }

        $order_note = ! empty( $note ) ? $note : __( 'تراکنش با موفقیت تطبیق داده شد و تأیید گردید.', 'wccc' );
        $order->update_status( $target_status, $order_note );
        return true;
    }

    /**
     * تأیید دستی فیش توسط مدیر
     * تفکیک وضعیت بر اساس فیزیکی/ترکیبی بودن یا صرفاً مجازی/دانلودی بودن سفارش
     *
     * @param WC_Order $order
     * @param string   $note
     * @return bool
     */
    public static function apply_manual_approval( WC_Order $order, string $note = '' ): bool {
        $settings = get_option( 'woocommerce_wccc_card_to_card_settings', array() );

        // اگر needs_processing() برابر true باشد سفارش فیزیکی یا ترکیبی است، در غیر این صورت صرفاً مجازی/دانلودی است
        if ( method_exists( $order, 'needs_processing' ) && ! $order->needs_processing() ) {
            $target_status = ! empty( $settings['manual_approved_virtual_status'] ) ? sanitize_key( (string) $settings['manual_approved_virtual_status'] ) : 'completed';
        } else {
            $target_status = ! empty( $settings['manual_approved_status'] ) ? sanitize_key( (string) $settings['manual_approved_status'] ) : 'processing';
        }

        $current_user = wp_get_current_user();
        $admin_name   = $current_user->exists() ? $current_user->display_name : __( 'مدیر سیستم', 'wccc' );

        $order_note = sprintf(
            /* translators: 1: admin name, 2: additional note */
            __( 'فیش واریزی توسط %1$s بررسی و تأیید شد. %2$s', 'wccc' ),
            $admin_name,
            $note ? '(' . $note . ')' : ''
        );

        $order->update_status( $target_status, trim( $order_note ) );
        return true;
    }

    /**
     * رد دستی فیش توسط مدیر
     *
     * @param WC_Order $order
     * @param string   $reason
     * @return bool
     */
    public static function apply_manual_rejection( WC_Order $order, string $reason = '' ): bool {
        $settings      = get_option( 'woocommerce_wccc_card_to_card_settings', array() );
        $target_status = ! empty( $settings['manual_rejected_status'] ) ? sanitize_key( (string) $settings['manual_rejected_status'] ) : 'cancelled';

        $current_user = wp_get_current_user();
        $admin_name   = $current_user->exists() ? $current_user->display_name : __( 'مدیر سیستم', 'wccc' );

        $order_note = sprintf(
            /* translators: 1: admin name, 2: reason */
            __( 'فیش واریزی توسط %1$s رد شد. علت رد: %2$s', 'wccc' ),
            $admin_name,
            $reason ? $reason : __( 'عدم تطابق یا نقص مدارک', 'wccc' )
        );

        $order->update_status( $target_status, $order_note );
        return true;
    }

    /**
     * هندلر ایجکس تأیید فیش از متاباکس
     */
    public static function handle_ajax_approve(): void {
        check_ajax_referer( 'wccc_order_action_nonce', 'security' );

        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        if ( ! $order_id ) {
            wp_send_json_error( array( 'message' => __( 'شناسه سفارش نامعتبر است.', 'wccc' ) ) );
        }

        if ( ! current_user_can( 'edit_shop_orders', $order_id ) && ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز است.', 'wccc' ) ) );
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_send_json_error( array( 'message' => __( 'سفارش یافت نشد.', 'wccc' ) ) );
        }

        $admin_note = isset( $_POST['note'] ) ? sanitize_text_field( wp_unslash( $_POST['note'] ) ) : '';
        self::apply_manual_approval( $order, $admin_note );

        wp_send_json_success( array(
            'message'      => __( 'سفارش با موفقیت تأیید شد و وضعیت ارتقا یافت.', 'wccc' ),
            'status_label' => wc_get_order_status_name( $order->get_status() ),
            'status_slug'  => $order->get_status(),
        ) );
    }

    /**
     * هندلر ایجکس رد فیش از متاباکس
     */
    public static function handle_ajax_reject(): void {
        check_ajax_referer( 'wccc_order_action_nonce', 'security' );

        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        if ( ! $order_id ) {
            wp_send_json_error( array( 'message' => __( 'شناسه سفارش نامعتبر است.', 'wccc' ) ) );
        }

        if ( ! current_user_can( 'edit_shop_orders', $order_id ) && ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز است.', 'wccc' ) ) );
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_send_json_error( array( 'message' => __( 'سفارش یافت نشد.', 'wccc' ) ) );
        }

        $reason = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
        self::apply_manual_rejection( $order, $reason );

        wp_send_json_success( array(
            'message'      => __( 'فیش واریزی رد شد و سفارش به وضعیت مشخص‌شده منتقل گردید.', 'wccc' ),
            'status_label' => wc_get_order_status_name( $order->get_status() ),
            'status_slug'  => $order->get_status(),
        ) );
    }
}
