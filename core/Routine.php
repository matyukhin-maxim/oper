<?php
/**
 * Created by PhpStorm.
 * User: fellix
 * Date: 09.05.14
 * Time: 17:17
 */


/**
 * @param $source
 * @param null $key
 * @param bool $def
 * @return bool | array
 */
function get_param(&$source, $key = null, $def = false) {
	if ($key === null) {
		return isset($source) ? $source : $def;
	}
	return isset($source[$key]) ? $source[$key] : $def;
}

function date_valid($date) {
    $parts = explode('.', $date);
    return checkdate($parts[1], $parts[0], $parts[2]);
}

/** @param string $date Строка с датой в формате dd.mm.yyyy */
function date2mysql($date, $time = '') {
    if (empty($date) || !date_valid($date))
        return null;
    $parts = explode('.', $date);
    if (!empty($time)) $time = " $time";
    return implode('-', array($parts[2], $parts[1], $parts[0])) . $time;
}

function createURL($type = '', $path = '', $base = '') {
    switch ($type) {
        case 'css': $base = CSSDIR;
            break;
        case 'js' : $base = JSDIR;
            break;
        //default   : $path .= "/";
    }
    if (empty($base))
        $base = ROOT;

    $res = $base . $path;

    // not shure print or return...
    return $res;
}

function charsetChange(&$value) {
    $type = gettype($value);
    if ($type === 'string')
        $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1251');
}

/**
 * Преобразовываем дату полученную из базы к человеческому виду
 * 2015-03-01 12:14 => 1 Марта 2015г
 * 
 * @param string $pdate Строка с датой
 * @param stirng $pformat Входной формат даты
 * @return string
 */
function date2human($pdate, $pformat = 'Y-m-d') {

    $result = '1 Января 1999 г.';
    $months = array('Мартября','Января','Февраля','Марта','Апреля','Мая','Июня','Июля','Августа','Сентября','Октября','Ноября','Декабря');
    $parts = date_parse_from_format($pformat, $pdate);
    
    $bad = get_param($parts, 'error_count') + get_param($parts, 'warning_count');
    if ($bad === 0) {
        $month = get_param($months, $parts['month']);
        $result = sprintf('%d %s %d г.', $parts['day'], $month, $parts['year']);
    }
    
    //var_dump($result);
    //var_dump($parts);
    //die;
    
    return $result;
}

function date2format($pdate, $i_fmt = 'Y-m-d', $o_fmt = 'd.m.Y') {

	$dt = DateTime::createFromFormat($i_fmt, $pdate);
	return $dt ? $dt->format($o_fmt) : date($o_fmt);
}

function trimHereDoc($txt) {
    // разбиваем текст по строками, и удаляем пробелы в каждой
    // т.к. при копипасте с word`а нсс копируют ТАБы
    return implode("\n", array_map('trim', explode("\n", $txt)));
}

function mb_capitalize($str) {

	$str = trim($str);
	$fc = mb_strtoupper(mb_substr($str, 0, 1));
	return $fc . mb_substr($str, 1);
}

/**
 * Возвращает часть массива по списку ключей
 * передынных в виде строки или массива
 *
 * @param array $data
 * @param string|array $keys
 * @param boolean $addempty
 *
 * @return array
 */
function get_array_part($data, $keys, $addempty = false) {

	$result = [];

	if (!is_array($keys)) {
		$keys = explode(' ', $keys);
	}

	foreach ($keys as $key) {
		$value = get_param($data, $key);
		if ($value || $addempty)
			$result[] = $value;
	}

	return $result;
}