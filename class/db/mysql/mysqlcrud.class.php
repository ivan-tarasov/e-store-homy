<?php
namespace db\mysql;

/**
* Класс для работы с БД MySQL
*
* @method  bool		connect()
* @method  bool		disconnect()
* @method  bool		sql(string $sql)
* @method  bool		select(string $table, string $rows, string $join, string $where, string $order, int $limit)
* @method  bool		insert(string $table, string $params)
* @method  bool		delete(string $table, string $where)
* @method  bool		update(string $table, array $params, string $where)
* @method  bool		tableExists(string $table)
* @method  array		getResult()
* @method	string	getSql()
* @method  int		numRows()
* @method  string	escapeString(string $data)
*
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 1.8.2
*/

new \core\config\cfg();

class mysqlcrud {
	private $con = false;		// Проверка активного подключения
	private $result = array(); // Результаты запроса будут храниться здесь
   private $myQuery = "";		// Для дебага
   private $numResults = "2";	// Для возвращения кол-ва строк

   /**
   * Функция соединения с БД
   * @return   bool 	connect
   */
	public function connect() {
		if (!$this->con) {
			$myconn = @mysql_connect(DB_HOST,DB_USER,DB_PASS);
			if ($myconn) {
				mysql_query("set names utf8");	// для кирилицы используем костыль с кодировкой
				$seldb = @mysql_select_db(DB_NAME,$myconn);
				if ($seldb) {
					$this->con = true;
					return true;	// Соединение установлено, возвращаем TRUE
				} else {
					array_push($this->result,mysql_error());
               error_log(DB_HOST . ' ' . mysql_error(), 0);
					return false;  // Проблема в выборе БД, возвращаем FALSE
				}
			} else {
				array_push($this->result, mysql_error());
            error_log(DB_HOST . ' ' . mysql_error(), 0);
				return false; // Проблема с соединением, возвращаем FALSE
			}
		} else
			return true; // Соединение было установлено ранее, возвращаем TRUE
	}

   /**
   * Функция дисконнекта от БД
   * @return	bool 	disconnect
   */
	public function disconnect() {
		// Если соединение установлено
		if ($this->con) {
			// Находим соед, пытаемся его закрыть
			if (@mysql_close()) {
    			// Соединение закрыто, устанавливаем переменную в FALSE
    			$this->con = false;
				// Возвращаем TRUE, ведь соединение мы закрыли
				return true;
			} else
				// Проблемы с закрытием соединения, возвращаем FALSE
				return false;
		}
	}

   /**
   * Функция дисконнекта БД
   *
	* <code>
	* $db = new mysqlcrud();
	* $db->connect();
	* $db->sql('SELECT id,name FROM some_table');
	* $res = $db->getResult();
	* foreach($res as $output){
	*     echo $output["name"]."<br />";
	* }
	* </code>
	*
	* @param    string 	$sql    		SQL строка запроса
   */
	public function sql($sql) {
		$query = @mysql_query($sql); $this->sqlCounter();
		$this->myQuery = $sql;
		if ($query) {
			// Если запрос возвращает >= 1, то назначаем кол-во строк в numResults
			$this->numResults = @mysql_num_rows($query);

			// Проходим по результатам запроса
         $this->result = [];
			for ($i = 0; $i < $this->numResults; $i++) {
				$r = mysql_fetch_array($query);
				$key = array_keys($r);
				for ($x = 0; $x < count($key); $x++) {
					if (!is_int($key[$x])) {
						if(mysql_num_rows($query) >= 1){
							$this->result[$i][$key[$x]] = $r[$key[$x]];
						} else
							$this->result = null;
					}
				}
			}
			return true; // Запрос прошел успешно
		} else {
			array_push($this->result,mysql_error());
			return false; // Ни одной строки не получено
		}
	}

   /**
   * Функция выборки из БД (SELECT)
   *
	* <code>
	* $db = new mysqlcrud();
	* $db->connect();
	* $db->select('some_table');
	* $res = $db->getResult();
	* print_r($res);
	* </code>
	*
	* @param    string 	$table    		таблица выборки
	* @param    string 	$rows    		поля для выборки (по-умолчанию "*")
	* @param    string 	$join    		поля JOIN (по-умолчанию NULL)
	* @param    string 	$where    		строка с параметром выборки (по-умолчанию NULL)
	* @param    string 	$order    		строка с параметрами сортировки (по-умолчанию NULL)
	* @param    int	 	$limit    		ограничение кол-ва выводимых строк (по-умолчанию NULL)
   */
	public function select($table, $rows = '*', $join = null, $where = null, $order = null, $limit = null) {

		// Собираем запрос из переданных переменных
		$q = 'SELECT '.$rows.' FROM '.$table;
		if($join != null)
			$q .= ' JOIN '.$join;
		if($where != null)
        	$q .= ' WHERE '.$where;
		if($order != null)
			$q .= ' ORDER BY '.$order;
		if($limit != null)
			$q .= ' LIMIT '.$limit;

		$this->myQuery = $q;

		// Проверяем существования таблицы
		if ($this->tableExists($table)) {
			// Таблица существует, выполняем запрос
			$query = @mysql_query($q); $this->sqlCounter();
			if ($query) {
				// Если запрос возвращает >= 1, то назначаем кол-во строк в numResults
				$this->numResults = mysql_num_rows($query);
				// Проходим по результатам запроса
            $this->result = [];
				for ($i = 0; $i < $this->numResults; $i++) {
					$r = mysql_fetch_array($query);
					$key = array_keys($r);
					for ($x = 0; $x < count($key); $x++) {
						if (!is_int($key[$x])) {
							if (mysql_num_rows($query) >= 1)
								$this->result[$i][$key[$x]] = $r[$key[$x]];
							else
								$this->result = null;
						}
					}
				}
				return true; // Запрос был успешным
			} else {
				array_push($this->result,mysql_error());
				return false; // Ни одной строки не возвращено
			}
		} else
			return false; // Таблица не существует
	}

