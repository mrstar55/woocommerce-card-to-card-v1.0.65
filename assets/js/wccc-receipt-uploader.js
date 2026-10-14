(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        var fileInput = document.getElementById('wccc-receipt-file');
        var dropzone = document.getElementById('wccc-file-dropzone');
        var form = document.getElementById('wccc-receipt-upload-form');
        var previewContainer = document.getElementById('wccc-file-name-preview');
        var feedbackContainer = document.getElementById('wccc-upload-feedback');
        var submitBtn = document.getElementById('wccc-submit-receipt-btn');
        var btnSpinner = document.getElementById('wccc-btn-spinner');
        var btnText = document.getElementById('wccc-btn-text');

        // عناصر سوئیچ دوحالته و مبالغ داینامیک
        var modeRadios = document.querySelectorAll('input[name="wccc_payment_mode_radio"]');
        var instantCard = document.getElementById('wccc-mode-card-instant');
        var manualCard = document.getElementById('wccc-mode-card-manual');
        var modeHiddenInput = document.getElementById('wccc_payment_mode');

        var amountCard = document.getElementById('wccc-amount-card');
        var amountTitle = document.getElementById('wccc-amount-title');
        var amountFigureVal = document.getElementById('wccc-payable-figure-val');
        var copyPayableBtn = document.getElementById('wccc-copy-payable-btn');
        var pennyBadge = document.getElementById('wccc-penny-badge');

        var instantFieldsBox = document.getElementById('wccc-instant-fields-container');
        var cardLast4Input = document.getElementById('wccc_buyer_card_last4');
        var trackingInput = document.getElementById('wccc_buyer_tracking_number');
        var manualUploadBox = document.getElementById('wccc-receipt-upload-box');

        var hiddenTokenInput = document.getElementById('wccc_uploaded_receipt_token');
        if (!hiddenTokenInput && form) {
            hiddenTokenInput = document.createElement('input');
            hiddenTokenInput.type = 'hidden';
            hiddenTokenInput.name = 'wccc_uploaded_receipt_token';
            hiddenTokenInput.id = 'wccc_uploaded_receipt_token';
            form.appendChild(hiddenTokenInput);
        }

        var progressWrapper = document.getElementById('wccc-upload-progress-wrapper');
        if (!progressWrapper && dropzone) {
            progressWrapper = document.createElement('div');
            progressWrapper.id = 'wccc-upload-progress-wrapper';
            progressWrapper.style.display = 'none';
            progressWrapper.style.margin = '12px 0';
            progressWrapper.style.direction = 'rtl';
            progressWrapper.innerHTML = 
                '<div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px; color:#475569;">' +
                    '<span id="wccc-progress-label">در حال آپلود فیش...</span>' +
                    '<span id="wccc-progress-percent" style="font-family:monospace; font-weight:bold; color:#059669;">0%</span>' +
                '</div>' +
                '<div style="background:#e2e8f0; border-radius:8px; height:8px; overflow:hidden;">' +
                    '<div id="wccc-progress-bar" style="background:#059669; width:0%; height:100%; transition:width 0.2s ease;"></div>' +
                '</div>';
            dropzone.parentNode.insertBefore(progressWrapper, dropzone.nextSibling);
        }

        // ۴. رفع باگ ارقام در دسکتاپ، لپ‌تاپ و اینکوگنیتو با مهار رویدادهای input, keyup, paste
        function convertFaToEnDigits(str) {
            return (str || '')
                .replace(/[۰-۹]/g, function(d) { return String.fromCharCode(d.charCodeAt(0) - 1728); })
                .replace(/[٠-٩]/g, function(d) { return String.fromCharCode(d.charCodeAt(0) - 1584); });
        }

        function sanitizeNumericElement(input) {
            if (!input) return;
            var raw = input.value || '';
            var clean = convertFaToEnDigits(raw).replace(/\D/g, '');
            var maxLen = parseInt(input.getAttribute('maxlength'), 10);
            if (maxLen && maxLen > 0) {
                clean = clean.slice(0, maxLen);
            }
            if (input.value !== clean) {
                input.value = clean;
            }
        }

        ['input', 'keyup', 'paste'].forEach(function(eventType) {
            document.addEventListener(eventType, function(e) {
                if (e.target && e.target.classList && e.target.classList.contains('wccc-numeric-input')) {
                    if (eventType === 'paste') {
                        setTimeout(function() { sanitizeNumericElement(e.target); }, 0);
                    } else {
                        sanitizeNumericElement(e.target);
                    }
                }
            });
        });

        // ۲. رفتار تعاملی و پویای سوئیچ دوحالته
        function setPaymentMode(mode) {
            if (modeHiddenInput) modeHiddenInput.value = mode;

            if (mode === 'instant_auto') {
                if (instantCard) {
                    instantCard.style.borderColor = '#10b981';
                    instantCard.style.background = '#f0fdf4';
                }
                if (manualCard) {
                    manualCard.style.borderColor = '#cbd5e1';
                    manualCard.style.background = '#ffffff';
                }

                // مبلغ با پنی هوشمند
                if (amountCard) {
                    var formattedInstant = amountCard.getAttribute('data-formatted-instant');
                    var rawInstant = amountCard.getAttribute('data-amount-instant');
                    if (amountFigureVal && formattedInstant) amountFigureVal.innerText = formattedInstant;
                    if (copyPayableBtn && rawInstant) copyPayableBtn.setAttribute('data-wccc-copy', rawInstant);
                }
                if (amountTitle) amountTitle.innerText = 'مبلغ دقیق واریزی (شامل خورده پنی):';
                if (pennyBadge) pennyBadge.style.display = 'inline-block';

                // فیلدهای ۴ رقم کارت و پیگیری فعال، باکس آپلود فیش مخفی
                if (instantFieldsBox) instantFieldsBox.style.display = 'flex';
                if (cardLast4Input) cardLast4Input.required = true;

                if (manualUploadBox) manualUploadBox.style.display = 'none';
                if (fileInput) fileInput.required = false;

            } else if (mode === 'manual_receipt') {
                if (instantCard) {
                    instantCard.style.borderColor = '#cbd5e1';
                    instantCard.style.background = '#ffffff';
                }
                if (manualCard) {
                    manualCard.style.borderColor = '#10b981';
                    manualCard.style.background = '#f0fdf4';
                }

                // مبلغ بدون پنی (اصل فاکتور)
                if (amountCard) {
                    var formattedManual = amountCard.getAttribute('data-formatted-manual');
                    var rawManual = amountCard.getAttribute('data-amount-manual');
                    if (amountFigureVal && formattedManual) amountFigureVal.innerText = formattedManual;
                    if (copyPayableBtn && rawManual) copyPayableBtn.setAttribute('data-wccc-copy', rawManual);
                }
                if (amountTitle) amountTitle.innerText = 'مبلغ قابل پرداخت فاکتور:';
                if (pennyBadge) pennyBadge.style.display = 'none';

                // فیلدهای ۴ رقم کارت مخفی و غیرالزامی، باکس آپلود فیش نمایش داده شود
                if (instantFieldsBox) instantFieldsBox.style.display = 'none';
                if (cardLast4Input) {
                    cardLast4Input.required = false;
                }

                if (manualUploadBox) manualUploadBox.style.display = 'block';
                if (fileInput) fileInput.required = true;
            }
        }

        if (modeRadios && modeRadios.length > 0) {
            modeRadios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    setPaymentMode(this.value);
                });
            });
        }

        if (modeHiddenInput && modeHiddenInput.value) {
            setPaymentMode(modeHiddenInput.value);
        }

        // تایمر شمارش معکوس مهلت پرداخت آنی
        var timerEl = document.getElementById('wccc-countdown-display');
        if (timerEl) {
            var expiresTs = parseInt(timerEl.getAttribute('data-expires'), 10);
            var updateTimer = function() {
                var now = Math.floor(Date.now() / 1000);
                var diff = expiresTs - now;
                if (diff <= 0) {
                    timerEl.innerText = 'منقضی شده';
                    timerEl.style.color = '#ef4444';
                    return;
                }
                var m = Math.floor(diff / 60);
                var s = diff % 60;
                timerEl.innerText = (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
            };
            updateTimer();
            setInterval(updateTimer, 1000);
        }

        if (instantCard) {
            instantCard.addEventListener('click', function(e) {
                if (e.target.tagName !== 'INPUT') {
                    var r = instantCard.querySelector('input[type="radio"]');
                    if (r) { r.checked = true; setPaymentMode('instant_auto'); }
                }
            });
        }

        if (manualCard) {
            manualCard.addEventListener('click', function(e) {
                if (e.target.tagName !== 'INPUT') {
                    var r = manualCard.querySelector('input[type="radio"]');
                    if (r) { r.checked = true; setPaymentMode('manual_receipt'); }
                }
            });
        }

        // ۱. اعتبارسنجی فرمت فایل و آپلود ایزوله
        var allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        var maxFileSizeBytes = 5 * 1024 * 1024;

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                var file = fileInput.files[0];
                if (!file) return;

                var ext = file.name.split('.').pop().toLowerCase();
                if (allowedExtensions.indexOf(ext) === -1) {
                    showFeedback('فرمت فایل غیر مجاز است', 'error');
                    fileInput.value = '';
                    return;
                }

                if (file.size > maxFileSizeBytes) {
                    showFeedback('حجم فایل بیشتر از ۵ مگابایت است.', 'error');
                    fileInput.value = '';
                    return;
                }

                uploadFileViaAjax(file);
            });
        }

        function uploadFileViaAjax(file) {
            var progressBar = document.getElementById('wccc-progress-bar');
            var progressPercent = document.getElementById('wccc-progress-percent');
            var progressLabel = document.getElementById('wccc-progress-label');

            if (progressWrapper) progressWrapper.style.display = 'block';
            if (progressBar) progressBar.style.width = '0%';
            if (progressPercent) progressPercent.innerText = '0%';
            if (progressLabel) progressLabel.innerText = 'در حال آپلود فیش...';
            if (submitBtn) submitBtn.disabled = true;

            showFeedback('', 'clear');

            var formData = new FormData();
            formData.append('action', 'wccc_upload_receipt');
            formData.append('receipt_file', file);

            var nonceInput = form ? form.querySelector('input[name="security"]') : null;
            var nonceVal = nonceInput ? nonceInput.value : (window.wccc_params ? window.wccc_params.nonce : '');
            if (nonceVal) formData.append('security', nonceVal);

            var orderIdInput = form ? form.querySelector('input[name="order_id"]') : null;
            if (orderIdInput) formData.append('order_id', orderIdInput.value);

            var orderKeyInput = form ? form.querySelector('input[name="order_key"]') : null;
            if (orderKeyInput) formData.append('order_key', orderKeyInput.value);

            var ajaxUrl = (window.wccc_params && window.wccc_params.ajax_url) ? window.wccc_params.ajax_url : '/wp-admin/admin-ajax.php';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxUrl, true);

            xhr.upload.onprogress = function(event) {
                if (event.lengthComputable) {
                    var percent = Math.round((event.loaded / event.total) * 100);
                    if (progressBar) progressBar.style.width = percent + '%';
                    if (progressPercent) progressPercent.innerText = percent + '%';
                }
            };

            xhr.onload = function() {
                if (submitBtn) submitBtn.disabled = false;
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (xhr.status >= 200 && xhr.status < 300 && response.success) {
                        if (progressBar) progressBar.style.width = '100%';
                        if (progressPercent) progressPercent.innerText = '۱۰۰٪';
                        if (progressLabel) progressLabel.innerText = 'آپلود انجام شد';
                        if (hiddenTokenInput && response.data.token) hiddenTokenInput.value = response.data.token;
                        renderPreview(file, response.data);
                        showFeedback(response.data.message || 'فیش با موفقیت بارگذاری گردید.', 'success');
                    } else {
                        var errMsg = (response.data && response.data.message) ? response.data.message : 'خطا در آپلود.';
                        showFeedback(errMsg, 'error');
                        if (progressWrapper) progressWrapper.style.display = 'none';
                    }
                } catch(e) {
                    showFeedback('خطا در پاسخ سرور.', 'error');
                    if (progressWrapper) progressWrapper.style.display = 'none';
                }
            };

            xhr.onerror = function() {
                if (submitBtn) submitBtn.disabled = false;
                if (progressWrapper) progressWrapper.style.display = 'none';
                showFeedback('خطا در اتصال به سرور.', 'error');
            };

            xhr.send(formData);
        }

        function renderPreview(file, data) {
            if (!previewContainer) return;
            previewContainer.style.display = 'block';
            previewContainer.innerHTML = '';
            var p = document.createElement('div');
            p.style.display = 'flex';
            p.style.alignItems = 'center';
            p.style.gap = '8px';
            p.style.marginTop = '8px';
            p.style.background = '#f0fdf4';
            p.style.border = '1px solid #bbf7d0';
            p.style.borderRadius = '8px';
            p.style.padding = '8px 12px';

            if (data.is_pdf) {
                p.innerHTML = '<span style="font-size:20px;">📄</span><span style="font-size:12px; font-weight:600; color:#166534;">' + data.file_name + ' (فایل PDF)</span>';
            } else {
                var img = document.createElement('img');
                img.src = data.receipt_url;
                img.style.width = '48px';
                img.style.height = '48px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '6px';
                var text = document.createElement('span');
                text.style.fontSize = '12px';
                text.style.fontWeight = '600';
                text.style.color = '#166534';
                text.innerText = data.file_name + ' (آماده ثبت)';
                p.appendChild(img);
                p.appendChild(text);
            }
            previewContainer.appendChild(p);
        }

        // ۳. مهار لودینگ و قفل کردن دکمه ثبت نهایی پرداخت
        function lockSubmitButton() {
            if (submitBtn) {
                submitBtn.disabled = true;
                if (btnSpinner) btnSpinner.style.display = 'inline-block';
                if (btnText) btnText.innerText = 'در حال ثبت و پردازش سفارش...';
            }
        }

        function unlockSubmitButton() {
            if (submitBtn) {
                submitBtn.disabled = false;
                if (btnSpinner) btnSpinner.style.display = 'none';
                if (btnText) btnText.innerText = 'ثبت نهایی پرداخت';
            }
        }

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var currentMode = modeHiddenInput ? modeHiddenInput.value : 'instant_auto';

                if (currentMode === 'instant_auto') {
                    // حالت آنی توسط هندلر اختصاصی wccc-frontend.js پردازش و قفل می‌شود
                    return;
                } else if (currentMode === 'manual_receipt') {
                    var token = hiddenTokenInput ? hiddenTokenInput.value : '';
                    var hasFile = (fileInput && fileInput.files && fileInput.files.length > 0);
                    if (!token && !hasFile) {
                        showFeedback('لطفاً تصویر فیش واریزی را بارگذاری فرمایید.', 'error');
                        return;
                    }
                }

                lockSubmitButton();

                var formData = new FormData(form);
                formData.append('action', 'wccc_submit_payment');
                if (!formData.get('security') && window.wccc_params && window.wccc_params.nonce) {
                    formData.append('security', window.wccc_params.nonce);
                }
                if (!formData.get('order_key')) {
                    var orderKeyInput = form ? form.querySelector('input[name="order_key"]') : null;
                    if (orderKeyInput && orderKeyInput.value) {
                        formData.append('order_key', orderKeyInput.value);
                    }
                }

                var ajaxUrl = (window.wccc_params && window.wccc_params.ajax_url) ? window.wccc_params.ajax_url : '/wp-admin/admin-ajax.php';
                var xhr = new XMLHttpRequest();
                xhr.open('POST', ajaxUrl, true);
                xhr.onload = function() {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (xhr.status >= 200 && xhr.status < 300 && res.success) {
                            var defaultSuccessMsg = (currentMode === 'instant_auto') 
                                ? 'اطلاعات کارت شما با موفقیت ثبت شد و در انتظار تطبیق با پیامک بانکی است' 
                                : ((window.wccc_params && window.wccc_params.pending_review_message) ? window.wccc_params.pending_review_message : 'فیش شما با موفقیت ثبت شد و در انتظار بررسی مدیریت است.');
                            showFeedback(res.data.message || defaultSuccessMsg, 'success');
                            if (currentMode === 'instant_auto' || res.data.card_locked || res.data.no_redirect) {
                                if (cardLast4Input) cardLast4Input.disabled = true;
                                if (trackingInput) trackingInput.disabled = true;
                                if (submitBtn) {
                                    submitBtn.disabled = true;
                                    if (btnText) btnText.innerText = 'اطلاعات کارت ثبت شده است ✓';
                                }
                            } else if (res.data.redirect_url) {
                                setTimeout(function() { window.location.href = res.data.redirect_url; }, 1500);
                            }
                        } else {
                            unlockSubmitButton();
                            var msg = (res.data && res.data.message) ? res.data.message : 'خطا در ثبت اطلاعات.';
                            showFeedback(msg, 'error');
                        }
                    } catch(err) {
                        unlockSubmitButton();
                        showFeedback('خطا در ارتباط با سرور.', 'error');
                    }
                };
                xhr.onerror = function() {
                    unlockSubmitButton();
                    showFeedback('خطا در اتصال به سرور.', 'error');
                };
                xhr.send(formData);
            });
        }

        // عملکرد دکمه‌های کپی
        document.addEventListener('click', function(e) {
            var target = e.target.closest('[data-wccc-copy]');
            if (target) {
                var text = target.getAttribute('data-wccc-copy');
                if (text && navigator.clipboard) {
                    navigator.clipboard.writeText(text);
                    var originalText = target.innerText;
                    target.innerText = 'کپی شد ✓';
                    setTimeout(function() { target.innerText = originalText; }, 2000);
                }
            }
        });

        function showFeedback(msg, type) {
            if (!feedbackContainer) return;
            if (type === 'clear') {
                feedbackContainer.innerHTML = '';
                feedbackContainer.style.display = 'none';
                return;
            }
            feedbackContainer.style.display = 'block';
            feedbackContainer.style.marginTop = '10px';
            feedbackContainer.style.padding = '10px 14px';
            feedbackContainer.style.borderRadius = '8px';
            feedbackContainer.style.fontSize = '12px';
            if (type === 'success') {
                feedbackContainer.style.background = '#ecfdf5';
                feedbackContainer.style.color = '#065f46';
                feedbackContainer.innerHTML = '✓ ' + msg;
            } else {
                feedbackContainer.style.background = '#fef2f2';
                feedbackContainer.style.color = '#991b1b';
                feedbackContainer.innerHTML = '⚠️ ' + msg;
            }
        }
    });
})();
