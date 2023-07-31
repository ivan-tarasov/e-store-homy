<?php
/**
* Модель движка магазина HOMY.SU
*
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 2.1
*/

// SPR-4 Автоподключение классов
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
include_once(DOCUMENT_ROOT.'/class/core/autoload.inc.php');

// Подключение конфигурационного класса
new \core\config\cfg();
//new test\test();
// Ядро
new \core\core();
