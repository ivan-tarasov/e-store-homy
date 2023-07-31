<?php
// SPR-4 Автоподключение классов
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
include_once(DOCUMENT_ROOT.'/class/core/autoload.inc.php');

use \order\order;

class checkout {

   function __construct() {
      if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])
         && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
         && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

         $order = new order();

         if (empty($_POST['lastname'])) {
            $return['error']['input'] = 'lastname';
            $return['error']['help']  = 'Пожалуйста, заполните это поле, чтобы мы знали как к Вам обращаться.';

            echo json_encode($return);
            exit;
         } elseif (empty($_POST['reg-phone'])) {
            $return['error']['input'] = 'reg-phone';
            $return['error']['help']  = 'Нам нужно знать на какой номер позвонить';

            echo json_encode($return);
            exit;
         }

         if ($_POST['address'] != '')
            $_POST['note'] .= "\n\nАдрес доставки: ".$_POST['address'];
         if ($_POST['deliveryTime'] != '')
            $_POST['note'] .= "\nВремя доставки: ".$_POST['deliveryTime'];
         if ($_POST['pay'] != '')
            $_POST['note'] .= "\nОплата: ".$_POST['pay'];

         $return = $order->addToDB($_POST['reg-phone'],$_POST['lastname'],$_POST['note']);

         if ($return['ok']) {
            $return['class']   = 'success';
            $return['title']   = 'Заказ размещен';
            $return['message'] = 'Заказ успешно размещен. Ожидайте звонка оператора для уточнения заказа.';
            $return['url']     = '/my/orders/';
         } else {
            $return['class']   = 'error';
            $return['title']   = 'Ошибка при размещении заказа';
            $return['message'] = $order;
            $return['url']     = '/checkout/';
         }

         echo json_encode($return);
      } else
         echo 'И что мы здесь забыли?';
   }

}

new checkout();
