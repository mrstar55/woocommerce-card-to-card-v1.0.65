<?php
/**
 * WCCC Admin Android Sync Devices View
 * Native Enterprise Android SMS Sync Component
 *
 * @package WCCC\Admin\Views
 * @version 3.8.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wccc-view <?php echo ( isset( $_GET['tab'] ) && sanitize_key( $_GET['tab'] ) === 'devices' ) ? 'active' : ''; ?>" id="wccc-view-devices">

    <!-- Top Action Banner -->
    <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 18px 20px; margin-bottom: 24px; box-shadow: var(--wccc-shadow); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 800; color: var(--wccc-text);">دستگاه‌های متصل پیامک‌خوان اندروید (SMS Sync Nodes)</h3>
            <p style="margin: 0; font-size: 12px; color: var(--wccc-text-muted);">
                دریافت مستقیم و آنی پیامک‌های واریزی بانک از طریق اپلیکیشن اختصاصی با امضای رمزنگاری‌شده HMAC-SHA256.
            </p>
        </div>

        <button type="button" class="wccc-btn wccc-btn-primary" id="wccc-ping-all-devices-btn" style="font-size: 11px;">
            <span class="dashicons dashicons-rest-api" style="font-size: 16px; width: 16px; height: 16px;"></span>
            <span>ارسال پینگ به تمامی دستگاه‌ها</span>
        </button>
    </div>

    <!-- 2 Column Layout: Devices List + Pairing QR Box -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px;">

        <!-- Left: Connected Devices List -->
        <div style="display: flex; flex-direction: column; gap: 16px;">

            <!-- Node 1: Samsung Galaxy A15 -->
            <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 20px; box-shadow: var(--wccc-shadow);">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: var(--wccc-radius-md); background: rgba(0, 97, 148, 0.1); color: var(--wccc-primary); display: flex; align-items: center; justify-content: center;">
                            <span class="dashicons dashicons-smartphone" style="font-size: 26px; width: 26px; height: 26px;"></span>
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 14px; color: var(--wccc-text);">سامسونگ گلکسی A15 (دفتر مرکزی)</div>
                            <div class="wccc-mono" style="font-size: 11px; color: var(--wccc-text-muted);">ID: wccc_pos_mellat_01 • SM-A155F</div>
                        </div>
                    </div>
                    <span class="wccc-badge wccc-badge-success" style="font-size: 10px;">
                        <span class="wccc-pulse-dot" style="background: #10b981; margin-left: 4px;"></span>
                        <span>آنلاین و برخط</span>
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; padding: 12px; background: var(--wccc-surface-subtle); border-radius: var(--wccc-radius-md); font-size: 11px;">
                    <div>
                        <div style="color: var(--wccc-text-muted);">میزان باتری</div>
                        <div style="font-weight: 800; color: var(--wccc-success); display: flex; align-items: center; gap: 4px;">
                            <span class="dashicons dashicons-media-default" style="font-size: 14px; width: 14px; height: 14px;"></span>
                            <span>۹۴٪ (در حال شارژ)</span>
                        </div>
                    </div>
                    <div>
                        <div style="color: var(--wccc-text-muted);">تاخیر پینگ</div>
                        <div class="wccc-mono" style="font-weight: 800; color: var(--wccc-primary);">۱۸ ms</div>
                    </div>
                    <div>
                        <div style="color: var(--wccc-text-muted);">آخرین هارت‌بیت</div>
                        <div style="font-weight: 700; color: var(--wccc-text);">هم‌اکنون</div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 11px; color: var(--wccc-text-muted);">پیامک‌های دریافتی امروز: <strong>۱۴۲ پیامک</strong></span>
                    <button type="button" class="wccc-btn wccc-btn-secondary wccc-device-ping-btn" data-device-id="wccc_pos_mellat_01" style="padding: 4px 12px; font-size: 11px;">
                        <span class="dashicons dashicons-rss" style="font-size: 14px; width: 14px; height: 14px;"></span>
                        <span>ارسال پینگ</span>
                    </button>
                </div>
            </div>

            <!-- Node 2: Xiaomi Redmi 12 -->
            <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 20px; box-shadow: var(--wccc-shadow);">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: var(--wccc-radius-md); background: rgba(14, 165, 233, 0.1); color: #0284c7; display: flex; align-items: center; justify-content: center;">
                            <span class="dashicons dashicons-smartphone" style="font-size: 26px; width: 26px; height: 26px;"></span>
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 14px; color: var(--wccc-text);">شیائومی ردمی ۱۲ (انبار و بسته‌بندی)</div>
                            <div class="wccc-mono" style="font-size: 11px; color: var(--wccc-text-muted);">ID: wccc_pos_melli_02 • 23053RN02Y</div>
                        </div>
                    </div>
                    <span class="wccc-badge wccc-badge-success" style="font-size: 10px;">
                        <span class="wccc-pulse-dot" style="background: #10b981; margin-left: 4px;"></span>
                        <span>آنلاین و برخط</span>
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; padding: 12px; background: var(--wccc-surface-subtle); border-radius: var(--wccc-radius-md); font-size: 11px;">
                    <div>
                        <div style="color: var(--wccc-text-muted);">میزان باتری</div>
                        <div style="font-weight: 800; color: var(--wccc-text); display: flex; align-items: center; gap: 4px;">
                            <span class="dashicons dashicons-media-default" style="font-size: 14px; width: 14px; height: 14px;"></span>
                            <span>۶۸٪</span>
                        </div>
                    </div>
                    <div>
                        <div style="color: var(--wccc-text-muted);">تاخیر پینگ</div>
                        <div class="wccc-mono" style="font-weight: 800; color: var(--wccc-primary);">۳۱ ms</div>
                    </div>
                    <div>
                        <div style="color: var(--wccc-text-muted);">آخرین هارت‌بیت</div>
                        <div style="font-weight: 700; color: var(--wccc-text);">۲ دقیقه پیش</div>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 11px; color: var(--wccc-text-muted);">پیامک‌های دریافتی امروز: <strong>۵۸ پیامک</strong></span>
                    <button type="button" class="wccc-btn wccc-btn-secondary wccc-device-ping-btn" data-device-id="wccc_pos_melli_02" style="padding: 4px 12px; font-size: 11px;">
                        <span class="dashicons dashicons-rss" style="font-size: 14px; width: 14px; height: 14px;"></span>
                        <span>ارسال پینگ</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- Right: Pairing & Setup Box -->
        <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius); padding: 24px; box-shadow: var(--wccc-shadow); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <h4 style="margin: 0; font-size: 14px; font-weight: 800; color: var(--wccc-text);">اتصال گوشی جدید به افزونه</h4>
                    <span class="wccc-badge wccc-badge-warning" style="font-size: 10px;">انقضا: <span id="wccc-pairing-countdown" class="wccc-mono">۰۴:۵۹</span></span>
                </div>

                <!-- Futuristic QR Scanner Box -->
                <div class="wccc-qr-box" style="text-align: center;">
                    <div class="wccc-scan-beam"></div>
                    <div style="background: #fff; padding: 16px; border-radius: 1rem; display: inline-block; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                        <!-- SVG QR Representation -->
                        <svg width="140" height="140" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="140" height="140" fill="white"/>
                            <!-- Corners -->
                            <rect x="10" y="10" width="35" height="35" rx="4" fill="#0b132b"/>
                            <rect x="17" y="17" width="21" height="21" rx="2" fill="white"/>
                            <rect x="22" y="22" width="11" height="11" rx="1" fill="#006194"/>
                            
                            <rect x="95" y="10" width="35" height="35" rx="4" fill="#0b132b"/>
                            <rect x="102" y="17" width="21" height="21" rx="2" fill="white"/>
                            <rect x="107" y="22" width="11" height="11" rx="1" fill="#006194"/>

                            <rect x="10" y="95" width="35" height="35" rx="4" fill="#0b132b"/>
                            <rect x="17" y="102" width="21" height="21" rx="2" fill="white"/>
                            <rect x="22" y="107" width="11" height="11" rx="1" fill="#006194"/>
                            <!-- Data Dots Pattern -->
                            <rect x="55" y="15" width="8" height="8" fill="#0b132b"/>
                            <rect x="70" y="25" width="8" height="8" fill="#0b132b"/>
                            <rect x="55" y="40" width="8" height="8" fill="#0b132b"/>
                            <rect x="68" y="55" width="12" height="12" fill="#006194"/>
                            <rect x="15" y="55" width="8" height="8" fill="#0b132b"/>
                            <rect x="30" y="68" width="8" height="8" fill="#0b132b"/>
                            <rect x="95" y="55" width="8" height="8" fill="#0b132b"/>
                            <rect x="110" y="70" width="8" height="8" fill="#0b132b"/>
                            <rect x="55" y="85" width="8" height="8" fill="#0b132b"/>
                            <rect x="70" y="100" width="8" height="8" fill="#0b132b"/>
                            <rect x="95" y="100" width="8" height="8" fill="#0b132b"/>
                            <rect x="115" y="115" width="8" height="8" fill="#0b132b"/>
                        </svg>
                    </div>

                    <div style="margin-top: 14px; color: #94a3b8; font-size: 11px;">
                        کد اتصال سریع دستی:
                        <div id="wccc-pairing-code-display" class="wccc-mono" style="font-size: 18px; font-weight: 900; color: #38bdf8; letter-spacing: 2px; margin-top: 4px;">
                            WCC-8942
                        </div>
                    </div>
                </div>

                <ol style="margin: 0; padding-right: 18px; font-size: 11px; color: var(--wccc-text-muted); line-height: 1.8;">
                    <li>برنامه اندروید <strong>WCCC Sync</strong> را روی گوشی سیم‌کارت‌دار نصب کنید.</li>
                    <li>دوربین برنامه را مقابل QR Code قرار داده یا کد عددی را وارد نمایید.</li>
                    <li>کلیدهای HMAC و آدرس وب‌هوک به صورت خودکار رد و بدل خواهند شد.</li>
                </ol>
            </div>

            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--wccc-border); display: flex; justify-content: flex-end;">
                <button type="button" class="wccc-btn wccc-btn-secondary" id="wccc-regen-qr-btn" style="font-size: 11px;">
                    <span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px;"></span>
                    <span>تولید مجدد کد و QR</span>
                </button>
            </div>
        </div>

    </div>

</div>
