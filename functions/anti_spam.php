<?php

function get_headers_with_stream_context($url, $context, $assoc = 0) {
  // is not a valid url
  if(!filter_var($url, FILTER_VALIDATE_URL)){
    return false;
  }

  $fp = fopen($url, 'r', null, $context);
  if(!$fp){ // timeout or something else
    return false;
  }

  $metaData = stream_get_meta_data($fp);
  fclose($fp);

  $headerLines = $metaData['wrapper_data'];

  if(!$assoc) return $headerLines;

  $headers = array();
  foreach($headerLines as $line) {
      if(strpos($line, 'HTTP') === 0) {
          $headers[0] = $line;
          continue;
      }
      list($key, $value) = explode(': ', $line);
      $headers[$key] = $value;
  }

  return $headers;
}
/* <<小牆>> Anti-Spam v1.84 by Willin Kan. */
class anti_spam {

  function get_form_name(){
    return $GLOBALS['philnaopt']['anti_spam_field'];
  }

  function anti_spam() {
    if ( !current_user_can('read') ) {
      add_action('template_redirect', array($this, 'w_tb'), 20); // invoke w_tb after tidy
      add_action('pre_comment_on_post', array($this, 'gate'), 1);
      add_action('preprocess_comment', array($this, 'sink'), 1);
    }
  }
  // 設欄位
  function w_tb() {
    if ( is_singular() ) {
      // 非中文語系
      if ( stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'zh') === false ) {
        add_filter( 'comments_open', create_function('', "return false;") ); // 關閉評論
      } else {
        ob_start(create_function('$input','return preg_replace("#textarea(.*)name=([\\"\'])comment([\\"\'])(.+)/textarea>#", "textarea$1name=$2' . $this->get_form_name() . '$3$4/textarea><textarea name=\\"comment\\" cols=\\"100%\\" rows=\\"4\\" style=\\"display:none\\"></textarea>",$input);') );
      }
    }
  }
  // 檢查
  function gate() {
    $w = $this->get_form_name();
    if(!empty($_POST['comment'])){
      $request = $_SERVER['REQUEST_URI'];
      $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '隐瞒';
      $IP      = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] . '(通过代理)' : $_SERVER["REMOTE_ADDR"];
      $way     = isset($_POST[$w]) ? '手动操作' : '未经评论表格';
      $spamcom = isset($_POST['comment']) ? $_POST['comment'] : '';
      $_POST['spam_confirmed'] = "请求: " . $request . "\n來路: " . $referer . "\nIP: " . $IP . "\n方式: " . $way . "\n內容: " . $spamcom . "\n -- 记录成功 --";
    }else{
      $_POST['comment'] = $_POST[$w];
    }
  }
  // 處理
  function sink( $comment ) {
    // 不管 Trackbacks/Pingbacks
    if ( in_array( $comment['comment_type'], array('pingback', 'trackback') ) ) {
      return $comment;
    }
    // 已確定為 spam
    if ( !empty($_POST['spam_confirmed']) ) {

      // 方法一: 直接擋掉, 將 die(); 前面兩斜線刪除即可.
      //die();

      // 方法二: 標記為 spam, 留在資料庫檢查是否誤判.
      add_filter('pre_comment_approved', create_function('', 'return "spam";'));
      $comment['comment_content'] = "[ 小墙判断这是Spam! ]\n". $_POST['spam_confirmed'];
      $this->add_black( $comment );
    } else {

      // 檢查頭像, 国内开启此方法后, 可能会导致超时
      $hash = md5( strtolower($comment['comment_author_email']) );
      $gravatar_url = sprintf( "http://%d.gravatar.com", (hexdec($hash{0}) % 2) ) .'/avatar/'. $hash .'?d=404';

      $stream_context = stream_context_create(array(
        'http' => array(
          'timeout' => 2
        )
      ));
      $headers = get_headers_with_stream_context($gravatar_url, $stream_context);

      if ( !$headers || !preg_match("|200|", $headers[0]) ) {
        // 沒頭像的列入待審
        add_filter('pre_comment_approved', create_function('', 'return "0";'));
        //$this->add_black( $comment );
      }
    }
    return $comment;
  }
  // 列入黑名單
  function add_black( $comment ) {
    if (!($comment_author_url = $comment['comment_author_url'])) {
      return;
    }
    if ($pos = strpos($comment_author_url, '//')){
      $comment_author_url = substr($comment_author_url, $pos + 2);
    }
    if ($pos = strpos($comment_author_url, '/')){
      $comment_author_url = substr($comment_author_url, 0, $pos);
    }

    $comment_author_url = strtr($comment_author_url, array('www.' => ''));

    if (!wp_blacklist_check('', '', $comment_author_url, '', '', '')){
      update_option('blacklist_keys', $comment_author_url . "\n" . get_option('blacklist_keys'));
    }
  }
}
$anti_spam = new anti_spam();
// -- END ----------------------------------------
