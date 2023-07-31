<?php
namespace telegram;

use \debug\dBug;

class telegram {
	public $token;

	const BASE_API_URL = 'https://api.telegram.org/bot';

   /*public function __construct() {
      $this->token = cfg::BOT_ID.':'.cfg::BOT_TOKEN;
   }/**/

   /**
   * @param String $hookUrl - адрес на нашем сервере, куда будут приходить обновления
   * @return mixed|null
   */
   public function setWebHook($hookUrl) {
      return $this->sendPost('setWebHook', ['url' => $hookUrl]);
   }

   /**
   * Отправка запроса к Telegram API
   * @param    string   $method     Метод, к которому идет обращение
   * @param    array    $params     Параметры для доступа к методу
   * @return   mixed
   */
   public function apiRequest($method, $params = []) {
      if(!is_array($params)) {
         $params = array() ;
      }

      new dBug($params);

      $url = $this->buildUrl($method).'?'.http_build_query($params);

      $data = file_get_contents($url);
      return json_decode($data, true);
   }



   public function ReplyKeyboardHide() {
      $reply_markup = ['hide_keyboard' => true];
      $reply_markup = json_encode($reply_markup);

      return $reply_markup;
   }

   /**
   * @param String $methodName - имя метода в API, который вызываем
   * @param array $data - параметры, которые передаем, необязательное поле
   * @return mixed|null
   */
   private function sendPost($methodName, $data = []) {
      $result = null;

		if (is_array($data)) {
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $this->buildUrl($methodName));
			curl_setopt($ch,CURLOPT_POST, count($data));
			curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($data));
			$result = curl_exec($ch);
			curl_close($ch);
		}

		return $result;
	}

   /**
   * @param String $methodName - имя метода в API, который вызываем
   * @return string - Софрмированный URL для отправки запроса
   */
	private function buildUrl($methodName) {
      return self::BASE_API_URL.$this->token.'/'.$methodName;
	}

}
