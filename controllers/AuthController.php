<?php

// структура параметра авторизации в сессии такова
/*
 * [auth] => Array
  (
  [id] => 1  -> ИД сотрудника
  [fname] => Алексей
  [lname] => Старцев
  [pname] => Анатольевич
  [groupid] => 1 -> ИД группы пользователя
  [groupname] => Администратор -> название группы
  [position] => -> должность
  [shortname] => Старцев А. А. -> сокращенное фио
  [roles] => Array -> доступные роли (гранты выполняемых операций)
  (
  [ACE_OPEN_SHIFT_VIEW] => 1
  [ACE_MESSAGE_ADD] => 1
  [ACE_MESSAGE_EDIT] => 1
  [ACE_MESSAGE_DELETE] => 1
  [ACE_SHIFT_CREATE] => 1
  [ACE_SHIFT_CLOSE] => 1
  [ACE_SHIFT_SIGN] => 1
  [ACE_ARCHIVE_VIEW] => 1
  )

  [grants] => Array -> перечень журналов с которыми пользователю разрешена работа
  (
  [1] => Журнал начальника смены станции НГРЭС
  [2] => Журнал начальника смены электроцеха
  [3] => Журнал начальника смены котлотурбинного цеха
  [4] => Журнал начальника смены цеха ТАИ
  )

  )
 */

/** @property AuthModel $model  */
class Auth extends Controller {

    public function __construct() {
        parent::__construct();
        $this->pageTitle = 'Авторизация пользователя';
    }

    /**
     * Дефолтное событие контрлеера авторизации.
     * Отрисовка формы аторизации и запрос (аяксом) списка пользователей
     */
    public function actionIndex() {

        Session::del('auth');
        
        // добавляем скрипт автозаполнения ввода фамилии
        $this->js[] = 'autocomplete';
        $this->render('login');
    }

    /**
     * Событие контроллера авторизации пользователя
     */
    public function actionLogin() {

        $userid = get_param($_POST, 'userid');
        $userpwd = get_param($_POST, 'password');

        if (!($userid && $userpwd)) {
            // если поля логина и пароля не заданы,
            // то делать тут нечего. 
            // по идее сюда попасть не должны, т.к. поля required, но на всякий случай..
            $this->redirect(array(
                'location' => 'auth/',
            ));
        }


        Session::del('auth');
        $authdata = $this->model->login($userid, $userpwd);

        if ($authdata === false)
            $this->appendDebug( 'Авторизация не пройдена. Проверьте правильность ввода пароля.');
        else {
            Session::set('auth', $authdata);
            $this->authdata = $authdata;
            
            if ($userpwd === '123') {
                // Если пароль дефолтный, то отправим на страцицу смены пароля
                $this->appendDebug('Ваш пароль не надежен. Его нужно изменить!', 3);
                $this->redirect(array(
                    'location' => $this->selfurl . 'newpassword/',
                ));
            }
        }
        
        // посмотрим адрес из сессии, куда ши до авторизации
        $location = Session::get('query', '');
        $this->redirect(array(
            'location' => $location,
        ));
        // (если авторизация не прошла, то оттуда будет редирект на авторизацию,
        // а из сесии покажется ошибка)
    }

    public function actionSavepassword() {

        
        $userid = get_param($this->authdata, 'id');
        $userpwd = get_param($_POST, 'password');

        if ($userid && $userpwd) {
            $res = $this->model->setNewPassword($userid, $userpwd);
            if ($res)
                $this->appendDebug('Пароль успешно изменен. При следующей авторизации используйте новый пароль!', 2);
            else
                $this->appendDebug('Пароль не изменен!');
        }
        $this->redirect();
    }

    /**
     * Устновка нового пароля пользователя
     */
    public function actionNewpassword() {

        $this->pageTitle = 'Смена пароля';
        
        if (get_param($this->authdata, 'id') === false) {
            // Если ид пользователя нет, или авторизация не пройдена вовсе,
            // то шагаем на домашнюю страницу
            $this->redirect();
        }
        $this->render('change');
    }

    /**
     * действие выхода пользователя,
     * удаление анных из сесии и регистрация события выхода в журнале
     */
    public function actionLogout() {

        Session::destroy();
        
        if ($this->authdata) {
            $this->model->logout(get_param($this->authdata, 'sid'));
            Session::del('auth');
            Session::del('jid');
        }
        $this->redirect();
    }

    /**
     * Функциз возвращает в json формате список всех пользователей
     * (вызов аяксом на стринице авторизации) для работы автозаполнения
     */
    public function actionGetlist() {
        
        $result = array();
        
        if ($this->isPOST()) {
            // для безопасности, проверяем каким способом запрошена страцица,
            // и если это не POST, то спсок автоподстановки отправлен не будет
            if ($this->isAjax())
                $result = $this->model->getnames();
            echo json_encode($result);
        } else
            $this->redirect();
    }

    /**
     * Процедура выбора оперативного журнала, по ссылке с главной страницы,
     * с которым пользователь собирается производить работу
     * site.ru/change/id/X
     */
    public function actionChange() {

        Session::del('jid');
        $depid = get_param($this->arguments, 'id');
        if (filter_var($depid, FILTER_VALIDATE_INT) && $depid > 0) {
            //if ($depid == 7) {
            //    Controller::appendDebug('Журнал находится на техническом обслуживании', 1, 1);
            //} else 
            Session::set('jid', $depid);
        }
        
        $loc = $this->isRoleGranted('ACE_OPEN_SHIFT_VIEW') ? 'journal/' : 'journal/archive/';
        
        $this->redirect(array(
            'location' => $loc,
        ));
    }
    
    /**
     * Страница выбора оперативного журнала
     */
    public function actionSelect() {
        
        $this->pageTitle = 'Выбор журнала';
        
        // если пользователь не авторизован, то шлем...
        if ($this->authdata === false)
            $this->redirect(array(
                'location' => 'auth/',
            ));
        
        // получим список журналов доступных для пользователя,
        // и отрисуем ссылки выбора
        $this->data['journals'] = get_param($this->authdata, 'journals');
        $this->render('journal-list');
    }

}
