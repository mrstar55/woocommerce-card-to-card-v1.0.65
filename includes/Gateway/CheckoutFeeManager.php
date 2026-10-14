<?php
/**
 * مدیر کارمزد هوشمند پنی در تسویه حساب (Checkout Fee Manager)
 *
 * @package   WCCC
 * @author    تیم فنی WCCC
 * @license   GPL-2.0-or-later
 */

declare(strict_types=1);

namespace WCCC\Gateway;

use WCCC\Engine\PennyEngine;
use WC_Cart;

class CheckoutFeeManager {

    /**
     * ثبت هوک‌های مورد نیاز در ووکامرس
     */
    public static function init(): void {
        add_action( 'woocommerce_cart_calculate_fees', array( self::class, 'calculate_penny_fee' ), 20, 1 );
        add_action( 'wp_ajax_wccc_set_checkout_mode', array( self::class, 'ajax_set_checkout_mode' ) );
        add_action( 'wp_ajax_nopriv_wccc_set_checkout_mode', array( self::class, 'ajax_set_checkout_mode' ) );
    }

    /**
     * محاسبه و افزودن کارمزد پنی به سبد خرید در زمان انتخاب درگاه و حالت آنی
     *
     * @param WC_Cart $cart شیء سبد خرید ووکامرس
     */
    public static function calculate_penny_fee( WC_Cart $cart ): void {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        if ( ! isset( WC()->session ) ) {
            return;
        }

        // بررسی درگاه انتخابی کاربر در سشن یا پارامترهای ارسالی ایجکس تسویه
        $chosen_payment_method = WC()->session->get( 'chosen_payment_method' );
        if ( ! empty( $_POST['payment_method'] ) ) {
            $chosen_payment_method = sanitize_text_field( wp_unslash( $_POST['payment_method'] ) );
        } elseif ( ! empty( $_POST['post_data'] ) ) {
            parse_str( (string) $_POST['post_data'], $post_data );
            if ( ! empty( $post_data['payment_method'] ) ) {
                $chosen_payment_method = sanitize_text_field( (string) $post_data['payment_method'] );
            }
            if ( ! empty( $post_data['wccc_checkout_mode'] ) ) {
                $post_mode = sanitize_text_field( (string) $post_data['wccc_checkout_mode'] );
                if ( in_array( $post_mode, array( 'instant_auto', 'manual_receipt' ), true ) ) {
                    WC()->session->set( 'wccc_payment_mode', $post_mode );
                }
            }
        }

        if ( 'wccc_card_to_card' !== $chosen_payment_method ) {
            if ( isset( WC()->session ) ) {
                WC()->session->__unset( 'wccc_penny_offset' );
                WC()->session->__unset( 'wccc_payment_mode' );
            }
            return;
        }

        // بررسی فعال بودن موتور پنی در تنظیمات درگاه
        $gateway_settings = get_option( 'woocommerce_wccc_card_to_card_settings', array() );
        $penny_enabled    = isset( $gateway_settings['penny_enabled'] ) && 'yes' === $gateway_settings['penny_enabled'];
        if ( ! $penny_enabled ) {
            return;
        }

        // بررسی حالت انتخاب شده در صفحه تسویه حساب (پیش‌فرض: instant_auto)
        $mode = (string) WC()->session->get( 'wccc_payment_mode', 'instant_auto' );
        if ( 'instant_auto' !== $mode ) {
            // در حالت واریز عادی با فیش، کارمزد پنی سشن پاک شده و هزینه‌ای اضافه نمی‌شود
            if ( isset( WC()->session ) ) {
                WC()->session->__unset( 'wccc_penny_offset' );
            }
            return;
        }

        // استخراج یا تولید آفست پنی برای سشن جاری
        $penny_offset = self::get_or_create_penny_offset( $cart, $gateway_settings );

        if ( $penny_offset > 0 ) {
            $fee_title = __( 'شناسه پرداخت هوشمند (پنی)', 'wccc' );
            $cart->add_fee( $fee_title, $penny_offset, false );
        }
    }

    /**
     * دریافت یا تخصیص امن مقدار پنی با جلوگیری از تداخل مبالغ فعال
     *
     * @param WC_Cart $cart
     * @param array   $gateway_settings
     * @return float
     */
    public static function get_or_create_penny_offset( WC_Cart $cart, array $gateway_settings ): float {
        $raw_min   = isset( $gateway_settings['penny_min'] ) ? (int) $gateway_settings['penny_min'] : PennyEngine::DEFAULT_MIN_PENNY;
        $raw_max   = isset( $gateway_settings['penny_max'] ) ? (int) $gateway_settings['penny_max'] : PennyEngine::DEFAULT_MAX_PENNY;
        $min_penny = max( 100, $raw_min );
        $max_penny = max( $min_penny + 10, $raw_max );

        $cached_offset = (int) WC()->session->get( 'wccc_penny_offset', 0 );
        if ( $cached_offset >= $min_penny && $cached_offset <= $max_penny ) {
            return (float) $cached_offset;
        }

        // تخصیص آفست تصادفی منحصر به فرد با بررسی تداخل مبالغ در صف انتظار
        $reserved_totals = PennyEngine::get_active_reserved_amounts( 0 );
        $base_amount     = (float) $cart->get_cart_contents_total() + (float) $cart->get_shipping_total();

        $chosen_offset = 0;
        $allocated     = false;

        for ( $attempt = 0; $attempt < PennyEngine::MAX_COLLISION_RETRIES; $attempt++ ) {
            $candidate_offset = random_int( $min_penny, $max_penny );
            $candidate_total  = $base_amount + (float) $candidate_offset;

            if ( ! in_array( $candidate_total, $reserved_totals, true ) ) {
                $chosen_offset = $candidate_offset;
                $allocated     = true;
                break;
            }
        }

        if ( ! $allocated ) {
            $chosen_offset = $min_penny;
        }

        WC()->session->set( 'wccc_penny_offset', $chosen_offset );

        return (float) $chosen_offset;
    }

    /**
     * هندلر ایجکس برای تغییر حالت پرداخت و ثبت در سشن ووکامرس
     */
    public static function ajax_set_checkout_mode(): void {
        check_ajax_referer( 'wccc_checkout_nonce', 'security' );

        $mode = isset( $_POST['mode'] ) ? sanitize_key( $_POST['mode'] ) : 'instant_auto';
        if ( ! in_array( $mode, array( 'instant_auto', 'manual_receipt' ), true ) ) {
            $mode = 'instant_auto';
        }

        if ( isset( WC()->session ) ) {
            WC()->session->set( 'wccc_payment_mode', $mode );
        }

        wp_send_json_success( array(
            'mode'    => $mode,
            'message' => __( 'حالت پرداخت با موفقیت در نشست کاربر ذخیره شد.', 'wccc' ),
        ) );
    }
}
