<?php

/** @property JournalModel $model  */
class Journal extends Controller {

    private $journal_id     = -1;
    private $open_shift_id  = -1;

    public function __construct() {
        parent::__construct();
        
       // $this->redirect(array(
        //    'location' => 'service/',
        //));

        $this->journal_id = Session::get('jid', -1);

        // если сюда пришел не авторизованный пользователь, то шлем его на.. (авторизацию)
        if ($this->authdata === false)
            $this->redirect(array(
                'location' => 'auth/',
            ));

        // если журнал не выбран, то отправляем пользователя, на страницу выбора
        if ($this->journal_id <= 0)
            $this->redirect(array(
                'location' => 'auth/select/',
            ));

        // если какимто образом оказался выбран журнал, прав на который у пользователя нет,
        // то скажем ему об этом, и отправим на страцису выбора...
        if (!$this->isJournalGranted($this->journal_id)) {
            $this->appendDebug('Нет прав на просмотр выбранного журнала!');
            $this->redirect(array(
                'location' => 'auth/select/',
            ));
        }

        // Получим имя жунала из БД
        if (Model::$db) {
            $this->data['jname'] = $this->model->getJournalFullName($this->journal_id);
            $this->open_shift_id = $this->model->getOpenShiftID($this->journal_id);
        }
    }

    /**
     * Просмотр оперативной смены
     */
    public function actionIndex() {
        
        if (!$this->isRoleGranted('ACE_OPEN_SHIFT_VIEW')) {
            //$this->appendDebug('Нет прав на просмотр оперативной смены.', 1);
            $this->redirect(array(
                'location' => $this->selfurl . 'archive/',
            ));
        }
        
        $grant = get_param($this->grants,  $this->journal_id);
        $editmode = (int)get_param($grant,'em') === 1;
            
        if ($this->isRoleGranted('ACE_SHIFT_SIGN')) {

            // 2015-05-12
            // Старцев хочет иметь возможность подписывать смену которая еще не закрыта
            // Можно бы было переадресовать на actionPreview но тогда там косяк с кнопкой "назад в архив"
            // поэтому проще продублировать запросы по одписям, и отрисовать другой шаблон, но только для тех
            // у кого есть право подписи

            if (!$editmode && $this->open_shift_id > 0)
                $this->redirect(array(
                    'location' => $this->selfurl . 'preview/',
                ));
        }

        $this->data['subtitle'] = ' :: Оперативная смена';
        $this->js[] = 'message-handler';
        
        //$shift_info = $this->model->getShiftInformation($this->open_shift_id);
        //$this->data['shift'] = $shift_info;
        
        $this->render('index', false);

        if ($this->open_shift_id > 0) {
            
            $shift_info = $this->model->getShiftInformation($this->open_shift_id);
            $this->data['shift'] = $shift_info;
            $this->render('shift-info', false);
            
            $this->data['messages'] = $this->model->getShiftMessages($this->open_shift_id);
            
            
            $canCloseShift = ((get_param($this->authdata, 'id') === get_param($shift_info, 'c_user_id'))) 
                    || (get_param($this->authdata, 'groupid') == 1);

            // если есть право на редактирование журнала
            // и теущий пользователь - тот кто принял смену
            // то дадим ему возможость закрыть смену
            $this->data['link_close'] = CHtml::drawLink('Сдать смену', array(
                'class' => 'btn btn-primary btn-block ' . ($canCloseShift ? '' : 'disabled'),
                'id' => 'close-shift-link',
            ));

            // поле с разрешеня главного инженера заполним по умлчанию
            // временем конца мены (т.е. либо 8:00, либо 20:00)
            $interval = explode('-',get_param($shift_info, 'period'));
            $this->data['time_agree'] = trim(get_param($interval, 1));
            
            
            // КНОПКИ ПЕЧАТИ ОТЧЕТА
            $this->data['printgroup'][] = CHtml::drawLink('Просмотр отчета', array(
                'class' => 'btn btn-default',
                'href' => '/journal/report/',
                'target' => '_blank',
            ));
            $this->data['printgroup'][] = CHtml::drawLink('4 смены', array(
                'class' => 'btn btn-default',
                'href' => '/journal/report/cnt/4/',
                'target' => '_blank',
            ));
            $this->data['printgroup'][] = CHtml::drawLink('6 смен', array(
                'class' => 'btn btn-default',
                'href' => '/journal/report/cnt/6/',
                'target' => '_blank',
            ));
            
            $this->data['candel'] = $this->isRoleGranted('ACE_MESSAGE_DELETE');

	        $mustC = get_param($_COOKIE, 'open-comp', 0) !== $this->open_shift_id;
	        $mustD = get_param($_COOKIE, 'open-devs', 0) !== $this->open_shift_id;

	        $this->data['compBtn'] =  CHtml::drawLink('Состав смены', array(
		        'class' => 'btn btn-default ' . ( $mustC ? 'must' : ''),
		        'href' => '/journal/compositions/',
	        ));

	        $this->data['devsBtn'] = CHtml::drawLink('Оборудование', array(
		        'class' => 'btn btn-default ' . ( $mustD ? 'must' : ''),
		        'href' => '/journal/equipment/',
	        ));

            //$this->render('messages-sign-view', false);
            
            $this->render('messages'     . ($editmode ? '' : '-view'), false);
            $this->render('shift-footer' . ($editmode ? '' : '-view'), false);
            
        } else {
            $this->data['gsc'] = $this->isRoleGranted('ACE_SHIFT_CREATE', $this->journal_id);
            $this->render('no-shift', false);
        }
        
        $this->render('');
        //$this->render('../action-panel'); // render footer
    }

