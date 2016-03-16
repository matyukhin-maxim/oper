<?php

require_once 'models/JournalModel.php';

// тестовый контроллер для переноса данных из базы старого журнала

/** @property JournalModel $model  */
class Syncronization extends Controller {

    private $mdb;
    private $cache;
            
    function __construct() {
        
        parent::__construct();
        
        $this->cache = array();
    }
    
    function loadModel() {
        // переопределили
        $this->model = new JournalModel();
        return false;
    }

    public function actionIndex() {
        
        
        
    }
    
    public function actionTAI() {
        
        $dns = sprintf('%s:host=%s;dbname=%s;charset=cp1251','mysql','172.28.120.2','operjournals');
        $this->mdb = new PDO($dns,'kristya','31281080',array());
        
        $this->mdb->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->mdb->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
                
        $sth = $this->mdb->prepare('select * from users where ceh_id = :cid');
        $sth->execute(array(
            'cid' => 8,
        ));
        $data = $sth->fetchAll();
        
        array_walk_recursive($data, 'charsetChange');
        
        foreach ($data as $user) {
            $who = $this->findUserByName(get_param($user, 'username'));
            if ($who)
                var_dump($who);
        }
        
        
        var_dump($this->cache);
    }
    
    public function actionNSE() {
        
        $this->mdb = new AccessModel('d:\ojnsec_be.mdb');
        
        $this->render('index', false);
        
        $sthd = $this->mdb->db->prepare("delete from gurnal where data >= :ff");
        $sthd->execute(array(
            'ff' => '2015-06-16'
        ));
        
        $einfo = $sthd->errorInfo();
        array_walk_recursive($einfo, 'charsetChange');
        var_dump($einfo);
        
        $sth = $this->mdb->db->prepare(""
                . " select * from gurnal"
                . " where data >= :startdate"
                . " order by data desc");
        
        $sth->execute(array(
            'startdate' => '2015-04-01'
        ));
        
        $einfo = $sth->errorInfo();
        if ((int)get_param($einfo, 0) > 0) {
            array_walk_recursive($einfo, 'charsetChange');
            var_dump($einfo);
        }
        $data = $sth->fetchAll();
        
        
        array_walk_recursive($data, 'charsetChange');
        foreach ($data as $item) {
            var_dump($item);
        }
        
        $this->render('');
    }

