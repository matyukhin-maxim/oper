<?php

require_once 'models/JournalModel.php';

class Equipment extends Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function actionIndex() {
        $this->render('', false);
        
        $mod = new JournalModel();
        
        var_dump($mod->getListPeriod());
        for($dd = 1; $dd < 100000000; $dd++);
        
        var_dump(glob("models/*.php"));
        
        $this->render('');
        
    }
    
}
