<?php
namespace helper;

/**
* Класс генератора паролей, пинов и UUID
*
* @method  string   uuid()       - генерация уникального UUID
* @method  string   pin()        - генерация уникального цифрового ПИН
* @method  string   password()   - генерация цифро-буквенного пароля
* @method  string   orderID()    - генерация номера заказа
*
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 0.1
*/

class generate {

   /**
   * UUID (Universally Unique Identifier) - 16-байтный (128-битный) идентификатор
   *
   * @uses generate::uuid() для генерации уникального UUID
   * @return string Уникальный UUID
   *
   * @version 1.0
   */
   static function uuid() {
      return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
         mt_rand(0,0xffff), mt_rand(0,0xffff),
         mt_rand(0,0xffff),
         mt_rand(0,0x0fff) | 0x4000,
         mt_rand(0,0x3fff) | 0x8000,
         mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff)
      );
   }

   /**
   * Генератор цифрового ПИН-кода
   *
   * @uses generate::pin($lenght) для получения цифрового ПИН-кода $lenght длины
   * @return string Цифровой ПИН-код
   *
   * @version 1.1
   */
   static function pin($lenght = 5) {
      $field = array('1','2','3','4','5','6','7','8','9');

      $pin = null;
      for($i = 0; $i < $lenght; $i++) {
         $index = rand(0, count($field) - 1);
         $pin .= $field[$index];
      }

      return $pin;
   }

   /**
   * Генератор цифро-буквенного пароля заданной длины
   *
   * @uses generate::password($lenght) для получения цифро-буквенного пароля $lenght длины
   * @return string цыфро-буквенный пароль
   *
   * @version 1.1
   */
   static function password($lenght = 12) {
      $field = [
         'a','b','c','d','e','f','g','h','i','j','k','m','n','o','p','r','s','t','u','v','x','y','z',
         'A','B','C','D','E','F','G','H','J','K','M','N','P','R','S','T','U','V','X','Y','Z',
         '1','2','3','4','5','6','7','8','9'
      ];

      $password = null;
      for($i = 0; $i < $lenght; $i++) {
         $index = rand(0, count($field) - 1);
         $password .= $field[$index];
      }

      return $password;
   }

   /**
   * Генератор номера заказа
   *
   * @uses generate::orderID() для получения номера заказа
   * @return string номер заказа
   *
   * @version 0.2
   */
   static function orderID() {
      $order = date('Ymd-') . strtoupper(self::pin(4));

      return $order;
   }

}
