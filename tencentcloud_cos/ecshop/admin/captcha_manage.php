<?php

/**
 * ECSHOP
 * ============================================================================
 * * 版权所有 2005-2018 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: captcha_manage.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

/* 检查权限 */
admin_priv('shop_config');

/*------------------------------------------------------ */
//-- 验证码设置
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'main')
{
    if (gd_version() == 0)
    {
        sys_msg($_LANG['captcha_note'], 1);
    }
    assign_query_info();
    $captcha = intval($_CFG['captcha']);

    $captcha_check = array();
    if ($captcha & CAPTCHA_REGISTER)
    {
        $captcha_check['register']          = 'checked="checked"';
    }
    if ($captcha & CAPTCHA_LOGIN)
    {
        $captcha_check['login']             = 'checked="checked"';
    }
    if ($captcha & CAPTCHA_COMMENT)
    {
        $captcha_check['comment']           = 'checked="checked"';
    }
    if ($captcha & CAPTCHA_ADMIN)
    {
        $captcha_check['admin']             = 'checked="checked"';
    }
    if ($captcha & CAPTCHA_MESSAGE)
    {
        $captcha_check['message']    = 'checked="checked"';
    }
    if ($captcha & CAPTCHA_LOGIN_FAIL)
    {
        $captcha_check['login_fail_yes']    = 'checked="checked"';
    }
    else
    {
        $captcha_check['login_fail_no']     = 'checked="checked"';
    }

    $captcha_type = isset($_CFG['captcha_type']) ? $_CFG['captcha_type'] : '';
    if (!empty($captcha_type) && $captcha_type == '2')
    {
        $captcha_check['default'] = '';
        $captcha_check['tencent'] = 'checked="checked"';
    }
    else
    {
        $captcha_check['default'] = 'checked="checked"';
        $captcha_check['tencent'] = '';
    }

    $data  = get_captcha_plugin();
    if (!empty($data) && isset($data['captcha_app_id']))
    {
        $captcha_check['captcha_app_id'] = $data['captcha_app_id'];
    }
    $smarty->assign('captcha',          $captcha_check);
    $smarty->assign('captcha_width',    $_CFG['captcha_width']);
    $smarty->assign('captcha_height',   $_CFG['captcha_height']);
    $smarty->assign('ur_here',          $_LANG['captcha_manage']);
    $smarty->display('captcha_manage.htm');
}

/*------------------------------------------------------ */
//-- 腾讯验证码接口测试
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'tencent_cpatcha_test')
{
    $codeVerifyTicket = isset($_GET['ticket']) ? json_str_iconv($_GET['ticket']) : '';
    $codeVerifyRandstr = isset($_GET['randstr']) ? json_str_iconv($_GET['randstr']) : '';
    include_once('../includes/cls_tencentcloud_captcha.php');
    $data  = get_captcha_plugin();
    if (empty($data))
    {
        echo 'false';
    }
    $captcha_class = new tencent_captcha($data);
    if (!$captcha_class->check($codeVerifyTicket, $codeVerifyRandstr))
    {
        echo 'false';
    }
    echo 'true';
}

/*------------------------------------------------------ */
//-- 保存设置
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'save_config')
{
    $captcha = 0;
    $captcha = empty($_POST['captcha_register'])    ? $captcha : $captcha | CAPTCHA_REGISTER;
    $captcha = empty($_POST['captcha_login'])       ? $captcha : $captcha | CAPTCHA_LOGIN;
    $captcha = empty($_POST['captcha_comment'])     ? $captcha : $captcha | CAPTCHA_COMMENT;
    $captcha = empty($_POST['captcha_tag'])         ? $captcha : $captcha | CAPTCHA_TAG;
    $captcha = empty($_POST['captcha_admin'])       ? $captcha : $captcha | CAPTCHA_ADMIN;
    $captcha = empty($_POST['captcha_login_fail'])  ? $captcha : $captcha | CAPTCHA_LOGIN_FAIL;
    $captcha = empty($_POST['captcha_message'])     ? $captcha : $captcha | CAPTCHA_MESSAGE;

    $captcha_type_value = empty($_POST['captcha_type'])     ? 2 : intval($_POST['captcha_type']);
    $captcha_width = empty($_POST['captcha_width'])     ? 145 : intval($_POST['captcha_width']);
    $captcha_height = empty($_POST['captcha_height'])   ? 20 : intval($_POST['captcha_height']);

    $captcha_type = get_captcha_type();
    if (empty($captcha_type))
    {
        $sql1 =  "insert into " . $ecs->table('shop_config') . " (parent_id, code, type, value) values (6, 'captcha_type', 'hidden', $captcha_type_value)";
        $db->query($sql1);
    } else {
        $sql = "UPDATE " . $ecs->table('shop_config') . " SET value='$captcha_type_value' WHERE code='captcha_type'";
        $db->query($sql);
    }

    $sql = "UPDATE " . $ecs->table('shop_config') . " SET value='$captcha' WHERE code='captcha'";
    $db->query($sql);
    $sql = "UPDATE " . $ecs->table('shop_config') . " SET value='$captcha_width' WHERE code='captcha_width'";
    $db->query($sql);
    $sql = "UPDATE " . $ecs->table('shop_config') . " SET value='$captcha_height' WHERE code='captcha_height'";
    $db->query($sql);

    clear_cache_files();
    sys_msg($_LANG['save_ok'], 0, array(array('href'=>'captcha_manage.php?act=main', 'text'=>$_LANG['captcha_manage'])));
}

function get_captcha_type()
{
    $sql = "select value from " . $GLOBALS['ecs']->table('shop_config')." where code='captcha_type'";
    $captcha_type = $GLOBALS['db']->getOne($sql);
    if (empty($captcha_type))
    {
        return '';
    }
    return $captcha_type;
}

function get_captcha_plugin()
{
    $sql = "select value from " . $GLOBALS['ecs']->table('shop_config')." where code='captcha_plugin'";
    $captcha_plugin = $GLOBALS['db']->getOne($sql);
    $captcha_plugin = is_string($captcha_plugin) ? $captcha_plugin : '';
    $captcha_plugin  = !empty($captcha_plugin) && is_string($captcha_plugin) ? json_decode($captcha_plugin, true) : array();
    return $captcha_plugin;
}

?>
