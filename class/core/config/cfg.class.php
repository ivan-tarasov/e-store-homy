<?php
namespace core\config;

/**
* Конфигурационный класс
*
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 2.1
*/

use \core\counters;
use \db\mysql\mysqlcrud;
use \debug\dBug;

class cfg {
   public function __construct() {
      if (defined('SITE_VERSION'))
         return;

      define( 'SITE_VERSION',          '2.3.6' );

      define( 'DB_HOST',               'localhost' );
      define( 'DB_NAME',               'homysu' );
      define( 'DB_USER',               'homysu' );
      define( 'DB_PASS',               'kfvthbnf' );
      define( 'DB_PREF',               null );

      define( 'LAST_CATALOG_UPDATE',   counters::get('LAST_CATALOG_UPDATE') );
      # дата обновления каталога
      define( 'HTTP_PROTOCOL',         (isset($_SERVER["HTTPS"]) ? 'https:' : 'http:') . '//' );
      # используемый протокол
      define( 'HTTP_HOST',             implode('.', array_slice(explode('.', $_SERVER['SERVER_NAME']), -2)) );
      # хост

      // собираем конфигурацию из БД
      $this->db = new mysqlcrud();
      $this->db->connect();

      $this->db->sql('
         SELECT
            var, val_char, val_int, val_bool
         FROM
            config
      ');

      $config = $this->db->getResult();

      foreach ($config as $conf) {
         $var        = $conf['var'];
         $val_char   = $conf['val_char'];
         $val_int    = $conf['val_int'];
         $val_bool   = $conf['val_bool'];

         if ($val_char != null)
            $val = $val_char;
         elseif ($val_int != null)
            $val = $val_int;
         else {
            $val = $val_bool;
            if ($val == 1)
               $val = true;
            else
               $val = false;
         }

         define($var,$val);
      }

   }
}
