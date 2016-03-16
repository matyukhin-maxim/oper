<?php

/** @property DashboardModel $model  */
class Dashboard extends Controller {

    private $journal_id = -1;
    private $shiftinfo = false;

    public function __construct() {
        parent::__construct();
        $this->journal_id = Session::get('depid', -1);

        if (get_param($this->data, 'authdata') !== false) {
            // если данные по авторизации есть (пользователь авторизован)
            // но данных о том с каким журналом он работает, то отправляем 
            // его на страницу выбора журнала (index ctrl)
            if ($this->journal_id <= 0)
                $this->redirect();
        } else // иначе отправляем на страницу авторизации           
            $this->redirect(array('location' => 'auth/'));

        // проверим разрешена ли работа с данным журналом (по списку грантов)
        // вывод ошибки и редирект в случае отказа
        if ($this->model->isJournalGranted($this->journal_id) === false) {
            Session::set('error', 'Доступ к указанному журналу запрещен.');
            $this->redirect();
        }

        // Заносим в параметры шаблона название журнала с которым работаем
        $this->data['jname'] = get_param($this->grants, $this->journal_id, 'n/a');

        // авторизация пройдена, журнал выбран, все супер..
        // значит пора найти смену с которой будем работать
        // или предложить создать новую, если открытых смен нет, и есть права
        $this->shiftinfo = $this->model->getOpenShiftParam($this->journal_id);
    }

    public function actionIndex() {

        //$this->data['createbutton'] = $this->isGranted('ACE_SHIFT_CREATE');
        $this->data['shift'] = $this->shiftinfo;
        
        $this->data['messages'] = array();
        $this->data['equip'] = array();
        
        if ($this->shiftinfo !== false) {
            $this->data['messages'] = $this->model->getShiftMessages(get_param($this->shiftinfo, 'id'), $this->journal_id);
            $this->data['equip'] = $this->model->getShiftEquipment($this->journal_id, get_param($this->shiftinfo, 'id'));
        }
        
        $this->data['btn_new']   = $this->isGranted('ACE_MESSAGE_ADD');
        $this->data['btn_close'] = $this->isGranted('ACE_SHIFT_CLOSE');
        $this->data['btn_open']  = $this->isGranted('ACE_SHIFT_CREATE');
        $this->data['btn_equip'] = $this->isGranted('ACE_EQUIP_VIEW');

        if (self::$debugMode) {
            //$this->js[] = 'idle';
        }
        
        $this->render('index');
    }

    public function actionNewShift() {

        if ($this->shiftinfo !== false) {
            // если смена открыта, то создавать новую смену не надо
            // а бежать отсюда, и как можно быстрее...
            Session::set('error', 'Создание новой смены не требуеся. Смена уже открыта!');
            $this->redirect(array(
                'location' => $this->selfurl,
            ));
        }

        $params = $this->model->getNewShiftParams();
        $this->data['intervals'] = $params[0]; // deprecated
        $this->data['watches']   = $params[1]; // deprecated
        $this->data['cdate'] = date('Y-m-d');
        
        $this->data['pint']   = CHtml::drawCombo(get_param($params, 0), 'sinterval', 1, array('title'=>'period'),true);
        $this->data['pwatch'] = CHtml::drawCombo(get_param($params, 1), 'swatch',    1, array('title'=>'abbr'),true);

        echo $this->renderPartial('NewShiftCreate');
    }

    public function actionCloseShift() {

        /*
         * @todo перед закрытием смены выдать вопрос javascript`ом,
         * мол уверен ли пользователь в своем решении, и закрывать только после согласия
         * 
         * так же нужно выполнить проверку на заполнение состава смены
         * или хотябы тех должностей, которые помечены как обязательные (required)
         */
        
        if ($this->shiftinfo === false) {
            Session::set('error', 'Смена не открыта.');
            $this->redirect(array(
                'location' => $this->selfurl,
            ));
        }

        $authdata = Session::get('auth');

        if ($this->isGranted('ACE_SHIFT_CLOSE')) {
            $this->model->closeCurrentShift(get_param($this->shiftinfo, 'id'), get_param($authdata, 'id'));
            Session::set('error', 'Смена сдана.');
        } else
            Session::set('error', 'Недостаточно прав для сдачи смены.');

        $this->redirect(array(
            'location' => $this->selfurl,
        ));
    }

    public function actionNewmessage() {
        
        $this->data['cdate'] = date('Y-m-d');
        $this->data['ctime'] = date('H:i');
        $this->data['formtitle'] = 'Ввод нового сообщения';
        $this->data['msg-id'] = '';
        
        
        
        echo $this->renderPartial('message-edit');
    }
    
