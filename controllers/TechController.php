<?php

header('Content-Type: text/html; charset=Windows-1251');
error_reporting(0);
set_time_limit(0);

include_once 'core/Routine.php';

/**
 * Description of TechController
 *
 * @author Матюхин_МП
 * 
 * @property pdo $rdb tech-db connection
 */
class Tech {

    public function actionIndex() {

        $dsn = sprintf('mysql:host=%s;dbname=%s', 'tech-db', 'oper');
        $cnt = 5;
        $this->rdb = null;

        while ($cnt-- && !$this->rdb) {
            try {
                $this->rdb = new PDO($dsn, 'max', 'maxmax0', array(
                    PDO::ATTR_TIMEOUT => 5,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',
                ));
            } catch (Exception $e) {
                $msg = $e->getMessage();
                echo "try #$cnt... $msg\n";
            }
        }

        if ($this->rdb) {

            $sth = $this->rdb->prepare('insert into test values (null, :guid)');
            $sth->execute(array(
                'guid' => uniqid(),
            ));

            $einfo = $sth->errorInfo();
            
            if ((int) get_param($einfo, 0) > 0)
                echo "ERROR: " . get_param($einfo, 2) . PHP_EOL;
            else
                echo "OK!\n";
        } else {
            echo "No connection...\n";
        }
    }

}

$tech = new Tech();
$tech->actionIndex();