    public function actionDeleteMessage() {

        $result = array(
            'ok' => false,
            'message' => 'Нарушение прав доступа',
        );

        if ($this->isAjax() && $this->isPOST() && $this->isRoleGranted('ACE_MESSAGE_DELETE')) {

            // выполняем удаление только если страница запрошена аяксом и POSTом
            // т.о. блокируя ручной ввод в адресной строке
            // проверим указан ли идентификатор сообщения
            // и является ли он числом
            $msg_id = (int) get_param($this->arguments, 'id');
            if (filter_var($msg_id, FILTER_VALIDATE_INT) && $msg_id > 0) {
                
                $result = $this->model->setMessageDeleteMark($msg_id, $this->open_shift_id);
            } else
                $result['message'] = 'Параметр запроса указан не верно';
        }
        echo json_encode($result);
    }

    public function actionEditMessage() {

        $result = array(
            'ok' => false,
            'message' => 'Нарушение прав доступа',
        );

        if ($this->isAjax() && $this->isPOST() && $this->isRoleGranted('ACE_MESSAGE_EDIT')) {

            // выполняем удаление только если страница запрошена аяксом и POSTом
            // т.о. блокируя ручной ввод в адресной строке

            $message = get_param($_POST, 'message', '');

            // проверим указан ли идентификатор сообщения
            // и является ли он числом
            $msg_id = (int) get_param($_POST, 'mid');
            if (filter_var($msg_id, FILTER_VALIDATE_INT) && $msg_id > 0) {

                $result = $this->model->setMessageText($msg_id, $this->open_shift_id, $message);
            } else
                $result['message'] = 'Параметр запроса указан не верно';
        }

        echo json_encode($result);
    }

    public function actionChangeMessageTime() {

        $result = array(
            'ok' => false,
            'message' => 'Нарушение прав доступа',
        );

        if ($this->isAjax() && $this->isPOST() && $this->isRoleGranted('ACE_MESSAGE_EDIT')) {

            // выполняем удаление только если страница запрошена аяксом и POSTом
            // т.о. блокируя ручной ввод в адресной строке
            // проверим указан ли идентификатор сообщения
            // и является ли он числом
            $msg_id = (int) get_param($_POST, 'mid');
            $m_date = get_param($_POST, 'mdate');
            $m_time = get_param($_POST, 'mtime');
            $newDate = date2mysql($m_date, $m_time);
            
            // Проеверка на то чтобы новое время не выходило за рамки интервала смены
            $sinfo = $this->model->getShiftInformation($this->open_shift_id);
            if ($newDate > get_param($sinfo, 'ed') || 
                    $newDate < get_param($sinfo, 'sd')) {
                $this->appendDebug("Время сообщения выходит за границы интервала смены. <br /> Возможно вы забыли сдать смену!", 3);
                //$result['message'] = 'Время сообщения указано неверно.';
                $result['ok'] = true; // чтобы страница обновилась, сделаем вид что все ок!
            } elseif (filter_var($msg_id, FILTER_VALIDATE_INT) && $msg_id > 0 && !empty($newDate)) {
                $result = $this->model->setMessageTime($msg_id, $this->open_shift_id, $newDate);
            } else
                $result['message'] = 'Параметр запроса указан не верно';
        }

        echo json_encode($result);
    }

