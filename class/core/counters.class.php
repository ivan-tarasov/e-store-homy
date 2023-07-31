<?php

namespace core;

use \db\mysql\mysqlcrud;

/**
* Работа со счетчиками
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 0.1
*/
class counters {

   static function get($counter) {
      $db = new mysqlcrud();
      $db->connect();

      $db->sql('
         SELECT
            val_int,
            val_datetime
         FROM counters
         WHERE
            counter = "'.$counter.'"

      ');
      $value = $db->getResult();
      $value = $value[0];

      $return = ($value['val_int'] != null ? $value['val_int'] : $value['val_datetime']);

      return $return;
   }

}
