<?php
/**
 * ماژول مدیریت چرخه عمر و پاکسازی فیش‌های واریزی (Receipt Lifecycle & Cleanup)
 *
 * @package   WCCC
 * @author    تیم فنی WCCC
 * @license   GPL-2.0-or-later
 */

declare(strict_types=1);

namespace WCCC\Storage;

use WC_Order;

final class ReceiptCleanup {

    /**
     * نام هوک رویداد زمان‌بندی‌شده پاکسازی روزانه
     */
    public const CRON_HOOK = 'wccc_daily_receipt_cleanup';

    /**
     * ثبت هوک‌ها و زمان‌بندی‌های چرخه عمر فیش
     */
    public static function init(): void {
        // ۱. پاکسازی فیش‌ها هنگام حذف دائمی سفارش در HPOS
        add_action( 'woocommerce_before_delete_order', array( self::class, 'handle_order_deletion' ), 10, 1 );

        // ۲. پاکسازی فیش‌ها هنگام حذف پست سفارش در ساختار سنتی وردپرس (Legacy CPT)
        add_action( 'before_delete_post', array( self::class, 'handle_legacy_post_deletion' ), 10, 1 );

        // ۳. ثبت رویداد زمان‌بندی‌شده کران وردپرس
        add_action( 'init', array( self::class, 'schedule_cleanup_cron' ) );
        add_action( self::CRON_HOOK, array( self::class, 'cleanup_orphaned_receipts' ) );
    }