    public function actionNewMessage() {

        $result = array(
            'ok' => false,
            'message' => 'Нарушение прав доступа',
        );

        if ($this->isAjax() && $this->isPOST() && $this->isRoleGranted('ACE_MESSAGE_ADD')) {
            
            $message = get_param($_POST,'message','-');
            $msgtime = explode(' ', get_param($_POST, 'mdate'));
            $mdate   = date2mysql(get_param($msgtime,0), get_param($msgtime,1));
            
            // и тут сделаем онтроль на время сообщения
            $sinfo = $this->model->getShiftInformation($this->open_shift_id);
            
            if (empty($mdate)) {
                $result['message'] = 'Формат даты указан не верно!';
            } elseif ($this->open_shift_id === -1) {
                $result['message'] = 'Оперативная смена не открыта';
            } elseif ($mdate > get_param($sinfo, 'ed') || 
                    $mdate < get_param($sinfo, 'sd')) {
                $this->appendDebug("Время сообщения выходит за границы интервала смены. <br /> Возможно вы забыли сдать смену!", 3);
                $result['ok'] = true; // чтобы страница обновилась, сделаем вид что все ок!
            } else {
                $result = $this->model->addNewMessage($this->open_shift_id, $message, $mdate, get_param($this->authdata,'id'));
            }
            
        }

        echo json_encode($result);
    }

    public function actionNewShift() {

        if ($this->open_shift_id > 0) {
            // если смена открыта, то создавать новую смену не надо
            // а бежать отсюда, и как можно быстрее...
            $this->appendDebug('Создание новой смены не требуеся. Смена уже открыта!');
            $this->redirect(array(
                'location' => $this->selfurl,
            ));
        }
        
        if ($this->isPOST() && $this->isRoleGranted('ACE_SHIFT_CREATE')) {
            // если это пост запрос, то это
            // значит что в форме создания смены нажали кнопу "ок"
            // следовательно надо создать новую смену
            $s_date = date2mysql(get_param($_POST, 'sdate'));
            $speriod = get_param($_POST,'sinterval',1);
            $swatch  = get_param($_POST,'swatch',1);
            
            $res = $this->model->createNewShift($this->journal_id, $s_date, $speriod, $swatch, get_param($this->authdata,'id'));
            if (get_param($res,'error')) {
                $this->appendDebug('Создать новую смену не удалось!', 3);
                $this->appendDebug(get_param($res, 'error'), 3);
            } else {
                $this->appendDebug('Смена принята. Просмотреть отчет за предыдущую смену можно из архива.',2);

	            // удаляем куки-флаги о просмотре состава смены и оборудования
	            setcookie('open-comp', null, -1, '/');
	            setcookie('open-devs', null, -1, '/');
            }
            
            $this->redirect(array(
                'location' => $this->selfurl,
            ));
        }
        
        $this->data['cdate'] = date('d.m.Y');
        $this->data['useropen'] = get_param($this->authdata,'fio','n/a');
        
        $intervals = $this->model->getListPeriod();
        $watches   = $this->model->getListWatches();
        
        $lastint = $this->model->getLastShiftInterval($this->journal_id);
        
        $this->data['pint'] = CHtml::drawCombo($intervals, 3 - $lastint, array(
            'keys' => array('title'=>'period'),
            'htmlOptions' => array(
                'name' => 'sinterval',
	            'id' => 'iselector',
	            'required' => true,
            ),
        ));
        $this->data['pwatch'] = CHtml::drawCombo($watches, 1, array(
            'keys' => array('title'=>'abbr'),
            'htmlOptions' => array(
                'name' => 'swatch',
	            'id' => 'wselector',
	            'required' => true,
            ),
        ));

        echo $this->renderPartial('modal-shiftcreate');
    }
    
