<?php
/**
* Обработка выгрузки из 1С (ручной режим с загрузкой прайс-листа)
* @method  void     log(string $script, string $msg)  - лог выполнения скриптов (beta)
* @author  Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 2.2
*/

// SPR-4 Автоподключение классов
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
include_once(DOCUMENT_ROOT.'/class/core/autoload.inc.php');

use \db\mysql\mysqlcrud;
use \template\template;

use \tmp\master;
use \helper\date;
use \tmp\photoupload;
use \vendor\parseCSV;
use \vendor\php_rutils\RUtils;
use \vendor\php_rutils\Translit as translit;

class csvToMysql {

   public function __construct() {
      header('Content-Type: text/event-stream; charset=UTF-8');
      header('Cache-Control: no-cache');

      $this->db = new mysqlcrud();
      $this->db->connect();

      if (!self::split()) {
         self::send_message('CLOSE', 'ПРОИЗОШЕЛ СБОЙ ПРИ СКЛЕИВАНИИ!',null,'danger');
         die();
      }

      if (!master::counterUpdate('LAST_CATALOG_UPDATE',1,date("Y-m-d H:i:s"))) {
         self::send_message('CLOSE', 'Не могу обновить дату последней выгрузки',null,'danger');
         die();
      }

      /* !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */
      /*$this->db->sql('TRUNCATE catalog');
      $this->db->sql('TRUNCATE properties');
      $this->db->sql('TRUNCATE opinions');
      $this->db->sql('TRUNCATE images');/**/
      /* !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! */
      // Обновляем наличие товаров на складе
      $this->db->sql('UPDATE catalog SET exist = DEFAULT');

      $csv = new parseCSV();
      $csv->delimiter = ";";
      $csv->parse(DOCUMENT_ROOT.'/api/price_current.csv');
      $csv_data = $csv->data;

      $count = count($csv_data);
      $this->step = $this->new = $this->upd = $this->pht = 0;

      foreach ($csv_data as $this->param) {
         $this->step++;
         $this->percent = ((100*$this->step)/$count);
         $this->percent = number_format($this->percent, 0, '.', '');

         $this->insert = $this->update = null;

         $price = (int)str_replace(' ','',$this->param['price']);
         $price = ceil($price/10) * 10;

         $this->insert['cat_id']       = $this->update['cat_id']     = $this->catID($this->param['cat_id']);
                                                                     // Идентификатор категории
         $this->insert['1c_id']                                      = str_replace(' ', '', $this->param['1c_id']);
                                                                     // Идентификатор в базе 1С
         $this->insert['brand_id']     = $this->update['brand_id']   = $this->brandID($this->param['brand']);
                                                                     // Внутренний идентификатор производителя
         $this->insert['updated']      = $this->update['updated']    = date::now();
                                                                     // Дата и время обновления позиции
         $this->insert['name']         = $this->update['name']       = htmlspecialchars($this->param['name']);
                                                                     // Модель
         $this->insert['homepage']     = $this->update['homepage']   = (int)$this->param['homepage'];
                                                                     // УТОЧНИТЬ ПРО ЭТОТ ПАРАМЕТР
         $this->insert['stock']        = $this->update['stock']      = (int)str_replace(' ', '', $this->param['stock']);
                                                                     // Доступное количество на складе
         $this->insert['exist']        = $this->update['exist']      = ($this->insert['stock'] > 0 ? 1 : 0);
                                                                     // Наличие товара на складе
         $this->insert['price']        = $this->update['price']      = $price;
                                                                     // Цена товара
         $this->insert['ya_id']                                      = 'NULL';
                                                                     // Идентификатор Яндекс.Маркета
         $this->insert['description']                                = 'NULL';
                                                                     // Описание товара из Яндекс.Маркета
         $this->insert['rating']                                     = 0;
                                                                     // Рейтинг по-умолчанию

         // добавление НОВОЙ позиция
         if ($this->blackList($this->insert['cat_id'])) {
            self::send_message($this->step,'Категория <code>'.$this->param['cat_id'].'</code> в <b>черном списке</b>. Пропускаю.',$this->percent,'danger');
            $this->db->update('catalog',$this->update,'1c_id='.$this->insert['1c_id']);
            $this->upd++;
         } elseif ($this->isNew($this->insert['1c_id'])) {
            self::send_message($this->step,'<span class="label label-default">'.$this->insert['1c_id'].'</span> <span class="label label-warning">НОВАЯ</span> <b>'.$this->param['brand'].' '.$this->insert['name'].'</b>',$this->percent,'primary');
            $this->addItemProperties();
            $this->db->insert('catalog',$this->insert);
            $this->new++;
         // обновление СУЩЕСТВУЮЩЕЙ позиции
         } elseif (!$this->isHaveProperties($this->insert['1c_id'])) {
            self::send_message($this->step,'<kbd>'.$this->insert['1c_id'].'</kbd> - <code>'.$this->param['brand'].' '.$this->insert['name'].'</code>: обновляю характеристики и фотографии',$this->percent);
            $this->addItemProperties();
            $this->db->update('catalog',$this->update,'1c_id='.$this->insert['1c_id']);
            $this->upd++;
         } else {
            self::send_message($this->step,'<kbd>'.$this->insert['1c_id'].'</kbd> - <code>'.$this->param['brand'].' '.$this->insert['name'].'</code> обновляю цену и остатки',$this->percent);
            $this->db->update('catalog',$this->update,'1c_id='.$this->insert['1c_id']);
            $this->upd++;
         }

         $res = $this->db->getResult();
         //self::send_message($this->step,'-----',$this->percent);
      }

      /*self::send_message(0,'Выгружаю данные в Яндекс.Маркет...',99);
      if ($this->yamarketPrice()) {
         self::send_message(0,'Данные в Яндекс.Маркет успешно выгружены!',100,'success');
      } else {
         self::send_message(0,'Не удалось выгрузить данные в Яндекс.Маркет!',100,'danger');
      }/**/

      self::send_message(0,'Импорт прайс-листа завершен',100,'success');

      $variants = array('позиция','позиции','позиций');
      self::send_message('CLOSE', '<code>'.$count.' '.RUtils::numeral()->choosePlural($count, $variants).'</code> успешно обработаны.
                                 Новых: <code>'.$this->new.'</code>,
                                 обновленных: <code>'.$this->upd.'</code>,
                                 фото: <code>'.$this->pht.'</code>.',
                        100,'info');
   }

