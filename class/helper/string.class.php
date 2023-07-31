<?php
namespace helper;

class string {

   static function prestring( $str ) {
      // удаляем ненужные символы из начала и конца строки
      $str = trim($str);
      // преобразуем специальные символы в HTML-сущности
      $str = htmlspecialchars($str, ENT_QUOTES);

      return $str;
   }

   static function truncate( $string, $length = 18, $dots = "..." ) {
      //return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
      return mb_substr($string,0,$length) . $dots;
   }

}
