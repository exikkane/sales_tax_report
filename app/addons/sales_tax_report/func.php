<?php

use Tygh\Enum\OrderStatuses;

/**
 * Hook 'get_orders'
 *
 * Adds a marketplace profit to the orders array
 *
 * @param $params
 * @param $fields
 * @param $sortings
 * @param $condition
 * @param $join
 * @return void
 *
 * @see fn_get_orders()
 */
function fn_sales_tax_report_get_orders($params, &$fields, $sortings, $condition, &$join): void
{
    if (!isset($params['get_commissions'])) {
        return;
    }

    $fields[] = '?:vendor_payouts.marketplace_profit as marketplace_profit';
    if (!strpos($join, 'vendor_payouts')) {
        $join .= " LEFT JOIN ?:vendor_payouts ON ?:vendor_payouts.order_id = ?:orders.order_id";
    }
}

/**
 * Hook 'change_order_status_post'
 *
 * Adds a payment date timestamp to the database once an order gets Paid status
 *
 * @param $order_id
 * @param $status_to
 * @return void
 *
 * @see fn_change_order_status()
 */
function fn_sales_tax_report_change_order_status_post($order_id, $status_to): void
{
    if ($status_to !== OrderStatuses::PAID) {
        return;
    }

    db_query("UPDATE ?:orders SET payment_timestamp = ?i WHERE order_id = ?i", time(), $order_id);
}