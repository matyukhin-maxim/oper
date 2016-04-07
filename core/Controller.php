<?php

/**
 * Created by PhpStorm.
 * User: fellix
 * Date: 09.05.14
 * Time: 15:42
 */
class Controller {

    /** @var Model $model  class */
    public $model;
    public $classname = __CLASS__;
    public $arguments = array();
    public $data = array();
    public $selfurl = '';
    public $js = array(); // list of included javascript files
    public $stack = array(); // list of error or debug messages
    public $pageTitle = 'Оперативные журналы НГРЭС';
    public $authdata;

    private $printhead = false;
    
    protected $roles = array();
    protected $grants = array();
    protected $viewFolder = 'views/';

    public function __construct() {

        $this->classname = get_class($this);
        $this->selfurl = strtolower($this->classname) . '/';
        
        $this->loadModel();

        $this->authdata = Session::get('auth');
        $this->data['authdata'] = $this->authdata;
        
        //$this->data['dbsource'] = $this->model ? $this->model->getSource() : null;
        $this->data['server']   = getenv('COMPUTERNAME');
        
        $this->grants = get_param($this->authdata, 'journals', array());
        $this->roles = get_param($this->authdata, 'roles', array());
        
        $this->js = array(
            'jquery-1.11.2',
            'jquery-ui',
            'jquery.blockUI',
            'jquery-ui-timepicker-addon',
            'localization',
            'bootstrap.min',
            'default',
            'activetimer',
	        'moment.min',
	        'moment-ru',
        );
        
    }
    
    public function generateURI($ctrl, $args = array()) {
        
        $result = ROOT . $ctrl . '/';
        foreach ($args as $key => $value) {
            $result .= "$key/$value/";
        }
        return $result;
    }
    
    public function isRoleGranted($role_name, $journal_id = 0) {
        $role = get_param($this->roles, $role_name);
        
        // При проверке роли, и указании ИД журнала, проверим, есть ли доступ к этому самому журналу,
        // в режиме редактирования, и если нет, то будем считать что и роли тоже нет
        if ($journal_id > 0) {
            $grant = get_param($this->grants, $journal_id);
            $role = min(array(
                $role, get_param($grant, 'em', 0),
            ));
        }
        return $role;
    }
    
    public function isJournalGranted($journal_id) {
        return get_param($this->grants, $journal_id) !== false;
    }

    public function render($view, $print_footer = true) {

        extract($this->data);
        if (!$this->printhead) {
            include $this->viewFolder . 'hcommon.php';
            $this->printhead = true;
        }

        $path = $this->viewFolder . strtolower("$this->classname/$view.php");
        if (file_exists($path)) {
            include $path;
        }

        if ($print_footer) {
            //include $this->viewFolder . 'action-panel.php';
            include $this->viewFolder . 'fcommon.php';
        }
    }

    public function renderPartial($view) {

        ob_start();
        ob_implicit_flush(false);

        extract($this->data);
        $path = $this->viewFolder . strtolower("$this->classname/$view.php");
        if (file_exists($path)) {
            include $path;
        }

        return ob_get_clean();
    }
    

    public function loadModel() {
        
        $model_name = $this->classname . 'Model';
        $model_file = "models/$model_name.php";
        $this->model = null;

        if (file_exists($model_file)) {
            require_once $model_file;
            if (class_exists($model_name)) {
                $this->model = new $model_name();
            }
        }
    }

    public static function isPOST() {
        return get_param($_SERVER,'REQUEST_METHOD') === 'POST';
    }
    
    public static function isAjax() {
        return get_param($_SERVER, 'HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
    }


    public function redirect($param = null) {

	    if (is_null($param)) $param = '';
	    if (!is_array($param)) {
		    $param = ['location' => $param];
	    }

        
        if (self::isAjax()) {
            // если страница запрошена аяксом, то редирект работать не должен
            // вместо этоготого покажем диалог с текстом ошибки
            die($this->renderPartial('../modal-message'));
        }

        $location = ROOT . get_param($param, 'location', '');
        if (get_param($param, 'back') === 1)
            $location = get_param($_SERVER, 'HTTP_REFERER', $location);
        if (get_param($param, 'soft') === 1) {
            $delay = get_param($param, 'delay', 3);
            echo "<meta http-equiv=\"refresh\" content=\"$delay; URL=$location\">";
        } else
            header("Location: $location");
        die;
    }
    
    /**
     * 
     * Добавляет запись к общему списку ошибок
     * 1 - info; 2 - success; 3 - warning; 0 - danger
     * 
     * @param string $data
     * @param int $type
     */
    public static function appendDebug($data, $type = 0, $static = 0) {
        
        if (empty($data)) return;
        
        $debug = Session::get('debug', array());
        $d_class = '';
        switch ($type) {
            case 1: $d_class = 'alert-info';  break;
            case 2: $d_class = 'alert-success';  break;
            case 3: $d_class = 'alert-warning';  break;
            default:$d_class = 'alert-danger';
        }
       
        $debug[] = array(
            'd_message'   => $data,
            'd_class'     => $d_class,
            'static'      => $static,
        );
        Session::set('debug', $debug);
    }
    
    public function drawError() {
        
        $elist = Session::get('debug', array());
        
        if (!empty($elist)) {
            foreach ($elist as $value) {
                $static = get_param($value, 'static', 0);
                $this->data['d_message'] = get_param($value, 'd_message');
                $this->data['d_class'] = get_param($value, 'd_class');
                echo $this->renderPartial( $static ? '../error_static' : '../error_dismiss');
            }
        }
     
        
        Session::del('debug');
    }

}
