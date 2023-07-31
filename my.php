<?php
/**
* Сборка страницы пользователя
* @version 2.0
*/

use \db\mysql\mysqlcrud;
use \template\template;
use \template\header;

use \tmp\master;
use \auth\auth;
use \vendor\php_rutils\RUtils;
use \vendor\php_rutils\struct\TimeParams;

if(!auth::isLogin())
   header("Location: /login/");

class my {

   public function __construct() {
      $this->db = new mysqlcrud();
      $this->db->connect();
      $this->content = new template();
   }

   /**
   * Начальная страница профиля пользователя
   */
   public function index() {
      // START
      // Задаем meta заголовки страницы
      $header['description'] = 'Профиль пользователя';
      $header['keywords'] = 'пользователь, профиль, user profile';
      $header['title'] = 'Мой профиль'.HEAD_TITLE_END;
      echo $this->content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
      $header = new header();
      $this->leftMenu();

      $this->db->select('users','*',NULL,'id='.$_SESSION['id']);
      $res = $this->db->getResult(); $res = $res[0];

      $usr_cfg['firstname']   = ($res['firstname'] != null ? $res['firstname'] : null);
      $usr_cfg['lastname']    = ($res['lastname'] != null ? $res['lastname'] : null);
      $usr_cfg['user_avatar'] = HTTP_PROTOCOL.STATIC_URL.AVATAR_PATH.$res['avatar'];
      echo $this->content->design('my','index',$usr_cfg);

      echo $this->content->design('my','bottom');
   }

  /**
   *
   * Страница с заказами пользователя
   *
   *
   */
   public function orders() {
      // START
      // Задаем meta заголовки страницы
      $header['description'] = 'Список заказов пользователя';
      $header['keywords'] = 'пользователь, заказы';
      $header['title'] = 'Мои заказы'.HEAD_TITLE_END;
      echo $this->content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
      $header = new header();
      $this->leftMenu();

      $this->db->select('orders','*',NULL,'user='.$_SESSION['id'],'date_post DESC',NULL);
      $res = $this->db->getResult();

      $orders['orders_list'] = null;
      foreach ($res as $key => $val) {
         // Номер заказа
         @$order['id']   = $val['order_id'];

         // Статус заказа
         $order['status'] = master::orderStatus($val['status']);

         // Дата заказа
         $params = new TimeParams();
         $params->format = 'd F Y года в H:i';
         $params->monthInflected = true;
         $params->date = $val['date_post'];
         $order['date'] = RUtils::dt()->ruStrFTime($params);

         // Кол-во позиций в заказе
         $variants = array('позиция','позиции','позиций');
         $cart_count = count(json_decode($val['cart'],true));
         $order['cart_count'] = $cart_count . ' ' . RUtils::numeral()->choosePlural($cart_count, $variants);

         $orders['orders_list'] .= $this->content->design('my','orders-list',$order);
      }

      echo $this->content->design('my','orders',$orders);
      echo $this->content->design('my','bottom');
   }
  /**
   *
   * Страница смены пароля пользователя
   *
   *
   */
   public function pass($params) {
      // START
      // Задаем meta заголовки страницы
      $header['description'] = 'Смена пароля пользователя';
      $header['keywords'] = 'пользователь, пароль';
      $header['title'] = 'Смена пароля'.HEAD_TITLE_END;
      echo $this->content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
      $header = new header();
      $this->leftMenu();

      // Если данные отправлены из формы
      if ($_POST) {
         $pass['old'] = $_POST['old_pass'];
         $pass['new'] = $_POST['new_pass'];
         $pass['chk'] = $_POST['new_pass_check'];
         $type     = "error";
         $title    = "Ошибка";
         $redirect = "/my/pass/";

         // если все поля не пусты
         if ($pass['old'] != "" && $pass['new'] != "" && $pass['chk'] != "") {
            // новый пароль и его проверка совпадают
            if ($pass['new'] == $pass['chk']) {
               // селектим данные о пользователе из бд
               $this->db->select('users','*',NULL,'id='.$_SESSION['id'],NULL,NULL,1);
               $res = $this->db->getResult();

               // если селект прошел
               if (count($res) == 1) {
                  $row = $res[0];
                  // если старый пароль из формы и текущий пароль из базы совпадают
                  if(md5(md5($pass['old']).$row['salt']) == $row['pass']) {
                     $update['pass'] = md5(md5($pass['new']).$row['salt']);
                     $this->db->update('users',$update,'id='.$_SESSION['id']);

                     $type     = "success";
                     $title    = "Поздравляем!";
                     $message  = "Ваш пароль успешно изменен.";
                     $redirect = "/my/";
                  } else
                     $message = "Ваш текущий пароль и пароль в форме не совпадают. Возможно неверная раскладка клавиатуры или нажата клавиша <kbd>Caps Lock</kbd>";
               }
            } else
               $message = "Поля с новым паролем должны быть идентичны. Убедитесь, что вводите один и тот же пароль в оба поля.";
         } else
            $message = "Вы не заполнили все поля в форме смены пароля. Попробуйте еще раз, у Вас обязательно все получится.";

         $modal_cfg['type']     = $type;
         $modal_cfg['title']    = $title;
         $modal_cfg['message']  = $message;
         $modal_cfg['redirect'] = $redirect;
         echo $this->content->design('modal','redirect',$modal_cfg);
      }

      echo $this->content->design('my','pass');
      echo $this->content->design('my','bottom');
   }

   private function leftMenu() {
      $lft['user_avatar']  = master::get_user_info("avatar");

      // Кол-во заказов пользователя
      $this->db->select('orders','*',NULL,'user='.$_SESSION['id'],NULL,NULL);
      $res = $this->db->getResult();
      $lft['count_orders'] = count($res);

      echo $this->content->design('my','left-menu',$lft);
   }

}
