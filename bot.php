<?
/**
* Telegram бот
* @author  Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 0.4
*/

class bot {

   public function __construct() {
      $this->db = new mysqlcrud();
      $this->db->connect();
      $this->content = new template();
      $this->sms = new smsru();

      $this->telegram = new telegram();
      $this->telegram->token = cfg::BOT_ID.':'.cfg::BOT_TOKEN;

      $this->output     = json_decode(file_get_contents('php://input'), TRUE);
      $this->chat_id    = $this->output['message']['chat']['id'];
      $this->message    = $this->output['message']['text'];
      $this->phone      = $this->output['message']['contact']['phone_number'];
      $this->pin        = (preg_match('/^\d{5}$/',$this->output['message']['text']) ? $this->output['message']['text'] : false);

      $this->commands = [
         'help'      => 'все допустимые команды',
         'contacts'  => 'контактная информация магазина',
         'location'  => 'расположение точки самовывоза',
         'whoami'    => 'информация о пользователи на сайте'
      ];

      if ($this->is_auth()) {
         $this->auth = 1;
         $this->reply_markup = $this->telegram->ReplyKeyboardHide();
      }

      //$this->reply_markup = ($this->is_auth() ? $this->telegram->ReplyKeyboardHide() : null);

      //$this->sms->send('+79155173925',file_get_contents('php://input'),cfg::SMSRU_SENDER_1);
   }

   /**
   * WebHOOK
   * @version 0.2
   */
   public function hook($params) {
      if ($params['token'] != cfg::BOT_ID) {
         die('This token not my!');
      } elseif ($this->phone) {
         $this->reg_user();
         die;
      } elseif ($this->pin) {
         $this->auth_user();
         die;
      }

      switch($this->message) {
         case '/start':
            $this->com_start();
            break;
         case '/contacts':
            $this->telegram->apiRequest('sendContact', [
               'chat_id'      => $this->chat_id,
               'phone_number' => cfg::CONTACT_PHONE,
               'first_name'   => 'HOMY.su',
               'reply_markup' => $this->reply_markup
            ]);
            break;
         case '/location':
            $this->telegram->apiRequest('sendLocation', [
               'chat_id'      => $this->chat_id,
               'latitude'     => cfg::PLACE_LAT,
               'longitude'    => cfg::PLACE_LON,
               'reply_markup' => $this->reply_markup
            ]);
            break;
         case '/whoami':
            $this->com_whoami();
            break;
         case '/help':
            $this->com_help();
            break;
         default:
            $this->com_unknown();
            break;
      }
   }

   /**
   * Обработчики команд чата
   * @version 0.1
   */
   private function com_start() {
      $message = 'Привет! Идентификатор этого чата *'.$this->chat_id.'*';

      // показываем кнопку авторизации
      if ($this->reply_markup == null) {
         $this->reply_markup = [
            'keyboard'        => [[['text' => 'Авторизация','request_contact' => true]]],
            'one_time_keyboard' => true,
            'resize_keyboard' => true
         ];
         $this->reply_markup = json_encode($this->reply_markup);
      }

      // отправляем сообщение
      $this->telegram->apiRequest('sendMessage', [
         'chat_id'      => $this->chat_id,
         'text'         => $message,
         'parse_mode'   => 'Markdown',
         'reply_markup' => $this->reply_markup
      ]);
   }

   private function com_help() {
      $message = cfg::BOT_HELP_K."\n";
      $message .= $this->commands_list();

      $this->telegram->apiRequest('sendMessage', [
         'chat_id'      => $this->chat_id,
         'text'         => $message,
         'parse_mode'   => 'Markdown',
         'reply_markup' => $this->reply_markup
      ]);
   }

   private function com_whoami() {
      $this->db->sql('
         SELECT
            users.firstname,
            users.lastname
         FROM users
         LEFT JOIN
            telegram
         ON
            telegram.user_id = users.id
         WHERE
            telegram.chat_id = '.$this->chat_id
      );
      $result = $this->db->getResult();
      if (count($result) != 0) {
         $message = 'Ваше имя на сайте *'.$result[0]['firstname'].' '.$result[0]['lastname'].'*';
      } else {
         $message = 'Вы еще не авторизованы. Пройдите авторизацию!';
         $this->reply_markup = [
            'keyboard'        => [[['text' => 'Авторизация','request_contact' => true]]],
            'one_time_keyboard' => true,
            'resize_keyboard' => true
         ];
         $this->reply_markup = json_encode($this->reply_markup);
      }

      $this->telegram->apiRequest('sendMessage', [
         'chat_id'      => $this->chat_id,
         'text'         => $message,
         'parse_mode'   => 'Markdown',
         'reply_markup' => $this->reply_markup
      ]);
   }

