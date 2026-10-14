<?php
declare(strict_types=1);

namespace WCCC\Engine;

use WC_Order;

class PennyEngine {
    public const DEFAULT_MIN_PENNY = 100;
    public const DEFAULT_MAX_PENNY = 999;
    public const MAX_COLLISION_RETRIES = 50;

    public static function allocate_amount( WC_Order $order, array $settings ): array {
        $base_amount     = (float) $order->get_total();
        $is_enabled      = isset( $settings['penny_enabled'] ) && 'yes' === $settings['penny_enabled'];
        $timeout_minutes = ! empty( $settings['timeout_minutes'] ) ? max( 5, (int) $settings['timeout_minutes'] ) : 15;
        $now             = time();
        $expires_at      = $now + ( $timeout_minutes * 60 );

        if ( ! $is_enabled ) {
            return array(
                'base_amount'      => $base_amount,
                'penny_offset'     => 0.0,
                'total_amount'     => $base_amount,
                'expires_at'       => $expires_at,
                'expires_in_secs'  => $timeout_minutes * 60,
                'is_penny_applied' => false,
            );
        }

        $raw_min   = isset( $settings['penny_min'] ) ? (int) $settings['penny_min'] : self::DEFAULT_MIN_PENNY;
        $raw_max   = isset( $settings['penny_max'] ) ? (int) $settings['penny_max'] : self::DEFAULT_MAX_PENNY;
        $min_penny = max( 100, $raw_min );
        $max_penny = max( $min_penny + 10, $raw_max );

        $reserved_totals = self::get_active_reserved_amounts( $order->get_id() );

        $chosen_offset = 0;
        $allocated     = false;

        for ( $attempt = 0; $attempt < self::MAX_COLLISION_RETRIES; $attempt++ ) {
            $candidate_offset = random_int( $min_penny, $max_penny );
            $candidate_total  = $base_amount + (float) $candidate_offset;

            if ( ! in_array( $candidate_total, $reserved_totals, true ) ) {
                $chosen_offset = (float) $candidate_offset;
                $allocated     = true;
                break;
            }
        }

        if ( ! $allocated ) {
            for ( $offset = $min_penny; $offset <= $max_penny; $offset++ ) {
                $candidate_total = $base_amount + (float) $offset;
                if ( ! in_array( $candidate_total, $reserved_totals, true ) ) {
                    $chosen_offset = (float) $offset;
                    $allocated     = true;
                    break;
                }
            }
        }

        if ( ! $allocated ) {
            $chosen_offset = (float) $min_penny;
        }

        return array(
            'base_amount'      => $base_amount,
            'penny_offset'     => $chosen_offset,
            'total_amount'     => $base_amount + $chosen_offset,
            'expires_at'       => $expires_at,
            'expires_in_secs'  => $timeout_minutes * 60,
            'is_penny_applied' => true,
        );
    }

    public static function get_active_reserved_amounts( int $current_order_id = 0 ): array {
        $now = time();
        $args = array(
            'status'  => array( 'wc-wccc-awaiting', 'wccc-awaiting' ),
            'limit'   => 100,
            'return'  => 'ids',
            'exclude' => $current_order_id > 0 ? array( $current_order_id ) : array(),
        );

        $order_ids       = function_exists( 'wc_get_orders' ) ? wc_get_orders( $args ) : array();
        $reserved_totals = array();

        foreach ( $order_ids as $order_id ) {
            $order = wc_get_order( $order_id );
            if ( ! $order ) continue;

            $expires_at = (int) $order->get_meta( '_wccc_expires_at' );
            if ( $expires_at > $now ) {
                $allocated_total = (float) $order->get_meta( '_wccc_allocated_amount' );
                if ( $allocated_total > 0 ) {
                    $reserved_totals[] = $allocated_total;
                }
            }
        }

        return $reserved_totals;
    }
}
