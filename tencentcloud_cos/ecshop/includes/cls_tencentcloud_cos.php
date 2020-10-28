<?php

if (!defined('IN_ECS')) {
    die('Hacking attempt');
}
require_once 'tencent_cloud/cos/autoload.php';
require_once 'cls_tencentcloud_center.php';

class tencent_cos
{
    private $secret_id;
    private $secret_key;
    private $region;
    private $bucket;


    /**
     * tencent_cos constructor.
     * @param $cos_options
     */
    public function __construct($cos_options)
    {
        $this->secret_id = isset($cos_options['secret_id']) ? $cos_options['secret_id'] : '';
        $this->secret_key = isset($cos_options['secret_key']) ? $cos_options['secret_key'] : '';
        $this->region = isset($cos_options['region']) ? $cos_options['region'] : '';
        $this->bucket = isset($cos_options['bucket']) ? $cos_options['bucket'] : '';
    }

    /**
     * 返回cos对象
     * @param array $options 用户自定义插件参数
     * @return \Qcloud\Cos\Client
     */
    private function getCosClient($secret_id, $secret_key, $region)
    {
        if (empty($region) || empty($secret_id) || empty($secret_key)) {
            return false;
        }

        return new Qcloud\Cos\Client(
            array(
                'region' => $region,
                'schema' => ($this->isHttps() === true) ? "https" : "http",
                'credentials' => array(
                    'secretId' => $secret_id,
                    'secretKey' => $secret_key
                )
            )
        );
    }

    /**
     * 判断是否为https请求
     * @return bool
     */
    public function isHttps()
    {
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        } elseif ($_SERVER['SERVER_PORT'] == 443) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 上传远程附件
     * @param $file_dir
     * @param $cos_file_path
     */
    public function uploadFilesToCos($file_dir, $cos_file_path)
    {
        if (is_array($cos_file_path) && count($cos_file_path) > 0) {
            foreach ($cos_file_path as $cos_file) {
                $this->uploadFileToCos($file_dir . $cos_file, $cos_file);
            }
        }
    }

    /**
     * 上传附件
     * @param $source
     * @param $target
     * @return bool
     */
    public function uploadFileToCos($source, $target)
    {
        try {
            $cosClient = $this->getCosClient($this->secret_id, $this->secret_key, $this->region);
            $fh = fopen($source, 'rb');
            if ($fh) {
                $result = $cosClient->Upload(
                    $bucket = $this->bucket,
                    $key = $target,
                    $body = $fh
                );
                fclose($fh);
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 删除远程附件
     * @param $key
     * @return bool
     */
    public function deleteRemoteAttachment($key)
    {
        $deleteObjects = array();
        if (is_array($key)) {
            $deleteObjects = $key;
        } elseif (is_string($key)) {
            $deleteObjects[] = array(
                'Key' => $key
            );
        } else {
            $deleteObjects = array();
        }
        if (!empty($deleteObjects)) {
            $cosClient = $this->getCosClient($this->secret_id, $this->secret_key, $this->region);
            try {
                $result = $cosClient->deleteObjects(array(
                    'Bucket' => $this->bucket,
                    'Objects' => $deleteObjects,
                ));
                return true;
            } catch (Exception $ex) {
                return false;
            }
        }
    }

    /**
     * 检查存储桶是否存在
     * @param $options
     * @return bool
     */
    public function checkCosBucket($options)
    {
        $cosClient = $this->getCosClient($options['secret_id'], $options['secret_key'], $options['region']);
        if (!$cosClient) {
            return false;
        }
        try {
            if ($cosClient->doesBucketExist($options['bucket'])) {
                return true;
            }
            return false;
        } catch (ServiceResponseException $e) {
            return false;
        }
    }
}
