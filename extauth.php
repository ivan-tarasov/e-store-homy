<?php

use \template\template;
use \debug\dBug;

/**
* Обработка ответов серверов социальных сетей
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 0.1
*/
class extauth {

   public function __construct() {
      //$this->db = new mysqlcrud();
      //$this->db->connect();
      $this->content = new template();
      $this->callback = HTTP_PROTOCOL.HTTP_HOST.EXTAUTH_REDIRECT;
   }
   function index() {
      header('Location: /login/');
   }

   private function add( $social, $userInfo ) {
      echo $social;
      new dBug($userInfo);
   }

   /**
   * Авторизация через API vk.com
   * Настройки приложения: https://vk.com/apps?act=manage
   * @return   void
   */
   public function vk() {
      if (isset($_GET['code'])) {
         $result = false;
         $params = array(
            'client_id'       => AUTH_VK_ID,
            'client_secret'   => AUTH_VK_SECRET,
            'code'            => $_GET['code'],
            'redirect_uri'    => $this->callback.__FUNCTION__
         );

         $token = json_decode(@file_get_contents('https://oauth.vk.com/access_token' . '?' . urldecode(http_build_query($params))), true);

         if (isset($token['access_token'])) {
            $params = array(
               'uids'         => $token['user_id'],
               'fields'       => 'uid,first_name,last_name,bdate,photo_200',
               'access_token' => $token['access_token']
            );

            $userInfo = json_decode(@file_get_contents('https://api.vk.com/method/users.get' . '?' . urldecode(http_build_query($params))), true);
            if (isset($userInfo['response'][0]['uid'])) {
               $userInfo = $userInfo['response'][0];
               $result = true;
            }
         }

         $return = [
            'id'        => $userInfo['uid'],
            'firstname' => $userInfo['first_name'],
            'lastname'  => $userInfo['last_name'],
            'avatar'    => $userInfo['photo_200']
         ];

         if ($result) {
            $this->add(__FUNCTION__,$return);
         }
      } else
         die('Дратути (:');
   }

   /**
   * Авторизация через API Facebook
   * Настройки приложения: https://developers.facebook.com/apps
   * @return   void
   */
   public function fb() {
      if (isset($_GET['code'])) {
         $result = false;

         $params = array(
            'client_id'     => AUTH_FB_ID,
            'client_secret' => AUTH_FB_SECRET,
            'code'          => $_GET['code'],
            'redirect_uri'  => $this->callback.__FUNCTION__
         );

         $url = 'https://graph.facebook.com/oauth/access_token';

         $tokenInfo = null;
         parse_str(@file_get_contents($url . '?' . http_build_query($params)), $tokenInfo);

         if (count($tokenInfo) > 0 && isset($tokenInfo['access_token'])) {
            $params = [
               'fields'       => 'first_name,last_name,email,picture',
               'access_token' => $tokenInfo['access_token']
            ];

            $userInfo = json_decode(@file_get_contents('https://graph.facebook.com/me' . '?' . urldecode(http_build_query($params))), true);

            if (isset($userInfo['id'])) {
               $userInfo = $userInfo;
               $result = true;
            }
         }

         $return = [
            'id'        => $userInfo['id'],
            'firstname' => $userInfo['first_name'],
            'lastname'  => $userInfo['last_name'],
            'avatar'    => $userInfo['picture']['data']['url']
         ];

         if ($result) {
            $this->add(__FUNCTION__,$return);
         }
      } else
         die('Дратути (:');
   }

   /**
   * Авторизация через API Яндекс.Паспорт
   * Настройки приложения: https://oauth.yandex.ru
   * @return   void
   */
   public function yandex() {
      if (isset($_GET['code'])) {
         $result = false;

         $params = array(
            'client_id'     => AUTH_YANDEX_ID,
            'client_secret' => AUTH_YANDEX_SECRET,
            'grant_type'    => 'authorization_code',
            'code'          => $_GET['code']
         );

         $url = 'https://oauth.yandex.ru/token';

         $curl = curl_init();
         curl_setopt($curl, CURLOPT_URL, $url);
         curl_setopt($curl, CURLOPT_POST, 1);
         curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
         $result = curl_exec($curl);
         curl_close($curl);

         $tokenInfo = json_decode($result, true);

         if (isset($tokenInfo['access_token'])) {
            $params = array(
               'format'       => 'json',
               'oauth_token'  => $tokenInfo['access_token']
            );

            $userInfo = json_decode(@file_get_contents('https://login.yandex.ru/info' . '?' . urldecode(http_build_query($params))), true);
            if (isset($userInfo['id'])) {
               $userInfo = $userInfo;
               $result = true;
            }
         }

         $return = [
            'id'        => $userInfo['id'],
            'firstname' => $userInfo['first_name'],
            'lastname'  => $userInfo['last_name'],
            'avatar'    => 'https://avatars.yandex.net/get-yapic/'.$userInfo['default_avatar_id'].'/islands-200'
         ];

         if ($result) {
            $this->add(__FUNCTION__,$return);
         }
      } else
         die('Дратути (:');
   }

