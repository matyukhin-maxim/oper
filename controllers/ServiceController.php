<?php

class Service extends Controller {

    public function actionIndex() {

        $this->appendDebug("Выполняется техническое обслуживание сервера. <br />"
                . "Сайт возобновит работу через некоторое время.", 0, 1);

        $this->render('');
    }

}
