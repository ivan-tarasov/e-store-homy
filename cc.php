<?php
/**
* Административная панель управления магазином
* @version 2.2
*/

use \language\lang;
use \db\mysql\mysqlcrud;
use \template\template;
use \template\header;

use \tmp\master;
use \tmp\nestedtree;
use \core\counters;
use \auth\auth;
use \helper\phone;
use \helper\price;
use \sms\smsru;
use \vendor\php_rutils\RUtils;
use \vendor\php_rutils\struct\TimeParams;
use \debug\dBug;

if(!auth::isLogin() || !auth::isAdmin())
   header("Location: /login/");

class cc {
   const UPLOADER_VERSION = '2.3';

   /**
   * Конструктор и футер класса
   */
   function __construct() {
      $this->db = new mysqlcrud();
      $this->db->connect();
      $this->content = new template();
      $this->sms = new smsru();
      $this->ntree = new nestedtree();

      ### HEADER
      echo $this->content->design('cc','header');

      ### TOP NAVIGATION
      echo $this->content->design('cc','top-nav');

      ### LEFT MENU
      ###### Всего заказов
      $this->db->select('orders','COUNT(*) AS count');
      $res = $this->db->getResult();
      $menu['orders_total'] = $res[0]['count'];
      ###### Новых заказов
      $this->db->select('orders','COUNT(*) AS count',null,'deleted = 0 AND status '.ORDER_STATUS_NEW);
      $res = $this->db->getResult();
      $menu['orders_new'] = ($res[0]['count'] == 0 ? null : $res[0]['count']);
      ###### Закаов в работе (статусы с 1 по 5)
      $this->db->select('orders','COUNT(*) AS count',null,'deleted = 0 AND status '.ORDER_STATUS_INWORK);
      $res = $this->db->getResult();
      $menu['orders_inwork'] = ($res[0]['count'] == 0 ? null : $res[0]['count']);
      ###### Закрытые с ошибками заказы
      $this->db->select('orders','COUNT(*) AS count',null,'deleted = 0 AND status '.ORDER_STATUS_ERROR);
      $res = $this->db->getResult();
      $menu['orders_err'] = ($res[0]['count'] == 0 ? null : $res[0]['count']);
      ###### УСПЕШНО выполненные заказы
      $this->db->select('orders','COUNT(*) AS count',null,'deleted = 0 AND status '.ORDER_STATUS_DONE);
      $res = $this->db->getResult();
      $menu['orders_end'] = ($res[0]['count'] == 0 ? null : $res[0]['count']);

      echo $this->content->design('cc','left-nav',$menu);
   }
   function __destruct() {
      echo $this->content->design('cc','footer');
   }

   /**
   * Dashboard админ. панели
   */
   public function index() {
      $pg['title'] = "Панель администрирования";
      echo $this->content->design('cc','page-title',$pg);

      // Новых заказов
      $sql = $this->db->select('orders','COUNT(*) AS orders',null,'deleted = 0 AND status = 0');
      $res = $this->db->getResult();
      $homepage['new_orders'] = $res[0]['orders'];

      // Баланс на SMS.RU
      $homepage['sms_balance'] = ceil($this->sms->balance());

      // Всего товаров в базе
      $sql = $this->db->select('catalog','COUNT(*) AS items');
      $res = $this->db->getResult();
      $homepage['items_total'] = $res[0]['items'];

      // Всего пользователей в базе
      $sql = $this->db->select('users','COUNT(*) AS users');
      $res = $this->db->getResult();
      $homepage['users_total'] = $res[0]['users'];

      echo $this->content->design('cc','homepage',$homepage);

      //$this->__footer();
   }

