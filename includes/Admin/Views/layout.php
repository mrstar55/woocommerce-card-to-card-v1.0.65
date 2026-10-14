<?php
/**
 * WCCC Admin Master Layout Template
 * Native WordPress & WooCommerce Enterprise Dashboard Layout
 *
 * @package WCCC\Admin\Views
 * @version 1.0.65
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wccc_allowed_tabs = array( 'dashboard', 'orders', 'cards', 'devices', 'settings', 'logs', 'health' );
$wccc_active_tab   = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard';
if ( ! in_array( $wccc_active_tab, $wccc_allowed_tabs, true ) ) {
    $wccc_active_tab = 'dashboard';
}

$layout_mode = get_option( 'wccc_admin_layout_mode', 'horizontal' );
if ( ! in_array( $layout_mode, array( 'horizontal', 'sidebar_compact', 'sidebar_classic' ), true ) ) {
    $layout_mode = 'horizontal';
}
$sticky_header = get_option( 'wccc_sticky_header', 'yes' );
$sticky_class  = ( 'yes' === $sticky_header ) ? 'wccc-has-sticky-header' : '';

// موتور انطباقی انتخاب فونت پیشخوان توسط مدیر (Adaptive Admin Font Engine)
$font_source = get_option( 'wccc_admin_font_source', 'inherit' );
$font_style  = ( 'vazirmatn' === $font_source )
    ? "--wccc-font-sans: 'Vazirmatn', -apple-system, BlinkMacSystemFont, Tahoma, sans-serif !important;"
    : '--wccc-font-sans: inherit, -apple-system, BlinkMacSystemFont, Tahoma, sans-serif !important;';
?>

<div class="wccc-wrap <?php echo esc_attr( $sticky_class ); ?>" id="wccc-app" data-theme="light" data-font-source="<?php echo esc_attr( $font_source ); ?>" style="<?php echo esc_attr( $font_style ); ?>">
    <!-- Immediate Theme Initialization (prevents flash of wrong theme) -->
    <script>
        (function() {
            var savedTheme = localStorage.getItem('wccc_theme') || 'light';
            var wrap = document.querySelector('.wccc-wrap');
            if (wrap) {
                wrap.setAttribute('data-theme', savedTheme);
            }
        })();
    </script>
    <div class="wccc-layout wccc-admin-wrapper wccc-layout-<?php echo esc_attr( $layout_mode ); ?> <?php echo esc_attr( $sticky_class ); ?>">

        <!-- Sidebar Navigation -->
        <aside class="wccc-sidebar">
            <div>
                <!-- Brand Header -->
                <div class="wccc-sidebar-brand">
                    <div class="wccc-brand-info">
                        <div class="wccc-brand-icon">
                            <span class="dashicons dashicons-money-alt" style="font-size: 22px; width: 22px; height: 22px;"></span>
                        </div>
                        <div>
                            <div class="wccc-brand-title"><?php esc_html_e( 'کارت به کارت هوشمند', 'wccc' ); ?></div>
                            <span class="wccc-brand-tag"><?php printf( esc_html__( 'نسخه %s', 'wccc' ), esc_html( defined( 'WCCC_VERSION' ) ? WCCC_VERSION : '1.0.0' ) ); ?></span>
                        </div>
                    </div>
                    <!-- دکمه سنجاق / پین کردن منو در حالت مینی -->
                    <button type="button" class="wccc-sidebar-pin-btn" id="wccc-sidebar-pin-btn" title="<?php esc_attr_e( 'سنجاق کردن منو', 'wccc' ); ?>" aria-label="<?php esc_attr_e( 'سنجاق کردن سایدبار', 'wccc' ); ?>">
                        <span class="dashicons dashicons-admin-post" aria-hidden="true"></span>
                    </button>
                </div>

                <!-- Status indicator -->
                <div class="wccc-sidebar-status-box">
                    <div class="wccc-status-pill">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="wccc-pulse-dot"></span>
                            <span style="font-size: 11px; font-weight: 700; color: #fff;"><?php esc_html_e( 'سیستم بانکی برخط', 'wccc' ); ?></span>
                        </div>
                        <span style="font-size: 10px; color: #94a3b8;"><?php esc_html_e( 'پینگ ۲۴ms', 'wccc' ); ?></span>
                    </div>
                </div>

				<!-- Navigation List -->
				<nav class="wccc-nav" aria-label="<?php esc_attr_e( 'بخش‌های مدیریت WCCC', 'wccc' ); ?>">
					<button type="button" class="wccc-nav-item <?php echo $wccc_active_tab === 'dashboard' ? 'active' : ''; ?>" data-wccc-tab="dashboard">
						<span class="dashicons dashicons-dashboard" aria-hidden="true"></span>
						<span class="wccc-nav-label"><?php esc_html_e( 'داشبورد مانیتورینگ', 'wccc' ); ?></span>
					</button>

					<button type="button" class="wccc-nav-item <?php echo $wccc_active_tab === 'orders' ? 'active' : ''; ?>" data-wccc-tab="orders">
						<span class="dashicons dashicons-clipboard" aria-hidden="true"></span>
						<span class="wccc-nav-label"><?php esc_html_e( 'سفارشات و رسیدها', 'wccc' ); ?></span>
					</button>

					<button type="button" class="wccc-nav-item <?php echo $wccc_active_tab === 'cards' ? 'active' : ''; ?>" data-wccc-tab="cards">
						<span class="dashicons dashicons-id-alt" aria-hidden="true"></span>
						<span class="wccc-nav-label"><?php esc_html_e( 'حساب‌ها و کارت‌ها', 'wccc' ); ?></span>
					</button>

					<button type="button" class="wccc-nav-item <?php echo $wccc_active_tab === 'devices' ? 'active' : ''; ?>" data-wccc-tab="devices">
						<span class="dashicons dashicons-smartphone" aria-hidden="true"></span>
						<span class="wccc-nav-label"><?php esc_html_e( 'دستگاه‌های اندروید', 'wccc' ); ?></span>
					</button>

					<button type="button" class="wccc-nav-item <?php echo $wccc_active_tab === 'settings' ? 'active' : ''; ?>" data-wccc-tab="settings">
						<span class="dashicons dashicons-admin-settings" aria-hidden="true"></span>
						<span class="wccc-nav-label"><?php esc_html_e( 'تنظیمات', 'wccc' ); ?></span>
					</button>

					<button type="button" class="wccc-nav-item <?php echo $wccc_active_tab === 'logs' ? 'active' : ''; ?>" data-wccc-tab="logs">
						<span class="dashicons dashicons-backup" aria-hidden="true"></span>
						<span class="wccc-nav-label"><?php esc_html_e( 'لاگ‌ها و رویدادها', 'wccc' ); ?></span>
					</button>

					<button type="button" class="wccc-nav-item <?php echo $wccc_active_tab === 'health' ? 'active' : ''; ?>" data-wccc-tab="health">
						<span class="dashicons dashicons-shield" aria-hidden="true"></span>
						<span class="wccc-nav-label"><?php esc_html_e( 'وضعیت سلامت سیستم', 'wccc' ); ?></span>
					</button>
				</nav>

            </div>

            <!-- Sidebar Footer -->
            <div class="wccc-sidebar-footer">
                <div class="wccc-footer-box">
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #fff;"><?php esc_html_e( 'نسخه سازمانی WCCC', 'wccc' ); ?></div>
                        <div style="font-size: 10px; color: #94a3b8;"><?php esc_html_e( 'پشتیبانی از HPOS ووکامرس', 'wccc' ); ?></div>
                    </div>
                    <span class="dashicons dashicons-yes-alt" style="font-size: 20px; width: 20px; height: 20px; color: #38bdf8;"></span>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="wccc-main">

            <!-- Sticky Header -->
            <header class="wccc-header">
                <!-- Breadcrumbs -->
                <div class="wccc-breadcrumb">
                    <span class="dashicons dashicons-admin-home" style="font-size: 18px; width: 18px; height: 18px;"></span>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=wccc-dashboard' ) ); ?>"><?php esc_html_e( 'سامانه کارت‌به‌کارت', 'wccc' ); ?></a>
                    <span>/</span>
                    <span id="wccc-breadcrumb-title" class="wccc-breadcrumb-current"><?php esc_html_e( 'داشبورد مالی و مانیتورینگ', 'wccc' ); ?></span>
                </div>

                <!-- Actions -->
                <div class="wccc-header-actions">
                    <!-- Dark / Light Mode Toggle -->
                    <button type="button" class="wccc-btn wccc-btn-secondary" id="wccc-theme-toggle" title="<?php esc_attr_e( 'تغییر پوسته تیره / روشن', 'wccc' ); ?>" style="padding: 8px;">
                        <span id="wccc-theme-icon" class="wccc-icon" style="font-size: 20px; line-height: 1;">dark_mode</span>
                    </button>

                    <!-- Webhook Simulator Trigger -->
                    <button type="button" class="wccc-btn wccc-btn-secondary" data-wccc-modal-open="wccc-modal-test-webhook">
                        <span class="dashicons dashicons-smartphone" style="font-size: 18px; width: 18px; height: 18px;"></span>
                        <span><?php esc_html_e( 'تست وب‌هوک', 'wccc' ); ?></span>
                    </button>

                    <!-- Add Card Modal Trigger -->
                    <button type="button" class="wccc-btn wccc-btn-secondary" data-wccc-modal-open="wccc-modal-add-card">
                        <span class="dashicons dashicons-plus-alt2" style="font-size: 18px; width: 18px; height: 18px;"></span>
                        <span><?php esc_html_e( 'افزودن کارت', 'wccc' ); ?></span>
                    </button>

                    <!-- Live Shaparak Sync Refresh -->
                    <button type="button" class="wccc-btn wccc-btn-primary" id="wccc-refresh-shaparak-btn">
                        <span class="dashicons dashicons-update" style="font-size: 18px; width: 18px; height: 18px;"></span>
                        <span><?php esc_html_e( 'همگام‌سازی شاپرک', 'wccc' ); ?></span>
                    </button>
                </div>
            </header>

            <!-- Views Container -->
            <div class="wccc-content">
                <?php
                include __DIR__ . '/dashboard.php';
                include __DIR__ . '/orders.php';
                include __DIR__ . '/cards.php';
                include __DIR__ . '/devices.php';
                include __DIR__ . '/settings.php';
                include __DIR__ . '/logs.php';
                include __DIR__ . '/health.php';
                ?>
            </div>

        </main>

    </div>

    <!-- Slide-over Receipt Inspection Drawer -->
    <div class="wccc-drawer" id="wccc-receipt-drawer">
        <div class="wccc-drawer-header">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'بررسی و تایید فیش سفارش', 'wccc' ); ?></h3>
                    <span id="wccc-drawer-badge" class="wccc-badge wccc-badge-primary">#WC-1042</span>
                </div>
                <div id="wccc-drawer-sub" style="font-size: 11px; color: var(--wccc-text-muted);">
                    <?php esc_html_e( 'سفارش #1042 ووکامرس • ثبت شده در دقایق اخیر', 'wccc' ); ?>
                </div>
            </div>
            <button type="button" class="wccc-btn wccc-btn-secondary" id="wccc-close-drawer-btn" style="padding: 6px; border: none; background: transparent;">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>

        <div style="padding: 24px; flex: 1; overflow-y: auto;">
            <!-- Receipt Image Preview -->
            <div style="background: var(--wccc-surface-subtle); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius-md); padding: 12px; margin-bottom: 20px; text-align: center;">
                <img id="wccc-drawer-receipt-img" src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&q=80&w=800" alt="تصویر فیش واریزی" style="max-width: 100%; height: auto; max-height: 320px; border-radius: 8px; object-fit: contain; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <div style="margin-top: 8px; display: flex; justify-content: center; gap: 8px;">
                    <a id="wccc-drawer-view-full" href="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&q=80&w=800" target="_blank" rel="noopener noreferrer" class="wccc-btn wccc-btn-secondary" style="font-size: 11px; padding: 4px 10px;">
                        <span class="dashicons dashicons-external" style="font-size: 14px; width: 14px; height: 14px;"></span>
                        <span><?php esc_html_e( 'مشاهده اندازه کامل', 'wccc' ); ?></span>
                    </a>
                </div>
            </div>

            <!-- Customer & Transaction Metadata -->
            <div style="background: var(--wccc-surface); border: 1px solid var(--wccc-border); border-radius: var(--wccc-radius-md); padding: 16px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 12px 0; font-size: 13px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'اطلاعات پرداخت مشتری', 'wccc' ); ?></h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 11px;">
                    <div>
                        <div style="color: var(--wccc-text-muted);"><?php esc_html_e( 'نام خریدار:', 'wccc' ); ?></div>
                        <div id="wccc-drawer-customer-name" style="font-weight: 700; color: var(--wccc-text);"><?php esc_html_e( 'امیرحسین رضایی', 'wccc' ); ?></div>
                    </div>
                    <div>
                        <div style="color: var(--wccc-text-muted);"><?php esc_html_e( 'کارت مبدا خریدار:', 'wccc' ); ?></div>
                        <div class="wccc-mono" style="font-weight: 700;"><?php esc_html_e( '۶۰۳۷ •••• ۵۱۱۲', 'wccc' ); ?></div>
                    </div>
                    <div>
                        <div style="color: var(--wccc-text-muted);"><?php esc_html_e( 'مبلغ فاکتور:', 'wccc' ); ?></div>
                        <div class="wccc-mono"><?php esc_html_e( '۲۵۰,۰۰۰ تومان', 'wccc' ); ?></div>
                    </div>
                    <div>
                        <div style="color: var(--wccc-text-muted);"><?php esc_html_e( 'کارمزد انحصاری پنی:', 'wccc' ); ?></div>
                        <div class="wccc-mono" style="font-weight: 800; color: var(--wccc-primary);"><?php esc_html_e( '+۳۴۰ تومان', 'wccc' ); ?></div>
                    </div>
                    <div>
                        <div style="color: var(--wccc-text-muted);"><?php esc_html_e( 'کل مبلغ قابل واریز:', 'wccc' ); ?></div>
                        <div class="wccc-mono" style="font-weight: 900; color: var(--wccc-success); font-size: 13px;"><?php esc_html_e( '۲۵۰,۳۴۰ تومان', 'wccc' ); ?></div>
                    </div>
                    <div>
                        <div style="color: var(--wccc-text-muted);"><?php esc_html_e( 'شماره ارجاع / RRN:', 'wccc' ); ?></div>
                        <div class="wccc-mono"><?php esc_html_e( '۹۸۲۳۰۴۸۱۲۹۵', 'wccc' ); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Drawer Footer Actions -->
        <div style="padding: 16px 24px; border-top: 1px solid var(--wccc-border); background-color: var(--wccc-surface-subtle); display: flex; align-items: center; justify-content: space-between; gap: 12px; position: sticky; bottom: 0;">
            <button type="button" class="wccc-btn wccc-btn-danger" id="wccc-drawer-reject-btn" data-order-id="1042">
                <span class="dashicons dashicons-dismiss"></span>
                <span><?php esc_html_e( 'رد کردن فیش', 'wccc' ); ?></span>
            </button>
            <button type="button" class="wccc-btn wccc-btn-success" id="wccc-drawer-approve-btn" data-order-id="1042">
                <span class="dashicons dashicons-yes-alt"></span>
                <span><?php esc_html_e( 'تایید فیش و تکمیل سفارش', 'wccc' ); ?></span>
            </button>
        </div>
    </div>

    <!-- Modal 1: Add Bank Card -->
    <div class="wccc-modal-backdrop" id="wccc-modal-add-card">
        <div class="wccc-modal" style="background: var(--wccc-surface); border-radius: var(--wccc-radius); width: 100%; max-width: 520px; overflow: hidden; box-shadow: var(--wccc-shadow-lg); border: 1px solid var(--wccc-border);">
            <div class="wccc-modal-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-plus-alt2" style="color: var(--wccc-primary);"></span>
                    <h3 id="wccc-card-modal-title" style="margin: 0; font-size: 14px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'افزودن کارت بانکی جدید به سامانه', 'wccc' ); ?></h3>
                </div>
                <button type="button" data-wccc-modal-close="wccc-modal-add-card" style="background: transparent; border: none; cursor: pointer; color: var(--wccc-text-muted);">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>

            <form id="wccc-add-card-form">
                <input type="hidden" name="card_id" id="wccc-card-form-id" value="">
                <input type="hidden" name="is_active" id="wccc-card-form-active" value="1">
                <div class="wccc-modal-body">
                    <?php wp_nonce_field( 'wccc_admin_nonce', 'nonce' ); ?>
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'نام بانک صادرکننده', 'wccc' ); ?></label>
                        <input type="text" name="bank_name" id="wccc-card-form-bank" placeholder="<?php esc_attr_e( 'مثال: بانک ملت، تجارت، سامان...', 'wccc' ); ?>" required style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'شماره کارت ۱۶ رقمی شتاب', 'wccc' ); ?></label>
                        <input type="text" name="card_number" id="wccc-card-form-number" class="wccc-card-number-input wccc-mono" placeholder="xxxx xxxx xxxx xxxx" maxlength="19" required style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 13px; font-weight: 800;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <label style="font-size: 11px; font-weight: 700; color: var(--wccc-text); margin: 0;"><?php esc_html_e( 'شماره شبا (IBAN اختیاری)', 'wccc' ); ?></label>
                            <span id="wccc-shaba-counter" class="wccc-mono" style="font-size: 11px; font-weight: 700; color: var(--wccc-text-muted); direction: ltr;">0 / 24</span>
                        </div>
                        <div style="position: relative; display: flex; align-items: center; direction: ltr;">
                            <span style="position: absolute; left: 10px; font-size: 12px; font-weight: 800; color: var(--wccc-text-muted); background: var(--wccc-border); padding: 2px 6px; border-radius: 4px; pointer-events: none; user-select: none; z-index: 1;">IR</span>
                            <input type="text" name="iban" id="wccc-card-form-iban" class="wccc-mono" placeholder="<?php esc_attr_e( 'مثال: 000000000000000000000000', 'wccc' ); ?>" maxlength="24" style="width: 100%; padding: 8px 12px 8px 38px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px; letter-spacing: 0.5px;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'نام و نام خانوادگی صاحب حساب', 'wccc' ); ?></label>
                            <input type="text" name="card_holder" id="wccc-card-form-holder" placeholder="<?php esc_attr_e( 'صاحب حساب', 'wccc' ); ?>" required style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'سقف پذیرش روزانه (تومان)', 'wccc' ); ?></label>
                            <input type="text" name="daily_limit" id="wccc-card-form-limit" value="100,000,000" placeholder="100,000,000" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;" class="wccc-mono">
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'سقف تعداد تراکنش روزانه', 'wccc' ); ?></label>
                        <input type="number" name="daily_tx_limit" id="wccc-card-form-tx-limit" value="50" min="1" step="1" placeholder="50" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;" class="wccc-mono">
                    </div>
                </div>

                <div class="wccc-modal-footer">
                    <button type="button" class="wccc-btn wccc-btn-secondary" data-wccc-modal-close="wccc-modal-add-card"><?php esc_html_e( 'انصراف', 'wccc' ); ?></button>
                    <button type="submit" class="wccc-btn wccc-btn-primary" id="wccc-submit-card-btn"><?php esc_html_e( 'ثبت و فعال‌سازی کارت', 'wccc' ); ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Webhook Simulator -->
    <div class="wccc-modal-backdrop" id="wccc-modal-test-webhook">
        <div class="wccc-modal" style="background: var(--wccc-surface); border-radius: var(--wccc-radius); width: 100%; max-width: 520px; overflow: hidden; box-shadow: var(--wccc-shadow-lg); border: 1px solid var(--wccc-border);">
            <div class="wccc-modal-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-smartphone" style="color: var(--wccc-primary);"></span>
                    <h3 style="margin: 0; font-size: 14px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'شبیه‌ساز تزریق پیامک شتابی به وب‌هوک', 'wccc' ); ?></h3>
                </div>
                <button type="button" data-wccc-modal-close="wccc-modal-test-webhook" style="background: transparent; border: none; cursor: pointer; color: var(--wccc-text-muted);">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>

            <form id="wccc-test-webhook-form">
                <div class="wccc-modal-body">
                    <p style="margin: 0 0 14px 0; font-size: 11px; color: var(--wccc-text-muted); line-height: 1.6;">
                        <?php esc_html_e( 'برای راستی‌آزمایی تطبیق خودکار پنی‌انجین، می‌توانید یک پیامک نمونه بانک را مستقیماً با امضای HMAC به پردازشگر افزونه ارسال کنید.', 'wccc' ); ?>
                    </p>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'متن خام پیامک واریز بانکی', 'wccc' ); ?></label>
                        <textarea name="raw_sms" rows="4" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 11px; line-height: 1.6; font-family: inherit;" required>بانک سامان
واریز: ۲۵۰,۳۴۰ تومان
به کارت: ۵۴۱۲
کد پیگیری: ۹۸۲۳۰۴۸۱۲۹۵
۱۴:۳۲:۱۰</textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'شماره سرشماره فرستنده', 'wccc' ); ?></label>
                            <input type="text" name="sender_number" value="20000189" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;" class="wccc-mono">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'مبلغ استخراجی (تومان)', 'wccc' ); ?></label>
                            <input type="number" name="amount" value="250340" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;" class="wccc-mono">
                        </div>
                    </div>
                </div>

                <div class="wccc-modal-footer">
                    <button type="button" class="wccc-btn wccc-btn-secondary" data-wccc-modal-close="wccc-modal-test-webhook"><?php esc_html_e( 'انصراف', 'wccc' ); ?></button>
                    <button type="submit" class="wccc-btn wccc-btn-primary" id="wccc-submit-test-webhook"><?php esc_html_e( 'ارسال و تست وب‌هوک', 'wccc' ); ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Manual Receipt Entry -->
    <div class="wccc-modal-backdrop" id="wccc-modal-manual-receipt">
        <div class="wccc-modal" style="background: var(--wccc-surface); border-radius: var(--wccc-radius); width: 100%; max-width: 480px; overflow: hidden; box-shadow: var(--wccc-shadow-lg); border: 1px solid var(--wccc-border);">
            <div class="wccc-modal-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-clipboard" style="color: var(--wccc-primary);"></span>
                    <h3 style="margin: 0; font-size: 14px; font-weight: 800; color: var(--wccc-text);"><?php esc_html_e( 'ثبت دستی رسید پرداخت', 'wccc' ); ?></h3>
                </div>
                <button type="button" data-wccc-modal-close="wccc-modal-manual-receipt" style="background: transparent; border: none; cursor: pointer; color: var(--wccc-text-muted);">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>

            <form id="wccc-manual-receipt-form">
                <div class="wccc-modal-body">
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'شناسه عددی سفارش ووکامرس', 'wccc' ); ?></label>
                        <input type="number" name="order_id" placeholder="1042" required style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;" class="wccc-mono">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--wccc-text); margin-bottom: 6px;"><?php esc_html_e( 'یادداشت یا نحوه احراز وجه', 'wccc' ); ?></label>
                        <input type="text" name="notes" placeholder="<?php esc_attr_e( 'مثال: فیش دریافتی در واتساپ، دستگاه پوز حضوری...', 'wccc' ); ?>" style="width: 100%; padding: 8px 12px; border-radius: var(--wccc-radius-md); border: 1px solid var(--wccc-border); background: var(--wccc-surface-subtle); color: var(--wccc-text); font-size: 12px;">
                    </div>
                </div>

                <div class="wccc-modal-footer">
                    <button type="button" class="wccc-btn wccc-btn-secondary" data-wccc-modal-close="wccc-modal-manual-receipt"><?php esc_html_e( 'انصراف', 'wccc' ); ?></button>
                    <button type="submit" class="wccc-btn wccc-btn-primary" id="wccc-submit-manual-receipt"><?php esc_html_e( 'تایید پرداخت و سفارش', 'wccc' ); ?></button>
                </div>
            </form>
        </div>
    </div>

</div>
