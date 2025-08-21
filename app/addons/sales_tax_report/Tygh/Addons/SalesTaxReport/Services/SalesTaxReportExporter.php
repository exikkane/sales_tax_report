<?php

namespace Tygh\Addons\SalesTaxReport\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tygh\Enum\FileFormats;

class SalesTaxReportExporter
{
    protected array $params;
    protected string $format;
    protected array $meta;
    protected int $company_id;
    protected array $taxes = [];

    public function __construct(array $params)
    {
        $this->params     = $params;
        $formats          = FileFormats::getAll();
        $this->format     = $formats[$params['format']];
        $this->company_id = $params['company_id'];
        $this->taxes      = fn_get_taxes();
    }

    /**
     * Entry point
     */
    public function generate(): void
    {
        // prepare data first
        $orders_data = $this->prepareData();

        if ($this->format === FileFormats::CSV_FORMAT) {
            $this->generateCsv($orders_data);
        } elseif ($this->format === FileFormats::XLSX_FORMAT) {
            $this->generateXlsx($orders_data);
        }
    }

    /**
     * Prepares orders data
     */
    public function prepareData(): array
    {
        $this->getFileMeta(); // build headers first
        list($orders,) = fn_get_orders($this->params);

        if (empty($orders)) {
            return [];
        }

        // Collect tax IDs in fixed order
        $tax_ids = [];
        if (!empty($this->taxes)) {
            foreach ($this->taxes as $tax) {
                $tax_ids[$tax['tax_id']] = $tax['tax'];
            }
        }

        $prepared_data = [];

        foreach ($orders as $order) {
            $order_data = array_merge($order, fn_get_order_info($order['order_id']));
            $user_name  = $order_data['firstname'] . ' ' . $order_data['lastname'];

            $gross_total   = $order['total'];
            $platform      = $order['marketplace_profit'];

            // default taxes = 0
            $tax_values = array_fill_keys(array_keys($tax_ids), 0);

            if (!empty($order_data['taxes'])) {
                foreach ($order_data['taxes'] as $tax_id => $tax) {
                    if (isset($tax_values[$tax_id])) {
                        $tax_values[$tax_id] = $tax['tax_subtotal'] ?? 0;
                    }
                }
            }

            $sum_taxes    = array_sum($tax_values);
            $net_total    = $gross_total - $platform - $sum_taxes;
            $payment_date = !empty($order_data['payment_timestamp'])
                ? date('d.m.Y', $order_data['payment_timestamp'])
                : '';

            // build row matching header order
            $row = [
                $order['order_id'],                 // Invoice Number
                date('d.m.Y', $order_data['timestamp']), // Order Date
                $payment_date,                      // Payment Date
                $user_name,                         // Customer Name
                $gross_total,                       // Gross Total (€)
                $platform,                          // Platform (€)
                $net_total,                         // Net Total (€) after taxes
            ];

            // append taxes in order
            foreach ($tax_ids as $tax_id => $tax_name) {
                $row[] = $tax_values[$tax_id];
            }

            $prepared_data[] = $row;
        }

        return $prepared_data;
    }



    /**
     * Generate Excel (.xlsx)
     */
    protected function generateXlsx($orders_data): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // headers
        $sheet->fromArray($this->meta['headers']);
        // data
        $sheet->fromArray($orders_data, NULL, 'A2');

        // output
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $this->meta['filename'] . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Generate CSV
     */
    protected function generateCsv($orders_data): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $this->meta['filename'] . '.csv"');

        $out = fopen('php://output', 'w');

        fputcsv($out, $this->meta['headers'], ';');
        foreach ($orders_data as $row) {
            fputcsv($out, $row, ';');
        }

        fclose($out);
        exit;
    }

    /*
     * Gather file meta
     */
    protected function getFileMeta()
    {
        $this->meta = [
            'filename' => 'sales_tax_report_' . date('Y-m-d_His'),
            'headers'  => [
                __('sales_report_invoice_number'),
                __('sales_report_order_date'),
                __('sales_report_payment_date'),
                __('sales_report_customer_name'),
                __('sales_report_gross_total'),
                __('sales_report_platform'),
                __('sales_report_net_total'),
            ],
        ];

        if (!empty($this->taxes)) {
            foreach ($this->taxes as $tax) {
                $this->meta['headers'][] = $tax['tax'];
            }
        }
    }
}
