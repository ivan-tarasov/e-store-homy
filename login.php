<?php
/**
* Индексная страница с формами входа на сайт и регистрации
* @return   void
*/

use \language\lang;
use \db\mysql\mysqlcrud;
use \template\template;
use \template\header;
use \template\terms;

use \tmp\master;
use \auth\auth;
use \auth\extauth;

class login {

   public function index() {
      if(auth::isLogin())
         header("Location: /my/");

      $content  = new template();

      // Задаем meta заголовки страницы
      $header['description'] = 'Страница с формой для входа существующих и регистрации новых пользователей.';
      $header['keywords'] = 'вход, регистрация, новый пользователь';
      $header['title'] = 'Вход и регистрация'.HEAD_TITLE_END;
      echo $content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
      $header = new header();

      $extauth = new extauth();
      $tpl['extauth'] = $extauth->buttons('vk,fb,google');
      $tpl['terms-personal'] = terms::personal('Зарегистрироваться');

		// START
      echo $content->design('auth','index',$tpl);
   }

  /**
   *
   * Регистрация нового пользователя
   *
   * @return   void
   *
   */
   public function registration() {
      $content  = new template();
      $db = new mysqlcrud();
      $db->connect();

      $msg = '';

      if (!empty($_POST['email']) && isset($_POST['email'])) {
         $email = $db->escapeString($_POST['email']);

         // check email by regex
         $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/';
         if (preg_match($regex, $email)) {
            $activation = md5($email.time()); // encrypted email+timestamp

            // проверка адреса электронной почты
            $db->select('users','id,login',NULL,'email="'.$email.'"',NULL,NULL,1);
         	$res = $db->getResult();
         	if (count($res) < 1) { // if we not found any entryes
               // prepare array for insert
               $insert['email'] = $email;
               $insert['date_reg'] = time();
               $insert['activation'] = $activation;

               // insert user data into db
               $db->insert('users',$insert);  // Table name, column names and respective values
               $res = $db->getResult();

               // отправка письма на электронный ящик
         		$configs['title']           = 'Активация аккаунта';
         		$configs['body']            = 'Здравствуйте!<br/><br/>Мы должны убедиться в том, что вы человек. Пожалуйста, подтвердите адрес вашей электронной почты, и можете начать использовать ваш аккаунт в магазине.';
         		$configs['activation_link'] = 'http://' . $_SERVER['SERVER_NAME'] . '/login/activation/code/'.$activation;

         		//$objMail = new sendmail();
               $objMail = new sendmailSMTP(SMTP_LOGIN,SMTP_PASS,SMTP_HOST,SMTP_SENDER,SMTP_PORT);
               $body = $content->design('mail','header') . $content->design('mail','confirm_email',$configs) . $content->design('mail','footer');
               $objMail->send($email,'Регистрация в магазине',$body);

               $msg = 'Регистрация выполнена успешно!<br />На указанный Вами адрес электронной почты (<b>' . $email . '</b>) мы отправили сообщение, в котором содержаться инструкции по активации аккаунта.';
            } else {
               $msg = 'Данный адрес электронный почты уже занят, пожалуйста, введите другой.';
            }
         } else {
            $msg = 'Адрес, введенный Вами, неверен. Пожалуйста, пороверьте правильно ли Вы написали его.';
         }
      } else {
         $msg = 'Вы не указали адрес электронной почты в поле регистрации.';
      }

      // START
      // Задаем meta заголовки страницы
      $header['description'] = 'Регистрация новых пользователей';
      $header['keywords'] = 'регистрация, новый пользователь';
      $header['title'] = 'Регистрация нового пользователя (Шаг 1)';
      echo $content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
      $header = new header();

      $reg_cfg['msg_title'] = "Регистрация нового пользователя (Шаг 1)";
      $reg_cfg['msg_body'] = $msg;
      $reg_cfg['msg_redirect_timer'] = master::redirect('/login/');

      echo $content->design('login','message',$reg_cfg);
   }

