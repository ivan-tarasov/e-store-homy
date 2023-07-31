<?php

namespace tmp;

use \db\mysql\mysqlcrud;
use \template\template;

use \tmp\nestedtree;
use \helper\date;
use \auth\auth;
use \helper\price;
use \product\prod;
use \debug\dBug;

/**
 *
 * Класс с основными функциями (требует переработки)
 *
 * @method  void     log(string $script, string $msg)  - лог выполнения скриптов (beta)
 * @method  string   gitversion()                      - ревизия версии git
 * @method  string   redirect(string $url, int $time)  - перенаправление пользователя на $url через $time
 * @method  bool     login()                           - выставление и продление cookie для пользователя
 * @method  bool     is_login()                        - проверка авторизации пользователя
 * @method  void     last_active(int $id)              - обновление информации о последней активности пользователя c ID
 * @method  string   get_user_info(string $field)      - получение поля $field из таблицы с пользователями
 * @method  bool     is_admin()                        - проверка пользователя на административные права
 * @method  array    brandInfo(int $brand_id)          - получение информации о бренда по ID
 * @method  string   priceFormat(int $price)           - форматирование цены товара
 *
 * @author Ivan Karapuzoff <ivan@karapuzoff.net>
 * @version 1.2
 *
 */

class master {
   static function curl($url) {
      if ($curl = curl_init()) {
         curl_setopt($curl, CURLOPT_URL, $url);
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
         $out = curl_exec($curl);
         $info = curl_getinfo($curl);

         // проверка кода ответа сервера
         if ($info['http_code']) {
            $db = new mysqlcrud();
            $db->connect();

            $db->sql('
               INSERT INTO logs
                  (title,details)
               VALUES
                  ("Код '.$info['http_code'].'","URL: '.$url.'")
            ');
         }

         curl_close($curl);
         return $out;
      } else
         return false;
   }
   /**
   * Форматирование сообщений
   */
   /*static function notification($notif) {
      $content = new template();

      $return = $content->design('auth','notification',$notif);
      return $return;
   }/**/

  /**
   *
   * Номер ревизии git и номер коммита
   *
   * @uses master::gitversion() для получения текущей версии git и ссылки на последний коммит
   * @return string номер версии
   *
   * @version 1.0
   *
   */
   static function gitversion() {
      exec('git describe --long',$git_commit);
      $git_commit = explode('-',$git_commit[0]);
      $version = '<abbr title="git commit #'.substr($git_commit[2],1).'"><a href="http://bitbucket.org/homysu/homy.su/commits/'.substr($git_commit[2],1).'" target="_blank">v.'.$git_commit[0].'</a></abbr>';
   	return $version;
   }

  /**
   *
   * Перенаправление пользователя на указанный URL с выводом счетчика оставшегося времени
   *
   * @uses master::redirect(URI,TIME) для перенаправления пользователя на URI через TIME
   * @return string javascript-код для перенаправления
   *
   * @version 1.0
   *
   */
   static function redirect($url,$time=5) {
      $content = new template();

      $timer['redirect_timer'] = $time;
      $timer['redirect_url'] = $url;
      $code = $content->design('javascript','redirect',$timer);

      return $code;
   }

   /*static function now($format = 0) {
      $now = date('Y-m-d G:i:s');

      switch ($format) {
         case 0:
            $now = $now;
            break;
         case 1:
            $now = '[' . $now . ']';
            break;
      }

      return $now;
   }/**/

   /*static function phoneFormat($phone,$reverce = false) {
      if (!$reverce) {
         $return = preg_replace('/[^0-9]/i','', $phone);
         $return = (strlen($return) < 11 ? '7'.$return : $return);
      } else {
         $return  = "+" .substr($phone,0,1);
         $return .= " (".substr($phone,1,3);
         $return .= ") ".substr($phone,4,3);
         $return .= "-" .substr($phone,7);
      }

      return $return;
   }/**/

   /*static function phoneValidate($phone) {
      $phone = self::phoneFormat($phone);

      $chk_1 = (strlen($phone) == 11 ? true : false);
      $chk_2 = (preg_match("/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i", $phone) == 1 ? true : false);

      return ($chk_1 && $chk_2 ? true : false);
   }/**/

  /**
   * Регистрация нового пользователя
   */
   /*static function registration($phone, $fio=false) {
      $db = new mysqlcrud();
      $db->connect();

      if (self::is_login()) {
         $return['ok'] = 1;
         $return['userid'] = $_SESSION['id'];
         return $return;
         exit;
      }

      $phone = self::phoneFormat($phone);

      $return['ok'] = 0;

      if (self::phoneValidate($phone)) {

         $db->select('users','*',NULL,'phone="'.$phone.'"',NULL,NULL,1);
         $res = $db->getResult();

         if (count($res) < 1) {
            $pass = self::passwordGen();

            $insert['phone'] = $phone;
            $insert['date_reg'] = self::now();
            $insert['salt'] = mt_rand(100, 999);
            $insert['pass'] = md5(md5($pass).$insert['salt']);

            if ($fio) {
               $insert['firstname'] = $fio['firstname'];
               $insert['lastname'] = $fio['lastname'];
               if (!empty($fio['middlename'])) {
                  $insert['middlename'] = $fio['middlename'];
               }
            }

            $db->insert('users',$insert);
            $res = $db->getResult(); $userid = $res[0];
            $return['userid'] = $userid;

            $sms = urlencode('Мы рады, что Вы теперь с нами! Временный пароль: ' . $pass);
            $return['notif'] = array(
               'class' => 'success',
               'title' => 'Временный пароль отправлен.',
               'text'  => 'В скором времени Вы получите смс сообщение с временным паролем. Если сообщение не приходит в течение нескольких минут, то <a href="/login/" class="alert-link">повторите попытку регистрации</a>.'
            );
            $return['ok'] = 1;
            $body = file_get_contents('http://sms.ru/sms/send?api_id='.SMSRU_API_ID.'&to='.$phone.'&text='.$sms);
         } else {
            $return['notif'] = array(
               'class' => 'danger',
               'title' => 'Ошибка',
               'text'  => 'Данный номер телефона уже связан с аккаунтом в нашем магазине, попробуйте авторизоваться.'
            );
         }
      } else {
         $return['notif'] = array(
            'class' => 'danger',
            'title' => 'Ошибка',
            'text'  => 'Формат номера не верен.'
         );
      }

      $message = self::notification($return['notif']);
      $return['message'] = $message;

      return $return;
   }/**/

   static function orderStatus($status) {
      /*switch ($status) {
         case 0:
            $arr = array('#999','clock-o','Проверяется'); break;
         case 1:
            $arr = array('#008080','money','Оплачен онлайн'); break;
         case 2:
            $arr = array('#5bc0de','file-text-o','Заказ в работе'); break;
         case 3:
            $arr = array('#337ab7','truck','Доставляется'); break;
         case 4:
         case 8:
            $arr = array('#d9534f','times','Заказ отменен'); break;
         case 9:
            $arr = array('#5cb85c','check','Заказ исполнен'); break;
         default:
            echo "def";
      }/**/

      $template = '<span class="label" style="background-color: #5bc0de"><i class="fa fa-lg fa-file-text-o"></i> Заказ в работе</span>';
      //$status = vsprintf($template, $arr);

      return $template;
   }

  /**
   *
   * Проверка и обновление авторизации пользователя
   *
   * Проверяет наличие параметра ID в текущей сессии пользователя. Если ID найден, то проверяет наличие у пользователя cookie
   * с параметрами LOGIN и HASH. Обновляет куки пользователя, прибавляя к текущему времени 14 часов. Если куки не найдены, то
   * выдает новые.
   * Если ID пользователя нет в сессии, то проверяет cookie пользователя. Если cookie найдены, то проверяет правильность хэша
   * в cookie и при совпадении регистрируем сессию и обновляем куки.
   *
   * @uses master::login() для проверки и обновления авторизации
   * @return bool авторизован пользователь или нет
   *
   * @version 1.0
   *
   */
   static function login() {
      $db = new mysqlcrud();
      $db->connect();

      //ini_set ("session.use_trans_sid", true);
      //session_start();

      if (isset($_SESSION['id'])) {
         if(isset($_COOKIE['login']) && isset($_COOKIE['hash'])) {

            // убиваем куки
            setcookie("login", "", time() - 1, '/','.'.HTTP_HOST);
            setcookie("hash","", time() - 1, '/','.'.HTTP_HOST);
            // ставим новые
            setcookie("login", $_COOKIE['login'], time() + 50000, '/','.'.HTTP_HOST);
            setcookie("hash", $_COOKIE['hash'], time() + 50000, '/','.'.HTTP_HOST);

            $id = $_SESSION['id'];
            self::last_active($id);
            return true;
         } else {
            $rez = $db->select('users','*',NULL,'id="'.$_SESSION['id'].'"',NULL,NULL,1);
            if (count($rez) == 1) {
               $row = $rez[0];
               setcookie("login", $row['login'], time()+50000, '/','.'.HTTP_HOST);
               setcookie("hash", md5($row['login'].$row['pass']), time()+50000, '/','.'.HTTP_HOST);

               $id = $_SESSION['id'];
               self::last_active($id);
               return true;
            } else
               return false;
         }
      } else { //если сессии нет, то проверим существование cookie. Если они существуют, то проверим их валидность по БД
         if (isset($_COOKIE['login']) && isset($_COOKIE['hash'])) { //если куки существуют.
            $rez = $db->select('users','*',NULL,'login="'.$_COOKIE['login'].'"',NULL,NULL,1);
            @$row = $rez[0];

            if (@count($rez) == 1 && md5($row['login'].$row['pass']) == $_COOKIE['hash']) {
               $_SESSION['id'] = $row['id'];
               $id = $_SESSION['id'];

               self::last_active($id);
               return true;
            } else {
               setcookie("login", "", time() - 360000, '/','.'.HTTP_HOST);
               setcookie("hash", "", time() - 360000, '/','.'.HTTP_HOST);
               return false;
            }
         } else { //если куки не существуют
            return false;
         }
      }
   }

  /**
   *
   * Проверяет авторизацию пользователя
   *
   * @uses master::is_login() для проверки
   * @return bool авторизован TRUE или нет FALSE
   *
   * @version 1.0
   *
   */
   /*static function is_login() {
      if(isset($_SESSION['id']))
         return true;
      else
         return false;
   }/**/

  /**
   *
   * Обновление информации с последнего визита пользователя
   *
   * @uses master::last_active(ID) для внесения информации о пользователе ID в БД
   *
   * @version 1.0
   *
   */
   static function last_active($id) {
      $db = new mysqlcrud();
      $db->connect();

      $db->update('users',array('date_act'=>date::now()),'id="' . $id . '"');
   }

  /**
   *
   * Информация о пользователе
   *
   * @uses master::get_user_info(FIELD) для получения значения FIELD из таблицы с информацией о пользователе в БД
   * @return string значение поля FIELD
   *
   * @version 1.0
   *
   */
   static function get_user_info($field) {
      $db = new mysqlcrud();
      $db->connect();

      @$db->select('users',$field,NULL,'id="'.$_SESSION['id'].'"',NULL,NULL,1);
      $res = $db->getResult();

      @$info =  $res[0][$field];

      return $info;
   }

  /**
   *
   * Проверка прав пользователя
   *
   * @uses master::is_admin() для проверки прав пользователя
   * @return bool пользователь с правами администратора TRUE или обычный FALSE
   *
   * @version 1.0
   *
   */
   /*static function is_admin() {
      $db = new mysqlcrud();
      $db->connect();

      @$db->select('users','permissions',NULL,'id="'.$_SESSION['id'].'"',NULL,NULL,1);
      $res = $db->getResult();

      if (count($res) == 1) {
         $prava = $res[0]['permissions'];

         if ($prava == 1)
            return true;
         else
            return false;
      } else
         return false;
   }/**/

  /**
   *
   * Получаем информацию о бренде по ID
   *
   * @uses master::brandInfo(32)
   * @return array информация о бренде
   *
   * @version 1.0
   *
   */
   static function brandInfo($brand_id) {
      $db = new mysqlcrud();
		$db->connect();

      $db->sql('SELECT * FROM brands WHERE brand_id = '.$brand_id);
      $result = $db->getResult();
      //new dBug($db->getSql());
      return $result[0];
   }

  /**
   *
   * Получаем информацию о бренде по ID
   *
   * @uses master::brandInfo(32)
   * @return array информация о бренде
   *
   * @version 1.0
   *
   */
   static function catInfo($cat_id) {
      $db = new mysqlcrud();
		$db->connect();

      $db->select('category','*',NULL,'cat_id=' . $cat_id);
      $result = $db->getResult();
      //new dBug($db->getSql());
      return @$result[0];
   }

   /**
   * Форматирование цены товара
   *
   * @uses price::format(34455)
   * @return string форматированная цена
   *
   * @version 1.0
   */
   /*static function priceFormat($price) {
      if ($price != 0)
         $price = number_format($price, 0, ',', ' ') . CURRENCY;
      else
         $price = 'б/п<sup>*</sup>';

      return $price;
   }/**/

  /**
   *
   * Формирование пути до карточки товара
   *
   * @uses master::prodURL(34455)
   * @return string путь до изображения
   *
   * @version 2.0
   *
   */
   static function prodURL($id,$name) {
      $delimiter = '-';

      $link = \helper\translit::translify($name);
      $link = iconv('UTF-8', 'ASCII//TRANSLIT', $link);
      $link = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $link);
      $link = strtolower(trim($link, '-'));
      $link = preg_replace("/[\/_|+ -]+/", $delimiter, $link);
      $link = '/product/'.$id.'-'.$link.'/';

      return $link;
   }

