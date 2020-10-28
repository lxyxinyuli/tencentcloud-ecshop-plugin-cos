<?php


/**
 * ECSHOP 管理中心腾讯云产品设置
 * ============================================================================
 * * 版权所有 2020-2040 腾讯云，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: tencent $
 * $Id: tencent_center.php 17217 2020-08-25 06:29:08Z jerry $
 */

define('IN_ECS', true);

/* 代码 */
require(dirname(__FILE__) . '/includes/init.php');

if ($GLOBALS['_CFG']['certificate_id'] == '') {
    $certi_id = 'error';
} else {
    $certi_id = $GLOBALS['_CFG']['certificate_id'];
}

$sess_id = $GLOBALS['sess']->get_session_id();
/* 检查权限 */
admin_priv('shop_config');
/*------------------------------------------------------ */
//-- 列表编辑 ?act=list_edit
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list_edit') {
    $captcha = isset($_CFG['captcha_plugin']) ? json_decode($_CFG['captcha_plugin'], true) : array();
    $smarty->assign('captcha', $captcha);
    $center  = json_decode($_CFG['tencent_center'], true);
    if (isset($center['global_secret']) && $center['global_secret'] === '1') {
        $smarty->assign('center',    $center);
    }
    $smarty->assign('ur_here',   $_LANG['tencent_captcha']);
    $smarty->display('tencent_captcha.htm');
}

/*------------------------------------------------------ */
//-- 提交   ?act=post
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'save_config') {
    $custome_secret = empty($_POST['custome_secret']) ? '' : $_POST['custome_secret'];
    $captcha_secret_id = empty($_POST['captcha_secret_id']) ? '' : $_POST['captcha_secret_id'];
    $captcha_secret_key = empty($_POST['captcha_secret_key']) ? '' : $_POST['captcha_secret_key'];
    $captcha_app_id = empty($_POST['captcha_app_id']) ? '' : $_POST['captcha_app_id'];
    $captcha_app_secret_key = empty($_POST['captcha_app_secret_key']) ? '' : $_POST['captcha_app_secret_key'];
    $data = array(
        'custome_secret' => trim($custome_secret),
        'secret_id' => trim($captcha_secret_id),
        'secret_key' => trim($captcha_secret_key),
        'captcha_app_id' => trim($captcha_app_id),
        'captcha_app_secret_key' => trim($captcha_app_secret_key),
    );
    $data = addslashes_deep($data);
    $datastr = json_encode($data);
    $captcha_plugin = isset($_CFG['captcha_plugin']) ? $_CFG['captcha_plugin'] : array();
    if (empty($captcha_plugin)) {
        $sql = "insert into " . $ecs->table('shop_config') . " (parent_id, code, type, value) values (6, 'captcha_plugin', 'hidden', '$datastr')";
        $db->query($sql);
    } else {
        $sql = "UPDATE " . $ecs->table('shop_config') . " SET value= '" . $datastr . "' WHERE code='captcha_plugin'";
        $db->query($sql);
    }
    clear_cache_files();
    require(dirname(__FILE__) . '/../includes/cls_tencentcloud_center.php');
    $tencent_center = new tencent_center();
    $upload_data = array(
        'action' => 'save_config',
        'plugin_type' => 'captcha',
        'data' => array(
            'uin' => $tencent_center->getUserUinBySecret($data['secret_id'], $data['secret_key']),
            'cust_sec_on' => $data['custome_secret'] === '1' ? 1 : 2,
            'others' => json_encode(array('captcha_appid' => $data['captcha_app_id'], 'captcha_appid_pwd'=>$data['captcha_app_secret_key']))
        )
    );
    $tencent_center->sendUserExperienceInfo($upload_data);

    sys_msg($_LANG['save_ok'], 0, array(array('href' => 'tencent_captcha.php?act=list_edit', 'text' => $_LANG['2tencent_captcha'])));
}
