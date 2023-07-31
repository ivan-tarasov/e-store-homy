<?php
namespace helper;

/**
* Действия с номерами телефонов
* @version 0.2
*/

class phone {

   static function format($phone,$reverce = false) {
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
   }

   static function validate($phone) {
      $phone = self::format($phone);

      $chk_1 = (strlen($phone) == 11 ? true : false);
      $chk_2 = (preg_match("/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i", $phone) == 1 ? true : false);

      return ($chk_1 && $chk_2 ? true : false);
   }

}
