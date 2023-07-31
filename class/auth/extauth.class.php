<?php
namespace auth;

use \db\mysql\mysqlcrud;
use \template\template;

use \debug\dBug;

/**
* Формирование блока кнопок для авторизации через социальные сети
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 0.2
*/
class extauth {

   function __construct() {
      $this->content = new template();
      $this->callback = HTTP_PROTOCOL.HTTP_HOST.EXTAUTH_REDIRECT;
   }

   /**
   * Генератор кнопок для входа через социальные сети
   * @return   html-код
   */
   public function buttons( $socials = 'vk' ) {
      $code = null;

      $socials = explode(',', $socials);

      foreach ($socials as $social) {
         $code .= $this->$social();
      }

      return $code;
   }

   /**
   * Кнопка для VK
   * @return   html-код
   */
   private function vk() {
      $params = array(
          'client_id'     => AUTH_VK_ID,
          'redirect_uri'  => $this->callback.__FUNCTION__,
          'response_type' => 'code'
      );

      $params['url']    = AUTH_VK_URL . '?' . urldecode(http_build_query($params));
      $params['title']  = 'ВКонтакте';
      $params['social'] = 'vk';
      $params['icon']   = 'vk';

      return $this->content->design('auth','extauth/button',$params);
   }

   /**
   * Кнопка для Facebook
   * @return   html-код
   */
   private function fb() {
      $params = array(
         'client_id'     => AUTH_FB_ID,
         'redirect_uri'  => $this->callback.__FUNCTION__,
         'response_type' => 'code',
         'scope'         => 'email'
      );

      $params['url']    = AUTH_FB_URL . '?' . urldecode(http_build_query($params));
      $params['title']  = 'Facebook';
      $params['social'] = 'facebook';
      $params['icon']   = 'facebook';

      return $this->content->design('auth','extauth/button',$params);
   }

   /**
   * Кнопка для Яндекс.Паспорт
   * @return   html-код
   */
   private function yandex() {
      $params = array(
          'response_type' => 'code',
          'client_id'     => AUTH_YANDEX_ID,
          'display'       => 'popup'
      );

      $params['url']    = AUTH_YANDEX_URL . '?' . urldecode(http_build_query($params));
      $params['title']  = 'Яndex';
      $params['social'] = 'yandex';
      $params['icon']   = 'yahoo';

      return $this->content->design('auth','extauth/button',$params);
   }

   /**
   * Кнопка для twitter
   * @return   html-код
   */
   private function twitter() {
      define('AUTHORIZE_URL', 'https://api.twitter.com/oauth/authorize');
      define('URL_SEPARATOR', '&');

      // формируем подпись для получения токена доступа
      $oauth_nonce = md5(uniqid(rand(), true));
      $oauth_timestamp = time();

      $params = array(
          'oauth_callback=' . urlencode($this->callback.__FUNCTION__) . URL_SEPARATOR,
          'oauth_consumer_key=' . AUTH_TWITTER_ID . URL_SEPARATOR,
          'oauth_nonce=' . $oauth_nonce . URL_SEPARATOR,
          'oauth_signature_method=HMAC-SHA1' . URL_SEPARATOR,
          'oauth_timestamp=' . $oauth_timestamp . URL_SEPARATOR,
          'oauth_version=1.0'
      );

      $oauth_base_text = implode('', array_map('urlencode', $params));
      $key = AUTH_TWITTER_SECRET . URL_SEPARATOR;
      $oauth_base_text = 'GET' . URL_SEPARATOR . urlencode(AUTH_TWITTER_URL) . URL_SEPARATOR . $oauth_base_text;
      $oauth_signature = base64_encode(hash_hmac('sha1', $oauth_base_text, $key, true));


      // получаем токен запроса
      $params = array(
          URL_SEPARATOR . 'oauth_consumer_key=' . AUTH_TWITTER_ID,
          'oauth_nonce=' . $oauth_nonce,
          'oauth_signature=' . urlencode($oauth_signature),
          'oauth_signature_method=HMAC-SHA1',
          'oauth_timestamp=' . $oauth_timestamp,
          'oauth_version=1.0'
      );
      $url = AUTH_TWITTER_URL . '?oauth_callback=' . urlencode($this->callback.__FUNCTION__) . implode('&', $params);

      $response = file_get_contents($url);
      parse_str($response, $response);

      $oauth_token = $response['oauth_token'];
      $oauth_token_secret = $response['oauth_token_secret'];

      $params['url']    = AUTHORIZE_URL . '?oauth_token=' . $oauth_token;
      $params['title']  = 'twitter';
      $params['social'] = 'twitter';
      $params['icon']   = 'twitter';

      return $this->content->design('auth','extauth/button',$params);
   }

   /**
   * Кнопка для Google
   * @return   html-код
   */
   private function google() {
      $params = array(
          'redirect_uri'  => $this->callback.__FUNCTION__,
          'response_type' => 'code',
          'client_id'     => AUTH_GOOGLE_ID,
          'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile'
      );

      $params['url']    = AUTH_GOOGLE_URL . '?' . urldecode(http_build_query($params));
      $params['title']  = 'Google';
      $params['social'] = 'google';
      $params['icon']   = 'google';

      return $this->content->design('auth','extauth/button',$params);
   }

}
