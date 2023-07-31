<?php
// SPR-4 Автоподключение классов
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
include_once(DOCUMENT_ROOT.'/class/core/autoload.inc.php');

use \tmp\photoupload;

class photouploadAJAX {

   function __construct() {
      if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])
         && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
         && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

         session_start();

         /*$tst = array(
            array(
               "url" => "http://mdata.yandex.net/i?path=b0919115838_img_id2193757513927420257.jpg",
               "width" => 551,
               "height" => 444
            ),
            array(
               "url" => "http://mdata.yandex.net/i?path=b0919115952_img_id2284651529172552876.jpg",
               "width" => 525,
               "height" => 520
            ),
         );/**/

         $photoupload = new photoupload();
         $photoupload->photos($_SESSION['cc']['item_id'],$_FILES['photo-file']);
         //$photoupload->test($_SESSION['cc']['item_id'],$tst);

         echo $return['message'] = '<pre>' . var_dump($_SESSION) . '</pre>';

         //echo json_encode($return);

      } else
         echo 'И что мы здесь забыли?';
   }

}

new photouploadAJAX();
