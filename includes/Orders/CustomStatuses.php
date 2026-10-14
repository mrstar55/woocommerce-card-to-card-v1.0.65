<?php
declare(strict_types=1);

namespace WCCC\Orders;

class CustomStatuses {
    public const STATUS_AWAITING    = 'wccc-awaiting';
    public const STATUS_REVIEW      = 'wccc-review';
    public const WC_STATUS_AWAITING = 'wc-wccc-awaiting';
    public const WC_STATUS_REVIEW   = 'wc-wccc-review';

    public function init(): void {
        add_action( 'init', array( self::class, 'register_statuses' ) );
        add_filter( 'wc_order_statuses', array( $this, 'add_wc_order_statuses' ) );
        add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', array( $this, 'allow_payment_complete_transition' ), 10, 2 );
    }

    public static function register_statuses(): void {
        register_post_status( self::WC_STATUS_AWAITING, array(
            'label'                     => _x( 'در انتظار کارت به کارت', 'Order status', 'wccc' ),
            'public'                    => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
        ) );

        register_post_status( self::WC_STATUS_REVIEW, array(
            'label'                     => _x( 'فیش در حال بررسی', 'Order status', 'wccc' ),
            'public'                    => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
        ) );
    }

    public function add_wc_order_statuses( array $order_statuses ): array {
        $order_statuses[ self::WC_STATUS_AWAITING ] = _x( 'در انتظار کارت به کارت', 'Order status', 'wccc' );
        $order_statuses[ self::WC_STATUS_REVIEW ]   = _x( 'فیش در حال بررسی', 'Order status', 'wccc' );
        return $order_statuses;
    }

    public function allow_payment_complete_transition( array $statuses, $order = null ): array {
        $statuses[] = self::STATUS_AWAITING;
        $statuses[] = self::STATUS_REVIEW;
        $statuses[] = self::WC_STATUS_AWAITING;
        $statuses[] = self::WC_STATUS_REVIEW;
        return array_unique( $statuses );
    }
}
