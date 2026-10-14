/**
 * WCCC Frontend Script
 * مدیریت کپی اطلاعات، تایمر معکوس، انقضای مهلت و ثبت امن ۴ رقم کارت در صفحه تشکر/پرداخت
 *
 * @package WCCC
 */
(function($) {
    'use strict';

    // تبدیل ارقام فارسی و عربی به انگلیسی
    function convertFaToEnDigits(str) {
        return (str || '')
            .replace(/[۰-۹]/g, function(d) { return String.fromCharCode(d.charCodeAt(0) - 1728); })
            .replace(/[٠-٩]/g, function(d) { return String.fromCharCode(d.charCodeAt(0) - 1584); });
    }

    // تابع کپی امن در کلیپ‌بورد با فال‌بک تکست‌اریا
    function copyToClipboard(text, onSuccess, onError) {
        if (!text) {
            if (typeof onError === 'function') onError();
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function() {
                if (typeof onSuccess === 'function') onSuccess();
            }).catch(function() {
                fallbackCopyText(text, onSuccess, onError);
            });
        } else {
            fallbackCopyText(text, onSuccess, onError);
        }
    }

    function fallbackCopyText(text, onSuccess, onError) {
        try {
            var $temp = $('<textarea>');
            $temp.css({
                position: 'fixed',
                top: '-9999px',
                left: '-9999px',
                opacity: '0'
            });
            $('body').append($temp);
            $temp.val(text).focus().select();
            var successful = document.execCommand('copy');
            $temp.remove();
            if (successful) {
                if (typeof onSuccess === 'function') onSuccess();
            } else {
                if (typeof onError === 'function') onError();
            }
        } catch (err) {
            if (typeof onError === 'function') onError();
        }
    }

    // نمایش اعلان سبک و هماهنگ داخل کانتینر درگاه
    function showCopyToast(msg, isError) {
        var $toast = $('#wccc-copy-toast');
        if (!$toast.length) {
            $toast = $('<div id="wccc-copy-toast" class="wccc-copy-toast" style="display:none;"></div>');
            $('body').append($toast);
        }
        $toast.css({
            background: isError ? '#ef4444' : '#059669',
            color: '#ffffff'
        }).text(msg).stop(true, true).fadeIn(200).delay(2200).fadeOut(300);
    }

    $(document).ready(function() {

        // ۱. عملکرد دکمه‌های کپی شماره کارت، شبا و مبلغ (با احترام به تنظیم enable_copy_buttons)
        var isCopyEnabled = !(window.wccc_params && parseInt(window.wccc_params.enable_copy_buttons, 10) === 0);
        if (isCopyEnabled) {
            $(document).on('click', '[data-wccc-copy]', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var text = $btn.attr('data-wccc-copy');
                if (!text) return;

                copyToClipboard(text, function() {
                    var originalText = $btn.text();
                    $btn.addClass('wccc-copied');
                    $btn.text('کپی شد ✓');
                    showCopyToast('با موفقیت در کلیپ‌بورد کپی شد.');
                    setTimeout(function() {
                        $btn.text(originalText);
                        $btn.removeClass('wccc-copied');
                    }, 2000);
                }, function() {
                    showCopyToast('خطا در کپی خودکار. لطفاً دستی کپی فرمایید.', true);
                });
            });
        }

        // ۲. تصحیح زنده ارقام در فیلدهای عددی (۴ رقم کارت و شماره پیگیری)
        $(document).on('input keyup paste', '.wccc-numeric-input', function() {
            var $input = $(this);
            var raw = $input.val() || '';
            var clean = convertFaToEnDigits(raw).replace(/\D/g, '');
            var maxLen = parseInt($input.attr('maxlength'), 10);
            if (maxLen && maxLen > 0) {
                clean = clean.slice(0, maxLen);
            }
            if ($input.val() !== clean) {
                $input.val(clean);
            }
        });

        // ۳. تایمر شمارش معکوس و مکانیزم فال‌بک در زمان انقضا (Issue 3)
        var $timerDisplay = $('#wccc-countdown-display');
        if ($timerDisplay.length > 0) {
            var expiresTs = parseInt($timerDisplay.attr('data-expires'), 10);
            var timerInterval = null;

            function triggerExpirationFallback() {
                var $cardInput = $('#wccc_buyer_card_last4');
                // اگر ۴ رقم کارت قبلاً با موفقیت ثبت شده و قفل است، انقضا اعمال نشود
                if ($cardInput.prop('disabled')) {
                    return;
                }

                // الف) مخفی‌سازی بخش پنی آنی و تایمر
                $('#wccc-instant-fields-container').slideUp(200);
                $('#wccc-countdown-box').fadeOut(200);
                $('#wccc-instant-note').fadeOut(200);
                $('#wccc-penny-badge').fadeOut(200);

                // ب) نمایش پیام هشدار انقضا
                $('#wccc-expiration-alert').slideDown(250);

                // ج) نمایش کانتینر آپلود فیش دستی
                $('#wccc-receipt-upload-box, .wccc-receipt-upload-section').slideDown(250);
                $('#wccc-receipt-file').prop('required', true);
                $('#wccc_buyer_card_last4').prop('required', false);

                // د) تغییر حالت پرداخت به واریز عادی جهت پردازش فیش
                $('#wccc_payment_mode').val('manual_receipt');
                $('#wccc-btn-text').text('ارسال فیش و تکمیل سفارش');

                // هـ) طبق الزامات Fix 1: مبلغ تفکیک‌شده پنی‌دار به هیچ وجه از سفارش حذف یا کم نمی‌شود
                // رقم نمایشی و قابل کپی دقیقاً همان جمع کل فاکتور با پنی (_wccc_expected_total) باقی می‌ماند
                var $amountCard = $('#wccc-amount-card');
                if ($amountCard.length > 0) {
                    $('#wccc-amount-title').text('مبلغ واریزی فاکتور (شامل شناسه هوشمند):');
                }
            }

            function updateTimer() {
                var now = Math.floor(Date.now() / 1000);
                var diff = expiresTs - now;

                if (diff <= 0) {
                    $timerDisplay.text('۰۰:۰۰').css('color', '#ef4444');
                    if (timerInterval) {
                        clearInterval(timerInterval);
                    }
                    triggerExpirationFallback();
                    return;
                }

                var m = Math.floor(diff / 60);
                var s = diff % 60;
                var formatted = (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
                $timerDisplay.text(formatted);
            }

            updateTimer();
            timerInterval = setInterval(updateTimer, 1000);
        }

        // ۴. ارسال فرم ۴ رقم کارت و قفل دائمی فیلدها بدون رفرش (Issue 2)
        var $form = $('#wccc-receipt-upload-form');
        if ($form.length > 0) {
            $form.on('submit', function(e) {
                var currentMode = $('#wccc_payment_mode').val() || 'instant_auto';

                // فقط اگر در حالت آنی باشد، هندل اختصاصی ارسال ۴ رقم کارت را اجرا می‌کنیم
                if (currentMode === 'instant_auto') {
                    e.preventDefault();

                    var $cardLast4 = $('#wccc_buyer_card_last4');
                    var $tracking  = $('#wccc_buyer_tracking_number');
                    var $submitBtn = $('#wccc-submit-receipt-btn');
                    var $feedback  = $('#wccc-upload-feedback');
                    var $spinner   = $('#wccc-btn-spinner');
                    var $btnText   = $('#wccc-btn-text');

                    var cardVal = ($cardLast4.val() || '').replace(/\D/g, '');
                    if (cardVal.length !== 4) {
                        $feedback.html('<div class="wccc-feedback-inline" style="background:#fff1f2; border:1px solid #fecdd3; color:#9f1239; padding:10px 14px; border-radius:8px; margin-bottom:12px; font-size:12px; font-weight:600;">لطفاً ۴ رقم آخر شماره کارت واریزی را به صورت صحیح وارد فرمایید.</div>');
                        $cardLast4.focus();
                        return;
                    }

                    // قفل موقت دکمه حین ارسال جهت جلوگیری از ارسال موازی
                    if ($form.data('wccc-submitting')) {
                        return;
                    }
                    $form.data('wccc-submitting', true);

                    $submitBtn.prop('disabled', true);
                    $spinner.show();
                    $btnText.text('در حال ثبت...');

                    var formData = new FormData($form[0]);
                    formData.append('action', 'wccc_submit_payment');
                    if (!formData.get('security') && window.wccc_params && window.wccc_params.nonce) {
                        formData.append('security', window.wccc_params.nonce);
                    }
                    if (!formData.get('order_key')) {
                        var orderKeyVal = $form.find('input[name="order_key"]').val();
                        if (orderKeyVal) {
                            formData.append('order_key', orderKeyVal);
                        }
                    }

                    var ajaxUrl = (window.wccc_params && window.wccc_params.ajax_url) ? window.wccc_params.ajax_url : '/wp-admin/admin-ajax.php';

                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(res) {
                            $spinner.hide();
                            if (res && res.success) {
                                // نمایش پیام موفقیت فارسی خواسته شده
                                var successMsg = res.data.message || 'اطلاعات کارت شما با موفقیت ثبت شد و در انتظار تطبیق با پیامک بانکی است';
                                $feedback.html('<div class="wccc-feedback-inline" style="background:#ecfdf5; border:1px solid #10b981; color:#065f46; padding:12px 14px; border-radius:10px; font-size:13px; font-weight:600; text-align:right; direction:rtl; margin-bottom:12px;">✓ ' + successMsg + '</div>');

                                // قفل دائمی فیلدها و دکمه (بدون هیچ ریدایرکت یا رفرش صفحه)
                                $cardLast4.prop('disabled', true).css({ 'background': '#f1f5f9', 'cursor': 'not-allowed' });
                                $tracking.prop('disabled', true).css({ 'background': '#f1f5f9', 'cursor': 'not-allowed' });
                                $submitBtn.prop('disabled', true).css({ 'opacity': '0.7', 'cursor': 'not-allowed' });
                                $btnText.text('اطلاعات کارت ثبت شده است ✓');

                                // متوقف ساختن تایمر در صورت ثبت موفق
                                $('#wccc-countdown-box').fadeOut(200);
                            } else {
                                $form.data('wccc-submitting', false);
                                $submitBtn.prop('disabled', false);
                                $btnText.text('ثبت و تایید آنی سفارش');
                                var errMsg = (res && res.data && res.data.message) ? res.data.message : 'خطا در ثبت اطلاعات کارت.';
                                $feedback.html('<div class="wccc-feedback-inline" style="background:#fff1f2; border:1px solid #fecdd3; color:#9f1239; padding:10px 14px; border-radius:8px; margin-bottom:12px; font-size:12px; font-weight:600;">' + errMsg + '</div>');
                            }
                        },
                        error: function() {
                            $form.data('wccc-submitting', false);
                            $spinner.hide();
                            $submitBtn.prop('disabled', false);
                            $btnText.text('ثبت و تایید آنی سفارش');
                            $feedback.html('<div class="wccc-feedback-inline" style="background:#fff1f2; border:1px solid #fecdd3; color:#9f1239; padding:10px 14px; border-radius:8px; margin-bottom:12px; font-size:12px; font-weight:600;">خطا در برقراری ارتباط با سرور. لطفاً مجدداً تلاش فرمایید.</div>');
                        }
                    });
                }
            });
        }
    });
})(jQuery);
