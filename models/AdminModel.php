<?php

class AdminModel extends Model {
 
    public function getAll($ptext, $ppage, $plimit) {
        
        $wlike = "";
        if (!empty($ptext)) {
            //$wlike = "and get_user_full(u.id) like '%$ptext%'";
            $wlike = "and u.lname like '%$ptext%'";
        }
        
        //return array('data' => "
        
        $sql = "
        select 
            u.lname, u.fname, u.pname, 
            group_concat(p.name separator '\n') pos
        from users u
        left join user_positions up on u.id = up.user_id
        left join positions p on up.position_id = p.id and up.deleted = 0
        where u.deleted = 0
            $wlike
        group by 1,2,3
        order by 1";
        
        if (!empty($plimit)) {
            $sql .= " limit $plimit";
        }
        
        return $this->select($sql);
        
    }
    
}