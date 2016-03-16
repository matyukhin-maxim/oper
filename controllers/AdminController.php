<?php

/** @property AdminModel $model  */
class Admin extends Controller {
    
    public function __construct() {
        parent::__construct();
        
        //$this->js[] = 'jquery.dataTables';
        
    }
    
    public function actionIndex() {
        
        $this->js[] = 'adminusers';
        
        $this->pageTitle = 'Панель администрирования';
        
        $this->render('index');
        
    }
    public function actionJson() {
        
        //sleep(1);
        
        $text = filter_input(INPUT_POST, 'text', FILTER_SANITIZE_STRING);
        $page = filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 1,
                'min-range' => 1,
            ),
        ));
        
        $limit = filter_input(INPUT_POST, 'limit', FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 20,
                'min-range' => 1,
            ),
        ));        
        
        $result = $this->model->getAll($text, $page, $limit);
        $this->data['users'] = $result['data'];
        
        echo $this->renderPartial('ulist');
        
    }
    
    public function actionTest() {
        
        $this->render('test'); 
       
    }
    
}