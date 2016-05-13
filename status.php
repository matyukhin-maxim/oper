<?php
    echo $_SERVER['COMPUTERNAME'] . "<br/>" . PHP_EOL;
    
	//print_r($_SERVER);
    //phpinfo();
	
	foreach ($_SERVER as $key => $val) {
		echo  "$key  =>  $val\n<br/>";
	}
?>