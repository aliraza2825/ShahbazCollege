<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Uploads new CI-managed files straight to S3.
 *
 * The legacy application stores only a filename in many tables.  To preserve
 * that contract while keeping files off the web server, successful uploads are
 * stored as `s3/<filename>`.  Apache routes only that prefix to the current
 * asset CDN; all historic paths continue using their existing route.
 */
class MY_Upload extends CI_Upload
{
    // CI_Upload::initialize() iterates subclass properties from the parent
    // scope, so these must not be private.
    public $direct_s3_object_url = null;
    public $direct_s3_file_name = null;

    public function do_upload($field = 'userfile')
    {
        if (!parent::do_upload($field)) {
            return false;
        }

        if (!$this->_direct_s3_enabled()) {
            return true;
        }

        $local_path = $this->upload_path . $this->file_name;
        try {
            $this->direct_s3_object_url = $this->_put_to_s3($local_path);
            if (!$this->direct_s3_object_url) {
                throw new RuntimeException('S3 did not return an object URL.');
            }
            if (!@unlink($local_path)) {
                throw new RuntimeException('S3 upload succeeded but local cleanup failed.');
            }
            $this->direct_s3_file_name = 's3/' . $this->file_name;
            return true;
        } catch (Exception $e) {
            // Never retain or return a local filename after a failed direct-S3
            // upload. The caller receives an error and can safely retry.
            @unlink($local_path);
            $this->set_error('s3_upload_failed: ' . $e->getMessage());
            return false;
        }
    }

    public function data($index = null)
    {
        $data = parent::data();
        if ($this->direct_s3_file_name !== null) {
            $data['file_name'] = $this->direct_s3_file_name;
            $data['raw_name'] = 's3/' . $data['raw_name'];
            $data['file_path'] = rtrim($this->_cdn_base(), '/') . '/';
            $data['full_path'] = $this->_cdn_url();
        }
        return $index === null ? $data : (isset($data[$index]) ? $data[$index] : null);
    }

    private function _direct_s3_enabled()
    {
        $ci =& get_instance();
        if (!(bool) $ci->config->item('s3_direct_uploads')) {
            return false;
        }
        $relative_dir = trim(str_replace('\\', '/', str_replace(FCPATH, '', $this->upload_path)), '/');
        if ($relative_dir === '' || strpos($relative_dir, '..') !== false) {
            return false;
        }

        // Import-only CSV handlers read PHP's request temp file directly.
        // Every CI-managed upload is therefore a persistent asset and must
        // leave the web server after reaching S3.
        return true;
    }

    private function _settings()
    {
        $ci =& get_instance();
        $settings = array(
            'bucket' => $ci->config->item('s3_direct_bucket'),
            'region' => $ci->config->item('s3_direct_region'),
            'key' => $ci->config->item('s3_direct_key'),
            'secret' => $ci->config->item('s3_direct_secret'),
        );
        foreach ($settings as $name => $value) {
            if (!$value) {
                throw new RuntimeException('Missing S3 setting: ' . $name);
            }
        }
        return $settings;
    }

    private function _put_to_s3($local_path)
    {
        if (!is_file($local_path) || !is_readable($local_path)) {
            throw new RuntimeException('Temporary upload is unavailable.');
        }
        $settings = $this->_settings();
        $autoload = FCPATH . 'amazon/aws-autoloader.php';
        if (!is_file($autoload)) {
            throw new RuntimeException('AWS SDK is not installed.');
        }
        require_once $autoload;

        $relative_dir = trim(str_replace('\\', '/', str_replace(FCPATH, '', $this->upload_path)), '/');
        if ($relative_dir === '' || strpos($relative_dir, '..') !== false) {
            throw new RuntimeException('Invalid upload directory.');
        }
        $key = 'lahore-campus/' . $relative_dir . '/s3/' . basename($this->file_name);
        $s3 = \Aws\S3\S3Client::factory(array(
            'credentials' => array('key' => $settings['key'], 'secret' => $settings['secret']),
            'version' => 'latest',
            'region' => $settings['region'],
        ));
        // Bucket-owner-enforced S3 buckets reject object ACLs. Visibility is
        // governed by the bucket policy and the canonical application route.
        $result = $s3->putObject(array(
            'Bucket' => $settings['bucket'],
            'Key' => $key,
            'SourceFile' => $local_path,
            'ContentType' => $this->file_type ?: 'application/octet-stream',
        ));
        return isset($result['ObjectURL']) ? $result['ObjectURL'] : null;
    }

    private function _cdn_base()
    {
        $ci =& get_instance();
        return (string) $ci->config->item('cloudfront_address');
    }

    private function _cdn_url()
    {
        $relative_dir = trim(str_replace('\\', '/', str_replace(FCPATH, '', $this->upload_path)), '/');
        return rtrim($this->_cdn_base(), '/') . '/lahore-campus/' . $relative_dir . '/' . $this->direct_s3_file_name;
    }
}
