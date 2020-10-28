<?php

/**
 * ECSHOP
 * ============================================================================
 * * 版權所有 2005-2018 上海商派網絡科技有限公司，並保留所有權利。
 * 網站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 這不是一個自由軟件！您只能在不用於商業目的的前提下對程序代碼進行修改和
 * 使用；不允許對程序代碼以任何形式任何目的的再發布。
 * ============================================================================
 * $Author: liubo $
 * $Id: captcha_manage.php 17217 2011-01-19 06:29:08Z liubo $
*/

$_LANG['captcha_manage'] = '驗證碼設置';
$_LANG['captcha_note'] = '開啓驗證碼需要服務GD庫支持，而您的服務器不支持GD。';

$_LANG['captcha_setting'] = '驗證碼設置';
$_LANG['captcha_turn_on'] = '啓用驗證碼';
$_LANG['turn_on_note'] = '圖片驗證碼可以避免惡意批量評論或提交信息，推薦打開驗證碼功能。注意: 啓用驗證碼會使得部分操作變得繁瑣，建議僅在必需時打開';
$_LANG['captcha_register'] = '新用戶註冊';
$_LANG['captcha_login'] = '用戶登錄';
$_LANG['captcha_comment'] = '發表評論';
$_LANG['captcha_admin'] = '後臺管理員登錄';
$_LANG['captcha_login_fail'] = '登錄失敗時顯示驗證碼';
$_LANG['login_fail_note'] = '選擇“是”將在用戶登錄失敗 3 次後才顯示驗證碼，選擇“否”將始終在登錄時顯示驗證碼。注意: 只有在啓用了用戶登錄驗證碼時本設置纔有效';
$_LANG['captcha_width'] = '驗證碼圖片寬度';
$_LANG['width_note'] = '驗證碼圖片的寬度，範圍在 40～145 之間';
$_LANG['captcha_height'] = '驗證碼圖片高度';
$_LANG['height_note'] = '驗證碼圖片的高度，範圍在 15～50 之間';

$_LANG['js_languages']['width_number'] = '圖片寬度請輸入數字!';
$_LANG['js_languages']['proper_width'] = '圖片寬度要在40到145之間!';
$_LANG['js_languages']['height_number'] = '圖片高度請輸入數字!';
$_LANG['js_languages']['proper_height'] = '圖片高度要在15到50之間!';

$_LANG['save_ok'] = '設置保存成功';
$_LANG['captcha_message'] = '留言板留言';

$_LANG['captcha_type'] = '驗證碼類型';
$_LANG['captcha_type_note'] = '英文圖片驗證碼是ecshop自帶的含字母數字的圖片驗證方式，騰訊雲驗證碼為圖片拖動驗證類型';
$_LANG['default_type'] = '英文圖片驗證碼';
$_LANG['tencent_type'] = '騰訊雲驗證碼';
$_LANG['open_captcha_config'] = '打開驗證碼配置';
$_LANG['captcha_machine_test'] = '人機驗證';
$_LANG['captcha_machine_test_pass'] = '人機驗證通過';
$_LANG['captcha_machine_test_fail'] = '人機驗證失敗，請打開驗證碼配置，填寫騰訊雲驗證碼配置信息並確保信息正確。';

?>