   /**
   * Добавление характеристик товара в базу данных
   * @author   Ivan Karapuzoff <ivan@karapuzoff.net>
   * @version  0.1
   */
   private function addItemProperties() {
      // Запрос к API возвращает 2 массива: 0 - основные параметры, 1 - характеристики
      $yapi = $this->yapiSearch($this->param['brand'] . ' ' . $this->param['name']);
      $search = $yapi[0];
      $main   = $yapi[1];
      $prop   = $yapi[2];

      // если запрос к API дал результат
      if ($search) {
         $this->insert['ya_id']       = $search['id'];
         $this->insert['description'] = trim($this->db->escapeString(preg_replace("/[\r\n]+/", "",$main['description'])));
         $this->insert['rating']      = ($search['rating'] > 0) ? $search['rating'] : 0;

         // Характеристики товара
         if (!empty($prop)) {
            self::send_message($this->step,'Идентификатор в каталоге Яндекс.Маркет: <code>'.$this->insert['ya_id'].'</code>',$this->percent);
            if (self::mysqlDetails($this->insert['1c_id'],$prop['modelDetails'])) {
               self::send_message($this->step,'Характеристики товара успешно добавлены в БД',$this->percent,'success');
            }
            if (!$this->haveImg($this->insert['1c_id']) && !empty($main['photos']['photo'])) {
               $photoupload = new photoupload();
               $photoupload->photos($this->insert['1c_id'],$main['photos']['photo']);
               self::send_message($this->step,'Фотографии товара успешно загружены',$this->percent,'success');
            } else
               self::send_message($this->step,'У позиции уже есть сохраненные фотографии',$this->percent);
         }

         // Отзывы о товаре
         self::yapiOpinions($this->insert['1c_id'],$this->insert['ya_id']);
      } else
         self::send_message($this->step,'Позиция не найдена в каталоге Яндекс.Маркета',$this->percent,'warning');
   }

