<?php
/**
 * WCCC Admin Cards View
 * Native Enterprise Bank Accounts Management Component (wccc_cards Table Driven)
 *
 * @package WCCC\Admin\Views
 * @version 3.8.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;


$table_name = \WCCC\Database\Schema::get_table_name( \WCCC\Database\Schema::TABLE_CARDS );
$cards      = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY id DESC" );
if ( ! is_array( $cards ) ) {
    $cards = array();
}
?>

<div class="wccc-view <?php echo ( isset( $_GET['tab'] ) && sanitize_key( $_GET['tab'] ) === 'cards' ) ? 'active' : ''; ?>" id="wccc-view-cards">

    <!-- Top Action Banner -->
    <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 18px 20px; margin-bottom: 24px; box-shadow: var(--wccc-shadow); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'مدیریت کارت‌ها و حساب‌های مقصد شتاب (پایگاه داده اختصاصی)', 'wccc' ); ?></h3>
            <p style="margin: 0; font-size: 12px; color: var(--wccc-text-muted);">
                <?php esc_html_e( 'اطلاعات مستقیماً در جدول اختصاصی wccc_cards ذخیره و مدیریت می‌شوند.', 'wccc' ); ?>
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" class="wccc-btn wccc-btn-secondary" id="wccc-reset-limits-btn" style="font-size: 11px;">
                <span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px;"></span>
                <span><?php esc_html_e( 'بازنشانی سقف‌های روزانه', 'wccc' ); ?></span>
            </button>
            <button type="button" class="wccc-btn wccc-btn-primary wccc-open-add-card-modal-btn" data-wccc-modal-open="wccc-modal-add-card" style="font-size: 11px;">
                <span class="dashicons dashicons-plus-alt2" style="font-size: 16px; width: 16px; height: 16px;"></span>
                <span><?php esc_html_e( 'افزودن کارت بانکی جدید', 'wccc' ); ?></span>
            </button>
        </div>
    </div>

    <!-- Metallic Cards Visual Grid -->
    <?php if ( ! empty( $cards ) ) : ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <?php foreach ( array_slice( $cards, 0, 3 ) as $c ) : 
                $num_clean   = preg_replace( '/\D/', '', (string) $c->card_number );
                $num_spaced  = trim( chunk_split( $num_clean, 4, ' ' ) );
                $is_act      = intval( $c->is_active ) === 1;
                $limit_txt   = number_format_i18n( floatval( $c->daily_limit ) );
                $today_txt   = number_format_i18n( floatval( $c->current_daily_total ) );
            ?>
                <div class="wccc-bank-card" data-card-id="<?php echo esc_attr( (string) $c->id ); ?>" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 1rem; padding: 20px; color: #fff; position: relative; overflow: hidden; box-shadow: var(--wccc-shadow-lg);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                        <div>
                            <div style="font-size: 11px; color: #94a3b8; font-weight: 600;"><?php echo esc_html( $c->bank_name ); ?></div>
                            <div style="font-size: 14px; font-weight: 800; color: #f8fafc;"><?php echo esc_html( $c->card_holder ); ?></div>
                        </div>
                        <span class="dashicons dashicons-id-alt" style="font-size: 30px; width: 30px; height: 30px; color: #38bdf8;"></span>
                    </div>
                    <div class="wccc-mono" style="font-size: 17px; font-weight: 800; letter-spacing: 2px; margin-bottom: 16px; color: #f1f5f9; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                        <?php echo esc_html( $num_spaced ); ?>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; font-size: 11px;">
                        <div>
                            <div style="color: #64748b; font-size: 9px; text-transform: uppercase;"><?php esc_html_e( 'سقف / گردش امروز', 'wccc' ); ?></div>
                            <div class="wccc-mono" style="font-size: 10px; color: #e2e8f0;"><?php echo esc_html( $today_txt . ' / ' . $limit_txt ); ?> ت</div>
                        </div>
                        <div style="text-align: left;">
                            <span class="wccc-badge <?php echo $is_act ? 'wccc-badge-success' : 'wccc-badge-danger'; ?>" style="font-size: 10px;">
                                <?php echo $is_act ? esc_html__( 'فعال', 'wccc' ) : esc_html__( 'غیرفعال', 'wccc' ); ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Bank Cards Table Card -->
    <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); overflow: hidden; box-shadow: var(--wccc-shadow); margin-bottom: 24px;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--wccc-border); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-id-alt" style="color: var(--wccc-primary);"></span>
                <span style="font-weight: 800; font-size: 13px; color: var(--wccc-text);"><?php esc_html_e( 'فهرست کارت‌های تعریف‌شده در جدول اختصاصی', 'wccc' ); ?></span>
            </div>
            <span class="wccc-badge wccc-badge-info" style="font-size: 11px;"><?php esc_html_e( 'همگام‌سازی بی‌درنگ با دیتابیس', 'wccc' ); ?></span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: right; font-size: 12px;">
                <thead>
                    <tr style="background: var(--wccc-surface-subtle); color: var(--wccc-text-muted); border-bottom: 1px solid var(--wccc-border);">
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'شناسه', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'نام بانک', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'شماره کارت (۱۶ رقمی)', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'صاحب حساب', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'شماره شبا (IBAN)', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'سقف روزانه', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'سقف تراکنش', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'واریز امروز', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px;"><?php esc_html_e( 'وضعیت فعالیت', 'wccc' ); ?></th>
                        <th style="padding: 12px 16px; text-align: center;"><?php esc_html_e( 'عملیات', 'wccc' ); ?></th>
                    </tr>
                </thead>
                <tbody id="wccc-cards-table-body">
                    <?php if ( empty( $cards ) ) : ?>
                        <tr id="wccc-cards-empty-row">
                            <td colspan="10" style="text-align: center; padding: 32px; color: var(--wccc-text-muted);">
                                <span class="dashicons dashicons-id-alt" style="font-size: 32px; width: 32px; height: 32px; display: block; margin: 0 auto 8px auto; color: var(--wccc-text-muted);"></span>
                                <?php esc_html_e( 'هیچ کارت بانکی در سیستم ثبت نشده است. روی «افزودن کارت بانکی جدید» کلیک فرمایید.', 'wccc' ); ?>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ( $cards as $card ) :
                            $clean_num    = preg_replace( '/\D/', '', (string) $card->card_number );
                            $spaced_num   = trim( chunk_split( $clean_num, 4, ' ' ) );
                            $is_active    = intval( $card->is_active ) === 1;
                            $daily_limit  = floatval( $card->daily_limit );
                            $daily_tx_limit = isset( $card->daily_tx_limit ) ? intval( $card->daily_tx_limit ) : 50;
                            $current_tx_count = isset( $card->current_daily_tx_count ) ? intval( $card->current_daily_tx_count ) : 0;
                            $daily_total  = floatval( $card->current_daily_total );
                        ?>
                            <tr data-card-id="<?php echo esc_attr( (string) $card->id ); ?>"
                                data-bank-name="<?php echo esc_attr( (string) $card->bank_name ); ?>"
                                data-card-number="<?php echo esc_attr( $clean_num ); ?>"
                                data-card-holder="<?php echo esc_attr( (string) $card->card_holder ); ?>"
                                data-iban="<?php echo esc_attr( (string) $card->iban ); ?>"
                                data-daily-limit="<?php echo esc_attr( (string) $daily_limit ); ?>"
                                data-tx-limit="<?php echo esc_attr( (string) $daily_tx_limit ); ?>"
                                data-active="<?php echo $is_active ? '1' : '0'; ?>"
                                style="border-bottom: 1px solid var(--wccc-border-subtle);">

                                <td style="padding: 12px 16px;">
                                    <span class="wccc-mono" style="font-weight: 800; color: var(--wccc-text-muted);">#<?php echo esc_html( (string) $card->id ); ?></span>
                                </td>

                                <td style="padding: 12px 16px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="width: 10px; height: 10px; border-radius: 50%; background: #006194;"></span>
                                        <span class="wccc-cell-bank" style="font-weight: 800; color: var(--wccc-text);"><?php echo esc_html( $card->bank_name ); ?></span>
                                    </div>
                                </td>

                                <td style="padding: 12px 16px;">
                                    <span class="wccc-mono wccc-cell-number" style="font-weight: 800; font-size: 13px;"><?php echo esc_html( $spaced_num ); ?></span>
                                </td>

                                <td style="padding: 12px 16px; font-weight: 700;" class="wccc-cell-holder">
                                    <?php echo esc_html( $card->card_holder ); ?>
                                </td>

                                <td style="padding: 12px 16px;">
                                    <span class="wccc-mono wccc-cell-iban" style="font-size: 11px; color: var(--wccc-text-muted);">
                                        <?php echo ! empty( $card->iban ) ? esc_html( $card->iban ) : '—'; ?>
                                    </span>
                                </td>

                                <td style="padding: 12px 16px;" class="wccc-mono wccc-cell-limit">
                                    <?php echo esc_html( number_format_i18n( $daily_limit ) ); ?> <?php esc_html_e( 'تومان', 'wccc' ); ?>
                                </td>

                                <td style="padding: 12px 16px;" class="wccc-mono wccc-cell-tx-limit">
                                    <span class="wccc-badge wccc-badge-info" style="font-size: 11px;">
                                        <?php echo esc_html( sprintf( __( '%d / %d مجاز', 'wccc' ), $current_tx_count, $daily_tx_limit ) ); ?>
                                    </span>
                                </td>

                                <td style="padding: 12px 16px;">
                                    <span class="wccc-mono" style="font-weight: 800; color: var(--wccc-primary);">
                                        <?php echo esc_html( number_format_i18n( $daily_total ) ); ?> <?php esc_html_e( 'تومان', 'wccc' ); ?>
                                    </span>
                                </td>

                                <td style="padding: 12px 16px;">
                                    <button type="button" class="wccc-btn wccc-card-toggle-btn <?php echo $is_active ? 'wccc-badge-success' : 'wccc-badge-danger'; ?>" data-card-id="<?php echo esc_attr( (string) $card->id ); ?>" data-active="<?php echo $is_active ? '1' : '0'; ?>" style="border: none; padding: 4px 10px; font-size: 11px; cursor: pointer;">
                                        <?php if ( $is_active ) : ?>
                                            <span class="wccc-pulse-dot" style="background: #10b981; margin-left: 4px;"></span>
                                            <span><?php esc_html_e( 'فعال (کلیک برای تغییر)', 'wccc' ); ?></span>
                                        <?php else : ?>
                                            <span><?php esc_html_e( 'غیرفعال (کلیک برای فعال‌سازی)', 'wccc' ); ?></span>
                                        <?php endif; ?>
                                    </button>
                                </td>

                                <td style="padding: 12px 16px; text-align: center;">
                                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                                        <button type="button" class="wccc-btn wccc-btn-secondary wccc-edit-card-btn"
                                            data-card-id="<?php echo esc_attr( (string) $card->id ); ?>"
                                            data-bank-name="<?php echo esc_attr( (string) $card->bank_name ); ?>"
                                            data-card-number="<?php echo esc_attr( $clean_num ); ?>"
                                            data-card-holder="<?php echo esc_attr( (string) $card->card_holder ); ?>"
                                            data-iban="<?php echo esc_attr( (string) $card->iban ); ?>"
                                            data-daily-limit="<?php echo esc_attr( (string) $daily_limit ); ?>"
                                            data-tx-limit="<?php echo esc_attr( (string) $daily_tx_limit ); ?>"
                                            data-active="<?php echo $is_active ? '1' : '0'; ?>"
                                            style="padding: 4px 8px; font-size: 11px;">
                                            <span class="dashicons dashicons-edit" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                            <span><?php esc_html_e( 'ویرایش', 'wccc' ); ?></span>
                                        </button>

                                        <button type="button" class="wccc-btn wccc-btn-danger wccc-delete-card-btn" data-card-id="<?php echo esc_attr( (string) $card->id ); ?>" style="padding: 4px 8px; font-size: 11px;">
                                            <span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                            <span><?php esc_html_e( 'حذف', 'wccc' ); ?></span>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
