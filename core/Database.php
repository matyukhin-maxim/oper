<?php

/**
 * Created by PhpStorm.
 * User: fellix
 * Date: 09.05.14
 * Time: 16:48
 */
class Database extends PDO {

    function __construct($dns) {

        $pool = array(
            'local' => array(
                'host' => 'localhot',
                'user' => 'root',
                'pass' => 'max',
                'base' => 'oper',
            ),
            'srv-1' => array(
                'host' => 'dgk90tch003',
                'user' => 'web',
                'pass' => 'web-user',
                'base' => 'oper',
            ),
            'srv-2' => array(
                'host' => 'dgk90tch004',
                'user' => 'web',
                'pass' => 'web-user',
                'base' => 'oper',
            ),
            'freebsd' => array(
                'host' => '172.28.120.2',
                'user' => 'kristya',
                'pass' => '31281080',
                'base' => 'operjournals',
            ),
        );
        
        // перебираем все источники, и пытаемся подключиться хоть к какому нибудь
        foreach ($pool as $source => $params) {
            
            $host = get_param($params, 'host');
            $user = get_param($params, 'user');
            $pass = get_param($params, 'pass');
            $base = get_param($params, 'base');
            
            $dns = sprintf('mysql:host=%s;dbname=%s', $host, $base);
            
            try {
                
                //parent::__construct($dns, $user, $pass);
                //Controller::appendDebug("Подключились к базе данных: $source", 2);
                //break;
                
            } catch (Exception $exc) {
                
                $error = $exc->getMessage();
                //Controller::appendDebug("Источник '$source' не доступен: $error");
                
            }
                    
        }
        
        return null;
        
        // БАЗА FreeBSD
        //$dns = sprintf('%s:host=%s;dbname=%s','mysql','172.28.120.2','operjournals');
        //parent::__construct($dns,'kristya','31281080',array());

        //$basename = 'oper';

        // МОЙ КОМП
        //$dns = sprintf('%s:host=%s;dbname=%s', 'mysql', 'localhost', $basename);
        //parent::__construct($dns, 'root', 'max', array());
        
        // ПАРОЛЬ НА Вебке
        //parent::__construct($dns, 'web', 'web-user', array());
        
        // Запись с локального сайта в удаленную базу
        //$dns = sprintf('%s:host=%s;dbname=%s', 'mysql', 'dgk90tch003', $basename);
        //parent::__construct($dns, 'max', 'maxmax0', array());
    }

    public function select($query, $param = array()) {

        $sth = $this->prepare($query);

        foreach ($param as $key => $value) {
            $type = strtolower(gettype($value));
            $cast = null;
            switch ($type) {
                case 'integer': $cast = PDO::PARAM_INT;
                    break;
                case 'null':    $cast = PDO::PARAM_NULL;
                    break;
                case 'boolean': $cast = PDO::PARAM_BOOL;
                    break;
                default:        $cast = PDO::PARAM_STR;
                    break;
            }
            $sth->bindValue($key, $value, $cast);
        }
        
        $sth->execute();
        $error = $this->errorInfo();
        
        $data = $sth->fetchAll();
        
        return array(
            'data' => $data,
            'error' => get_param($error, 2),
        );
    }

}
