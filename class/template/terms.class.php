<?php
namespace template;

class terms {

   function __construct() {

   }

   static function personal( $button = 'Оформить заказ') {
      $content = new template();

      $templ['button'] = $button;

      $return = $content->design('terms','terms-personal',$templ);
      return $return;
   }

}
