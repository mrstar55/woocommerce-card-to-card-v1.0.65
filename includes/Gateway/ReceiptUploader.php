<?php
/**
 * کلاس مدیریت ایزوله آپلود رسید و فیش واریزی کارت به کارت
 * اتصال به جدول اختصاصی wccc_receipts و پشتیبانی از Multi-Receipt
 *
 * @package   WCCC\Gateway
 * @author    تیم فنی WCCC
 * @license   GPL-2.0-or-later
 */

declare(strict_types=1);

namespace WCCC\Gateway;

use WC_Order;
use WC_Order_Item_Fee;
use WP_Error;
use WCCC\Database\Schema;

class ReceiptUploader {

    public const DEFAULT_MAX_SIZE = 5242880; // 5 * 1024 * 1024 (5MB)

    public static function init(): void {
        add_action( 'wp_ajax_wccc_upload_receipt', array( self::class, 'handle_upload' ) );
        add_action( 'wp_ajax_nopriv_wccc_upload_receipt', array( self::class, 'handle_upload' ) );
        add_action( 'wp_ajax_wccc_submit_payment', array( self::class, 'handle_submit_payment' ) );
        add_action( 'wp_ajax_nopriv_wccc_submit_payment', array( self::class, 'handle_submit_payment' ) );
    }

    /**
     * افزودن یا بروزرسانی مبلغ پنی به عنوان یک Fee مجزا با عنوان «شناسه پرداخت هوشمند (پنی)» در فاکتور سفارش
     */
    public static function set_order_penny_fee( WC_Order $order, float $penny_amount ): void {
        $fee_title    = __( 'شناسه پرداخت هوشمند (پنی)', 'wccc' );
        $alt_title    = __( 'شناسه پرداخت هوشمند', 'wccc' );
        $existing_fee = null;

        foreach ( $order->get_fees() as $fee ) {
            if ( $fee->get_name() === $fee_title || $fee->get_name() === $alt_title ) {
                $existing_fee = $fee;
                break;
            }
        }

        if ( $penny_amount > 0 ) {
            if ( ! $existing_fee ) {
                $existing_fee = new WC_Order_Item_Fee();
                $existing_fee->set_name( $fee_title );
                $order->add_item( $existing_fee );
            }
            $existing_fee->set_name( $fee_title );
            $existing_fee->set_amount( $penny_amount );
            $existing_fee->set_total( $penny_amount );
            $existing_fee->set_tax_status( 'none' );
            $existing_fee->save();
        } else {
            if ( $existing_fee ) {
                $order->remove_item( $existing_fee->get_id() );
            }
        }

        $order->calculate_totals( false );
    }

    /**
     * حذف کامل Fee پنی از سفارش (برای حالت واریز عادی با فیش)
     */
    public static function remove_order_penny_fee( WC_Order $order ): void {
        $fee_title = __( 'شناسه پرداخت هوشمند (پنی)', 'wccc' );
        $alt_title = __( 'شناسه پرداخت هوشمند', 'wccc' );
        $removed   = false;

        foreach ( $order->get_fees() as $fee_id => $fee ) {
            if ( $fee->get_name() === $fee_title || $fee->get_name() === $alt_title ) {
                $order->remove_item( $fee_id );
                $removed = true;
            }
        }

        if ( $removed ) {
            $order->calculate_totals( false );
        }
    }

