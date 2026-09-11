<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Direct S3 storage for legacy endpoints that do not use CI_Upload. */
class S3_direct_storage
{
    private $ci;
    private $last_error = '';

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function put_uploaded_file($field, $directory, $filename)
    {
        if (empty($_FILES[$field]['tmp_name']) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
            return false;
        }
        $mime = !empty($_FILES[$field]['type']) ? $_FILES[$field]['type'] : 'application/octet-stream';
        return $this->put_file($_FILES[$field]['tmp_name'], $directory, $filename, $mime);
    }

    public function put_contents($contents, $directory, $filename, $mime = 'application/octet-stream')
    {
        $tmp = tempnam(sys_get_temp_dir(), 's3upload_');
        if ($tmp === false || file_put_contents($tmp, $contents) === false) {
            if ($tmp) @unlink($tmp);
            return false;
        }
        try {
            return $this->put_file($tmp, $directory, $filename, $mime);
        } finally {
            @unlink($tmp);
        }
    }

    public function put_file($source, $directory, $filename, $mime = 'application/octet-stream')
    {
        $this->last_error = '';
        if (!is_file($source) || !is_readable($source)) {
            $this->last_error = 'Temporary upload file is unavailable';
            return false;
        }
        $directory = trim(str_replace('\\', '/', $directory), '/');
        $filename = basename($filename);
        if ($directory === '' || $filename === '' || strpos($directory, '..') !== false) {
            $this->last_error = 'Invalid upload path';
            return false;
        }
        $settings = $this->_settings();
        if (!$settings) {
            $this->last_error = 'S3 upload is not configured on this server';
            return false;
        }
        $autoload = FCPATH . 'amazon/aws-autoloader.php';
        if (!is_file($autoload)) {
            $this->last_error = 'S3 upload library is unavailable';
            return false;
        }
        require_once $autoload;
        try {
            $client = \Aws\S3\S3Client::factory(array(
                'credentials' => array('key' => $settings['key'], 'secret' => $settings['secret']),
                'version' => 'latest',
                'region' => $settings['region'],
            ));
            // This bucket uses bucket-policy/Object Ownership access. Supplying a
            // per-object public ACL makes uploads fail on Bucket-owner-enforced
            // S3 buckets (AccessControlListNotSupported).
            $client->putObject(array(
                'Bucket' => $settings['bucket'],
                'Key' => 'lahore-campus/' . $directory . '/s3/' . $filename,
                'SourceFile' => $source,
                'ContentType' => $mime ?: 'application/octet-stream',
            ));
            return 's3/' . $filename;
        } catch (Exception $e) {
            $code = method_exists($e, 'getAwsErrorCode') ? $e->getAwsErrorCode() : '';
            $this->last_error = $code ? ('S3 error: ' . $code) : 'S3 request failed';
            log_message('error', 'Direct S3 upload failed: ' . $e->getMessage());
            return false;
        }
    }

    /** Safe, non-secret reason suitable for an API error response. */
    public function last_error()
    {
        return $this->last_error !== '' ? $this->last_error : 'Upload failed';
    }

    private function _settings()
    {
        if (!$this->ci->config->item('s3_direct_uploads')) return false;
        $settings = array(
            'bucket' => $this->ci->config->item('s3_direct_bucket'),
            'region' => $this->ci->config->item('s3_direct_region'),
            'key' => $this->ci->config->item('s3_direct_key'),
            'secret' => $this->ci->config->item('s3_direct_secret'),
        );
        foreach ($settings as $value) if (!$value) return false;
        return $settings;
    }
}
