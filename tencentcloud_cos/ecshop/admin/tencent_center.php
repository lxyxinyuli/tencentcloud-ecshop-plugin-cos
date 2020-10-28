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

if($GLOBALS['_CFG']['certificate_id']  == '')
{
    $certi_id='error';
}
else
{
    $certi_id=$GLOBALS['_CFG']['certificate_id'];
}

$sess_id = $GLOBALS['sess']->get_session_id();
/* 检查权限 */
admin_priv('shop_config');
/*------------------------------------------------------ */
//-- 列表编辑 ?act=list_edit
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list_edit')
{
    $data  = isset($_CFG['tencent_center']) ? json_decode($_CFG['tencent_center'], true) : array();
    $smarty->assign('ur_here',   $_LANG['tencent_center']);
    $smarty->assign('center',    $data);
    $smarty->display('tencent_center.htm');
}

/*------------------------------------------------------ */
//-- 提交   ?act=post
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'save_config')
{
    $data['global_secret'] = empty($_POST['global_secret'])  ? '' : trim($_POST['global_secret']);
    $data['secret_id'] = empty($_POST['secret_id'])  ? '' : trim($_POST['secret_id']);
    $data['secret_key'] = empty($_POST['secret_key'])  ? '' : trim($_POST['secret_key']);
    $data['user_experience'] = empty($_POST['user_experience'])  ? '0' : trim($_POST['user_experience']);
    $data = addslashes_deep($data);
    $tencent_center = isset($_CFG['tencent_center']) ? $_CFG['tencent_center'] : '';
    $tencent_center  = json_decode($tencent_center, true);
    if (empty($tencent_center))
    {
        $tencent_center = json_encode($data);
        echo $tencent_center;
        $sql =  "insert into " . $ecs->table('shop_config') . " (parent_id, code, type, value) values ('6', 'tencent_center', 'hidden', '$tencent_center')";
        $db->query($sql);
    } else {
        $tencent_center = array_merge($tencent_center, $data);
        $sql = "UPDATE " . $ecs->table('shop_config') . " SET value= '" . json_encode($tencent_center) . "' WHERE code='tencent_center'";
        $db->query($sql);
    }
    clear_cache_files();
    require(dirname(__FILE__) . '/../includes/cls_tencentcloud_center.php');
    $tencent_center = new tencent_center();
    $upload_data = array(
        'action' => 'save_common_config',
        'plugin_type' => 'center',
        'data' => array(
            'uin' => $tencent_center->getUserUinBySecret($data['secret_id'], $data['secret_key']),
            'cust_sec_on' => $data['global_secret'] === '1' ? 1 : 2,
        )
    );
    $tencent_center->sendUserExperienceInfo($upload_data);

    sys_msg($_LANG['save_ok'], 0, array(array('href'=>'tencent_center.php?act=list_edit', 'text'=>$_LANG['1tencent_plugins'])));
}
