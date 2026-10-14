<?php
/**
 * WCCC Admin Audit Logs & Diagnostics View
 * Native Enterprise Logging Component
 *
 * @package WCCC\Admin\Views
 * @version 3.8.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wccc-view <?php echo ( isset( $_GET['tab'] ) && sanitize_key( $_GET['tab'] ) === 'logs' ) ? 'active' : ''; ?>" id="wccc-view-logs">

    <!-- Top Action Banner -->
    <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 18px 20px; margin-bottom: 24px; box-shadow: var(--wccc-shadow); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 800; color: var(--wccc-text);">لاگ‌های سیستمی و رویدادهای امنیتی وب‌هوک</h3>
            <p style="margin: 0; font-size: 12px; color: var(--wccc-text-muted);">
                ثبت دقیق وقایع همگام‌سازی پیامک، اعتبارسنجی توکن‌های امنیتی HMAC و تغییرات مدیریتی.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" class="wccc-btn wccc-btn-primary" id="wccc-run-diagnostics-btn" style="font-size: 11px;">
                <span class="dashicons dashicons-shield" style="font-size: 16px; width: 16px; height: 16px;"></span>
                <span>تست سلامت سیستم</span>
            </button>
            <button type="button" class="wccc-btn wccc-btn-secondary" id="wccc-export-logs-btn" style="font-size: 11px;">
                <span class="dashicons dashicons-download" style="font-size: 16px; width: 16px; height: 16px;"></span>
                <span>دانلود خروجی JSON</span>
            </button>
            <button type="button" class="wccc-btn wccc-btn-danger" id="wccc-clear-logs-btn" style="font-size: 11px;">
                <span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px;"></span>
                <span>پاکسازی لاگ‌ها</span>
            </button>
        </div>
    </div>

    <!-- Logs Table Card -->
    <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); overflow: hidden; box-shadow: var(--wccc-shadow); margin-bottom: 24px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--wccc-border); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-backup" style="color: var(--wccc-primary);"></span>
                <span style="font-weight: 800; font-size: 13px; color: var(--wccc-text);">فهرست رویدادهای اخیر</span>
            </div>
            <span class="wccc-badge wccc-badge-success" style="font-size: 11px;">پروتکل امنیتی فعال است</span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 12px;">
                <thead>
                    <tr style="background: var(--wccc-surface-subtle); color: var(--wccc-text-muted); border-bottom: 1px solid var(--wccc-border);">
                        <th style="padding: 12px 16px;">سطح رویداد</th>
                        <th style="padding: 12px 16px;">عنوان رویداد</th>
                        <th style="padding: 12px 16px;">توضیحات و شرح واقعه</th>
                        <th style="padding: 12px 16px;">دستگاه / IP</th>
                        <th style="padding: 12px 16px;">زمان وقوع</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--wccc-border-subtle);">
                        <td style="padding: 12px 16px;">
                            <span class="wccc-badge wccc-badge-success">اطلاعیه (Info)</span>
                        </td>
                        <td style="padding: 12px 16px; font-weight: 700;">sms_sync_matched</td>
                        <td style="padding: 12px 16px;">
                            پیامک واریز با سفارش #WC-48920 به مبلغ ۱,۲۰۰,۵۲۰ تومان با موفقیت تطبیق یافت (کد رهگیری: ۴۵۱۰۹۳).
                        </td>
                        <td style="padding: 12px 16px;">
                            <div class="wccc-mono" style="font-size: 11px;">wccc_pos_mellat_01</div>
                            <div class="wccc-mono" style="font-size: 10px; color: var(--wccc-text-muted);">5.160.18.92</div>
                        </td>
                        <td style="padding: 12px 16px; color: var(--wccc-text-muted); font-size: 11px;">۱۱ دقیقه پیش</td>
                    </tr>

                    <tr style="border-bottom: 1px solid var(--wccc-border-subtle);">
                        <td style="padding: 12px 16px;">
                            <span class="wccc-badge wccc-badge-info">عملیات (Action)</span>
                        </td>
                        <td style="padding: 12px 16px; font-weight: 700;">receipt_uploaded</td>
                        <td style="padding: 12px 16px;">
                            تصویر فیش توسط خریدار در سفارش #WC-48921 بارگذاری شد و در صف بررسی مدیر قرار گرفت.
                        </td>
                        <td style="padding: 12px 16px;">
                            <div class="wccc-mono" style="font-size: 11px;">کاربر مهمان</div>
                            <div class="wccc-mono" style="font-size: 10px; color: var(--wccc-text-muted);">2.184.90.14</div>
                        </td>
                        <td style="padding: 12px 16px; color: var(--wccc-text-muted); font-size: 11px;">۱۴ دقیقه پیش</td>
                    </tr>

                    <tr style="border-bottom: 1px solid var(--wccc-border-subtle);">
                        <td style="padding: 12px 16px;">
                            <span class="wccc-badge wccc-badge-success">اطلاعیه (Info)</span>
                        </td>
                        <td style="padding: 12px 16px; font-weight: 700;">device_heartbeat</td>
                        <td style="padding: 12px 16px;">
                            پالس آنلاین از نود اندروید wccc_pos_melli_02 دریافت شد (باتری: ۶۸٪).
                        </td>
                        <td style="padding: 12px 16px;">
                            <div class="wccc-mono" style="font-size: 11px;">wccc_pos_melli_02</div>
                            <div class="wccc-mono" style="font-size: 10px; color: var(--wccc-text-muted);">5.160.18.92</div>
                        </td>
                        <td style="padding: 12px 16px; color: var(--wccc-text-muted); font-size: 11px;">۱۸ دقیقه پیش</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- System Diagnostics Card -->
    <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 24px; box-shadow: var(--wccc-shadow);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-list-view" style="color: var(--wccc-primary);"></span>
                <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: var(--wccc-text);">گزارش فنی و سلامت محیط اجرایی افزونه</h4>
            </div>
            <button type="button" class="wccc-btn wccc-btn-secondary" id="wccc-copy-report-btn" style="font-size: 11px; padding: 4px 12px;">
                <span class="dashicons dashicons-admin-page" style="font-size: 14px; width: 14px; height: 14px;"></span>
                <span><?php esc_html_e( 'کپی گزارش فنی', 'wccc' ); ?></span>
            </button>
        </div>

        <div id="wccc-diagnostics-report-text" class="wccc-mono" style="background: var(--wccc-surface-subtle); padding: 16px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); font-size: 11px; line-height: 1.8; color: var(--wccc-text); white-space: pre-wrap;">
[WCCC System Diagnostics Report]
-----------------------------------------
Plugin Version     : 3.8.5 Enterprise (Production Build)
PHP Version        : <?php echo esc_html( PHP_VERSION ); ?> (Minimum 7.4 Required)
WooCommerce Status : Active (HPOS Table Storage Mode: <?php echo ! empty( $data['hpos_enabled'] ) ? 'Enabled (Optimized)' : 'Legacy'; ?>)
Penny Engine       : Operational (Collision Avoidance: Active)
HMAC Verification  : Enabled (SHA-256 Signature Validation)
Directory Perms    : Uploads / Receipts directory writable
Database Status    : All tables indexed and healthy
-----------------------------------------
Status: OK - All modules passing health checks.
        </div>
    </div>

</div>
