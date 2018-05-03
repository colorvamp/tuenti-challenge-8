#!/usr/bin/php
<?php
	$i = file('php://stdin');
	//$out = fopen('php://stderr', 'w');
	$num = trim(array_shift($i));

	$case = 0;
	while (($data = trim(array_shift($i))) && ++$case) {
		$data = explode(' ',$data);
		$holes = ($data[0] - 1) * ($data[1] - 1);

		echo 'Case #'.$case.': '.$holes.PHP_EOL;
	}

