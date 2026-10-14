<?php
/**
 * WCCC Database Schema & Self-Healing Engine
 *
 * @package   WCCC\Database
 * @author    تیم فنی WCCC
 * @license   GPL-2.0-or-later
 */

declare(strict_types=1);

namespace WCCC\Database;

defined( 'ABSPATH' ) || exit;

class Schema {

    public const DB_VERSION = '1.0.0';
    public const DB_VERSION_OPTION = 'wccc_db_version';

    public const TABLE_CARDS            = 'wccc_cards';
    public const TABLE_RESERVATIONS     = 'wccc_reservations';
    public const TABLE_CLIENTS          = 'wccc_clients';
    public const TABLE_MESSAGES         = 'wccc_messages';
    public const TABLE_RECEIPTS         = 'wccc_receipts';
    public const TABLE_MESSAGE_RECEIPTS = 'wccc_receipts'; // سازگاری با کدهای پیشین
    public const TABLE_EVENTS           = 'wccc_events';

    /**
     * دریافت نام کامل جدول با پیشوند وردپرس
     *
     * @param string $table نام شناسه جدول
     * @return string
     */
    public static function get_table_name( string $table ): string {
        global $wpdb;
        return $wpdb->prefix . $table;
    }

    /**
     * اجرای مایگریشن و ساخت/به‌روزرسانی جداول با dbDelta
     */
    public static function migrate(): void {
        global $wpdb;

        if ( ! function_exists( 'dbDelta' ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $charset_collate = $wpdb->get_charset_collate();
        $queries         = self::get_schema_queries( $charset_collate );

        foreach ( $queries as $sql ) {
            dbDelta( $sql );
        }

        update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
    }

    /**
     * مکانیزم خودترمیمی دیتابیس (Self-Healing DB Mechanism)
     * بررسی وجود ۶ جدول اختصاصی و بازسازی فوری و بی‌صدا در صورت کسری یا حذف دستی
     */
    public static function check_and_repair(): void {
        global $wpdb;

        $required_tables = array(
            self::get_table_name( self::TABLE_CARDS ),
            self::get_table_name( self::TABLE_RESERVATIONS ),
            self::get_table_name( self::TABLE_CLIENTS ),
            self::get_table_name( self::TABLE_MESSAGES ),
            self::get_table_name( self::TABLE_RECEIPTS ),
            self::get_table_name( self::TABLE_EVENTS ),
        );

        $like_pattern    = $wpdb->esc_like( $wpdb->prefix . 'wccc_' ) . '%';
        $existing_tables = $wpdb->get_col( $wpdb->prepare( "SHOW TABLES LIKE %s", $like_pattern ) );

        $needs_repair = false;
        foreach ( $required_tables as $table ) {
            if ( ! in_array( $table, $existing_tables, true ) ) {
                $needs_repair = true;
                break;
            }
        }

        if ( $needs_repair ) {
            self::migrate();
        }

        // بررسی و اضافه کردن ستون‌های جدید به جدول کارت‌ها در صورت عدم وجود (Safe Schema Repair)
        $cards_table = self::get_table_name( self::TABLE_CARDS );
        if ( in_array( $cards_table, $existing_tables, true ) || ! $needs_repair ) {
            $has_daily_tx_limit = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM `{$cards_table}` LIKE %s", 'daily_tx_limit' ) );
            if ( empty( $has_daily_tx_limit ) ) {
                $wpdb->query( "ALTER TABLE `{$cards_table}` ADD COLUMN `daily_tx_limit` INT(11) NOT NULL DEFAULT 50 AFTER `daily_limit`" );
            }
            $has_current_daily_tx_count = $wpdb->get_results( $wpdb->prepare( "SHOW COLUMNS FROM `{$cards_table}` LIKE %s", 'current_daily_tx_count' ) );
            if ( empty( $has_current_daily_tx_count ) ) {
                $wpdb->query( "ALTER TABLE `{$cards_table}` ADD COLUMN `current_daily_tx_count` INT(11) NOT NULL DEFAULT 0 AFTER `daily_tx_limit`" );
            }
        }
    }

    /**
     * کوئری‌های استاندارد dbDelta برای ساخت جداول
     * با رعایت دقیق:
     * - دو فاصله الزامی بعد از PRIMARY KEY  (id)
     * - قرار گرفتن هر ستون در یک سطر مجزا
     * - حذف کامای پایانی قبل از بستن پرانتز جدول
     *
     * @param string $charset_collate
     * @return array<string, string>
     */
    public static function get_schema_queries( string $charset_collate ): array {
        $cards_table        = self::get_table_name( self::TABLE_CARDS );
        $reservations_table = self::get_table_name( self::TABLE_RESERVATIONS );
        $clients_table      = self::get_table_name( self::TABLE_CLIENTS );
        $messages_table     = self::get_table_name( self::TABLE_MESSAGES );
        $receipts_table     = self::get_table_name( self::TABLE_RECEIPTS );
        $events_table       = self::get_table_name( self::TABLE_EVENTS );

        return array(
            self::TABLE_CARDS => "CREATE TABLE {$cards_table} (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
card_number varchar(32) NOT NULL,
bank_name varchar(100) NOT NULL,
card_holder varchar(150) NOT NULL,
iban varchar(34) DEFAULT NULL,
is_active tinyint(1) NOT NULL DEFAULT 1,
daily_limit decimal(14,2) NOT NULL DEFAULT 0.00,
daily_tx_limit int(11) NOT NULL DEFAULT 50,
current_daily_tx_count int(11) NOT NULL DEFAULT 0,
current_daily_total decimal(14,2) NOT NULL DEFAULT 0.00,
last_reset_date date DEFAULT NULL,
created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY  (id),
KEY idx_card_number (card_number),
KEY idx_is_active (is_active)
) {$charset_collate};",

            self::TABLE_RESERVATIONS => "CREATE TABLE {$reservations_table} (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
order_id bigint(20) unsigned NOT NULL,
card_id bigint(20) unsigned DEFAULT NULL,
original_amount decimal(14,2) NOT NULL,
final_amount decimal(14,2) NOT NULL,
offset_amount int(11) NOT NULL DEFAULT 0,
status varchar(30) NOT NULL DEFAULT 'pending',
expires_at datetime NOT NULL,
created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY  (id),
KEY idx_order_id (order_id),
KEY idx_card_id (card_id),
KEY idx_final_amount (final_amount),
KEY idx_status (status),
KEY idx_expires_at (expires_at)
) {$charset_collate};",

            self::TABLE_CLIENTS => "CREATE TABLE {$clients_table} (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
client_id varchar(64) NOT NULL,
client_secret varchar(255) NOT NULL,
device_name varchar(100) NOT NULL,
device_model varchar(100) DEFAULT NULL,
battery_level tinyint(3) unsigned DEFAULT NULL,
is_charging tinyint(1) DEFAULT 0,
status varchar(30) DEFAULT 'pending',
last_seen_at datetime DEFAULT NULL,
created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY  (id),
UNIQUE KEY uq_client_id (client_id),
KEY idx_status (status)
) {$charset_collate};",

            self::TABLE_MESSAGES => "CREATE TABLE {$messages_table} (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
client_id varchar(64) NOT NULL,
bank_key varchar(50) NOT NULL,
sender_number varchar(30) NOT NULL,
raw_message text NOT NULL,
parsed_amount decimal(14,2) NOT NULL,
source_card_last4 varchar(4) DEFAULT NULL,
dest_card_last4 varchar(4) DEFAULT NULL,
tracking_number varchar(64) DEFAULT NULL,
remaining_balance decimal(14,2) DEFAULT NULL,
sms_timestamp datetime NOT NULL,
sync_status varchar(30) DEFAULT 'synced',
matched_order_id bigint(20) unsigned DEFAULT NULL,
created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY  (id),
KEY idx_client_id (client_id),
KEY idx_bank_key (bank_key),
KEY idx_parsed_amount (parsed_amount),
KEY idx_sms_timestamp (sms_timestamp),
KEY idx_sync_status (sync_status),
KEY idx_matched_order_id (matched_order_id)
) {$charset_collate};",

