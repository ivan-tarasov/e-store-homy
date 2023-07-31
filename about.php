<?php
/**
* Страница О КОМПАНИИ
* @return   integer  html code
*/

use \template\template;
use \template\header;

class about {

   public function index() {
      $content  = new template();

      // START
      // Задаем meta заголовки страницы
      $header['description'] = 'О компании';
      $header['keywords'] = 'о нас, о компании, описание деятельности';
      $header['title'] = 'О компании';
      echo $content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
      $header = new header();
      echo $content->design('about','index');
   }

}
