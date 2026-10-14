<?php
/**
 * WCCC Admin Orders & Receipts View
 * Native Enterprise Orders Component with Multi-Receipt Data Binding
 *
 * @package WCCC\Admin\Views
 * @version 3.8.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$receipts_table = \WCCC\Database\Schema::get_table_name( \WCCC\Database\Schema::TABLE_RECEIPTS );

// بازیابی سفارش‌های ووکامرس در صورت وجود
$real_orders = array();
if ( function_exists( 'wc_get_orders' ) ) {
    try {
        $real_orders = wc_get_orders( array(
            'limit'   => 20,
            'orderby' => 'date',
            'order'   => 'DESC',
        ) );
    } catch ( \Exception $e ) {
        $real_orders = array();
    }
}
?>

<div class="wccc-view <?php echo ( isset( $_GET['tab'] ) && sanitize_key( $_GET['tab'] ) === 'orders' ) ? 'active' : ''; ?>" id="wccc-view-orders">

    <!-- Filters & Action Bar -->
    <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 18px 20px; margin-bottom: 20px; box-shadow: var(--wccc-shadow); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        
        <!-- Status Filter Pills -->
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <button type="button" class="wccc-btn wccc-btn-secondary active" data-order-status="all" style="font-size: 11px;">
                <span><?php esc_html_e( 'همه وضعیت‌ها', 'wccc' ); ?></span>
            </button>
            <button type="button" class="wccc-btn wccc-btn-secondary" data-order-status="pending" style="font-size: 11px;">
                <span class="wccc-pulse-dot" style="background: var(--wccc-warning); width: 6px; height: 6px;"></span>
                <span><?php esc_html_e( 'در انتظار بررسی فیش', 'wccc' ); ?></span>
            </button>
            <button type="button" class="wccc-btn wccc-btn-secondary" data-order-status="matched" style="font-size: 11px;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--wccc-success);"></span>
                <span><?php esc_html_e( 'تایید شده (پیامکی / دستی)', 'wccc' ); ?></span>
            </button>
            <button type="button" class="wccc-btn wccc-btn-secondary" data-order-status="rejected" style="font-size: 11px;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--wccc-error);"></span>
                <span><?php esc_html_e( 'رد شده / نامعتبر', 'wccc' ); ?></span>
            </button>
        </div>

        <!-- Search & Actions -->
        <div style="display: flex; align-items: center; gap: 10px; flex: 1; max-width: 480px; justify-content: flex-end;">
            <div style="position: relative; width: 100%; max-width: 320px;">
                <input type="text" id="wccc-order-search-input" placeholder="<?php esc_attr_e( 'جستجو شماره سفارش، مشتری...', 'wccc' ); ?>" style="width: 100%; padding: 8px 36px 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px; font-family: inherit;">
                <span class="dashicons dashicons-search" style="position: absolute; right: 10px; top: 8px; font-size: 18px; width: 18px; height: 18px; color: var(--wccc-text-muted); pointer-events: none;"></span>
            </div>
            <button type="button" class="wccc-btn wccc-btn-secondary" data-wccc-modal-open="wccc-modal-manual-receipt" style="white-space: nowrap; font-size: 11px;">
                <span class="dashicons dashicons-media-document" style="font-size: 16px; width: 16px; height: 16px;"></span>
                <span><?php esc_html_e( 'ثبت فیش دستی', 'wccc' ); ?></span>
            </button>
        </div>

    </div>

    <!-- Orders Table Card -->
    <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); overflow: hidden; box-shadow: var(--wccc-shadow);">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 12px;">
                <thead>
                    <tr style="background: var(--wccc-surface-subtle); color: var(--wccc-text-muted); border-bottom: 1px solid var(--wccc-border);">
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'سفارش', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'مشتری', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'حساب مقصد', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'مبلغ پایه', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'پنی انحصاری', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'مبلغ کل واریزی', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'کد رهگیری / RRN', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'وضعیت رسید', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'زمان', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px; text-align: center;"><?php esc_html_e( 'عملیات', 'wccc' ); ?></th>
                    </tr>
                </thead>
                <tbody id="wccc-orders-table-body">
                    <?php if ( ! empty( $real_orders ) ) : ?>
                        <?php foreach ( $real_orders as $wc_o ) : ?>
                            <?php
                            $o_id       = $wc_o->get_id();
                            $cust_name  = $wc_o->get_formatted_billing_full_name() ?: __( 'مشتری مهمان', 'wccc' );
                            $phone      = $wc_o->get_billing_phone() ?: '---';
                            $penny      = (float) $wc_o->get_meta( '_wccc_penny_offset' ) ?: ( (float) $wc_o->get_meta( '_wccc_penny_amount' ) ?: 0 );
                            $total_pay  = (float) $wc_o->get_total();
                            $base_pay   = max( 0.0, $total_pay - $penny );
                            $dest_card  = (string) $wc_o->get_meta( '_wccc_bank_name' ) ?: 'بانک شتاب';
                            $rrn        = (string) $wc_o->get_meta( '_wccc_tracking_code' ) ?: ( (string) $wc_o->get_meta( '_wccc_buyer_tracking_number' ) ?: '---' );
                            $status_str = $wc_o->get_status();

                            // تعداد فیش‌های ثبت شده در جدول اختصاصی
                            $rcpt_count = 0;
                            if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $receipts_table ) ) === $receipts_table ) {
                                $rcpt_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$receipts_table} WHERE order_id = %d", $o_id ) );
                            }
                            if ( 0 === $rcpt_count ) {
                                $meta_rc = (array) $wc_o->get_meta( '_wccc_receipts' );
                                $rcpt_count = ! empty( $meta_rc ) ? count( $meta_rc ) : ( $wc_o->get_meta( '_wccc_receipt_url' ) ? 1 : 0 );
                            }

                            $is_review_needed = in_array( $status_str, array( 'wccc-review', 'on-hold' ), true );
                            $is_success       = in_array( $status_str, array( 'processing', 'completed', 'wccc-paid' ), true );
                            $is_rejected      = in_array( $status_str, array( 'cancelled', 'failed' ), true );
                            ?>
                            <tr data-order-id="<?php echo esc_attr( (string) $o_id ); ?>" style="border-bottom: 1px solid var(--wccc-border-subtle);">
                                <td style="padding: 12px 16px;">
                                    <span class="wccc-mono" style="font-weight: 800; color: var(--wccc-primary);">#WC-<?php echo esc_html( (string) $o_id ); ?></span>
                                </td>
                                <td style="padding: 12px 16px;">
                                    <div style="font-weight: 700; color: var(--wccc-text);"><?php echo esc_html( $cust_name ); ?></div>
                                    <div class="wccc-mono" style="font-size: 10px; color: var(--wccc-text-muted);"><?php echo esc_html( $phone ); ?></div>
                                </td>
                                <td style="padding: 12px 16px;">
                                    <div style="font-weight: 600; color: var(--wccc-text);"><?php echo esc_html( $dest_card ); ?></div>
                                </td>
                                <td style="padding: 12px 16px;"><span class="wccc-mono"><?php echo number_format_i18n( (int) $base_pay ); ?></span> تومان</td>
                                <td style="padding: 12px 16px;">
                                    <?php if ( $penny > 0 ) : ?>
                                        <span class="wccc-badge wccc-badge-info" style="font-size: 10px;">+<?php echo number_format_i18n( (int) $penny ); ?> تومان</span>
                                    <?php else : ?>
                                        <span style="color: var(--wccc-text-muted); font-size: 11px;">ندارد</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 16px;">
                                    <span class="wccc-mono" style="font-weight: 800; color: var(--wccc-text);"><?php echo number_format_i18n( (int) $total_pay ); ?></span> تومان
                                </td>
                                <td style="padding: 12px 16px;">
                                    <span class="wccc-mono" style="font-size: 11px; color: var(--wccc-text-muted);"><?php echo esc_html( $rrn ); ?></span>
                                </td>
                                <td style="padding: 12px 16px;" class="wccc-status-cell">
                                    <?php if ( $is_review_needed ) : ?>
                                        <span class="wccc-badge wccc-badge-warning">
                                            <span class="wccc-pulse-dot" style="background: #f59e0b; width: 6px; height: 6px; margin-left: 4px;"></span>
                                            <span><?php printf( esc_html__( 'بررسی فیش (%d رسید)', 'wccc' ), max( 1, $rcpt_count ) ); ?></span>
                                        </span>
                                    <?php elseif ( $is_success ) : ?>
                                        <span class="wccc-badge wccc-badge-success"><?php esc_html_e( 'تایید شده ✓', 'wccc' ); ?></span>
                                    <?php elseif ( $is_rejected ) : ?>
                                        <span class="wccc-badge wccc-badge-danger"><?php esc_html_e( 'رد شده / لغو', 'wccc' ); ?></span>
                                    <?php else : ?>
                                        <span class="wccc-badge" style="background: var(--wccc-surface-subtle); color: var(--wccc-text-muted);"><?php echo esc_html( wc_get_order_status_name( $status_str ) ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 16px; color: var(--wccc-text-muted); font-size: 11px;">
                                    <?php echo $wc_o->get_date_created() ? esc_html( $wc_o->get_date_created()->date( 'H:i - Y/m/d' ) ) : '---'; ?>
                                </td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <button type="button" class="wccc-btn <?php echo $is_review_needed ? 'wccc-btn-primary' : 'wccc-btn-secondary'; ?> wccc-open-drawer-btn" data-order-id="<?php echo esc_attr( (string) $o_id ); ?>" style="padding: 4px 10px; font-size: 11px;">
                                        <span class="dashicons <?php echo $rcpt_count > 0 ? 'dashicons-visibility' : 'dashicons-media-document'; ?>" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                        <span><?php echo $rcpt_count > 0 ? esc_html__( 'بررسی فیش', 'wccc' ) : esc_html__( 'جزئیات', 'wccc' ); ?></span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <!-- دیتای پیش‌فرض و سناریوهای تستی برای دمو و پیشخوان -->
                        <tr data-order-id="1042" style="border-bottom: 1px solid var(--wccc-border-subtle);">
                            <td style="padding: 12px 16px;">
                                <span class="wccc-mono" style="font-weight: 800; color: var(--wccc-primary);">#WC-48921</span>
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 700; color: var(--wccc-text);">امیرحسین رضایی</div>
                                <div class="wccc-mono" style="font-size: 10px; color: var(--wccc-text-muted);">۰۹۱۲•••۴۵۸۹</div>
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 600; color: var(--wccc-text);">بانک تجارت</div>
                                <div class="wccc-mono" style="font-size: 10px; color: var(--wccc-text-muted);">(۴۴۱۲)</div>
                            </td>
                            <td style="padding: 12px 16px;"><span class="wccc-mono">۲۵۰,۰۰۰</span> تومان</td>
                            <td style="padding: 12px 16px;">
                                <span class="wccc-badge wccc-badge-info" style="font-size: 10px;">+۳۴۰ تومان</span>
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="wccc-mono" style="font-weight: 800; color: var(--wccc-text);">۲۵۰,۳۴۰</span> تومان
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="wccc-mono" style="font-size: 11px; color: var(--wccc-text-muted);">۹۸۲۳۰۴۸۱۲۹۵</span>
                            </td>
                            <td style="padding: 12px 16px;" class="wccc-status-cell">
                                <span class="wccc-badge wccc-badge-warning">
                                    <span class="wccc-pulse-dot" style="background: #f59e0b; width: 6px; height: 6px; margin-left: 4px;"></span>
                                    <span>در حال بررسی رسید (۲ فیش)</span>
                                </span>
                            </td>
                            <td style="padding: 12px 16px; color: var(--wccc-text-muted); font-size: 11px;">۸ دقیقه پیش</td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <button type="button" class="wccc-btn wccc-btn-primary wccc-open-drawer-btn" data-order-id="1042" style="padding: 4px 10px; font-size: 11px;">
                                    <span class="dashicons dashicons-visibility" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                    <span>بررسی فیش</span>
                                </button>
                            </td>
                        </tr>

                        <tr data-order-id="1041" style="border-bottom: 1px solid var(--wccc-border-subtle);">
                            <td style="padding: 12px 16px;">
                                <span class="wccc-mono" style="font-weight: 800; color: var(--wccc-primary);">#WC-48920</span>
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 700; color: var(--wccc-text);">سارا میرزایی</div>
                                <div class="wccc-mono" style="font-size: 10px; color: var(--wccc-text-muted);">۰۹۳۵•••۱۲۹۸</div>
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 600; color: var(--wccc-text);">بانک سامان</div>
                                <div class="wccc-mono" style="font-size: 10px; color: var(--wccc-text-muted);">(۵۴۱۲)</div>
                            </td>
                            <td style="padding: 12px 16px;"><span class="wccc-mono">۱,۲۰۰,۰۰۰</span> تومان</td>
                            <td style="padding: 12px 16px;">
                                <span class="wccc-badge wccc-badge-info" style="font-size: 10px;">+۵۲۰ تومان</span>
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="wccc-mono" style="font-weight: 800; color: var(--wccc-text);">۱,۲۰۰,۵۲۰</span> تومان
                            </td>
                            <td style="padding: 12px 16px;">
                                <span class="wccc-mono" style="font-size: 11px; color: var(--wccc-text-muted);">۴۵۱۰۹۳</span>
                            </td>
                            <td style="padding: 12px 16px;" class="wccc-status-cell">
                                <span class="wccc-badge wccc-badge-success">تایید پیامکی</span>
                            </td>
                            <td style="padding: 12px 16px; color: var(--wccc-text-muted); font-size: 11px;">۱۱ دقیقه پیش</td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <button type="button" class="wccc-btn wccc-btn-secondary wccc-open-drawer-btn" data-order-id="1041" style="padding: 4px 10px; font-size: 11px;">
                                    <span class="dashicons dashicons-media-document" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                    <span>جزئیات</span>
                                </button>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
