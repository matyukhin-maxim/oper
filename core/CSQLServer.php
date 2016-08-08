<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 16.06.2016
 * Time: 8:49
 *
 * @property PDOStatement $statement
 */
class CSQLServer extends PDO {
	
	public $queryString;
	public $queryParameters = [];
	public $rowCount = 0;
	
	private $dsn = 'odbc:Driver={SQL Server};Server=%s;Database=%s; Uid=%s;Pwd=%s';
	//private $dsn = 'mysql:host=%s;dbname=%s';
	private $statement;
	private static $errors = array();
	
	public function __construct($auth) {

		$db_host = get_param($auth, 'host', 'localhost');
		$db_user = get_param($auth, 'user', 'root');
		$db_pass = get_param($auth, 'pass', '');
		$db_name = get_param($auth, 'base', '');

		try {
			parent::__construct(sprintf($this->dsn, $db_host, $db_name, $db_user, $db_pass));
			//parent::__construct(sprintf($this->dsn, $db_host, $db_name), $db_user, $db_pass);

			//$this->query('set names utf8');
			$this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

			//$this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			//$this->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
		} catch (Exception $ex) {
			$error = $ex->getMessage();
			charsetChange($error);
			throw new Exception ($error);
		}
	}

	/**
	 * Росворачивает многомерный массив с параметрами в одномерный
	 * для замены конструкций запроса 'WHERE FIELD IN (:LIST)'
	 *
	 * т.о. если исходный массив будет иметь вид
	 * [ 'a' => 0, 'list' => ['x', 'y', 'z'] ]
	 * то строка запроса изменится на WHERE FIELD IN (:__X1, :__X2, :__X3)
	 * и в массив параметров добавятся соответствующие значения для подстановки в запрос
	 * [ 'a' => 0, '__X1' => 'x', '__X2' => 'y', '__X3' => 'z' ]
	 *
	 *
	 */
	private function prepareParams() {

		$index = 0;
		foreach ($this->queryParameters as $key => $value) {
			if (gettype($value) === 'array') {

				// Получим строку вида :__X1, :__X2, ...
				// В зависимости от числа вложенных в значение параметра элементов
				// И добавим каждой значение в общий массив, с соответствующим именем переменной
				$vars = join(',', array_map(function($v) use (&$index, &$param) {
					$np = "__X" . ++$index; $this->queryParameters[$np] = $v;
					return ":$np";
				}, $value));
				unset($this->queryParameters[$key]);

				$this->queryString = str_replace(":$key", $vars, $this->queryString);
			}
		}
	}
	
	public function checkError() {

		if (!$this->statement) return;

		$dump = $this->statement->errorInfo();
		list($ecode, $etext) = get_array_part($dump, [0,2], true);
		if ($ecode !== '00000')
			self::$errors[] = sprintf("DB Error [%d]: %s", $ecode, $etext ?: 'Ошибка в параметрах');
	}
	
	private function prepareQuery() {
		
		$this->statement = $this->prepare($this->queryString);
		if (!$this->statement) {
			self::$errors[] = 'Ошибка при подготовке запроса. Проверте текст';
			return;
		}

		$castTypes = [
			'integer' => PDO::PARAM_INT,
			'boolean' => PDO::PARAM_BOOL,
			'null' => PDO::PARAM_NULL,
		];

		// Выбираем регулярным выражением из строки все параметры запроса,
		// и будем привязывать к значениям массива
		preg_match_all('/:([a-z_]+\d?)/is', $this->queryString, $match);
		foreach (get_param($match, 1, []) as $pName) {

			$pValue = get_param($this->queryParameters, $pName, 0);
			$pType = gettype($pValue);
			$this->statement->bindValue($pName, $pValue, get_param($castTypes, $pType, PDO::PARAM_STR));
		}

		$this->statement->execute();
		$this->checkError();

		$this->rowCount = $this->statement->rowCount();
	}

	public function selectAll($query = null, $params = null) {

		if ($query !== null) $this->queryString = $query;
		if ($params !== null) $this->queryParameters = $params;

		$this->prepareParams();
		$this->prepareQuery();

		return $this->statement->fetchAll();
	}

	public function selectOne($query = null, $params = null) {

		if ($query !== null) $this->queryString = $query;
		if ($params !== null) $this->queryParameters = $params;

		$this->prepareParams();
		$this->prepareQuery();

		return $this->statement->fetch();
	}

	public function getErrorList($separator = '<br/>') {

		return join($separator, self::$errors);
	}
}