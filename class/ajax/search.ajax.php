<?php
// SPR-4 Автоподключение классов
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
include_once(DOCUMENT_ROOT.'/class/core/autoload.inc.php');

use \db\mysql\mysqlcrud;
use \template\template;

use \tmp\master;
use \product\prod;

class search {

   function __construct() {
      $this->db = new mysqlcrud();
      $this->db->connect();
      $this->content = new template();

      $user_input       = trim($_REQUEST['term']);
      $user_input_words = explode(' ', $user_input);
      //$user_input = trim('олодил');

      $display_json = array();
      $json_arr = array();

      $user_input = preg_replace('/\s+/', ' ', $user_input);

      $this->db->sql('
            SELECT
               catalog.1c_id,
               brands.brand_clean AS brand,
               catalog.name,
               catalog.price,
               catalog.rating,
               category.name AS category,
               category.singular,
               images.path_thumbnail AS img,
                   (LOWER(category.name)      REGEXP LOWER("('.implode('|',$user_input_words).')"))
                  +(LOWER(category.singular)  REGEXP LOWER("('.implode('|',$user_input_words).')"))
                  +(LOWER(brands.brand_clean) REGEXP LOWER("('.implode('|',$user_input_words).')"))
                  +(LOWER(catalog.name)       REGEXP LOWER("('.implode('|',$user_input_words).')")) as relevance
            FROM catalog
            LEFT JOIN
               brands
            ON
               catalog.brand_id = brands.brand_id
            LEFT JOIN
               category
            ON
               catalog.cat_id = category.cat_id
            LEFT JOIN
               images
            ON
               catalog.1c_id = images.1c_id AND images.onmain = 1
            WHERE
               catalog.1c_id = "'.$user_input.'" OR
               LOWER(category.name)      REGEXP LOWER("('.implode('|',$user_input_words).')") OR
               LOWER(category.singular)  REGEXP LOWER("('.implode('|',$user_input_words).')") OR
               LOWER(brands.brand_clean) REGEXP LOWER("('.implode('|',$user_input_words).')") OR
               LOWER(catalog.name)       REGEXP LOWER("('.implode('|',$user_input_words).')")
            ORDER BY
               relevance DESC,
               rating DESC
            LIMIT 6
         ');

      $res = $this->db->getResult();
      //new dBug($res);
      //echo count($recSql);

      if(count($res) != 0) {
         foreach ($res as $item) {
            $json_arr = null;

            ### Ссылка на карточку искомого товара
            $json_arr['id']    = master::prodURL($item['1c_id'],prod::title($item['name'],$item['brand'])).QUERY_FROMSRCH;
            ### Заполнение формы поиска после нажатия
            $json_arr['value'] = $item['name'];
            ### Отображаемая позиция с подсветкой искомой фразы
            $tmp['image_url']  = ($item['img'] != null ? HTTP_PROTOCOL.STATIC_URL.IMG_DIR.$item['img'] : HTTP_PROTOCOL.STATIC_URL.IMG_DIR.IMG_NO);
            $tmp['category']   = self::highlightKeyword($user_input,$item['category']);
            //$tmp['relevance']  = self::relevance($user_input,implode(' ',$item));
            //$tmp['relevance']  = ($item['relevance'] != null ? $item['relevance'] : 'null');
            //$tmp['rating']     = $item['rating'];
            $tmp['highlight']  = prod::title($item['name'],mb_strtoupper($item['brand'], 'utf-8'),$item['singular']);
            $tmp['highlight']  = self::highlightKeyword($user_input,$tmp['highlight']);
            $json_arr['label'] = $this->content->design('search','element',$tmp);
            /**///$item_thumb['path'] = IMG_DIR.$photo['path_thumbnail'];
            array_push($display_json, $json_arr);
         }
      } else {
        $json_arr["id"] = "#";
        $json_arr["value"] = "";
        $json_arr["label"] = "Увы, ничего не найдено";
        array_push($display_json, $json_arr);
      }


      $jsonWrite = json_encode($display_json);
      print $jsonWrite;
   }

   private static function relevance($search,$string) {
      $search = explode(' ', $search);
      $return = preg_match_all("/(".implode('|',$search).")/iu", $string, $out);

      return $return;
   }

   private static function highlightKeyword($search,$str) {
      $highlightcolor = "#daa732";
      $occurrences = substr_count(strtolower($str), strtolower($search));
      $newstring = $str;
      $match = explode(' ', $search);

      $newstring = preg_replace("/(".implode('|',$match).")/iu","[#]\\1[@]",$newstring);
      /*for ($i=0;$i<$occurrences;$i++) {
         $match[$i] = stripos($str, $search, $i);
         $match[$i] = substr($str, $match[$i], strlen($search));

         $newstring = preg_replace("/(".$match[$i].")/iu", "[#]".$match[$i]."[@]", strip_tags($newstring));
         //$newstring = preg_replace('/('.$match[$i].')/iu', '<b>\\1</b>', strip_tags($newstring));
      }/**/

      $newstring = str_replace('[#]', '<mark>', $newstring);
      $newstring = str_replace('[@]', '</mark>', $newstring);

      return $newstring;
      /**/
   }
}

new search();
