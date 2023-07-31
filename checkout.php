<?php
/**
* Оформление заказа
*
* @return   void
*/

use \db\mysql\mysqlcrud;
use \template\template;
use \template\header;

use \tmp\master;
use \helper\price;
use \template\terms;

class checkout {

   public function __construct() {
      $this->db = new mysqlcrud();
      $this->db->connect();
      $this->content = new template();
   }

   public function index() {
      // Задаем meta заголовки страницы
      $header['description'] = 'Оформление заказа';
      $header['keywords'] = 'оформление заказа, checkout';
      $header['title'] = 'Оформление заказа'.HEAD_TITLE_END;
      echo $this->content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
      $header = new header();

      echo master::breadcrumbs();

		// START
      if(isset($_SESSION['shoppingcart'])) {
         $sql = master::shoppingCart();

         $total_value = 0;
         $order_items = null;
         foreach ($sql as $item) {
            $order_item['item_qty']        = $_SESSION['shoppingcart'][$item['1c_id']]['qty'];
            $order_item['item_cost_total'] = price::format($item['price']*$order_item['item_qty']);
            $order_item['brand']           = mb_strtoupper($item['brand']);
            $order_item['item_name']       = $order_item['brand'] . ' ' .$item['name'];
            $order_item['url']             = master::prodURL($item['1c_id'],$item['brand_lat'] . ' ' . $item['name']);

            $order_items .= $this->content->design('checkout','order-item',$order_item);

            $total_value += $item['price'] * $_SESSION['shoppingcart'][$item['1c_id']]['qty'];
         }

         $order['order-items'] = $order_items;
         $order['total-value'] = price::format($total_value);

         if ($total_value >= SHIPPING_STEP) {
            $order['shipping_value'] = 'бесплатная';
            $order['total_value'] = price::format($total_value);

         } else {
            $order['shipping_value'] = price::format(SHIPPING_COST);
            $order['total_value'] = price::format($total_value + SHIPPING_COST);
         }

         if (!empty($_SESSION['id'])) {
            //$this->db->select('users','phone,firstname,lastname,middlename',NULL,'id='.$_SESSION['id'],NULL,NULL,1);
            $this->db->sql('
               SELECT
                  users.phone,
                  users.firstname,
                  users.lastname,
                  users.middlename,
                  shipping.address
               FROM users
               LEFT JOIN
                  shipping
               ON
                  users.id = shipping.user_id AND shipping.dflt = 1
               WHERE users.id = ' . $_SESSION['id'] . '
               LIMIT 1
            ');
            $res = $this->db->getResult();

            $template = ' value="%s"';
            if ($res != null) {
               foreach ($res[0] as $key => $val) {
                  $order['value_'.$key] = (!empty($val) ? sprintf($template, $val) : null);
                  if ($key == 'phone')
                     $order['value_'.$key] .= ' readonly';
               }
            }
         } else {
            $order['value_phone'] = null;
         }

         $order['terms-personal'] = terms::personal();

         echo $this->content->design('checkout','index',$order);
      } else {
         $redirect['redirect_url'] = '/';
         $redirect['redirect_timer'] = 0;
         echo $this->content->design('javascript','redirect',$redirect);
      }
   }

}