   /**
   * Поиск товара через Яndex.Маркет API
   * @author   Ivan Karapuzoff <ivan@karapuzoff.net>
   * @version  2.0
   */
   private function yapiSearch( $search ) {
      $search_meizu = ['Серый с черным экраном','Серебристый с белым экраном','Cеребристый'];
      $search = str_replace($search_meizu,null,$search);

      $data = array(
               'api_key'         => YAPI_KEY,
               'count'           => YAPI_SEARCH_COUNT,
               'check_spelling'  => YAPI_SPELL_CHECK,
               'geo_id'          => YAPI_GEO_ID,
               'text'            => $search
            );
      $url = YAPI_URI.'search'.YAPI_FORMAT.'?'.http_build_query($data);

      // поиск по наименованию
      $content = json_decode(master::curl($url), true);

      foreach ($content['searchResult']['results'] as $item) {
         if (isset($item['offer'])) {
            return false;
            break;
         }

         $model = $item['model'];
         $have = mb_strtolower($model['name']);
         $replace = array('/','');
         $search = str_replace($replace,' ',$search);
         $search = str_replace(' ','|',$search);
         if (preg_match('/('.$search.')/i', $have)) {
            // поиск в каталоге Яндекс.Маркета по идентификатору модели
            $url = YAPI_URI.'model/'.$model['id'].YAPI_FORMAT.'?api_key='.YAPI_KEY;
            $main = json_decode(master::curl($url), true);
            $main = $main['model'];

            // поиск подробных характеристик модели
            $url = YAPI_URI.'model/'.$model['id'].'/details'.YAPI_FORMAT.'?api_key='.YAPI_KEY;
            $prop = json_decode(master::curl($url), true);
            $return = array($model,$main,$prop);

            return $return;
            break;
         }
      }
   }

