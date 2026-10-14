<?php
/**
 * WCCC Admin Settings & Penny Engine View
 * Native Enterprise Gateway Settings Component with Visual Tabs
 *
 * @package WCCC\Admin\Views
 * @version 1.0.65
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$s = ! empty( $data['settings'] ) && is_array( $data['settings'] ) ? $data['settings'] : array();
$penny_enabled   = ! isset( $s['penny_engine_enabled'] ) || ! empty( $s['penny_engine_enabled'] );
$min_offset      = isset( $s['min_offset'] ) ? intval( $s['min_offset'] ) : 100;
$max_offset      = isset( $s['max_offset'] ) ? intval( $s['max_offset'] ) : 900;
$window_timeout  = isset( $s['window_timeout'] ) ? intval( $s['window_timeout'] ) : 15;
$matching_digits = isset( $s['matching_digits'] ) ? intval( $s['matching_digits'] ) : 3;
$max_file_size   = isset( $s['max_file_size'] ) ? intval( $s['max_file_size'] ) : 5;
$hmac_secret     = ! empty( $s['hmac_secret'] ) ? (string) $s['hmac_secret'] : 'wccc_sec_live_94f8a37b120c89e248b';
$auto_refresh    = isset( $s['auto_refresh'] ) ? intval( $s['auto_refresh'] ) : 30;
$allowed_formats = ! empty( $s['allowed_formats'] ) && is_array( $s['allowed_formats'] ) ? $s['allowed_formats'] : array( 'jpg', 'png', 'webp' );
$layout_mode     = get_option( 'wccc_admin_layout_mode', 'horizontal' );
$sticky_header   = get_option( 'wccc_sticky_header', 'yes' );
$font_source     = get_option( 'wccc_admin_font_source', 'inherit' );

// متون و پیام‌های مشتری (Tab 2: متن‌ها و پیام‌ها)
$wc_gateway_settings    = get_option( 'woocommerce_wccc_settings', array() );
$gateway_title          = ! empty( $s['gateway_title'] ) ? (string) $s['gateway_title'] : ( ! empty( $wc_gateway_settings['title'] ) ? (string) $wc_gateway_settings['title'] : __( 'پرداخت کارت به کارت / انتقال بانکی', 'wccc' ) );
$gateway_description    = ! empty( $s['gateway_description'] ) ? (string) $s['gateway_description'] : ( ! empty( $wc_gateway_settings['description'] ) ? (string) $wc_gateway_settings['description'] : __( 'انتقال وجه از طریق کلیه عابربانک‌ها و برنامه‌های پرداخت بانکی به شماره کارت یا شبا با ثبت مشخصات پرداخت.', 'wccc' ) );
$order_instructions     = ! empty( $s['order_instructions'] ) ? (string) $s['order_instructions'] : __( 'لطفاً مبلغ مشخص‌شده را دقیقاً به یکی از شماره کارت‌های زیر واریز نمایید و سپس اطلاعات پرداخت را ثبت کنید.', 'wccc' );
$pending_review_message = ! empty( $s['pending_review_message'] ) ? (string) $s['pending_review_message'] : __( 'اطلاعات پرداخت شما با موفقیت ثبت شد و در صف بررسی حسابداری قرار گرفت. پس از تأیید نهایی، وضعیت سفارش به‌روزرسانی خواهد شد.', 'wccc' );
$instant_pay_notice     = ! empty( $s['instant_pay_notice'] ) ? (string) $s['instant_pay_notice'] : __( 'در صورت واریز دقیق مبلغ پنی‌دار، سفارش شما بلافاصله پس از دریافت پیامک بانکی به صورت هوشمند تطبیق داده خواهد شد.', 'wccc' );

// تنظیمات ظاهر کارت سمت مشتری (Tab 3: ظاهر و چیدمان - مسیر ۲)
$card_theme          = ! empty( $s['card_theme'] ) ? sanitize_key( (string) $s['card_theme'] ) : 'modern_gold';
$show_bank_logo      = ! isset( $s['show_bank_logo'] ) || ! empty( $s['show_bank_logo'] );
$enable_copy_buttons = ! isset( $s['enable_copy_buttons'] ) || ! empty( $s['enable_copy_buttons'] );
$show_shaba          = ! isset( $s['show_shaba'] ) || ! empty( $s['show_shaba'] );
?>

<div class="wccc-view <?php echo ( isset( $_GET['tab'] ) && sanitize_key( $_GET['tab'] ) === 'settings' ) ? 'active' : ''; ?>" id="wccc-view-settings">

    <!-- Top Header Banner (فقط عناوین توضیحی) -->
    <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 18px 20px; margin-bottom: 20px; box-shadow: var(--wccc-shadow);">
        <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'تنظیمات درگاه و پنی‌انجین', 'wccc' ); ?></h3>
        <p style="margin: 0; font-size: 12px; color: var(--wccc-text-muted);">
            <?php esc_html_e( 'پیکربندی هوشمند الگوریتم پنی، متن‌ها و پیام‌های پرداخت خریدار، و سفارشی‌سازی ظاهر پیشخوان.', 'wccc' ); ?>
        </p>
    </div>

    <!-- تب‌های ناوبری داخلی برگه تنظیمات (Subnav Visual Tabs - v1.0.63) -->
    <div class="wccc-subnav-tabs" role="tablist" aria-label="<?php esc_attr_e( 'بخش‌های تنظیمات', 'wccc' ); ?>">
        <button type="button" class="wccc-subnav-tab active" data-settings-tab="tab-engine" role="tab" aria-selected="true" aria-controls="tab-engine" id="tab-btn-engine">
            <span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
            <span><?php esc_html_e( 'تنظیمات پایه و موتور', 'wccc' ); ?></span>
        </button>
        <button type="button" class="wccc-subnav-tab" data-settings-tab="tab-messages" role="tab" aria-selected="false" aria-controls="tab-messages" id="tab-btn-messages">
            <span class="dashicons dashicons-format-chat" aria-hidden="true"></span>
            <span><?php esc_html_e( 'متن‌ها و پیام‌ها', 'wccc' ); ?></span>
        </button>
        <button type="button" class="wccc-subnav-tab" data-settings-tab="tab-appearance" role="tab" aria-selected="false" aria-controls="tab-appearance" id="tab-btn-appearance">
            <span class="dashicons dashicons-art" aria-hidden="true"></span>
            <span><?php esc_html_e( 'ظاهر و چیدمان', 'wccc' ); ?></span>
        </button>
    </div>

    <!-- فرم واحد ذخیره‌سازی داده‌ها (حفظ جامعیت بدون شکستن کانتینر) -->
    <form id="wccc-settings-form">

        <!-- نوار ذخیره شناور فین‌تک (پایین سمت چپ) -->
        <div class="wccc-floating-save-bar">
            <span id="wccc-unsaved-badge" style="display: none;" class="wccc-badge wccc-badge-warning"><?php esc_html_e( 'تغییرات ذخیره‌نشده', 'wccc' ); ?></span>
            <button type="button" class="wccc-btn wccc-btn-secondary" id="wccc-undo-settings-btn" disabled>
                <span class="dashicons dashicons-undo" style="font-size: 16px; width: 16px; height: 16px;"></span>
                <span><?php esc_html_e( 'واگرد', 'wccc' ); ?></span>
            </button>
            <button type="submit" class="wccc-btn wccc-btn-primary" id="wccc-save-settings-btn" disabled>
                <span class="dashicons dashicons-saved" style="font-size: 16px; width: 16px; height: 16px;"></span>
                <span><?php esc_html_e( 'ذخیره تنظیمات', 'wccc' ); ?></span>
            </button>
        </div>

        <!-- ========================================================================= -->
        <!-- تب ۱: تنظیمات پایه و موتور (Penny Engine, Upload Settings, Security & Danger Zone) -->
        <!-- ========================================================================= -->
        <div class="wccc-tab-pane active" id="tab-engine" role="tabpanel" aria-labelledby="tab-btn-engine">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px;">

                <!-- Section 1: Penny Engine -->
                <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 24px; box-shadow: var(--wccc-shadow);">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--wccc-border);">
                        <span class="dashicons dashicons-performance" style="color: var(--wccc-primary);"></span>
                        <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'موتور تفکیک انحصاری مبالغ (Penny Engine)', 'wccc' ); ?></h4>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none;">
                            <input type="checkbox" name="penny_engine_enabled" value="1" <?php checked( $penny_enabled ); ?> style="width: 18px; height: 18px;">
                            <div>
                                <span style="font-weight: 700; font-size: 13px; color: var(--wccc-text);"><?php esc_html_e( 'فعالسازی موتور تفکیک خرد (Penny Offset)', 'wccc' ); ?></span>
                                <div style="font-size: 11px; color: var(--wccc-text-muted);"><?php esc_html_e( 'افزودن مبلغی ناچیز (مثلاً ۱۰۰ تا ۹۰۰ تومان) به فاکتور جهت تشخیص خودکار پیامک بانکی', 'wccc' ); ?></div>
                            </div>
                        </label>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'حداقل پنی (تومان)', 'wccc' ); ?></label>
                            <input type="number" name="min_offset" value="<?php echo esc_attr( (string) $min_offset ); ?>" min="10" max="1000" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;" class="wccc-mono">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'حداکثر پنی (تومان)', 'wccc' ); ?></label>
                            <input type="number" name="max_offset" value="<?php echo esc_attr( (string) $max_offset ); ?>" min="100" max="9999" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;" class="wccc-mono">
                        </div>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'مهلت زمان واریز آنی (دقیقه)', 'wccc' ); ?></label>
                        <input type="number" name="window_timeout" value="<?php echo esc_attr( (string) $window_timeout ); ?>" min="5" max="120" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;" class="wccc-mono">
                        <span style="font-size: 10px; color: var(--wccc-text-muted); display: block; margin-top: 4px;"><?php esc_html_e( 'پس از اتمام این زمان، مبلغ پنی اختصاص داده شده آزاد شده و برای سفارش دیگری قابل استفاده خواهد بود.', 'wccc' ); ?></span>
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'تعداد ارقام پنی (Digit Precision)', 'wccc' ); ?></label>
                        <select name="matching_digits" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;">
                            <option value="2" <?php selected( $matching_digits, 2 ); ?>><?php esc_html_e( '۲ رقم (مثال: ۵۰ تومان تا ۹۰ تومان)', 'wccc' ); ?></option>
                            <option value="3" <?php selected( $matching_digits, 3 ); ?>><?php esc_html_e( '۳ رقم (پیشنهادی: ۱۰۰ تومان تا ۹۹۰ تومان)', 'wccc' ); ?></option>
                            <option value="4" <?php selected( $matching_digits, 4 ); ?>><?php esc_html_e( '۴ رقم (برای فروشگاه‌های بسیار شلوغ)', 'wccc' ); ?></option>
                        </select>
                    </div>
                </div>

                <!-- Section 2: Security & Webhook -->
                <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 24px; box-shadow: var(--wccc-shadow);">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--wccc-border);">
                        <span class="dashicons dashicons-lock" style="color: var(--wccc-primary);"></span>
                        <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'امنیت، توکن وب‌هوک و آپلود فیش', 'wccc' ); ?></h4>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'کلید محرمانه وب‌هوک (HMAC Secret Token)', 'wccc' ); ?></label>
                        <div style="position: relative;">
                            <input type="password" name="hmac_secret" id="wccc-hmac-token-input" value="<?php echo esc_attr( $hmac_secret ); ?>" style="width: 100%; padding: 8px 40px 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;" class="wccc-mono">
                            <button type="button" id="wccc-toggle-hmac-eye" style="position: absolute; right: 8px; top: 7px; background: transparent; border: none; cursor: pointer; color: var(--wccc-text-muted);">
                                <span class="dashicons dashicons-visibility" style="font-size: 20px; width: 20px; height: 20px;"></span>
                            </button>
                        </div>
                        <span style="font-size: 10px; color: var(--wccc-text-muted); display: block; margin-top: 4px;"><?php esc_html_e( 'این کلید رمزنگاری را در اپلیکیشن اندروید جهت تولید امضای امنیتی وارد نمایید.', 'wccc' ); ?></span>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label style="font-size: 11px; font-weight: 700; color: var(--wccc-text);"><?php esc_html_e( 'حداکثر حجم مجاز بارگذاری فیش', 'wccc' ); ?></label>
                            <span id="wccc-slider-val" class="wccc-badge wccc-badge-info" style="font-size: 10px;"><?php echo esc_html( (string) $max_file_size ); ?> <?php esc_html_e( 'مگابایت', 'wccc' ); ?></span>
                        </div>
                        <input type="range" name="max_file_size" id="wccc-file-size-slider" min="1" max="15" value="<?php echo esc_attr( (string) $max_file_size ); ?>" style="width: 100%;">
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 8px;"><?php esc_html_e( 'فرمت‌های مجاز فیش بانکی', 'wccc' ); ?></label>
                        <div style="display: flex; gap: 14px; flex-wrap: wrap; font-size: 12px;">
                            <label style="display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" name="allowed_formats[]" value="jpg" <?php checked( in_array( 'jpg', $allowed_formats, true ) ); ?>>
                                <span>JPG / JPEG</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" name="allowed_formats[]" value="png" <?php checked( in_array( 'png', $allowed_formats, true ) ); ?>>
                                <span>PNG</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" name="allowed_formats[]" value="webp" <?php checked( in_array( 'webp', $allowed_formats, true ) ); ?>>
                                <span>WebP</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 6px;">
                                <input type="checkbox" name="allowed_formats[]" value="pdf" <?php checked( in_array( 'pdf', $allowed_formats, true ) ); ?>>
                                <span>PDF</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'فاصله زمانی به‌روزرسانی خودکار پیشخوان (ثانیه)', 'wccc' ); ?></label>
                        <input type="number" name="auto_refresh" value="<?php echo esc_attr( (string) $auto_refresh ); ?>" min="10" max="300" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;" class="wccc-mono">
                    </div>
                </div>

            </div>

            <!-- Danger Zone Card -->
            <div class="wccc-card wccc-card-danger" style="margin-top: 24px; border: 1px solid rgba(239, 68, 68, 0.35); background: linear-gradient(180deg, rgba(239, 68, 68, 0.03) 0%, rgba(239, 68, 68, 0.01) 100%);">
                <div class="wccc-card-header" style="border-bottom: 1px solid rgba(239, 68, 68, 0.15); display: flex; align-items: center; justify-content: space-between; padding: 16px 20px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="wccc-icon" style="color: #ef4444; font-size: 20px;">warning</span>
                        <h3 style="margin: 0; color: #ef4444; font-size: 15px; font-weight: 700;"><?php esc_html_e( 'منطقه خطر / بازنشانی تنظیمات', 'wccc' ); ?></h3>
                    </div>
                    <span class="wccc-badge wccc-badge-danger" style="background: rgba(239, 68, 68, 0.12); color: #ef4444; font-size: 11px; padding: 3px 8px; border-radius: 6px; font-weight: 600;"><?php esc_html_e( 'غیرقابل بازگشت', 'wccc' ); ?></span>
                </div>
                <div class="wccc-card-body" style="padding: 18px 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px; font-size: 12.5px; line-height: 1.8;">
                        <div style="background: rgba(239, 68, 68, 0.06); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 8px; padding: 12px 14px;">
                            <div style="font-weight: 700; color: #ef4444; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                                <span class="wccc-icon" style="font-size: 18px;">restart_alt</span>
                                <?php esc_html_e( 'مواردی که بازنشانی می‌شوند:', 'wccc' ); ?>
                            </div>
                            <ul style="margin: 0; padding-right: 18px; color: var(--wccc-text-muted, #94a3b8);">
                                <li><?php esc_html_e( 'تنظیمات الگوریتم پنی‌انجین (بازه مبالغ، زمان انقضا و گام‌ها)', 'wccc' ); ?></li>
                                <li><?php esc_html_e( 'تنظیمات وب‌هوک و امنیت ارتباطات', 'wccc' ); ?></li>
                                <li><?php esc_html_e( 'تنظیمات کارمزد و قوانین پرداخت این برگه', 'wccc' ); ?></li>
                            </ul>
                        </div>
                        <div style="background: rgba(16, 185, 129, 0.06); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 8px; padding: 12px 14px;">
                            <div style="font-weight: 700; color: #10b981; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                                <span class="wccc-icon" style="font-size: 18px;">verified_user</span>
                                <?php esc_html_e( 'مواردی که کاملاً دست‌نخورده باقی می‌مانند:', 'wccc' ); ?>
                            </div>
                            <ul style="margin: 0; padding-right: 18px; color: var(--wccc-text-muted, #94a3b8);">
                                <li><?php esc_html_e( 'کارت‌های بانکی تعریف‌شده و شماره شباها', 'wccc' ); ?></li>
                                <li><?php esc_html_e( 'سفارش‌ها، تراکنش‌ها و تاریخچه رسیدها', 'wccc' ); ?></li>
                                <li><?php esc_html_e( 'توکن و وضعیت اتصال دستگاه‌های اندروید و لاگ‌ها', 'wccc' ); ?></li>
                            </ul>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px;">
                        <p style="margin: 0; font-size: 12px; color: var(--wccc-text-muted, #64748b);">
                            <?php esc_html_e( 'با این اقدام تمام گزینه‌های این تب به مقادیر اولیه افزونه برمی‌گردند.', 'wccc' ); ?>
                        </p>
                        <button type="button" id="wccc-open-reset-modal-btn" class="wccc-btn" style="background: #ef4444; color: #ffffff; border: none; font-weight: 600; padding: 8px 16px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <span class="wccc-icon" style="font-size: 18px;">restart_alt</span>
                            <?php esc_html_e( 'بازنشانی به پیش‌فرض کارخانه', 'wccc' ); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- تب ۲: متن‌ها و پیام‌ها (Customer Texts & Messages Panel - v1.0.63) -->
        <!-- ========================================================================= -->
        <div class="wccc-tab-pane" id="tab-messages" role="tabpanel" aria-labelledby="tab-btn-messages" style="display: none;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px;">

                <!-- Card 1: متون صفحه تسویه حساب (Checkout Texts) -->
                <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 24px; box-shadow: var(--wccc-shadow);">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--wccc-border);">
                        <span class="dashicons dashicons-cart" style="color: var(--wccc-primary);"></span>
                        <div>
                            <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'متون صفحه تسویه حساب (Checkout Texts)', 'wccc' ); ?></h4>
                            <div style="font-size: 11px; color: var(--wccc-text-muted); margin-top: 2px;">
                                <?php esc_html_e( 'عنوان و توضیحات قابل مشاهده توسط خریدار در صفحه تسویه حساب ووکامرس', 'wccc' ); ?>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;">
                            <?php esc_html_e( 'عنوان روش پرداخت در تسویه حساب', 'wccc' ); ?>
                        </label>
                        <input type="text" name="gateway_title" value="<?php echo esc_attr( $gateway_title ); ?>" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;">
                        <span style="font-size: 10px; color: var(--wccc-text-muted); display: block; margin-top: 4px;">
                            <?php esc_html_e( 'نامی که خریدار در فهرست درگاه‌های تسویه حساب مشاهده می‌کند (پیش‌فرض: پرداخت کارت به کارت / انتقال بانکی)', 'wccc' ); ?>
                        </span>
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;">
                            <?php esc_html_e( 'توضیحات درگاه برای خریدار در تسویه حساب', 'wccc' ); ?>
                        </label>
                        <textarea name="gateway_description" rows="3" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px; line-height: 1.6;"><?php echo esc_textarea( $gateway_description ); ?></textarea>
                        <span style="font-size: 10px; color: var(--wccc-text-muted); display: block; margin-top: 4px;">
                            <?php esc_html_e( 'متن راهنما که پس از انتخاب گزینه کارت به کارت به خریدار نمایش داده می‌شود.', 'wccc' ); ?>
                        </span>
                    </div>
                </div>

                <!-- Card 2: پیام‌های صفحه تشکر، واریز و رسید (Thank You Page Notices) -->
                <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 24px; box-shadow: var(--wccc-shadow);">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--wccc-border);">
                        <span class="dashicons dashicons-media-text" style="color: var(--wccc-primary);"></span>
                        <div>
                            <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'پیام‌های صفحه تشکر و پیگیری واریز (Thank You Page Notices)', 'wccc' ); ?></h4>
                            <div style="font-size: 11px; color: var(--wccc-text-muted); margin-top: 2px;">
                                <?php esc_html_e( 'متون راهنما در برگه دریافت فاکتور، آپلود رسید و وضعیت سفارش خریدار', 'wccc' ); ?>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;">
                            <?php esc_html_e( 'راهنمای واریز به خریدار (صفحه تشکر)', 'wccc' ); ?>
                        </label>
                        <textarea name="order_instructions" rows="2" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px; line-height: 1.6;"><?php echo esc_textarea( $order_instructions ); ?></textarea>
                        <span style="font-size: 10px; color: var(--wccc-text-muted); display: block; margin-top: 4px;">
                            <?php esc_html_e( 'این متن بالای کارت‌های بانکی در صفحه پرداخت و فاکتور نمایش می‌یابد.', 'wccc' ); ?>
                        </span>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;">
                            <?php esc_html_e( 'پیام تأیید دریافت فیش و در صف بررسی', 'wccc' ); ?>
                        </label>
                        <textarea name="pending_review_message" rows="2" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px; line-height: 1.6;"><?php echo esc_textarea( $pending_review_message ); ?></textarea>
                        <span style="font-size: 10px; color: var(--wccc-text-muted); display: block; margin-top: 4px;">
                            <?php esc_html_e( 'پس از ارسال مشخصات پرداخت یا آپلود فیش، این پیام به مشتری نشان داده می‌شود.', 'wccc' ); ?>
                        </span>
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;">
                            <?php esc_html_e( 'متن راهنمای واریز سریع و پنی هوشمند', 'wccc' ); ?>
                        </label>
                        <textarea name="instant_pay_notice" rows="2" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px; line-height: 1.6;"><?php echo esc_textarea( $instant_pay_notice ); ?></textarea>
                        <span style="font-size: 10px; color: var(--wccc-text-muted); display: block; margin-top: 4px;">
                            <?php esc_html_e( 'توضیحات واریز دقیق رقم با پنی جهت تطبیق سریع پیامک بانکی.', 'wccc' ); ?>
                        </span>
                    </div>
                </div>

            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- تب ۳: ظاهر و چیدمان (Admin Layout Style & Font Engine - v1.0.63) -->
        <!-- ========================================================================= -->
        <div class="wccc-tab-pane" id="tab-appearance" role="tabpanel" aria-labelledby="tab-btn-appearance" style="display: none;">

            <!-- Section 3: Admin Layout Style -->
            <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 24px; box-shadow: var(--wccc-shadow);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--wccc-border);">
                    <span class="dashicons dashicons-layout" style="color: var(--wccc-primary);"></span>
                    <div>
                        <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'سبک چیدمان پیشخوان مدیریت (Admin Layout Style)', 'wccc' ); ?></h4>
                        <div style="font-size: 11px; color: var(--wccc-text-muted); margin-top: 2px;">
                            <?php esc_html_e( 'انتخاب شیوه نمایش منوها و ناوبری جهت حداکثر کارایی و دید بهتر جداول مالی', 'wccc' ); ?>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">

                    <!-- Option 1: Horizontal (Default) -->
                    <label style="display: block; border: 2px solid <?php echo 'horizontal' === $layout_mode ? 'var(--wccc-primary)' : 'var(--wccc-border)'; ?>; background: <?php echo 'horizontal' === $layout_mode ? 'rgba(0, 97, 148, 0.04)' : 'var(--wccc-surface-subtle)'; ?>; border-radius: var(--wccc-radius); padding: 18px; cursor: pointer; transition: all 0.2s ease;">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <input type="radio" name="wccc_admin_layout_mode" value="horizontal" <?php checked( $layout_mode, 'horizontal' ); ?> style="margin-top: 3px;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                                    <strong style="font-size: 13px; color: var(--wccc-text);"><?php esc_html_e( 'تب‌های افقی مدرن در بالای صفحه', 'wccc' ); ?></strong>
                                    <span class="wccc-badge wccc-badge-success" style="font-size: 9px;"><?php esc_html_e( 'پیشنهادی', 'wccc' ); ?></span>
                                </div>
                                <p style="margin: 0; font-size: 11px; color: var(--wccc-text-muted); line-height: 1.6;">
                                    <?php esc_html_e( 'منوها به صورت تب‌های افقی و مدرن در هدر بالا قرار می‌گیرند و ۱۰۰٪ عرض صفحه به جداول و گزارش‌های مالی اختصاص می‌یابد.', 'wccc' ); ?>
                                </p>
                            </div>
                        </div>
                    </label>

                    <!-- Option 2: Compact Sidebar -->
                    <label style="display: block; border: 2px solid <?php echo 'sidebar_compact' === $layout_mode ? 'var(--wccc-primary)' : 'var(--wccc-border)'; ?>; background: <?php echo 'sidebar_compact' === $layout_mode ? 'rgba(0, 97, 148, 0.04)' : 'var(--wccc-surface-subtle)'; ?>; border-radius: var(--wccc-radius); padding: 18px; cursor: pointer; transition: all 0.2s ease;">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <input type="radio" name="wccc_admin_layout_mode" value="sidebar_compact" <?php checked( $layout_mode, 'sidebar_compact' ); ?> style="margin-top: 3px;">
                            <div>
                                <strong style="display: block; font-size: 13px; color: var(--wccc-text); margin-bottom: 4px;">
                                    <?php esc_html_e( 'سایدبار تاشو / مینی (Compact Mini-Sidebar)', 'wccc' ); ?>
                                </strong>
                                <p style="margin: 0; font-size: 11px; color: var(--wccc-text-muted); line-height: 1.6;">
                                    <?php esc_html_e( 'سایدبار باریک با آیکون‌های جمع‌وجور و باز شدن روان عنوان‌ها با هاور موس (Expand on Hover) بدون اشغال فضای کاربری.', 'wccc' ); ?>
                                </p>
                            </div>
                        </div>
                    </label>

                    <!-- Option 3: Classic Sidebar -->
                    <label style="display: block; border: 2px solid <?php echo 'sidebar_classic' === $layout_mode ? 'var(--wccc-primary)' : 'var(--wccc-border)'; ?>; background: <?php echo 'sidebar_classic' === $layout_mode ? 'rgba(0, 97, 148, 0.04)' : 'var(--wccc-surface-subtle)'; ?>; border-radius: var(--wccc-radius); padding: 18px; cursor: pointer; transition: all 0.2s ease;">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <input type="radio" name="wccc_admin_layout_mode" value="sidebar_classic" <?php checked( $layout_mode, 'sidebar_classic' ); ?> style="margin-top: 3px;">
                            <div>
                                <strong style="display: block; font-size: 13px; color: var(--wccc-text); margin-bottom: 4px;">
                                    <?php esc_html_e( 'سایدبار عمودی کلاسیک (Classic Full Sidebar)', 'wccc' ); ?>
                                </strong>
                                <p style="margin: 0; font-size: 11px; color: var(--wccc-text-muted); line-height: 1.6;">
                                    <?php esc_html_e( 'چیدمان ثابت عمودی سنتی در سمت راست با عناوین و متون کامل ناوبری.', 'wccc' ); ?>
                                </p>
                            </div>
                        </div>
                    </label>

                </div>

                <!-- تنظیم هدر افقی چسبان (Sticky Header) -->
                <div style="margin-top: 20px; padding: 14px 18px; background: var(--wccc-surface-subtle); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius-md); display: flex; align-items: center; justify-content: space-between;">
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; user-select: none; width: 100%;">
                        <input type="checkbox" name="wccc_sticky_header" id="wccc_sticky_header" value="yes" <?php checked( $sticky_header, 'yes' ); ?> style="width: 18px; height: 18px; cursor: pointer;">
                        <div>
                            <strong style="display: block; font-size: 13px; color: var(--wccc-text); margin-bottom: 2px;">
                                <?php esc_html_e( 'هدر افقی چسبان (Sticky Header)', 'wccc' ); ?>
                            </strong>
                            <span style="font-size: 11px; color: var(--wccc-text-muted); line-height: 1.6;">
                                <?php esc_html_e( 'با فعال بودن این گزینه، نوار هدر اصلی (شامل نشان نسخه، عنوان افزونه، تب‌های اصلی و شمارنده‌های وضعیت) هنگام اسکرول به بالای صفحه می‌چسبد.', 'wccc' ); ?>
                            </span>
                        </div>
                    </label>
                </div>

                <!-- تنظیم موتور انطباقی انتخاب فونت پیشخوان (Adaptive Admin Font Engine) -->
                <div style="margin-top: 16px; padding: 16px 20px; background: var(--wccc-surface-subtle); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius-md);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <label for="wccc_admin_font_source" style="font-size: 13px; font-weight: 700; color: var(--wccc-text);">
                            <?php esc_html_e( 'قلم و منبع فونت پیشخوان مدیریت (Admin Font Engine)', 'wccc' ); ?>
                        </label>
                        <span id="wccc-font-active-badge" class="wccc-badge" style="font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 6px; background: var(--wccc-primary-light); color: var(--wccc-primary);">
                            <?php echo ( 'vazirmatn' === $font_source ) ? esc_html__( 'فونت فعال: وزیرمتن محلی افزونه', 'wccc' ) : esc_html__( 'فونت فعال: ارث‌بری از پیشخوان / قالب', 'wccc' ); ?>
                        </span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <select name="wccc_admin_font_source" id="wccc_admin_font_source" style="width: 100%; max-width: 600px; padding: 10px 14px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface); color: var(--wccc-text); font-size: 13px; font-family: inherit; cursor: pointer;">
                            <option value="inherit" <?php selected( $font_source, 'inherit' ); ?>>
                                <?php esc_html_e( 'فونت قالب / پیشخوان سایت (ارث‌بری استاندارد)', 'wccc' ); ?>
                            </option>
                            <option value="vazirmatn" <?php selected( $font_source, 'vazirmatn' ); ?>>
                                <?php esc_html_e( 'فونت اختصاصی افزونه (وزیرمتن محلی)', 'wccc' ); ?>
                            </option>
                        </select>
                        <span style="font-size: 11px; color: var(--wccc-text-muted); line-height: 1.6;">
                            <?php esc_html_e( 'در حالت پیش‌فرض (ارث‌بری)، مصرف ترافیک برای وب‌فونت دقیقاً صفر بایت است و ظاهر بخش مدیریت با قالب پیشخوان شما همگام می‌شود. در صورت تمایل به تایپوگرافی یکپارچه و بهینه، گزینه وزیرمتن محلی را فعال کنید.', 'wccc' ); ?>
                        </span>
                    </div>

                    <!-- کارت پیش‌نمایش زنده قلم (صرفاً تستی) -->
                    <div id="wccc-font-live-preview-box" class="wccc-font-preview-card" style="margin-top: 18px; padding: 20px; background: var(--wccc-surface); border: 2px dashed var(--wccc-border); border-radius: var(--wccc-radius); box-shadow: var(--wccc-shadow); position: relative;">
                        <!-- هدر کارت با نشانگر کوچک آزمایشی -->
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid var(--wccc-border-subtle); padding-bottom: 10px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span class="material-symbols-outlined" style="color: var(--wccc-primary); font-size: 20px;">font_download</span>
                                <strong style="font-size: 13px; color: var(--wccc-text);">
                                    <?php esc_html_e( 'کارت پیش‌نمایش زنده قلم (صرفاً تستی)', 'wccc' ); ?>
                                </strong>
                            </div>
                            <span class="wccc-badge wccc-badge-info" style="font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; background: rgba(0, 97, 148, 0.1); color: var(--wccc-primary);">
                                <?php esc_html_e( 'آزمایشی / بدون اثر روی عملکرد درگاه', 'wccc' ); ?>
                            </span>
                        </div>

                        <!-- پاراگراف تستی برای مشاهده حروف فارسی -->
                        <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: 700; color: var(--wccc-text); line-height: 1.6;">
                            <?php esc_html_e( 'درگاه پرداخت هوشمند کارت‌به‌کارت ووکامرس (WCCC)', 'wccc' ); ?>
                        </p>

                        <!-- ارقام تستی برای بررسی اعداد فارسی -->
                        <div style="margin-bottom: 14px; font-size: 13px; color: var(--wccc-text); font-weight: 600; line-height: 1.6;" class="wccc-persian-num">
                            <?php esc_html_e( 'مبلغ آزمایشی: ۱۲۵,۰۰۰ تومان | شناسه: ۹۸۷۶۵۴۳۲۱۰', 'wccc' ); ?>
                        </div>

                        <!-- ردیف جدول کوچک ماکت -->
                        <div style="overflow-x: auto; margin-bottom: 14px;">
                            <table class="wccc-table" style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                <thead>
                                    <tr style="background: var(--wccc-surface-subtle); border-bottom: 1px solid var(--wccc-border);">
                                        <th style="padding: 8px 12px; text-align: right;"><?php esc_html_e( 'ردیف ماکت', 'wccc' ); ?></th>
                                        <th style="padding: 8px 12px; text-align: right;"><?php esc_html_e( 'عنوان ماکت تستی', 'wccc' ); ?></th>
                                        <th style="padding: 8px 12px; text-align: right;"><?php esc_html_e( 'مبلغ نمونه (فارسی)', 'wccc' ); ?></th>
                                        <th style="padding: 8px 12px; text-align: right;"><?php esc_html_e( 'ارقام فنی (مونو/LTR)', 'wccc' ); ?></th>
                                        <th style="padding: 8px 12px; text-align: center;"><?php esc_html_e( 'وضعیت نمونه', 'wccc' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom: 1px solid var(--wccc-border-subtle);">
                                        <td style="padding: 8px 12px;" class="wccc-persian-num"><?php esc_html_e( '۱', 'wccc' ); ?></td>
                                        <td style="padding: 8px 12px; font-weight: 600;"><?php esc_html_e( 'سفارش تست تایپوگرافی و جدول', 'wccc' ); ?></td>
                                        <td style="padding: 8px 12px; font-weight: 700; color: var(--wccc-primary);" class="wccc-persian-num"><?php esc_html_e( '۱۲۵,۰۰۰ تومان', 'wccc' ); ?></td>
                                        <td style="padding: 8px 12px;"><span class="wccc-card-num wccc-mono">6037-9975-1234-5678</span></td>
                                        <td style="padding: 8px 12px; text-align: center;"><span class="wccc-badge wccc-badge-success" style="font-size: 10px; padding: 2px 8px; border-radius: 4px;"><?php esc_html_e( 'تایید ماکت آزمایشی', 'wccc' ); ?></span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- دکمه نمونه غیرفعال جهت اطمینان از اعمال فونت روی دکمه‌ها -->
                        <div style="display: flex; align-items: center; justify-content: flex-start; gap: 12px; flex-wrap: wrap;">
                            <button type="button" class="wccc-btn wccc-btn-primary" disabled style="opacity: 0.65; cursor: not-allowed; font-size: 12px; padding: 6px 14px;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">touch_app</span>
                                <span><?php esc_html_e( 'دکمه نمونه غیرفعال (تست قلم)', 'wccc' ); ?></span>
                            </button>
                            <span style="font-size: 11px; color: var(--wccc-text-muted);">
                                <?php esc_html_e( 'این دکمه و جدول ماکت صرفاً جهت ارزیابی زنده قلم بوده و اثری بر عملکرد درگاه ندارند.', 'wccc' ); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: ظاهر کارت بانکی در تسویه حساب و صفحه تشکر (Customer Card Styling - v1.0.65) -->
            <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 24px; box-shadow: var(--wccc-shadow); margin-top: 20px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--wccc-border);">
                    <span class="dashicons dashicons-id" style="color: var(--wccc-primary); font-size: 20px;"></span>
                    <div>
                        <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'ظاهر کارت بانکی در تسویه حساب و صفحه تشکر', 'wccc' ); ?></h4>
                        <div style="font-size: 11px; color: var(--wccc-text-muted); margin-top: 2px;">
                            <?php esc_html_e( 'شخصی‌سازی تم، دکمه‌های کپی هوشمند و المان‌های بصری کارت مقصد در دید خریداران', 'wccc' ); ?>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; margin-bottom: 20px;">
                    <!-- فیلد تم کارت (card_theme) -->
                    <div>
                        <label for="wccc_card_theme" style="display: block; font-size: 12px; font-weight: 700; color: var(--wccc-text); margin-bottom: 8px;">
                            <?php esc_html_e( 'تم و الگوی رنگی کارت بانکی', 'wccc' ); ?>
                        </label>
                        <select name="card_theme" id="wccc_card_theme" style="width: 100%; padding: 10px 14px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 13px; font-family: inherit; cursor: pointer;">
                            <option value="modern_gold" <?php selected( $card_theme, 'modern_gold' ); ?>>
                                <?php esc_html_e( 'مدرن (سرمه‌ای/طلایی)', 'wccc' ); ?>
                            </option>
                            <option value="dark_glass" <?php selected( $card_theme, 'dark_glass' ); ?>>
                                <?php esc_html_e( 'تیره شیشه‌ای', 'wccc' ); ?>
                            </option>
                            <option value="minimal_light" <?php selected( $card_theme, 'minimal_light' ); ?>>
                                <?php esc_html_e( 'روشن مینیمال', 'wccc' ); ?>
                            </option>
                        </select>
                        <span style="font-size: 10.5px; color: var(--wccc-text-muted); display: block; margin-top: 4px; line-height: 1.5;">
                            <?php esc_html_e( 'سبک بصری کارت بانکی که خریدار در صفحه تسویه حساب و صفحه دریافت فاکتور مشاهده می‌کند.', 'wccc' ); ?>
                        </span>
                    </div>

                    <!-- گزینه‌های کنترلی و نمایشی -->
                    <div style="display: flex; flex-direction: column; gap: 12px; justify-content: center; background: var(--wccc-surface-subtle); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius-md); padding: 16px;">
                        <!-- چک‌باکس لوگوی بانک (show_bank_logo) -->
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none;">
                            <input type="checkbox" name="show_bank_logo" value="1" <?php checked( $show_bank_logo ); ?> style="width: 17px; height: 17px; cursor: pointer;">
                            <span style="font-size: 12px; font-weight: 600; color: var(--wccc-text);">
                                <?php esc_html_e( 'نمایش لوگوی بانک مقصد', 'wccc' ); ?>
                            </span>
                        </label>

                        <!-- چک‌باکس دکمه‌های کپی (enable_copy_buttons) -->
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none;">
                            <input type="checkbox" name="enable_copy_buttons" value="1" <?php checked( $enable_copy_buttons ); ?> style="width: 17px; height: 17px; cursor: pointer;">
                            <span style="font-size: 12px; font-weight: 600; color: var(--wccc-text);">
                                <?php esc_html_e( 'نمایش دکمه‌های کپی سریع', 'wccc' ); ?>
                            </span>
                        </label>

                        <!-- چک‌باکس نمایش شماره شبا (show_shaba) -->
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none;">
                            <input type="checkbox" name="show_shaba" value="1" <?php checked( $show_shaba ); ?> style="width: 17px; height: 17px; cursor: pointer;">
                            <span style="font-size: 12px; font-weight: 600; color: var(--wccc-text);">
                                <?php esc_html_e( 'نمایش شماره شبا در کارت', 'wccc' ); ?>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

        </div>

    </form>

    <!-- Custom WCCC Reset Modal -->
    <div id="wccc-reset-confirm-modal" class="wccc-modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center;">
        <div class="wccc-modal-container" role="dialog" aria-modal="true" style="background: var(--wccc-card-bg, #ffffff); color: var(--wccc-text, #1e293b); width: 100%; max-width: 460px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.15); border: 1px solid var(--wccc-border, #334155); overflow: hidden; animation: wcccModalIn 0.2s ease-out;">
            <div style="padding: 24px; text-align: center;">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: rgba(239, 68, 68, 0.12); color: #ef4444; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <span class="wccc-icon" style="font-size: 30px;">warning</span>
                </div>
                <h3 style="margin: 0 0 8px; font-size: 16px; font-weight: 700; color: var(--wccc-text);"><?php esc_html_e( 'تأیید بازنشانی تنظیمات درگاه', 'wccc' ); ?></h3>
                <p style="margin: 0; font-size: 13px; line-height: 1.6; color: var(--wccc-text-muted);">
                    <?php esc_html_e( 'آیا مطمئن هستید؟ تمام مقادیر این صفحه (پنی‌انجین، وب‌هوک و کارمزد) به حالت اولیه برمی‌گردند. کارت‌های بانکی، سفارش‌ها و توکن‌ها بدون تغییر باقی می‌مانند.', 'wccc' ); ?>
                </p>
            </div>
            <div class="wccc-modal-footer" style="padding: 12px 20px; background: var(--wccc-surface-subtle); border-top: 1px solid var(--wccc-border); display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" id="wccc-reset-modal-cancel" class="wccc-btn wccc-btn-secondary" style="min-width: 90px;">
                    <?php esc_html_e( 'انصراف', 'wccc' ); ?>
                </button>
                <button type="button" id="wccc-reset-modal-confirm" class="wccc-btn" style="background: #ef4444; color: #ffffff; border: none; min-width: 130px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                    <span class="wccc-icon" style="font-size: 18px;">delete_forever</span>
                    <?php esc_html_e( 'بله، بازنشانی شود', 'wccc' ); ?>
                </button>
            </div>
        </div>
    </div>
</div>
