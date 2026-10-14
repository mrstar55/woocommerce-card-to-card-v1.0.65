<?php
/**
 * کلاس اورکستراتور اصلی و سینگلتون افزونه
 *
 * @package   WCCC
 * @author    تیم فنی WCCC
 * @license   GPL-2.0-or-later
 */

declare(strict_types=1);

namespace WCCC;

use WCCC\Database\Schema;
use WCCC\Orders\CustomStatuses;
use WCCC\Security\HmacValidator;
use WCCC\Gateway\ReceiptUploader;
use WCCC\Gateway\CheckoutFeeManager;
use WCCC\Admin\OrderMetaBox;
use WCCC\Admin\AdminBar;
use WCCC\Admin\AdminMenu;
use WCCC\Admin\AjaxHandler;
use WCCC\Storage\ReceiptCleanup;
use WCCC\Orders\StatusTransitionManager;

final class Plugin {

    private static ?Plugin $instance = null;
    private Schema $schema;
    private HmacValidator $hmac_validator;
    private CustomStatuses $custom_statuses;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_components();
        $this->register_hooks();
    }

    private function __clone() {}

    public function __wakeup(): void {
        throw new \Exception( 'امکان unserialize کردن کلاس سینگلتون وجود ندارد.' );
    }

    private function init_components(): void {
        $this->schema          = new Schema();
        $this->hmac_validator  = new HmacValidator();
        $this->custom_statuses = new CustomStatuses();

        // راه‌اندازی هندلر آپلود امن فیش واریزی
        ReceiptUploader::init();

        // راه‌اندازی مدیر کارمزد هوشمند پنی در تسویه حساب
        CheckoutFeeManager::init();

        // راه‌اندازی ماژول پاکسازی و چرخه عمر فیش‌های واریزی
        ReceiptCleanup::init();

        // راه‌اندازی موتور نگاشت و انتقال وضعیت سفارش‌ها (Phase 4)
        StatusTransitionManager::init();

        // راه‌اندازی جعبه متای سفارشات در پیشخوان ادمین
        if ( is_admin() ) {
            OrderMetaBox::init();
        }

        // راه‌اندازی دسترسی سریع در نوار مدیریت وردپرس (Admin Bar)
        AdminBar::get_instance();

        // راه‌اندازی پیشخوان مدیریت و پردازشگر ایجکس WCCC
        if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            AdminMenu::get_instance();
            AjaxHandler::get_instance();
        }
    }

    private function register_hooks(): void {
        $this->custom_statuses->init();
        add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );

        // مکانیزم خودترمیمی دیتابیس در محیط ادمین (Self-Healing DB)
        add_action( 'admin_init', array( Schema::class, 'check_and_repair' ) );
    }

    public function register_gateway( array $gateways ): array {
        $gateways[] = \WCCC\Gateway\WCCC_Gateway::class;
        return $gateways;
    }

    public function get_schema(): Schema {
        return $this->schema;
    }

    public function get_hmac_validator(): HmacValidator {
        return $this->hmac_validator;
    }

    public function get_custom_statuses(): CustomStatuses {
        return $this->custom_statuses;
    }
}
