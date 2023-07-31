<?php
/**
* Сборка домашней страницы
* @version 2.0
*/

use \language\lang;
use \db\mysql\mysqlcrud;
use \template\template;
use \template\header;

use \tmp\master;
use \product\prod;
use \helper\price;

class homepage {

   public function index() {
      $this->db = new mysqlcrud();
      $this->db->connect();
      $this->content  = new template();
      $this->prod = new prod();

      $header_cfg['description'] = HEAD_DESCR;
      $header_cfg['keywords']    = HEAD_KEYWORDS;
      $header_cfg['title']       = HEAD_TITLE.HEAD_TITLE_END;
      echo $this->content->design('index','header',$header_cfg);

      $header = new header();

      // Большой баннер
      echo $this->bannerHero();
      // Табуляторы: новые товары, популярные товары, ТОП продаж
		$tab['tab-new-arrivals'] = $this->tabNewArrivals();
		//$tab['tab-futured'] = $this->tabFutured();
      echo $this->content->design('homepage','tab-new-fav-top',$tab);
      // Лидеры продаж
		//echo $this->bestSellers();
      // Последние просмотренные
		echo $this->recentlyViewed();
      // Маленькие баннеры
      //echo $this->bannerSmall();
      // Преимущества магазина
      //echo $this->content->design('homepage','advantages');
      // Баннеры производителей
      echo $this->brandsBanners();
   }

   private function bannerHero() {
      return $this->content->design('homepage','hero-banner');
   }

   private function bannerSmall() {
      return $this->content->design('homepage','banners');
   }

   private function tabNewArrivals() {
      $content = null;

      $this->db->sql('
         SELECT
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
            catalog.1c_id = images.1c_id AND images.onmain = 1
         WHERE images.path_big IS NOT NULL
         ORDER BY
            catalog.gds_id DESC
         LIMIT 12
      ');
      $res = $this->db->getResult();

      foreach ($res as $pos) {
         // Путь до изображения
         $pos['img_path'] = (!empty($pos['path_big']) ? HTTP_PROTOCOL.STATIC_URL.IMG_DIR.$pos['path_big'] : HTTP_PROTOCOL.STATIC_URL.IMG_DIR.IMG_NO);
         // Название товара
         $pos['name'] = $this->prod->title($pos['name'],mb_strtoupper($pos['brand'],'utf-8'),$pos['singular']);
         // Цена
         $pos['price'] = price::format($pos['price']);
         // URL карточки товара
         $pos['url'] = master::prodURL($pos['1c_id'],$pos['brand_lat'] . ' ' . $pos['name']);
         // Кнопка добавления в корзину
         $pos['to_cart_button'] = master::inCart($pos['1c_id'],'small');

         $content .= $this->content->design('homepage','tab-new-arrivals',$pos);
      }

      return $content;
   }

   private function tabFutured() {
      $content = null;
      $content .= $this->content->design('homepage','tab-futured');
      return $content;
   }

   private function bestSellers() {
      return $this->content->design('homepage','best-sellers');
   }

   private function recentlyViewed() {
      $this->db->sql('
         SELECT
            catalog.1c_id,
            brands.brand_clean AS brand,
            brands.brand_lat,
            catalog.name,
            catalog.price,
            catalog_counts.last_view,
            category.singular,
            images.path_big
         FROM catalog_counts
         LEFT JOIN
            catalog
         ON
            catalog_counts.gds_id = catalog.1c_id
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
         WHERE images.path_big IS NOT NULL
         ORDER BY
            catalog_counts.last_view DESC
         LIMIT 10
      ');
      $res = $this->db->getResult();

      $content['recently-viewed'] = null;

      foreach ($res as $pos) {
         // Имя товара
         $pos['name'] = $this->prod->title($pos['name'],mb_strtoupper($pos['brand'], 'utf-8'),$pos['singular']);
         // Ценник товара
         $pos['price'] = price::format($pos['price']);
         // Фото товара
         $pos['photo'] = (!empty($pos['path_big']) ? HTTP_PROTOCOL.STATIC_URL.IMG_DIR.$pos['path_big'] : HTTP_PROTOCOL.STATIC_URL.IMG_DIR.IMG_NO);
         // URL карточки товара
         $pos['url'] = master::prodURL($pos['1c_id'],$pos['brand_lat'] . ' ' . $pos['name']);
         // Кнопка добавления в корзину
         $pos['to_cart_button'] = master::inCart($pos['1c_id'],'small');

         $content['recently-viewed'] .= $this->content->design('homepage','recently-viewed-item',$pos);
      }

      return $this->content->design('homepage','recently-viewed',$content);
   }

   private function brandsBanners() {
      $brand_sql = $this->db->select('brands','*',NULL,"img=1 AND url IS NOT NULL","RAND()",6);
      $brand_res = $this->db->getResult();
      $brand_list = '';
      foreach ($brand_res as $k => $param) {
         $brand_list .= '<div class="carousel-item"><a href="http://' . $param['url'] . '" target="_blank"><img alt="" src="/img/brands/brand-' . $param['brand_lat'] . '.png" /></a></div>';
      }
      $brands['item'] = $brand_list;

      return $this->content->design('homepage','top-brands',$brands);
   }
}
