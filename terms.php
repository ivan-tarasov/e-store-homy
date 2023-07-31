<?php

use \template\template;
use \tmp\master;
use \template\header;

class terms {
   /**
   * @return   integer  html code
   */
   public function index() {
      $content  = new template();

      // Creating META headers
      $header['description'] = 'Правила пользования магазином';
      $header['keywords']    = 'правила, terms';
      $header['title']       = 'Оплата, доставка и получение товара' . HEAD_TITLE_END;

      echo $content->design('index','header',$header);

      $header = new header();

      $terms['path'] = 'http://static.homy.su/zakon/';

      echo $content->design('terms','index',$terms);
   }

}
