<?php

/**
 * Created by PhpStorm.
 * User: fellix
 * Date: 09.05.14
 * Time: 16:51
 */
class Model {

	/** @var PDO $db link to mysql database */
	public static $db;
	public $status;

	public function __construct() {

		$dns = sprintf('mysql:host=%s;dbname=%s', 'localhost', 'oper');
		self::$db = null;

		$retry = 5;

		while (--$retry && !self::$db) {
			try {
				self::$db = new PDO($dns, 'root', 'fell1x', array(
					PDO::ATTR_TIMEOUT => 5,
					PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',
				));
			} catch (Exception $exc) {
				$this->status = $exc->getMessage();
				charsetChange($this->status);
				self::$db = null;
			}
		}

		// Если удалось подключиться к БД,
		// то сконфигурируем драйвер как на м надо
		if (self::$db) {

			try {
				self::$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
				//this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
				self::$db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
				self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
			} catch (Exception $exc) {

			}
		}
	}

	public function select($query, $param = array(), & $cnt = null) {

		$sth = self::$db->prepare($query);

		foreach ($param as $key => $value) {
			$type = strtolower(gettype($value));
			$cast = null;
			switch ($type) {
				case 'integer':
					$cast = PDO::PARAM_INT;
					break;
				case 'null':
					$cast = PDO::PARAM_NULL;
					break;
				case 'boolean':
					$cast = PDO::PARAM_BOOL;
					break;
				default:
					$cast = PDO::PARAM_STR;
					break;
			}
			$sth->bindValue($key, $value, $cast);
		}

		$sth->execute();
		$error = $sth->errorInfo();

		$data = $sth->fetchAll();
		if (get_param($error, 2)) {
			Controller::appendDebug(get_param($error, 2));
		}

		$cnt = $sth->rowCount();
		return $data;
	}

}