  /**
   *
   * Поиск пути до главного изображения товара
   *
   * @uses master::photoURL(34455)
   * @return string путь до изображения
   *
   * @version 1.0
   *
   */
   static function photoURL($id,$type = 'big') {
      $img_path = HTTP_PROTOCOL.STATIC_URL.IMG_DIR . intval($id / 1000) . '/' . $id . '/' . $type . '/';
      $img_real = realpath($_SERVER['DOCUMENT_ROOT'] . '/' . $img_path);
      $img_file = glob($img_real . '/*.{jpg,JPG,jpeg,JPEG,png,PNG}',GLOB_BRACE);
      @sort($img_file, SORT_STRING);

      if($img_file) {
         foreach ($img_file as $photo) {
            $content = $img_path . basename($photo);
            break;
         }
      } else
         $content = HTTP_PROTOCOL.STATIC_URL.IMG_DIR.IMG_NO;

      return $content;
   }

  /**
   *
   * Организация постраничного вывода позиций в категориях
   *
   * @uses master::pagination(URL,QUERY_STRING,CURRENT_PAGE,PAGES)
   * @return string HTML код навигации
   *
   * @version 1.0
   *
   */
   static function pagination($base_url, $query_str, $total_pages, $current_page, $paginate_limit = 1) {
      // Array to store page link list
      $page_array = array ();

      // Show dots flag - where to show dots?
      $dotshow = true;

      if ($current_page != 1) {
         $next_i = 2;
         $page_array[0]['url'] = $base_url . "?" . $query_str . "=" . ($current_page - 1);
         $page_array[0]['text'] = '<i class="fa fa-long-arrow-left"></i>';
      }

      // walk through the list of pages
      for ( $i = 1; $i <= $total_pages; $i++ ) {
         // If first or last page or the page number falls
         // within the pagination limit
         // generate the links for these pages
         if ($i == 1 || $i == $total_pages || ($i >= $current_page - $paginate_limit && $i <= $current_page + $paginate_limit)) {
            // reset the show dots flag
            $dotshow = true;
            // If it's the current page, leave out the link
            // otherwise set a URL field also
            if ($i != $current_page)
               $page_array[$i]['url'] = $base_url . "?" . $query_str . "=" . $i;

            $page_array[$i]['text'] = strval ($i);
         }

         // If ellipses dots are to be displayed
         // (page navigation skipped)
         elseif ($dotshow == true) {
            // set it to false, so that more than one
            // set of ellipses is not displayed
            $dotshow = false;
            $page_array[$i]['disabled'] = true;
            $page_array[$i]['text'] = '...';
         }
      }

      if ($current_page != $total_pages) {
         $page_array[$i + 1]['url'] = $base_url . "?" . $query_str . "=" . ($current_page + 1);
         $page_array[$i + 1]['text'] = '<i class="fa fa-long-arrow-right"></i>';
      }

      $return = '<ul class="pagination">';

      foreach ($page_array as $page) {
         if (isset ($page['url'])) {
            $return .= '<li><a href="' . $page['url'] . '">' . $page['text'] . '</a></li>';
         } else {
            if (isset($page['disabled']))
               $return .= '<li><a href="#" class="btn disabled">' . $page['text'] . '</a></li>';
            else
               $return .= '<li class="current"><a href="#">' . $page['text'] . '</a></li>';
         }
      }
      $return .= "</ul>";

      return $return;
   }

