<?php
	//shell_exec('strings unknown');exit;
	$code = trim(file_get_contents('rom.txt'));
	$code = explode(' ',$code);
	$code = array_reverse($code);
	$code = implode(' ',$code);
	echo $code;exit;
