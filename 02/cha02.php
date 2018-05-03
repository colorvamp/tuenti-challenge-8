#!/usr/bin/php
<?php
	$i = file('php://stdin');
	//$out = fopen('php://stderr', 'w');
	$num = trim(array_shift($i));

	$case = 0;
	while (($data = trim(array_shift($i))) && ++$case) {
		$len = strlen($data);
		$range = range(0,$len - 1);
		foreach ($range as &$num) {
			$num = base_convert($num,10,$len + 1);
		}
		unset($num);

		$min = implode('',$range);
		rsort($range);
		$max = implode('',$range);
		$tmp = $min[0];
		$min[0] = $min[1];
		$min[1] = $tmp;


		$max1 = str_baseconvert($max,$len,10);
		$min1 = str_baseconvert($min,$len,10);

		echo 'Case #'.$case.': '.bcsub($max1,$min1).PHP_EOL;
	}

	function str_baseconvert($str, $frombase=10, $tobase=36) { 
		$str = trim($str); 
		if (intval($frombase) != 10) { 
			$len = strlen($str); 
			$q = 0; 
			for ($i=0; $i<$len; $i++) { 
				$r = base_convert($str[$i], $frombase, 10); 
				$q = bcadd(bcmul($q, $frombase), $r); 
			} 
		} 
		else $q = $str; 

		if (intval($tobase) != 10) { 
			$s = ''; 
			while (bccomp($q, '0', 0) > 0) { 
				$r = intval(bcmod($q, $tobase)); 
				$s = base_convert($r, 10, $tobase) . $s; 
				$q = bcdiv($q, $tobase, 0); 
			} 
		} 
		else $s = $q; 

		return $s; 
	}
