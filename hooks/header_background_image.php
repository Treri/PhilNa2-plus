<?php
/**
 */

// no direct access
defined('PHILNA') or die('Restricted access -- PhilNa2 gorgeous design by yinheli < http://philna.com/ >');

/**
 * head background image
 * @param unknown_type $format
 * @return unknown_type
 */
function philna_get_header_background_image($format = ''){
  // the option form db
  $opt = $GLOBALS['philnaopt']['headimg'];
  $opt = $opt ? $opt : 'default.jpg';

  if($format !== 'option' && $opt !== 'random'){
    return $opt;
  }

  $imagesDir = get_template_directory().'/images/headers';

  // get all images
  $allImages = scandir($imagesDir);
  foreach($allImages as $key=>$name){
    if(!preg_match('/[\.]gif$|png$|jpg$|jpeg$/i', $name)){
      unset($allImages[$key]);
    }
  }
  unset($key, $name);

  if(empty($allImages)){
    return;
  }

  sort($allImages);

  // for css random
  if($opt == 'random' && $format !== 'option'){
    return count($allImages)>1 ? $allImages[ mt_rand(0, count($allImages) - 1) ] : 'default.jpg';
  }

  // for admin select
  $out = '';

  foreach($allImages as $file){
    $selected = $file == $opt ? ' selected="selected"' : '';
    $out .= '<option value="'.$file.'"'.$selected.'>'.$file.'</option>';
  }

  // add a random select
  $selected = $opt == 'random' ? ' selected="selected"' : '';
  $out .='<option value="random"'.$selected.'>'.__('random', YHL).'</option>';
  unset($allImages, $file, $selected);
  return $out;
}

/**
 * add css style for #header
 * @return unknown_type
 */
function philna_header_backgroud_image(){
  $img = get_template_directory_uri() . '/images/headers/' . philna_get_header_background_image();
  if($GLOBALS['philnaopt']['post_list_type'] == 'slide'){
    $post_list_style = '.content-list .post_content{display:none;}';
  }else{
    $post_list_style = '';
  }
  $style = <<<EOF
<style type="text/css">
#header {
  background: #fff url($img) no-repeat;
}$post_list_style
</style>\n
EOF;
  echo $style;
}
add_action('wp_head', 'philna_header_backgroud_image', 99);
