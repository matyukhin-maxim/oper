<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 16.06.2016
 * Time: 8:49
 */
class CSQLServer extends PDO {

	private $dsn = 'odbc:Driver={SQL Server};Server=%s;Database=%s; Uid=%s;Pwd=%s';
	public $queryString;
	private static $errors = array();

	public function __construct($auth) {

		$db_host = get_param($auth, 'host', 'localhost');
		$db_user = get_param($auth, 'user', 'root');
		$db_pass = get_param($auth, 'pass', '');
		$db_name = get_param($auth, 'base', '');


		//parent::__construct(sprintf($this->dsn, $db_host, $db_name, $db_user, $db_pass));

		//$this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		//$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

		//$this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		//$this->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
	}

	public static function expandParam($pval, $pkey, &$proot) {

		if (gettype($pval) === 'array') {

		}
	}

	public function prepareParams(&$param = array()) {

		$index = 0;
		foreach ($param as $key => $value) {
			if (gettype($value) === 'array') {

				$keys = array_map(function($v) use (&$index, &$param) {
					$np = "__X" . ++$index; $param[$np] = $v;
					return ":$np";
				}, $value);
				unset($param[$key]);

				//var_dump($keys);
				//$condition = "(";
				//$local = 0;
				//foreach ($value as $item) {
				//
				//	$condition .= $local ? "," : ""; // если не первый параметр, то добавим запятую
				//	$vparam = "_X" . ++$cnt;
				//	$condition .= " :$vparam";
				//
				//	// а параметр подмассива перекидываем в основной массив
				//	// елементы вложенного массива не должны быть сами массивами, иначе хрень будет
				//	$param[$vparam] = $item;
				//	$local++;
				//}
				//$condition .= ") ";
				//$this->queryString = str_replace(":$key", $condition, $this->queryString);
				//unset($param[$key]);
			}
		}

		//$index = 0;
		//array_walk($param, 'CSQLServer::expandParam', $param);
		var_dump($param);
	}
}