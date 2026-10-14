/**
 * WCCC Checkout Mode Handler
 * مدیریت پویای انتخاب شیوه پرداخت کارت به کارت با همگام‌سازی پیش‌فرض (Init Sync) و رفع Race Condition
 *
 * @package WCCC
 */
(function($) {
    'use strict';

    var activeModeRequest = null;
    var lastSyncedMode    = null;
    var lastSyncedGateway = null;

    /**
     * مسدودسازی و غیرفعال‌کردن فرم تسویه جهت جلوگیری از ثبت ناقص سفارش
     */
    function lockCheckout() {
        var $checkoutForm = $('form.checkout, .woocommerce-checkout');
        if ($.fn.block) {
            $checkoutForm.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        }
        $('#place_order').prop('disabled', true).addClass('disabled');
    }

    /**
     * رفع انسداد فرم تسویه و فعال‌سازی مجدد دکمه ثبت سفارش
     */
    function unlockCheckout() {
        var $checkoutForm = $('form.checkout, .woocommerce-checkout');
        if ($.fn.unblock) {
            $checkoutForm.unblock();
        }
        $('#place_order').prop('disabled', false).removeClass('disabled');
    }

    /**
     * بررسی فعال بودن درگاه کارت به کارت WCCC در فرم تسویه حساب
     */
    function isWcccGatewaySelected() {
        var $checkedGateway = $('input[name="payment_method"]:checked');
        if ($checkedGateway.length > 0) {
            return $checkedGateway.val() === 'wccc_card_to_card';
        }
        // در صورتی که فقط یک درگاه پرداخت فعال باشد و بدون رادیوباتن رندر شود
        if ($('.payment_method_wccc_card_to_card').length > 0 && $('input[name="payment_method"]').length === 0) {
            return true;
        }
        return false;
    }

    /**
     * همگام‌سازی حالت اولیه و انتخاب پیش‌فرض به سشن ووکامرس (Init Sync)
     */
    function init_sync(force) {
        if (!isWcccGatewaySelected()) {
            if (lastSyncedGateway === 'wccc_card_to_card') {
                lastSyncedGateway = null;
                lastSyncedMode    = null;
            }
            return;
        }

        var $checkedMode = $('input[name="wccc_checkout_mode"]:checked');
        var modeToSync   = $checkedMode.length > 0 ? $checkedMode.val() : 'instant_auto';

        // جلوگیری از ایجاد حلقه یا ارسال درخواست تکراری برای همان حالت
        if (!force && lastSyncedMode === modeToSync && lastSyncedGateway === 'wccc_card_to_card') {
            return;
        }

        if (typeof wccc_checkout_params === 'undefined' || !wccc_checkout_params.ajax_url) {
            return;
        }

        // مسدودسازی دکمه پرداخت حین اعمال کارمزد پنی پیش‌فرض
        lockCheckout();

        if (activeModeRequest && activeModeRequest.readyState !== 4) {
            activeModeRequest.abort();
        }

        activeModeRequest = $.ajax({
            type: 'POST',
            url: wccc_checkout_params.ajax_url,
            data: {
                action: 'wccc_set_checkout_mode',
                mode: modeToSync,
                security: wccc_checkout_params.nonce
            },
            dataType: 'json',
            timeout: 8000,
            success: function(response) {
                lastSyncedMode    = modeToSync;
                lastSyncedGateway = 'wccc_card_to_card';
                // اعمال تغییر و محاسبه مجدد فاکتور تسویه حساب
                $(document.body).trigger('update_checkout');
            },
            error: function(xhr, status) {
                if (status !== 'abort') {
                    unlockCheckout();
                }
            },
            complete: function(xhr, status) {
                activeModeRequest = null;
            }
        });
    }

    $(document).ready(function() {

        // ۱. همگام‌سازی در بارگذاری اولیه صفحه تسویه حساب (در صورتی که درگاه WCCC از پیش انتخاب شده باشد)
        init_sync(false);

        // ۲. تغییر وضعیت انتخاب رادیوباتن شیوه پرداخت (آنی vs عادی)
        $(document).on('change', 'input[name="wccc_checkout_mode"]', function(e) {
            var selectedMode = $(this).val();
            var $container   = $(this).closest('.wccc-checkout-mode-container');

            // فوراً فرم تسویه مسدود و دکمه پرداخت قفل می‌شود
            lockCheckout();

            // به‌روزرسانی هایلایت بصری گزینه‌ها
            $container.find('.wccc-checkout-mode-option').removeClass('is-selected').css({
                'border-color': '#cbd5e1',
                'background': '#ffffff'
            });

            var $selectedOption = $(this).closest('.wccc-checkout-mode-option');
            $selectedOption.addClass('is-selected').css({
                'border-color': '#10b981',
                'background': '#f0fdf4'
            });

            // لغو درخواست قبلی در صورتی که در حال اجرا باشد
            if (activeModeRequest && activeModeRequest.readyState !== 4) {
                activeModeRequest.abort();
            }

            if (typeof wccc_checkout_params === 'undefined' || !wccc_checkout_params.ajax_url) {
                unlockCheckout();
                $(document.body).trigger('update_checkout');
                return;
            }

            activeModeRequest = $.ajax({
                type: 'POST',
                url: wccc_checkout_params.ajax_url,
                data: {
                    action: 'wccc_set_checkout_mode',
                    mode: selectedMode,
                    security: wccc_checkout_params.nonce
                },
                dataType: 'json',
                timeout: 8000,
                beforeSend: function() {
                    $container.css('opacity', '0.6');
                },
                success: function(response) {
                    lastSyncedMode    = selectedMode;
                    lastSyncedGateway = 'wccc_card_to_card';
                    $(document.body).trigger('update_checkout');
                },
                error: function(xhr, status) {
                    if (status !== 'abort') {
                        unlockCheckout();
                        $container.css('opacity', '1');
                    }
                },
                complete: function(xhr, status) {
                    activeModeRequest = null;
                    if (status !== 'abort') {
                        $container.css('opacity', '1');
                    }
                }
            });
        });

        // ۳. رویدادهای تغییر روش پرداخت و بازخوانی فرگمنت‌های تسویه حساب
        $(document.body).on('updated_checkout', function() {
            unlockCheckout();

            // استایل‌دهی رادیوباتن انتخاب‌شده
            $('input[name="wccc_checkout_mode"]:checked').each(function() {
                var $container = $(this).closest('.wccc-checkout-mode-container');
                $container.find('.wccc-checkout-mode-option').removeClass('is-selected').css({
                    'border-color': '#cbd5e1',
                    'background': '#ffffff'
                });

                $(this).closest('.wccc-checkout-mode-option').addClass('is-selected').css({
                    'border-color': '#10b981',
                    'background': '#f0fdf4'
                });
            });

            // اجرای Init Sync در صورتی که درگاه WCCC انتخاب شده ولی مقدار پنی هنوز ثبت نشده باشد
            init_sync(false);
        });

        // رویداد تغییر درگاه پرداخت در ووکامرس
        $(document).on('change', 'input[name="payment_method"]', function() {
            if ($(this).val() === 'wccc_card_to_card') {
                init_sync(false);
            } else {
                lastSyncedMode = null;
                lastSyncedGateway = null;
                $(document.body).trigger('update_checkout');
            }
        });

        $(document.body).on('payment_method_selected', function() {
            if (isWcccGatewaySelected()) {
                init_sync(false);
            } else {
                lastSyncedMode = null;
                lastSyncedGateway = null;
                $(document.body).trigger('update_checkout');
            }
        });

    });
})(jQuery);
