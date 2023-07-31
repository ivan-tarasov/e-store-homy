<?php
use \template\template;
use \template\header;

use \tmp\master;

class feedback {

   public function __construct() {
      //$this->db = new mysqlcrud();
      //$this->db->connect();
      $this->content = new template();
   }

   /**
   * Обратная связь
   * @return   void
   */
   public function index() {
      // Задаем meta заголовки страницы
      $header['description'] = 'Оформление заказа';
      $header['keywords'] = 'обратная связь, feedback';
      $header['title'] = 'Обратная связь'.HEAD_TITLE_END;
      echo $this->content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
      $header = new header();

      echo $this->content->design('feedback','index');
   }

}
