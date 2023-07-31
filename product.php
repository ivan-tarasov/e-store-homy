<?php

/**
* Карточка товара
* @version 2.0
*/

use \language\lang;
use \db\mysql\mysqlcrud;
use \template\template;
use \template\header;

use \tmp\master;
use \product\prod;
use \tmp\rating;
use \auth\auth;
use \helper\price;
use \helper\string;
use \vendor\php_rutils\RUtils;
use \vendor\php_rutils\struct\TimeParams;

use \tmp\nestedtree;
use \debug\dBug;

class product {

   public function __construct() {
      $this->db = new mysqlcrud();
      $this->db->connect();
      $this->content = new template();
   }

   /**
   * Основная карточка товара
   * @return   integer  html code
   */
   public function index($params) {
      if($params['id'] == 'index') {
         header('Location: /404/');
         exit;
      }
      //new dBug($params);
      // получаем идентификатор товара из строки URL
      $this->PROD_ID = explode('-',$params['id']);
      $this->PROD_ID = $this->PROD_ID[0];

      // Обновление счетчика просмотов и даты последнего просмотра
      $sql = '
            INSERT DELAYED INTO
               catalog_counts (gds_id,views,last_view)
            VALUES
               ('.$this->PROD_ID.',1,CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE
               views = views + 1,
               last_view = CURRENT_TIMESTAMP
            ';
      $this->db->sql($sql);

      // получаем информацию о товаре
      //$this->db->select('catalog','*',null,'1c_id='.$this->PROD_ID);
      $this->db->sql('
         SELECT
            catalog.*,
				category.name AS cat_name,
            promo.id AS promo_id,
            promo.path AS promo_url,
            promo.title AS promo_title,
            promo.description AS promo_description,
            promo.color AS promo_color
         FROM catalog
			LEFT JOIN
				category
			ON
				catalog.cat_id = category.cat_id
         LEFT JOIN
            promo
         ON
            catalog.promo = promo.id
         WHERE
            1c_id = '.$this->PROD_ID
      );
      $product = $this->db->getResult();
		//new dBug($product);

      if (count($product) == 0) {
         header('Location: /404/');
      }
      //if ($product )
      $product = $product[0];

      // получаем информацию о категории
      $cat_info = master::catInfo($product['cat_id']);

      // получаем информацию о бренде
      $brand = master::brandInfo($product['brand_id']);
      $brand['name'] = mb_strtoupper($brand['brand_clean'], 'utf-8');
      //new dBug($brand);

      //$product['full_name'] = $brand['name'] . ' ' . $product['name'];
      //$product['full_name'] = prod::title($cat_info['singular'],$brand['name'],$product['name']);

      // START
      // Задаем meta заголовки страницы
      $description = string::prestring($product['description']);
      $header['description'] = $description;
      $header['keywords'] = $description;
      $header['title'] = prod::title($product['name'],$brand['name'],$cat_info['singular']).HEAD_TITLE_END;
      echo $this->content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
      $header = new header();

      echo master::breadcrumbs($cat_info['url']);

		### seperator
		$single_product['seperator'] = '';

      ### 1c_id
      $single_product['1c_id'] = $this->PROD_ID;

		### категории для пуш в метрику
		$single_product['categoryPath'] = prod::categoryPath($product['cat_id']);

      // Наличие
      //$prod_list['prod_axistence'] = info::axistence($prod_param['updated'],$prod_param['stock']);
      if ($product['exist'] > 0) {
         $single_product['prod_axistence'] = lang::PROD_INSTOCK;
         $single_product['available_bool'] = '';
      } else {
         $single_product['prod_axistence'] = lang::PROD_OUTSTOCK;
         $single_product['available_bool'] = 'not-';
      }/**/

      // item_name_hide
      $single_product['item_name_hide'] = prod::title($product['name'],$brand['name'],$cat_info['singular']);
      //  item_name
      $single_product['item_name'] = prod::title($product['name'],$brand['name'],$cat_info['singular'],'http://' . $brand['url']);

      ### Рейтинг товара
      $rating = new rating();
      $single_product['rating'] = $rating->show($product['rating']);


      //$single_product['rating_url']   = ($product['ya_id'] != 0 ? sprintf(YAPI_PROD_REV,$product['ya_id'],urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])) : null);

		### brand
		$single_product['brand'] = $brand['name'];

      ### description
      $single_product['description'] = ($product['description'] != 'NULL' ? $product['description'] : null);

      ### price_current & price_prev
		$single_product['price_current_clear'] = $product['price'];
      $single_product['price_current']			= price::format($product['price']);
      if ($product['exist'] == 0) {
         $single_product['no_exist_price'] = ' no-exist-price';
         $single_product['no_exist_note']  = lang::PROD_NOEXIST_PRICE;
      } else {
         $single_product['no_exist_price'] = null;
         $single_product['no_exist_note']  = null;
      }

      ### Кнопка добавления в корзину
      $single_product['to_cart_button']     = master::inCart($product['1c_id']);
      $single_product['to_cart_button_clc'] = prod::buy1clickButton($product['1c_id'],($product['exist'] == 0 ? true : false));
      $single_product['to_fav_button']      = master::inFav($product['1c_id']);

      ### ПРОМО-баннер
      if ($product['promo'] != null) {
         $promo['id']            = $product['promo_id'];
         $promo['url']           = PROMO_DOMEN.$product['promo_url'];
         $promo['title']         = $product['promo_title'];
         $promo['description']   = $product['promo_description'];
         $promo['color']         = $product['promo_color'];

         $single_product['promo_banner']    = $this->content->design('widget','promo-banner',$promo);
      } else
         $single_product['promo_banner'] = null;

      ### Фотографии товаров
      // ссылка на редактирование фотографий для админа
      $item_photo['edit_photo'] = (auth::isAdmin() ? '<a class="edit-photo" href="/cc/addphotos/id/'.$single_product['1c_id'].'"></a>' : null);

      $single_product['photo_big'] = null;
      $single_product['photo_thumb'] = null;

      $sql = $this->db->select('images','*',NULL,'1c_id='.$single_product['1c_id']);
      $res = $this->db->getResult();

      if (!empty($res)) {
         foreach ($res as $count => $photo) {
            $item_photo['count'] = $count;
            $item_thumb['count'] = $count;

            $item_photo['path']  = HTTP_PROTOCOL.STATIC_URL.IMG_DIR.$photo['path_orig'];
            $item_thumb['path']  = HTTP_PROTOCOL.STATIC_URL.IMG_DIR.$photo['path_thumbnail'];

            $item_photo['title'] = prod::title($product['name'],$brand['name'],$cat_info['singular']).' (код: '.$single_product['1c_id'].')';

            $single_product['photo_big'] .= $this->content->design('product','item-photo',$item_photo);
            $single_product['photo_thumb'] .= $this->content->design('product','item-thumb',$item_thumb);
         }
      } else {
         //$item_photo['count'] = 0;
         $item_photo['path'] = HTTP_PROTOCOL.STATIC_URL.IMG_DIR.IMG_NO_433;

         $single_product['photo_big'] = $this->content->design('product','item-photo-null',$item_photo);
         $single_product['photo_thumb'] = null;
      }

      ### SEO текст
      $single_product['seo_text'] = sprintf(lang::SEO_TEXT_PROD,strip_tags($single_product['item_name']));

      ### MONGO PROPERTIES
      //$single_product['properties'] = ($_SERVER['HTTP_HOST'] != 'localhost' ? $this->mongoProperties() : null);
      $single_product['properties'] = $this->mongoProperties();

      ### Отзывы из БД
      $opinions = $this->opinions();
      $single_product['reviews_count'] = $opinions['reviews_count'];
      $single_product['opinions']      = $opinions['opinions'];
      /**/


      $single_product['same_category'] = $this->sameCategory($product['cat_id']);

      echo $this->content->design('product','single-product',$single_product);
   }

  /**
   *
   * Характеристики товара из MongoDB
   *
   * @return   string  html code
   *
   */
   private function mongoProperties() {
      /*$mongo_db = mongocrud::connect();
      $collection = MONGO_PROP;
      $collection = $mongo_db->$collection;

      $finding = array('_id' => (int)$this->PROD_ID);
      $cursor = $collection->find($finding);/**/

      $properties = lang::PROD_NO_PROPERTIES;

      // Шаблоны для хаарктеристик товара
      $tpl['prop_body'] = '
                           <div class="row" style="margin-bottom: 2.5em;">
                              <div class="col-md-12">
                                 <h3 class="lead">%s</h3>
                              </div>
                              <div class="col-md-6 col-xs-12">
                                 %s
                              </div>
                           </div>
                           ';
      $tpl['prop_list'] = '
                           <div class="row prop-list">
                              <div class="col-md-6 col-xs-12">
                                 <label>%s</label>
                              </div>
                              <div class="col-md-6 col-xs-12">
                                 %s
                              </div>
                           </div>
                           ';

      $sql = $this->db->sql('SELECT main,other FROM properties WHERE id = '.(int)$this->PROD_ID);
      $res = $this->db->getResult();
      //new dBug($res);

      // если характеристики есть в БД - выводим
      if (!empty($res)) {
         foreach ($res as $arr) {
            foreach ($arr as $state => $prop) {
               $prop = json_decode($prop,true);
               if ($state == 'main') {
                  $prop_list = null;
                  foreach ($prop as $name => $val) {
                     $prop_list .= sprintf($tpl['prop_list'],$name,$val);
                  }
                  $properties = sprintf($tpl['prop_body'],lang::PROD_MAIN_PROP,$prop_list);
               } elseif ($state == 'other') {
                  foreach ($prop as $array) {
                     //new dBug($array);
                     $prop_list = null;
                     foreach ($array['params'] as $params) {
                        $prop_list .= sprintf($tpl['prop_list'],$params['name'],$params['value']);
                     }
                     $properties .= sprintf($tpl['prop_body'],$array['name'],$prop_list);
                  }
               }
            }
         }
      }

      return $properties;
   }

  /**
   *
   * Отзывы о товаре из БД MySQL
   *
   * @return   string  html code
   *
   */
   private function opinions() {
      $sql = $this->db->select('opinions','*',NULL,'1c_id="'.$this->PROD_ID.'" AND grade > 2');
      $res = $this->db->getResult();
      $total = count($res);

      $single_product['opinions'] = null;

      if($total > 0) {

         // Параметры даты
         $params = new TimeParams();
         $params->format = 'd F Y г. в H:i';
         $params->monthInflected = true;

         //new dBug($arr);

         $single_product['reviews_count'] = $total;

         foreach ($res as $opinion) {
            // дата отзыва
            $params->date = $opinion['date'];
            $opinions['date'] = RUtils::dt()->ruStrFTime($params);
            // Оценка товара
            $opinions['grade'] = $opinion['grade'];
            // Автор отзыва
            $opinions['username'] = ($opinion['author'] != null ? $opinion['author'] : lang::MAIN_ANONIM_USER);
            // Аватар пользователя, оставившего отзыв
            $opinions['avatar'] = ($opinion['avatar'] ? 'http://' . $opinion['avatar'] : HTTP_PROTOCOL.STATIC_URL.AVATAR_PATH.AVATAR_NO);
            // Кол-во отзывов от автора
            $opinions['opinions_count'] = (isset($opinion['authorInfo']['grades']) ? '<small class="text-muted">(отзывов: ' . $opinion['authorInfo']['grades'] . ')</small>' : null);
            // Отзыв о товаре
            $opinions['comment'] = (isset($opinion['text']) && $opinion['text'] != null ? nl2br($opinion['text']) : lang::NO);
            // ПЛЮСЫ
            $opinions['comment_pro'] = (isset($opinion['pro']) && $opinion['pro'] != null ? nl2br($opinion['pro']) : lang::NO);
            // МИНУСЫ
            $opinions['comment_contra'] = (isset($opinion['contra']) && $opinion['contra'] != null ? nl2br($opinion['contra']) : lang::NO);

            // Соотношение плюсов и минусов за отзыв
            $opinions['agree_count'] = $opinion['agree'];
            $opinions['reject_count'] = $opinion['reject'];
            $count_total = $opinions['agree_count'] + $opinions['reject_count'];
            if ($count_total != 0) {
               $opinions['agree_ratio'] = ($opinions['agree_count']/$count_total)*100;
               $opinions['reject_ratio'] = ($opinions['reject_count']/$count_total)*100;
            }

            // сводим все отзывы в один блок
            $single_product['opinions'] .= $this->content->design('product','opinion',$opinions);
         }
      } else {
         $single_product['reviews_count'] = 'нет';
         $single_product['opinions'] = '<div class="well well-lg">Отзывов пока нет</div>';
      }

      return $single_product;
   }

  /**
   *
   * Вывод товаров из той же категории. Позиции отсортированы по рейтингу.
   *
   * @return   string  html code
   *
   */
   private function sameCategory($cat_id) {
      $this->db->sql('
         SELECT DISTINCT
            catalog.1c_id,
            brands.brand_clean AS brand,
            brands.brand_lat,
            catalog.name,
            catalog.price,
            category.singular,
            images.path_big
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
            catalog.1c_id = images.1c_id
         WHERE
            catalog.cat_id = '.$cat_id.' AND
            catalog.1c_id != '.$this->PROD_ID.' AND
            catalog.exist = 1 AND
            images.path_big IS NOT NULL AND
            images.onmain = 1
         ORDER BY
            RAND()
         LIMIT 10
      ');
      $res = $this->db->getResult();

      if (!empty($res)) {
         $content['same_category'] = null;

         foreach ($res as $pos) {
            // Имя товара
            $pos['name'] = prod::title($pos['name'],mb_strtoupper($pos['brand'], 'utf-8'),$pos['singular']);

            // Ценник товара
            $pos['price'] = price::format($pos['price']);

            // Фото товара
            $pos['photo'] = (!empty($pos['path_big']) ? HTTP_PROTOCOL.STATIC_URL.IMG_DIR.$pos['path_big'] : HTTP_PROTOCOL.STATIC_URL.IMG_DIR.IMG_NO);

            // URL карточки товара
            $pos['url'] = master::prodURL($pos['1c_id'],$pos['brand_lat'] . ' ' . $pos['name']);

            // To cart button
            $pos['to_cart_button'] = master::inCart($pos['1c_id'],'small');

            $content['same_category'] .= $this->content->design('product','same-category-item',$pos);
         }

         return $this->content->design('product','same-category',$content);
      } else
         return false;

   }

}