   /**
   * Работа с заказами
   */
   public function orders($params = false) {
      $pg['title'] = "Заказы";
      echo $this->content->design('cc','page-title',$pg);

      switch ($params['status']) {
         case 'new':
            $order_status = ORDER_STATUS_NEW;
            break;
         case 'inwork':
            $order_status = ORDER_STATUS_INWORK;
            break;
         case 'error':
            $order_status = ORDER_STATUS_ERROR;
            break;
         case 'done':
            $order_status = ORDER_STATUS_DONE;
            break;
         default:
            $order_status = ORDER_STATUS_NEW;
      }

      $this->db->sql('
         SELECT
            orders.order_id,
            orders.status,
            orders_status.next AS status_next,
            orders_status.stage AS status_stage,
            orders_status.name AS status_name,
            orders_status.style AS status_style,
            orders_status.strip AS status_strip,
            orders_status.active AS status_active,
				orders.paid,
            orders.date_post,
            orders.fio,
            orders.phone AS phone_order,
            orders.details,
            orders.cart,
            orders.note_usr,
            users.phone AS phone_user,
            users.avatar
         FROM orders
         LEFT JOIN
            users
         ON
            orders.user = users.id
         LEFT JOIN
            orders_status
         ON
            orders.status = orders_status.id
         WHERE
            deleted = 0 AND
            orders.status '.$order_status.'
         ORDER BY
            orders.date_done DESC,
            orders.date_change DESC
      ');
      $res = $this->db->getResult();

      ### Шаблон вывода (временный)
      $tmp['dl'] = '<dt><abbr title="%1$s">%1$s</abbr></dt><dd>%2$s</dd>';

      foreach ($res as $order) {
         $details       = json_decode($order['details'],true);
         //$order['cart'] = json_decode($order['cart'],true);
         $cart = explode('|', $order['cart']);
         $order['cart'] = [];
         foreach ($cart as $element) {
            $element = explode(':', $element);
            $id = $element[0];
            $order['cart'][$id] = [
               'price' => $element[1],
               'qty'   => $element[2]
            ];
         }
         //new dBug($order['cart']);

         ### ID заказа
         $list['id'] = $order['order_id'];

         ### Форматируем дату
         $list['was_created'] = date('d.m.Y в H:i',strtotime($order['date_post']));

         ### Статус заказа
         //$list['status']      = master::orderStatus($order['status']);
         ### ФИО
         $list['order_fio']   = $order['fio'];
         ### Телефон
         $list['order_phone'] = phone::format($order['phone_order'],true);
         ### Город
         $list['user_city']   = ($details['location']['city'] != null ? $details['location']['city'] : null) .
                                ($details['location']['region'] != null ? ', '.$details['location']['region'] : null);
         $list['user_city']   = ($list['user_city'] == null ? 'Нет данных о городе.' : $list['user_city']);
         ### Дата регистрации заказа
         $list['date_post']   = $order['date_post'];
         ### Данные пользователя
         $list['user_info']   = sprintf(
                                    $tmp['dl'],
                                    'Служебная информация',
                                    '<p><i class="fa fa-lg fa-globe"></i> '.$details['location']['ip'].'<br />
                                        <i class="fa fa-lg fa-'.$details['system']['platform'].'"></i> '.ucfirst($details['system']['platform']).'<br />
                                        <i class="fa fa-lg fa-'.$details['system']['short'].'"></i> '.$details['system']['browser'].'</p>
                                ');
         ### Заметки пользователя
         $list['note_user']   = ( $order['note_usr'] != '' ? sprintf($tmp['dl'],'Заметка пользователя',nl2br($order['note_usr'])) : null );
         ### Статус заказа
         $list['order_status_stage'] = $order['status_stage'];
         $list['order_status_name']  = $order['status_name'];
         $list['order_status_style'] = $order['status_style'];
         $list['order_bar_striped']  = ($order['status_strip'] == 1 ? ' progress-striped' : null);
         $list['order_bar_striped'] .= ($order['status_active'] == 1 ? ' active' : null);
			### Статус выплат по заказу
			if ($order['status'] == 99)
				$list['paid_status'] = ($order['paid'] == 1 ? 'paid' : 'not-paid');

         ### Формирование позиций, заказанных пользователем
         $this->db->sql('
            SELECT
               catalog.1c_id,
               catalog.name AS title,
               category.url AS cat_url,
               category.name AS category,
               brands.brand_clean AS brand,
               images.path_big AS photo
            FROM catalog
            LEFT JOIN
               category
            ON
               catalog.cat_id = category.cat_id
            LEFT JOIN
               brands
            ON
               catalog.brand_id = brands.brand_id
            LEFT JOIN
               images
            ON
               catalog.1c_id = images.1c_id AND images.onmain = 1
            WHERE catalog.1c_id IN ('.implode(',',array_keys($order['cart'])).')
         ');
         $res = $this->db->getResult();

         $total = 0;
         $cart  = null;
         foreach ($res as $item) {
            //new dBug($item);

            //$subtotal = $order['cart'][$item['1c_id']]['qty'] * $item['price'];
            $subtotal = $order['cart'][$item['1c_id']]['qty'] * $order['cart'][$item['1c_id']]['price'];
            $total += $subtotal;

            $title = mb_strtoupper($item['brand']).' '.$item['title'];

            ### Дерево каталогов категории товара
            $res = $this->ntree->single_path($item['cat_url']);
            $ntree = null;
            foreach ($res as $tree) {
               $ntree .= $tree['name'] . ' -> ';
            }
            $ntree = str_replace('Каталог','',$ntree);

            $config['rnd']       = rand(1,3000);
            $config['id']        = $item['1c_id'];
            $config['cat_url']   = MENU_CAT_PATH.$item['cat_url'];
            $config['category']  = $item['category'];
            //$config['ntree']     = $ntree;
            $config['title_url'] = master::prodURL($item['1c_id'],$title);
            $config['ppvr-img']  = ($item['photo'] ? HTTP_PROTOCOL.STATIC_URL.IMG_DIR.$item['photo'] : HTTP_PROTOCOL.STATIC_URL.IMG_DIR.IMG_NO);
            $config['title']     = $title;
            $config['qty']       = $order['cart'][$item['1c_id']]['qty'];
            $config['price']     = price::format($order['cart'][$item['1c_id']]['price']);
            $config['subtotal']  = price::format($subtotal);
            $cart .= $this->content->design('cc','order-list-table',$config);
         }
         $list['order_cart']  = $cart;
         $list['order_total'] = lang::TOTAL.price::format($total);
         $list['timeline']    = $this->orderTimeline($list['id']);

         // кнопки статусов заказа
         if ($order['status_next'] != null) {
            $status_next = explode('|',$order['status_next']);
            $next_btns = null;
            foreach ($status_next as $status) {
               $this->db->sql('SELECT name,name_next,icon,style FROM orders_status WHERE id = ' . $status);
               $next = $this->db->getResult();

               $next_tpl['id']      = $order['order_id'];
               $next_tpl['status']  = $status;
               //$next_tpl['title']   = $next[0]['name_next'];
               $next_tpl['title']   = $next[0]['name'];
               $next_tpl['icon']    = $next[0]['icon'];
               $next_tpl['style']   = $next[0]['style'];
               $next_btns .= $this->content->design('cc','btn-status',$next_tpl);
            }
         } else
            $next_btns = null;

         $list['btn-status']  = $next_btns;

         echo $this->content->design('cc','orders-list',$list);
      }

      //$this->__footer();
   }

   /**
   * Обновление прайс-листа (выгрузка)
   */
   public function priceupdate() {
      $pg['title'] = "Обновление прайс-листа";
      echo $this->content->design('cc','page-title',$pg);

      $params = new TimeParams();
      $params->format = 'd F Y года в H:i';
      $params->monthInflected = true;
      $params->date = counters::get('LAST_CATALOG_UPDATE');
      $mtime = RUtils::dt()->ruStrFTime($params);
      $config['last_price_update'] = $mtime;
      $config['uploader_version'] = self::UPLOADER_VERSION;

      echo $this->content->design('cc','price-update',$config);

      //$this->__footer();
   }

   /**
   * Загрузка фотографий для каталога
   */
   public function addphotos($params) {
      $pg['title'] = "Добавление фотографий товаров";
      echo $this->content->design('cc','page-title',$pg);

      $photo['item_photos'] = null;
      $photo['back_link'] = null;

      if ($params) {
         $photo['back_link'] = '<a href="/product/'.$params['id'].'"><i class="fa fa-arrow-left"></i> Назад</a>';
         $photo['item_id'] = $params['id'];
         $_SESSION['cc']['item_id'] = $params['id'];

         $sql = $this->db->select('images','*',NULL,'1c_id='.$params['id']);
         $res = $this->db->getResult();

         foreach ($res as $image) {
            $photo['item_photos'] .= '<img src="'.HTTP_PROTOCOL.STATIC_URL.IMG_DIR.$image['path_big'].'" />';
         }

      } else {
         echo 'Пустой запрос';
      }

      echo $this->content->design('cc','edit-photo',$photo);

      //$this->__footer();
   }

   /**
   * Timeline история заказа
   */
   private function orderTimeline($id) {
      $this->db->sql('
         SELECT
            orders_log.date,
            orders_log.note,
            orders_status.name,
            orders_status.icon,
            orders_status.style,
            users.firstname,
            users.lastname,
            users.avatar
         FROM orders_log
         LEFT JOIN
            orders_status
         ON
            orders_log.status = orders_status.id
         LEFT JOIN
            users
         ON
            orders_log.operator = users.id
         WHERE orders_log.order_id = "'.$id.'"
         ORDER BY orders_log.date
      ');
      $result = $this->db->getResult();

      $code = null;
      $invert = true;
      foreach ($result as $status) {

         // инвертируем положение блока
         $invert = !$invert;
         $status['inverted'] = ($invert ? ' class="timeline-inverted"' : null);

         // форматирование даты
         $params = new TimeParams();
         $params->format = 'd F Y года в <b>H:i</b>';
         $params->monthInflected = true;
         $params->date = $status['date'];
         $mtime = RUtils::dt()->ruStrFTime($params);
         $status['date'] = $mtime;

         // оператор
         $status['username'] = $status['firstname'].' '.$status['lastname'];
         $status['avatar']   = HTTP_PROTOCOL.STATIC_URL.AVATAR_PATH.$status['avatar'];

			// заметки
			$status['note'] = nl2br($status['note']);

         $code .= $this->content->design('cc/orders/timeline','timeline-element',$status);
      }

      return $code;
   }

}
