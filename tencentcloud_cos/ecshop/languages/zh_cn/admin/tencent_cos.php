<?php
/**
 * ECSHOP 腾讯插件管理语言文件
 * ============================================================================
 * * 版权所有 2020-2050 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: jerry $
 * $Id: tencent_center.php 17217 2020-09-02 06:29:08Z jierry $
 */
/* 列表页面 */
$_LANG['tencent_cos'] = '对象存储配置';
$_LANG['cos_on'] = '开启腾讯云存储';
$_LANG['custome_secret'] = '开启自定义配置';
$_LANG['SecretId'] = 'SecretId';
$_LANG['SecretKey'] = 'SecretKey';
$_LANG['Region'] = '所属地域';
$_LANG['Bucket'] = '空间名称';
$_LANG['CosRemoteUrl'] = '访问域名';
$_LANG['NoLocalFile'] = '不在本地保存';
$_LANG['Test'] = '一键测试';
$_LANG['CosOnNotice'] = '可对商品图片、商品缩略图及商品相册的附件进行远程存储';
$_LANG['ButtonTest'] = '开始测试';
$_LANG['RegionNotice'] = '"所属区域"的值必须和腾讯云对象存储中存储桶的所属区域一致';
$_LANG['BucketNotice'] = '首先到<a href="https://console.cloud.tencent.com/cos5/bucket" target="_blank">腾讯云控制台</a>新建bucket存储桶或填写腾讯云COS中已创建的bucket';
$_LANG['RemoteUrlNotice'] = '示范：https://wordpress-cos-xxxxx.cos.ap-shanghai.myqcloud.com';
$_LANG['tencent_cos_disable'] = '腾讯云存储测试失败，请检查配置参数是否正确。';
$_LANG['tencent_cos_enable'] = '腾讯云存储测试成功。';

/* JS 语言项 */
$_LANG['js_languages']['no_secret_id'] = '没有输入SecretId。';
$_LANG['js_languages']['no_secret_key'] = '没有输入SecretKey。';
$_LANG['js_languages']['no_region'] = '没有输入所属地域。';
$_LANG['js_languages']['no_bucket'] = '没有输入空间名称。';
$_LANG['js_languages']['no_remote_url'] = '没有输入访问域名。';