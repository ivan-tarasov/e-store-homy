<?php
// SPR-4 Автоподключение классов
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
include_once(DOCUMENT_ROOT.'/class/core/autoload.inc.php');

use \db\mysql\mysqlcrud;
use \template\template;

use \tmp\master;
use \core\auth\registration;
use \helper\phone;

class auth {

   function __construct() {
      if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])
         && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
         && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

         session_start();

         $this->db = new mysqlcrud();
         $this->db->connect();

         $this->content = new template();

         switch ($_POST['action']) {
            case "reg":
               $phone  = $_POST['phone'];
               $return = registration::reg($phone);
               break;
            case "auth":
               $login  = $this->db->escapeString($_POST['login']);
               $pass   = $this->db->escapeString($_POST['pass']);
               $return = $this->auth($login,$pass);
               break;
            case 'chpass':
               $oldpass = $_POST['oldpass'];
               $newpass = $_POST['newpass'];
               $passchk = $_POST['passchk'];
               $return  = $this->changePassword($oldpass,$newpass,$passchk);
               break;
            case 'out':
               unset($_SESSION['id']);
               setcookie("login", "");
               setcookie("hash", "");
               $return['out'] = 1;
               break;
            default:
               echo "def";
         }

         echo json_encode($return);

      } else
         echo 'И что мы здесь забыли?';
   }

  /**
   *
   * Авторизация существующего пользователя
   *
   */
   private function auth($login,$pass) {
      $return['ok'] = 0;

      if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
         $where = 'email = "'.$login.'"';
      } else {
         $login = phone::format($login);
         $where = 'phone = "'.$login.'"';
      }

      $this->db->sql('
               SELECT *
               FROM users
               WHERE
                  ' . $where . '
               LIMIT 1
            ');
      $res = $this->db->getResult();

      if (count($res) == 1) {
         $row = $res[0];
         if (md5(md5($pass).$row['salt']) == $row['pass']) {
            master::last_active($row['id']);
            setcookie ("login", $row['id'], time() + 50000, '/','.'.HTTP_HOST);
            @setcookie ("hash", md5($row['login'].$row['pass']), time() + 50000, '/','.'.HTTP_HOST);
            $_SESSION['id'] = $row['id'];

            $return['ok'] = 1;
            $notif = array(
               'class' => 'success',
               'title' => 'Удачная авторизация',
               'text'  => 'Вы успешно авторизировались.'
            );
         } else {
            $notif = array(
               'class' => 'danger',
               'title' => 'Авторизация не удалась!',
               'text'  => 'Вы ввели не верный пароль.'
            );
         }
      } else {
         $notif = array(
            'class' => 'danger',
            'title' => 'Авторизация не удалась!',
            'text'  => 'Пользователь с таким логином не найден.'
         );
      }

      $return['message'] = registration::notification($notif);

      return $return;
   }

  /**
   *
   * Смена пароля пользователя
   *
   */
   private function changePassword($oldpass,$newpass,$passchk) {
      $return['ok'] = 0;

      if (!empty($oldpass) && !empty($newpass) && !empty($passchk)) {
         if ($newpass == $passchk) {
            $this->db->select('users','*',NULL,'id='.$_SESSION['id'],NULL,NULL,1);
            $res = $this->db->getResult();
            $row = $res[0];

            if(md5(md5($oldpass).$row['salt']) == $row['pass']) {
               $update['pass'] = md5(md5($newpass).$row['salt']);
               $this->db->update('users',$update,'id='.$_SESSION['id']);

               $notif = array(
                  'class' => 'success',
                  'title' => 'Пароль успешно изменен.',
                  'text'  => ''
               );

               $return['ok'] = 1;
            } else {
               $notif = array(
                  'class' => 'danger',
                  'title' => 'Ошибка!',
                  'text'  => 'Ваш текущий пароль и пароль в форме не совпадают. Возможно неверная раскладка клавиатуры или нажата клавиша <kbd>Caps Lock</kbd>'
               );
            }
         } else {
            $notif = array(
               'class' => 'danger',
               'title' => 'Пусто!',
               'text'  => 'Поля с новым паролем должны быть идентичны. Убедитесь, что вводите один и тот же пароль в оба поля.'
            );
         }
      } else {
         $notif = array(
            'class' => 'danger',
            'title' => 'Пусто!',
            'text'  => 'Вы не заполнили все поля в форме смены пароля. Попробуйте еще раз, у Вас обязательно все получится.'
         );
      }


      $return['message'] = registration::notification($notif);
      return $return;
   }

}

new auth();
