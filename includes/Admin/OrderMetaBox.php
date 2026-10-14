<?php
/**
 * جعبه متای سفارشات ووکامرس برای نمایش اطلاعات پرداخت WCCC
 *
 * @package   WCCC
 * @author    تیم فنی WCCC
 * @license   GPL-2.0-or-later
 */

declare(strict_types=1);

namespace WCCC\Admin;

use WC_Order;

class OrderMetaBox {

    /**
     * راه‌اندازی و ثبت هوک‌های جعبه متا در پیشخوان وردپرس
     */
    public static function init(): void {
        add_action( 'add_meta_boxes', array( self::class, 'register_meta_box' ) );
    }

    /**
     * ثبت متاباکس در هر دو حالت متداول پست و HPOS (Custom Order Tables)
     */
    public static function register_meta_box(): void {
        $screens = array( 'shop_order' );

        // پشتیبانی از صفحه سفارشات HPOS در ووکامرس جدید
        if ( function_exists( 'wc_get_page_screen_id' ) ) {
            $hpos_screen = wc_get_page_screen_id( 'shop-order' );
            if ( $hpos_screen ) {
                $screens[] = $hpos_screen;
            }
        }
        $screens[] = 'woocommerce_page_wc-orders';

        $screens = array_unique( $screens );

        foreach ( $screens as $screen ) {
            add_meta_box(
                'wccc_order_payment_info',
                __( 'اطلاعات پرداخت کارت به کارت (WCCC)', 'wccc' ),
                array( self::class, 'render_meta_box' ),
                $screen,
                'side',
                'high'
            );
        }
    }