  /**
   *
   * Активация аккаунта по коду, присланному в email
   *
   * @return   void
   *
   */
   public function activation($vars) {
      $content = new template();
      $db = new mysqlcrud();
      $db->connect();

      $msg = '';

      if(!empty($vars['code']) && isset($vars['code'])) {
         $code = $db->escapeString($vars['code']);

         $db->select('users','id',NULL,'activation="'.$code.'"',NULL,NULL,1);
         $res = $db->getResult();
         if (count($res) > 0) { // if we not found any entryes
            $db->select('users','*',NULL,'activation="'.$code.'" and status="0"',NULL,NULL,1);
            $res = $db->getResult();
            $row = $res[0];
            if (count($res) > 0) { // if we not found any entryes
               // генерируем временный пароль
               $pass = generate::pin(6);
               $update['salt'] = generate::pin(3);;
               $update['pass'] = md5(md5($pass).$update['salt']);

               // получаем временный логин из email пользователя
               $login = explode("@",$row['email']);
               $update['login'] = $login[0];

               $update['status']    = 1;
               $db->update('users',$update,'activation="'.$code.'"');
               master::last_active($row['id']);

               $configs['title'] = 'Добро пожаловать!';
               $configs['body']  = 'Мы рады, что Вы теперь с нами!<br/><br/>Ниже указаны данные для входа в аккаунт.';
               $configs['login'] = $row['email'];
               $configs['pass']  = $pass;

               $objMail = new sendmailSMTP(SMTP_LOGIN,SMTP_PASS,SMTP_HOST,SMTP_SENDER,SMTP_PORT);
               $body = $content->design('mail','header') . $content->design('mail','temp_pass',$configs) . $content->design('mail','footer');
               $objMail->send($row['email'],'Завершение регистрации на homy.su',$body);

               $msg="Ваш аккаунт активирован";
            } else {
               $msg ="Ваш аккаунт уже активирован, нет необходимости активировать его снова.";
            }
         } else {
            $msg ="Неверный код активации.";
         }
      }

      // START
      // Задаем meta заголовки страницы
      $header['description'] = 'Активация учетной записи пользователя';
      $header['keywords'] = 'активация, регистрация, новый пользователь';
      $header['title'] = 'Активация учетной записи (Шаг 2)';
      echo $content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
      $header = new header();

      $act_cfg['msg_title'] = "Активация учетной записи (Шаг 2)";
      $act_cfg['msg_body'] = $msg;
      $act_cfg['msg_redirect_timer'] = master::redirect('/');

      echo $content->design('login','message',$act_cfg);
   }

  /**
   *
   * Авторизация существующего пользователя
   *
   * @return   void
   *
   */
   public function auth() {
      $content  = new template();
      $db = new mysqlcrud();
      $db->connect();

      if ($_POST['login'] != "" && $_POST['pass'] != "") {
         $login = $db->escapeString($_POST['login']);
         $pass = $_POST['pass'];

         $db->select('users','*',NULL,'email="' . $login . '" AND status = "1"',NULL,NULL,1);
      	$res = $db->getResult();

      	if (count($res) == 1) {
            $row = $res[0];
            if (md5(md5($pass).$row['salt']) == $row['pass']) {
               // update information about user
               master::last_active($row['id']);

               setcookie ("login", $row['login'], time() + 50000, '/','.'.HTTP_HOST);
               setcookie ("hash", md5($row['login'].$row['pass']), time() + 50000, '/','.'.HTTP_HOST);
               $_SESSION['id'] = $row['id'];

               $msg = "Вы успешно авторизировались.";
            } else
               $msg = "Пара логин/пароль не совпадает или Ваша учетная запись еще не активирована.";
      	} else
      		$msg = "Пользователь не найден или не выполнена активация учетной записи.";
      } else
         $msg = "Поля не должны быть пустыми.";

      // START
      // Задаем meta заголовки страницы
      $header['description'] = 'Регистрация новых пользователей';
      $header['keywords'] = 'регистрация, новый пользователь';
      $header['title'] = 'Регистрация нового пользователя (Шаг 1)';
      echo $content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
      $header = new header();

      $auth_cfg['msg_title'] = "Авторизация пользователя";
      $auth_cfg['msg_body'] = $msg;
      $auth_cfg['msg_redirect_timer'] = master::redirect('/my/',0);
      echo $content->design('login','message',$auth_cfg);
   }

  /**
   *
   * Выход из учетной записи пользователя
   *
   * @return   void
   *
   */
   public function out() {
      unset($_SESSION['id']);
      setcookie("login", "");
      setcookie("hash", "");

      $content  = new template();

      // START
      // Задаем meta заголовки страницы
      $header['description'] = 'Страница для выхода пользователя из системы';
      $header['keywords'] = 'выход, logout, loging out';
      $header['title'] = 'Выход';
      echo $content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
      $header = new header();

      //$auth_cfg['msg_title'] = "Выход";
      //$auth_cfg['msg_body'] = "До скорой встречи!";
      //$auth_cfg['msg_redirect_timer'] = master::redirect('/');
      //echo $content->design('login','message',$auth_cfg);
      $modal_cfg['type']     = 'warning';
      $modal_cfg['title']    = 'До скорой встречи!';
      $modal_cfg['message']  = 'Вы успешно вышли из своего аккаунта. Надеемся Вы вернетесь к нам снова.';
      $modal_cfg['redirect'] = '/';
      echo $content->design('modal','redirect',$modal_cfg);

      echo $content->design('master','empty-page');
   }
}