    public function actionCloseShift() {
        
        if (!$this->isAjax()) {
            $this->appendDebug('Нарушение прав доступа!');
            $this->redirect(array(
                'location' => $this->selfurl,
            ));
        }
        
        if ($this->open_shift_id === -1) {
            $this->appendDebug('Нет принятой смены, которую можно сдать!');
            die (1);
        }

        if ($this->isRoleGranted('ACE_SHIFT_CLOSE',  $this->journal_id)) {
            
            // проверка на сдающего
            // по идее пользователь не принимавший смену, сдать е не может (кнопка у него будет не доступна)
            // но мало-ли.. Если ввести в строку адреса, например, то можено напороться...
            $info = $this->model->getShiftInformation($this->open_shift_id);
            if (get_param($info, 'c_user_id') !== get_param($this->authdata, 'id')
                    && (int)get_param($this->authdata, 'groupid') !== 1) {
                $this->appendDebug('Сдать смену может лишь тот, кто её принял!', 1);
                // или админ ;)
                die(2);
            }
            
            $agree = get_param($_POST, 'agree');
            $res = $this->model->closeCurrentShift($this->open_shift_id, $agree);
            if (get_param($res, 'ok') === true) {
                //Session::del('auth'); // отменяем авторизацию
                //Session::del('jid'); //  забываем какой журнал был выбран (чтобы выбрать снова)
                Session::destroy(true); // Удалям сессию, заставляя таким способом снова пройти авторизацию
                $this->appendDebug('Смена успешно сдана. Для принятия смены, пройдите авторизацию.', 2);
            } else
                $this->appendDebug('Ошибка при закрытии смены <br />' . get_param($res, 'error'), 3);
        } else
            $this->appendDebug('Недостаточно прав для сдачи смены.',3);

        die(0);
    }
    
    public function actionEquipment() {
        
        $this->js[] = 'equipment';
        $this->data['subtitle'] = ':: Состояние оборудования';
        $this->render('index',false);
        
        $grant = get_param($this->grants,  $this->journal_id);
        $editmode = (int)get_param($grant,'em') === 1;
        
        // проверяем не пришел ли номер смены в аргументах
        $sid = filter_var(get_param($this->arguments, 'id'), FILTER_VALIDATE_INT);
        if (!$sid)
            // если не приходил, н будем смотреть на оперативную смену
            $sid = $this->open_shift_id;
        else
            // а если пришел, то это независимо от прав, рисуем форму просмотра
            $editmode = false;
        
        if ($sid > 0) {   
            $shift_info = $this->model->getShiftInformation($sid);
            $this->data['shift'] = $shift_info;
            $this->render('shift-info', false);
            
            //$this->data['backlink'] = get_param($_SERVER, 'HTTP_REFERER', ROOT. $this->selfurl);
            $this->data['btnback'] = CHtml::drawLink('Назад', array(
                    'class' => 'btn btn-default back-link',
                    //'id' => 'back-link',
                    'title' => 'Назад',
            ));
            
            $this->data['equip'] = $this->model->getShiftEquipment($sid, $this->journal_id);
            
            $this->render('equipment-list' . ($editmode ? '' : '-view'));
        } else {
            $this->appendDebug('Просмотр оборудования выбранной смены не возможен.',3);
            $this->redirect(array(
                'back'=> 1,
            ));
        }
    }
    
    public function actionEquipmentSave() {
        
        if (!$this->isAjax()) {
            $this->appendDebug('Нарушение прав доступа!');
            $this->redirect(array(
                'location' => $this->selfurl,
            ));
        }
        
        // если смена открыта, и право на редактирование оборудования текущего журнала есть
        if (($this->open_shift_id > 0) && $this->isRoleGranted('ACE_EQUIP_VIEW',  $this->journal_id)) {
            // то сохраняем присланные данные
            $equip = get_param($_POST, 'equip');
            $result = $this->model->saveEquipment($equip, $this->open_shift_id);
            if ($result > 0)    {
	            $this->appendDebug ('Данные по оборудованию сохранены',1);

	            // ставим куку-флаг о сохранении состава оборудования
	            setcookie('open-devs', $this->open_shift_id , time() + 24 * 3600, '/');
            } else                $this->appendDebug ('Ошибка сохранения данных.');
            $this->drawError();
        }
        
    }
    
    public function actionArchive() {
        
        $this->pageTitle = 'Архив смен';
        $this->data['subtitle'] = ':: Просмотр архива';
        $this->js[] = 'archive';
        
        $perpage = 9;
        
        $current = (int)get_param($this->arguments, 'page', 1);
        Session::set('archpage', $current);
        $count   = $this->model->getCountArchiveShifts($this->journal_id);
        
        $pagination = new Pager($count, $perpage, $current);
        $pagination->setUrl(ROOT . $this->selfurl . 'archive/');
        $this->data['pager'] = $pagination->draw();
        
        $this->data['archive'] = $this->model->getArchiveShifts($this->journal_id, get_param($this->authdata, 'id'), $pagination->m_current - 1, $perpage);
        $this->data['sign'] = $this->isRoleGranted('ACE_SHIFT_SIGN');
        
        $this->render('index', false);
        if ($count > $perpage) $this->render('pagination',false);
        $this->render('archive-list');
        
    }
    