    /**
     * رندر محتوای جعبه متا در صفحه ویرایش سفارش
     *
     * @param \WP_Post|WC_Order $post_or_order_object شیء سفارش یا پست وردپرس
     */
    public static function render_meta_box( $post_or_order_object ): void {
        $order = $post_or_order_object instanceof WC_Order ? $post_or_order_object : wc_get_order( $post_or_order_object->ID );
        if ( ! $order ) {
            echo '<p style="color:#64748b; font-size:12px;">' . esc_html__( 'اطلاعات سفارش در دسترس نیست.', 'wccc' ) . '</p>';
            return;
        }

        $payment_mode = (string) $order->get_meta( '_wccc_payment_mode' );
        if ( empty( $payment_mode ) && 'wccc_card_to_card' !== $order->get_payment_method() ) {
            echo '<p style="color:#64748b; font-size:12px; margin:0;">' . esc_html__( 'این سفارش با درگاه کارت به کارت WCCC ثبت نشده است.', 'wccc' ) . '</p>';
            return;
        }

        $penny_amount = (float) $order->get_meta( '_wccc_penny_amount' );
        if ( $penny_amount <= 0 ) {
            $penny_amount = (float) $order->get_meta( '_wccc_penny_offset' );
        }

        $card_last4   = (string) $order->get_meta( '_wccc_card_last4' );
        if ( empty( $card_last4 ) ) {
            $card_last4 = (string) $order->get_meta( '_wccc_buyer_card_last4' );
        }

        $tracking_code = (string) $order->get_meta( '_wccc_tracking_code' );
        if ( empty( $tracking_code ) ) {
            $tracking_code = (string) $order->get_meta( '_wccc_buyer_tracking_number' );
        }

        // دریافت و ساختاردهی فیش‌های واریزی (پشتیبانی از چند فیش و سازگاری با سفارش‌های قدیمی)
        $raw_receipts = $order->get_meta( '_wccc_receipts' );
        $receipts_list = array();

        if ( is_array( $raw_receipts ) && ! empty( $raw_receipts ) ) {
            foreach ( $raw_receipts as $item ) {
                if ( ! is_array( $item ) ) {
                    continue;
                }
                $item_url = ! empty( $item['url'] ) ? (string) $item['url'] : ( ! empty( $item['file_url'] ) ? (string) $item['file_url'] : '' );
                if ( empty( $item_url ) ) {
                    continue;
                }
                $is_pdf = ! empty( $item['is_pdf'] ) || strtolower( (string) pathinfo( wp_parse_url( $item_url, PHP_URL_PATH ) ?? '', PATHINFO_EXTENSION ) ) === 'pdf';
                $receipts_list[] = array(
                    'url'         => $item_url,
                    'file_name'   => ! empty( $item['file_name'] ) ? (string) $item['file_name'] : wp_basename( $item_url ),
                    'file_path'   => ! empty( $item['file_path'] ) ? (string) $item['file_path'] : '',
                    'file_hash'   => ! empty( $item['file_hash'] ) ? (string) $item['file_hash'] : '',
                    'is_pdf'      => $is_pdf,
                    'uploaded_at' => ! empty( $item['uploaded_at'] ) ? (string) $item['uploaded_at'] : '',
                );
            }
        }

        // بررسی فال‌بک تک‌فیش قدیمی (_wccc_receipt_url یا _wccc_receipt_attachment_id)
        $legacy_receipt_url = (string) $order->get_meta( '_wccc_receipt_url' );
        $attachment_id      = absint( $order->get_meta( '_wccc_receipt_attachment_id' ) );
        if ( ! $legacy_receipt_url && $attachment_id > 0 ) {
            $legacy_receipt_url = wp_get_attachment_url( $attachment_id );
        }
        if ( ! empty( $legacy_receipt_url ) ) {
            $already_exists = false;
            foreach ( $receipts_list as $existing_rcpt ) {
                if ( $existing_rcpt['url'] === $legacy_receipt_url ) {
                    $already_exists = true;
                    break;
                }
            }
            if ( ! $already_exists ) {
                $is_pdf = strtolower( (string) pathinfo( wp_parse_url( $legacy_receipt_url, PHP_URL_PATH ) ?? '', PATHINFO_EXTENSION ) ) === 'pdf';
                array_unshift( $receipts_list, array(
                    'url'         => $legacy_receipt_url,
                    'file_name'   => wp_basename( $legacy_receipt_url ),
                    'file_path'   => '',
                    'file_hash'   => (string) $order->get_meta( '_wccc_receipt_hash' ),
                    'is_pdf'      => $is_pdf,
                    'uploaded_at' => '',
                ) );
            }
        }

        $receipt_url = ! empty( $receipts_list ) ? $receipts_list[0]['url'] : '';

        $expires_at      = (int) $order->get_meta( '_wccc_expires_at' );
        $currency        = $order->get_currency();
        $fallback_reason = (string) $order->get_meta( '_wccc_fallback_reason' );
        $mode_history    = (array) $order->get_meta( '_wccc_mode_history' );
        $total_amount    = (float) $order->get_total();

        $currency_symbol = get_woocommerce_currency_symbol();
        $currency_code   = strtoupper( (string) $currency );
        $is_toman        = in_array( $currency_code, array( 'IRT', 'TOMAN', 'TMN' ), true ) || 'تومان' === $currency_symbol;
        $rial_equivalent = $is_toman ? ( $total_amount * 10 ) : 0;

        $mode_title = 'instant_auto' === $payment_mode
            ? __( 'تأیید آنی و هوشمند (پنی) ⚡', 'wccc' )
            : __( 'واریز عادی با فیش 📝', 'wccc' );
        ?>
        <div class="wccc-admin-meta-box" style="direction: rtl; font-size: 13px; line-height: 1.8;">
            <div style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0;">
                <span style="color: #64748b; display: block; font-size: 11px;"><?php esc_html_e( 'شیوه پرداخت جاری:', 'wccc' ); ?></span>
                <strong style="color: <?php echo 'instant_auto' === $payment_mode ? '#059669' : '#0284c7'; ?>;">
                    <?php echo esc_html( $mode_title ); ?>
                </strong>
            </div>

            <?php if ( 'instant_expired' === $fallback_reason || in_array( 'instant_auto', $mode_history, true ) && 'manual_receipt' === $payment_mode ) : ?>
                <div style="margin-bottom: 10px; padding: 8px 10px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;">
                    <span style="color: #92400e; display: block; font-size: 11px; font-weight: bold;">⚠️ <?php esc_html_e( 'گزارش حسابرسی فال‌بک (Audit Trail):', 'wccc' ); ?></span>
                    <span style="font-size: 12px; color: #b45309; display: block; line-height: 1.5;">
                        <?php esc_html_e( 'مهلت پرداخت آنی منقضی شده و سفارش به حالت آپلود فیش دستی منتقل شده است. مبلغ پنی‌دار سفارش در فاکتور حفظ گردید.', 'wccc' ); ?>
                    </span>
                </div>
            <?php endif; ?>

            <div style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0;">
                <span style="color: #64748b; display: block; font-size: 11px;"><?php esc_html_e( 'مبلغ فاکتور سفارش:', 'wccc' ); ?></span>
                <strong style="font-family: monospace; font-size: 14px; color: #0f172a;">
                    <?php echo number_format_i18n( (int) $total_amount ) . ' ' . esc_html( $currency_symbol ); ?>
                </strong>
                <?php if ( $is_toman ) : ?>
                    <span style="display: block; font-size: 11px; color: #d97706; font-weight: bold; margin-top: 2px;">
                        (<?php esc_html_e( 'مبالغ به تومان است', 'wccc' ); ?> — <?php esc_html_e( 'معادل ریال:', 'wccc' ); ?> <?php echo number_format_i18n( (int) $rial_equivalent ); ?> <?php esc_html_e( 'ریال', 'wccc' ); ?>)
                    </span>
                <?php endif; ?>
            </div>

            <div style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0;">
                <span style="color: #64748b; display: block; font-size: 11px;"><?php esc_html_e( 'مبلغ شناسه پنی:', 'wccc' ); ?></span>
                <strong style="font-family: monospace; font-size: 14px; color: #0f172a;">
                    <?php echo $penny_amount > 0 ? '+' . number_format_i18n( (int) $penny_amount ) . ' ' . esc_html( $currency_symbol ) : esc_html__( 'ندارد (صفر)', 'wccc' ); ?>
                </strong>
            </div>

            <div style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0;">
                <span style="color: #64748b; display: block; font-size: 11px;"><?php esc_html_e( '۴ رقم آخر شماره کارت مشتری:', 'wccc' ); ?></span>
                <?php if ( ! empty( $card_last4 ) ) : ?>
                    <span style="display: inline-block; background: #f1f5f9; padding: 2px 8px; border-radius: 6px; font-family: monospace; font-size: 14px; font-weight: bold; letter-spacing: 2px; color: #1e293b;">
                        <?php echo esc_html( $card_last4 ); ?>
                    </span>
                <?php else : ?>
                    <span style="color: #94a3b8; font-style: italic;"><?php esc_html_e( 'ثبت نشده', 'wccc' ); ?></span>
                <?php endif; ?>
            </div>

            <div style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0;">
                <span style="color: #64748b; display: block; font-size: 11px;"><?php esc_html_e( 'کد پیگیری / ارجاع:', 'wccc' ); ?></span>
                <?php if ( ! empty( $tracking_code ) ) : ?>
                    <span style="font-family: monospace; font-size: 13px; font-weight: bold; color: #334155;">
                        <?php echo esc_html( $tracking_code ); ?>
                    </span>
                <?php else : ?>
                    <span style="color: #94a3b8; font-style: italic;"><?php esc_html_e( 'ثبت نشده', 'wccc' ); ?></span>
                <?php endif; ?>
            </div>

            <?php if ( $expires_at > 0 ) : ?>
                <div style="margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0;">
                    <span style="color: #64748b; display: block; font-size: 11px;"><?php esc_html_e( 'مهلت واریز (انقضا):', 'wccc' ); ?></span>
                    <span style="font-size: 12px; color: <?php echo $expires_at > time() ? '#059669' : '#e11d48'; ?>;">
                        <?php echo esc_html( date_i18n( 'Y/m/d H:i:s', $expires_at ) ); ?>
                        <?php if ( $expires_at <= time() ) : ?>
                            <span style="font-weight: bold; color: #e11d48;">(<?php esc_html_e( 'منقضی شده', 'wccc' ); ?>)</span>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endif; ?>

            <div>
                <span style="color: #64748b; display: block; font-size: 11px; margin-bottom: 6px;">
                    <?php echo count( $receipts_list ) > 1 ? esc_html__( 'فیش‌های واریزی:', 'wccc' ) : esc_html__( 'تصویر فیش واریزی:', 'wccc' ); ?>
                </span>
                <?php if ( ! empty( $receipts_list ) ) : ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ( $receipts_list as $index => $rcpt ) : ?>
                            <?php
                            $rcpt_url  = esc_url( $rcpt['url'] );
                            $rcpt_name = esc_html( $rcpt['file_name'] );
                            $is_pdf    = ! empty( $rcpt['is_pdf'] );
                            $badge_num = count( $receipts_list ) > 1 ? sprintf( __( 'فیش شماره %d', 'wccc' ), $index + 1 ) : '';
                            ?>
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; text-align: center;">
                                <?php if ( $badge_num ) : ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; font-size: 11px; color: #475569;">
                                        <strong><?php echo esc_html( $badge_num ); ?></strong>
                                        <?php if ( ! empty( $rcpt['uploaded_at'] ) ) : ?>
                                            <span style="direction: ltr; font-family: monospace; font-size: 10px; color: #94a3b8;"><?php echo esc_html( $rcpt['uploaded_at'] ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ( $is_pdf ) : ?>
                                    <!-- نمایش کارت اختصاصی برای اسناد PDF -->
                                    <div style="padding: 12px 6px; background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 6px; margin-bottom: 8px;">
                                        <div style="font-size: 28px; line-height: 1; margin-bottom: 6px;">📄</div>
                                        <div style="font-weight: bold; font-size: 12px; color: #b91c1c; margin-bottom: 2px;">
                                            <?php esc_html_e( 'سند فیش در قالب PDF', 'wccc' ); ?>
                                        </div>
                                        <div style="font-family: monospace; font-size: 11px; color: #64748b; direction: ltr; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%;">
                                            <?php echo $rcpt_name; ?>
                                        </div>
                                    </div>
                                    <a href="<?php echo $rcpt_url; ?>" target="_blank" rel="noopener noreferrer" class="button button-small" style="width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                                        <span>📥</span> <?php esc_html_e( 'مشاهده / دانلود فایل PDF', 'wccc' ); ?>
                                    </a>
                                <?php else : ?>
                                    <!-- نمایش بندانگشتی برای تصاویر -->
                                    <a href="<?php echo $rcpt_url; ?>" target="_blank" rel="noopener noreferrer" style="display: block; background: #ffffff; padding: 4px; border-radius: 6px; border: 1px solid #f1f5f9;">
                                        <img src="<?php echo $rcpt_url; ?>" alt="<?php esc_attr_e( 'فیش واریزی', 'wccc' ); ?>" style="max-width: 100%; max-height: 150px; border-radius: 4px; object-fit: contain; display: block; margin: 0 auto;" />
                                    </a>
                                    <a href="<?php echo $rcpt_url; ?>" target="_blank" rel="noopener noreferrer" class="button button-small" style="margin-top: 6px; width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                                        <span>🔍</span> <?php esc_html_e( 'مشاهده تصویر در اندازه اصلی', 'wccc' ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <span style="color: #94a3b8; font-style: italic;"><?php esc_html_e( 'فیشی بارگذاری نشده است.', 'wccc' ); ?></span>
                <?php endif; ?>
            </div>

            <!-- بخش عملیات سریع مدیریت WCCC -->
            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #e2e8f0;">
            <div class="wccc-admin-actions" style="direction: rtl; text-align: right;">
                <h4 style="margin: 0 0 10px 0; color: #1e293b;"><?php esc_html_e( 'اقدامات سریع مدیر برای این سفارش:', 'wccc' ); ?></h4>
                
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <button type="button" 
                            class="button button-primary wccc-approve-order wccc-action-btn" 
                            id="wccc_btn_approve" 
                            style="background: #10b981; border-color: #059669; font-weight: 600;" 
                            data-order-id="<?php echo esc_attr( (string) $order->get_id() ); ?>">
                        <?php esc_html_e( '✔ تأیید فیش و تغییر وضعیت', 'wccc' ); ?>
                    </button>
                    
                    <button type="button" 
                            class="button button-secondary wccc-reject-order wccc-action-btn" 
                            id="wccc_btn_reject" 
                            style="color: #ef4444; border-color: #ef4444; font-weight: 600;" 
                            data-order-id="<?php echo esc_attr( (string) $order->get_id() ); ?>">
                        <?php esc_html_e( '✖ رد فیش / لغو', 'wccc' ); ?>
                    </button>
                    
                    <span id="wccc_action_spinner" class="spinner" style="float: none; margin: 0;"></span>
                </div>

                <!-- فیلد textarea جهت درج دلیل رد فیش -->
                <div id="wccc_reject_reason_wrapper" style="margin-top: 10px; display: none;">
                    <label for="wccc_reject_reason" style="display: block; font-size: 11px; color: #64748b; margin-bottom: 4px; font-weight: 600;">
                        <?php esc_html_e( 'دلیل رد فیش (اختیاری):', 'wccc' ); ?>
                    </label>
                    <textarea id="wccc_reject_reason" 
                              rows="2" 
                              placeholder="<?php esc_attr_e( 'علت رد فیش را بنویسید (مثلاً: عدم وضوح تصویر یا عدم تطابق مبلغ)...', 'wccc' ); ?>" 
                              style="width: 100%; font-size: 12px; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; resize: vertical;"></textarea>
                    
                    <div style="margin-top: 6px; display: flex; gap: 6px;">
                        <button type="button" 
                                class="button button-small" 
                                id="wccc_btn_confirm_reject" 
                                style="background: #ef4444; border-color: #dc2626; color: #ffffff;">
                            <?php esc_html_e( 'ثبت نهایی رد فیش', 'wccc' ); ?>
                        </button>
                        <button type="button" 
                                class="button button-small" 
                                id="wccc_btn_cancel_reject">
                            <?php esc_html_e( 'انصراف', 'wccc' ); ?>
                        </button>
                    </div>
                </div>

                <!-- باکس پیام نتیجه -->
                <div id="wccc_action_msg" style="margin-top: 10px; display: none; padding: 8px 12px; border-radius: 4px; font-weight: 500; font-size: 12px; line-height: 1.5;"></div>
            </div>

            <script type="text/javascript">
            jQuery(document).ready(function($) {
                var nonce = '<?php echo esc_js( wp_create_nonce( 'wccc_order_action_nonce' ) ); ?>';

                function performAction(actionName, extraData) {
                    var $spinner = $('#wccc_action_spinner');
                    var $msg = $('#wccc_action_msg');
                    var $btns = $('.wccc-action-btn, #wccc_btn_confirm_reject');

                    $btns.prop('disabled', true);
                    $spinner.addClass('is-active');
                    $msg.hide().removeClass('notice-success notice-error');

                    var postData = $.extend({
                        action: actionName,
                        security: nonce,
                        order_id: <?php echo intval( $order->get_id() ); ?>
                    }, extraData);

                    $.post(ajaxurl, postData, function(res) {
                        $spinner.removeClass('is-active');
                        $btns.prop('disabled', false);

                        if (res.success) {
                            $msg.css({
                                'background': '#ecfdf5',
                                'color': '#065f46',
                                'border': '1px solid #a7f3d0'
                            }).text(res.data.message || '<?php echo esc_js( __( 'عملیات با موفقیت انجام شد.', 'wccc' ) ); ?>').show();
                            setTimeout(function(){ location.reload(); }, 1200);
                        } else {
                            $msg.css({
                                'background': '#fef2f2',
                                'color': '#991b1b',
                                'border': '1px solid #fecaca'
                            }).text(res.data && res.data.message ? res.data.message : '<?php echo esc_js( __( 'خطا در انجام عملیات.', 'wccc' ) ); ?>').show();
                        }
                    }).fail(function() {
                        $spinner.removeClass('is-active');
                        $btns.prop('disabled', false);
                        $msg.css({
                            'background': '#fef2f2',
                            'color': '#991b1b',
                            'border': '1px solid #fecaca'
                        }).text('<?php echo esc_js( __( 'خطای ارتباط با سرور.', 'wccc' ) ); ?>').show();
                    });
                }

                // تأیید فیش
                $(document).on('click', '#wccc_btn_approve, .wccc-approve-order', function(e) {
                    e.preventDefault();
                    if (!confirm('<?php echo esc_js( __( 'آیا از تأیید پرداخت این سفارش و ارتقای وضعیت آن اطمینان دارید؟', 'wccc' ) ); ?>')) {
                        return;
                    }
                    var note = prompt('<?php echo esc_js( __( 'یادداشت اختیاری برای تأیید سفارش:', 'wccc' ) ); ?>') || '';
                    performAction('wccc_quick_approve_order', { note: note });
                });

                // باز کردن پنل رد فیش
                $(document).on('click', '#wccc_btn_reject, .wccc-reject-order', function(e) {
                    e.preventDefault();
                    $('#wccc_reject_reason_wrapper').slideToggle(150);
                    $('#wccc_reject_reason').focus();
                });

                // انصراف از رد فیش
                $('#wccc_btn_cancel_reject').on('click', function(e) {
                    e.preventDefault();
                    $('#wccc_reject_reason_wrapper').slideUp(150);
                });

                // ثبت قطعی رد فیش
                $('#wccc_btn_confirm_reject').on('click', function(e) {
                    e.preventDefault();
                    if (!confirm('<?php echo esc_js( __( 'آیا از رد فیش و لغو این سفارش اطمینان دارید؟', 'wccc' ) ); ?>')) {
                        return;
                    }
                    var reason = $('#wccc_reject_reason').val();
                    performAction('wccc_quick_reject_order', { reason: reason });
                });
            });
            </script>
        </div>
        <?php
    }
}
