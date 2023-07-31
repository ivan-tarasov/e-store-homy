<?php

namespace core;

/**
* Инициализация подключений, шаблонов и прочих необходимых элементов
*
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 1.0
*/
class init {

   public function __construct() {
      // подключение к БД MySQL
      global $DB;
      $DB = new \db\mysql\mysqlcrud();
      $DB->connect();

      // HTML-шаблоны
      global $TPL;
      $TPL = new \template\template();

      // Debug-скрипты
      global $dBug;
      $dBug = new \debug\dBug();
   }

}
