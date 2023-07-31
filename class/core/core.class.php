<?php

namespace core;

use \language\lang;
use \db\mysql\mysqlcrud;
use \template\template;

use \tmp\master;
use \core\counters;
use \auth\auth;
use \helper\git;
use \vendor\php_rutils\RUtils;
use \vendor\php_rutils\struct\TimeParams;
use \debug\dBug;

class core {
   // Не показываем footer в админке и на тестовых страницах
   const FOOTER_EXCLUDE = array('extauth','cc','bot','__test__test_');

  /**
   *
   * CORE
   *
   */
   function __construct() {
      error_reporting(E_ALL);
      ini_set('display_errors', 1);
      session_start();

      $this->db = new mysqlcrud();
      $this->db->connect();
      $this->content = new template();

      // Запускаем таймер загрузки страницы
      $this->start_time = microtime(TRUE);

      // Удаление товара из корзину
      if (isset($_POST['del'])) {
         unset($_SESSION['shoppingcart'][$_POST['del']['id']]);

         if (empty($_SESSION['shoppingcart']))
            unset($_SESSION['shoppingcart']);

         header('Location: '.$_SERVER['REQUEST_URI']);
      }

      // Проверка авторизации пользователя.
      //master::login();

      // Модуль и действие по-умолчанию
      $extantion     = ".php";
      $this->module  = 'homepage';
      $action        = 'index';
      // массив с параметрами, переданными в адресной строке через GET
      $this->params  = array();

      // разбираем переменные, переданные через адресную строку QUERY_STRING (доступ к: $_GET и $_REQUEST)
      $url_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

      // Если пользователь находится в корне сайта, то показываем начальную страницу
      if ($url_path != '/') {
      	try {
            // разбираем виртуальный адрес по символу "/"
      		$uri_parts = explode('/', trim($url_path, ' /'));

            if(!isset($uri_parts[1]))
               $uri_parts[1] = "index";

      		// если части URL не кратны 2, то выводим ошибку 404
      		if ((count($uri_parts) % 2))
               throw new \Exception();

      		$this->module = array_shift($uri_parts);   // получаем имя модуля
      		$action = array_shift($uri_parts);         // получаем имя действия

            if (!file_exists($this->module.".php"))
               throw new \Exception();

      		// параметры запроса раскладываем в массив $this->params
      		for ($i=0; $i < count($uri_parts); $i++) {
      			$this->params[$uri_parts[$i]] = $uri_parts[++$i];
      		}

            if ($this->module == 'category') {        // ГРУППЫ ТОВАРОВ
               if($action != 'index') {
                  $this->params['id'] = $action;
                  $action = 'show';
               }
            } elseif ($this->module == 'product') {   // КАРТОЧКА ТОВАРА
               $this->params['id'] = $action;
               $action = 'index';
            }

      	} catch (\Exception $e) {
      		$this->module = 'errors';
      		$action = 'error_404';
      	}
      }

      // Подключаем вызываемый модуль
      require $this->module.".php";
      $include = new $this->module();

      if (is_callable(array($include, $action))) {
         // и вызываем действие в модуле
         $include->$action($this->params);
      } else {
      	$include->index();
      }

   }

  /**
   *
   * DESTRUCTOR = FOOTER
   *
   */
   function __destruct() {
      if (in_array($this->module,self::FOOTER_EXCLUDE))
         die;

      $elements['list-element'] = $this->content->design('widget','list-element');
      //$footer_cfg['recomended-body'] = $this->content->design('widget','recomended-body',$elements);
      $footer_cfg['recomended-body'] = null;
      //$footer_cfg['on-sale-body'] = $this->content->design('widget','on-sale-body',$elements);
      $footer_cfg['on-sale-body'] = null;
      //$footer_cfg['top-rated-body'] = $this->content->design('widget','top-rated-body',$elements);
      $footer_cfg['top-rated-body'] = null;

      // Подключаем footer страницы
      $footer_cfg['iconset']   = 'round';
      $footer_cfg['icon_size'] = 32;
      $footer_cfg['effect']    = 'hvr-pulse-shrink'; // hvr-bounce-out тоже ничего

      $footer_cfg['quick_menu'] = null;

      $this->db->select('category','*',NULL,'lvl = 1 AND enable = 1');
      $sql = $this->db->getResult();
      foreach ($sql as $quick) {
         $footer_cfg['quick_menu'] .= '<li><a href="' . MENU_CAT_PATH . $quick['url'] . '/">' . $quick['name'] . '</a></li>';
      }

      // Последнее обновление цен в каталоге
      $params = new TimeParams();
      $params->format = 'd F Y года в H:i';
      $params->monthInflected = true;
      $params->date = counters::get('LAST_CATALOG_UPDATE');

      $footer_cfg['subscribe']    = lang::MAILING_SUBSCRIBE;
      $footer_cfg['gogogo']       = lang::GOGOGO;
      $footer_cfg['addr_descr']   = lang::FOOTER_ADDR_DESCRIPTION;
      $footer_cfg['homy_address'] = lang::SHOP_ADDRESS;
      $footer_cfg['homy_phone']   = lang::SHOP_PHONE;
      $footer_cfg['social_btns']  = lang::SOCIAL_BUTTONS;
      $footer_cfg['prod_catalog'] = lang::PRODUCTS_CATALOG;
      $footer_cfg['price_update'] = RUtils::dt()->ruStrFTime($params);

      // Время загрузки страницы : END
      $this->end_time = microtime(TRUE);
      $time_taken = $this->end_time - $this->start_time;
      $this->time_taken = round($time_taken,4);

      $footer_cfg['admin_inf'] = (auth::isAdmin() ? $this->adminCounters() : null);
      $footer_cfg['cp_year']   = "2014-" . date("Y");
      $footer_cfg['oferta']    = lang::FOOTER_OFERTA;

      $_SESSION['mysql_count'] = null;

      echo $this->content->design('index','footer',$footer_cfg);
   }

   /**
    *
    * Счетчики для администратора
    * 1) Номер версии магазина со ссылкой на последний GIT-коммит
    * 2) Время генерации страницы
    * 3) Число MySQL-запросов для генерации страницы
    *
    * @return   string  html code
    *
    */
   private function adminCounters() {
      $counter['git_version'] = @git::version();
      $counter['render_time'] = $this->time_taken;
      $counter['mysql_count'] = $_SESSION['mysql_count'];

      $return = $this->content->design('index','footer/counters',$counter);

      return $return;
   }

}
