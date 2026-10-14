<?php
declare(strict_types=1);

namespace WCCC\Gateway;

use WCCC\Database\Schema;
use WCCC\Engine\PennyEngine;
use WCCC\Orders\StatusTransitionManager;
use WC_Payment_Gateway;
use WC_Order;

class WCCC_Gateway extends WC_Payment_Gateway {

    public string $card_assignment_mode;
    public string $card_number;
    public string $bank_name;
    public string $account_owner;
    public string $shaba_number;
    public bool $penny_enabled;
    public int $penny_min;
    public int $penny_max;
    public int $timeout_minutes;
    public string $status_auto_verified;
    public string $status_receipt_uploaded;
    public string $status_pending_payment;
    public string $auto_success_status;
    public string $auto_success_virtual_status;
    public string $manual_approved_status;
    public string $manual_approved_virtual_status;
    public string $manual_rejected_status;
    public string $order_instructions;
    public string $pending_review_message;
    public string $instant_pay_notice;
    public string $card_theme;
    public bool $show_bank_logo;
    public bool $enable_copy_buttons;
    public bool $show_shaba;

    public function __construct() {
        $this->id                 = 'wccc_card_to_card';
        $this->icon               = '';
        $this->has_fields         = true;
        $this->method_title       = __( 'کارت به کارت هوشمند (WCCC)', 'wccc' );
        $this->method_description = __( 'درگاه پرداخت کارت به کارت با قابلیت تفکیک خودکار مبالغ از طریق پنی (Penny Engine)، همگام‌سازی پیامک بانکی و ارسال فیش واریزی.', 'wccc' );
        $this->supports           = array( 'products' );

        $this->init_form_fields();
        $wccc_panel_settings        = get_option( 'wccc_gateway_settings', array() );
        $default_title              = ! empty( $wccc_panel_settings['gateway_title'] ) ? (string) $wccc_panel_settings['gateway_title'] : __( 'پرداخت کارت به کارت / انتقال بانکی', 'wccc' );
        $default_desc               = ! empty( $wccc_panel_settings['gateway_description'] ) ? (string) $wccc_panel_settings['gateway_description'] : __( 'انتقال وجه از طریق کارت به کارت، همراه بانک یا شماره شبا با ثبت مشخصات پرداخت.', 'wccc' );

        $this->title                  = $this->get_option( 'title', $default_title );
        $this->description            = $this->get_option( 'description', $default_desc );
        $this->order_instructions     = ! empty( $wccc_panel_settings['order_instructions'] ) ? (string) $wccc_panel_settings['order_instructions'] : __( 'لطفاً مبلغ مشخص‌شده را دقیقاً به یکی از شماره کارت‌های زیر واریز نمایید و سپس اطلاعات پرداخت را ثبت کنید.', 'wccc' );
        $this->pending_review_message = ! empty( $wccc_panel_settings['pending_review_message'] ) ? (string) $wccc_panel_settings['pending_review_message'] : __( 'اطلاعات پرداخت شما با موفقیت ثبت شد و در صف بررسی حسابداری قرار گرفت. پس از تأیید نهایی، وضعیت سفارش به‌روزرسانی خواهد شد.', 'wccc' );
        $this->instant_pay_notice     = ! empty( $wccc_panel_settings['instant_pay_notice'] ) ? (string) $wccc_panel_settings['instant_pay_notice'] : __( 'در صورت واریز دقیق مبلغ پنی‌دار، سفارش شما بلافاصله پس از دریافت پیامک بانکی به صورت هوشمند تطبیق داده خواهد شد.', 'wccc' );
        $this->card_theme             = ! empty( $wccc_panel_settings['card_theme'] ) ? sanitize_key( (string) $wccc_panel_settings['card_theme'] ) : 'modern_gold';
        $this->show_bank_logo         = ! isset( $wccc_panel_settings['show_bank_logo'] ) || ! empty( $wccc_panel_settings['show_bank_logo'] );
        $this->enable_copy_buttons    = ! isset( $wccc_panel_settings['enable_copy_buttons'] ) || ! empty( $wccc_panel_settings['enable_copy_buttons'] );
        $this->show_shaba             = ! isset( $wccc_panel_settings['show_shaba'] ) || ! empty( $wccc_panel_settings['show_shaba'] );
        $this->enabled                = $this->get_option( 'enabled', 'no' );
        $this->card_assignment_mode = sanitize_key( (string) $this->get_option( 'card_assignment_mode', 'auto' ) );
        $this->card_number          = sanitize_text_field( (string) $this->get_option( 'card_number', '' ) );
        $this->bank_name            = sanitize_text_field( (string) $this->get_option( 'bank_name', '' ) );
        $this->account_owner        = sanitize_text_field( (string) $this->get_option( 'account_owner', '' ) );
        $this->shaba_number         = sanitize_text_field( (string) $this->get_option( 'shaba_number', '' ) );
        $this->penny_enabled        = 'yes' === $this->get_option( 'penny_enabled', 'yes' );
        $this->penny_min            = max( 100, (int) $this->get_option( 'penny_min', 100 ) );
        $this->penny_max            = max( $this->penny_min + 10, (int) $this->get_option( 'penny_max', 999 ) );
        $this->timeout_minutes      = max( 5, (int) $this->get_option( 'timeout_minutes', 15 ) );
        $this->status_auto_verified    = sanitize_key( (string) $this->get_option( 'status_auto_verified', 'completed' ) );
        $this->status_receipt_uploaded = sanitize_key( (string) $this->get_option( 'status_receipt_uploaded', 'wccc-review' ) );
        $this->status_pending_payment  = sanitize_key( (string) $this->get_option( 'status_pending_payment', 'wccc-awaiting' ) );
        $this->auto_success_status             = sanitize_key( (string) $this->get_option( 'auto_success_status', 'processing' ) );
        $this->auto_success_virtual_status     = sanitize_key( (string) $this->get_option( 'auto_success_virtual_status', 'completed' ) );
        $this->manual_approved_status          = sanitize_key( (string) $this->get_option( 'manual_approved_status', 'processing' ) );
        $this->manual_approved_virtual_status  = sanitize_key( (string) $this->get_option( 'manual_approved_virtual_status', 'completed' ) );
        $this->manual_rejected_status          = sanitize_key( (string) $this->get_option( 'manual_rejected_status', 'cancelled' ) );

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
        add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
    }

