<?php
/**
* Обработка HTTP статус-кодов и ошибок
* @author  Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 1.1
*/

use \template\template;
use \template\header;

class errors {

   public function __construct() {
      $this->content  = new template();
   }
   /**
   * 404 - Not Found
   */
   public function error_404() {
      http_response_code(404);

      // Задаем meta заголовки страницы
      $header['description'] = 'Страница не найдена';
      $header['keywords'] = '404, not found, error';
      $header['title'] = '404: Страница не найдена'.HEAD_TITLE_END;
      echo $this->content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
      $header = new header();

		// START
      echo $this->content->design('error','404');
   }

   /**
   * 503 - Service Unavailable
   */
   public function error_503() {
      http_response_code(503);

      // Задаем meta заголовки страницы
      $header['description'] = 'Сайт находится в режиме обслуживания';
      $header['keywords'] = '503, maintenance, обслуживание';
      $header['title'] = '503: Сайт в режиме обслуживания'.HEAD_TITLE_END;
      echo $this->content->design('index','header',$header);

      // Подключаем логотип, форму поиска и корзину покупок
      $header = new header();

      // START
      echo $this->content->design('error','404');
   }
}
