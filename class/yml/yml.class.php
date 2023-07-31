<?php
namespace yml;

/**
* Класс для генерации YML-файла Яндекс.Маркета из массива PHP.
*
* @method  bool		connect()
* @method  bool		disconnect()
* @method  bool		sql(string $sql)
*
* @uses $yml = yml::createXML('root_node_name', $php_array);
*       echo $yml->saveXML();
*
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 1.0
*/

class yml {
   
   private static $xml = null;
   private static $encoding = 'UTF-8';

   /**
   * Инициализация корневого узла [опционально]
   * @param $version
   * @param $encoding
   * @param $format_output
   */
   public static function init($version = '1.0', $encoding = 'UTF-8', $format_output = true) {
      self::$xml = new \DomDocument($version, $encoding);
      self::$xml->formatOutput = $format_output;
      self::$encoding = $encoding;
   }

   /**
   * Создание XML-файла
   * @param  string  $node_name  имя корневого узла для конверсии
   * @param  array   $arr        массив для конверсии
   * @return DomDocument
   */
   public static function &createXML($node_name, $arr=array()) {
      $xml = self::getXMLRoot();
      $xml->appendChild(self::convert($node_name, $arr));

      self::$xml = null;
      return $xml;
   }

   /**
   * Конвертация массива в XML
   * @param  string  $node_name     имя корневого узла для конверсии
   * @param  array   $arr           массив для конверсии
   * @return DOMNode
   */
   private static function &convert($node_name, $arr=array()) {
      //print_arr($node_name);
      $xml = self::getXMLRoot();
      $node = $xml->createElement($node_name);

      if(is_array($arr)) {
         // ищем сперва атрибуты
         if(isset($arr['@attributes'])) {
            foreach($arr['@attributes'] as $key => $value) {
               if(!self::isValidTagName($key)) {
                  throw new Exception('[YML Converter] Запрещенный символ в имени атрибута. Атрибут: '.$key.' в узле: '.$node_name);
               }
               $node->setAttribute($key, self::bool2str($value));
            }
            unset($arr['@attributes']);
         }

         if(isset($arr['@value'])) {
            $node->appendChild($xml->createTextNode(self::bool2str($arr['@value'])));
            unset($arr['@value']);
            return $node;
         } elseif(isset($arr['@cdata'])) {
            $node->appendChild($xml->createCDATASection(self::bool2str($arr['@cdata'])));
            unset($arr['@cdata']);
            return $node;
         }
      }

      // создание подъветвей рекурсивно
      if(is_array($arr)) {
         foreach($arr as $key=>$value) {
            if(!self::isValidTagName($key)) {
               throw new \Exception('[YML Converter] Запрещенный символ в тэге. Тэг: '.$key.' в узле: '.$node_name);
            }
            if(is_array($value) && is_numeric(key($value))) {
               foreach($value as $k=>$v){
                  $node->appendChild(self::convert($key, $v));
               }
            } else {
               $node->appendChild(self::convert($key, $value));
            }
            unset($arr[$key]);
         }
      }

      // Проверяем наличие строковых значений. Если такие есть - присоединяем.
      if(!is_array($arr)) {
         $node->appendChild($xml->createTextNode(self::bool2str($arr)));
      }

      return $node;
   }

   /*
   * Получаем корневой узел XML. Если он отсутствует, то создаем его.
   */
   private static function getXMLRoot() {
      if(empty(self::$xml)) {
         self::init();
      }
      return self::$xml;
   }

   /*
   * Конвертация логического значение в строковое
   */
   private static function bool2str($v) {
      $v = $v === true ? 'true' : $v;
      $v = $v === false ? 'false' : $v;
      return $v;
   }

   /*
   * Проверяем тэги и аттрибуты на валидность
   * Ref: http://www.w3.org/TR/xml/#sec-common-syn
   */
   private static function isValidTagName($tag){
      $pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
      return preg_match($pattern, $tag, $matches) && $matches[0] == $tag;
   }

}
