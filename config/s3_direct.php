<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Direct S3 storage remains disabled until live credentials are added to the
 * server-only s3_direct.local.php file and the CDN routes are deployed.
 * Never commit credentials to this repository.
 */
$config['s3_direct_uploads'] = false;
$config['s3_direct_bucket'] = '';
$config['s3_direct_region'] = 'ca-central-1';
$config['s3_direct_key'] = '';
$config['s3_direct_secret'] = '';
$config['s3_direct_paths'] = array();

$local = APPPATH . 'config/s3_direct.local.php';
if (is_file($local)) {
    $override = include $local;
    if (is_array($override)) {
        foreach ($override as $key => $value) {
            $config['s3_direct_' . $key] = $value;
        }
    }
}
