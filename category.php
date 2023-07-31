<?php
use \language\lang;
use \db\mysql\mysqlcrud;
use \template\template;
use \template\header;

use \tmp\master;
use \product\prod;
use \tmp\rating;
use \helper\price;
use \helper\string;
use \vendor\php_rutils\RUtils;
use \vendor\php_rutils\struct\TimeParams;

class category {

	public function __construct() {
		$this->db = new mysqlcrud();
		$this->db->connect();
		$this->content = new template();
	}

	/**
	* Вывод корня дерева категорий
	* @return	integer  html code with faq items
	*/
	public function index() {
		// START
		// Задаем meta заголовки страницы
		$header['description'] = null;
		$header['keywords'] = null;
		$header['title'] = 'Каталог бытовой техники и электроники'.HEAD_TITLE_END;
		echo $this->content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
		$header = new header();

		$cat_info = master::catInfo(1);

		$lvl = 1;
      $this->db->sql('
         SELECT *
         FROM category
         WHERE
            lft BETWEEN '.$cat_info['lft'].' AND '.$cat_info['rgt'].' AND
            lvl = '.$lvl.' AND
            cat_id != '.$cat_info['cat_id']
      );
		$subcats = $this->db->getResult();

		$banners['banners-item'] = null;

      // текущее время
      $params = new TimeParams();
      $params->format = 'd F Y года H:i';
      $params->monthInflected = true;
      $params->date = time();
      $mtime = RUtils::dt()->ruStrFTime($params);

      // количество товаров в наличии
      $variants = ['позиция','позиции','позиций'];
      $this->db->sql('SELECT COUNT(*) AS count FROM catalog');
      $catalog_count = $this->db->getResult();
      $catalog_count = RUtils::numeral()->getPlural($catalog_count[0]['count'],$variants);

      $banners['items-count'] = 'По состоянию на '.$mtime.' в каталоге '.$catalog_count;

      foreach ($subcats as $subcat) {
			$conf['title'] = $subcat['name'];
			$conf['url'] = MENU_CAT_PATH . $subcat['url'] . '/';
			$conf['description'] = 'Опциональное описание категории';
			$conf['id'] = $subcat['cat_id'];

			$banners['banners-item'] .= $this->content->design('category','banners/element',$conf);
		}

		$cat['banner'] = $this->content->design('category','banners/body',$banners);

		echo $this->content->design('category','index',$cat);
	}

	/**
	* @return	integer  html code with faq items
	*/
	public function show($params) {
		$this->db->select('category','*',null,'url="' . $params['id'] . '"');
		$this->cat_res = $this->db->getResult();
		$this->cat_res = $this->cat_res[0];
		$diff = $this->cat_res['rgt'] - $this->cat_res['lft'];

		// START
		// Задаем meta заголовки страницы
		$header['description'] =
         ($this->cat_res['description'] != null
         ? string::prestring($this->cat_res['description'])
         : $this->description($this->cat_res['name'],$this->cat_res['singular'],$this->cat_res['title_name'],$this->cat_res['description'])
      );
      //$header['keywords'] = $this->keywords($this->cat_res['name'],$this->cat_res['singular'],$this->cat_res['title_name'],$this->cat_res['keywords']);
      $header['keywords'] =
         (isset($this->cat_res['keywords'])
         ? string::prestring($this->cat_res['keywords'])
         : $this->keywords($this->cat_res['name'],$this->cat_res['singular'],$this->cat_res['title_name'],$this->cat_res['keywords'])
      );

      $header['title'] = sprintf(
         HEAD_TITLE_F,
         mb_strtolower($this->cat_res['title_name'] != null ? $this->cat_res['title_name'] : $this->cat_res['name']),
         HEAD_TITLE_END
      );

      echo $this->content->design('index','header',$header);

		// Подключаем логотип, форму поиска и корзину покупок
		$header = new header();

		$cat_info = master::catInfo($this->cat_res['cat_id']);
		echo master::breadcrumbs($cat_info['url']);

		// Выводим header страницы
		echo $this->content->design('category','header');

		if ($diff > 1)
			echo $this->showSubCategories();
		else
			echo $this->showItemsList($params);

		// Выводим footer
		echo $this->content->design('category','footer');
	}