  /**
   *
   * "Хлебные крошки" для быстрого доступа в дереве категорий
   *
   * @uses master::breadcrumbs(CAT_ID,ITEM_NAME)
   * @return string HTML код
   *
   * @version 1.0
   *
   */
   static function breadcrumbs($url = false,$item = false) {
      $content = new template();

      $breadcrumb = nestedtree::single_path($url);
      //new dBug($breadcrumb);
      $bread_items = null;

      foreach ($breadcrumb as $k => $v) {
         $current = null;
         if (!$item)
            $current = ($v['url'] == $url ? ' current' : null);

         if ($k == 0) {
            $menu_name = '<i class="fa fa-' . BREAD_ICON . '"></i>';
         } else {
            $menu_name = $v['name'];
            $v['url'] .= '/';
         }

         $bread_items .= '<li class="breadcrumb-item' . $current . '"><a href="' . MENU_CAT_PATH . $v['url'] . '">' . $menu_name . '</a></li>';
      }

      /*if($item)
         $bread_items .= '<li class="breadcrumb-item current"><a href="#">' . $item . '</a></li>';/**/

      $breadcrumb['bread_items'] = $bread_items;

      $breadcrumb = $content->design('index','header/breadcrumb',$breadcrumb);

      return $breadcrumb;
   }

