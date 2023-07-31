<?php
namespace helper;

class date {

   static function now($format = 0) {
      $now = date('Y-m-d G:i:s');

      switch ($format) {
         case 0:
            $now = $now;
            break;
         case 1:
            $now = '[' . $now . ']';
            break;
      }

      return $now;
   }

}