    public function actionSignShift() {

        if (!$this->isAjax())
            $this->redirect();
        
        $result = array('ok' => false);
        
        if (!$this->isRoleGranted('ACE_SHIFT_SIGN')) {
            $result['error'] = 'Нарушение прав доступа';
            die(json_encode($result));
        }
        
        $sid = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, array(
            'options' => array(
                'min_range' => 1,
            ),
        ));
        
        if ($sid) {
            
            $result = $this->model->setShiftSignature($sid, get_param($this->authdata,'id'));
            
        } else $result['error'] = 'Номер смены указан не верно';
        
        
        echo json_encode($result);
    }

    public function actionPreview() {
        
        $this->pageTitle = 'Просмотр смены';
        $this->data['subtitle'] = ':: Просмотр смены';
        $this->js[] = 'archive';
        
        $sid = filter_var(get_param($this->arguments, 'id', $this->open_shift_id), FILTER_VALIDATE_INT);
        
        $shift_info = $this->model->getShiftInformation($sid);
                
        // проверим что смена соответствует выбранному журналу 
        // (если вдруг просто будут id водить в строке адреса)
        $canview = get_param($shift_info, 'journal_id') === $this->journal_id;
        if ($canview) {
            $this->render('index', false);
        } else {
            $this->appendDebug ('Просмотр выбранной смены не возможен.',3);
             $this->redirect(array(
                'back'=> 1,
            ));
        }
        
        $this->data['messages'] = $this->model->getShiftMessages($sid);
        $this->data['shift'] = $shift_info;
        
        $tagree = get_param($shift_info, 'agree');
        $this->data['time_agree'] = empty($tagree) ? '--:--' : $tagree;
        
        
        $this->data['eid'] = "id/$sid/"; // добавочная строка к ссылке на оборудование и состав смены
        
        $signs = $this->model->getShiftSignatuses($sid);
        $this->data['signatures'] = $signs;
        $this->data['sign_info'] = count($signs) ? count($signs) . ' человек (а)' : '';
        
        $signed = 0;
        foreach ($signs as $value) {
            $signed = max($signed, get_param($value, 'user_id') === get_param($this->authdata, 'id'));
        }
        $lname = $signed ? 'Подписано' : 'Подписать';
        $lclass = $signed ? 'btn-primary disabled' : 'btn-default';

        $btn_sign = CHtml::drawLink($lname, array(
                    'href' => '',
                    'class' => "btn $lclass btn-block",
                    'sid' => $sid,
                    'id' => 'btn-sign',
                    'title' => 'Подписать смену',
        ));
        $this->data['signbuton'] = $this->isRoleGranted('ACE_SHIFT_SIGN') ? $btn_sign : '';

        $page = Session::get('archpage');
        $this->data['link_close'] = CHtml::drawLink('Назад в архив', array(
                'class' => 'btn btn-primary btn-block text-bold back-link',
        ));
        
        // КНОПКИ ПЕЧАТИ ОТЧЕТА
        $this->data['printgroup'][] = CHtml::drawLink('Просмотр отчета', array(
            'class' => 'btn btn-default',
            'href' => "/journal/report/id/$sid/",
            'target' => '_blank',
        ));
        $this->data['printgroup'][] = CHtml::drawLink('4 смены', array(
            'class' => 'btn btn-default',
            'href' => "/journal/report/id/$sid/cnt/4/",
            'target' => '_blank',
        ));
        $this->data['printgroup'][] = CHtml::drawLink('6 смен', array(
            'class' => 'btn btn-default',
            'href' => "/journal/report/id/$sid/cnt/6/",
            'target' => '_blank',
        ));
        
        // т.к. сюда могли попасть не из архива, то кнопку "назад в архив" затрем
        // если ид открытой смены равен текущему запрощенному
        if (get_param($this->arguments, 'id') === false) {
            $this->data['link_close'] = '';
        }
        
        $this->render('shift-info', false);
        $this->render('messages-sign-view', false);
        $this->render('shift-footer-view', false);
        
        $this->render('');
    }
    
    public function actionCompositions() {
        
        $this->pageTitle = 'Состав смены';
        $this->js[] = 'compositions';
        
        //$sid = get_param($this->arguments, 'id', $this->open_shift_id);
        $sid = filter_var(get_param($this->arguments, 'id', $this->open_shift_id), FILTER_VALIDATE_INT);
        $info = $this->model->getShiftInformation($sid);
        
        
        if (get_param($this->arguments, 'id') || !$this->isRoleGranted('ACE_COMPOSITION_EDIT', $this->journal_id)) {
            // если указан ид (просмотр архива), то редактировать состав смены нельзя
            $this->data['viewonly'] = true;
            $this->data['subtitle'] = ':: Просмотр состава смены';
        } else {
            // иначе полный функционал
            $this->data['viewonly'] = false;
            $this->data['subtitle'] = ':: Редактирование состава смены';
            
            $this->data['btnsave'] = CHtml::drawLink('Сохранить', array(
                        'class' => 'btn btn-primary save-compositions',
                        //'id' => 'save-compositions',
                        'title' => 'Сохранить все изменения',
            ));
        }
        
        // ВРЕММЕННО!!!!
        //$this->data['viewonly'] = false;
        //$this->data['btnsave'] = CHtml::drawLink('Сохранить', array(
        //                'class' => 'btn btn-primary',
        //                'id' => 'save-compositions',
        //                'title' => 'Сохранить все изменения',
        //    ));
        // ВРЕМЕННО !!!!!
        
        
        // формируем кнопку "Назад" в завсимости от того, откуда пришли
        $this->data['btnback'] = CHtml::drawLink('Назад', array(
                    'class' => 'btn btn-default back-link',
                    //'id' => 'back-link',
                    'title' => 'Назад',
        ));

        if ($this->isAjax()) {
            
            if ($this->isPOST()) {
                // если пришли сюда аяксовым постом, то это сохранение состава смены
                
                //var_dump($_POST);
                //die;
                
                $udata = get_param($_POST, 'users'  , array());
                $tdata = get_param($_POST, 'trainee', array());
                $sdata = get_param($_POST, 'standin', array());
                
                $ok = $this->model->saveCompositions($info, $udata, $tdata, $sdata);
                if ($ok) {
	                $this->appendDebug ('Состав смены успешно сохранен.',2);

	                // ставим куку-флаг о сохранении состава смены
	                setcookie('open-comp', $this->open_shift_id, time() + 24 * 3600, '/');
                }

                die($this->drawError());
            }
            
            // а иначе это считывание параметров, и заполненеи первоначальных данных.
            echo json_encode($this->model->getCompositionValues($info));
            return;
        }
        
        if ($info === false) {
            $this->appendDebug('Не удалось получить информацию о смене', 3);
            $this->redirect(array(
                'back' => 1,
            ));
        }
        $this->data['shift'] = $info;
        
        
        $this->render('index', false);
        $this->render('shift-info', false);
        //var_dump($sid);
        
        //$vals = $this->model->getCompositionValues($info);
        //var_dump($vals);
        
        $compositions = $this->model->getCompositionRules($info);
        $positions = $this->model->getAllUsersByPositions(array_keys($compositions));
        
        $optionkey = array('title' => 'fio');
        foreach ($positions as $pid => $users) {
            $this->data['plist'][$pid] = CHtml::drawCombo($users, '', array(
                'keys' => $optionkey,
            ));
        }
        
        $this->data['cdata'] = $compositions;
        
        $this->render('comp-panel', false);
        $this->render('composition-list-new', false);
        $this->render('comp-panel', false);
        
        $this->render('');
    }
    
    /**
     * 
     * @param int $sid
     * @param tFPDF $report
     */
    public function generateReport($sid, &$report) {
        
        $title = trim($this->data['jname']);
        $info = $this->model->getShiftInformation($sid);
        
        // ОБЩИЕ ПАРАМЕТРЫ
        $report->AddPage();
        $report->SetTextColor(0, 63, 127);
        $report->Image('logo.png', 5, 5);
        
        // ШАПКА
        $report->SetFont('FreeSerifBoldItalic','BIU',18);
        $report->Cell(0, 10, $title, 0, 1, 'C');
        
        if (!$info) {
            $report->Ln(20);
            $report->Cell(0, 10, "Нет данных по запрошенной смене: [$sid]", 1, 0, 'C');
            return;
        }
        
        $sinfo = sprintf("%s Смена: %s Вахта: %s",  
                date2human(get_param($info, 'dopen', 'n/a')),
                get_param($info, 'period', 'n/a'),
                get_param($info, 'abbr', 'n/a'));
        
        $report->SetFont('FreeSerif','I',11);
        $report->Cell(0, 5, $sinfo, 0, 1, 'C');
        
        $report->SetFont('FreeSerifBold','B',10);        
        $report->Ln(5);
        
        // СОСТАВ СМЕНЫ
        $crules = $this->model->getCompositionRules($info);
        $cvalues = $this->model->getCompositionValues($info, true);
        //var_dump($cvalues); die;
        $allow = $this->model->getAllowRulesForReport($info);
        
        foreach ($crules as $pid => $lrule) {
            $pname = '';
            $users = '';
            $flag  = false;
            //var_dump($lrule);
            // правил может быть несколько, но должность одна (если в БД не чудить)
            foreach ($lrule as $rule) {
                $pname = get_param($rule,'pname');
                
                if (!in_array(get_param($rule,'ruleid'), $allow)) continue; // фильтруем правила
                
                $flag = true; // помечаем что должность нужно вывести, даже если юзер не указан
                // получаем пользователя, и список стажеров и дублеров по номеру правила
                $ulist = get_param($cvalues, get_param($rule, 'ruleid'));
                if ($ulist) {
                    // если параметры правила заполнены, то начинаем их обработку
                    $users .= empty($users) ? '' : PHP_EOL;
                    $users .= get_param($ulist,'user');
                    
                    $stdn = get_param($ulist, 'standin', array());
                    foreach ($stdn as $uname) $users .= "\n$uname (стажер)";
                    
                    $stdn = get_param($ulist, 'trainee', array());
                    foreach ($stdn as $uname) $users .= "\n$uname (дублер)";
                }
            }
            
            if ($flag) {
                $report->Cell(80, 5, "$pname:", 0, 0, 'R');
                $report->Cell(5);
                $report->MultiCell(80, 5, $users);
                //$total[array_search($uname, $total)];
            }
        }
        
        // ОБОРУДОВАНИЕ
        $report->Ln(); // Или лучше с новой страницы?
        //$report->Cell(0, 5, 'Оборудование', 0, 1, 'C', true);
        $equip = $this->model->getShiftEquipment($sid, $this->journal_id);
        foreach ($equip as $cell) {
            
            $etitle = get_param($cell, 'name');
            $etext  = trimHereDoc(htmlspecialchars_decode(get_param($cell, 'message')));
            
            $report->SetFont('FreeSerifBold','BU',10);
            $report->Cell(50, 5, "$etitle:");
            
            if (empty($etext)) {
                $etext = "нет.";
            }
            
            $report->SetFont('Tahoma','',10);
            $report->MultiCell(0, 5, $etext);
            $report->Ln();
        }
        
        
        // СООБЩЕНИЯ
        //$report->Ln(45);
        $report->AddPage();
        $report->SetFont('FreeSerifBoldItalic','BIU',10);
        $report->Cell(30,  8, 'Время', 'LTB', 0, 'C');
        $report->Cell(0  , 8, 'Содержание записей в течении смены', 'RTB', 1, 'C');
        $report->Ln(5);
        $messages = $this->model->getShiftMessages($sid);
        foreach ($messages as $item) {
            $mtext = htmlspecialchars_decode(get_param($item, 'comment'));            
            $report->SetFont('FreeSerifBold','B',9);
            $report->Cell(30, 5, get_param($item, 'mt'), 0, 0, 'C');
            $report->SetFont('FreeSerif','',9);
            $report->MultiCell(0, 5, $mtext);   
        }

        // ПОДПИСИ
        $report->Ln(10);
        $signatures = $this->model->getShiftSignatuses($sid);
        if (count($signatures) > 0) {
            $report->SetFont('FreeSerifBoldItalic','BIU',12);
            $report->Cell(0, 5, 'Отметки руководителей НГРЭС об ознакомлении с записями', 0, 1, 'C');
            $report->Ln(3);
            
            $report->SetFont('Tahoma','',10);
            $report->Cell(25, 5, 'Дата', 'B', 0, 'C');
            $report->Cell(25, 5, 'Время', 'B', 0, 'C');
            $report->Cell(90, 5, 'Указания и замечания', 'B', 0, 'C');
            $report->Cell(0, 5, 'Ф.И.О', 'B', 1, 'C');
            
            $report->SetFontSize(9);
            foreach ($signatures as $item) {
                $date = explode(' ', get_param($item, 'stamp'));
                $report->Ln(4);
                
                $report->Cell(25, 5, get_param($date, 0), 0, 0, 'R');
                $report->Cell(25, 5, get_param($date, 1), 0, 0, 'R');
                $report->Cell(90, 5, 'С записями ознакомился', 0, 0, 'C');
                $report->Cell(0, 5, get_param($item,'uname'), 0, 0, 'C');
            }
        }
        
        // ПОДВАЛ
        $report->Ln(10);
        $report->SetFont('FreeSerifBoldItalic','BI',10);
        
        if (get_param($info, 'dclose')) {
        
            $report->Cell(50, 5, "С разрешения", 0, 'L');
            $report->Cell(30, 5, get_param($info,'agree'), 0, 0, 'C');
            $report->Cell(50, 5, 'Смену принял:', 0, 0, 'R');
            $report->Cell(0, 5, get_param($info, 'ou', '-'), 0, 1, 'R');

            $report->Cell(50, 5, "главного инженера", 0, 'L');
            $report->Cell(30, 5, '', 0, 0, 'C');
            $report->Cell(50, 5, 'Смену сдал:', 0, 0, 'R');
            $report->Cell(0, 5, get_param($info, 'cu', '-'), 0, 1, 'R');
            
        } else {
            
            $report->SetFontSize(20);
            $report->Cell(0, 10, 'СМЕНА НЕ СДАНА!', 1, 0, 'C');
            
        }
        
    }
    
    public function actionReport() {
        
        $sid = filter_var(get_param($this->arguments, 'id', $this->open_shift_id), FILTER_VALIDATE_INT);
        $cnt = filter_var(get_param($this->arguments, 'cnt', 1), FILTER_VALIDATE_INT, array(
            'options' => array(
                'min_range' => 1,
                'max_range' => 6,
            ),
        ));
        
        if (!$cnt) $cnt = 1;
        
        // Выбираем последние CNT закрытых смен, начиая со смены с ид SID
        // по текущему журналу
        // и последовательно скармливаем их процедуре генерации отчета
        
        $repid = array_reverse($this->model->getShiftsForReport($this->journal_id, $sid, $cnt));
        if (!$repid) {
            $this->appendDebug('Не удалось получить список смен для отчета.', 3);
            $this->redirect(array(
                'back' => 1,
            ));
            //var_dump($repid);
            //return;
        }
        $pdf = new tFPDF();
        
        $pdf->AddFont('FreeSerif',  '',     'FreeSerif.ttf',true);
        $pdf->AddFont('FreeSerif',  'I',    'FreeSerifItalic.ttf',true);
        $pdf->AddFont('FreeSerifBold',  'B',    'FreeSerifBold.ttf', true);
        $pdf->AddFont('FreeSerifBoldItalic',  'BI',   'FreeSerifBoldItalic.ttf', true);
        $pdf->AddFont('Tahoma',     '',     'Tahoma.ttf',true);
        
        $pdf->SetTitle('Оперативный отчет по смене', true);
        $pdf->SetAuthor(get_param($this->authdata, 'fio'), true);
        $pdf->SetAutoPageBreak(true, 10);
        
        
        foreach ($repid as $snum) {
            $this->generateReport($snum, $pdf);
        }
        
        $fname = "oper_report_" . uniqid() . '.pdf';
        //$pdf->Output($fname, 'D');
        $pdf->Output();
    }

	public function actionCalc() {
		// Расчет литеры выхты для указанной даты и интервала смены

		$args = filter_input_array(INPUT_POST, [
			'sdate' => FILTER_SANITIZE_STRING,
			'sinterval' => [
				'filter' => FILTER_VALIDATE_INT,
				'options' => [
					'min_range' => 1,
					'max_range' => 2,
					'default' => 1,
				],
			],
		]);

		$dateFormat = 'd.m.Y';
		//       [А, В, А, Г, Б, Г, Б, А, В, А, В, Б, Г, Б, Г, В];
		$wlist = [1, 3, 1, 4, 2, 4, 2, 1, 3, 1, 3, 2, 4, 2, 4, 3];
		$wd = DateTime::createFromFormat($dateFormat, get_param($args, 'sdate')) ?:
			  DateTime::createFromFormat($dateFormat, date($dateFormat));

		// Цикличность смен - 16 вахт. На момент внедрения ближайшая точка начала цыкла была 7 марта...
		$delta = $wd->diff(DateTime::createFromFormat($dateFormat, '07.03.2016'))->days;

		// а теперь немного математики ;)
		$idx = ($delta % 8) * 2 + get_param($args, 'sinterval', 1);
		$args['sw'] = get_param($wlist, $idx - 1, 1); // 0-based

		echo json_encode($args);
	}
}
