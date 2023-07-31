<?php

namespace product;

/**
* Объекты для работы с товарами
*
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 0.1
*/

use \db\mysql\mysqlcrud;
use \template\template;
use \template\terms;
use \helper\price;
use \tmp\master;
use \tmp\nestedtree;
use \debug\dBug;

class prod {

   /*public function __construct() {
      $this->db = new mysqlcrud();
      $this->db->connect();
      $this->content = new template();
   }/**/

   static function title( $title, $brand = null, $singular = null, $brand_url = false ) {
      $singular = (!is_null($singular) ? $singular.' ' : null);

      $tpl['regular'] = '%s%s %s';
      $tpl['product'] = '%s<a href="%s" target="_blank"><strong>%s</strong></a> %s';

      if ($brand_url) {
         return sprintf($tpl['product'], $singular, $brand_url, $brand, $title);
      } else {
         return sprintf($tpl['regular'], $singular, $brand, $title);
      }
   }

   static function buy1clickButton( $id = 0, $outOfStock = false, $size = 'huge' ) {
      $content = new template();

      $size = ' ' . $size;

      $class  = null;
      $status = null;
      $buy    = ($outOfStock ? 'Заказать' : 'Купить');
      $text   = $buy.' в 1 клик';

      $tpl['id']      = $id;
      $tpl['size']    = $size;
      $tpl['class']   = $class;
      $tpl['text']    = $text;
      $tpl['terms']   = terms::personal();
      $tpl['captcha'] = $content->design('captcha','recaptcha',['sitekey'=>RECAPTCHA_SITE_KEY]);

      $code = $content->design('modal','buy-clc',$tpl);
      //$code .= '<button id="buy-clc-'.$id.'" class="le-button'.$size.$class.' hvr-icon-buzz-out" data-toggle="modal" data-target="#buy-clc">'.$text.'</button>';
      #$code = '<a href="#" id="addto-cart" class="le-button'.$size.$class.'" data-options=\'{"id":'.$id.'}\''.$status.'>'.$text.'</a>';
      #$code = '<a class="plus" href="#addtocart" id="addto-cart" data-options=\'{"id":'.$id.'\'></a>';

      return $code;
   }

   static function itemsCarousel($cat_id,$title,$array) {
      $content = new template();

      $catinfo = master::catInfo($cat_id);
      //new dBug($catinfo);

      $conf['cat_id'] = $cat_id;
      $conf['cat_url'] = MENU_CAT_PATH . $catinfo['url'] . '/';
      $conf['title']  = $title;
      $conf['items']  = null;
      foreach ($array as $subcat) {
         //new dBug($subcat);

         //$item['name']  = mb_strtoupper($subcat['brand'], 'utf-8') . ' ' . $subcat['name'];
         $item['name']  = self::title($subcat['name'],mb_strtoupper($subcat['brand'], 'utf-8'));
         $item['brand'] = $subcat['brand'];
         $item['price'] = price::format($subcat['price']);
         //$item['photo'] = self::photoURL($subcat['1c_id']);
         $item['photo'] = HTTP_PROTOCOL.STATIC_URL.IMG_DIR.$subcat['path_big'];
         $item['url']   = master::prodURL($subcat['1c_id'],$subcat['brand_lat'] . ' ' . $subcat['name']);
         $item['to_cart_button'] = master::inCart($subcat['1c_id'],'small');

         $conf['items'] .= $content->design('category','carousel/element',$item);
      }
      //$conf['items'] .= $content->design('category','carousel/to-category');

      $return = $content->design('category','carousel/grid',$conf);

      return $return;
   }

	static function categoryPath( $id ) {
		$db = new mysqlcrud();
      $db->connect();

      $db->sql('
			SELECT
				parent.name
			FROM
				category AS node,
				category AS parent
			WHERE
				node.lft BETWEEN parent.lft AND parent.rgt AND node.cat_id = "' . $id . '"
			ORDER BY
				parent.lft
		');

      $result = $db->getResult();
		unset($result[0]);
		$return = array_column($result, 'name');
		$return = implode('/',$return);

		return $return;
	}

}