    public function actionEditMessage() {
        
        $this->data['cdate'] = date('Y-m-d');
        $this->data['ctime'] = date('H:i');
        $this->data['mtext'] = '';
        $this->data['special'] = '';
        $this->data['recid'] = '';
        
        $this->data['formtitle'] = 'Новое сообщение';
        
        
        $msgID = (int) get_param($this->arguments, 'mid');
        if (filter_var($msgID, FILTER_VALIDATE_INT) && $msgID > 0) {
            $this->data['formtitle'] = 'Редактирование сообщения';
            
            $minfo = $this->model->getMessageByID($msgID);
            
            $mdate = explode(' ', get_param($minfo, 'date_msg', date('Y-m-d H:i')));
            
            $this->data['cdate'] = get_param($mdate, 0);
            $this->data['ctime'] = get_param($mdate, 1);
            $this->data['mtext'] = get_param($minfo, 'comment');
            $this->data['special'] = get_param($minfo, 'special') === '1' ? 'checked' : '';
            $this->data['recid'] = $msgID;
        }
        
        echo $this->renderPartial('message-edit');
    }
    
    public function actionSaveMessage() {
        
        $btn = get_param($_POST, 'btn-save');
        $mid = (int) get_param($_POST, 'recid', 0);
        $authdata = Session::get('auth');
        
        // если пришлили сюда с формы редактирования сообщения
        // то btn-save будет не пустой (первая ступень проверки)
        // 
        // далее смотрим есть ли во входных параметрах, recid (он же номер записи)
        // в случае редактирования сообщения, если же его нет, то это новое сообщение
        //
        $edit = filter_var($mid, FILTER_VALIDATE_INT) && ($mid > 0);
        
        $msgStamp  = get_param($_POST, 'msg-date', date('Y-m-d'));
        $msgStamp .= ' ';
        $msgStamp .= get_param($_POST, 'msg-time', date('H:i'));
        
        if (!$edit) {
            // добавление нового собщения
            if ($this->isGranted('ACE_MESSAGE_ADD')) {
                
                $this->model->addNewMessage(
                        get_param($this->shiftinfo, 'id'), 
                        get_param($authdata, 'id'), 
                        get_param($_POST, 'message'),
                        $msgStamp,
                        get_param($_POST, 'special', 0));
            } else {
                Session::set('error', 'Не достаточно прав для добавления сообщений.');
            }
        } else {
            // редактирование существующего (удаление)
            
            if ($this->isGranted('ACE_MESSAGE_EDIT')) {
                
                $this->model->saveMessage($mid, $msgStamp, get_param($_POST, 'message'), get_param($_POST, 'special'), 0);
                
            } else {
                Session::set('error', 'Не достаточно прав для редакирования сообщений.');
            }
        }
        
        
        $this->redirect(array(
            'location' => $this->selfurl,
        ));
    }

    public function actionPOSTprocess() {

        // вся обработка post запросов будет осуществляться тут
        // это и создание смены, и добавление сообщений..
        // вобщем все...

        $authdata = Session::get('auth');

        if (get_param($_POST, 'newshift') !== false) {
            if ($this->isGranted('ACE_SHIFT_CREATE')) {
                $ok = $this->model->createNewShift(
                        $this->journal_id, get_param($_POST, 'sdate', date('Y-m-d')), 
                            get_param($_POST, 'sinterval', 1), 
                            get_param($_POST, 'swatch', 1), 
                            get_param($authdata, 'id')
                );
                if ($ok) {
                    Session::set('error', array(
                        'class' => 'alert-success',
                        'msg' => 'Новая смена успешно создана.',
                    ));
                }
            }
        }

        if (get_param($_POST, 'newmsg') !== false) {
            if ($this->isGranted('ACE_MESSAGE_ADD')) {
                $msgStamp = get_param($_POST, 'msg-date', date('Y-m-d'));
                $msgStamp .= ' ' . get_param($_POST, 'msg-time', date('H:i'));
                $this->model->addNewMessage(
                        get_param($this->shiftinfo, 'id'), 
                        get_param($authdata, 'id'), 
                        get_param($_POST, 'message'),
                        $msgStamp,
                        get_param($_POST, 'special', 0));
            }
            //if (self::$debugMode) die(print_r($_POST,true));
        }
        
        if (get_param($_POST, 'savecomp') !== false) {
                        
            if ($this->shiftinfo !== false) {
                // если смеа открыта
                Session::set('error', array(
                    'class' => 'alert-success',
                    'msg' => 'Состав смены сохранен.',
                ));
                $this->model->saveCompositions(get_param($this->shiftinfo, 'id'));
            }
            
        }

        $this->redirect(array(
            'location' => $this->selfurl,
        ));
    }

