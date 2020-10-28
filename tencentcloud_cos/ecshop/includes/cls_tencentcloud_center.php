<?php

/*
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}
require_once  'tencent_cloud/center/autoload.php';

use GuzzleHttp\Client;
class tencent_center {

    //开启数据上报标志
    const SITE_REPORT_OPEN = '1';

    //开启自定义密钥标志
    const SITE_SECKEY_OPEN = '1';

    private $site_app = 'ECSHOP';
    private $experience_url = 'https://appdata.qq.com/upload';

    /*
     * 获取站点URL
     */
    public function getSiteUrl(){
        return $_SERVER['HTTP_HOST'];
    }

    /**
     * 获取唯一站点ID
     */
    private function getSiteID(){
        $sql = "select value from " . $GLOBALS['ecs']->table('shop_config')." where code='tencent_center'";
        $captcha_items = $GLOBALS['db']->getAll($sql);
        $data  = json_decode($captcha_items[0]['value'], true);
        if (empty($data['site_id'])) {
            $data['site_id'] = uniqid("ecshop_");
            $sql = "UPDATE " . $GLOBALS['ecs']->table('shop_config') . " SET value= '" . json_encode($data) . "' WHERE code='tencent_center'";
            $GLOBALS['db']->query($sql);
        }
        return $data['site_id'];
    }

    /**
     * 获取站点的平台名称
     *
     */
    private function getSiteApp(){
        return $this->site_app;
    }

    /**
     * 发送用户体验计划相关数据
     * @param $data array 插件使用的公共数据 非私密数据
     * @return bool|void
     */
    public function sendUserExperienceInfo($data=array()){
        $data['action'] = isset($data['action']) ? $data['action'] : 'save_common_config';
        $data['plugin_type'] = isset($data['plugin_type']) ? $data['plugin_type'] : 'center';
        $data['data']['site_id'] = $this->getSiteID();
        $data['data']['site_url'] = $this->getSiteUrl();
        $data['data']['site_app'] = $this->getSiteApp();

        $url = $this->getLogServerUrl();
        if (isset($data['data']['uin']) && !empty($data['data']['uin'])) {
            $this->sendPostRequest($url, $data);
            return true;
        }
        $sql = "select value from " . $GLOBALS['ecs']->table('shop_config')." where code='tencent_center'";
        $center_items = $GLOBALS['db']->getAll($sql);
        $params  = json_decode($center_items[0]['value'], true);
        if (isset($params['global_secret']) && $params['global_secret'] != self::SITE_REPORT_OPEN) {
            return false;
        }
        $this->sendPostRequest($url, $data);
        return true;
    }

    /**
     * 获取腾讯云插件日志服务器地址
     * @return string
     */
    private function getLogServerUrl(){

        return $this->experience_url;
    }

    /**
     * 发送post请求
     * @param $url
     * @param $data
     */
    private function sendPostRequest($url, $data){
        ob_start();
        if (function_exists('curl_init')) {
            $json_data = json_encode($data);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);
            curl_exec($curl);
            curl_close($curl);
        } else {
            $client = new Client();
            $client->post($url, [
                GuzzleHttp\RequestOptions::JSON => $data
            ]);
        }
        ob_end_clean();
    }

    /**
     * get user Uin by secretId and secretKey
     * @return string
     */
    public function getUserUinBySecret($secret_id, $secret_key) {
        try {
            $options = [
                'headers' => $this->getSignatureHeaders($secret_id, $secret_key),
                'body' => '{}'
            ];
            $response = (new Client(['base_uri' => 'https://ms.tencentcloudapi.com']))
                ->post('/', $options)
                ->getBody()
                ->getContents();
            $response = \GuzzleHttp\json_decode($response);
            return $response->Response->UserUin;
        } catch (\Exception $e) {
            return '';
        }
    }

    private function getSignatureHeaders($secret_id, $secret_key) {
        $headers = array();
        $service = 'ms';
        $timestamp = time();
        $algo = 'TC3-HMAC-SHA256';
        $headers['Host'] = 'ms.tencentcloudapi.com';
        $headers['X-TC-Action'] = 'DescribeUserBaseInfoInstance';
        $headers['X-TC-RequestClient'] = 'SDK_PHP_3.0.187';
        $headers['X-TC-Timestamp'] = $timestamp;
        $headers['X-TC-Version'] = '2018-04-08';
        $headers['Content-Type'] = 'application/json';

        $canonicalHeaders = 'content-type:' . $headers['Content-Type'] . "\n" .
            'host:' . $headers['Host'] . "\n";
        $canonicalRequest = "POST\n/\n\n" .
            $canonicalHeaders . "\n" .
            "content-type;host\n" .
            hash('SHA256', '{}');
        $date = gmdate('Y-m-d', $timestamp);
        $credentialScope = $date . '/' . $service . '/tc3_request';
        $str2sign = $algo . "\n" .
            $headers['X-TC-Timestamp'] . "\n" .
            $credentialScope . "\n" .
            hash('SHA256', $canonicalRequest);

        $dateKey = hash_hmac('SHA256', $date, 'TC3' . $secret_key, true);
        $serviceKey = hash_hmac('SHA256', $service, $dateKey, true);
        $reqKey = hash_hmac('SHA256', 'tc3_request', $serviceKey, true);
        $signature = hash_hmac('SHA256', $str2sign, $reqKey);

        $headers['Authorization'] = $algo . ' Credential=' . $secret_id . '/' . $credentialScope .
            ', SignedHeaders=content-type;host, Signature=' . $signature;
        return $headers;
    }

}