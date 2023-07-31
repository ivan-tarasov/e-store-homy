<?php

namespace template;

use \language\lang;
use \db\mysql\mysqlcrud;
use \template\template;

use \tmp\master;
use \auth\auth;
use \helper\price;
use \helper\string;

class header {
   /**
   * Header страницы
   */
   function __construct() {
      $this->db = new mysqlcrud();
      $this->db->connect();
      $this->content  = new template();

      // выводим имя пользователя если он авторизован
      if(isset($_SESSION['id'])) {
         // Отображаемое имя пользователя
         $this->db->select('users','*',NULL,'id='.$_SESSION['id'],NULL,NULL,1);
         $res = $this->db->getResult(); $res = $res[0];

         if (!empty($res['firstname']) && !empty($res['lastname'])) {
            $auth['username'] = $res['firstname'] . ' ' . $res['lastname'];
         } elseif (!empty($res['email'])) {
            $auth['username'] = $res['email'];
         } else {
            $auth['username'] = 'Личный кабинет';
         }

         // добавляем заглушку если пользователь админ
         $auth['admin'] = (auth::isAdmin() ? $this->content->design('index','header/navigation/admin-links') : NULL);
         $auth_menu = $this->content->design('index','header/navigation/auth-true',$auth);
      } else {
         $auth_menu = $this->content->design('index','header/navigation/auth-false');
      }
      $top_nav['auth_menu']  = $auth_menu;
      echo $this->content->design('index','header/navigation/links',$top_nav);

      // build middle block with logo, search area and cart
      $head['logo_alt']   = HEAD_DESCR;
      $head['top_cart']   = $this->top_cart();
      $head['homy_phone'] = lang::SHOP_PHONE;
      $head['homy_email'] = lang::SHOP_EMAIL_INFO;
      $head['search_txt'] = lang::SEARCH_TEXT;

		echo $this->content->design('index','header/middle',$head);

      // build categories menu
      $this->db->sql('
               SELECT
                  node.cat_id, node.name, node.url, node.icon, node.description, (COUNT(parent.name) - 1) AS depth
               FROM
                  category AS node
               CROSS JOIN
                  category AS parent
               WHERE
                  node.enable = 1 AND
                  node.lft BETWEEN parent.lft AND parent.rgt
               GROUP BY
                  node.name
               ORDER BY
                  node.cat_id
               ');
      $sql = $this->db->getResult();
      //new dBug($sql);
      $top_menu['list'] = $this->top_menu($sql);
      echo $this->content->design('index','header/menu',$top_menu);
   }

  /**
   *
   * Вывод корзины покупок пользователя
   *
   */
   private function top_cart() {
      /*
      $total = 0;
      $result = '';

      for ($i=0;$i<rand(1,6);$i++) {
         $cost = rand(999,19000);
         $total += $cost;
         $cart_item['item_cost'] = number_format($cost, 0, ',', ' ');
         $cart_item['item_name'] = 'Apple iPhone 6 plus';
         $cart_item['item_img']  = 'product-small-02.jpg';
         $result .= $this->content->design('index','shoppingcart/item',$cart_item);
      }

      $top_cart['total_value'] = number_format($total, 0, ',', ' ');
      $top_cart['total_items'] = $i;
      $top_cart['top_cart_items'] = $result;
      */

      if(isset($_SESSION['shoppingcart'])) {
         //new dBug($_SESSION['shoppingcart']);
         $sql = master::shoppingCart();

         $top_cart_items = null;
         $total_value = 0;
         $show = 1;
         foreach ($sql as $item) {
            $cart_item['item_id']  = $item['1c_id'];
            $cart_item['item_qty']  = $_SESSION['shoppingcart'][$item['1c_id']]['qty'];
            $total_value += $item['price'] * $_SESSION['shoppingcart'][$item['1c_id']]['qty'];
            $cart_item['item_cost'] = price::format($item['price']);
            $cart_item['item_brand'] = mb_strtoupper($item['brand']);
            $cart_item['item_name'] = string::truncate($item['name']);
            //$cart_item['item_img']  = master::photoURL($item['1c_id'],'thumbnail');
            $cart_item['item_img']  = ($item['path_thumbnail'] != null ? HTTP_PROTOCOL.STATIC_URL.IMG_DIR.$item['path_thumbnail'] : HTTP_PROTOCOL.STATIC_URL.IMG_DIR.IMG_NO_73);

            //if ($show <= 3)
               $top_cart_items .= $this->content->design('index','shoppingcart/item',$cart_item);

            $show++;
         }

         $total_items = count($_SESSION['shoppingcart']);
         $total_value = price::format($total_value);
      } else {
         $total_items = 0;
         $total_value = "пуста";
         $top_cart_items = '<li class="text-center"><img src="/img/cart/trolley.png" /><div class="h4 lead">Ваша корзина пуста</div></li>';
      }

      $top_cart['total_items'] = $total_items;
      $top_cart['total_value'] = $total_value;

      $top_cart['top_cart_items'] = $top_cart_items;

      return $this->content->design('index','shoppingcart/body',$top_cart);
   }

  /**
   *
   * Формирование меню с категориями товаров
   *
   * @version 2.0
   *
   */
   private function top_menu($tree) {
      //new dBug($tree);
      $code = null;
      $close_one = true;
      $close_two = true;

      foreach ($tree as $cat) {
         //new dBug($cat);
         switch ($cat['depth']) {
            case 1:
               if ($close_one) {
                  $close_one = false;
                  $after = null;
               } else {
                  $after = '</div></div></li></ul></li>';
               }
               if ($close_two) {
                  $after_two = null;
               } else {
                  $close_two = true;
                  $after_two = '</div>';
               }

               $code .= $after_two . $after .
                           '<li class="dropdown yamm-fw">
                              <a href="' . MENU_CAT_PATH . $cat['url'] . '/" class="dropdown-toggle" data-hover="dropdown">' . $cat['name'] . '</a>
                              <ul class="dropdown-menu">
                                 <li>
                                    <div class="yamm-content">
                                       <div class="row">';
               break;
            case 2:
               if ($close_two) {
                  $close_two = false;
                  $after = null;
               } else {
                  $after = '</div>';
               }

               $description = ($cat['description'] != null ? '<p class="text-muted">' . $cat['description'] . '</p>' : null);
               //$description = null;
               $code .= $after .
                           '<div class="col-md-4">
                              <a href="' . MENU_CAT_PATH . $cat['url'] . '/">
                                 <h2>
                                    <span class="glyph-icon flaticon-' . $cat['icon'] . ' fa-lg"></span>' . $cat['name'] . '
                                 </h2>
                              </a>
                              ';/**/
               break;
            case 3:
               //$code .= '<li><a href="' . MENU_CAT_PATH . $cat['url'] . '/">' . $cat['name'] . '</a></li>';
               //$code .= '<a href="' . MENU_CAT_PATH . $cat['url'] . '/" class="text-muted">' . $cat['name'] . '</a>';
               break;/**/
         }
      }

      $code .= '</div></div></div></li></ul></li>';

      return $code;
   }
}