    public function actionNSS() {
        
        $this->mdb = new AccessModel('d:\oj_be.mdb');
        
        $this->render('index', false);
        
        $sth = $this->mdb->db->prepare(""
                . " select * from gurnal"
                . " where data >= :startdate"
                . " order by data");
        
        $sth->execute(array(
            'startdate' => '2015-05-17'
        ));
        
        $einfo = $sth->errorInfo();
        if ((int)get_param($einfo, 0) > 0) {
            array_walk_recursive($einfo, 'charsetChange');
            var_dump($einfo);
        }
        $data = $sth->fetchAll();
        
        
        array_walk_recursive($data, 'charsetChange');
        
        $periods = array_column($this->model->getListPeriod(),'id', 'period');
        
        $watches = array_column($this->model->getListWatches(), 'id', 'abbr');
        $watches['B'] = '3';
        $watches['A'] = '1';
        
        //$people = array_column($this->model->getUserList(), 'id', 'fio');
        
        //var_dump($periods);
        //var_dump($watches);
        //var_dump($people);
        
        $newshift = Model::$db->prepare('
            replace into shifts values (
                :sid, :jid, :dopen,
                adddate(:dclose, interval 12 hour), :ouser, :cuser,
                :intid, :wid, :atime, sha1(:hash), 0)');
        
        //var_dump($newshift->errorInfo());

        $status = 1;
        $tt = 2;
        foreach ($data as $item) {
            
            $shift_id = get_param($item, 'COD');
            
            if (--$tt >= 0) var_dump($item);
            
            $sm = str_replace('8:', '08:', get_param($item,'Smena'));
            $vs = get_param($item, 'Vremia_sdanshi','');
            
            // если кто сдал смену не указано, то и даты закрытия не будет - смена открыта
            $dclose = get_param($item, 'Sm_Sdal') ? get_param($item, 'Data') : null;
            $key  = trim(substr(get_param($item, 'Data'),0,10));
            $key .= get_param($periods, $sm, '1');
            $key .= get_param($watches, get_param($item,'Vahta'), '1');
            
            // копируем первые 5 символов интервала смены, чтобы вычислить потом дату закрытия
            $topen = mb_substr($sm, 0, 5);
            if ($dclose) {
                $dclose = explode(' ', $dclose)[0] . ' ' . $topen;
                //var_dump($dclose);
            }
            
            $opt = array(
                'sid' => $shift_id,
                'jid' => 1,
                'dopen' => get_param($item, 'Data'),
                'dclose' => $dclose,
                'ouser' => $this->findUserByName(get_param($item, 'Sm_Prinal')),
                'cuser' => $this->findUserByName(get_param($item, 'Sm_Sdal')),
                'intid' => get_param($periods, $sm, 0),
                'wid' => get_param($watches, get_param($item,'Vahta'), 0),
                'atime' => substr($vs, -8, 5), // '1899-12-30 08:00:00' => '08:00'
                'hash' => $key,
            );
            
            
            // заполняем и сохраняем состояние оборудования
            $equip = array(
                1 => trimHereDoc(get_param($item, 'V_RABOTE')),
                2 => trimHereDoc(get_param($item, 'V_REZERVE')),
                3 => trimHereDoc(get_param($item, 'V_REMONTE')),
                4 => trimHereDoc(get_param($item, 'ORU')),
                5 => trimHereDoc(get_param($item, 'EU')),
                6 => trimHereDoc(get_param($item, 'T_SET')),
                7 => trimHereDoc(get_param($item, 'UROVNI')),
                8 => trimHereDoc(get_param($item, 'STHuTETS')),
                9 => trimHereDoc(get_param($item, 'VGK')),
                11 => trimHereDoc(get_param($item, 'Zametsania')),
            );
            
            // перезаписываем данные по смене
            $status *= $newshift->execute($opt);
            $einfo = $newshift->errorInfo();
            $this->appendDebug(get_param($einfo, 2));
            
            $compos = array(
                145 => $this->findUserByName(get_param($item,'StDDS')),
                3   => $this->findUserByName(get_param($item,'DDS')),
                146 => $this->findUserByName(get_param($item, 'DODS')),
                1   => $this->findUserByName(get_param($item, 'NSS')),
                115 => $this->findUserByName(get_param($item, 'NSEZ')),
                6   => $this->findUserByName(get_param($item, 'NSKTZ')),
                7   => $this->findUserByName(get_param($item, 'NSHZ')),
                8   => $this->findUserByName(get_param($item, 'NSTTZ')),
                9   => $this->findUserByName(get_param($item, 'DTSTAI')),
                10  => $this->findUserByName(get_param($item, 'DTS')),
                147 => $this->findUserByName(get_param($item, 'voditel')),
                11  => $this->findUserByName(get_param($item, 'NS_TSUTETS')),
                12  => $this->findUserByName(get_param($item, 'NSVGK')),
            );
            
            // если смена сохранилась без ошибок, 
            if ($status) {
                // то запишем информацию об оборудовании
                $this->model->saveEquipment($equip, $shift_id);

                // перенесем сообщения
                $this->fillMessages($shift_id);

                // и подписи
                $this->fillSignatures($shift_id);
                
                $sinfo = array(
                    'hash' => sha1($key),
                );
                $this->model->saveCompositions($sinfo, $compos, array(), array());
            }
        }
        
        $this->drawError();
        
        var_dump($status);
        var_dump($this->cache);
        $this->render('');
    }
    
    function actionKTC() {
        
        $conf = array(
            'host' => '172.28.120.2',
            'user' => 'kristya',
            'pass' => '31281080',
            'base' => 'oj_ktc',
        );
        
        $host = get_param($conf, 'host');
        $user = get_param($conf, 'user');
        $pass = get_param($conf, 'pass');
        $base = get_param($conf, 'base');

        $dsn = sprintf('mysql:host=%s;dbname=%s', $host, $base);
        
        $this->render('', false);
        //echo $dsn;

        try {

            $this->mdb = new PDO($dsn, $user, $pass, array(
                PDO::ATTR_TIMEOUT => 2,
                //PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',
            ));
            
            $this->mdb->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->mdb->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);

        } catch (Exception $exc) {
            $status = $exc->getMessage();
            charsetChange($status);
            die($status);
        }
        
        $tname = 'mnuPK';
        
        $sth = $this->mdb->prepare("select * from $tname");
        $sth->execute();

        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        
        array_walk_recursive($data, 'charsetChange');
        //var_dump($data);
        
        $this->cache = array();
        
        foreach ($data as $key => $value) {
            $prt = array();
            $uname = trim(get_param($value, $tname));
            if (empty($uname))                continue;
            $id = $this->findUserByName($uname  , $prt);
            
            if (empty($id)) {
                //var_dump($prt);
                $nu = Model::$db->prepare('insert into users (lname, fname, pname) values (?, ?, ?)');
                $nu->execute($prt);
                
                $err = $nu->errorInfo();
                $this->appendDebug(get_param($err, 2));
                $this->drawError();
                
                $id = $this->findUserByName($uname  , $prt);
                
                echo "Создан новый пользователь '" . join(' ', $prt) . "' </br>\n";
            }
        }
        
        $pid = 32;
        
        foreach ($this->cache as $key => $value) {
            $up = Model::$db->prepare('select count(*) cnt from user_positions where user_id = ? and position_id = ?');
            $up->execute(array($value, $pid));
            $exist = (int)$up->fetchColumn();
            
            if (!$exist) {
                $nl = Model::$db->prepare('insert into user_positions (user_id, position_id) values (?, ?)');
                $nl->execute(array($value, $pid));
                
                $err = $nl->errorInfo();
                $this->appendDebug(get_param($err, 2));
                $this->drawError();
                
                echo "Пользователь $key привязан к должности $pid </br>\n";
            }
        }

        
        var_dump($this->cache);
        $this->render('');
    }

