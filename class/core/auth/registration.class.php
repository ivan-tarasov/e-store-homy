<?php
namespace core\auth;

/**
* Регистрация нового пользователя в системе
*
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 1.0
*/

use \db\mysql\mysqlcrud;
use \template\template;

use \helper\phone;
use \helper\date;
use \auth\auth;
use \helper\generate;

class registration {

   public function reg($phone, $fio=false) {
      $db = new mysqlcrud();
      $db->connect();

      if (auth::isLogin()) {
         $return['ok'] = 1;
         $return['userid'] = $_SESSION['id'];
         return $return;
         exit;
      }

      $phone = phone::format($phone);

      $return['ok'] = 0;

      if (phone::validate($phone)) {

         $db->select('users','*',NULL,'phone="'.$phone.'"',NULL,NULL,1);
         $res = $db->getResult();

         if (count($res) < 1) {
            $pass = generate::pin();

            $insert['phone'] = $phone;
            $insert['date_reg'] = date::now();
            $insert['salt'] = generate::pin(3);
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
   }

   /**
   * Форматирование сообщений
   */
   static function notification($notif) {
      $content = new template();

      $return = $content->design('auth','notification',$notif);
      return $return;
   }

}