            self::TABLE_RECEIPTS => "CREATE TABLE {$receipts_table} (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
order_id bigint(20) unsigned NOT NULL,
receipt_url text NOT NULL,
receipt_hash varchar(64) NOT NULL,
uploaded_by_user_id bigint(20) unsigned DEFAULT NULL,
buyer_card_last4 varchar(4) DEFAULT NULL,
buyer_tracking_number varchar(64) DEFAULT NULL,
verification_status varchar(30) DEFAULT 'pending',
verified_by_client_id varchar(64) DEFAULT NULL,
rejection_reason text DEFAULT NULL,
verified_at datetime DEFAULT NULL,
created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY  (id),
KEY idx_order_id (order_id),
KEY idx_receipt_hash (receipt_hash),
KEY idx_verification_status (verification_status)
) {$charset_collate};",

            self::TABLE_EVENTS => "CREATE TABLE {$events_table} (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
client_id varchar(64) DEFAULT NULL,
order_id bigint(20) unsigned DEFAULT NULL,
event_type varchar(50) NOT NULL,
severity varchar(30) DEFAULT 'info',
payload longtext DEFAULT NULL,
ip_address varchar(45) DEFAULT NULL,
user_agent varchar(255) DEFAULT NULL,
created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY  (id),
KEY idx_client_id (client_id),
KEY idx_order_id (order_id),
KEY idx_event_type (event_type),
KEY idx_created_at (created_at)
) {$charset_collate};",
        );
    }
}