    /**
     * زمان‌بندی کران پاکسازی خودکار فیش‌های یتیم (روزانه)
     */
    public static function schedule_cleanup_cron(): void {
        if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( time(), 'daily', self::CRON_HOOK );
        }
    }

    /**
     * لغو زمان‌بندی کران پاکسازی در زمان غیرفعال‌سازی افزونه
     */
    public static function clear_cleanup_cron(): void {
        $timestamp = wp_next_scheduled( self::CRON_HOOK );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, self::CRON_HOOK );
        }
        wp_clear_scheduled_hook( self::CRON_HOOK );
    }

    /**
     * مدیریت حذف فیش‌های مرتبط با سفارش هنگام حذف قطعی سفارش در ووکامرس (HPOS)
     *
     * @param int|WC_Order $order شناسه سفارش یا شیء WC_Order
     */
    public static function handle_order_deletion( $order ): void {
        $order_obj = ( $order instanceof WC_Order ) ? $order : wc_get_order( $order );
        if ( ! $order_obj ) {
            return;
        }

        self::cleanup_order_receipts( $order_obj );
    }

    /**
     * مدیریت حذف سفارش‌های سنتی وردپرس با پست تایپ shop_order
     *
     * @param int $post_id شناسه پست
     */
    public static function handle_legacy_post_deletion( int $post_id ): void {
        if ( 'shop_order' !== get_post_type( $post_id ) ) {
            return;
        }

        $order = wc_get_order( $post_id );
        if ( $order ) {
            self::cleanup_order_receipts( $order );
        }
    }

    /**
     * پاکسازی فیزیکی کلیه فیش‌های متعلق به یک سفارش از دیسک
     *
     * @param WC_Order $order شیء سفارش ووکامرس
     * @return int تعداد فایل‌های حذف‌شده
     */
    public static function cleanup_order_receipts( WC_Order $order ): int {
        $deleted_count = 0;
        $files_to_delete = array();

        // استخراج فیش‌های چندگانه از _wccc_receipts
        $receipts = (array) $order->get_meta( '_wccc_receipts' );
        if ( is_array( $receipts ) && ! empty( $receipts ) ) {
            foreach ( $receipts as $receipt ) {
                if ( ! is_array( $receipt ) ) {
                    continue;
                }
                if ( ! empty( $receipt['file_path'] ) ) {
                    $files_to_delete[] = (string) $receipt['file_path'];
                } elseif ( ! empty( $receipt['file_url'] ) ) {
                    $files_to_delete[] = (string) $receipt['file_url'];
                } elseif ( ! empty( $receipt['url'] ) ) {
                    $files_to_delete[] = (string) $receipt['url'];
                }
            }
        }

        // استخراج فیش تک‌گانه از _wccc_receipt_url
        $single_url = (string) $order->get_meta( '_wccc_receipt_url' );
        if ( ! empty( $single_url ) ) {
            $files_to_delete[] = $single_url;
        }

        // پاکسازی اتچمنت رسانه‌ای در صورت وجود (نسخه‌های قدیمی‌تر قبل از فاز ۱)
        $attach_id = (int) $order->get_meta( '_wccc_receipt_attachment_id' );
        if ( $attach_id > 0 && function_exists( 'wp_delete_attachment' ) ) {
            wp_delete_attachment( $attach_id, true );
        }

        // حذف تک تک فایل‌ها با رعایت امنیت
        $files_to_delete = array_unique( $files_to_delete );
        foreach ( $files_to_delete as $file_ref ) {
            if ( self::delete_receipt_file( $file_ref ) ) {
                $deleted_count++;
            }
        }

        return $deleted_count;
    }

    /**
     * کران جاب پاکسازی فیش‌های یتیم (Orphaned Receipts)
     * شامل فایل‌های آپلود موقت منقضی‌شده، فایل‌های بدون سفارش، و موارد حذف‌نشده
     *
     * @return int تعداد فایل‌های یتیم پاکسازی‌شده
     */
    public static function cleanup_orphaned_receipts(): int {
        $upload_dir   = wp_upload_dir();
        $receipts_dir = trailingslashit( $upload_dir['basedir'] ) . 'wccc-receipts';

        if ( ! is_dir( $receipts_dir ) ) {
            return 0;
        }

        // حداکثر عمر فایل‌های موقت قبل از پاکسازی (پیش‌فرض: ۲۴ ساعت)
        $max_age_hours  = (int) apply_filters( 'wccc_receipt_cleanup_max_age_hours', 24 );
        $threshold_time = time() - ( $max_age_hours * HOUR_IN_SECONDS );

        $deleted_count = 0;

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator( $receipts_dir, \FilesystemIterator::SKIP_DOTS ),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ( $iterator as $item ) {
                if ( $item->isDir() ) {
                    // حذف پوشه‌های خالی باقی‌مانده (مثلاً سال یا ماه خالی)
                    self::remove_empty_directory( $item->getPathname() );
                    continue;
                }

                $file_path = $item->getPathname();
                $filename  = $item->getFilename();

                // فایل‌های محافظت‌شده را دست نزنیم
                if ( in_array( $filename, array( 'index.php', '.htaccess', 'index.html' ), true ) ) {
                    continue;
                }

                // فایل‌هایی که به تازگی آپلود شده‌اند را پاک نکنیم
                if ( $item->getMTime() > $threshold_time ) {
                    continue;
                }

                if ( self::is_file_orphaned( $file_path, $filename ) ) {
                    if ( self::delete_receipt_file( $file_path ) ) {
                        $deleted_count++;
                    }
                }
            }
        } catch ( \Exception $e ) {
            // ثبت خطا در صورت بروز استثنا در بررسی فایل‌ها
            if ( function_exists( 'wc_get_logger' ) ) {
                wc_get_logger()->warning(
                    'خطا در اجرای کران پاکسازی فیش‌های یتیم WCCC: ' . $e->getMessage(),
                    array( 'source' => 'wccc-receipt-cleanup' )
                );
            }
        }

        return $deleted_count;
    }

    /**
     * بررسی اینکه آیا یک فایل فیش یتیم (Orphaned) است یا خیر
     *
     * @param string $file_path مسیر کامل فایل
     * @param string $filename  نام فایل
     * @return bool
     */
    public static function is_file_orphaned( string $file_path, string $filename ): bool {
        // حالت ۱: فایل‌های موقت بدون سفارش (پیشوند rcpt_0_)
        // این فایل‌ها قبل از ثبت نهایی سفارش ساخته شده‌اند و با گذشت ۲۴ ساعت حتماً یتیم هستند
        if ( 0 === strpos( $filename, 'rcpt_0_' ) ) {
            return true;
        }

        // حالت ۲: استخراج شماره سفارش از الگوی نامگذاری rcpt_{order_id}_{uniqid}.ext
        if ( preg_match( '/^rcpt_(\d+)_/i', $filename, $matches ) ) {
            $order_id = (int) $matches[1];
            if ( $order_id <= 0 ) {
                return true;
            }

            $order = wc_get_order( $order_id );
            // اگر سفارش در دیتابیس وجود نداشته باشد، فایل یتیم است
            if ( ! $order ) {
                return true;
            }

            // اگر سفارش وجود دارد، بررسی می‌کنیم آیا این فایل خاص در متادیتای سفارش قرار دارد یا خیر
            if ( ! self::is_file_attached_to_order( $order, $filename, $file_path ) ) {
                return true;
            }

            return false;
        }

        // حالت ۳: هر فایل ناشناخته در دایرکتوری با عمر بیش از مهلت مجاز که در هیچ سفارشی نیست
        return ! self::is_file_referenced_in_any_order( $filename );
    }

    /**
     * بررسی اینکه آیا فایل در متادیتای سفارش مشخص‌شده حضور دارد
     *
     * @param WC_Order $order     سفارش
     * @param string   $filename  نام فایل
     * @param string   $file_path مسیر فایل
     * @return bool
     */
    private static function is_file_attached_to_order( WC_Order $order, string $filename, string $file_path ): bool {
        $single_url = (string) $order->get_meta( '_wccc_receipt_url' );
        if ( ! empty( $single_url ) && false !== strpos( $single_url, $filename ) ) {
            return true;
        }

        $receipts = (array) $order->get_meta( '_wccc_receipts' );
        if ( is_array( $receipts ) ) {
            foreach ( $receipts as $rcpt ) {
                if ( ! is_array( $rcpt ) ) {
                    continue;
                }
                if ( ! empty( $rcpt['file_path'] ) && ( $rcpt['file_path'] === $file_path || basename( $rcpt['file_path'] ) === $filename ) ) {
                    return true;
                }
                if ( ! empty( $rcpt['file_url'] ) && false !== strpos( (string) $rcpt['file_url'], $filename ) ) {
                    return true;
                }
                if ( ! empty( $rcpt['url'] ) && false !== strpos( (string) $rcpt['url'], $filename ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * بررسی سریع ارجاع فایل در سفارشات از طریق کوئری دیتابیس (سازگار با HPOS و پایگاه داده سنتی)
     *
     * @param string $filename نام فایل
     * @return bool
     */
    public static function is_file_referenced_in_any_order( string $filename ): bool {
        global $wpdb;

        $like = '%' . $wpdb->esc_like( $filename ) . '%';

        // ۱. بررسی در جدول متای سفارشات HPOS در صورت وجود
        $hpos_meta_table = $wpdb->prefix . 'wc_orders_meta';
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $hpos_meta_table ) ) === $hpos_meta_table ) {
            $found_hpos = $wpdb->get_var( $wpdb->prepare(
                "SELECT meta_id FROM {$hpos_meta_table} WHERE meta_key IN ('_wccc_receipt_url', '_wccc_receipts') AND meta_value LIKE %s LIMIT 1",
                $like
            ) );
            if ( ! empty( $found_hpos ) ) {
                return true;
            }
        }

        // ۲. بررسی در جدول سنتی postmeta
        $found_postmeta = $wpdb->get_var( $wpdb->prepare(
            "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key IN ('_wccc_receipt_url', '_wccc_receipts') AND meta_value LIKE %s LIMIT 1",
            $like
        ) );

        return ! empty( $found_postmeta );
    }

    /**
     * حذف امن یک فایل فیش با اعتبارسنجی دقیق مسیر (Anti-Path Traversal)
     *
     * @param string $path_or_url مسیر فیزیکی یا URL فیش
     * @return bool نتیجه حذف فایل
     */
    public static function delete_receipt_file( string $path_or_url ): bool {
        $real_path = self::resolve_file_path( $path_or_url );
        if ( ! $real_path || ! self::is_safe_receipt_path( $real_path ) ) {
            return false;
        }

        if ( file_exists( $real_path ) && is_file( $real_path ) ) {
            $result = @unlink( $real_path );
            if ( $result ) {
                self::remove_empty_parent_directories( dirname( $real_path ) );
            }
            return (bool) $result;
        }

        return false;
    }

    /**
     * تبدیل URL یا مسیر ورودی به مسیر مطلق فایل در سرور
     *
     * @param string $path_or_url URL یا مسیر فایل
     * @return string|null مسیر تمیز شده فایل یا null
     */
    public static function resolve_file_path( string $path_or_url ): ?string {
        $trimmed = trim( $path_or_url );
        if ( empty( $trimmed ) ) {
            return null;
        }

        // اگر از قبل مسیر محلی فایل است
        if ( file_exists( $trimmed ) && is_file( $trimmed ) ) {
            return wp_normalize_path( (string) realpath( $trimmed ) );
        }

        $upload_dir = wp_upload_dir();
        $base_url   = trailingslashit( $upload_dir['baseurl'] );
        $base_dir   = trailingslashit( $upload_dir['basedir'] );

        // اگر آدرس با پیشوند baseurl سایت شروع می‌شود
        if ( 0 === strpos( $trimmed, $base_url ) ) {
            $relative  = substr( $trimmed, strlen( $base_url ) );
            $candidate = wp_normalize_path( $base_dir . $relative );
            return $candidate;
        }

        // اگر فقط یک مسیر نسبی مانند /wp-content/uploads/wccc-receipts/... ارسال شده
        if ( false !== strpos( $trimmed, 'wccc-receipts/' ) ) {
            $sub       = substr( $trimmed, (int) strpos( $trimmed, 'wccc-receipts/' ) );
            $candidate = wp_normalize_path( $base_dir . $sub );
            return $candidate;
        }

        return wp_normalize_path( $trimmed );
    }

    /**
     * اعتبارسنجی امنیتی مسیر فایل جهت جلوگیری از Path Traversal
     *
     * @param string $file_path مسیر فیزیکی فایل
     * @return bool آیا فایل در مسیر مجاز فیش‌های WCCC قرار دارد و امن است؟
     */
    public static function is_safe_receipt_path( string $file_path ): bool {
        if ( empty( $file_path ) ) {
            return false;
        }

        $upload_dir   = wp_upload_dir();
        $receipts_dir = wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . 'wccc-receipts' );
        $normalized   = wp_normalize_path( $file_path );

        // مسیر حتماً باید زیرمجموعه wccc-receipts باشد
        if ( 0 !== strpos( $normalized, $receipts_dir ) ) {
            return false;
        }

        // جلوگیری از دستکاری و حذف فایل‌های زیرساختی
        $filename = basename( $normalized );
        if ( in_array( $filename, array( 'index.php', '.htaccess', 'index.html', '' ), true ) ) {
            return false;
        }

        // جلوگیری از بازگشت در مسیر (Directory Traversal)
        if ( false !== strpos( $file_path, '..' ) ) {
            return false;
        }

        return true;
    }

    /**
     * پاکسازی پوشه‌های والد در صورت خالی بودن پس از حذف فیش‌ها
     *
     * @param string $dir مسیر پوشه
     */
    public static function remove_empty_parent_directories( string $dir ): void {
        $upload_dir   = wp_upload_dir();
        $receipts_dir = wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . 'wccc-receipts' );
        $current_dir  = wp_normalize_path( $dir );

        // فقط تا پوشه wccc-receipts به عقب می‌رویم و خود پوشه اصلی را حذف نمی‌کنیم
        while ( strlen( $current_dir ) > strlen( $receipts_dir ) && 0 === strpos( $current_dir, $receipts_dir ) ) {
            if ( ! is_dir( $current_dir ) ) {
                $current_dir = dirname( $current_dir );
                continue;
            }

            self::remove_empty_directory( $current_dir );
            $current_dir = dirname( $current_dir );
        }
    }

    /**
     * حذف یک دایرکتوری در صورتی که فقط حاوی index.php یا کاملاً خالی باشد
     *
     * @param string $dir مسیر دایرکتوری
     */
    private static function remove_empty_directory( string $dir ): void {
        if ( ! is_dir( $dir ) ) {
            return;
        }

        $items = @scandir( $dir );
        if ( false === $items ) {
            return;
        }

        $meaningful_items = array_diff( $items, array( '.', '..', 'index.php', 'index.html' ) );
        if ( empty( $meaningful_items ) ) {
            // حذف index.php قبل از rmdir در صورت وجود
            if ( file_exists( $dir . '/index.php' ) ) {
                @unlink( $dir . '/index.php' );
            }
            if ( file_exists( $dir . '/index.html' ) ) {
                @unlink( $dir . '/index.html' );
            }
            @rmdir( $dir );
        }
    }
}