   /**
   * Функция добавления записей в БД (INSERT)
   *
	* <code>
	* $db = new mysqlcrud();
	* $db->connect();
	* $data = $db->escapeString("ivan@karapuzoff.net"); 						// Экранируем спец. символы, прежде чем работать с запросами
	* $db->insert('some_table',array('name'=>'Ivan','email'=>$data));   	// Таблица, имя колонки и данные
	* $res = $db->getResult();
	* print_r($res);
	* </code>
	*
	* @param    string 	$table    		таблица выборки
	* @param    array 	$params    		массив параметров
   */
	public function insert($table,$params=array()) {
		// Проверяем существования таблицы
		if ($this->tableExists($table)) {
			$sql='INSERT INTO `'.$table.'` (`'.implode('`, `',array_keys($params)).'`) VALUES ("' . implode('", "', $params) . '")';
			$this->myQuery = $sql; // Возвращаем SQL

			// Делаем запрос для добавления данных
			if ($ins = @mysql_query($sql)) {
				$this->sqlCounter();

				array_push($this->result,mysql_insert_id());
				return true; // Данные были добавлены
			} else {
				array_push($this->result,mysql_error());
				return false; // Данные не были добавлены
			}
		} else
			return false; // Таблица не существует
	}

   /**
   * Функция удаления таблицы или записи (записей) из БД (DELETE)
   *
	* <code>
	* $db = new mysqlcrud();
	* $db->connect();
	* $db->delete('some_table','id=5'); или $db->delete('some_table'); для всей таблицы
	* $res = $db->getResult();
	* print_r($res);
	* </code>
	*
	* @param    string 	$table    		таблица
	* @param    string 	$where    		параметры для удаления строк
   */
	public function delete($table,$where = null) {
		// Проверяем существования таблицы
		if ($this->tableExists($table)) {

			// Таблица существует, проверяем удаляем ли мы таблицу целиком или только строки
			if ($where == null) {
				$delete = 'DELETE '.$table; // Собираем запрос для удаления таблицы
			} else
				$delete = 'DELETE FROM '.$table.' WHERE '.$where; // Собираем запрос для удаления строк

			// Отправляем запрос
			if ($del = @mysql_query($delete)) {
				$this->sqlCounter();

				array_push($this->result,mysql_affected_rows());
				$this->myQuery = $delete; // Возвращаем SQL
				return true; // Запрос был выполнен корректно
			} else {
				array_push($this->result,mysql_error());
				return false; // Запрос не был выполнен корректно
			}
		} else
			return false; // Таблица не существует
	}

   /**
   * Функция обновления записей в БД (UPDATE)
   *
	* <code>
	* $db = new mysqlcrud();
	* $db->connect();
	* $db->update('some_table',array('name'=>"Ivan",'email'=>"ivan@karapuzoff.net"),'id="1" AND name="Not Ivan"');
	* $res = $db->getResult();
	* print_r($res);
	* </code>
	*
	* @param    string 	$table    		таблица
	* @param    array 	$params    		параметры для обновления строк
	* @param    string 	$where    		строка с параметром выборки для обновления
   */
	public function update($table,$params=array(),$where) {
		// Проверяем существования таблицы
		if ($this->tableExists($table)) {

			// Создаем массив, в котором будем держать все колонки для обновления
			$args=array();
			foreach ($params as $field=>$value) {
				// Разбиваем колонки и добавляем значения
				$args[]=$field.'="'.$value.'"';
			}

			// Создание запроса
			$sql='UPDATE '.$table.' SET '.implode(',',$args).' WHERE '.$where;

			// Make query to database
			$this->myQuery = $sql; // Возвращаем SQL
			if ($query = @mysql_query($sql)) {
				$this->sqlCounter();

				array_push($this->result,mysql_affected_rows());
				return true; // Обновление прошло успешно
			} else {
				array_push($this->result,mysql_error());
				return false; // Обновление не прошло успешно
			}
		} else
			return false; // Таблица не существует
	}

   /**
   * Приватная функция проверки существования таблицы для использования с запросами
	* @param    string 	$table    		таблица
   */
	private function tableExists($table) {
		$tablesInDb = @mysql_query('SHOW TABLES FROM '.DB_NAME.' LIKE "'.$table.'"');
		$this->sqlCounter();

		if ($tablesInDb) {
			if (mysql_num_rows($tablesInDb)==1)
				return true; // Таблица существует
			else {
				array_push($this->result,$table." не существует в БД");
				return false; // Таблица не существует
			}
		}
	}

   /**
   * Приращение количества запросов
   * @version 0.1
   */
	private function sqlCounter() {
		$_SESSION['mysql_count'] = (isset($_SESSION['mysql_count']) ? ++$_SESSION['mysql_count'] : 1);
	}

   /**
   * Возвращает данные запроса пользователю
   * @version 0.1
   */
	public function getResult(){
		$val = $this->result;
		$this->result = array();
		return $val;
	}

   /**
   * Возвращает SQL-запрос для дебага
   * @version 0.2
   */
	public function getSql(){
		$val = $this->myQuery;
		$this->myQuery = array();
		return $val;
	}

   /**
   * Возвращает кол-во строк в запросе
   * @version 0.3
   */
	public function numRows(){
		$val = $this->numResults;
		$this->numResults = array();
		return $val;
	}

   /**
   * Экранирует спец. символы
   * @version 0.2
   */
	public function escapeString($data){
		return @mysql_real_escape_string($data);
	}
}
