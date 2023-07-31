<?php

namespace tmp;

use \template\template;

/**
* Работа с рейтингом
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 0.2
*/

class rating {

   const LABEL_TPL   = '<span class="label label-%s">%s</span>',
         STAR_TPL    = '<i class="fa fa-%s"></i>',
         STAR_WHOLE  = 'star',
         STAR_HALF   = 'star-half',
         ICON_USERS  = 'trophy fa-lg';

   public function __construct() {
      $this->content = new template();
   }

   /**
   * Формирование вида рейтинга
   */
   public function show($rating) {
      if ((int)$rating > 2) {
         $tpl['stars'] = $this->rating2stars($rating);
         $tpl['color'] = str_replace('.','',$rating);
         $tpl['value'] = $rating;
			//$tpl['best']  = ($rating == 5 ? sprintf(self::LABEL_TPL,'warning',sprintf(self::STAR_TPL,self::ICON_USERS)) : null);
         $tpl['best']  = null;

         $return = $this->content->design('widget','rating',$tpl);

         return $return;
      } else {
         return null;
      }
   }

  /**
   *
   * Конвертация числового рейтинга в звезды
   *
   */
   private function rating2stars($rating) {
      $stars = null;
      $whole = floor($rating);
      $half  = round($rating * 2) % 2;

      for ($i=0; $i<$whole; $i++){
         $stars .= sprintf(self::STAR_TPL,self::STAR_WHOLE);
      }
      if( $half ) {
         $stars .= sprintf(self::STAR_TPL,self::STAR_HALF);
      }

      return $stars;
   }

}