    public function init_form_fields(): void {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'فعال‌سازی / غیرفعال‌سازی', 'wccc' ),
                'type'    => 'checkbox',
                'label'   => __( 'فعال‌سازی درگاه پرداخت کارت به کارت', 'wccc' ),
                'default' => 'no',
            ),
            'title' => array(
                'title'       => __( 'عنوان درگاه', 'wccc' ),
                'type'        => 'text',
                'default'     => __( 'پرداخت کارت به کارت / انتقال بانکی', 'wccc' ),
            ),
            'description' => array(
                'title'       => __( 'توضیحات درگاه', 'wccc' ),
                'type'        => 'textarea',
                'default'     => __( 'واریز وجه از طریق کلیه عابربانک‌ها و برنامه‌های پرداخت بانکی به شماره کارت یا شبا با ثبت مشخصات پرداخت.', 'wccc' ),
            ),
            'card_assignment_mode' => array(
                'title'       => __( 'نحوه تخصیص کارت مقصد', 'wccc' ),
                'type'        => 'select',
                'default'     => 'auto',
                'description' => __( 'در حالت «خودکار»، سیستم بر اساس سقف و ظرفیت روزانه کارت‌ها حساب بهینه را تخصیص می‌دهد. در حالت «انتخاب توسط خریدار»، مشتری در صفحه تسویه حساب می‌تواند کارت بانکی مقصد را از منوی کشویی انتخاب کند.', 'wccc' ),
                'desc_tip'    => true,
                'options'     => array(
                    'auto'            => __( 'تخصیص خودکار توسط سیستم (پیش‌فرض)', 'wccc' ),
                    'customer_choice' => __( 'انتخاب توسط خریدار در صفحه پرداخت', 'wccc' ),
                ),
            ),
            'cards_management_notice' => array(
                'title'       => __( 'منبع واحد حساب‌ها (SSOT)', 'wccc' ),
                'type'        => 'title',
                'description' => __( 'توجه: مدیریت کارت‌ها و حساب‌های مقصد از طریق منوی اختصاصی کارت‌به‌کارت > کارت‌ها و حساب‌ها انجام می‌شود. فیلدهای زیر صرفاً به عنوان کارت پشتیبان (فال‌بک در صورت عدم تعریف کارت در جدول) استفاده می‌شوند.', 'wccc' ),
            ),
            'bank_name' => array(
                'title'   => __( 'نام بانک (پشتیبان)', 'wccc' ),
                'type'    => 'text',
                'default' => __( 'بانک ملت', 'wccc' ),
            ),
            'card_number' => array(
                'title'       => __( 'شماره ۱۶ رقمی کارت بانکی (پشتیبان)', 'wccc' ),
                'type'        => 'text',
                'placeholder' => '6037997512345678',
            ),
            'account_owner' => array(
                'title'       => __( 'نام صاحب حساب / کارت (پشتیبان)', 'wccc' ),
                'type'        => 'text',
            ),
            'shaba_number' => array(
                'title'       => __( 'شماره شبا (اختیاری)', 'wccc' ),
                'type'        => 'text',
            ),
            'penny_enabled' => array(
                'title'   => __( 'فعال‌سازی موتور خورده مبلغ', 'wccc' ),
                'type'    => 'checkbox',
                'default' => 'yes',
            ),
            'penny_min' => array(
                'title'   => __( 'حداقل خورده مبلغ (تومان/ریال)', 'wccc' ),
                'type'    => 'number',
                'default' => 100,
            ),
            'penny_max' => array(
                'title'   => __( 'حداکثر خورده مبلغ (تومان/ریال)', 'wccc' ),
                'type'    => 'number',
                'default' => 999,
            ),
            'timeout_minutes' => array(
                'title'   => __( 'مهلت زمان واریز (دقیقه)', 'wccc' ),
                'type'    => 'number',
                'default' => 15,
            ),
            'status_receipt_uploaded' => array(
                'title'       => __( 'وضعیت پس از ارسال فیش توسط کاربر', 'wccc' ),
                'type'        => 'select',
                'description' => __( 'وضعیتی که سفارش پس از آپلود تصویر یا پی‌دی‌اف فیش واریزی به آن منتقل می‌شود.', 'wccc' ),
                'default'     => 'wccc-review',
                'desc_tip'    => true,
                'options'     => \WCCC\Orders\StatusTransitionManager::get_order_status_options(),
            ),
            'status_pending_payment' => array(
                'title'       => __( 'وضعیت سفارش در انتظار پرداخت', 'wccc' ),
                'type'        => 'select',
                'description' => __( 'وضعیت اولیه سفارش بلافاصله پس از ثبت در تسویه حساب تا پیش از پرداخت یا ارسال فیش.', 'wccc' ),
                'default'     => 'wccc-awaiting',
                'desc_tip'    => true,
                'options'     => \WCCC\Orders\StatusTransitionManager::get_order_status_options(),
            ),
            'status_mapping_title' => array(
                'title'       => __( 'مدیریت وضعیت سفارش', 'wccc' ),
                'type'        => 'title',
                'description' => __( 'تعیین وضعیت‌های مقصد برای سناریوهای تطبیق هوشمند و بررسی دستی فیش.', 'wccc' ),
            ),
            'auto_success_status' => array(
                'title'       => __( 'وضعیت پس از تطبیق و تأیید پرداخت', 'wccc' ),
                'type'        => 'select',
                'default'     => 'processing',
                'options'     => \WCCC\Orders\StatusTransitionManager::get_order_status_options(),
                'description' => __( 'برای فروشگاه‌های محصولات دانلودی، روی «تکمیل شده (completed)» قرار دهید تا لینک دانلود بلافاصله برای مشتری فعال گردد. برای محصولات فیزیکی «در حال انجام (processing)» توصیه می‌شود.', 'wccc' ),
                'desc_tip'    => true,
            ),
            'auto_success_virtual_status' => array(
                'title'       => __( 'وضعیت تطبیق پرداخت سفارش مجازی/دانلودی', 'wccc' ),
                'type'        => 'select',
                'default'     => 'completed',
                'options'     => \WCCC\Orders\StatusTransitionManager::get_order_status_options(),
                'description' => __( 'این وضعیت زمانی استفاده می‌شود که تمام اقلام سفارش مجازی یا دانلودی باشند.', 'wccc' ),
                'desc_tip'    => true,
            ),
            'manual_approved_status' => array(
                'title'       => __( 'وضعیت پس از تأیید دستی فیش', 'wccc' ),
                'type'        => 'select',
                'default'     => 'processing',
                'options'     => \WCCC\Orders\StatusTransitionManager::get_order_status_options(),
                'description' => __( 'وضعیتی که پس از کلیک مدیر روی دکمه «تأیید فیش» در صفحه سفارش اعمال می‌شود.', 'wccc' ),
                'desc_tip'    => true,
            ),
            'manual_approved_virtual_status' => array(
                'title'       => __( 'وضعیت تأیید دستی سفارش مجازی/دانلودی', 'wccc' ),
                'type'        => 'select',
                'default'     => 'completed',
                'options'     => \WCCC\Orders\StatusTransitionManager::get_order_status_options(),
                'description' => __( 'این وضعیت زمانی استفاده می‌شود که مدیر فیش سفارش کاملاً مجازی یا دانلودی را تأیید کند.', 'wccc' ),
                'desc_tip'    => true,
            ),
            'manual_rejected_status' => array(
                'title'       => __( 'وضعیت پس از رد فیش توسط مدیر', 'wccc' ),
                'type'        => 'select',
                'default'     => 'cancelled',
                'options'     => \WCCC\Orders\StatusTransitionManager::get_order_status_options(),
                'description' => __( 'وضعیتی که پس از کلیک مدیر روی دکمه «رد فیش» اعمال می‌شود.', 'wccc' ),
                'desc_tip'    => true,
            ),
        );
    }

    /**
     * دریافت لیست تمامی وضعیت‌های موجود سفارش در ووکامرس جهت استفاده در تنظیمات
     */
    private function get_order_status_options(): array {
        $statuses = function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array();
        $options  = array();
        foreach ( $statuses as $status_key => $status_label ) {
            $clean_key = str_replace( 'wc-', '', (string) $status_key );
            $options[ $clean_key ] = $status_label;
        }
        return $options;
    }

    /**
     * واکشی کارت‌های فعال از جدول wccc_cards (SSOT)
     */
    private function get_active_cards(): array {
        global $wpdb;
        $table = Schema::get_table_name( Schema::TABLE_CARDS );
        $table_escaped = esc_sql( $table );
        $results = $wpdb->get_results( "SELECT * FROM {$table_escaped} WHERE is_active = 1 ORDER BY id ASC" );
        return is_array( $results ) ? $results : array();
    }

    /**
     * رندر فیلدهای انتخاب شیوه پرداخت در صفحه تسویه حساب (Checkout)
     */
    public function payment_fields(): void {
        if ( ! empty( $this->description ) ) {
            echo wp_kses_post( wpautop( wptexturize( $this->description ) ) );
        }

        $active_cards = $this->get_active_cards();
        $is_customer_choice = ( 'customer_choice' === $this->card_assignment_mode && count( $active_cards ) > 1 );

        // نمایش کارت یا سلکتور کارت در صفحه تسویه حساب
        if ( ! empty( $active_cards ) ) {
            ?>
            <div class="wccc-checkout-destination-card-wrapper" style="margin-top: 15px; direction: rtl; text-align: right; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">
                    💳 <?php esc_html_e( 'حساب مقصد جهت واریز وجه:', 'wccc' ); ?>
                </label>

                <?php if ( $is_customer_choice ) : ?>
                    <!-- حالت منوی کشویی انتخاب کارت توسط خریدار (بیش از یک کارت فعال) -->
                    <select name="wccc_destination_card" 
                            id="wccc_destination_card_select" 
                            class="wccc-card-selector-dropdown" 
                            style="width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; margin-bottom: 10px; background: #ffffff; cursor: pointer;">
                        <?php foreach ( $active_cards as $idx => $ac ) : 
                            $clean_ac_num = preg_replace( '/\D/', '', (string) $ac->card_number );
                            $masked_ac_num = substr( $clean_ac_num, 0, 4 ) . ' - **** - **** - ' . substr( $clean_ac_num, -4 );
                            $shaba_val = ! empty( $ac->iban ) ? (string) $ac->iban : '';
                        ?>
                            <option value="<?php echo esc_attr( (string) $ac->id ); ?>"
                                    data-bank="<?php echo esc_attr( (string) $ac->bank_name ); ?>"
                                    data-card="<?php echo esc_attr( $clean_ac_num ); ?>"
                                    data-holder="<?php echo esc_attr( (string) $ac->card_holder ); ?>"
                                    data-shaba="<?php echo esc_attr( $shaba_val ); ?>"
                                    <?php selected( $idx, 0 ); ?>>
                                <?php echo esc_html( (string) $ac->bank_name . ' - ' . (string) $ac->card_holder . ' (' . $masked_ac_num . ')' ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <?php 
                    $first_card = $active_cards[0];
                    $first_card_clean = preg_replace( '/\D/', '', (string) $first_card->card_number );
                    $first_card_formatted = implode( ' - ', str_split( $first_card_clean, 4 ) );
                    ?>
                    <div id="wccc-selected-card-preview" class="wccc-bank-card-preview <?php echo esc_attr( 'wccc-card-theme-' . $this->card_theme ); ?>" style="border-radius: 8px; padding: 10px 14px; font-size: 12px; line-height: 1.8;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;"><?php esc_html_e( 'شماره کارت:', 'wccc' ); ?></span>
                            <strong id="wccc-preview-card-number" style="font-family: monospace, Tahoma; font-size: 14px; color: #0f172a; direction: ltr;"><?php echo esc_html( $first_card_formatted ); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;"><?php esc_html_e( 'صاحب حساب:', 'wccc' ); ?></span>
                            <span id="wccc-preview-card-holder" style="font-weight: 700; color: #1e293b;"><?php echo esc_html( (string) $first_card->card_holder ); ?></span>
                        </div>
                        <?php if ( $this->show_shaba && ! empty( $first_card->iban ) ) : ?>
                            <div id="wccc-preview-shaba-row" style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #64748b;"><?php esc_html_e( 'شماره شبا:', 'wccc' ); ?></span>
                                <span id="wccc-preview-card-shaba" style="font-family: monospace, Tahoma; font-size: 11px; color: #475569; direction: ltr;"><?php echo esc_html( (string) $first_card->iban ); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <script type="text/javascript">
                    (function() {
                        var cardSelect = document.getElementById('wccc_destination_card_select');
                        if (!cardSelect) return;
                        cardSelect.addEventListener('change', function() {
                            var opt = this.options[this.selectedIndex];
                            if (!opt) return;
                            var rawCard = opt.getAttribute('data-card') || '';
                            var holder = opt.getAttribute('data-holder') || '';
                            var shaba = opt.getAttribute('data-shaba') || '';
                            var formattedCard = rawCard.replace(/(\d{4})/g, '$1 - ').replace(/\s-\s$/, '');
                            
                            var cardEl = document.getElementById('wccc-preview-card-number');
                            var holderEl = document.getElementById('wccc-preview-card-holder');
                            var shabaEl = document.getElementById('wccc-preview-card-shaba');
                            var shabaRow = document.getElementById('wccc-preview-shaba-row');

                            if (cardEl) cardEl.textContent = formattedCard;
                            if (holderEl) holderEl.textContent = holder;
                            if (shabaEl) shabaEl.textContent = shaba;
                            if (shabaRow) shabaRow.style.display = shaba ? 'flex' : 'none';
                        });
                    })();
                    </script>
                <?php else : 
                    // حالت خودکار یا تنها ۱ کارت فعال موجود
                    $single_card = $active_cards[0];
                    $single_card_clean = preg_replace( '/\D/', '', (string) $single_card->card_number );
                    $single_card_formatted = implode( ' - ', str_split( $single_card_clean, 4 ) );
                ?>
                    <input type="hidden" name="wccc_destination_card" value="<?php echo esc_attr( (string) $single_card->id ); ?>" />
                    <div class="wccc-bank-card-preview <?php echo esc_attr( 'wccc-card-theme-' . $this->card_theme ); ?>" style="border-radius: 8px; padding: 10px 14px; font-size: 12px; line-height: 1.8;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;"><?php esc_html_e( 'بانک مقصد:', 'wccc' ); ?></span>
                            <span style="font-weight: 700; color: #1e293b;">
                                <?php if ( $this->show_bank_logo ) : ?><span class="wccc-bank-logo-icon">💳</span> <?php endif; ?>
                                <?php echo esc_html( (string) $single_card->bank_name ); ?>
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                            <span style="color: #64748b;"><?php esc_html_e( 'شماره کارت:', 'wccc' ); ?></span>
                            <strong style="font-family: monospace, Tahoma; font-size: 14px; color: #0f172a; direction: ltr;"><?php echo esc_html( $single_card_formatted ); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #64748b;"><?php esc_html_e( 'صاحب حساب:', 'wccc' ); ?></span>
                            <span style="font-weight: 700; color: #1e293b;"><?php echo esc_html( (string) $single_card->card_holder ); ?></span>
                        </div>
                        <?php if ( $this->show_shaba && ! empty( $single_card->iban ) ) : ?>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #64748b;"><?php esc_html_e( 'شماره شبا:', 'wccc' ); ?></span>
                                <span style="font-family: monospace, Tahoma; font-size: 11px; color: #475569; direction: ltr;"><?php echo esc_html( (string) $single_card->iban ); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        }

        if ( ! $this->penny_enabled ) {
            echo '<input type="hidden" name="wccc_checkout_mode" value="manual_receipt" />';
            return;
        }

        $current_mode = 'instant_auto';
        if ( isset( WC()->session ) ) {
            $current_mode = (string) WC()->session->get( 'wccc_payment_mode', 'instant_auto' );
        }
        ?>
        <div class="wccc-checkout-mode-container" style="margin-top: 15px; direction: rtl; text-align: right;">
            <span style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">
                <?php esc_html_e( 'شیوه پرداخت کارت به کارت را انتخاب فرمایید:', 'wccc' ); ?>
            </span>
            <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                <!-- گزینه ۱: پرداخت سریع با شناسه عددی -->
                <label class="wccc-checkout-mode-option <?php echo 'instant_auto' === $current_mode ? 'is-selected' : ''; ?>"
                       style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; border: 2px solid <?php echo 'instant_auto' === $current_mode ? '#10b981' : '#cbd5e1'; ?>; background: <?php echo 'instant_auto' === $current_mode ? '#f0fdf4' : '#ffffff'; ?>; border-radius: 12px; cursor: pointer; transition: all 0.2s ease;">
                    <input type="radio" 
                           name="wccc_checkout_mode" 
                           value="instant_auto" 
                           <?php checked( $current_mode, 'instant_auto' ); ?> 
                           style="margin-top: 4px; accent-color: #059669; cursor: pointer;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <strong style="font-size: 13px; color: #065f46; font-weight: 700;">
                                <?php esc_html_e( 'پرداخت سریع با شناسه عددی (تسریع در بررسی)', 'wccc' ); ?>
                            </strong>
                            <span style="background: #10b981; color: #fff; font-size: 10px; padding: 2px 8px; border-radius: 6px; font-weight: 700; white-space: nowrap;">
                                <?php esc_html_e( 'پیشنهادی (سریع)', 'wccc' ); ?>
                            </span>
                        </div>
                        <p style="font-size: 11px; color: #047857; margin: 4px 0 0; line-height: 1.6;">
                            <?php echo ! empty( $this->instant_pay_notice ) ? esc_html( $this->instant_pay_notice ) : esc_html__( 'یک شناسه عددی به عنوان خورده پرداخت به مبلغ فاکتور افزوده می‌شود تا با واریز دقیق آن، سفارش شما در کوتاه‌ترین زمان بررسی و تأیید گردد.', 'wccc' ); ?>
                        </p>
                    </div>
                </label>

                <!-- گزینه ۲: واریز عادی با آپلود فیش -->
                <label class="wccc-checkout-mode-option <?php echo 'manual_receipt' === $current_mode ? 'is-selected' : ''; ?>"
                       style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; border: 2px solid <?php echo 'manual_receipt' === $current_mode ? '#10b981' : '#cbd5e1'; ?>; background: <?php echo 'manual_receipt' === $current_mode ? '#f0fdf4' : '#ffffff'; ?>; border-radius: 12px; cursor: pointer; transition: all 0.2s ease;">
                    <input type="radio" 
                           name="wccc_checkout_mode" 
                           value="manual_receipt" 
                           <?php checked( $current_mode, 'manual_receipt' ); ?> 
                           style="margin-top: 4px; accent-color: #059669; cursor: pointer;">
                    <div style="flex: 1;">
                        <strong style="display: block; font-size: 13px; color: #334155; font-weight: 700;">
                            <?php esc_html_e( 'واریز عادی به کارت و ارسال فیش (بررسی اپراتور)', 'wccc' ); ?>
                        </strong>
                        <p style="font-size: 11px; color: #64748b; margin: 4px 0 0; line-height: 1.6;">
                            <?php esc_html_e( 'مبلغ خالص سفارش را واریز کرده و تصویر فیش رسید را در صفحه بعد ارسال می‌نمایید تا توسط مدیریت بررسی و تأیید شود.', 'wccc' ); ?>
                        </p>
                    </div>
                </label>
            </div>
        </div>
        <?php
    }

    public function enqueue_frontend_scripts(): void {
        if ( is_checkout() || is_order_received_page() || is_wc_endpoint_url( 'order-received' ) || is_wc_endpoint_url( 'order-pay' ) ) {
            $css_path      = WCCC_PLUGIN_DIR . 'assets/css/wccc-frontend.css';
            $js_front_path = WCCC_PLUGIN_DIR . 'assets/js/wccc-frontend.js';
            $js_rcpt_path  = WCCC_PLUGIN_DIR . 'assets/js/wccc-receipt-uploader.js';

            $css_ver      = file_exists( $css_path ) ? (string) filemtime( $css_path ) : (string) time();
            $js_front_ver = file_exists( $js_front_path ) ? (string) filemtime( $js_front_path ) : (string) time();
            $js_rcpt_ver  = file_exists( $js_rcpt_path ) ? (string) filemtime( $js_rcpt_path ) : (string) time();

            wp_enqueue_style( 'wccc-frontend', WCCC_PLUGIN_URL . 'assets/css/wccc-frontend.css', array(), $css_ver );
            wp_enqueue_script( 'wccc-frontend', WCCC_PLUGIN_URL . 'assets/js/wccc-frontend.js', array( 'jquery' ), $js_front_ver, true );
            wp_enqueue_script( 'wccc-receipt-uploader', WCCC_PLUGIN_URL . 'assets/js/wccc-receipt-uploader.js', array( 'jquery' ), $js_rcpt_ver, true );
            
            $wccc_panel_settings = get_option( 'wccc_gateway_settings', array() );
            $pending_review_msg  = ! empty( $wccc_panel_settings['pending_review_message'] ) ? (string) $wccc_panel_settings['pending_review_message'] : ( ! empty( $this->pending_review_message ) ? $this->pending_review_message : __( 'اطلاعات پرداخت شما با موفقیت ثبت شد و در صف بررسی حسابداری قرار گرفت. پس از تأیید نهایی، وضعیت سفارش به‌روزرسانی خواهد شد.', 'wccc' ) );

            $params = array(
                'ajax_url'               => admin_url( 'admin-ajax.php' ),
                'nonce'                  => wp_create_nonce( 'wccc_receipt_nonce' ),
                'pending_review_message' => $pending_review_msg,
                'enable_copy_buttons'    => $this->enable_copy_buttons ? 1 : 0,
            );
            wp_localize_script( 'wccc-frontend', 'wccc_params', $params );
            wp_localize_script( 'wccc-receipt-uploader', 'wccc_params', $params );
        }

        // اسکریپت تعاملی انتخاب حالت در تسویه حساب
        if ( is_checkout() && ! is_order_received_page() ) {
            $js_mode_path = WCCC_PLUGIN_DIR . 'assets/js/wccc-checkout-mode.js';
            $js_mode_ver  = file_exists( $js_mode_path ) ? (string) filemtime( $js_mode_path ) : (string) time();

            wp_enqueue_script(
                'wccc-checkout-mode',
                WCCC_PLUGIN_URL . 'assets/js/wccc-checkout-mode.js',
                array( 'jquery' ),
                $js_mode_ver,
                true
            );

            wp_localize_script(
                'wccc-checkout-mode',
                'wccc_checkout_params',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'wccc_checkout_nonce' ),
                )
            );
        }
    }

    public function process_payment( $order_id ): array {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return array( 'result' => 'failure', 'redirect' => '' );
        }

        // ۱. بررسی حالت انتخاب شده در صفحه تسویه حساب
        $mode = isset( $_POST['wccc_checkout_mode'] ) ? sanitize_key( $_POST['wccc_checkout_mode'] ) : '';
        if ( empty( $mode ) && isset( WC()->session ) ) {
            $mode = (string) WC()->session->get( 'wccc_payment_mode', 'instant_auto' );
        }
        if ( ! in_array( $mode, array( 'instant_auto', 'manual_receipt' ), true ) ) {
            $mode = $this->penny_enabled ? 'instant_auto' : 'manual_receipt';
        }
        if ( ! $this->penny_enabled ) {
            $mode = 'manual_receipt';
        }

        $now             = time();
        $timeout_minutes = max( 5, $this->timeout_minutes );
        $expires_at      = $now + ( $timeout_minutes * 60 );

        // ۲. بررسی وجود کارمزد پنی اضافه شده در تسویه (Cart Fee)
        $fee_title    = __( 'شناسه پرداخت هوشمند (پنی)', 'wccc' );
        $penny_amount = 0.0;
        foreach ( $order->get_fees() as $fee ) {
            if ( $fee->get_name() === $fee_title ) {
                $penny_amount = (float) $fee->get_total();
                break;
            }
        }

        if ( 'instant_auto' === $mode ) {
            // اگر کارمزد در Cart Fees ثبت نشده باشد، از سشن یا موتور پنی به عنوان فال‌بک استفاده شود
            if ( $penny_amount <= 0 && isset( WC()->session ) ) {
                $session_penny = (float) WC()->session->get( 'wccc_penny_offset', 0 );
                if ( $session_penny > 0 ) {
                    $penny_amount = $session_penny;
                    ReceiptUploader::set_order_penny_fee( $order, $penny_amount );
                }
            }

            if ( $penny_amount <= 0 && $this->penny_enabled ) {
                $allocated    = PennyEngine::allocate_amount( $order, $this->settings );
                $penny_amount = (float) $allocated['penny_offset'];
                if ( $penny_amount > 0 ) {
                    ReceiptUploader::set_order_penny_fee( $order, $penny_amount );
                }
            }

            $total_payable = (float) $order->get_total();
            $base_amount   = max( 0.0, $total_payable - $penny_amount );

            $order->update_meta_data( '_wccc_payment_mode', 'instant_auto' );
            $order->update_meta_data( '_wccc_penny_amount', (string) (int) $penny_amount );
            $order->update_meta_data( '_wccc_penny_offset', (string) (int) $penny_amount );
            $order->update_meta_data( '_wccc_base_amount', (string) $base_amount );
            $order->update_meta_data( '_wccc_allocated_amount', (string) $total_payable );
            $order->update_meta_data( '_wccc_expected_total', (string) $total_payable );
        } else {
            // حالت manual_receipt: اطمینان از عدم وجود هرگونه Fee پنی
            ReceiptUploader::remove_order_penny_fee( $order );

            $total_payable = (float) $order->get_total();

            $order->update_meta_data( '_wccc_payment_mode', 'manual_receipt' );
            $order->update_meta_data( '_wccc_penny_amount', '0' );
            $order->update_meta_data( '_wccc_penny_offset', '0' );
            $order->update_meta_data( '_wccc_base_amount', (string) $total_payable );
            $order->update_meta_data( '_wccc_allocated_amount', (string) $total_payable );
            $order->update_meta_data( '_wccc_expected_total', (string) $total_payable );
        }

        // ۳. تخصیص کارت مقصد (SSOT و پشتیبانی از انتخاب خریدار یا خودکار)
        global $wpdb;
        $dest_card_id     = isset( $_POST['wccc_destination_card'] ) ? absint( $_POST['wccc_destination_card'] ) : 0;
        $dest_card_row    = null;
        $cards_table      = Schema::get_table_name( Schema::TABLE_CARDS );
        $cards_table_esc  = esc_sql( $cards_table );

        if ( $dest_card_id > 0 ) {
            $dest_card_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$cards_table_esc} WHERE id = %d AND is_active = 1", $dest_card_id ) );
        }

        // اگر انتخاب نشده بود یا نامعتبر بود، اولین کارت فعال انتخاب شود
        if ( ! $dest_card_row ) {
            $dest_card_row = $wpdb->get_row( "SELECT * FROM {$cards_table_esc} WHERE is_active = 1 ORDER BY id ASC LIMIT 1" );
        }

        if ( $dest_card_row ) {
            $final_card_id    = (int) $dest_card_row->id;
            $final_card_num   = (string) $dest_card_row->card_number;
            $final_bank_name  = (string) $dest_card_row->bank_name;
            $final_owner_name = (string) $dest_card_row->card_holder;
            $final_shaba_num  = (string) $dest_card_row->iban;
        } else {
            // فال‌بک به تنظیمات استاتیک در صورت خالی بودن دیتابیس
            $final_card_id    = 0;
            $final_card_num   = $this->card_number;
            $final_bank_name  = $this->bank_name;
            $final_owner_name = $this->account_owner;
            $final_shaba_num  = $this->shaba_number;
        }

        $order->update_meta_data( '_wccc_destination_card_id', (string) $final_card_id );
        $order->update_meta_data( '_wccc_destination_card_number', $final_card_num );
        $order->update_meta_data( '_wccc_card_number', $final_card_num );
        $order->update_meta_data( '_wccc_bank_name', $final_bank_name );
        $order->update_meta_data( '_wccc_account_owner', $final_owner_name );
        $order->update_meta_data( '_wccc_shaba_number', $final_shaba_num );
        $order->update_meta_data( '_wccc_expires_at', (string) $expires_at );

        $target_pending_status = ! empty( $this->status_pending_payment ) ? $this->status_pending_payment : 'wccc-awaiting';
        $order->update_status( $target_pending_status, __( 'سفارش در انتظار پرداخت کارت به کارت ثبت شد.', 'wccc' ) );
        $order->save();

        // پاکسازی نشست موقت پس از ثبت سفارش
        if ( isset( WC()->session ) ) {
            WC()->session->__unset( 'wccc_payment_mode' );
            WC()->session->__unset( 'wccc_penny_offset' );
        }

        if ( isset( WC()->cart ) ) {
            WC()->cart->empty_cart();
        }

        return array( 'result' => 'success', 'redirect' => $this->get_return_url( $order ) );
    }

    public function thankyou_page( $order ): void {
        $order_obj = is_numeric( $order ) ? wc_get_order( $order ) : $order;
        if ( $order_obj ) {
            $this->render_card_payment_ui( $order_obj );
        }
    }

    public function receipt_page( $order_id ): void {
        $order = wc_get_order( $order_id );
        if ( $order ) {
            $this->render_card_payment_ui( $order );
        }
    }

    public function render_card_payment_ui( WC_Order $order ): void {
        $payment_mode = (string) $order->get_meta( '_wccc_payment_mode' );
        if ( empty( $payment_mode ) ) {
            $payment_mode = 'instant_auto';
        }
        $is_instant_mode = 'instant_auto' === $payment_mode;

        $base_total      = (float) $order->get_meta( '_wccc_base_amount' );
        if ( ! $base_total || $base_total <= 0 ) {
            $base_total = (float) $order->get_total();
        }
        $penny_offset    = (float) $order->get_meta( '_wccc_penny_offset' );
        if ( $penny_offset <= 0 ) {
            $penny_offset = (float) $order->get_meta( '_wccc_penny_amount' );
        }
        $allocated_total = (float) $order->get_meta( '_wccc_allocated_amount' );
        if ( ! $allocated_total || $allocated_total <= 0 ) {
            $allocated_total = (float) $order->get_meta( '_wccc_expected_total' );
        }
        if ( ! $allocated_total || $allocated_total <= 0 ) {
            $allocated_total = (float) $order->get_total();
        }

        // Fix 1: مبلغ تفکیک‌شده هوشمند پنی‌دار تحت هیچ شرایطی کسر نمی‌شود
        $has_penny_assigned = ( $penny_offset > 0 || $is_instant_mode );
        $payable_amount     = $has_penny_assigned ? $allocated_total : $base_total;

        $order_status       = $order->get_status();
        $order_status_slug  = str_replace( 'wc-', '', (string) $order_status );
        $completed_statuses = array( 'completed', 'processing' );
        if ( ! empty( $this->auto_success_status ) ) {
            $completed_statuses[] = str_replace( 'wc-', '', $this->auto_success_status );
        }
        if ( ! empty( $this->auto_success_virtual_status ) ) {
            $completed_statuses[] = str_replace( 'wc-', '', $this->auto_success_virtual_status );
        }
        if ( ! empty( $this->manual_approved_status ) ) {
            $completed_statuses[] = str_replace( 'wc-', '', $this->manual_approved_status );
        }
        if ( ! empty( $this->manual_approved_virtual_status ) ) {
            $completed_statuses[] = str_replace( 'wc-', '', $this->manual_approved_virtual_status );
        }
        if ( ! empty( $this->status_auto_verified ) ) {
            $completed_statuses[] = str_replace( 'wc-', '', $this->status_auto_verified );
        }
        $is_order_completed = in_array( $order_status_slug, array_unique( $completed_statuses ), true ) || ( method_exists( $order, 'is_paid' ) && $order->is_paid() );

        $stored_card_last4 = (string) $order->get_meta( '_wccc_card_last4' );
        if ( empty( $stored_card_last4 ) ) {
            $stored_card_last4 = (string) $order->get_meta( '_wccc_buyer_card_last4' );
        }
        $stored_tracking = (string) $order->get_meta( '_wccc_tracking_code' );
        if ( empty( $stored_tracking ) ) {
            $stored_tracking = (string) $order->get_meta( '_wccc_buyer_tracking_number' );
        }
        $is_card_locked = ! empty( $stored_card_last4 );

        // Fix 2: بررسی فیش ثبت شده و قفل کردن فرم
        $receipt_attach_id = (int) $order->get_meta( '_wccc_receipt_attachment_id' );
        $receipt_url       = (string) $order->get_meta( '_wccc_receipt_url' );
        if ( empty( $receipt_url ) && $receipt_attach_id > 0 && function_exists( 'wp_get_attachment_url' ) ) {
            $receipt_url = (string) wp_get_attachment_url( $receipt_attach_id );
        }
        $is_receipt_locked = ( $receipt_attach_id > 0 || ! empty( $receipt_url ) );

        $card_number     = (string) $order->get_meta( '_wccc_card_number' ) ?: $this->card_number;
        $clean_card      = preg_replace( '/\D/', '', $card_number );
        $formatted_card  = ! empty( $clean_card ) ? implode( ' - ', str_split( $clean_card, 4 ) ) : $card_number;
        $bank_name       = (string) $order->get_meta( '_wccc_bank_name' ) ?: $this->bank_name;
        $owner_name      = (string) $order->get_meta( '_wccc_account_owner' ) ?: $this->account_owner;
        $shaba           = (string) $order->get_meta( '_wccc_shaba_number' );
        $expires_at      = (int) $order->get_meta( '_wccc_expires_at' );
        $currency        = get_woocommerce_currency_symbol();
        $is_expired_fallback = ( $is_instant_mode && $expires_at > 0 && $expires_at <= time() && ! $is_card_locked && ! $is_order_completed );
        ?>
        <div class="wccc-payment-wrapper">
            <?php if ( ! empty( $this->order_instructions ) ) : ?>
                <div class="wccc-order-instructions-box" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; margin-bottom: 18px; direction: rtl; text-align: right; color: #334155; font-size: 13px; line-height: 1.8; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                        <span style="font-size: 16px;">📋</span>
                        <strong style="font-size: 13px; color: #1e293b;"><?php esc_html_e( 'راهنمای پرداخت و تکمیل سفارش', 'wccc' ); ?></strong>
                    </div>
                    <div style="color: #475569; font-size: 12.5px;"><?php echo nl2br( esc_html( $this->order_instructions ) ); ?></div>
                </div>
            <?php endif; ?>
            <div class="wccc-bank-card <?php echo esc_attr( 'wccc-card-theme-' . $this->card_theme ); ?>">
                <div class="wccc-card-header">
                    <div class="wccc-bank-brand">
                        <?php if ( $this->show_bank_logo ) : ?>
                            <span class="wccc-bank-logo-icon">💳</span>
                        <?php endif; ?>
                        <span><?php echo esc_html( $bank_name ); ?></span>
                    </div>
                    <div class="wccc-chip-icon"></div>
                </div>
                <div class="wccc-card-number-wrapper">
                    <span class="wccc-card-label"><?php esc_html_e( 'شماره کارت جهت واریز:', 'wccc' ); ?></span>
                    <div class="wccc-card-number" <?php echo $this->enable_copy_buttons ? 'data-wccc-copy="' . esc_attr( $clean_card ) . '"' : ''; ?>>
                        <?php echo esc_html( $formatted_card ); ?>
                    </div>
                </div>
                <div class="wccc-card-footer">
                    <div>
                        <span class="wccc-card-label"><?php esc_html_e( 'صاحب حساب:', 'wccc' ); ?></span>
                        <span class="wccc-card-owner"><?php echo esc_html( $owner_name ); ?></span>
                    </div>
                    <?php if ( $this->enable_copy_buttons ) : ?>
                        <button type="button" class="wccc-copy-badge" data-wccc-copy="<?php echo esc_attr( $clean_card ); ?>">
                            <?php esc_html_e( 'کپی شماره کارت', 'wccc' ); ?>
                        </button>
                    <?php endif; ?>
                </div>
                <?php if ( $this->show_shaba && ! empty( $shaba ) ) : ?>
                    <div class="wccc-shaba-box">
                        <span class="wccc-shaba-text"><?php echo esc_html( $shaba ); ?></span>
                        <?php if ( $this->enable_copy_buttons ) : ?>
                            <span class="wccc-shaba-copy-btn" data-wccc-copy="<?php echo esc_attr( $shaba ); ?>"><?php esc_html_e( 'کپی شبا', 'wccc' ); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- وضعیت شیوه پرداخت مشخص‌شده در تسویه (بدون رادیوباتن تکراری) -->
            <div class="wccc-selected-mode-badge" style="margin-bottom: 16px; padding: 12px 16px; border-radius: 12px; background: <?php echo $is_order_completed ? '#ecfdf5' : ( $is_instant_mode ? '#f0fdf4' : '#f8fafc' ); ?>; border: 1px solid <?php echo $is_order_completed ? '#10b981' : ( $is_instant_mode ? '#10b981' : '#cbd5e1' ); ?>; display: flex; align-items: center; justify-content: space-between; direction: rtl;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 16px;"><?php echo $is_order_completed ? '✅' : ( $is_instant_mode ? '⚡' : '📝' ); ?></span>
                    <div>
                        <strong style="font-size: 13px; color: <?php echo $is_order_completed ? '#065f46' : ( $is_instant_mode ? '#065f46' : '#1e293b' ); ?>;">
                            <?php
                            if ( $is_order_completed ) {
                                esc_html_e( 'وضعیت سفارش: پرداخت تایید و تکمیل شده است', 'wccc' );
                            } elseif ( $is_instant_mode ) {
                                esc_html_e( 'شیوه پرداخت: پرداخت سریع با شناسه عددی (تطبیق هوشمند)', 'wccc' );
                            } else {
                                esc_html_e( 'شیوه پرداخت: واریز عادی با بررسی فیش', 'wccc' );
                            }
                            ?>
                        </strong>
                        <span style="display: block; font-size: 11px; color: <?php echo $is_order_completed ? '#047857' : ( $is_instant_mode ? '#047857' : '#64748b' ); ?>;">
                            <?php
                            if ( $is_order_completed ) {
                                esc_html_e( 'تراکنش پرداخت شما با موفقیت تایید و سفارش نهایی گردیده است.', 'wccc' );
                            } elseif ( $is_instant_mode ) {
                                esc_html_e( 'انتخاب‌شده در مرحله تسویه حساب (نیازمند واریز دقیق مبلغ فاکتور و ثبت ۴ رقم کارت)', 'wccc' );
                            } else {
                                esc_html_e( 'انتخاب‌شده در مرحله تسویه حساب (نیازمند ارسال تصویر فیش واریزی)', 'wccc' );
                            }
                            ?>
                        </span>
                    </div>
                </div>
                <?php if ( $is_instant_mode && ! $is_card_locked && ! $is_order_completed && $expires_at > time() ) : ?>
                    <div class="wccc-timer-box" id="wccc-countdown-box" style="font-family: monospace; font-size: 13px; font-weight: 700; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; padding: 4px 10px; border-radius: 8px;">
                        ⏱️ <span id="wccc-countdown-display" data-expires="<?php echo esc_attr( (string) $expires_at ); ?>">--:--</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- بخش مبلغ پرداختی -->
            <div class="wccc-amount-card"
                 id="wccc-amount-card"
                 data-amount-instant="<?php echo esc_attr( (string) (int) $allocated_total ); ?>"
                 data-formatted-instant="<?php echo esc_attr( number_format_i18n( (int) $allocated_total ) ); ?>"
                 data-amount-manual="<?php echo esc_attr( (string) (int) ( $has_penny_assigned ? $allocated_total : $base_total ) ); ?>"
                 data-formatted-manual="<?php echo esc_attr( number_format_i18n( (int) ( $has_penny_assigned ? $allocated_total : $base_total ) ) ); ?>"
                 data-penny-offset="<?php echo esc_attr( (string) (int) $penny_offset ); ?>"
                 data-currency="<?php echo esc_attr( $currency ); ?>">
                <div class="wccc-amount-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span class="wccc-amount-title" id="wccc-amount-title">
                        <?php echo $is_instant_mode ? esc_html__( 'مبلغ دقیق واریزی (شامل خورده پنی هوشمند):', 'wccc' ) : esc_html__( 'مبلغ قابل پرداخت فاکتور:', 'wccc' ); ?>
                    </span>
                    <?php if ( $has_penny_assigned && $penny_offset > 0 ) : ?>
                        <span class="wccc-penny-badge" id="wccc-penny-badge" style="background: #ecfdf5; color: #065f46; font-size: 11px; padding: 3px 8px; border-radius: 6px; font-weight: 600;">
                            +<?php echo esc_html( number_format_i18n( (int) $penny_offset ) ); ?> <?php esc_html_e( 'خورده پنی هوشمند', 'wccc' ); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="wccc-payable-figure">
                    <span id="wccc-payable-figure-val"><?php echo esc_html( number_format_i18n( (int) $payable_amount ) ); ?></span>
                    <span><?php echo esc_html( $currency ); ?></span>
                    <button type="button" class="wccc-copy-badge" id="wccc-copy-payable-btn" data-wccc-copy="<?php echo esc_attr( (string) (int) $payable_amount ); ?>">
                        <?php esc_html_e( 'کپی مبلغ', 'wccc' ); ?>
                    </button>
                </div>
                <?php if ( $is_instant_mode ) : ?>
                    <p id="wccc-instant-note" style="font-size: 11px; color: #047857; margin: 8px 0 0; line-height: 1.6;">
                        <?php echo ! empty( $this->instant_pay_notice ) ? esc_html( $this->instant_pay_notice ) : esc_html__( 'لطفاً دقیقاً این مبلغ را کارت‌به‌کارت نمایید و ۴ رقم آخر کارت را ثبت فرمایید تا تطبیق و پردازش سفارش سریع‌تر انجام شود.', 'wccc' ); ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="wccc-checkout-action-box" style="background: #fff; border: 1px solid #cbd5e1; border-radius: 14px; padding: 22px;">
                <form id="wccc-receipt-upload-form" enctype="multipart/form-data">
                    <input type="hidden" name="order_id" value="<?php echo esc_attr( (string) $order->get_id() ); ?>">
                    <input type="hidden" name="order_key" value="<?php echo esc_attr( $order->get_order_key() ); ?>">
                    <input type="hidden" name="payment_mode" id="wccc_payment_mode" value="<?php echo esc_attr( $is_expired_fallback ? 'manual_receipt' : $payment_mode ); ?>">
                    <input type="hidden" name="wccc_uploaded_receipt_token" id="wccc_uploaded_receipt_token" value="">
                    <?php wp_nonce_field( 'wccc_receipt_nonce', 'security' ); ?>

                    <!-- باکس هشدار انقضای مهلت پرداخت آنی -->
                    <div id="wccc-expiration-alert" style="<?php echo ( $is_expired_fallback && ! $is_receipt_locked && ! $is_order_completed ) ? 'display: block;' : 'display: none;'; ?> background: #fff1f2; border: 1px solid #fecdd3; color: #9f1239; padding: 12px 14px; border-radius: 10px; margin-bottom: 14px; font-weight: 600; font-size: 13px; line-height: 1.6; text-align: right; direction: rtl;">
                        ⚠️ <?php esc_html_e( 'مهلت پرداخت آنی به پایان رسید. در صورت واریز، لطفاً تصویر فیش را ارسال نمایید.', 'wccc' ); ?>
                    </div>

                    <?php if ( $is_receipt_locked ) : ?>
                        <!-- نمایش فیش قبلاً ثبت‌شده در حالت قفل (Fix 2) -->
                        <div class="wccc-locked-receipt-card" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 16px; margin-bottom: 16px; text-align: center; direction: rtl;">
                            <?php if ( ! empty( $receipt_url ) && preg_match( '/\.(jpg|jpeg|png|webp)($|\?)/i', $receipt_url ) ) : ?>
                                <div style="margin-bottom: 10px;">
                                    <img src="<?php echo esc_url( $receipt_url ); ?>" alt="<?php esc_attr_e( 'تصویر فیش ثبت‌شده', 'wccc' ); ?>" style="max-height: 140px; border-radius: 8px; border: 1px solid #cbd5e1; object-fit: contain; margin: 0 auto; display: block;">
                                </div>
                            <?php elseif ( ! empty( $receipt_url ) ) : ?>
                                <div style="margin-bottom: 10px;">
                                    <a href="<?php echo esc_url( $receipt_url ); ?>" target="_blank" rel="noopener noreferrer" style="font-size: 13px; color: #0284c7; font-weight: 600; text-decoration: underline;">
                                        📄 <?php esc_html_e( 'مشاهده فایل فیش ارسالی', 'wccc' ); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if ( ! empty( $stored_tracking ) ) : ?>
                                <div style="font-size: 12px; color: #475569; margin-top: 6px;">
                                    <?php esc_html_e( 'کد پیگیری:', 'wccc' ); ?> <strong style="font-family: monospace; direction: ltr;"><?php echo esc_html( $stored_tracking ); ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( $is_instant_mode && ! $is_expired_fallback && ! $is_receipt_locked ) : ?>
                        <!-- فیلدهای حالت الف: تأیید آنی و هوشمند -->
                        <div id="wccc-instant-fields-container" class="wccc-form-row-group" style="display: flex; gap: 12px; margin-bottom: 16px; direction: rtl;">
                            <div style="flex: 1;">
                                <label for="wccc_buyer_card_last4" style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                    <?php esc_html_e( '۴ رقم آخر شماره کارت واریزی:', 'wccc' ); ?> <span style="color: #e11d48;">*</span>
                                </label>
                                <input type="text" id="wccc_buyer_card_last4" name="buyer_card_last4" maxlength="4" class="wccc-numeric-input" placeholder="مثال: 4192" value="<?php echo esc_attr( $stored_card_last4 ); ?>" <?php echo ( $is_card_locked || $is_order_completed ) ? 'disabled="disabled"' : 'required'; ?> style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; font-family: monospace; font-size: 14px; direction: ltr; text-align: center; <?php echo ( $is_card_locked || $is_order_completed ) ? 'background:#f1f5f9; cursor:not-allowed;' : ''; ?>">
                            </div>
                            <div style="flex: 1;">
                                <label for="wccc_buyer_tracking_number" style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                    <?php esc_html_e( 'شماره پیگیری / ارجاع:', 'wccc' ); ?> <span style="font-size: 10px; color: #64748b;">(اختیاری)</span>
                                </label>
                                <input type="text" id="wccc_buyer_tracking_number" name="buyer_tracking_number" class="wccc-numeric-input" placeholder="مثال: 9482710" value="<?php echo esc_attr( $stored_tracking ); ?>" <?php echo ( $is_card_locked || $is_order_completed ) ? 'disabled="disabled"' : ''; ?> style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; font-family: monospace; font-size: 14px; direction: ltr; text-align: right; <?php echo ( $is_card_locked || $is_order_completed ) ? 'background:#f1f5f9; cursor:not-allowed;' : ''; ?>">
                            </div>
                        </div>

                        <!-- باکس آپلود تصویر فیش: فال‌بک در زمان انقضا -->
                        <div id="wccc-receipt-upload-box" class="wccc-receipt-upload-section" style="display: none; margin-bottom: 16px;">
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                <?php esc_html_e( 'تصویر فیش واریزی:', 'wccc' ); ?> <span style="color: #e11d48;">*</span>
                            </label>
                            <div class="wccc-file-dropzone" id="wccc-file-dropzone" onclick="document.getElementById('wccc-receipt-file').click();">
                                <span>📷 <?php esc_html_e( 'انتخاب یا رهاسازی تصویر فیش واریزی (JPG, PNG, PDF)', 'wccc' ); ?></span>
                                <div id="wccc-file-name-preview"></div>
                            </div>
                            <input type="file" id="wccc-receipt-file" name="receipt_file" accept="image/png, image/jpeg, image/webp, application/pdf" style="display: none;">
                        </div>
                    <?php elseif ( ! $is_receipt_locked ) : ?>
                        <!-- باکس آپلود تصویر فیش: حالت ب یا فال‌بک انقضا -->
                        <div id="wccc-receipt-upload-box" class="wccc-receipt-upload-section" style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                <?php esc_html_e( 'تصویر فیش واریزی:', 'wccc' ); ?> <span style="color: #e11d48;">*</span>
                            </label>
                            <div class="wccc-file-dropzone" id="wccc-file-dropzone" <?php echo $is_order_completed ? 'style="opacity:0.6; cursor:not-allowed;"' : 'onclick="document.getElementById(\'wccc-receipt-file\').click();"'; ?>>
                                <span>📷 <?php esc_html_e( 'انتخاب یا رهاسازی تصویر فیش واریزی (JPG, PNG, PDF)', 'wccc' ); ?></span>
                                <div id="wccc-file-name-preview"></div>
                            </div>
                            <input type="file" id="wccc-receipt-file" name="receipt_file" accept="image/png, image/jpeg, image/webp, application/pdf" <?php echo $is_order_completed ? 'disabled="disabled"' : 'required'; ?> style="display: none;">
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label for="wccc_buyer_tracking_number" style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                <?php esc_html_e( 'شماره پیگیری / ارجاع فیش:', 'wccc' ); ?> <span style="font-size: 10px; color: #64748b;">(اختیاری)</span>
                            </label>
                            <input type="text" id="wccc_buyer_tracking_number" name="buyer_tracking_number" class="wccc-numeric-input" placeholder="مثال: 9482710" value="<?php echo esc_attr( $stored_tracking ); ?>" <?php echo $is_order_completed ? 'disabled="disabled" style="background:#f1f5f9; cursor:not-allowed;"' : ''; ?> style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; font-family: monospace; font-size: 14px; direction: ltr; text-align: right;">
                        </div>
                    <?php endif; ?>

                    <div id="wccc-upload-feedback">
                        <?php if ( $is_order_completed ) : ?>
                            <div class="wccc-feedback-inline" style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 12px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; text-align: right; direction: rtl; margin-bottom: 12px;">
                                ✓ <?php esc_html_e( 'پرداخت شما تایید شده و سفارش در وضعیت تکمیل شده قرار دارد.', 'wccc' ); ?>
                            </div>
                        <?php elseif ( $is_card_locked ) : ?>
                            <div class="wccc-feedback-inline" style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 12px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; text-align: right; direction: rtl; margin-bottom: 12px;">
                                ✓ <?php esc_html_e( 'اطلاعات کارت شما با موفقیت ثبت شد و در انتظار تطبیق با پیامک بانکی است.', 'wccc' ); ?>
                            </div>
                        <?php elseif ( $is_receipt_locked ) : ?>
                            <div class="wccc-feedback-inline" style="background: #ecfdf5; border: 1px solid #10b981; color: #065f46; padding: 12px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; text-align: right; direction: rtl; margin-bottom: 12px;">
                                ✓ <?php echo esc_html( ! empty( $this->pending_review_message ) ? $this->pending_review_message : __( 'فیش شما ثبت شده است و در انتظار بررسی مدیریت است.', 'wccc' ) ); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- دکمه ثبت سفارش -->
                    <button type="submit" id="wccc-submit-receipt-btn" class="wccc-submit-receipt-btn" <?php echo ( $is_card_locked || $is_receipt_locked || $is_order_completed ) ? 'disabled="disabled"' : ''; ?> style="margin-top: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; <?php echo ( $is_card_locked || $is_receipt_locked || $is_order_completed ) ? 'opacity: 0.7; cursor: not-allowed;' : ''; ?>">
                        <span id="wccc-btn-spinner" style="display: none; width: 16px; height: 16px; border: 2px solid #ffffff; border-top-color: transparent; border-radius: 50%; animation: wccc-spin 0.8s linear infinite;"></span>
                        <span id="wccc-btn-text">
                            <?php
                            if ( $is_order_completed ) {
                                esc_html_e( 'پرداخت تایید و سفارش تکمیل شده است ✓', 'wccc' );
                            } elseif ( $is_card_locked ) {
                                esc_html_e( 'اطلاعات کارت ثبت شده است ✓', 'wccc' );
                            } elseif ( $is_receipt_locked ) {
                                esc_html_e( 'فیش ثبت شده است ✓', 'wccc' );
                            } elseif ( $is_expired_fallback ) {
                                esc_html_e( 'ارسال فیش و تکمیل سفارش', 'wccc' );
                            } elseif ( $is_instant_mode ) {
                                esc_html_e( 'ثبت اطلاعات پرداخت و پیگیری سفارش', 'wccc' );
                            } else {
                                esc_html_e( 'ارسال فیش و تکمیل سفارش', 'wccc' );
                            }
                            ?>
                        </span>
                    </button>
                </form>
            </div>
        </div>
        <?php
    }
}
