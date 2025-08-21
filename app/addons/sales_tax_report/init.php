<?php

defined('BOOTSTRAP') or die('Access denied');

require_once __DIR__ . '/lib/vendor/autoload.php';

fn_register_hooks('get_orders', 'change_order_status_post');
