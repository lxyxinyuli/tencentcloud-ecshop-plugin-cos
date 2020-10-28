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
require_once  'tencent_cloud/captcha/autoload.php';
require_once 'cls_tencentcloud_center.php';

use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Captcha\V20190722\CaptchaClient;
use TencentCloud\Captcha\V20190722\Models\DescribeCaptchaResultRequest;

class tencent_captcha {

    const VERIFY_SUCCESS_FLG = 1;
    private $version = '1.0.0';
    private $secret_id = '';
    private $secret_key = '';
    private $app_id = '';
    private $app_secret_key = '';
    private $plugin_type = 'captcha';

    public function __construct($captcha_items)
    {
        $this->secret_id = $captcha_items['secret_id'];
        $this->secret_key = $captcha_items['secret_key'];
        $this->app_id = $captcha_items['captcha_app_id'];
        $this->app_secret_key = $captcha_items['captcha_app_secret_key'];
    }

    /**
     * valide captcha
     * @return bool true/false
     */
    public function check($ticket, $randStr){
        $verifyCode = $this->verifyCodeReal($this->secret_id, $this->secret_key, $ticket, $randStr, $this->app_id, $this->app_secret_key);
        if ($verifyCode['CaptchaCode'] != self::VERIFY_SUCCESS_FLG) {
            return false;
        }else{
            return true;
        }
    }

    /**
     * check captcha on server
     * @param $secretID
     * @param $secretKey
     * @param $ticket
     * @param $randStr
     * @param $codeAppId
     * @param $codeSecretKey
     * @return array|mixed
     */
    private function verifyCodeReal($secretID, $secretKey,$ticket, $randStr, $codeAppId, $codeSecretKey){

        try {
            $remote_ip = preg_replace('/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR']);
            $cred = new Credential($secretID, $secretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("captcha.tencentcloudapi.com");
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new CaptchaClient($cred, "", $clientProfile);
            $req = new DescribeCaptchaResultRequest();
            $params = array('CaptchaType' => 9, 'Ticket' => $ticket, 'Randstr' => $randStr, 'CaptchaAppId' => intval($codeAppId), 'AppSecretKey' => $codeSecretKey, 'UserIp' => $remote_ip);
            $req->fromJsonString(json_encode($params));
            $resp = $client->DescribeCaptchaResult($req);
            return json_decode($resp->toJsonString(), JSON_OBJECT_AS_ARRAY);
        } catch (TencentCloudSDKException $e) {
            return array('requestId' => $e->getRequestId(), 'errorMessage' => $e->getMessage());
        }
    }

    /**
     * 获取数据上报数据
     * @param $action
     * @return mixed
     */
    public function getTencentCloudWordPressStaticData($action)
    {
        $tencent_center = new tencent_center();
//        $site_id = TencentWordpressPluginsSettingActions::getWordPressSiteID();
//        $site_url = TencentWordpressPluginsSettingActions::getWordPressSiteUrl();
//        $site_app = TencentWordpressPluginsSettingActions::getWordPressSiteApp();
        $static_data['action'] = $action;
        $static_data['plugin_type'] = $this->plugin_type;
//        $static_data['data'] = array(
//            'site_id'  => $site_id,
//            'site_url' => $site_url,
//            'site_app' => $site_app
//        );

        $common_option = get_option(TENCENT_WORDPRESS_COMMON_OPTIONS);
        $tcwpcos_options = get_option(TENCENT_WORDPRESS_COS_OPTIONS);
        if ($tcwpcos_options['customize_secret'] === true && isset($tcwpcos_options['secret_id']) && isset($tcwpcos_options['secret_key'])) {
            $secret_id = $tcwpcos_options['secret_id'];
            $secret_key = $tcwpcos_options['secret_key'];
        } elseif (isset($common_option['secret_id']) && isset($common_option['secret_key'])) {
            $secret_id = $common_option['secret_id'];
            $secret_key = $common_option['secret_key'];
        } else {
            $secret_id = '';
            $secret_key = '';
        }
        $static_data['data']['uin'] = TencentWordpressPluginsSettingActions::getUserUinBySecret($secret_id, $secret_key);

        $static_data['data']['cust_sec_on'] = ((int)$tcwpcos_options['customize_secret']) === 1 ? 1 : 2;

        $others = array(
            'cos_bucket' => $tcwpcos_options['bucket'],
            'cos_region' => $tcwpcos_options['region']
        );
        $static_data['data']['others'] = json_encode($others);
        return $static_data;
    }

}