    /**
     * ثبت یا درج رکورد فیش در جدول اختصاصی wccc_receipts با مقابله با فیش‌های تکراری (Anti-fraud)
     *
     * @param int         $order_id
     * @param string      $receipt_url
     * @param string      $receipt_hash
     * @param string      $uploader_type
     * @param int|null    $user_id
     * @param string|null $buyer_card_last4
     * @param string|null $buyer_tracking_number
     * @return int|WP_Error شناسه ردیف رسید در جدول
     */
    public static function insert_receipt_record(
        int $order_id,
        string $receipt_url,
        string $receipt_hash,
        string $uploader_type = 'customer',
        ?int $user_id = null,
        ?string $buyer_card_last4 = null,
        ?string $buyer_tracking_number = null
    ) {
        global $wpdb;

        $table = Schema::get_table_name( Schema::TABLE_RECEIPTS );
        Schema::check_and_repair();

        // بررسی ضدجعل / عدم ارسال فیش تکراری (Anti-fraud Hash Check)
        $duplicate = $wpdb->get_row( $wpdb->prepare(
            "SELECT id, order_id FROM {$table} WHERE receipt_hash = %s LIMIT 1",
            $receipt_hash
        ) );

        if ( $duplicate && (int) $duplicate->order_id !== $order_id ) {
            return new WP_Error(
                'duplicate_receipt_hash',
                __( 'این فیش واریزی قبلاً در سامانه برای سفارش دیگری ثبت شده است. امکان ارسال مجدد وجود ندارد.', 'wccc' )
            );
        }

        $now_mysql = current_time( 'mysql' );
        $data = array(
            'order_id'              => $order_id,
            'receipt_url'           => $receipt_url,
            'receipt_hash'          => $receipt_hash,
            'uploader_type'         => in_array( $uploader_type, array( 'customer', 'admin' ), true ) ? $uploader_type : 'customer',
            'uploaded_by_user_id'   => $user_id ?: ( is_user_logged_in() ? get_current_user_id() : null ),
            'buyer_card_last4'      => $buyer_card_last4 ?: null,
            'buyer_tracking_number' => $buyer_tracking_number ?: null,
            'verification_status'   => 'pending',
            'created_at'            => $now_mysql,
        );

        $formats = array( '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s' );
        $inserted = $wpdb->insert( $table, $data, $formats );

        if ( false === $inserted ) {
            return new WP_Error( 'db_insert_error', __( 'خطا در ثبت اطلاعات فیش در دیتابیس.', 'wccc' ) );
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * دریافت تمام فیش‌های یک سفارش از جدول اختصاصی wccc_receipts همراه با پشتیبانی از فیش‌های قدیمی
     *
     * @param int $order_id
     * @return array
     */
    public static function get_order_receipts( int $order_id ): array {
        global $wpdb;

        $table    = Schema::get_table_name( Schema::TABLE_RECEIPTS );
        $receipts = array();

        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) === $table ) {
            $rows = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$table} WHERE order_id = %d ORDER BY id ASC",
                $order_id
            ), ARRAY_A );

            if ( ! empty( $rows ) ) {
                $receipts = $rows;
            }
        }

        // اگر در جدول رکوردی نبود، برای سازگاری با متادیتاهای قدیمی بررسی شود
        if ( empty( $receipts ) && function_exists( 'wc_get_order' ) ) {
            $order = wc_get_order( $order_id );
            if ( $order ) {
                $meta_receipts = (array) $order->get_meta( '_wccc_receipts' );
                if ( ! empty( $meta_receipts ) && is_array( $meta_receipts ) ) {
                    foreach ( $meta_receipts as $idx => $mr ) {
                        if ( ! is_array( $mr ) ) continue;
                        $receipts[] = array(
                            'id'                    => $idx + 1,
                            'order_id'              => $order_id,
                            'receipt_url'           => $mr['url'] ?? ( $mr['file_url'] ?? '' ),
                            'receipt_hash'          => $mr['file_hash'] ?? '',
                            'uploader_type'         => 'customer',
                            'uploaded_by_user_id'   => null,
                            'buyer_card_last4'      => (string) $order->get_meta( '_wccc_card_last4' ),
                            'buyer_tracking_number' => (string) $order->get_meta( '_wccc_tracking_code' ),
                            'verification_status'   => 'pending',
                            'verified_at'           => null,
                            'created_at'            => $mr['uploaded_at'] ?? current_time( 'mysql' ),
                        );
                    }
                } elseif ( $order->get_meta( '_wccc_receipt_url' ) ) {
                    $receipts[] = array(
                        'id'                    => 1,
                        'order_id'              => $order_id,
                        'receipt_url'           => (string) $order->get_meta( '_wccc_receipt_url' ),
                        'receipt_hash'          => (string) $order->get_meta( '_wccc_receipt_hash' ),
                        'uploader_type'         => 'customer',
                        'uploaded_by_user_id'   => null,
                        'buyer_card_last4'      => (string) $order->get_meta( '_wccc_card_last4' ),
                        'buyer_tracking_number' => (string) $order->get_meta( '_wccc_tracking_code' ),
                        'verification_status'   => 'pending',
                        'verified_at'           => null,
                        'created_at'            => current_time( 'mysql' ),
                    );
                }
            }
        }

        return $receipts;
    }

    /**
     * همگام‌سازی متای سفارش با فیش‌های جدول اختصاصی جهت سازگاری کامل عقب‌رو
     */
    public static function sync_order_receipts_meta( WC_Order $order, string $new_receipt_url, string $new_receipt_hash, string $file_path = '', string $file_name = '', bool $is_pdf = false ): void {
        // ذخیره تک‌فیش اصلی در متادیتا
        $order->update_meta_data( '_wccc_receipt_url', $new_receipt_url );
        $order->update_meta_data( '_wccc_receipt_hash', $new_receipt_hash );

        // ذخیره در آرایه متای چندفیشی
        $meta_receipts = (array) $order->get_meta( '_wccc_receipts' );
        if ( ! is_array( $meta_receipts ) ) {
            $meta_receipts = array();
        }

        $meta_receipts[] = array(
            'url'         => $new_receipt_url,
            'file_url'    => $new_receipt_url,
            'file_path'   => $file_path,
            'file_hash'   => $new_receipt_hash,
            'file_name'   => $file_name ?: wp_basename( $new_receipt_url ),
            'is_pdf'      => $is_pdf,
            'uploaded_at' => current_time( 'mysql', true ),
        );

        $order->update_meta_data( '_wccc_receipts', $meta_receipts );

        // آرایه‌ای از لینک‌ها برای استفاده آسان در قالب‌ها و افزونه‌های دیگر
        $urls_list = array();
        foreach ( $meta_receipts as $item ) {
            if ( ! empty( $item['url'] ) ) {
                $urls_list[] = $item['url'];
            }
        }
        $order->update_meta_data( '_wccc_receipt_urls', $urls_list );
    }

    /**
     * پردازش آپلود و اعتبارسنجی فایل فیش
     *
     * @param array $file آرایه $_FILES
     * @param int   $order_id شناسه سفارش
     * @return array|WP_Error
     */
    public static function process_file_upload( array $file, int $order_id = 0 ) {
        if ( isset( $file['error'] ) && UPLOAD_ERR_OK !== $file['error'] ) {
            return new WP_Error( 'upload_err', __( 'خطا در بارگذاری فایل در سرور.', 'wccc' ) );
        }

        $max_size = (int) apply_filters( 'wccc_max_receipt_size', self::DEFAULT_MAX_SIZE );
        if ( $file['size'] > $max_size ) {
            return new WP_Error( 'size_err', __( 'حجم فایل بیشتر از ۵ مگابایت مجاز است.', 'wccc' ) );
        }

        $allowed_mimes = array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png'          => 'image/png',
            'webp'         => 'image/webp',
            'pdf'          => 'application/pdf',
        );

        $file_info = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $allowed_mimes );
        $ext       = strtolower( (string) $file_info['ext'] );
        $type      = strtolower( (string) $file_info['type'] );

        if ( empty( $ext ) || empty( $type ) || ! in_array( $type, array_values( $allowed_mimes ), true ) ) {
            return new WP_Error( 'mime_err', __( 'فرمت فایل غیر مجاز است. تنها فرمت‌های JPG, PNG, WEBP و PDF پذیرفته می‌شوند.', 'wccc' ) );
        }

        $upload_dir = wp_upload_dir();
        if ( ! empty( $upload_dir['error'] ) ) {
            return new WP_Error( 'dir_err', __( 'پوشه آپلود وردپرس در دسترس نیست.', 'wccc' ) );
        }

        $year_month = gmdate( 'Y/m' );
        $base_dir   = trailingslashit( $upload_dir['basedir'] ) . 'wccc-receipts';
        $base_url   = trailingslashit( $upload_dir['baseurl'] ) . 'wccc-receipts';
        $target_dir = trailingslashit( $base_dir ) . $year_month;
        $target_url = trailingslashit( $base_url ) . $year_month;

        if ( ! wp_mkdir_p( $target_dir ) ) {
            return new WP_Error( 'mkdir_err', __( 'خطا در ایجاد پوشه ذخیره‌سازی فیش‌ها.', 'wccc' ) );
        }

        self::secure_directory( $base_dir );
        self::secure_directory( $target_dir );

        $random_token = wp_generate_password( 16, false, false );
        $new_filename = sprintf( 'receipt_%s_%s.%s', gmdate( 'Ymd_His' ), $random_token, $ext );
        $destination  = trailingslashit( $target_dir ) . $new_filename;

        if ( ! move_uploaded_file( $file['tmp_name'], $destination ) ) {
            return new WP_Error( 'move_err', __( 'خطا در انتقال فایل فیش به هاست.', 'wccc' ) );
        }

        chmod( $destination, 0644 );

        $is_pdf = ( 'pdf' === $ext );

        // بهینه‌سازی و فشرده‌سازی خودکار تصویر در لحظه آپلود با کلاس استاندارد wp_get_image_editor() وردپرس
        if ( ! $is_pdf && function_exists( 'wp_get_image_editor' ) ) {
            $editor = wp_get_image_editor( $destination );
            if ( ! is_wp_error( $editor ) ) {
                $editor->resize( 1400, 1400, false );
                $editor->set_quality( 82 );
                $editor->save( $destination );
            }
        }

        $file_hash = hash_file( 'sha256', $destination );
        $final_url = trailingslashit( $target_url ) . $new_filename;

        return array(
            'file_path'     => $destination,
            'file_url'      => $final_url,
            'file_hash'     => (string) $file_hash,
            'attachment_id' => 0,
            'file_name'     => sanitize_file_name( $file['name'] ),
            'is_pdf'        => $is_pdf,
        );
    }

    /**
     * ثبت نهایی پرداخت (ارسال اطلاعات آنی هوشمند یا فیش عادی با پشتیبانی Multi-Receipt)
     */
    public static function handle_submit_payment(): void {
        $nonce = isset( $_REQUEST['security'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'wccc_receipt_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'نشست امنیتی نامعتبر است. لطفاً صفحه را مجدداً بارگذاری فرمایید.', 'wccc' ) ), 403 );
        }

        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        $order    = $order_id ? wc_get_order( $order_id ) : null;
        if ( ! $order ) {
            wp_send_json_error( array( 'message' => __( 'سفارش یافت نشد.', 'wccc' ) ), 404 );
        }

        // بررسی امنیتی مقابله با آسیب‌پذیری IDOR از طریق تطابق دقیق order_key
        $posted_order_key = isset( $_POST['order_key'] ) ? sanitize_text_field( wp_unslash( $_POST['order_key'] ) ) : '';
        $actual_order_key = (string) $order->get_order_key();
        if ( empty( $posted_order_key ) || ! hash_equals( $actual_order_key, $posted_order_key ) ) {
            wp_send_json_error( array( 'message' => __( 'کلید امنیتی سفارش نامعتبر است. دسترسی غیرمجاز.', 'wccc' ) ), 403 );
        }

        // بررسی مالکیت سفارش برای کاربران واردشده به حساب کاربری
        if ( is_user_logged_in() && ! current_user_can( 'edit_shop_orders', $order_id ) && ! current_user_can( 'manage_woocommerce' ) ) {
            $current_user_id   = get_current_user_id();
            $order_customer_id = (int) $order->get_customer_id();
            if ( $order_customer_id > 0 && $order_customer_id !== $current_user_id ) {
                wp_send_json_error( array( 'message' => __( 'شما اجازه ثبت فیش یا اطلاعات پرداخت برای این سفارش را ندارید.', 'wccc' ) ), 403 );
            }
        }

        $payment_mode = isset( $_POST['payment_mode'] ) ? sanitize_key( $_POST['payment_mode'] ) : 'instant_auto';
        $redirect_url = $order->get_checkout_order_received_url();

        if ( 'instant_auto' === $payment_mode ) {
            // جلوگیری از ثبت تکراری و ناهماهنگی در ارسال همزمان
            $existing_card = (string) $order->get_meta( '_wccc_card_last4' );
            if ( empty( $existing_card ) ) {
                $existing_card = (string) $order->get_meta( '_wccc_buyer_card_last4' );
            }
            if ( ! empty( $existing_card ) ) {
                wp_send_json_success( array(
                    'message'      => __( 'اطلاعات کارت شما با موفقیت ثبت شد و در انتظار تطبیق با پیامک بانکی است', 'wccc' ),
                    'card_locked'  => true,
                    'no_redirect'  => true,
                ) );
                return;
            }

            // اعتبارسنجی ۴ رقم آخر کارت در حالت آنی
            $card_last4 = isset( $_POST['buyer_card_last4'] ) ? preg_replace( '/\D/', '', (string) $_POST['buyer_card_last4'] ) : '';
            if ( strlen( $card_last4 ) !== 4 ) {
                wp_send_json_error( array( 'message' => __( 'لطفاً ۴ رقم آخر شماره کارت واریزی را وارد فرمایید.', 'wccc' ) ), 400 );
            }
            $tracking = isset( $_POST['buyer_tracking_number'] ) ? sanitize_text_field( wp_unslash( $_POST['buyer_tracking_number'] ) ) : '';

            // استخراج مبلغ پنی اختصاص‌یافته
            $penny_amount = (float) $order->get_meta( '_wccc_penny_offset' );
            if ( $penny_amount <= 0 ) {
                $penny_amount = (float) $order->get_meta( '_wccc_penny_amount' );
            }
            if ( $penny_amount <= 0 ) {
                $allocated = (float) $order->get_meta( '_wccc_allocated_amount' );
                $base      = (float) $order->get_meta( '_wccc_base_amount' );
                if ( $allocated > $base && $base > 0 ) {
                    $penny_amount = $allocated - $base;
                }
            }
            if ( $penny_amount < 0 ) {
                $penny_amount = 0.0;
            }

            // ۱. ثبت پنی به عنوان Fee مجزا با عنوان «شناسه پرداخت هوشمند»
            self::set_order_penny_fee( $order, $penny_amount );

            // ۲. ذخیره متاداده‌های الزامی (سازگار با HPOS)
            $order->update_meta_data( '_wccc_payment_mode', 'instant_auto' );
            $order->update_meta_data( '_wccc_penny_amount', (string) (int) $penny_amount );
            $order->update_meta_data( '_wccc_card_last4', $card_last4 );
            $order->update_meta_data( '_wccc_tracking_code', $tracking );
            $order->update_meta_data( '_wccc_buyer_card_last4', $card_last4 );
            if ( ! empty( $tracking ) ) {
                $order->update_meta_data( '_wccc_buyer_tracking_number', $tracking );
            }
            $order->update_meta_data( '_wccc_allocated_amount', (string) $order->get_total() );

            // ۳. یادداشت سفارش با فرمت الزامی
            $currency        = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : 'تومان';
            $formatted_penny = function_exists( 'number_format_i18n' ) ? number_format_i18n( (int) $penny_amount ) : (string) (int) $penny_amount;
            $note            = sprintf(
                __( 'روش پرداخت: کارت به کارت هوشمند (تأیید آنی). ۴ رقم کارت مشتری: %s | شناسه پنی: %s %s.', 'wccc' ),
                $card_last4,
                $formatted_penny,
                $currency
            );
            $order->add_order_note( $note );
            $order->save();

            wp_send_json_success( array(
                'message'      => __( 'اطلاعات کارت شما با موفقیت ثبت شد و در انتظار تطبیق با پیامک بانکی است', 'wccc' ),
                'card_locked'  => true,
                'no_redirect'  => true,
            ) );
        } else {
            // حالت manual_receipt (واریز عادی با آپلود تصویر فیش):
            $card_last4 = isset( $_POST['buyer_card_last4'] ) ? preg_replace( '/\D/', '', (string) $_POST['buyer_card_last4'] ) : '';
            $tracking   = isset( $_POST['buyer_tracking_number'] ) ? sanitize_text_field( wp_unslash( $_POST['buyer_tracking_number'] ) ) : '';

            if ( ! empty( $card_last4 ) ) {
                $order->update_meta_data( '_wccc_card_last4', $card_last4 );
                $order->update_meta_data( '_wccc_buyer_card_last4', $card_last4 );
            }
            if ( ! empty( $tracking ) ) {
                $order->update_meta_data( '_wccc_tracking_code', $tracking );
                $order->update_meta_data( '_wccc_buyer_tracking_number', $tracking );
            }

            $token = isset( $_POST['wccc_uploaded_receipt_token'] ) ? sanitize_text_field( wp_unslash( $_POST['wccc_uploaded_receipt_token'] ) ) : '';
            $data  = ! empty( $token ) ? get_transient( $token ) : false;

            $receipt_url  = '';
            $receipt_hash = '';
            $file_path    = '';
            $file_name    = '';
            $is_pdf       = false;

            if ( is_array( $data ) ) {
                if ( ! empty( $data['file_url'] ) ) {
                    $receipt_url = esc_url_raw( $data['file_url'] );
                }
                if ( ! empty( $data['file_hash'] ) ) {
                    $receipt_hash = sanitize_text_field( $data['file_hash'] );
                }
                if ( ! empty( $data['file_path'] ) ) {
                    $file_path = sanitize_text_field( $data['file_path'] );
                }
                if ( ! empty( $data['original_name'] ) ) {
                    $file_name = sanitize_text_field( $data['original_name'] );
                } elseif ( ! empty( $data['file_name'] ) ) {
                    $file_name = sanitize_text_field( $data['file_name'] );
                }
                $is_pdf = ! empty( $data['is_pdf'] );
            } elseif ( ! empty( $_FILES['receipt_file'] ) && is_array( $_FILES['receipt_file'] ) && UPLOAD_ERR_OK === $_FILES['receipt_file']['error'] ) {
                $upload_result = self::process_file_upload( $_FILES['receipt_file'], $order->get_id() );
                if ( is_wp_error( $upload_result ) ) {
                    wp_send_json_error( array( 'message' => $upload_result->get_error_message() ), 400 );
                }
                $receipt_url  = esc_url_raw( $upload_result['file_url'] );
                $receipt_hash = sanitize_text_field( $upload_result['file_hash'] );
                $file_path    = sanitize_text_field( $upload_result['file_path'] );
                $file_name    = sanitize_text_field( $upload_result['file_name'] );
                $is_pdf       = ! empty( $upload_result['is_pdf'] );
            } else {
                wp_send_json_error( array( 'message' => __( 'لطفاً تصویر فیش واریزی را بارگذاری فرمایید.', 'wccc' ) ), 400 );
            }

            // درج در جدول اختصاصی wccc_receipts (با پشتیبانی از ثبت چند رسید مجزا برای یک سفارش و ممانعت از فیش تکراری)
            $db_receipt_id = self::insert_receipt_record(
                $order->get_id(),
                $receipt_url,
                $receipt_hash,
                'customer',
                get_current_user_id() ?: null,
                $card_last4,
                $tracking
            );

            if ( is_wp_error( $db_receipt_id ) ) {
                wp_send_json_error( array( 'message' => $db_receipt_id->get_error_message() ), 400 );
            }

            // همگام‌سازی متادیتای سفارش (سازگاری عقب‌رو با _wccc_receipts و _wccc_receipt_urls)
            self::sync_order_receipts_meta( $order, $receipt_url, $receipt_hash, $file_path, $file_name, $is_pdf );
            $order->delete_meta_data( '_wccc_receipt_attachment_id' );

            // عدم حذف پنی در صورت انقضای مهلت آنی و فال‌بک به آپلود فیش
            $existing_penny = (float) $order->get_meta( '_wccc_penny_offset' );
            if ( $existing_penny <= 0 ) {
                $existing_penny = (float) $order->get_meta( '_wccc_penny_amount' );
            }

            if ( $existing_penny <= 0 ) {
                self::remove_order_penny_fee( $order );
                $order->update_meta_data( '_wccc_penny_offset', '0' );
                $order->update_meta_data( '_wccc_penny_amount', '0' );

                $base_total = (float) $order->get_meta( '_wccc_base_amount' );
                if ( $base_total <= 0 ) {
                    $base_total = (float) $order->get_total();
                }
                $order->update_meta_data( '_wccc_allocated_amount', (string) $base_total );
            } else {
                $expected_total = (float) $order->get_meta( '_wccc_expected_total' );
                if ( $expected_total <= 0 ) {
                    $expected_total = (float) $order->get_total();
                }
                $order->update_meta_data( '_wccc_allocated_amount', (string) $expected_total );
                $order->update_meta_data( '_wccc_expected_total', (string) $expected_total );
            }

            $order->update_meta_data( '_wccc_payment_mode', 'manual_receipt' );

            if ( ! empty( $token ) ) {
                delete_transient( $token );
            }

            $target_status = 'wccc-review';
            $gateway_settings = get_option( 'woocommerce_wccc_card_to_card_settings', null );
            if ( is_array( $gateway_settings ) && ! empty( $gateway_settings['status_receipt_uploaded'] ) ) {
                $target_status = sanitize_key( (string) $gateway_settings['status_receipt_uploaded'] );
            }

            $statuses = function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array();
            $wc_prefixed_target = 0 === strpos( $target_status, 'wc-' ) ? $target_status : 'wc-' . $target_status;
            if ( ! array_key_exists( $wc_prefixed_target, $statuses ) && ! array_key_exists( $target_status, $statuses ) ) {
                $target_status = array_key_exists( 'wc-wccc-review', $statuses ) ? 'wccc-review' : 'on-hold';
            }

            $wccc_panel_settings = get_option( 'wccc_gateway_settings', array() );
            $pending_review_msg  = ! empty( $wccc_panel_settings['pending_review_message'] ) ? (string) $wccc_panel_settings['pending_review_message'] : __( 'اطلاعات پرداخت شما با موفقیت ثبت شد و در صف بررسی حسابداری قرار گرفت. پس از تأیید نهایی، وضعیت سفارش به‌روزرسانی خواهد شد.', 'wccc' );

            $note = sprintf(
                __( 'روش پرداخت: واریز عادی کارت به کارت. فیش جدید (شناسه #%d) پیوست شد و در انتظار بررسی مدیریت است.', 'wccc' ),
                $db_receipt_id
            );
            $order->update_status( $target_status, $note );
            $order->save();

            // ریدایرکت کاربر به صفحه تشکر (order-received)
            wp_send_json_success( array(
                'message'      => $pending_review_msg,
                'receipt_id'   => $db_receipt_id,
                'redirect_url' => $redirect_url,
            ) );
        }
    }

    /**
     * آپلود فیش به صورت ایزوله از طریق ایجکس پیش از سابمیت فرم
     * در حالت دستی (Manual Review)، ارسال چند رسید مجاز است.
     */
    public static function handle_upload(): void {
        $nonce = isset( $_REQUEST['security'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'wccc_receipt_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'نشست امنیتی نامعتبر است. لطفاً صفحه را مجدداً بارگذاری فرمایید.', 'wccc' ) ), 403 );
        }

        if ( empty( $_FILES['receipt_file'] ) || ! is_array( $_FILES['receipt_file'] ) ) {
            wp_send_json_error( array( 'message' => __( 'هیچ فایلی برای بارگذاری ارسال نشده است.', 'wccc' ) ), 400 );
        }

        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        if ( $order_id > 0 ) {
            $order = wc_get_order( $order_id );
            if ( ! $order ) {
                wp_send_json_error( array( 'message' => __( 'سفارش مورد نظر یافت نشد.', 'wccc' ) ), 404 );
            }

            // بررسی امنیتی مقابله با آسیب‌پذیری IDOR از طریق تطابق دقیق order_key
            $posted_order_key = isset( $_POST['order_key'] ) ? sanitize_text_field( wp_unslash( $_POST['order_key'] ) ) : '';
            $actual_order_key = (string) $order->get_order_key();
            if ( empty( $posted_order_key ) || ! hash_equals( $actual_order_key, $posted_order_key ) ) {
                wp_send_json_error( array( 'message' => __( 'کلید امنیتی سفارش نامعتبر است. دسترسی غیرمجاز.', 'wccc' ) ), 403 );
            }

            // بررسی مالکیت سفارش برای کاربران واردشده به حساب کاربری
            if ( is_user_logged_in() && ! current_user_can( 'edit_shop_orders', $order_id ) && ! current_user_can( 'manage_woocommerce' ) ) {
                $current_user_id   = get_current_user_id();
                $order_customer_id = (int) $order->get_customer_id();
                if ( $order_customer_id > 0 && $order_customer_id !== $current_user_id ) {
                    wp_send_json_error( array( 'message' => __( 'شما اجازه ثبت فیش برای این سفارش را ندارید.', 'wccc' ) ), 403 );
                }
            }

            // در حالت بررسی دستی (Multi-receipt)، ارسال فیش‌های تکمیلی مجاز است مگر آنکه سفارش تکمیل یا لغو شده باشد
            if ( method_exists( $order, 'is_paid' ) && $order->is_paid() ) {
                wp_send_json_error( array( 'message' => __( 'این سفارش قبلاً پرداخت و تایید شده است.', 'wccc' ) ), 400 );
            }
        }

        $result = self::process_file_upload( $_FILES['receipt_file'], $order_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
        }

        // بررسی ضد جعل هش فیش در سطح دیتابیس
        global $wpdb;
        $receipts_table = Schema::get_table_name( Schema::TABLE_RECEIPTS );
        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $receipts_table ) ) === $receipts_table ) {
            $duplicate = $wpdb->get_row( $wpdb->prepare(
                "SELECT id, order_id FROM {$receipts_table} WHERE receipt_hash = %s LIMIT 1",
                $result['file_hash']
            ) );
            if ( $duplicate && (int) $duplicate->order_id !== $order_id ) {
                wp_send_json_error( array(
                    'message' => __( 'این فیش واریزی قبلاً در سامانه برای سفارش دیگری ثبت شده است (تشخیص فیش تکراری).', 'wccc' ),
                ), 400 );
            }
        }

        $temp_token = 'wccc_rcpt_' . wp_generate_password( 24, false, false );
        set_transient( $temp_token, array(
            'file_path'     => $result['file_path'],
            'file_url'      => $result['file_url'],
            'file_hash'     => $result['file_hash'],
            'attachment_id' => $result['attachment_id'],
            'uploaded_at'   => current_time( 'mysql', true ),
            'is_pdf'        => $result['is_pdf'],
            'original_name' => $result['file_name'],
        ), HOUR_IN_SECONDS * 2 );

        wp_send_json_success( array(
            'token'         => $temp_token,
            'attachment_id' => $result['attachment_id'],
            'receipt_url'   => esc_url_raw( $result['file_url'] ),
            'receipt_hash'  => $result['file_hash'],
            'file_name'     => $result['file_name'],
            'is_pdf'        => $result['is_pdf'],
            'message'       => __( 'فیش واریزی با موفقیت بارگذاری گردید.', 'wccc' ),
        ) );
    }

    private static function secure_directory( string $dir ): void {
        $index_file = trailingslashit( $dir ) . 'index.php';
        if ( ! file_exists( $index_file ) ) {
            file_put_contents( $index_file, "<?php\n// Silence is golden.\n" );
        }

        $htaccess_file = trailingslashit( $dir ) . '.htaccess';
        if ( ! file_exists( $htaccess_file ) ) {
            $rules = "Options -Indexes\n<Files *.php>\nOrder Deny,Allow\nDeny from all\n</Files>\n";
            file_put_contents( $htaccess_file, $rules );
        }
    }
}