    public function findUserByName($uname, &$out = array()) {
        
        if (empty($uname)) return null;
        
        $res = get_param($this->cache, $uname);
        if ($res)
            return $res;
        
        $work = trim(preg_replace('/[\.\s]+/', ' ', $uname));
        $parts = explode(' ', $work);
        
        $data = $this->model->getUserByName($parts);
        $this->drawError();
        
        $uid = get_param($data, 'id', null);
        $this->cache[$uname] = $uid;
        
        $out = $parts;
        return $uid;
    }
    
    public function fillMessages($shiftID) {
        
        $this->model->deleteShiftMessages($shiftID);
        
        $sth = $this->mdb->db->prepare('select * from soderganie where cod = :sid');
        $sth->execute(array(
            'sid' => $shiftID,
        ));
        
        $messages = $sth->fetchAll();
        array_walk_recursive($messages, 'charsetChange');
        
        foreach ($messages as $msg) {
            
            $mtext = trim(get_param($msg, 'Soderganie'));
            
            // 'Vremia' => string '1899-12-30 22:35:00' (length=19)
            // 'Data' => string '2015-03-01 00:00:00' (length=19)
            // ППЦ
            
            $od = explode(' ',get_param($msg, 'Data'));
            $ot = explode(' ',get_param($msg, 'Vremia'));
            
            $mdate = get_param($od, 0) . ' ' . get_param($ot, 1);
            
            $this->model->addNewMessage($shiftID, $mtext, $mdate, get_param($this->authdata, 'id'));
        }
        
    }
    
    public function fillSignatures($shiftID) {
        
        $cls = Model::$db->prepare('delete from signatures where shift_id = :sid');
        $cls->execute(array(
            'sid' => $shiftID,
        ));
        
        $sth = $this->mdb->db->prepare('select * from Rucovoditeli where COD = :sid');
        $sth->execute(array(
            'sid' => $shiftID,
        ));
        
        $data = $sth->fetchAll();
        array_walk_recursive($data, 'charsetChange');
        
        foreach ($data as $sign) {
            //var_dump($sign);
            
            $uid = $this->findUserByName(get_param($sign, 'Podpis'));
            if ($uid !== -1) {
                $this->model->setShiftSignature($shiftID, $uid, get_param($sign, 'Ukaz'), get_param($sign, 'Vremia'));
            }
        }
        
    }

}

