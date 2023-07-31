<?php
/**
* Просмотр корзины покупок
* @return   void
* @version 2.0
*/

use \db\mysql\mysqlcrud;
use \template\template;
use \template\header;

use \tmp\master;
use \helper\price;
use \product\prod;

use \debug\dBug;

class cart {

   public function __construct() {
      $this->db = new mysqlcrud();
      $this->db->connect();
      $this->content = new template();
   }

   public function index() {
      // Задаем meta заголовки страницы
      $header['description'] = 'Корзина покупок пользователя';
      $header['keywords'] = 'корзина покупок, товары, покупки, cart, shopping cart';
      $header['title'] = 'Корзина'.HEAD_TITLE_END;
      echo $this->content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
      $header = new header();

      echo master::breadcrumbs();

      //new dBug($_SESSION['shoppingcart']);

		// START
      if(isset($_SESSION['shoppingcart'])) {
         $sql = master::shoppingCart();
         //new dBug($sql);

         $top_cart = null;
         $total_value = 0;
         $shipping_cost = 0;
         foreach ($sql as $item) {
            $total_value += $item['price'] * $_SESSION['shoppingcart'][$item['1c_id']]['qty'];

            $cart_item['item_id']         = $item['1c_id'];
            $cart_item['item_qty']        = $_SESSION['shoppingcart'][$item['1c_id']]['qty'];
            $cart_item['item_cost']       = price::format($item['price']);
            $cart_item['item_cost_total'] = price::format($item['price']*$cart_item['item_qty']);
            $cart_item['brand']           = mb_strtoupper($item['brand']);
            $cart_item['item_name']       = $cart_item['brand'] . ' ' .$item['name'];
            $cart_item['url']             = master::prodURL($item['1c_id'],$item['brand_lat'] . ' ' . $item['name']);
            $cart_item['item_img']        = HTTP_PROTOCOL.STATIC_URL.IMG_DIR.$item['path_thumbnail'];
            $cart_item['item_img']        = ($item['path_thumbnail'] != null ? HTTP_PROTOCOL.STATIC_URL.IMG_DIR.$item['path_thumbnail'] : HTTP_PROTOCOL.STATIC_URL.IMG_DIR.IMG_NO_73);
            $shipping_cost                = ($item['shipping_cost'] != null ? $item['shipping_cost'] : $shipping_cost);

            //new dBug($cart_item);

            $top_cart .= $this->content->design('cart','list-item',$cart_item);
         }

         $cart['list-item'] = $top_cart;
         $total_items = count($_SESSION['shoppingcart']);
         $cart['sub_total_value'] = price::format($total_value);

         if (($shipping_cost == 0) && ($total_value >= SHIPPING_STEP)) {
            $cart['shipping_value'] = 'бесплатная';
            $cart['total_value'] = price::format($total_value);

         } else {
            $shipping_cost = ($shipping_cost > SHIPPING_COST ? $shipping_cost : SHIPPING_COST);
            $cart['shipping_value'] = price::format($shipping_cost);
            $cart['total_value'] = price::format($total_value + $shipping_cost);
            //$cart['shipping_value'] = price::format(SHIPPING_COST);
            //$cart['total_value'] = price::format($total_value + SHIPPING_COST);
         }

         $cart['buy-1clc'] = prod::buy1clickButton();

         echo $this->content->design('cart','index',$cart);
      } else {
         $redirect['redirect_url'] = '/';
         $redirect['redirect_timer'] = 0;
         echo $this->content->design('javascript','redirect',$redirect);
      }
   }
}
