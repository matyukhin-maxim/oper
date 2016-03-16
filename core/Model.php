<?php
/**
 * Created by PhpStorm.
 * User: fellix
 * Date: 09.05.14
 * Time: 16:51
 */

class Model {

    /** @var PDO $db link to mysql database*/
    public static $db;
    public $status;
   
    /*
    public function __construct() {
        
        if (!empty(self::$db)) return;
        
        $this->status = '';
        $this->source = '';
        
        $pool = array(
//            'LOCAL' => array(
//                'host' => 'localhost',
//                'user' => 'root',
//                'pass' => 'max',
//                'base' => 'oper',
//            ),
            'TECH' => array(
                'host' => 'tech-db',
                'user' => 'max',
                'pass' => 'maxmax0',
                'base' => 'oper',
            ),
//            'SRV-1' => array(
//                'host' => 'dgk90tch003',
//                'user' => 'web',
//                'pass' => 'web-user',
//                'base' => 'oper',
//            ),
//            'SRV-2' => array(
//                'host' => 'dgk90tch004',
//                'user' => 'web',
//                'pass' => 'web-user',
//                'base' => 'oper',
//            ),
//            'FreeBSD' => array(
//                'host' => '172.28.120.2',
//                'user' => 'kristya',
//                'pass' => '31281080',
//                'base' => 'operjournals',
//            ),
        );
        
        // Смотрим в сессии, соединение с какой БД было последним,
        // и ставим его первым в списке
        //var_dump($pool);
        
        $last   = Session::get('db-source');
        $option = get_param($pool, $last);
        
        if ($option) {
            
            // удаляем из пула последнюю настройку
            unset($pool[$last]);
            
            // и новый пул получаем обхединением последней настройки, 
            // и оставшегося пула
            // таким образом ставим элемент настроек, к которому подключались последним,
            // на первое место...
            $pool = array_merge(array(
                $last => $option,
            ), $pool);
        }
            
        //var_dump($pool);
        
        // Проходим по всему циклу и пытаемся подключиться к источнику
        foreach ($pool as $source => $params) {
            
            $host = get_param($params, 'host');
            $user = get_param($params, 'user');
            $pass = get_param($params, 'pass');
            $base = get_param($params, 'base');

            $dns = sprintf('mysql:host=%s;dbname=%s', $host, $base);
            //self::$db = null;
            self::$db = null;

            try {
                
                self::$db = new PDO($dns, $user, $pass, array(
                    PDO::ATTR_TIMEOUT => 2,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',
                ));
                $this->source = $source;
                break;
                
            } catch (Exception $exc) {
                $this->status = $exc->getMessage();
                charsetChange($this->status);
                $this->source = null;
                self::$db = null;
                //Controller::appendDebug("Источник '$source' : $this->status", 3);
            }
        }
        
        // Если удалось подключиться к БД,
        // то сконфигурируем драйвер как на м надо
        if (self::$db) {
            
            Session::set('db-source', $this->getSource());
            
            try {
                self::$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                //this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                self::$db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                //self::$db->query("set names utf8");
                //self::$db->query("set lc_time_names = 'ru_RU'");
            } catch (Exception $exc) {
                
            }
        }
    }
    */

   public function __construct() {
       
        $dns = sprintf('mysql:host=%s;dbname=%s', 'tech-db', 'oper');
        self::$db = null;

        $retry = 5;
        
        while (--$retry && !self::$db) {
            try {
                self::$db = new PDO($dns, 'web-user', 'web-pass', array(
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
    
    public function select($query, $param = array()) {

        $sth = self::$db->prepare($query);

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
