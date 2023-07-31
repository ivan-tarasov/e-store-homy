<?php
namespace auth;

use \db\mysql\mysqlcrud;

class auth {

   /**
   * Проверяет авторизацию пользователя
   *
   * @uses auth::isLogin() для проверки
   * @return bool авторизован TRUE или нет FALSE
   * @version 1.0
   */
   static function isLogin() {
      if(isset($_SESSION['id']))
         return true;
      else
         return false;
   }

   /**
   * Проверка прав пользователя
   *
   * @uses auth::isAdmin() для проверки прав пользователя
   * @return bool пользователь с правами администратора TRUE или обычный FALSE
   *
   * @version 1.0
   */
   static function isAdmin() {
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
   }

}
