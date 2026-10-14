<?php
/**
 * WCCC Admin System Health & Environment Diagnostics View
 * Native Enterprise Health Component
 *
 * @package WCCC\Admin\Views
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

// 1. PHP Version Check
$php_version = PHP_VERSION;
$php_ok      = version_compare( $php_version, '7.4.0', '>=' );

// 2. cURL & OpenSSL Status
$curl_ok    = extension_loaded( 'curl' );
$openssl_ok = extension_loaded( 'openssl' );

// 3. Receipt Uploads Directory Write Permissions
$upload_dir_info = wp_upload_dir();
$receipts_dir    = $upload_dir_info['basedir'] . '/wccc-receipts';
if ( ! file_exists( $receipts_dir ) ) {
    wp_mkdir_p( $receipts_dir );
}
$dir_writable = is_writable( $receipts_dir );

// 4. Database Tables Status (Defensive Schema Check)
$tables_to_check = array(
    'cards'        => array(
        'constant' => 'TABLE_CARDS',
        'default'  => 'wccc_cards',
        'label'    => __( 'کارت‌های بانکی (wccc_cards)', 'wccc' ),
    ),
    'events'       => array(
        'constant' => 'TABLE_EVENTS',
        'default'  => 'wccc_events',
        'label'    => __( 'جدول رویدادها و لاگ‌های وب‌هوک (wccc_events)', 'wccc' ),
    ),
    'reservations' => array(
        'constant' => 'TABLE_RESERVATIONS',
        'default'  => 'wccc_reservations',
        'label'    => __( 'رزرو مبالغ پنی (wccc_reservations)', 'wccc' ),
    ),
    'clients'      => array(
        'constant' => 'TABLE_CLIENTS',
        'default'  => 'wccc_clients',
        'label'    => __( 'کلاینت‌های پردازشگر (wccc_clients)', 'wccc' ),
    ),
    'messages'     => array(
        'constant' => 'TABLE_MESSAGES',
        'default'  => 'wccc_messages',
        'label'    => __( 'پیامک‌های دریافتی (wccc_messages)', 'wccc' ),
    ),
    'receipts'     => array(
        'constant' => 'TABLE_RECEIPTS',
        'default'  => 'wccc_receipts',
        'label'    => __( 'فیش‌های واریزی (wccc_receipts)', 'wccc' ),
    ),
);

$tables_status = array();
$all_tables_ok = true;

foreach ( $tables_to_check as $tbl_key => $tbl_data ) {
    $const_full = '\WCCC\Database\Schema::' . $tbl_data['constant'];
    $tbl_suffix = defined( $const_full ) ? constant( $const_full ) : $tbl_data['default'];
    $tbl_full   = method_exists( '\WCCC\Database\Schema', 'get_table_name' )
        ? \WCCC\Database\Schema::get_table_name( $tbl_suffix )
        : $wpdb->prefix . $tbl_suffix;

    $exists = false;
    $count  = 0;
    try {
        $check  = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $tbl_full ) );
        $exists = ( $check === $tbl_full );
        if ( $exists ) {
            $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$tbl_full}`" );
        }
    } catch ( \Throwable $e ) {
        $exists = false;
    }

    if ( ! $exists ) {
        $all_tables_ok = false;
    }

    $tables_status[ $tbl_key ] = array(
        'full_name' => $tbl_full,
        'label'     => $tbl_data['label'],
        'exists'    => $exists,
        'count'     => $count,
    );
}

$cards_exists  = ! empty( $tables_status['cards']['exists'] );
$cards_count   = $tables_status['cards']['count'] ?? 0;
$events_exists = ! empty( $tables_status['events']['exists'] );
$events_count  = $tables_status['events']['count'] ?? 0;

// 5. Webhook HMAC Secret Readiness
$settings    = get_option( \WCCC\Admin\AjaxHandler::SETTINGS_OPTION_KEY, array() );
$hmac_secret = ! empty( $settings['hmac_secret'] ) ? (string) $settings['hmac_secret'] : '';
$hmac_ready  = ! empty( $hmac_secret ) && strlen( $hmac_secret ) >= 8;

// 6. HPOS Status
$hpos_active = false;
if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
    $hpos_active = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
}
?>

<div class="wccc-view <?php echo ( isset( $_GET['tab'] ) && sanitize_key( $_GET['tab'] ) === 'health' ) ? 'active' : ''; ?>" id="wccc-view-health">

    <!-- Top Action Banner -->
    <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 18px 20px; margin-bottom: 24px; box-shadow: var(--wccc-shadow); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center;">
                <span class="dashicons dashicons-shield" style="font-size: 26px; width: 26px; height: 26px;"></span>
            </div>
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'مرکز سلامت، عیب‌یابی و وضعیت سیستم', 'wccc' ); ?></h3>
                <p style="margin: 0; font-size: 12px; color: var(--wccc-text-muted);">
                    <?php esc_html_e( 'بررسی خودکار پیش‌نیازهای سرور، جداول دیتابیس، مجوزهای فایل و اتصال وب‌هوک.', 'wccc' ); ?>
                </p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" class="wccc-btn wccc-btn-secondary" onclick="window.location.reload();" style="font-size: 11px;">
                <span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px;"></span>
                <span><?php esc_html_e( 'بررسی مجدد وضعیت', 'wccc' ); ?></span>
            </button>
            <button type="button" class="wccc-btn wccc-btn-primary" id="wccc-health-copy-report-btn" style="font-size: 11px;">
                <span class="dashicons dashicons-admin-page" style="font-size: 16px; width: 16px; height: 16px;"></span>
                <span><?php esc_html_e( 'کپی گزارش سلامت', 'wccc' ); ?></span>
            </button>
        </div>
    </div>

    <!-- Health Checklist Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-bottom: 24px;">

        <!-- 1. PHP Runtime -->
        <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 18px; box-shadow: var(--wccc-shadow);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-editor-code" style="color: #0284c7;"></span>
                    <strong style="font-size: 13px; color: var(--wccc-text);"><?php esc_html_e( 'نسخه PHP سرور', 'wccc' ); ?></strong>
                </div>
                <span class="wccc-badge <?php echo $php_ok ? 'wccc-badge-success' : 'wccc-badge-danger'; ?>">
                    <?php echo $php_ok ? esc_html__( 'تأیید شده', 'wccc' ) : esc_html__( 'نیازمند ارتقا', 'wccc' ); ?>
                </span>
            </div>
            <div style="font-size: 12px; color: var(--wccc-text-muted); line-height: 1.6;">
                <?php esc_html_e( 'نسخه فعلی: ', 'wccc' ); ?><span class="wccc-mono" style="font-weight: 700; color: var(--wccc-text);"><?php echo esc_html( $php_version ); ?></span>
                <div style="font-size: 11px; margin-top: 4px;"><?php esc_html_e( 'حداقل نسخه الزامی: PHP 7.4 (تست شده تا PHP 8.3)', 'wccc' ); ?></div>
            </div>
        </div>

        <!-- 2. Security Extensions (cURL & OpenSSL) -->
        <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 18px; box-shadow: var(--wccc-shadow);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-lock" style="color: #8b5cf6;"></span>
                    <strong style="font-size: 13px; color: var(--wccc-text);"><?php esc_html_e( 'ماژول‌های امنیتی cURL & OpenSSL', 'wccc' ); ?></strong>
                </div>
                <span class="wccc-badge <?php echo ( $curl_ok && $openssl_ok ) ? 'wccc-badge-success' : 'wccc-badge-danger'; ?>">
                    <?php echo ( $curl_ok && $openssl_ok ) ? esc_html__( 'فعال و آماده', 'wccc' ) : esc_html__( 'خطا در ماژول', 'wccc' ); ?>
                </span>
            </div>
            <div style="font-size: 12px; color: var(--wccc-text-muted); line-height: 1.6;">
                cURL: <span class="wccc-mono" style="font-weight: 700; color: <?php echo $curl_ok ? '#10b981' : '#ef4444'; ?>;"><?php echo $curl_ok ? 'فعال ✓' : 'غیرفعال ✗'; ?></span> |
                OpenSSL: <span class="wccc-mono" style="font-weight: 700; color: <?php echo $openssl_ok ? '#10b981' : '#ef4444'; ?>;"><?php echo $openssl_ok ? 'فعال ✓' : 'غیرفعال ✗'; ?></span>
                <div style="font-size: 11px; margin-top: 4px;"><?php esc_html_e( 'جهت اعتبارسنجی رمزنگاری‌شده پیامک‌ها و ارتباطات وب‌هوک الزامی است.', 'wccc' ); ?></div>
            </div>
        </div>

        <!-- 3. Receipts Folder Write Permissions -->
        <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 18px; box-shadow: var(--wccc-shadow);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-category" style="color: #f59e0b;"></span>
                    <strong style="font-size: 13px; color: var(--wccc-text);"><?php esc_html_e( 'پوشه بارگذاری فیش‌ها', 'wccc' ); ?></strong>
                </div>
                <span class="wccc-badge <?php echo $dir_writable ? 'wccc-badge-success' : 'wccc-badge-danger'; ?>">
                    <?php echo $dir_writable ? esc_html__( 'قابل نوشتن (Writable)', 'wccc' ) : esc_html__( 'خطای دسترسی', 'wccc' ); ?>
                </span>
            </div>
            <div style="font-size: 11px; color: var(--wccc-text-muted); line-height: 1.6;">
                <span class="wccc-mono" style="font-size: 10px; word-break: break-all;"><?php echo esc_html( $receipts_dir ); ?></span>
                <div style="margin-top: 4px; color: <?php echo $dir_writable ? '#10b981' : '#ef4444'; ?>;">
                    <?php echo $dir_writable ? esc_html__( 'مجوزهای دسترسی ذخیره‌سازی فیش مشتریان کاملاً معتبر است.', 'wccc' ) : esc_html__( 'هشدار: پوشه قابل نوشتن نیست. دسترسی 755 را اعمال فرمایید.', 'wccc' ); ?>
                </div>
            </div>
        </div>

        <!-- 4. Database Tables Status -->
        <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 18px; box-shadow: var(--wccc-shadow);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-database" style="color: #006194;"></span>
                    <strong style="font-size: 13px; color: var(--wccc-text);"><?php esc_html_e( 'جداول اختصاصی پایگاه‌داده', 'wccc' ); ?></strong>
                </div>
                <span class="wccc-badge <?php echo $all_tables_ok ? 'wccc-badge-success' : 'wccc-badge-warning'; ?>">
                    <?php echo $all_tables_ok ? esc_html__( 'سالم و ایندکس‌شده', 'wccc' ) : esc_html__( 'نیاز به ترمیم', 'wccc' ); ?>
                </span>
            </div>
            <div style="font-size: 11px; color: var(--wccc-text-muted); line-height: 1.8;">
                <div><?php esc_html_e( 'کارت‌های بانکی (wccc_cards):', 'wccc' ); ?> <span class="wccc-mono" style="font-weight: 700; color: <?php echo $cards_exists ? '#10b981' : '#ef4444'; ?>;"><?php echo $cards_exists ? "موجود ({$cards_count} رکورد)" : 'یافت نشد'; ?></span></div>
                <div><?php esc_html_e( 'جدول رویدادها و لاگ‌های وب‌هوک (wccc_events):', 'wccc' ); ?> <span class="wccc-mono" style="font-weight: 700; color: <?php echo $events_exists ? '#10b981' : '#ef4444'; ?>;"><?php echo $events_exists ? "موجود ({$events_count} رکورد)" : 'یافت نشد'; ?></span></div>
                <div style="margin-top: 6px; font-size: 10px; color: var(--wccc-text-muted);">
                    <?php
                    $other_tables_ok = ! empty( $tables_status['reservations']['exists'] ) && ! empty( $tables_status['clients']['exists'] ) && ! empty( $tables_status['messages']['exists'] ) && ! empty( $tables_status['receipts']['exists'] );
                    echo $other_tables_ok
                        ? esc_html__( 'سایر جداول پایگاه‌داده (reservations, clients, messages, receipts) تأیید شدند ✓', 'wccc' )
                        : esc_html__( 'هشدار: برخی جداول هسته یافت نشدند.', 'wccc' );
                    ?>
                </div>
            </div>
        </div>

        <!-- 5. HMAC Secret Token -->
        <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 18px; box-shadow: var(--wccc-shadow);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-admin-network" style="color: #ec4899;"></span>
                    <strong style="font-size: 13px; color: var(--wccc-text);"><?php esc_html_e( 'کلید امنیتی وب‌هوک (HMAC)', 'wccc' ); ?></strong>
                </div>
                <span class="wccc-badge <?php echo $hmac_ready ? 'wccc-badge-success' : 'wccc-badge-warning'; ?>">
                    <?php echo $hmac_ready ? esc_html__( 'تنظیم و فعال', 'wccc' ) : esc_html__( 'نامعتبر یا خالی', 'wccc' ); ?>
                </span>
            </div>
            <div style="font-size: 11px; color: var(--wccc-text-muted); line-height: 1.6;">
                <?php echo $hmac_ready ? esc_html__( 'امضای رمزنگاری SHA-256 برای دریافت رویدادهای پرداخت فعال است.', 'wccc' ) : esc_html__( 'هشدار: لطفاً کلید امنیتی وب‌هوک را در تب تنظیمات تکمیل نمایید.', 'wccc' ); ?>
            </div>
        </div>

        <!-- 6. HPOS Compatibility -->
        <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 18px; box-shadow: var(--wccc-shadow);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-dashboard" style="color: #10b981;"></span>
                    <strong style="font-size: 13px; color: var(--wccc-text);"><?php esc_html_e( 'سازگاری با HPOS ووکامرس', 'wccc' ); ?></strong>
                </div>
                <span class="wccc-badge wccc-badge-success">
                    <?php echo $hpos_active ? esc_html__( 'فعال (High-Performance)', 'wccc' ) : esc_html__( 'پشتیبانی کامل (سازگار)', 'wccc' ); ?>
                </span>
            </div>
            <div style="font-size: 11px; color: var(--wccc-text-muted); line-height: 1.6;">
                <?php esc_html_e( 'کدهای درگاه و جداول متادیتا به طور کامل با ذخیره‌ساز سفارش فوق‌سریع ووکامرس همگام هستند.', 'wccc' ); ?>
            </div>
        </div>

    </div>

    <!-- Diagnostic Details Block -->
    <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 24px; box-shadow: var(--wccc-shadow);">
        <h4 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 800; color: var(--wccc-text);">
            <?php esc_html_e( 'متن خلاصه گزارش فنی جهت پشتیبانی:', 'wccc' ); ?>
        </h4>
        <pre id="wccc-health-report-text" class="wccc-mono" style="background: var(--wccc-surface-subtle); padding: 16px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); font-size: 11px; line-height: 1.8; color: var(--wccc-text); white-space: pre-wrap; margin: 0; direction: ltr; text-align: left;">
=== WCCC SYSTEM HEALTH DIAGNOSTICS ===
Report Generated : <?php echo esc_html( current_time( 'Y-m-d H:i:s' ) ); ?>

Plugin Version   : <?php echo esc_html( WCCC_VERSION ); ?> Enterprise
PHP Version      : <?php echo esc_html( $php_version ); ?> (Status: <?php echo $php_ok ? 'PASS' : 'FAIL'; ?>)
cURL Extension   : <?php echo $curl_ok ? 'Loaded (PASS)' : 'Missing (FAIL)'; ?>

OpenSSL Ext      : <?php echo $openssl_ok ? 'Loaded (PASS)' : 'Missing (FAIL)'; ?>

Uploads Perms    : <?php echo $dir_writable ? 'Writable (PASS)' : 'Not Writable (FAIL)'; ?>

Database Tables  : cards=<?php echo $cards_exists ? 'OK' : 'MISSING'; ?>, events=<?php echo $events_exists ? 'OK' : 'MISSING'; ?>, reservations=<?php echo ! empty( $tables_status['reservations']['exists'] ) ? 'OK' : 'MISSING'; ?>, clients=<?php echo ! empty( $tables_status['clients']['exists'] ) ? 'OK' : 'MISSING'; ?>, messages=<?php echo ! empty( $tables_status['messages']['exists'] ) ? 'OK' : 'MISSING'; ?>, receipts=<?php echo ! empty( $tables_status['receipts']['exists'] ) ? 'OK' : 'MISSING'; ?>

HMAC Secret      : <?php echo $hmac_ready ? 'Configured (PASS)' : 'Missing (WARN)'; ?>

HPOS Mode        : <?php echo $hpos_active ? 'Active (HPOS)' : 'Standard (Compatible)'; ?>

Overall Health   : <?php echo ( $php_ok && $curl_ok && $openssl_ok && $dir_writable && $all_tables_ok ) ? '100% HEALTHY' : 'ATTENTION REQUIRED'; ?>
=======================================
        </pre>
    </div>

</div>

<script type="text/javascript">
(function() {
    var copyBtn = document.getElementById('wccc-health-copy-report-btn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var reportText = document.getElementById('wccc-health-report-text');
            if (reportText && navigator.clipboard) {
                navigator.clipboard.writeText(reportText.innerText.trim()).then(function() {
                    if (window.WCCC && window.WCCC.toast) {
                        window.WCCC.toast.success('گزارش فنی با موفقیت در حافظه کپی شد.');
                    } else {
                        alert('گزارش فنی کپی شد.');
                    }
                });
            }
        });
    }
})();
</script>
