<?php
namespace helper;

/**
* Служебная информация
*
* @method  void     log(string $script, string $msg)  - лог выполнения скриптов (beta)
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 0.2
*/

class info {

   static function cityByIP() {
      $ip = self::userRealIP();
      $query = file_get_contents(SYPEXGEO_API.$ip);
      $query = json_decode($query,true);

      $lang = 'ru';

      if($query && !$query['error']) {
         $return['ip']      = $query['ip'];
         $return['country'] = $query['country']['name_'.$lang];
         $return['region']  = $query['region']['name_'.$lang];
         $return['city']    = $query['city']['name_'.$lang];
         $return['lon']     = $query['city']['lon'];
         $return['lat']     = $query['city']['lat'];

         $return = json_encode($return,JSON_UNESCAPED_UNICODE);

         return $return;
      }
   }

   static function yapiMaps($lon,$lat,$width = 400,$height = 200) {
      $return = '<img src="'.YA_MAPS_STATIC.'?ll='.$lon.','.$lat.'&z=12&l=map&size='.$width.','.$height.'" />';

      return $return;
   }

   static function getBrowser($u_agent = false) {
      if(!$u_agent)
         $u_agent = $_SERVER['HTTP_USER_AGENT'];
      $bname = 'Unknown';
      $platform = 'Unknown';
      $version= "";

      // Платформа
      if (preg_match('/linux/i', $u_agent)) {
         $platform = 'linux';
      } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
         $platform = 'apple';
      } elseif (preg_match('/windows|win32/i', $u_agent)) {
         $platform = 'windows';
      }

      // Useragent
      if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
         $bname = 'Internet Explorer';
         $ub = "MSIE";
         $ub_short = "internet-explorer";
      } elseif(preg_match('/Firefox/i',$u_agent)) {
         $bname = 'Mozilla Firefox';
         $ub = "Firefox";
         $ub_short = "firefox";
      } elseif(preg_match('/Chrome/i',$u_agent)) {
         $bname = 'Google Chrome';
         $ub = "Chrome";
         $ub_short = "chrome";
      } elseif(preg_match('/Safari/i',$u_agent)) {
         $bname = 'Apple Safari';
         $ub = "Safari";
         $ub_short = "safari";
      } elseif(preg_match('/Opera/i',$u_agent)) {
         $bname = 'Opera';
         $ub = "Opera";
         $ub_short = "opera";
      } elseif(preg_match('/Netscape/i',$u_agent)) {
         $bname = 'Netscape';
         $ub = "Netscape";
         $ub_short = "netscape";
      }

      // Версия браузера
      $known = array('Version', $ub, 'other');
      $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
      if (!preg_match_all($pattern, $u_agent, $matches)) {
        // у нас нет номера версии, продолжаем
      }

      // смотрим сколько у нас есть
      $i = count($matches['browser']);
      if ($i != 1) {
         //we will have two since we are not using 'other' argument yet
         //see if version is before or after the name
         if (strripos($u_agent,"Version") < strripos($u_agent,$ub)) {
            $version= $matches['version'][0];
         } else {
            $version= $matches['version'][1];
         }
      } else {
         $version= $matches['version'][0];
      }

      // проверяем есть ли номер
      if ($version==null || $version=="") {$version="?";}

      $return = array(
         'userAgent' => $u_agent,
         'name'      => $bname,
         'short'     => $ub_short,
         'version'   => $version,
         'platform'  => $platform,
         'pattern'   => $pattern
      );
      $return = json_encode($return);

      return $return;
   }

   static function userRealIP() {
      $ip = '';
      if (getenv('HTTP_CLIENT_IP'))
         $ip = getenv('HTTP_CLIENT_IP');
      else if(getenv('HTTP_X_FORWARDED_FOR'))
         $ip = getenv('HTTP_X_FORWARDED_FOR');
      else if(getenv('HTTP_X_FORWARDED'))
         $ip = getenv('HTTP_X_FORWARDED');
      else if(getenv('HTTP_FORWARDED_FOR'))
         $ip = getenv('HTTP_FORWARDED_FOR');
      else if(getenv('HTTP_FORWARDED'))
         $ip = getenv('HTTP_FORWARDED');
      else if(getenv('REMOTE_ADDR'))
         $ip = getenv('REMOTE_ADDR');
      else
         $ip = 'UNKNOWN';

      return $ip;
   }

   static function userDetails() {
      $location = json_decode(self::cityByIP(),true);
      $system   = json_decode(self::getBrowser(),true);

      $return['location'] = $location;
      $return['system'] = array(
                              'platform' => $system['platform'],
                              'browser'  => $system['name'].' '.$system['version'],
                              'short'    => $system['short']
                           );

      return json_encode($return,JSON_UNESCAPED_UNICODE);
   }

   static function axistence($catalog_upd,$stock) {
      /*<label class="available fa-lg">
         <i class="fa fa-circle"></i>
         <i class="fa fa-circle"></i>
         <i class="fa fa-circle"></i>
         <i class="fa fa-circle-o"></i>
         <i class="fa fa-circle-o"></i>
      </label>
      <span class="exist">||||||||||</span>
      <span class="{available_bool}available">  {prod_stock}</span>/**/

      $return = ($catalog_upd > LAST_CATALOG_UPDATE ? 'в наличие '.$stock : 'под заказ');



      return $return;
   }

}