    public function actionCompositions() {

        // если нет прав на редктирование состава - давай, досвидания
        if (!$this->isGranted('ACE_COMPOSITION_EDIT')) {
            Session::set('error', 'Нет прав на редактирование состава смены.');
            $this->redirect(array(
                'location' => $this->selfurl,
            ));
        }
        
        // если смена не отрыта - давай доствидания
        if ($this->shiftinfo === false) {
            Session::set('error', 'Перед редактированием состава смены, смену нужно открыть!');
            $this->redirect(array(
                'location' => $this->selfurl,
            ));
        }
        
        $this->pageTitle = 'Cостав смены';
        $this->data['compositions'] = $this->model->getShiftCompositions($this->journal_id, get_param($this->shiftinfo, 'id'));
        $this->data['positions'] = $this->model->getJournalComposition($this->journal_id);
        $this->data['users'] = $this->model->getAllUsersByPositions();
        
        $this->render('compositions_edit');
    }

    public function actionArchive() {
        
        $this->pageTitle = 'Архивные смены';
        
        $archive = $this->model->getArchiveShifts($this->journal_id);
        $this->data['archive'] = $archive;
         if (count($archive) === 0) {
            Session::set('error', array(
                'class' => 'alert-warning',
                'msg' => 'Архив пуст.',
            ));
        }
        
        $this->render('archive_list');
        
    }    
    
    public function actionViewShift() {
        
        $shift_id = get_param($this->arguments, 'id', 0);
        
        $this->pageTitle = 'Просмотр смены';
        $this->data['messages'] = $this->model->getShiftMessages($shift_id, $this->journal_id);
        $this->data['shift'] = $this->model->getShiftInfo($shift_id, $this->journal_id);
        $this->render('shift_preview');
    }
    
    public function actionSaveEquip() {
        
        if ($this->isAjax()) {
            $equip = get_param($_POST, 'equip');
            $result = $this->model->saveEquipment($equip, get_param($this->shiftinfo, 'id'));
            
            $this->data['eclass']   = $result ? 'alert-info' : 'alert-warning';
            $this->data['emessage'] = $result ? 'Изменения сохранены' : 'Ошибка сохранения';
            echo $this->renderPartial('../error_template');
            
            
        } else            
            $this->redirect (array(
                'location' => $this->selfurl,
            ));
    }
    
    public function actionTTT() {
        
        $this->render('ttt');
        
    }
    
    public function actionTest() {
        
        $data = $this->model->getShiftCompositionsNEW($this->journal_id, get_param($this->shiftinfo, 'id'));
        
        $this->data['plist'] = get_param($data, 'positions');
        $this->data['ulist'] = get_param($data, 'users');
        $this->data['rlist'] = get_param($data, 'rules');
        
        $ids = array_keys($this->data['plist']);
        $this->data['positions'] = $this->model->getAllUsersByPositions($ids);
        
        // если не пустое, то рисуются комбо-боксы, 
        // иначе фамилии отображаются как простой текст
        //
        $this->data['editmode'] = 1;
        
        $this->render('comp_preview');
    }
    
    public function actionReloadMessages() {
        
        if ($this->isAjax()) {
            $this->data['messages'] = $this->model->getShiftMessages(get_param($this->shiftinfo, 'id'), $this->journal_id);
            echo $this->renderPartial('messages-list');
        } else {
            $this->data['eclass'] = 'alert-danger';
            $this->data['emessage'] = 'Нарушение прав доступа.';
            echo $this->renderPartial('../error_template');
        }
    }










    // template functions
    public function drawSignatures() {
        //echo $this->renderPartial('signatures');
    }

    public function drawInfo() {
        echo $this->renderPartial('shiftinfo');
    }

    public function drawMessages() {
        echo $this->renderPartial('messages-panel');
    }
    
    public function drawMessagesList() {
        echo $this->renderPartial('messages-list');
    }

    public function drawEquip() {
        $data  = get_param($this->data, 'equip', array());
        if (count($data)) echo $this->renderPartial('equipment');
    }

    public function drawCompositionsPanel() {

        $this->data['positions'] = $this->model->getJournalComposition($this->journal_id);
        echo $this->renderPartial('compositions');
    }
    
    public function drawCompositions() {
        
        $positions = $this->model->getJournalComposition($this->journal_id);
           
    }

}
