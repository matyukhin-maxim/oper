<?php
/**
 * Created by PhpStorm.
 * User: fellix
 * Date: 09.05.14
 * Time: 17:58
 */

class Index extends Controller{

    public function actionIndex() {
        
        if ($this->data['authdata'] === false) {
            $this->redirect(array(
                'location' => 'auth/',
            ));
        }
        
        $this->render('index');
        
    }
    
    public function actionLogin() {
        
        $this->render('login');
        
    }

}