   private function com_unknown() {
      $message = "Прости, но я не понимаю что ты написал.\nПопробуй одну из нижеперечисленных команд, которые я точно понимаю:\n";
      $message .= $this->commands_list();

      $this->telegram->apiRequest('sendMessage', [
         'chat_id'      => $this->chat_id,
         'text'         => $message,
         'parse_mode'   => 'Markdown'
      ]);
   }

   /**
   * Формирование списка известных боту команд
   * @version 0.1
   */
   private function commands_list() {
      $return = null;
      foreach ($this->commands as $command => $note) {
         $return .= "\n/".$command.' - '.$note;
      }
      return $return;
   }

   private function reg_user() {
      //$this->sms->send('+79155173925',file_get_contents('php://input'),cfg::SMSRU_SENDER_1);
      // проверяем авторизацию пользователя
      $this->db->sql('
         SELECT
            users.id AS user_id,
            telegram.chat_id
         FROM users
         LEFT JOIN
            telegram
         ON
            telegram.user_id = users.id
         WHERE
            users.phone = '.$this->phone
      );
      $result = $this->db->getResult();

      // если пользователь не зарегистрирован на сайте
      if (count($result) == 0) {
         $message = 'Вы не зарегистрированы на сайте. [Пройдите регистрацию](http://homy.su/login/).';
      // если нашли запись о пользователе
      } else {
         $user_id = $result[0]['user_id'];

         if ($this->auth_user) {
            $message = 'Вы уже авторизованы, повторная авторизация не требуется.';
         } else {
            // генерируем одноразовый пин для авторизации
            $pin = generate::pin(5);
            $this->db->sql('
               INSERT INTO telegram
                  (chat_id,user_id,pin)
               VALUES
                  ('.$this->chat_id.','.$user_id.','.$pin.')
            ');
            $this->db->getResult();

            if ($this->sms->send($this->phone,'Код для авторизации бота '.$pin,cfg::SMSRU_SENDER_1))
               $message = 'На указанный Вами номер телефона *'.$this->phone.'* было отправлено смс сообщение с кодом для авторизации. Отправьте мне его в ответ.';
            else
               $message = 'Не удалось отправить Вам смс сообщение. Попробуйте аторизоваться позднее.';
         }
      }

      $this->telegram->apiRequest('sendMessage', [
         'chat_id'      => $this->chat_id,
         'text'         => $message,
         'parse_mode'   => 'Markdown',
         'reply_markup' => $this->reply_markup
      ]);
   }

   private function auth_user() {
      $this->db->sql('SELECT pin FROM telegram WHERE chat_id = '.$this->chat_id);
      $result = $this->db->getResult();

      if (count($result) != 0) {
         $pin = $result[0]['pin'];

         if ($this->pin == $pin) {
            $this->db->sql('
               UPDATE telegram
               SET
                  auth = 1,
                  pin = DEFAULT,
                  pin_added = NULL
               WHERE chat_id = '.$this->chat_id
            );
            $result = $this->db->getResult();
            $message = 'Вы успешно авторизованы';
            $this->reply_markup = $this->telegram->ReplyKeyboardHide();
         } else {
            $message = 'Код не совпадает!';
         }
      } else {
         $message = 'Код не совпадает с отправленным';
      }

      $this->telegram->apiRequest('sendMessage', [
         'chat_id'      => $this->chat_id,
         'text'         => $message,
         'parse_mode'   => 'Markdown',
         'reply_markup' => $this->reply_markup
      ]);
   }

   private function is_auth() {
      $this->db->sql('SELECT user_id FROM telegram WHERE chat_id = '.$this->chat_id.' AND auth = 1');
      $result = $this->db->getResult();

      return (count($result) == null ? false : true);
   }

}
?>
