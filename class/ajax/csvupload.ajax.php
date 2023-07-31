<?php
session_start();

// SPR-4 Автоподключение классов
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
include_once(DOCUMENT_ROOT.'/class/core/autoload.inc.php');

use \db\mysql\mysqlcrud;

$db = new mysqlcrud();
$db->connect();

// Каталог для загрузки
$folder = $_SERVER['DOCUMENT_ROOT'].'/api/';

//continue only if $_POST is set and it is a Ajax request
if (isset($_POST) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	// check $_FILES['ImageFile'] not empty
	if (!isset($_FILES['csv-file']) || !is_uploaded_file($_FILES['csv-file']['tmp_name'])){
		http_response_code(404);
      die();
	}

   //$uploadfile = $folder . basename($_FILES['csv-file']['name']);
   $uploadfile = $folder . 'price_new.csv';

   if (move_uploaded_file($_FILES['csv-file']['tmp_name'], $uploadfile)) {
      echo "Файл корректен и был успешно загружен.<br />";
   } else {
      echo "Возможная атака с помощью файловой загрузки!<br />";
   }

	//echo $csv_name = $_FILES['csv-file']['name']; //file name
	//echo ' | ' . $csv_size = $_FILES['csv-file']['size']; //file size
	//echo ' | ' . $csv_temp = $_FILES['csv-file']['tmp_name']; //file temp
   //echo "</pre>";
   /**/
}
