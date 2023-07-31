<?php
namespace order;

use \language\lang;
use \db\mysql\mysqlcrud;

use \helper\generate;
use \helper\date;
use \helper\phone;
use \helper\info;
use \sms\smsru;

use \product\prod;

use \debug\dBug;

class order {

   function __construct() {
      session_start();

      $this->db = new mysqlcrud();
      $this->db->connect();
      $this->sms = new smsru();
   }

   public function addToDB( $phone, $fio, $message, $itemID = false ) {
      // если пользователь авторизован, то вставляем id пользователя
      if (isset($_SESSION['id'])) $insert['user'] = $_SESSION['id'];

      // если пользователь ввел примечание - добавляем его
      if (!empty($message)) $insert['note_usr'] = $this->db->escapeString($message);

      // если заказ сделан в 1 клик - подставляем номер позиции
      if (!$itemID) {
         $cart = $_SESSION['shoppingcart'];
         unset($_SESSION['shoppingcart']);
      } else
         $cart = [$itemID => ['qty' => 1]];

      $insert['order_id']  = $return['order_id']   = generate::orderID();
      $insert['date_post']                         = date::now();
      $insert['fio']       = $return['fio']        = $fio;
      $insert['phone']     = $return['phone']      = phone::format($phone);
      $insert['details']                           = $this->db->escapeString(info::userDetails());
      $insert['cart']                              = $this->db->escapeString($this->cartToString($cart));

      if ($this->db->insert('orders',$insert)) {
         $return['title'] = 'Заказ оформлен';
         $return['ok'] = sprintf(lang::AJAX_1CLC_ORDER_NUM, $insert['order_id']);

			$return['orderID'] = $insert['order_id'];

			foreach ($cart as $id => $array) {
				$this->db->sql('
		         SELECT
		            catalog.cat_id,
						catalog.name,
						catalog.price,
						brands.brand_clean AS brand
		         FROM catalog
					LEFT JOIN
						brands
					ON
						catalog.brand_id = brands.brand_id
		         WHERE
		            1c_id = '.$id
		      );
				$product = $this->db->getResult();

				$data = $product[0];
				$data['brand']		= mb_strtoupper($data['brand'], 'utf-8');
				$data['name']		= $data['brand'] . ' ' . $data['name'];
				$data['category'] = prod::categoryPath($data['cat_id']);

				$elements[] = json_encode([
					'id'			=> (string)$id,
					"name"		=> $data['name'],
					"price"		=> $data['price'],
					"brand"		=> $data['brand'],
					"category"	=> $data['category'],
					"quantity" 	=> $array['qty']
				], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}

			implode(',',$elements);

			$return['push'] = '{"ecommerce":{"purchase":{"actionField":{"id":"'.$insert['order_id'].'","goal_id":"12457041"},"products":['.implode(',',$elements).']}}}';

			$this->smsToAdmin($insert['order_id'],$insert['fio'],$insert['phone']);
         $this->log($return['order_id'],0,(!empty($message) ? $insert['note_usr'] : null));
         //$return['message'] = 'SQL: ' . $this->db->getSql();
      } else {
         $return['title'] = 'Ошибка';
         $return['error'] = lang::AJAX_1CLC_ORDER_ERR;
      }

      return $return;
   }

   public function smsToAdmin($orderID, $fio, $phone) {
      // Извлекаем список получателей из БД
      $this->db->sql('SELECT phone FROM users WHERE permissions = 1');
      $result = $this->db->getResult();

      foreach ($result as $admin) {
         $to[] = $admin['phone'];
      }
      $to = implode(',',$to);

      $message = 'Заказ: '.$orderID.'\n'.$fio.'\n+'.$phone;

      $this->sms->send($to,$message,SMSRU_SENDER_2);

      return $this->sms->getResponseMessage();
   }

   public function log( $order_id, $status = 'NULL', $note = false ) {
      $operator = ($status != 0 ? $_SESSION['id'] : 0);

      $sql = $this->db->sql('
         INSERT INTO orders_log
            (order_id, status, operator, note)
         VALUES
            ("'.$order_id.'",'.$status.','.$operator.',"'.$note.'")
      ');

      if ($sql) {
         // информируем покупателя об изменении статуса заказа
         $this->db->sql('
            SELECT
               orders.fio,
               orders.phone,
               orders_status.sms
            FROM orders
            LEFT JOIN
					orders_status
				ON
					orders.status = orders_status.id
            WHERE order_id = "'.$order_id.'"
         ');
         $res = $this->db->getResult();

         $fio      = $res[0]['fio'];
         $phone    = $res[0]['phone'];
         $sms_spr  = $res[0]['sms'];

         if ($sms_spr != null) {
            $sms = ($status != 99 ? sprintf($sms_spr,$fio,$order_id) : sprintf($sms_spr,$fio));
            //if ($phone == '79155173925')
            $this->sms->send($phone,$sms);
         }
      }

      return $sql;
   }

   private function cartToString($arr) {
      $code = null;
      $count = count($arr);
      $last = 0;
      foreach ($arr as $id => $element) {
         $this->db->sql('SELECT price FROM catalog WHERE 1c_id = '.$id);
         $result = $this->db->getResult();
         $code .= $id.':'.$result[0]['price'].':'.$element['qty'];
         if (++$last !== $count)
            $code .= '|';
      }

      return $code;
   }

}
