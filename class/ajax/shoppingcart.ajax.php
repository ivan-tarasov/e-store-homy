<?php
// SPR-4 Автоподключение классов
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
include_once(DOCUMENT_ROOT.'/class/core/autoload.inc.php');

use \db\mysql\mysqlcrud;
use \template\template;

use \helper\price;
use \helper\string;
use \tmp\master;

class shoppingcart {

   function __construct() {
      if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
         && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
         && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

         session_start();

         $this->db = new mysqlcrud();
         $this->db->connect();
         $this->content = new template();

         $this->id  = $_POST['id'];
         $action = $_POST['action'];

         $this->db->sql('SELECT stock FROM catalog WHERE 1c_id = '.$this->id);
         $this->stock = $this->db->getResult();
         $this->stock = $this->stock[0]['stock'];

         $return_const['cartup_flg'] = (isset($_SESSION['shoppingcart']) ? true : false);

         switch ($_POST['action']) {
            case "addtocart":
               if (!isset($_SESSION['shoppingcart'][$this->id])) {
                  $_SESSION['shoppingcart'][$this->id]['qty'] = 1;
               } else {
                  $_SESSION['shoppingcart'][$this->id]['qty']++;
               }
               $return = $this->calc();
               break;
            case "plus":
               $return = $this->updPls();
               break;
            case "reduce":
               $return = $this->updMin();
               break;
            case "delete":
               $return = $this->deleting();
               break;
            default:
               $return['price'] = "Не выбрано действие!";
         }

         ### Объединяем массивы
         $return = array_merge($return, $return_const);

         ### Выводим JSON строку с результатом
         echo json_encode($return);

      } else
         echo 'И что мы здесь забыли?';
   }

   private function updPls() {
      if (!isset($_SESSION['shoppingcart'][$this->id])) {
         $_SESSION['shoppingcart'][$this->id]['qty'] = 1;
      } elseif ($_SESSION['shoppingcart'][$this->id]['qty'] < $this->stock) {
         $_SESSION['shoppingcart'][$this->id]['qty']++;
      } else {
         $return = $this->calc('В данный момент на складе '.$this->stock.' шт.');
      }

      $return = ( !isset($return) ? $this->calc() : $return );
      return $return;
   }

   private function updMin() {
      if (isset($_SESSION['shoppingcart'][$this->id])) {
         if ($_SESSION['shoppingcart'][$this->id]['qty'] == 1)
            unset($_SESSION['shoppingcart'][$this->id]);
         else
            $_SESSION['shoppingcart'][$this->id]['qty']--;

         if (empty($_SESSION['shoppingcart']))
            unset($_SESSION['shoppingcart']);
      }

      $return = $this->calc();
      return $return;
   }

   private function deleting() {
      if (isset($_SESSION['shoppingcart'][$this->id]))
         unset($_SESSION['shoppingcart'][$this->id]);

      $return = $this->calc();
      return $return;
   }

   private function calc( $message = null ) {
      if (empty($_SESSION['shoppingcart'])) {
         unset($_SESSION['shoppingcart']);
         $return['deleted'] = true;
         $return['redirect'] = '/';

         return $return;
      }

      $this->db->select('catalog','*',NULL,'1c_id='.$this->id,NULL,NULL,1);
      $sql = $this->db->getResult();

      // Тотал по обновляемой позиции
      $return['price'] = ( isset($_SESSION['shoppingcart'][$this->id]['qty'])
                           ? price::format($_SESSION['shoppingcart'][$this->id]['qty'] * $sql[0]['price'])
                           : '<span class="text-danger">удалено</span>'
                        );

      // Тотал по всей корзине
      $sql = master::shoppingCart();
      $total_value = 0;
      $shipping_cost = 0;
      foreach ($sql as $item) {
         $total_value   += $item['price'] * $_SESSION['shoppingcart'][$item['1c_id']]['qty'];
         $shipping_cost = ($item['shipping_cost'] != null ? $item['shipping_cost'] : $shipping_cost);
      }
      $return['subtotal'] = price::format($total_value);

      // Рассчитываем стоимость доставки
      list($return['shipping'], $return['totalvalue']) = $this->calcTotal($total_value,$shipping_cost);

      // Кол-во товара в корзине
      $return['itemqty'] = ( !isset($_SESSION['shoppingcart'][$this->id]['qty']) ? 0 : $_SESSION['shoppingcart'][$this->id]['qty'] );

      // Всего товаров в корзине
      $return['totalqty'] = count($_SESSION['shoppingcart']);

      // Приращиваем верхнюю корзину
      $cart_item['item_id']  = $this->id;
      $cart_item['item_qty']  = 1;
      $cart_item['item_cost'] = price::format($item['price']);
      $cart_item['item_brand'] = mb_strtoupper($item['brand']);
      $cart_item['item_name'] = string::truncate($item['name']);
      $cart_item['item_img']  = ($item['path_thumbnail'] != null ? HTTP_PROTOCOL.STATIC_URL.IMG_DIR.$item['path_thumbnail'] : HTTP_PROTOCOL.STATIC_URL.IMG_DIR.IMG_NO_73);
      $return['cartup'] = $this->content->design('index','shoppingcart/item',$cart_item);

      if (isset($_SESSION['shoppingcart'][$this->id]['qty'])) {
         $return['qty'] = $_SESSION['shoppingcart'][$this->id]['qty'];
      } else {
         $return['qty'] = 0;
         $return['deleted'] = true;
      }
      //$return['qty'] = ( isset($_SESSION['shoppingcart'][$this->id]['qty']) ? $_SESSION['shoppingcart'][$this->id]['qty'] : 0 );
      $return['message'] = $message;

      return $return;
   }

   private function calcTotal($total,$shipping_cost = 0) {
      if (($shipping_cost == 0) && ($total >= SHIPPING_STEP)) {
         $shipping = 'бесплатная';
         $totalvalue = price::format($total);
      } else {
         $shipping_cost = ($shipping_cost > SHIPPING_COST ? $shipping_cost : SHIPPING_COST);
         $shipping = price::format($shipping_cost);
         $totalvalue = price::format($total + $shipping_cost);
      }

      return array($shipping,$totalvalue);
   }
}

new shoppingcart();
