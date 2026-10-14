<?php
/**
 * WCCC Admin Dashboard View
 * Native Enterprise Dashboard Component
 *
 * @package WCCC\Admin\Views
 * @version 3.8.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$hpos_status_text  = ! empty( $data['hpos_enabled'] ) ? __( 'فعال و منطبق بر جداول مستقل ووکامرس (HPOS)', 'wccc' ) : __( 'غیرفعال (استفاده از جدول سنتی wp_posts)', 'wccc' );
$hpos_status_class = ! empty( $data['hpos_enabled'] ) ? 'wccc-badge-success' : 'wccc-badge-warning';
?>

<?php $wccc_is_dashboard_active = ( ! isset( $_GET['tab'] ) || sanitize_key( $_GET['tab'] ) === 'dashboard' ); ?>
<div class="wccc-view <?php echo $wccc_is_dashboard_active ? 'active' : ''; ?>" id="wccc-view-dashboard">

    <!-- Hero Status Banner -->
    <div style="background: linear-gradient(135deg, var(--wccc-surface) 0%, var(--wccc-surface-subtle) 100%); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 24px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; box-shadow: var(--wccc-shadow);">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 52px; height: 52px; border-radius: var(--wccc-radius-md); background: linear-gradient(135deg, #006194 0%, #0ea5e9 100%); display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 8px 16px rgba(0, 97, 148, 0.3);">
                <span class="dashicons dashicons-chart-area" style="font-size: 28px; width: 28px; height: 28px;"></span>
            </div>
            <div>
                <h2 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 800; color: var(--wccc-text);">مرکز مانیتورینگ زنده کارت‌به‌کارت</h2>
                <div style="display: flex; align-items: center; gap: 12px; font-size: 12px; color: var(--wccc-text-muted);">
                    <span>موتور هوشمند پنی‌انجین: <strong style="color: var(--wccc-success);">فعال و پایدار</strong></span>
                    <span>•</span>
                    <span>معماری پایگاه داده: <span class="wccc-badge <?php echo esc_attr( $hpos_status_class ); ?>" style="font-size: 10px;"><?php echo esc_html( $hpos_status_text ); ?></span></span>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" class="wccc-btn wccc-btn-secondary" data-wccc-modal-open="wccc-modal-test-webhook">
                <span class="dashicons dashicons-smartphone"></span>
                <span>تست وب‌هوک پیامک</span>
            </button>
            <button type="button" class="wccc-btn wccc-btn-primary" data-wccc-modal-open="wccc-modal-add-card">
                <span class="dashicons dashicons-plus-alt2"></span>
                <span>افزودن کارت بانکی</span>
            </button>
        </div>
    </div>

    <!-- 4 High-Impact KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px;">

        <!-- KPI 1: Today Volume -->
        <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 20px; box-shadow: var(--wccc-shadow);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 12px; font-weight: 700; color: var(--wccc-text-muted);">گردش مالی امروز</span>
                <span style="width: 36px; height: 36px; border-radius: 8px; background: rgba(0, 97, 148, 0.1); color: var(--wccc-primary); display: flex; align-items: center; justify-content: center;">
                    <span class="dashicons dashicons-money-alt" style="font-size: 20px; width: 20px; height: 20px;"></span>
                </span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 8px;">
                <span class="wccc-mono" style="font-size: 24px; font-weight: 900; color: var(--wccc-text);">۲۴,۸۵۰,۰۰۰</span>
                <span style="font-size: 12px; color: var(--wccc-text-muted);">تومان</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--wccc-success);">
                <span class="dashicons dashicons-chart-line" style="font-size: 16px; width: 16px; height: 16px;"></span>
                <span>۱۸٪ افزایش نسبت به روز گذشته</span>
            </div>
        </div>

        <!-- KPI 2: Auto Match Rate -->
        <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 20px; box-shadow: var(--wccc-shadow);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 12px; font-weight: 700; color: var(--wccc-text-muted);">تطبیق خودکار پنی‌انجین</span>
                <span style="width: 36px; height: 36px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); color: var(--wccc-success); display: flex; align-items: center; justify-content: center;">
                    <span class="dashicons dashicons-performance" style="font-size: 20px; width: 20px; height: 20px;"></span>
                </span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 8px;">
                <span class="wccc-mono" style="font-size: 24px; font-weight: 900; color: var(--wccc-success);">۹۸.۲٪</span>
                <span style="font-size: 12px; color: var(--wccc-text-muted);">(۴۲ سفارش)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--wccc-text-muted);">
                <span class="wccc-pulse-dot" style="background: #10b981;"></span>
                <span>بدون دخالت دست و تایید در لحظه</span>
            </div>
        </div>

        <!-- KPI 3: Active Bank Cards -->
        <?php
        global $wpdb;
        $wccc_cards_table = \WCCC\Database\Schema::get_table_name( \WCCC\Database\Schema::TABLE_CARDS );
        $wccc_card_stats  = $wpdb->get_row( "SELECT COUNT(*) as active_count, COALESCE(SUM(daily_limit), 0) as total_limit FROM {$wccc_cards_table} WHERE is_active = 1" );
        $wccc_active_cards = ! empty( $wccc_card_stats ) ? (int) $wccc_card_stats->active_count : 0;
        $wccc_total_limit  = ! empty( $wccc_card_stats ) ? (float) $wccc_card_stats->total_limit : 0.0;
        ?>
        <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 20px; box-shadow: var(--wccc-shadow);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 12px; font-weight: 700; color: var(--wccc-text-muted);"><?php esc_html_e( 'حساب‌های شتاب فعال', 'wccc' ); ?></span>
                <span style="width: 36px; height: 36px; border-radius: 8px; background: rgba(245, 158, 11, 0.1); color: var(--wccc-warning); display: flex; align-items: center; justify-content: center;">
                    <span class="dashicons dashicons-bank" style="font-size: 20px; width: 20px; height: 20px;"></span>
                </span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 8px;">
                <span class="wccc-mono" id="wccc-kpi-active-cards-count" style="font-size: 24px; font-weight: 900; color: var(--wccc-text);">
                    <?php
                    /* translators: %d: number of active cards */
                    echo esc_html( sprintf( _n( '%d کارت', '%d کارت', $wccc_active_cards, 'wccc' ), $wccc_active_cards ) );
                    ?>
                </span>
                <span style="font-size: 12px; color: var(--wccc-text-muted);"><?php esc_html_e( 'در گردش', 'wccc' ); ?></span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--wccc-text-muted);">
                <span id="wccc-kpi-active-cards-limit"><?php echo esc_html( sprintf( __( 'سقف ظرفیت روزانه: %s تومان', 'wccc' ), number_format_i18n( $wccc_total_limit ) ) ); ?></span>
            </div>
        </div>
        <!-- KPI 4: Connected Android Nodes -->
        <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 20px; box-shadow: var(--wccc-shadow);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 12px; font-weight: 700; color: var(--wccc-text-muted);">نودهای اندروید برخط</span>
                <span style="width: 36px; height: 36px; border-radius: 8px; background: rgba(139, 92, 246, 0.1); color: var(--wccc-purple); display: flex; align-items: center; justify-content: center;">
                    <span class="dashicons dashicons-smartphone" style="font-size: 20px; width: 20px; height: 20px;"></span>
                </span>
            </div>
            <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 8px;">
                <span class="wccc-mono" style="font-size: 24px; font-weight: 900; color: var(--wccc-text);">۲ دستگاه</span>
                <span class="wccc-badge wccc-badge-success" style="font-size: 10px;">آنلاین</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--wccc-text-muted);">
                <span>میانگین تاخیر ارسال پیامک: ۲۴ میلی‌ثانیه</span>
            </div>
        </div>

    </div>

    <!-- Active Cards Dynamic Compact Section -->
    <?php
    $wccc_dashboard_active_cards = $wpdb->get_results( "SELECT * FROM {$wccc_cards_table} WHERE is_active = 1 ORDER BY id DESC" );
    if ( ! is_array( $wccc_dashboard_active_cards ) ) {
        $wccc_dashboard_active_cards = array();
    }
    ?>
    <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); overflow: hidden; box-shadow: var(--wccc-shadow); margin-bottom: 24px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--wccc-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-id-alt" style="color: var(--wccc-primary);"></span>
                <h3 style="margin: 0; font-size: 14px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'کارت‌های مقصد فعال در چرخه سفارشات (پویا)', 'wccc' ); ?></h3>
                <span class="wccc-badge wccc-badge-success" id="wccc-dashboard-cards-badge" style="font-size: 10px;">
                    <?php echo esc_html( sprintf( __( '%d حساب فعال', 'wccc' ), count( $wccc_dashboard_active_cards ) ) ); ?>
                </span>
            </div>
            <button type="button" class="wccc-btn wccc-btn-secondary" data-wccc-tab="cards" style="font-size: 11px; padding: 4px 12px;">
                <span><?php esc_html_e( 'مدیریت کامل کارت‌ها', 'wccc' ); ?></span>
                <span class="dashicons dashicons-arrow-left-alt2" style="font-size: 14px; width: 14px; height: 14px;"></span>
            </button>
        </div>

        <div id="wccc-dashboard-cards-container">
        <?php if ( empty( $wccc_dashboard_active_cards ) ) : ?>
            <div style="text-align: center; padding: 36px 20px; color: var(--wccc-text-muted);" id="wccc-dashboard-cards-empty">
                <span class="dashicons dashicons-id-alt" style="font-size: 36px; width: 36px; height: 36px; display: block; margin: 0 auto 8px auto; color: var(--wccc-text-muted);"></span>
                <p style="margin: 0 0 12px 0; font-size: 13px; font-weight: 700;"><?php esc_html_e( 'هیچ کارت بانکی فعالی در سامانه تعریف نشده است.', 'wccc' ); ?></p>
                <button type="button" class="wccc-btn wccc-btn-primary" data-wccc-modal-open="wccc-modal-add-card" style="font-size: 11px;">
                    <span class="dashicons dashicons-plus-alt2" style="font-size: 14px; width: 14px; height: 14px;"></span>
                    <span><?php esc_html_e( 'افزودن کارت بانکی جدید', 'wccc' ); ?></span>
                </button>
            </div>
        <?php else : ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 12px;">
                    <thead>
                        <tr style="background: var(--wccc-surface-subtle); color: var(--wccc-text-muted); border-bottom: 1px solid var(--wccc-border);">
                            <th style="padding: 10px 16px;"><?php esc_html_e( 'بانک', 'wccc' ); ?></th>
                            <th style="padding: 10px 16px;"><?php esc_html_e( 'شماره کارت', 'wccc' ); ?></th>
                            <th style="padding: 10px 16px;"><?php esc_html_e( 'صاحب حساب', 'wccc' ); ?></th>
                            <th style="padding: 10px 16px;"><?php esc_html_e( 'سقف روزانه', 'wccc' ); ?></th>
                            <th style="padding: 10px 16px;"><?php esc_html_e( 'سقف تراکنش', 'wccc' ); ?></th>
                            <th style="padding: 10px 16px;"><?php esc_html_e( 'واریز امروز', 'wccc' ); ?></th>
                            <th style="padding: 10px 16px; text-align: center;"><?php esc_html_e( 'وضعیت', 'wccc' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $wccc_dashboard_active_cards as $d_card ) :
                            $clean_d_num  = preg_replace( '/\D/', '', (string) $d_card->card_number );
                            $spaced_d_num = trim( chunk_split( $clean_d_num, 4, ' ' ) );
                            $d_limit      = floatval( $d_card->daily_limit );
                            $d_tx_limit   = isset( $d_card->daily_tx_limit ) ? intval( $d_card->daily_tx_limit ) : 50;
                            $d_tx_count   = isset( $d_card->current_daily_tx_count ) ? intval( $d_card->current_daily_tx_count ) : 0;
                            $d_total      = floatval( $d_card->current_daily_total );
                        ?>
                            <tr style="border-bottom: 1px solid var(--wccc-border-subtle);">
                                <td style="padding: 10px 16px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                                        <span style="font-weight: 800; color: var(--wccc-text);"><?php echo esc_html( $d_card->bank_name ); ?></span>
                                    </div>
                                </td>
                                <td style="padding: 10px 16px;">
                                    <span class="wccc-mono" style="font-weight: 800; font-size: 13px; color: var(--wccc-text);"><?php echo esc_html( $spaced_d_num ); ?></span>
                                </td>
                                <td style="padding: 10px 16px; font-weight: 700; color: var(--wccc-text);">
                                    <?php echo esc_html( $d_card->card_holder ); ?>
                                </td>
                                <td style="padding: 10px 16px;" class="wccc-mono">
                                    <?php echo esc_html( number_format_i18n( $d_limit ) ); ?> <?php esc_html_e( 'تومان', 'wccc' ); ?>
                                </td>
                                <td style="padding: 10px 16px;" class="wccc-mono">
                                    <span class="wccc-badge wccc-badge-info" style="font-size: 11px;">
                                        <?php echo esc_html( sprintf( __( '%d / %d مجاز', 'wccc' ), $d_tx_count, $d_tx_limit ) ); ?>
                                    </span>
                                </td>
                                <td style="padding: 10px 16px;">
                                    <span class="wccc-mono" style="font-weight: 800; color: var(--wccc-primary);">
                                        <?php echo esc_html( number_format_i18n( $d_total ) ); ?> <?php esc_html_e( 'تومان', 'wccc' ); ?>
                                    </span>
                                </td>
                                <td style="padding: 10px 16px; text-align: center;">
                                    <span class="wccc-badge wccc-badge-success" style="font-size: 10px;">
                                        <span class="wccc-pulse-dot" style="background: #10b981; margin-left: 4px;"></span>
                                        <?php esc_html_e( 'فعال در تسویه', 'wccc' ); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <!-- Live Recent Orders Card -->
    <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); overflow: hidden; box-shadow: var(--wccc-shadow);">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--wccc-border); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-clock" style="color: var(--wccc-primary);"></span>
                <h3 style="margin: 0; font-size: 14px; font-weight: 800; color: var(--wccc-text);">آخرین فیش‌ها و سفارشات منتظر بررسی</h3>
            </div>
            <button type="button" class="wccc-btn wccc-btn-secondary" data-wccc-tab="orders" style="font-size: 11px; padding: 4px 12px;">
                <span>مشاهده تمام سفارشات</span>
                <span class="dashicons dashicons-arrow-left-alt2" style="font-size: 14px; width: 14px; height: 14px;"></span>
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 12px;">
                <thead>
                    <tr style="background: var(--wccc-surface-subtle); color: var(--wccc-text-muted); border-bottom: 1px solid var(--wccc-border);">
                        <th style="padding: 12px 16px;">شناسه سفارش</th>
                        <th style="padding: 12px 16px;">مشتری</th>
                        <th style="padding: 12px 16px;">مبلغ فاکتور</th>
                        <th style="padding: 12px 16px;">پنی انحصاری</th>
                        <th style="padding: 12px 16px;">مبلغ کل واریزی</th>
                        <th style="padding: 12px 16px;">وضعیت</th>
                        <th style="padding: 12px 16px; text-align: center;">عملیات بررسی</th>
                    </tr>
                </thead>
                <tbody>
                    <tr data-order-id="1042" style="border-bottom: 1px solid var(--wccc-border-subtle);">
                        <td style="padding: 12px 16px;"><span class="wccc-mono" style="font-weight: 800; color: var(--wccc-primary);">#WC-48921</span></td>
                        <td style="padding: 12px 16px; font-weight: 700;">امیرحسین رضایی</td>
                        <td style="padding: 12px 16px;"><span class="wccc-mono">۲۵۰,۰۰۰</span> تومان</td>
                        <td style="padding: 12px 16px;"><span class="wccc-badge wccc-badge-info" style="font-size: 11px;">+۳۴۰ تومان</span></td>
                        <td style="padding: 12px 16px;"><span class="wccc-mono" style="font-weight: 800; color: var(--wccc-text);">۲۵۰,۳۴۰ تومان</span></td>
                        <td style="padding: 12px 16px;" class="wccc-status-cell">
                            <span class="wccc-badge wccc-badge-warning">در حال بررسی فیش</span>
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <button type="button" class="wccc-btn wccc-btn-primary wccc-open-drawer-btn" data-order-id="1042" style="padding: 4px 12px; font-size: 11px;">
                                <span class="dashicons dashicons-visibility" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                <span>مشاهده فیش</span>
                            </button>
                        </td>
                    </tr>
                    <tr data-order-id="1041" style="border-bottom: 1px solid var(--wccc-border-subtle);">
                        <td style="padding: 12px 16px;"><span class="wccc-mono" style="font-weight: 800; color: var(--wccc-primary);">#WC-48920</span></td>
                        <td style="padding: 12px 16px; font-weight: 700;">سارا میرزایی</td>
                        <td style="padding: 12px 16px;"><span class="wccc-mono">۱,۲۰۰,۰۰۰</span> تومان</td>
                        <td style="padding: 12px 16px;"><span class="wccc-badge wccc-badge-info" style="font-size: 11px;">+۵۲۰ تومان</span></td>
                        <td style="padding: 12px 16px;"><span class="wccc-mono" style="font-weight: 800; color: var(--wccc-text);">۱,۲۰۰,۵۲۰ تومان</span></td>
                        <td style="padding: 12px 16px;" class="wccc-status-cell">
                            <span class="wccc-badge wccc-badge-success">تایید پیامکی</span>
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <button type="button" class="wccc-btn wccc-btn-secondary wccc-open-drawer-btn" data-order-id="1041" style="padding: 4px 12px; font-size: 11px;">
                                <span class="dashicons dashicons-media-document" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                <span>جزئیات</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
