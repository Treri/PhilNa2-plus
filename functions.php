<?php
/**
 * functions
 */

// no direct access
defined('ABSPATH') or die('Restricted access -- PhilNa2 gorgeous design by yinheli < http://philna.com/ >');
//激活菜单项
if ( function_exists('register_nav_menus') ) {
  register_nav_menus(array('primary' => '头部导航栏'));
}
/*
Note:
  加载顺序:
  1.读取主题配置(部分自定义函数要使用)
  2.加载自定义函数
  3.Ajax判断(自动调用已定义的函数)
*/
// debug all
//error_reporting(E_ALL);

// hide error (note: 开启隐藏所有错误时, 一旦出现严重错误将导致'白屏')
error_reporting(0);

define('YHL', 'philna2');
define('PHILNA', 'philna2');

// debug - if true the errors will display below footer when admin login
define('PHILNA_DEBUG', false);

// app dir
define('PHILNA_APP', TEMPLATEPATH.'/app');
// custom-functions dir
define('PHILNA_CUS', TEMPLATEPATH.'/custom-functions');

// Load theme textdomain
load_theme_textdomain(YHL, TEMPLATEPATH.'/languages');

// befor load my function we load the base
// functions for other functions
include_once PHILNA_APP.'/base/options.php';
include_once PHILNA_APP.'/base/format.php';
include_once PHILNA_APP.'/base/base.php';
include_once PHILNA_APP.'/base/json.php';
include_once PHILNA_APP.'/base/ajax.php';

// init philna options
$GLOBALS['philnaopt'] = PhilNaGetOpt::getInstance();

/**
 * include all PHP script
 * @param string $dir
 * @return unknown_type
 */
function philnaIncludeAll($dir){
  $dir = realpath($dir);
  if($dir){
    $files = scandir($dir);
    sort($files);
    foreach($files as $file){
      if($file == '.' || $file == '..'){
        continue;
      }elseif(preg_match('/\.php$/i', $file)){
        include_once $dir.'/'.$file;
      }
    }
  }
}

// include functions by yinheli
philnaIncludeAll( PHILNA_APP );

// include functions by user
philnaIncludeAll( PHILNA_CUS );

// admin panel
!is_admin() || include_once TEMPLATEPATH.'/admin/admin.php';

do_action('PhilNaReady');

function custom_smilies_src($src, $img){
    return get_bloginfo('template_directory').'/images/smilies/' . $img;
}
add_filter('smilies_src', 'custom_smilies_src', 10, 2); 
if ( !isset( $wpsmiliestrans ) ) {
    $wpsmiliestrans = array(
      ':mrgreen:' => '11.gif',
      ':no:' => '1.gif',
      ':twisted:' => '19.gif',
      ':shut:' => '23.gif',
      ':eat:' => '3.gif',
      ':arrow:' => '16.gif',
      ':shock:' => '7.gif',
      ':surprise:' => '26.gif',
      ':smile:' => '33.gif',
      ':???:' => '5.gif',
      ':cool:' => '10.gif',
      ':cold:' => '14.gif',
      ':evil:' => '2.gif',
      ':grin:' => '4.gif',
      ':idea:' => '9.gif',
      ':han:' => '6.gif',
      ':oops:' => '25.gif',
      ':mask:'=>'27.gif',
      ':sigh:' => '15.gif',
      ':razz:' => '17.gif',
      ':roll:' => '21.gif',
      ':cry:' => '28.gif',
      ':zzz:' => '29.gif',
      ':eek:' => '18.gif',
      ':love:' => '20.gif',
      ':sex:' => '13.gif',
      ':jiong:' => '8.gif',
      ':lol:' => '24.gif',
      ':mad:' => '31.gif',
      ':ool:' => '32.gif',
      ':sad:' => '30.gif',
    );
  }
//禁用半角符号自动转换为全角
foreach(array('comment_text','the_content','the_excerpt','the_title') as $xx){
  remove_filter($xx,'wptexturize');
}

function wp_hide_nag() {
  remove_action( 'admin_notices', 'update_nag', 3 );
}
add_action('admin_menu','wp_hide_nag');