   /**
   * Авторизация через twitter API
   * Настройки приложения: https://apps.twitter.com
   * @return   void
   */
   public function twitter() {
      if (!empty($_GET['oauth_token']) && !empty($_GET['oauth_verifier'])) {
         define('ACCESS_TOKEN_URL', 'https://api.twitter.com/oauth/access_token');
         define('ACCOUNT_DATA_URL', 'https://api.twitter.com/1.1/users/show.json');
         define('URL_SEPARATOR', '&');

         // готовим подпись для получения токена доступа
         $oauth_nonce = md5(uniqid(rand(), true));
         $oauth_timestamp = time();
         $oauth_token = $_GET['oauth_token'];
         $oauth_verifier = $_GET['oauth_verifier'];

         $oauth_base_text = "GET&";
         $oauth_base_text .= urlencode(ACCESS_TOKEN_URL)."&";

         $params = array(
            'oauth_consumer_key=' . AUTH_TWITTER_ID . URL_SEPARATOR,
            'oauth_nonce=' . $oauth_nonce . URL_SEPARATOR,
            'oauth_signature_method=HMAC-SHA1' . URL_SEPARATOR,
            'oauth_token=' . $oauth_token . URL_SEPARATOR,
            'oauth_timestamp=' . $oauth_timestamp . URL_SEPARATOR,
            'oauth_verifier=' . $oauth_verifier . URL_SEPARATOR,
            'oauth_version=1.0'
         );

         $key = AUTH_TWITTER_SECRET . URL_SEPARATOR;
         $oauth_base_text = 'GET' . URL_SEPARATOR . urlencode(ACCESS_TOKEN_URL) . URL_SEPARATOR . implode('', array_map('urlencode', $params));
         $oauth_signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));

         // получаем токен доступа
         $params = array(
            'oauth_nonce=' . $oauth_nonce,
            'oauth_signature_method=HMAC-SHA1',
            'oauth_timestamp=' . $oauth_timestamp,
            'oauth_consumer_key=' . AUTH_TWITTER_ID,
            'oauth_token=' . urlencode($oauth_token),
            'oauth_verifier=' . urlencode($oauth_verifier),
            'oauth_signature=' . urlencode($oauth_signature),
            'oauth_version=1.0'
         );
         $url = ACCESS_TOKEN_URL . '?' . implode('&', $params);

         $response = @file_get_contents($url);
         parse_str($response, $response);

         // формируем подпись для следующего запроса
         $oauth_nonce = md5(uniqid(rand(), true));
         $oauth_timestamp = time();

         $oauth_token = $response['oauth_token'];
         $oauth_token_secret = $response['oauth_token_secret'];
         $screen_name = $response['screen_name'];

         $params = array(
            'oauth_consumer_key=' . AUTH_TWITTER_ID . URL_SEPARATOR,
            'oauth_nonce=' . $oauth_nonce . URL_SEPARATOR,
            'oauth_signature_method=HMAC-SHA1' . URL_SEPARATOR,
            'oauth_timestamp=' . $oauth_timestamp . URL_SEPARATOR,
            'oauth_token=' . $oauth_token . URL_SEPARATOR,
            'oauth_version=1.0' . URL_SEPARATOR,
            'screen_name=' . $screen_name
         );
         $oauth_base_text = 'GET' . URL_SEPARATOR . urlencode(ACCOUNT_DATA_URL) . URL_SEPARATOR . implode('', array_map('urlencode', $params));

         $key = AUTH_TWITTER_SECRET . '&' . $oauth_token_secret;
         $signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));

         // получаем данные о пользователе
         $params = array(
            'oauth_consumer_key=' . AUTH_TWITTER_ID,
            'oauth_nonce=' . $oauth_nonce,
            'oauth_signature=' . urlencode($signature),
            'oauth_signature_method=HMAC-SHA1',
            'oauth_timestamp=' . $oauth_timestamp,
            'oauth_token=' . urlencode($oauth_token),
            'oauth_version=1.0',
            'screen_name=' . $screen_name
         );

         $url = ACCOUNT_DATA_URL . '?' . implode(URL_SEPARATOR, $params);

         $response = @file_get_contents($url);
         $userInfo = json_decode($response, true);

         $return = [
            'id'        => $userInfo['id'],
            'firstname' => $userInfo['name'],
            'lastname'  => null,
            'avatar'    => str_replace('normal','200x200',$userInfo['profile_image_url'])
         ];

         if ($userInfo) {
            $this->add(__FUNCTION__,$return);
         }
      } else
         die('Дратути (:');
   }

   /**
   * Авторизация через API Google
   * Настройки приложения: https://console.developers.google.com/iam-admin/projects
   * @return   void
   */
   public function google() {
      if (isset($_GET['code'])) {
         $result = false;

         $params = array(
            'client_id'     => AUTH_GOOGLE_ID,
            'client_secret' => AUTH_GOOGLE_SECRET,
            'redirect_uri'  => $this->callback.__FUNCTION__,
            'grant_type'    => 'authorization_code',
            'code'          => $_GET['code']
         );

         $url = 'https://accounts.google.com/o/oauth2/token';

         $curl = curl_init();
         curl_setopt($curl, CURLOPT_URL, $url);
         curl_setopt($curl, CURLOPT_POST, 1);
         curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
         $result = curl_exec($curl);
         curl_close($curl);
         $tokenInfo = json_decode($result, true);

         if (isset($tokenInfo['access_token'])) {
            $params['access_token'] = $tokenInfo['access_token'];

            $userInfo = json_decode(file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo' . '?' . urldecode(http_build_query($params))), true);
            if (isset($userInfo['id'])) {
               $userInfo = $userInfo;
               $result = true;
            }
         }

         $return = [
            'id'        => $userInfo['id'],
            'firstname' => $userInfo['given_name'],
            'lastname'  => $userInfo['family_name'],
            'avatar'    => $userInfo['picture']
         ];

         if ($userInfo) {
            $this->add(__FUNCTION__,$return);
         }
      } else
         die('Дратути (:');
   }

}
