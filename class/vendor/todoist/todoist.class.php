<?php

namespace vendor\todoist;
use cfg; // временно
use generate;

/**
* Взаимодействие с API сервиса Todoist
*
* @method  string   uuid()   - генерация уникального UUID
*
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 0.1
*/

class todoist {

   public function __construct() {
      $this->token = cfg::TODOIST_API_TOKEN;
      $this->url = cfg::TODOIST_API_URL;
      $this->project = cfg::TODOIST_PROJECT;
      $this->label = cfg::TODOIST_LABEL;
   }

   /**
   * Добавление нового задания с комментарием в Todoist
   *
   * @return   bool
   */
   public function add($params) {
      // добавляем новое задание
      $temp_id = generate::uuid();
      $uuid    = generate::uuid();
      $content = sprintf( "[%s] %s (%s)", $params['order'], $params['fio'], $params['phone'] );
      $todo_add = $this->apiRequest('item_add', [
         'temp_id'         => $temp_id,
         'uuid'            => $uuid,
         'args'            => [
            'content'      => $content,
            'project_id'   => $this->project,
            'date_string'  => 'today',
            'labels'       => [$this->label]
         ]
      ]);/**/

      $todo_add['sync_status'][$uuid] = 'ok';

      // добавляем комментарий к заданию
      if ($todo_add['sync_status'][$uuid] == 'ok') {
         $item_id = $todo_add['temp_id_mapping'][$temp_id];
         //$item_id = 51866376;

         $temp_id = generate::uuid();
         $uuid    = generate::uuid();
         $todo_note = $this->apiRequest('note_add', [
            'temp_id'         => $temp_id,
            'uuid'            => $uuid,
            'args'            => [
               'item_id'      => $item_id,
               'content'      => "Людмила Викторовна\n+7 (910) 279-1040\nЗаказ: [48436] ВЕТЕРОК-2 ЭСОФ-2 0,6/220 (1 шт.) = 2 560.-"
            ]
         ]);

         return true;
      }
   }

   /**
   * Отправка запроса к Todoist API
   *
   * @param    string   $method     Метод, к которому идет обращение
   * @param    array    $commands   Команды для метода
   * @return   mixed
   */
   private function apiRequest($method, $commands = []) {
      $commands['type'] = $method;
      $commands = [$commands];

      //new dBug($commands);

      if ($curl = curl_init()) {
         $data = [
            'token'    => $this->token,
            'commands' => json_encode($commands,JSON_UNESCAPED_UNICODE)
         ];

         curl_setopt($curl, CURLOPT_URL, $this->url);
         curl_setopt($curl, CURLOPT_POST, count($data));
         curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

         $out = curl_exec($curl);
         $info = curl_getinfo($curl);

         curl_close($curl);

         //new dBug($info);
         //new dBug(json_decode($out,true));

         return json_decode($out,true);
      } else
         return false;
      /**/
   }

}
