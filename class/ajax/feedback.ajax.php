<?php
// SPR-4 Автоподключение классов
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
include_once(DOCUMENT_ROOT.'/class/core/autoload.inc.php');

use \db\mysql\mysqlcrud;
use \template\template;

use \helper\info;

class feedback {

   function __construct() {
      if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])
         && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
         && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

            session_start();

            $this->db = new mysqlcrud();
            $this->content = new template();

            $browser = info::getBrowser();

            $category = htmlspecialchars($_POST['category']);
            $subject = htmlspecialchars($_POST['subject']);
            $name = $info['name'] = htmlspecialchars($_POST['name']);
            $email = $info['email'] = htmlspecialchars($_POST['email']);
            $info['browser'] = @$browser['platform'].' '.@$browser['name'].' '.@$browser['version'];
            $info['ip_addr'] = $_SERVER['REMOTE_ADDR'];
            $info['referer'] = $_SERVER['HTTP_REFERER'];
            $message = $this->content->design('feedback','issue-head',$info);
            $message .= nl2br(htmlspecialchars($_POST['message']));

            switch ($category) {
               case 1:
               case 2:
               case 3:
               case 4:
               case 9:
                  $assignee = 5;
                  break;
               case 10:
                  $assignee = 1;
                  break;
               default:
                  $category = 12;
            }

            $response = $this->sendFeedback($category,$name,$email,$subject,$message,$assignee);

            $return['ok'] = $response['ok'];
            $return['message'] = @$response['message'];

            echo json_encode($return);
      } else
         echo 'И что мы здесь забыли?';
   }

   private function sendFeedback($category,$name,$email,$subject,$message,$assignee = 1,$project = 1) {
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL,"http://bug.homy.su/api/issues.php");
      curl_setopt($ch, CURLOPT_USERPWD, BUGTRACKER_API . ':');
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(array(
                  'subject'      => $subject,
                  'description'  => $message,
                  'project'      => $project,
                  'category'     => $category,
                  'assignee'     => $assignee
            )));/**/

      // получаем ответ сервера ...
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      $server_output = curl_exec ($ch);
      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      curl_close ($ch);

      if ($http_code == "201") {
         $return['ok'] = 1;
      } else {
         $return['ok'] = 0;
      }

      return $return;
   }

}

new feedback();