   static function findKey($keyword, $array) {
      if (array_key_exists($keyword, $array)) {
         return true;
      }
      foreach ($array as $k => $v) {
         if (!is_array($v)) {
            continue;
         } elseif (array_key_exists($keyword, $v)) {
            //new dBug($v['model']);
            return $v['model'];
         }
      }
      return false;
   }

   static function shoppingCart($limit = null) {
      $db = new mysqlcrud();
      $db->connect();

      //$limit = ($limit != null ? " LIMIT " . $limit : null);

      $ids = join(',',array_keys($_SESSION['shoppingcart']));
      $db->sql('
               SELECT
                  catalog.1c_id,
                  brands.brand_clean AS brand,
                  brands.brand_lat,
                  catalog.name,
                  catalog.price,
                  images.path_thumbnail,
                  category.shipping_cost
               FROM catalog
               LEFT JOIN
                  brands
               ON
                  catalog.brand_id = brands.brand_id
               LEFT JOIN
                  images
               ON
                  catalog.1c_id = images.1c_id AND images.onmain = 1
               LEFT JOIN
                  category
               ON
                  catalog.cat_id = category.cat_id
               WHERE catalog.1c_id IN ('.$ids.')
               ORDER BY FIELD(catalog.1c_id, '.$ids.')
               ');
      $sql = $db->getResult();
      //echo $db->getSql();

      return $sql;
   }

   static function inCart($id, $size = 'huge') {
		$db = new mysqlcrud();
      $db->connect();

		$size = ' ' . $size;

      if (isset($_SESSION['shoppingcart']) && array_key_exists($id, $_SESSION['shoppingcart'])) {
			$code = '<button class="le-button'.$size.' incart" onclick="javascript:window.location.href=\'/cart/\'; return false;">В корзине</button>';
      } else {
         $class = null;
         $status = null;
         $text = 'В корзину';

			$db->sql('
	         SELECT
	            catalog.cat_id,
					catalog.name,
					catalog.price,
					brands.brand_clean AS brand
	         FROM catalog
				LEFT JOIN
					brands
				ON
					catalog.brand_id = brands.brand_id
	         WHERE
	            1c_id = '.$id
	      );
			$product = $db->getResult();

			$data = $product[0];
			$data['brand']		= mb_strtoupper($data['brand'], 'utf-8');
			$data['name']		= $data['brand'] . ' ' . $data['name'];
			$data['category'] = prod::categoryPath($data['cat_id']);
			//new dBug($data);

	      $code = '<button id="addtocart-'.$id.'" class="le-button'.$size.$class.'"  data-options=\'{"action":"addtocart","id":'.$id.',"name":"'.$data['name'].'","price":'.$data['price'].',"brand":"'.$data['brand'].'","category":"'.$data['category'].'"}\''.$status.'>'.$text.'</button>';
      }

      return $code;
   }

   /*static function inCartClc($id = 0, $outOfStock = false, $size = 'huge') {
      $content = new template();

      $size = ' ' . $size;

      $class  = null;
      $status = null;
      $buy    = ($outOfStock ? 'Заказать' : 'Купить');
      $text   = $buy.' в 1 клик';

      $tpl['id'] = $id;
      $tpl['size'] = $size;
      $tpl['class'] = $class;
      $tpl['text'] = $text;
      $tpl['terms-personal'] = self::termsPersonal('Оформить заказ');

      $code = $content->design('modal','buy-clc',$tpl);
      //$code .= '<button id="buy-clc-'.$id.'" class="le-button'.$size.$class.' hvr-icon-buzz-out" data-toggle="modal" data-target="#buy-clc">'.$text.'</button>';
      #$code = '<a href="#" id="addto-cart" class="le-button'.$size.$class.'" data-options=\'{"id":'.$id.'}\''.$status.'>'.$text.'</a>';
      #$code = '<a class="plus" href="#addtocart" id="addto-cart" data-options=\'{"id":'.$id.'\'></a>';

      return $code;
   }/**/

   static function inFav($id, $size = 'huge') {
      $text = 'В избранное';
      $icon = 'heart';
      $text_gen = '<i class="fa fa-'.$icon.'"></i> ' . $text;

      if (auth::isLogin()) {
         $code = '<button id="addtofav-'.$id.'" class="le-button '.$size.'"  data-options=\'{"action":"addtofav","id":'.$id.'}\'>'.$text_gen.'</button>';
      } else {
         $code = '<a href="/login" class="le-button '.$size.'">'.$text_gen.'</a>';
      }

      return $code;
   }

   /*static function passwordGen($number = PASSWORD_LENGHT) {
      $arr = array(
               'a','b','c','d','e','f',
               'g','h','i','j','k','m',
               'n','o','p','r','s','t',
               'u','v','x','y','z',
               'A','B','C','D','E','F',
               'G','H','J','K','M','N',
               'P','R','S','T','U','V',
               'X','Y','Z',
               '1','2','3','4','5','6',
               '7','8','9'
            );

      $pass = "";
      for($i = 0; $i < $number; $i++) {
         $index = rand(0, count($arr) - 1);
         $pass .= $arr[$index];
      }

      return $pass;
   }/**/

   /*static function termsPersonal($button) {
      $content = new template();

      $templ['button'] = $button;

      $return = $content->design('master','terms-personal',$templ);
      return $return;
   }/**/

   /*static function itemsCarousel($cat_id,$title,$array) {
      $content = new template();
      $PROD = new \product\prod();

      $catinfo = self::catInfo($cat_id);
      //new dBug($catinfo);

      $conf['cat_id'] = $cat_id;
      $conf['cat_url'] = MENU_CAT_PATH . $catinfo['url'] . '/';
      $conf['title']  = $title;
      $conf['items']  = null;
      foreach ($array as $subcat) {
         //new dBug($subcat);

         //$item['name']  = mb_strtoupper($subcat['brand'], 'utf-8') . ' ' . $subcat['name'];
         $item['name']  = $PROD::title($subcat['name'],mb_strtoupper($subcat['brand'], 'utf-8'));
         $item['brand'] = $subcat['brand'];
         $item['price'] = price::format($subcat['price']);
         //$item['photo'] = self::photoURL($subcat['1c_id']);
         $item['photo'] = IMG_DIR.$subcat['path_big'];
         $item['url']   = self::prodURL($subcat['1c_id'],$subcat['brand_lat'] . ' ' . $subcat['name']);
         $item['to_cart_button'] = self::inCart($subcat['1c_id'],'small');

         $conf['items'] .= $content->design('category','carousel/element',$item);
      }

      $return = $content->design('category','carousel/grid',$conf);

      return $return;
   }/**/

   static function counterUpdate($counter,$type = 0,$value) {
      $db = new mysqlcrud();
      $db->connect();

      $counter = $db->escapeString($counter);
      $value   = $db->escapeString($value);

      switch ($type) {
         case 0:
            $type = 'val_int';
            break;
         case 1:
            $type = 'val_datetime';
            break;
         default:
            return false;
      }

      $sql = $db->sql('
            INSERT INTO counters
               (counter,'.$type.')
            VALUES
               ("'.$counter.'","'.$value.'")
            ON DUPLICATE KEY UPDATE
               '.$type.' = "'.$value.'"
         ');

      if($sql)
         return true;
      else
         return false;
   }

   /*static function orderID() {
      $order = strtoupper(md5(uniqid(rand(),true)));
      $order = date('Y-md') . substr($order,0,6);

      return $order;
   }/**/

   /*static function prestring($str) {
      $str = trim($str);                                             // удаляем ненужные символы из начала и конца строки
      $str = htmlspecialchars($str, ENT_QUOTES);                     // преобразуем специальные символы в HTML-сущности

      return $str;
   }

   static function truncate($string, $length = 18, $dots = "...") {
      //return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
      return mb_substr($string,0,$length) . $dots;
   }/**/

}
