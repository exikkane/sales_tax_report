<?php

use Tygh\Addons\SalesTaxReport\Services\SalesTaxReportExporter;
use Tygh\Enum\FileFormats;
use Tygh\Registry;
use Tygh\Enum\OrderStatuses;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* @var $mode */
    if ($mode == 'generate_report') {
        $company_id = Registry::get('runtime.company_id');

        if (empty($company_id)) {
            return fn_set_notification("W", __("warning"), __("reports_gen_error"));
        }
        $params = [
            'period'          => $_REQUEST['period'],
            'time_from'       => $_REQUEST['time_from'],
            'time_to'         => $_REQUEST['time_to'],
            'company_id'      => $company_id,
            'format'          => $_REQUEST['format'] ?? FileFormats::CSV_FORMAT,
            'get_commissions' => true,
            'status'          => OrderStatuses::COMPLETE
        ];

        $exporter = new SalesTaxReportExporter($params);
        $exporter->generate();
    }
}