   /**
   * Получение отзывов о товаре через Яndex.Маркет API
   * @author   Ivan Karapuzoff <ivan@karapuzoff.net>
   * @version  1.1
   */
   private function yapiOpinions( $c_id, $ya_id ) {
      $url = YAPI_URI . 'model/' . $ya_id . '/opinion' . YAPI_FORMAT . '?api_key=' . YAPI_KEY . '&sort=rank&count=30';

      $content = file_get_contents($url);
      //new dBug($content);
      $content = json_decode($content, true);
      //sleep(YAPI_WAITTIME);
      $content = $content['modelOpinions']['opinion'];

      foreach ($content as $opinion) {
         $insert = null;

         $insert['1c_id'] = intval($c_id);
         $insert['opin_id'] = intval($opinion['id']);
         $insert['date'] = date('Y-m-d G:i:s',substr($opinion['date'], 0, -3));
         $insert['grade'] = intval($opinion['grade'] + 3);
         $insert['text'] = $this->db->escapeString(@nl2br($opinion['text']));
         $insert['agree'] = intval($opinion['agree']);
         $insert['reject'] = intval($opinion['reject']);
         if (!$opinion['anonymous']) {
            $insert['author'] = $opinion['author'];
            $insert['avatar'] = (!empty($opinion['authorInfo']['avatarUrl']) ? str_replace("//",'',$opinion['authorInfo']['avatarUrl']) : null);
         }
         $insert['pro'] = $this->db->escapeString(@nl2br($opinion['pro']));
         $insert['contra'] = $this->db->escapeString(@nl2br($opinion['contra']));

         $this->db->sql('
            INSERT IGNORE INTO opinions
               ('.implode(', ',array_keys($insert)).')
            VALUES
               ("' . implode('", "', $insert) . '")
         ');
      }
   }

   /**
   * Добавление характеристик товара в MySQL
   * @author   Ivan Karapuzoff <ivan@karapuzoff.net>
   * @version  0.1
   */
   private function mysqlDetails( $id, $details ) {
      $insert['_id'] = (int)$id;

      foreach ($details[0]['params'] as $k => $v) {
         // убераем точки из параметров
         $v['name'] = str_replace('.','',$v['name']);
         $insert['main'][$v['name']] = $v['value'];
      }
      array_shift($details);
      $insert['other'] = $details;

      $insert['main'] = json_encode($insert['main'],JSON_UNESCAPED_UNICODE);
      $insert['main'] = mysql_real_escape_string($insert['main']);
      $insert['other'] = json_encode($insert['other'],JSON_UNESCAPED_UNICODE);
      $insert['other'] = mysql_real_escape_string($insert['other']);

      $this->db->sql('
         INSERT IGNORE INTO properties
            (id,main,other)
         VALUES
            ('.$insert['_id'].',"'.$insert['main'].'","'.$insert['other'].'")
      ');
      //self::send_message(0, 'INSERT IGNORE INTO properties (id,main,other) VALUES ('.$insert['_id'].',"'.$insert['main'].'","'.$insert['other'].'")', 99);
      return true;
   }

   /**
   * Проверка позиции на присутствие в каталоге
   * @author   Ivan Karapuzoff <ivan@karapuzoff.net>
   * @version  1.0
   */
   private function isNew( $id ) {
      $sql = $this->db->select('catalog','*',NULL,'1c_id="'.$id.'"');
      $res = $this->db->getResult();

      return ($res == null ? true : false);
   }

   /**
   * Проверка на наличии характеристик модели
   * @author   Ivan Karapuzoff <ivan@karapuzoff.net>
   * @version  1.0
   */
   private function isHaveProperties( $id ) {
      $sql = $this->db->sql('SELECT COUNT(*) AS count FROM properties WHERE id = '.$id);
      $res = $this->db->getResult();

      return ($res[0]['count'] > 0 ? true : false);
   }

   /**
   * Проверка на наличии фотографии товара
   * @author   Ivan Karapuzoff <ivan@karapuzoff.net>
   * @version  1.0
   */
   private function haveImg( $id ) {
      $sql = $this->db->select('images','*',NULL,'1c_id="'.$id.'"');
      $res = $this->db->getResult();

      return ($res == null ? false : true);
   }

   /**
   * Исключение из обновления нежелательных категорий
   * @author   Ivan Karapuzoff <ivan@karapuzoff.net>
   * @version  1.0
   */
   private function blackList( $category ) {
      $black = array(30,31,47,48,49,52,53,54,58,67,75,94,98,99,100);
      return (in_array($category,$black) ? true : false);
   }

   /**
   * Определяем ID категории для позиции
   * @author   Ivan Karapuzoff <ivan@karapuzoff.net>
   * @version  1.0
   */
   private function catID( $id ) {
      $sql = $this->db->select('category','cat_id',null,'name="'.$id.'"');
      $res = $this->db->getResult();
      if ($res != null)
         $return = $res[0]['cat_id'];
      else
         $return = CAT_SORT;

      return $return;
   }

   /**
   * Определяем ID производителя для позиции
   * @author   Ivan Karapuzoff <ivan@karapuzoff.net>
   * @version  1.0
   */
   private function brandID( $brand ) {
      $brand = mb_convert_case($brand, MB_CASE_LOWER, 'UTF-8');
      if ($brand == '') {
         $brand = "no-name";
      }

      $sql = $this->db->select('brands','brand_id',NULL,'brand_clean="'.$brand.'"');

      if ($this->db->numRows() != 0) {
         $res = $this->db->getResult();
         $return = $res[0]['brand_id'];
      } else {
         $insert['brand_clean'] = $brand;
         $insert['brand_lat']   = translit::translify($brand);
         $insert['brand_url']   = str_replace(' ','-',$insert['brand_lat']);

         $this->db->insert('brands',$insert);
         $res = $this->db->getResult();
         $return = $res[0];
      }

      return $return;
   }

   /**
   * Склеивает выгрузку из 1С с наименование таблиц
   * @author   Ivan Karapuzoff <ivan@karapuzoff.net>
   * @version  1.0
   */
   private function split() {
      $path      = DOCUMENT_ROOT.'/api/';
      //$file_head = $path.'head.csv';
      //$file_head = '1c_id;cat_id;name;brand;homepage;stock;price';
      $file_from = $path.'price_new.csv';
      $file_to   = $path.'price_current.csv';

      // Поправляем кодировку
      if ($price = iconv("CP1251","UTF-8", file_get_contents($file_from))) {
      //if ($price = file_get_contents($file_from)) {
         // Добавляем заголовок к исходному файлу
         return (file_put_contents($file_to,CSV_HEAD."\n".$price) ? true : false);
      } else
         return false;
   }

   /**
   * Взаимодействие с фронтэндом загрузчика
   * @author   Ivan Karapuzoff <ivan@karapuzoff.net>
   * @version  1.0
   */
   private function send_message( $id, $message, $progress, $contextual = 'muted' ) {
      $message = sprintf(CSV_LOG_MSG,$contextual,date(CSV_LOG_DATE),$message);
      $d = array('message' => $message , 'progress' => $progress);

      echo "id: $id" . PHP_EOL;
      echo "data: " . json_encode($d) . PHP_EOL;
      echo PHP_EOL;

      @ob_flush();
      flush();
   }

}

new csvToMysql();