	/**
	* Показываем подкатегории в основной категории
	* @return	string  html code
	*/
	private function showSubCategories() {
		$cat_code = null;
		$lvl = $this->cat_res['lvl'] + 1;

		$this->db->select('category','*',null,'lft BETWEEN ' . $this->cat_res['lft'] . ' AND ' . $this->cat_res['rgt'] . ' AND lvl = ' . $lvl . ' AND cat_id != ' . $this->cat_res['cat_id']);
		$result = $this->db->getResult();
		//new dBug($result);

		foreach ($result as $subcat) {
			$this->db->sql('
				SELECT
					catalog.1c_id,
					brands.brand_clean AS brand,
					brands.brand_lat,
					catalog.name,
					catalog.price,
               images.path_big
				FROM catalog
				LEFT JOIN
					brands
				ON
					catalog.brand_id = brands.brand_id
            LEFT JOIN
               images
            ON
               catalog.1c_id = images.1c_id
				WHERE
					catalog.cat_id IN (SELECT cat_id FROM category WHERE lft BETWEEN '.$subcat['lft'].' AND '.$subcat['rgt'].') AND
               images.onmain = 1
				ORDER BY
					catalog.rating DESC
				LIMIT 5
			');
			$subcats = $this->db->getResult();

         $cat_code .= (count($subcats) != 0 ? prod::itemsCarousel($subcat['cat_id'],$subcat['name'],$subcats) : null);
		}

		return $cat_code;
	}

	/**
	* Показываем товары в категории
	* @return	string  html code
	*/
	private function showItemsList($params) {
		//new dBug($params);
		##
		## Формирование фильтра по производителям
		##
		//$this->db->select('catalog','*',NULL,'cat_id="'.$this->params['id'].'"');

		$this->db->sql('
					SELECT
						COUNT(*) as count,
						catalog.brand_id,
						brands.brand_url,
						brands.brand_clean
					FROM
						catalog
					LEFT JOIN
						brands
					ON
						catalog.brand_id = brands.brand_id
					WHERE
						catalog.cat_id = '.$this->cat_res['cat_id'].' AND
						catalog.brand_id != 7
					GROUP BY
						catalog.brand_id
					ORDER BY
						count DESC
					');
		$catalog_res = $this->db->getResult();
		//new dBug($catalog_res);

		// Проверяем фильтр по производителю
		$total_count = 0;

		if (!empty($params['brand'])) {
			$filter['brand'] = ' AND brands.brand_url = "'.$params['brand'].'"';

			foreach ($catalog_res as $cat_res) {
				$total_count = ($cat_res['brand_url'] == $params['brand'] ? $cat_res['count'] : $total_count);
			}
		} else {
			$filter['brand'] = null;

			foreach ($catalog_res as $cat_res) {
				$total_count += $cat_res['count'];
			}
		}

		foreach ($catalog_res as $k => $brand) {
			$brand_info = master::brandInfo($brand['brand_id']);
			$filter_brands['brand_clean']			= $brand_info['brand_clean'];
			$filter_brands['brand_url']			= MENU_CAT_PATH . $params['id'] . '/brand/' . $brand_info['brand_url'] . '/';
			$filter_brands['brands_count']		= $brand['count'];
			//@$filter_brands['brands_active'] 	= ($brand_info['brand_url'] == $params['brand'] ? ' active' : null);

			if ($brand_info['brand_url'] == @$params['brand']) {
				$filter_brands['brands_active'] = ' active';
				$brand_cleanname 					  = ' <strong>'.mb_strtoupper($brand_info['brand_clean'], 'utf-8').'</strong>';
			} else {
				$filter_brands['brands_active'] = null;
			}

			@$sidebar_content['filter_brands'] .= $this->content->design('category','filter-brands',$filter_brands);
		}

		// Сброс фильра
		$sidebar_content['clear_hidden']	= (empty($params['brand']) ? 'hidden' : null);
		$sidebar_content['clear_url'] 	= MENU_CAT_PATH . $params['id'] . '/';

		echo $this->content->design('category','sidebar',@$sidebar_content);
		##
		## END : Формирование фильтра по производителям
		##

		// Формируем блоки для показа
		$sections['banner']	   = (SHOW_BANNER ? $this->content->design('category','section-banner') : null);
		$sections['recomended'] = (SHOW_RECOMENDED ? $this->content->design('category','section-recomended') : null);

		$grid['cat_name']   = $this->cat_res['name'];
		$grid['sort_brand'] = (!empty($brand_cleanname) ? $brand_cleanname : null);

		// Форма для сортировки позиций
		if (isset($_POST['item-sort'])) {
			switch ($_POST['item-sort']) {
				case 'price-asc':
					$item_sort['name'] = 'price-asc';
					$item_sort['mysql'] = 'price';
					break;
				case 'price-desc':
					$item_sort['name'] = 'price-desc';
					$item_sort['mysql'] = 'price DESC';
					break;
				case 'rating-desc':
					$item_sort['name'] = 'rating-desc';
					$item_sort['mysql'] = 'rating DESC';
					break;
				default:
					$item_sort = false;
			}
			if ($item_sort)
				$_SESSION['item-sort'] = $item_sort;
			else
				unset($_SESSION['item-sort']);
		}

		$grid['item-sort'] = null;
		$sort_array = array(
								'no-sort'		=> lang::SORT_NO,
								'price-asc'		=> lang::SORT_PRICE_ASC,
								'price-desc'	=> lang::SORT_PRICE_DESC,
								'rating-desc'	=> lang::SORT_RATING_DESC
							);
		foreach ($sort_array as $value => $name) {
			$selected = null;
			if(isset($_SESSION['item-sort'])) {
				if($_SESSION['item-sort']['name'] == $value) {
					$selected = 'selected';
				}
			}
			$grid['item-sort'] .= '<option value="' . $value . '"' . $selected . '>' . $name . '</option>';
		}

		##
		## Формирование списка позиций
		##
		if (isset($_GET["page"]))
			$page  = $_GET["page"];
		else
			$page = 1;

		$start_from = ($page-1) * CAT_PER_PAGE;

		$sorting = (isset($_SESSION['item-sort']) ? $_SESSION['item-sort']['mysql'].',' : null);

		$this->db->sql('
			SELECT
				catalog.1c_id,
				catalog.ya_id,
				brands.brand_clean AS brand,
				catalog.updated,
				catalog.name,
				catalog.description,
				catalog.rating,
				catalog.exist,
				catalog.stock,
				catalog.price,
				images.path_big
			FROM catalog
			LEFT JOIN
				brands
			ON
				catalog.brand_id = brands.brand_id
			LEFT JOIN
				images
			ON
				catalog.1c_id = images.1c_id AND images.onmain = 1
			WHERE
				cat_id = '.$this->cat_res['cat_id'].$filter['brand'].'
			ORDER BY
				'.$sorting.'
				exist DESC,
				1c_id DESC
			LIMIT '.$start_from.','.CAT_PER_PAGE.'
		');
		$products = $this->db->getResult();
		//new dBug($products);
		//echo $this->db->getSql();
		//new dBug($products);

		$code_grid = null;
		$code_list = null;

		// Позиций на странице
		$positions = 0;

		foreach ($products as $k => $prod_param) {
			$positions++;

			// Номенклатурный код
			$prod_list['prod_code'] = $prod_param['1c_id'];
			// Наименование позиции
			$prod_param['brand'] = mb_strtoupper($prod_param['brand'], 'utf-8');
			$prod_list['prod_name'] = prod::title($prod_param['name'],$prod_param['brand'],$this->cat_res['singular']);
			// URL карточки товара
			$prod_list['prod_url'] = master::prodURL($prod_param['1c_id'],$prod_param['brand'] . ' ' . $prod_param['name']);
			// Производитель
			$prod_list['prod_brand'] = $prod_param['brand'];
			// Рейтинг
	      $rating = new rating();
	      $prod_list['rating'] = $rating->show($prod_param['rating']);
			// Краткое описание товара
			$prod_list['prod_description'] = ($prod_param['description'] != 'NULL' ? $prod_param['description'] : null);
			// Кнопка добавления в корзину
			$prod_list['to_cart_button'] = master::inCart($prod_param['1c_id'],'small');
			// Путь до изображения
			if (!empty($prod_param['path_big'])) {
				$prod_list['img_path'] = HTTP_PROTOCOL.STATIC_URL.IMG_DIR.$prod_param['path_big'];
			} else {
				$prod_list['img_path'] = HTTP_PROTOCOL.STATIC_URL.IMG_DIR.IMG_NO;
			}
			// Цена
			if ($prod_param['price'] != 0)
				$prod_list['prod_price'] = price::format($prod_param['price']);
			else
				$prod_list['prod_price'] = lang::PROD_NO_PRICE;

			// Наличие
			if ($prod_param['exist'] > 0) {
				$prod_list['prod_axistence'] = lang::PROD_INSTOCK;
				$prod_list['available_bool'] = '';
			} else {
				$prod_list['prod_axistence'] = lang::PROD_OUTSTOCK;
				$prod_list['available_bool'] = 'not-';
			}

			// Построение списка
			$code_list .= $this->content->design('category','product-list',$prod_list);
			$code_grid .= $this->content->design('category','product-grid',$prod_list);
		}

		// Всего страниц
		$total_pages = ceil($total_count / CAT_PER_PAGE);
		// Текущая страница
		$current_page = (isset($_GET['page']) ? $_GET['page'] : 1);
		// URL без страницы
		$url = 'http://' . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

		$grid['product_grid']	 = $code_grid;
		$grid['product_list']	 = $code_list;
		$grid['pagination']		 = master::pagination($url, 'page', $total_pages, $current_page);
		$grid['positions_to']	 = ($current_page * CAT_PER_PAGE) - (CAT_PER_PAGE - $positions);
		$grid['positions_from']  = $grid['positions_to']  - ($positions - 1);
		$grid['positions_total'] = $total_count;
		$sections['grid'] 		 = $this->content->design('category','section-grid',$grid);
		##
		## END : Формирование списка позиций
		##

		// Выводим блоки
		echo $this->content->design('category','show',$sections);
	}

   /**
   * Формирование строки с ключевыми словами
   * @return	string  keywords
   */
   private function keywords($cat_name,$singular,$title_name,$cat_keywords) {
      $category = mb_strtolower($title_name != null ? $title_name : $cat_name);
      $singular = mb_strtolower($singular);

      $keywords = sprintf(KEYWRDS_CAT,$category,$singular);

      return $keywords;
   }

   /**
   * Формирование строки с описанием страницы
   * @return	string  description
   */
   private function description($cat_name,$singular,$title_name,$cat_description) {
      $category = mb_strtolower($title_name != null ? $title_name : $cat_name);
      $singular = mb_strtolower($singular);

      $description = sprintf(DESCR_CAT,$category,$singular);

      return $description;
   }

}
