<?php
namespace sms;

/**
* Отправка СМС сообщений
*
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 0.1
*/

use \db\mysql\mysqlcrud;

use \tmp\master;
use \product\prod;

class smsru {
   protected $_apiId = SMSRU_API_ID;
   protected $_responseCode = null;
   protected $_lastAction = null;

   const HOST    = 'http://sms.ru/';
   const SEND    = 'sms/send?';
   const STATUS  = 'sms/status?';
   const BALANCE  = 'my/balance?';
   const LIMIT    = 'my/limit?';

   protected $_responseCodeTranstale = array(
      'send'    =>  array(
         '100'    =>  'Сообщение принято к отправке',
         '200'    =>  'Неправильный api_id',
         '201'    =>  'Не хватает средств на лицевом счету',
         '202'    =>  'Неправильно указан получатель',
         '203'    =>  'Нет текста сообщения',
         '204'    =>  'Имя отправителя не согласовано с администрацией',
         '205'    =>  'Сообщение слишком длинное (превышает 5 СМС)',
         '206'    =>  'Превышен дневной лимит на отправку сообщений',
         '207'    =>  'На этот номер нельзя отправлять сообщения',
         '208'    =>  'Параметр time указан неправильно',
         '210'    =>  'Используется GET, где необходимо использовать POST',
         '211'    =>  'Метод не найден',
         '220'    =>  'Сервис временно недоступен, попробуйте чуть позже.',
      ),
      'status'  =>  array(
         '-1'    =>  'Сообщение не найдено',
         '100'    =>  'Сообщение находится в очереди',
         '101'    =>  'Сообщение передается оператору',
         '102'    =>  'Сообщение отправлено (в пути)',
         '103'    =>  'Сообщение доставлено',
         '104'    =>  'Не может быть доставлено: время жизни истекло',
         '105'    =>  'Не может быть доставлено: удалено оператором',
         '106'    =>  'Не может быть доставлено: сбой в телефоне',
         '107'    =>  'Не может быть доставлено: неизвестная причина',
         '108'    =>  'Не может быть доставлено: отклонено',
         '200'    =>  'Неправильный api_id',
         '210'    =>  'Используется GET, где необходимо использовать POST',
         '211'    =>  'Метод не найден',
         '220'    =>  'Сервис временно недоступен, попробуйте чуть позже',
      ),
      'balance'  =>  array(
         '100'    =>  'Запрос выполнен',
         '200'    =>  'Неправильный api_id',
         '210'    =>  'Используется GET, где необходимо использовать POST',
         '211'    =>  'Метод не найден',
         '220'    =>  'Сервис временно недоступен, попробуйте чуть позже.',
      ),
      'limit'  =>  array(
         '100'    =>  'Запрос выполнен',
         '200'    =>  'Неправильный api_id',
         '210'    =>  'Используется GET, где необходимо использовать POST',
         '211'    =>  'Метод не найден',
         '220'    =>  'Сервис временно недоступен, попробуйте чуть позже.',
      ),
   );

   public function __construct() {
      $this->db = new mysqlcrud();
      $this->db->connect();
   }

  /**
   *
   * @param string $to телефон: 11 знаков. например 79060000000
   * @param string $text текст сообщение
   * @param string $from отправитель
   * @return string id сообщения
   */
   public function send($to, $text, $from = null, $test = null) {
      $apiParams['api_id']  = $this->_apiId;
      $apiParams['to']      = $to;
      $apiParams['text']    = urlencode($text);
      if ($from)
         $apiParams['from'] = urlencode($from);
      if ($test)
         $apiParams['test'] = 1;

      $url = urldecode(http_build_query($apiParams));

      $url = self::HOST.self::SEND.$url;
      $body = file_get_contents($url);
      @list($code,$smsId) = explode("\n", $body);
      $this->_lastAction    = 'send';
      $this->_responseCode  = $code;

      return $smsId;/**/
   }

  /**
   *
   * @param string $id id сообщения
   * @return string код статуса сообщения.
   */
   public function status($id) {
      $apiParams['api_id'] = $this->_apiId;
      $apiParams['id'] = $id;
      $url = self::HOST.self::STATUS.http_build_query($apiParams);
      $body = file_get_contents($url);
      $status = $body;
      $this->_lastAction    = 'status';
      $this->_responseCode  = $status;

      return $status;
   }

  /**
   *
   * @return string Баланс в рублях
   */
   public function balance() {
      $apiParams['api_id'] = $this->_apiId;
      $url = self::HOST.self::BALANCE.http_build_query($apiParams);
      $body = file_get_contents($url);
      @list($code,$balance) = explode("\n", $body);
      $this->_lastAction    = 'balance';
      $this->_responseCode  = $code;

      return $balance;
   }

  /**
   *
   * @return int количество оставшихся сообщений
   */
   public function limit() {
      $apiParams['api_id'] = $this->_apiId;
      $url = self::HOST.self::LIMIT.http_build_query($apiParams);
      $body = file_get_contents($url);
      @list($code,$count,$limit) = explode("\n", $body);
      $this->_lastAction    = 'limit';
      $this->_responseCode  = $code;

      return (int)($count - $limit);
   }

  /**
   *
   * @return string код результата выполнения последней операции
   */
   public function getResponseCode() {
      return $this->_responseCode;
   }

  /**
   *
   * @return string расшифровка кода результата выполнения последней операции
   */
   public function getResponseMessage() {
      if ($this->_lastAction)
         return $this->_responseCodeTranstale[$this->_lastAction][$this->getResponseCode()];
      else
         return 'Нет данных для возврата сообщения';
   }

}
