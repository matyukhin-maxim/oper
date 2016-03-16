<?php
/**
 * Created by PhpStorm.
 * User: fellix
 * Date: 09.05.14
 * Time: 16:51
 */

class AccessModel {

    /** @var PDO $db link to database*/
    public $db;

    function __construct($basename) {

        //$basename = 'd:\oj_be.mdb';

        $dns = sprintf('odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq=%s;Uid=Admin',$basename);
        
        try {
            $this->db = new PDO($dns,'','');
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
            //$this->db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->db = null;
            var_dump($dns);
            die($e->getMessage());
        }
    }
       
}
