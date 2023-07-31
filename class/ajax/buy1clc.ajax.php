<?php
// SPR-4 Автоподключение классов
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
include_once(DOCUMENT_ROOT.'/class/core/autoload.inc.php');

use \language\lang;
use \db\mysql\mysqlcrud;

use \order\order;

class buy1clc {

   function __construct() {
      if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
         && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
         && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

            $this->db = new mysqlcrud();
            $this->db->connect();
            $order = new order();

            if (empty($_POST['name']) || empty($_POST['phone'])) {
               $return['title'] = 'Поля не заполнены';
               $return['error'] = lang::AJAX_ERROR_EMPTY_FIELDS;

               echo json_encode($return);
               exit;
            }

            // проверяем капчу
            if (!isset($_POST['captcha']) || $_POST['captcha'] == '') {
               $return['title'] = 'Вы точно не робот?';
               $return['error'] = 'Поставьте галочку рядом с кнопкой "Оформить заказ".';
               echo json_encode($return);
               exit;
            } else {
               $params['secret']    = RECAPTCHA_SECRET;
               $params['response']  = $_POST['captcha'];
               $params['remoteip']  = $_SERVER["REMOTE_ADDR"];
               $response = json_decode(file_get_contents(RECAPTCHA_URL.'?'.http_build_query($params)),true);
               if ($response['success'] != true) {
                  $return['title'] = 'Проблемы с капчей:';
                  $return['error'] = $response['error-codes'];
                  echo json_encode($return);
                  exit;
               }
            }

            $return = $order->addToDB($_POST['phone'],$_POST['name'],$_POST['message'],$_POST['id']);

            echo json_encode($return);
      } else
         echo 'И что мы здесь забыли?';
   }

}

new buy1clc();
