<?php
// SPR-4 Автоподключение классов
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
include_once(DOCUMENT_ROOT.'/class/core/autoload.inc.php');

use \db\mysql\mysqlcrud;

use \order\order;
use \sms\smsru;

class order_ajax {

   function __construct() {
      if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])
         && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
         && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

         //session_start();

         $this->db = new mysqlcrud();
         $this->db->connect();
         $this->sms = new smsru();
         $this->order = new order();

         $this->order_id   = $_POST['order_id'];
         $this->action     = $_POST['action'];
         $this->note       = ($_POST['note'] != 'true' ? $_POST['note'] : null);

         switch ($this->action) {
            case 'change':
               $return = $this->act_change($_POST['status']);
               break;
            case 'delete':
               $return = $this->act_delete();
               break;
            default:
               echo 'def';
         }

         echo json_encode($return);

      } else
         echo 'И что мы здесь забыли?';
   }

   private function act_change( $status ) {
      $this->db->sql('
         SELECT
            status,phone,fio
         FROM orders
         WHERE
            order_id = "'.$this->order_id.'"
      ');
      $res = $this->db->getResult();
      $phone = $res[0]['phone'];
      $fio   = $res[0]['fio'];

      $add_sql = null;

      if (count($res) != 0) {
         if ($status == 99) $add_sql = ', date_done = NOW()';

         $sql = $this->db->sql('
            UPDATE orders
            SET status = '.$status.$add_sql.'
            WHERE order_id = "'.$this->order_id.'"
         ');

         if ($sql) {
            if ($this->order->log($this->order_id,$status,$this->note)) {
               // информируем покупателя об изменении статуса заказа
               /*$this->db->sql('SELECT name,sms FROM orders_status WHERE id = '.$status);
               $res = $this->db->getResult();
               if ($res[0]['sms'] != null) {
                  $sms = ($sprintf ? sprintf($res[0]['sms'],$fio,$this->order_id) : sprintf($res[0]['sms'],$fio));
                  //if ($phone == '79155173925')
                  $this->sms->send($phone,$sms);
               }/**/
               $out['ok'] = 'Статус заказа успешно обновлен';
            } else {
               $out['error'] = 'Не удалось обновить лог заказов.';
            }
         } else {
            $result = $this->db->getResult();
            $result = $result[0];
            $out['error'] = nl2br($result);
         }
      } else
         $out['error'] = 'Заказа с таким номером не обнаружено';/**/

      return $out;
   }

   private function act_delete() {
      $this->db->sql('SELECT * FROM orders WHERE order_id = "'.$this->order_id.'"');
      $res = $this->db->getResult();

      if (count($res) == 0)
         $out['error'] = 'Заказ с таким номером не найден.';
      else {
         if ($this->db->sql('UPDATE orders SET deleted = 1 WHERE order_id = "'.$this->order_id.'"')) {
            if ($this->order->log($this->order_id))
               $out['ok'] = 'Заказ успешно удален';
         } else {
            $result = $this->db->getResult();
            $result = $result[0];
            $out['error'] = 'Не удалось удалить заказ. Ошибка: '.$result;
         }
      }

      return $out;
   }

}

new order_ajax();
