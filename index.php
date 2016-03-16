<?php

set_time_limit(0);
//error_reporting(0);
error_reporting(E_ALL);

//define('ROOT',      dirname($_SERVER['PHP_SELF']) . '/');
define('ROOT',      '/');

require_once 'core/TFPDF.php';
require_once 'core/Routine.php';
require_once 'core/CHtml.php';

require_once 'core/Pager.php';

require_once 'core/Session.php';
Session::start();

require_once 'core/Database.php';
require_once 'core/AccessModel.php';
require_once 'core/Model.php';
require_once 'core/Controller.php';



// запоминаем какая страница была запрошена
// чтобы вернутся на нее, если авторизация не выполненеа
$query = strtolower(rtrim(get_param($_GET,'url','journal'),'/'));
$url = explode('/',$query);

//if (!Controller::isAjax() && $url[0] !== 'auth')
//    Session::set('query', "$query/"); // только если это не редирект на авторизацию
// после того как авторизация будет пройдена (если будет пройдена)
// то мы делаем редирект на этот адрес

/* @var $controller Controller */
try {
    
    $module = $url[0];
    $action = get_param($url, 1, 'index');
    $params = array_slice($url,2);
    
    if (count($params) % 2) {
        throw new Exception('Bad argument string');
    }

    // проверяем сущевствование файла контролера (класса)
    $file = 'controllers/' . ucfirst($module) . 'Controller.php';
    if (!file_exists($file)) {
        throw new Exception("Controller not found. $file");
    }
 
    require_once $file;
    if (!class_exists($module)) {
        throw new Exception("Bad controller class: $module");
    }

    /*
     * @var $conroller Controller
     */
    $controller = new $module();
    
    // Переносим параметры контроллера
    for ($i = 0; $i < count($params); $i++) {
        //$params[$url[$i]] = $url[++$i];
        $controller->arguments[$params[$i]] = $params[++$i];
    }
    $method = 'action' . ucfirst($action);

    if (!method_exists($controller, $method)) {
        throw new Exception("Action '$method' not found in controller '$module'.");
    }
    
    // Проверим:
    // Если модель у контроллера есть, *Model.php загружен
    // проверим удалось ли соедениться с каким нибудь источником БД
    // и если нет, то выкатим ошибку, и дальше работать не будем
    //
    if ($controller->model && !Model::$db) {
        $controller->appendDebug('База данных не доступна!', 0, 1);
        $controller->render(''); // шапка + подвал
        die;
    }

    $controller->$method();
    
} catch (Exception $exc) {
    $message = $exc->getMessage();  // Записать это в ЛОГ
    //error_log($message);
    if (!Controller::isAjax()) echo $message;
    $controller = new Controller();
    $controller->redirect(array(
        'back' => 1,
        'soft' => 1,
        'delay'=> 5,
    ));
    /*
    Controller::redirect(array(
        'back' => 1,
        'soft' => 1,
        'delay'=> 5,
    ));
     * 
     */
}