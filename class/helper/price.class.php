<?php
namespace helper;

/**
* Действия с прайсом
* @version 0.2
*/

class price {

   /**
   * Форматирование цены товара
   *
   * @uses price::format(34455)
   * @return string форматированная цена
   *
   * @version 1.1
   */
   static function format($price) {
      if ($price != 0)
         $price = number_format($price, 0, ',', ' ') . CURRENCY;
      else
         $price = 'б/п<sup>*</sup>';

      return $price;
   }


}
