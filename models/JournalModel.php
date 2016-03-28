<?php

class JournalModel extends Model {

	/**
	 *
	 * По переданному ID журнала, позвращает его наименование
	 *
	 * @param int $jid
	 * @return string
	 */
	public function getJournalFullName($jid) {

		$stmt = self::$db->prepare('SELECT description FROM journals WHERE id = :jid');
		$stmt->execute(array(
			'jid' => $jid,
		));

		$data = $stmt->fetch();

		return get_param($data, 'description', 'n/a');
	}

	/**
	 *
	 * По переданному ID журнала, получает ID открытой смены,
	 * если таковая имеется
	 *
	 * @param int $jid
	 * @return int | boolean
	 */
	public function getOpenShiftID($jid) {

		$stmt = self::$db->prepare('
            SELECT id FROM shifts
            WHERE date_close IS NULL
                    AND journal_id = :jid
                    AND deleted = 0');
		$stmt->execute(array(
			'jid' => $jid,
		));

		$data = $stmt->fetch();
		return get_param($data, 'id', -1);
	}

	/**
	 *
	 * По номеру смены возвращает сводку по ней
	 * Дату, Принял, Сдал, Интервал, Вахта
	 *
	 * @param int $sid
	 * @return mixed
	 */
	public function getShiftInformation($sid) {

		$stmt = self::$db->prepare("
            SELECT
                s.journal_id,
                date_format(s.date_open,'%Y-%m-%d') dopen,
                s.date_close dclose,
                get_user_short(s.o_user_id) ou, get_user_short(s.c_user_id) cu,
                s.o_user_id, s.c_user_id,
                i.period, v.abbr, s.agree,
                s.interval_id iid, s.watch_id wid, s.hash,
                addtime(s.date_open, i.minTime) sd,
                addtime(s.date_open, i.minTime) + INTERVAL 14 HOUR ed
            FROM shifts s
            LEFT JOIN intervals i ON s.interval_id = i.id
            LEFT JOIN watches v ON s.watch_id = v.id
            WHERE s.id = :shift_id
                AND s.deleted = 0
            ORDER BY s.date_open DESC");

		$stmt->execute(array(
			'shift_id' => $sid,
		));

		$data = $stmt->fetch();

		return $data;
	}

	/**
	 * Получение всех сообщений по смене, ID которой передан в параметре
	 *
	 *
	 * @param int $sid ID смены
	 */
	public function getShiftMessages($sid) {

		$sth = self::$db->prepare("
            SELECT
                m.id, date_format(m.date_msg, '%d.%m.%Y %H:%i') mdate, m.comment,
                date_format(m.date_msg,'%d.%m.%Y') md,
                date_format(m.date_msg,'%H:%i') mt,
                get_user_short(m.user_id) fio, m.special
            FROM messages m
            LEFT JOIN shifts s ON m.shift_id = s.id
            WHERE m.shift_id = :shift_id
                AND m.deleted = 0
            ORDER BY m.date_msg, m.realtime");

		$sth->execute(array(
			':shift_id' => $sid,
		));

		return $sth->fetchAll();
	}

	/**
	 * Помечает сообщение как удаленное (фактическое удаление не производим)
	 *
	 * @param int $mid ID сообщения
	 * @param int $sid ID открытой смены (только из открытой смены можем удалять сообщения)
	 *
	 * @return boolean      true если сообщение удалили, false - в противном случае
	 */
	public function setMessageDeleteMark($mid, $sid) {

		$sth = self::$db->prepare('
            UPDATE messages SET deleted = 1
            WHERE id = :mid
                AND shift_id = :sid');

		$result = $sth->execute(array(
			'mid' => $mid,
			'sid' => $sid,
		));

		$einfo = $sth->errorInfo();

		return array(
			'ok' => $result && $sth->rowCount() > 0,
			'error' => get_param($einfo, 2),
		);
	}


	public function setMessageText($mid, $sid, $mtext) {

		$sth = self::$db->prepare('
            UPDATE messages
                SET comment = :message,
                    special = :mark
            WHERE id = :mid
                AND shift_id = :sid');

		$mark = $mtext[0] === '*';

		$res = $sth->execute(array(
			'message' => trim($mtext),
			'mark' => intval($mark),
			'mid' => $mid,
			'sid' => $sid,
		));

		$einfo = $sth->errorInfo();

		return array(
			'ok' => $res, //&& $sth->rowCount(),
			'error' => get_param($einfo, 2),
			'mark' => $mark,
		);
	}

	public function setMessageTime($mid, $sid, $mdate) {

		$sth = self::$db->prepare('
            UPDATE messages
                SET date_msg = :newdate
            WHERE id = :mid
                AND shift_id = :sid');


		$res = $sth->execute(array(
			'newdate' => $mdate,
			'mid' => $mid,
			'sid' => $sid,
		));

		$einfo = $sth->errorInfo();

		return array(
			'ok' => $res, //&& $sth->rowCount(),
			'error' => get_param($einfo, 2),
		);
	}

	public function getMessageByID($mid) {

		$sth = self::$db->prepare("
            SELECT
                date_format(date_msg, '%Y-%m-%d %H:%i') mdate, 
                comment, 
                special 
            FROM messages
                WHERE id = :mid");

		$sth->execute(array(
			'mid' => $mid,
		));

		$data = $sth->fetchAll();
		return get_param($data, 0);
	}

	public function addNewMessage($sid, $mtext, $mdate, $userid) {

		if (empty($mtext)) {
			return array(
				'ok' => false,
				'error' => 'Текст сообщения не указан',
			);
		}

		$mark = $mtext[0] === '*';
		$sth = self::$db->prepare('
            INSERT INTO messages
                (shift_id, user_id, date_msg, comment, realtime, special)
                VALUES (:sid, :uid, :mdate, :mtext, now(), :mark)');

		$res = $sth->execute(array(
			'sid' => $sid,
			'uid' => $userid,
			'mdate' => $mdate,
			'mtext' => trim($mtext),
			'mark' => (int)$mark,
		));

		$einfo = $sth->errorInfo();
		return array(
			'ok' => $res,
			'error' => get_param($einfo, 2),
		);
	}

	public function getListPeriod() {

		$sth = self::$db->prepare('SELECT * FROM intervals');
		$sth->execute();

		return $sth->fetchAll();
	}

	public function getListWatches() {

		$sth = self::$db->prepare('SELECT * FROM watches ORDER BY abbr');
		$sth->execute();

		return $sth->fetchAll();
	}

	/**
	 * Возвращает ид интервала последней смены указанного журнала
	 * для организации чередования (ибо самим НСС это черезвычайно сложно сделать)
	 */
	public function getLastShiftInterval($jid) {

		$sth = self::$db->prepare('
            SELECT interval_id FROM shifts
            WHERE deleted = 0
                AND date_close IS NOT NULL
                AND journal_id = :jid
            ORDER BY date_close DESC
            LIMIT 1');
		$sth->execute(array(
			'jid' => $jid,
		));
		$data = $sth->fetch();

		return get_param($data, 'interval_id', 2);
	}


	/**
	 * Добавляет в базу данных запись по новой смене
	 *
	 * @param int $jid
	 * @param int $sdate
	 * @param int $sinterval
	 * @param int $swatch
	 * @param int $userid
	 * @return boolean
	 */
	public function createNewShift($jid, $sdate, $sinterval, $swatch, $userid) {

		$sth = self::$db->prepare('
          INSERT INTO shifts
            (journal_id, date_open, c_user_id, interval_id, watch_id, hash)
            VALUES (:jid, :sdate, :uid, :intid, :watchid, sha1(:key))');

		$result = $sth->execute(array(
			'jid' => $jid,
			'sdate' => $sdate,
			'uid' => $userid,
			'intid' => $sinterval,
			'watchid' => $swatch,
			'key' => $sdate . $sinterval . $swatch,
		));

		$einfo = $sth->errorInfo();

		$newid = self::$db->lastInsertId();
		$this->setPreviousShiftEquipment($newid, $jid, $userid);

		return array(
			'ok' => $result,
			'error' => get_param($einfo, 2),
		);
	}


	/**
	 * Заполняет состояние оборудование у смены $sid
	 * по данным последней закрытой смены журнала $jid
	 *
	 * @param int $sid ID-смены
	 * @param int $jid ID-журнала
	 */
	public function setPreviousShiftEquipment($sid, $jid, $uid) {

		// получим ИД последней закрытой смены текущего журнала
		//
		// если смены вводить не последовательно, то получим фигню, но..
		// этож оперативный журнал, значит все должно быть последовательно
		$sth = self::$db->prepare('
            SELECT id, o_user_id uid FROM shifts
            WHERE journal_id = :jid
                AND date_close IS NOT NULL
                AND deleted = 0
            ORDER BY date_close DESC
            LIMIT 1');

		$sth->execute(array(
			':jid' => $jid,
		));

		$data = $sth->fetch();

		$last_id = get_param($data, 'id');
		if ($last_id) {

			// и добавим в таблицу оборудования, данные, взятые из последней смены
			$equip = self::$db->prepare('
                REPLACE INTO shift_equipments
                SELECT :newId, equipment_id, message, deleted
                FROM shift_equipments WHERE shift_id = :lastId');
			$equip->execute(array(
				':newId' => $sid,
				':lastId' => $last_id,
			));

			// и тут же для предыдущей смены пропишем того, кто смену принял
			$closer = self::$db->prepare('
                UPDATE shifts
                    SET o_user_id = :uid
                WHERE id = :newid');
			$closer->execute(array(
				'uid' => $uid,
				'newid' => $last_id,
			));
		}
	}

	/**
	 * Получение полного списка пользователей по указанным должностям.
	 * На выходе будет сгруппированый массив
	 *
	 * @param array $ids Массив идентификаторов должностей, если путо - то все
	 * @return array
	 */
	public function getAllUsersByPositions($ids = array()) {

		$clause = 'where up.deleted = 0 ';
		if (count($ids)) {
			$mark = implode(',', array_fill(0, count($ids), '?'));
			$clause .= " and up.position_id in ($mark) ";
		}

		$sth = self::$db->prepare("
        select 
            p.id pid,
            get_user_short(us.id) fio, 
            us.id
        from positions p
         join user_positions up on up.position_id = p.id
         join users us on up.user_id = us.id
        $clause
        order by p.id, us.lname");

		$sth->execute($ids);

		$data = $sth->fetchAll(PDO::FETCH_GROUP);

		// добавим в результат пустые массивы, для должностей,
		// сотрудники по которым не найдены
		foreach ($ids as $pid) {
			if (!isset($data[$pid])) {
				$data[$pid] = array();
			}
		}

		//foreach ($data as $pid => $users) {
		//    $data[ $pid ] = array_column($users, 'fio', 'id');
		//}

		return $data;
	}

	/**
	 * Сдача смены. Установка времени закрытия и согласования
	 *
	 * @param int $sid Идентификатор смены
	 * @param string $time Время согласвания
	 * @return array
	 */
	public function closeCurrentShift($sid, $time) {

		$sth = self::$db->prepare('UPDATE shifts SET date_close = now(), agree = :atime WHERE id = :shift_id');
		$result = $sth->execute(array(
			'shift_id' => $sid,
			'atime' => $time,
		));

		$einfo = $sth->errorInfo();

		return array(
			'ok' => $result,
			'error' => get_param($einfo, 2),
		);
	}

	/**
	 * Получение списка и состояния оборудования по указанной смене
	 *
	 * @param int $sid Идентификатор смены
	 * @param int $jid Идентификатор журнала
	 * @return array
	 */
	public function getShiftEquipment($sid, $jid) {

		$sth = self::$db->prepare('
            SELECT
                    e.id, e.name, se.message
            FROM journal_equipments je
            LEFT JOIN equipments e ON je.equipment_id = e.id
            LEFT JOIN shift_equipments se
                    ON se.equipment_id = e.id AND se.shift_id = :sid AND se.deleted = 0
            WHERE je.journal_id = :jid AND je.deleted = 0
            ORDER BY e.id');

		$sth->execute(array(
			'sid' => $sid,
			'jid' => $jid,
		));

		return $sth->fetchAll();
	}

	/**
	 * Сохранение параметров по оборудованию
	 *
	 * @param array $data Массив данных с иденифитакорами оборудования и их состоянием
	 * @param int $sid Идентификатор смены
	 * @return boolean
	 */
	public function saveEquipment($data, $sid) {

		$res = 0;
		if (is_array($data)) {
			$sth = self::$db->prepare('replace shift_equipments values(:sid, :eid, :message, 0)');
			$res = 1;
			foreach ($data as $key => $value) {
				if ($res === 0) break;
				$res *= $sth->execute(array(
					'sid' => $sid,
					'eid' => $key,
					'message' => htmlspecialchars($value),
				));
			}
		}
		return $res;
	}

	/**
	 * Получение списка архивных смен (завершенных)
	 *
	 * @param int $jid Идентификатор журнала
	 * @param int $uid Идентификатор пользователя (для признака подписи)
	 * @return array
	 */
	public function getArchiveShifts($jid, $uid, $cpage = 0, $pagelimit = 20) {

		$sth = self::$db->prepare("
            SELECT
                s.id, date_format(s.date_open,'%d.%m.%Y') dopen,
                get_user_short(s.c_user_id) ou,
                i.period, v.abbr,
                (ifnull(c.shift_id,0) > 0) sign
            FROM shifts s
            LEFT JOIN intervals i ON s.interval_id = i.id
            LEFT JOIN watches v ON s.watch_id = v.id
            LEFT JOIN signatures c ON s.id = c.shift_id AND c.user_id = :user_id AND c.deleted = 0
            WHERE s.journal_id = :journal_id
                    AND s.date_close IS NOT NULL
                    AND s.deleted = 0
            ORDER BY s.date_open DESC, i.period DESC, s.id DESC
            LIMIT :rstart, :pagelimit");

		$sth->bindValue('rstart', $cpage * $pagelimit, PDO::PARAM_INT);
		$sth->bindValue('pagelimit', $pagelimit, PDO::PARAM_INT);
		$sth->bindValue('journal_id', $jid, PDO::PARAM_INT);
		$sth->bindValue('user_id', $uid, PDO::PARAM_INT);

		$sth->execute();

		$data = $sth->fetchAll();

		return $data;
	}

	public function getCountArchiveShifts($jid) {

		$sth = self::$db->prepare('
            SELECT count(*) cnt
            FROM shifts
            WHERE journal_id = :jid
                AND deleted = 0
                AND date_close IS NOT NULL');

		$sth->execute(array(
			'jid' => $jid,
		));

		$data = $sth->fetch();
		return (int)get_param($data, 'cnt', 0);
	}

	/**
	 * Подпись юзером смены с указаным идентификатором
	 *
	 * @param int $sid Идентификатор смены
	 * @param int $uid Идентификатор пользователя
	 */
	public function setShiftSignature($sid, $uid, $stext = '', $stime = '') {

		$sth = self::$db->prepare('
        REPLACE INTO signatures
            (shift_id, user_id, date, comment) 
        VALUES (:shift_id, :user_id, :stime, :comment)'); // now()

		if (empty($stime))
			$stime = date('Y-m-d H:i:s');

		$res = $sth->execute(array(
			'shift_id' => $sid,
			'user_id' => $uid,
			'comment' => $stext,
			'stime' => $stime,
		));

		$einfo = $sth->errorInfo();
		return array(
			'ok' => $res && ($sth->rowCount() > 0),
			'error' => get_param($einfo, 2),
		);
	}

	/**
	 * Полуение списка пользователей, подписавших указанную смену
	 *
	 * @param int $sid Идентификатор смены
	 */
	public function getShiftSignatuses($sid) {

		$sth = self::$db->prepare("
        SELECT
            user_id,
            get_user_short(user_id) uname,
            date_format(date, '%d.%m.%Y %H:%i') stamp
        FROM signatures
        WHERE deleted = 0
            AND shift_id = :shift_id
        ORDER BY stamp");

		$sth->execute(array(
			'shift_id' => $sid,
		));

		return $sth->fetchAll();
	}

	public function getShiftCompositionsNEW($jid, $sid) {

		$sth = self::$db->prepare('
            SELECT
                ifnull(p.description, p.name) posname,
                cr.position_id, cr.id ruleid, p.name, cr.porder,
                cv.user_id, cv.trainee_id, cv.standin_id			
            FROM composition_rules cr
            LEFT JOIN positions p ON cr.position_id = p.id
            LEFT JOIN compositions cv ON cr.id = cv.rule_id AND cv.shift_id = :shift_id AND cv.deleted = 0
            WHERE cr.journal_id = :journal_id
                AND cr.deleted = 0
            ORDER BY cr.position_id, cr.porder');

		$sth->execute(array(
			'shift_id' => $sid,
			'journal_id' => $jid,
		));


		//$data = $sth->fetchAll(PDO::FETCH_GROUP);
		$rules = array();
		$users = array();


		$data = $sth->fetchAll();
		foreach ($data as $item) {
			$rules[$item['position_id']][] = $item;

			if (!empty(get_param($item, 'user_id')))
				$users[get_param($item, 'user_id')] = 1;
			if (!empty(get_param($item, 'trainee_id')))
				$users[get_param($item, 'trainee_id')] = 1;
			if (!empty(get_param($item, 'standin_id')))
				$users[get_param($item, 'standin_id')] = 1;
		}


		$result['rules'] = $rules;
		$result['users'] = array_column($this->getUserList(array_keys($users)), 'id', 'fio');
		$result['positions'] = array_column($data, 'posname', 'position_id');

		return $result;
	}

	public function getUserList($ids = array()) {

		$where = '';

		if (count($ids)) {
			$marks = implode(',', array_fill(0, count($ids), '?'));
			$where = ' where id in ($marks)';
		}

		$sth = self::$db->prepare("select id, get_user_short(id) fio from users $where");
		$sth->execute($ids);
		$data = $sth->fetchAll();


		return $data;
	}

	public function getUserByName($param = array()) {

		$sth = self::$db->prepare("
        SELECT id, get_user_short(id) fio
        FROM users
        WHERE   lname LIKE :P1
            AND fname LIKE :P2
            AND pname LIKE :P3
        ");

		$sth->execute(array(
			'P1' => trim(get_param($param, 0)) . '%',
			'P2' => trim(get_param($param, 1)) . '%',
			'P3' => trim(get_param($param, 2)) . '%',
		));

		$einfo = $sth->errorInfo();
		Controller::appendDebug(get_param($einfo, 2));

		$data = $sth->fetchAll();
		if (count($data) > 1) {
			Controller::appendDebug('Более чем один пользователь подошел по запрашиваемым параметрам: ' . get_param($param, 0), 0, 1);
		}

		return get_param($data, 0);
	}

	public function deleteShiftMessages($sid) {

		$sth = self::$db->prepare('UPDATE messages SET deleted = 1 WHERE shift_id = :sid');
		$sth->execute(array(
			'sid' => $sid,
		));

		return $sth->rowCount();
	}

	public function getCommonComposition($sinfo) {

		$sth = self::$db->prepare("
        SELECT
            p.id pid, ifnull(p.name, 'no name') pname,
            cr.id ruleid, cr.journal_id jid, p.porder, cr.required,
            c.id, c.user_id, get_user_short(c.user_id) uname,
            (cr.journal_id = :jid) jown
        FROM composition_rules cr
        LEFT JOIN positions p ON cr.position_id = p.id
        LEFT JOIN compositions c ON
            c.rule_id = cr.id 
            AND c.sdate = :sdate
            AND c.sinterval = :sint
            AND c.swatch = :swatch
            AND c.deleted = 0
        WHERE cr.deleted = 0
            AND cr.journal_id > 0
        ORDER BY jown DESC, p.porder, p.id");

		$sth->execute(array(
			'sdate' => get_param($sinfo, 'dopen'),
			'sint' => get_param($sinfo, 'iid'),
			'swatch' => get_param($sinfo, 'wid'),
			'jid' => get_param($sinfo, 'journal_id'),
		));

		$data = $sth->fetchAll(PDO::FETCH_GROUP);

		return $data;
	}

	public function getCompositionRules($sinfo) {

		$sth = self::$db->prepare("
        SELECT
            p.id pid, ifnull(p.name, 'no name') pname,
            cr.id ruleid, cr.journal_id jid, p.porder, cr.required,
            (cr.journal_id = :jid) jown
        FROM composition_rules cr
        LEFT JOIN positions p ON cr.position_id = p.id
        WHERE cr.deleted = 0
            AND cr.journal_id > 0
        ORDER BY jown * cr.porder DESC, cr.porder DESC, p.porder, p.id");

		$sth->execute(array(
			'jid' => get_param($sinfo, 'journal_id'),
		));

		$data = $sth->fetchAll(PDO::FETCH_GROUP);

		return $data;
	}

	public function getCompositionValues($sinfo, $returnnames = false) {

		$sth = self::$db->prepare('
        SELECT
            cv.rule_id, cv.user_id, 
            get_user_short(cv.user_id) fio, 
            (cr.journal_id = :jid) em
        FROM compositions cv
        LEFT JOIN composition_rules cr ON cv.rule_id = cr.id
        WHERE cv.deleted = 0
            AND cv.shift_hash = :shash');
		$sth->execute(array(
			'shash' => get_param($sinfo, 'hash'),
			'jid' => get_param($sinfo, 'journal_id'),
		));

		$data = array();

		$users = $sth->fetchAll();
		foreach ($users as $value) {
			//$data[ $value['rule_id'] ]['user'] = $value['em'] === '1' ? $value['user_id'] : $value['fio'];
			$data[$value['rule_id']]['user'] = $returnnames ? $value['fio'] : $value['user_id'];
		}

		$esth = self::$db->prepare("
            SELECT
                ce.*, get_user_short(ce.user_id) fio 
            FROM compositions_ext ce
            WHERE ce.shift_hash = :shash");

		$esth->execute(array(
			'shash' => get_param($sinfo, 'hash'),
		));
		$ext = $esth->fetchAll();

		foreach ($ext as $value) {
			// определяем тип (стажер или дублер)
			$key = $value['u_type'] === '0' ? 'standin' : 'trainee';
			$data[$value['rule_id']][$key][] = $returnnames ? $value['fio'] : $value['user_id'];
		}

		return $data;
	}

	public function getAllowRulesForReport($sinfo) {

		$sth = self::$db->prepare('SELECT rule_id FROM report_rules WHERE journal_id = :jid ORDER BY porder DESC');
		$sth->execute(array(
			'jid' => get_param($sinfo, 'journal_id'),
		));

		return $sth->fetchAll(PDO::FETCH_COLUMN);
	}

	public function saveCompositions($sinfo, $pusers, $ptrainee, $pstandin) {

		$hash = get_param($sinfo, 'hash');

		// сохраняем основных сотрудников
		$usth = self::$db->prepare('REPLACE INTO compositions VALUES (:shash,:rid,:uid,:mark)');
		$status = 1;
		foreach ($pusers as $rid => $uid) {
			$mark = (int)empty($uid);
			$status *= $usth->execute(array(
				'shash' => $hash,
				'rid' => $rid,
				'uid' => empty($uid) ? null : $uid,
				'mark' => $mark,
			));
			$einfo = $usth->errorInfo();
			Controller::appendDebug(get_param($einfo, 2));
			if (!$status) break;
		}

		// очищаем информацию по стажерам/дублерам (только по текущму журналу)
		$dsth = self::$db->prepare('
            DELETE FROM compositions_ext
            WHERE shift_hash = :shash
                AND rule_id IN (SELECT id FROM composition_rules WHERE journal_id = :jid)');
		$status *= $dsth->execute(array(
			'shash' => $hash,
			'jid' => get_param($sinfo, 'journal_id'),
		));
		$einfo = $dsth->errorInfo();
		Controller::appendDebug(get_param($einfo, 2));


		$csth = self::$db->prepare('
        INSERT INTO compositions_ext (shift_hash, rule_id, user_id, u_type)
        VALUES (:shash, :rid, :uid, :utype)');

		// сохраняем дублеров
		foreach ($ptrainee as $rid => $users) {
			foreach ($users as $cuser) {
				if (empty($cuser) || !$status) continue;
				$status *= $csth->execute(array(
					'shash' => $hash,
					'rid' => $rid,
					'uid' => $cuser,
					'utype' => 1, // 1 - дублер
				));
				$einfo = $usth->errorInfo();
				Controller::appendDebug(get_param($einfo, 2));
			}
		}

		// сохраняем стажеров
		foreach ($pstandin as $rid => $users) {
			foreach ($users as $cuser) {
				if (empty($cuser) || !$status) continue;
				$status *= $csth->execute(array(
					'shash' => $hash,
					'rid' => $rid,
					'uid' => $cuser,
					'utype' => 0, // 0 - стажер
				));
				$einfo = $usth->errorInfo();
				Controller::appendDebug(get_param($einfo, 2));
			}
		}

		return $status === 1;
	}

	/**
	 * Выбираем последние $cnt смен, из журнала $jid
	 * начиная со смены с ид $sid
	 *
	 * @param int $jid
	 * @param int $sid
	 * @param int $cnt
	 * @return array
	 */
	public function getShiftsForReport($jid, $sid, $cnt) {

		$sth = self::$db->prepare("
            SELECT id, ifnull(date_close,now()) dc FROM shifts
            WHERE journal_id = :jid
                AND id <= :sid
                AND deleted = 0
            ORDER BY dc DESC
            LIMIT :limit");

		$sth->bindValue('jid', $jid, PDO::PARAM_INT);
		$sth->bindValue('sid', $sid, PDO::PARAM_INT);
		$sth->bindValue('limit', $cnt, PDO::PARAM_INT);

		$sth->execute();

		$data = $sth->fetchAll();
		return array_column($data, 'id');
	}

	public function getEarthList($journal_id) {

		$res = $this->select('
			SELECT
				c.id, c.equipment, t.shortname, c.num,
				get_user_short(c.user_on) ou,
				date_format(c.date_on, "%d.%m.%Y") odate
			FROM earth_control c
			LEFT JOIN earth_type t ON c.etype_id = t.id
			WHERE c.deleted = 0
				AND c.journal_id = :jid
				AND c.date_off IS NULL
			ORDER BY c.date_on DESC ', ['jid' => $journal_id]);

		return $res;
	}

	public function getEarthTypes() {

		$res = $this->select('SELECT id, shortname title FROM earth_type');
		//return array_column($res, 'title', 'id');
		return $res;
	}

	public function setupEarth($params) {

		$res = $this->select('
			REPLACE INTO earth_control
			(id, equipment, etype_id, num, date_on, user_on, journal_id)
			VALUES (NULL, :e_equip, :e_type, :e_num, :e_date, :e_dem, :jid)
			', $params);

		return $res;
	}

	public function getEarthInfo($eid, $jid) {

		$result = $this->select('SELECT * FROM earth_control
				WHERE id = :eid
					AND journal_id = :jid
					AND  deleted = 0', [
			'eid' => $eid,
			'jid' => $jid,
		]);

		return get_param($result, 0);
	}

	public function takeoffEarth($param) {

		$res = 0;
		$this->select('
			UPDATE earth_control
			SET date_off = :e_date, user_off = :e_dem
			WHERE journal_id = :jid
				AND id = :eid', $param, $res);

		return $res > 0;
	}

	public function getEarthTotal($jid) {

		return $this->select('
			SELECT et.description title, ifnull(o.cnt, 0) res
			FROM earth_type et
			LEFT JOIN (SELECT etype_id, count(*) cnt
			FROM earth_control ec
			WHERE ec.journal_id = :journal
				AND ec.date_off IS NULL
				AND ec.deleted = 0
			GROUP BY 1) o ON et.id = o.etype_id', ['journal' => $jid]);
	}
}