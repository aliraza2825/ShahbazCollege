<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Credentials live only on the server and are intentionally excluded from Git.
$config['hbl_test_api_token'] = '';
$config['hbl_production_api_token'] = '';
$local_hbl_config = __DIR__ . '/hbl.local.php';
if (is_file($local_hbl_config)) require $local_hbl_config;
$config['hbl_return_json_pretty'] = true;
