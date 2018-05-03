#!/usr/bin/php
<?php
	$i = file('php://stdin');
	//$out = fopen('php://stderr', 'w');
	$num = trim(array_shift($i));

	$_scale = array_merge(['C','D','E','F','G','A','B'],['C','D','E','F','G','A']);
	$_map   = array_merge([ 2 , 2 , 1 , 2 , 2 , 2 , 1 ],[ 2 , 2 , 1 , 2 , 2 , 2 ]);
	$_mapin = array_combine($_scale,$_map);

	$case = 0;
	while (($data = array_shift($i)) && ++$case) {
		if ($data === '0'.PHP_EOL) {echo 'Case #'.$case.': MA MA# MB MC MC# MD MD# ME MF MF# MG MG# mA mA# mB mC mC# mD mD# mE mF mF# mG mG#'.PHP_EOL;continue;}
		$notes = trim(array_shift($i));
		$notes = str_replace(['E#','B#','Cb','Fb'],['F','C','B','E'],$notes);
		$notes = explode(' ',$notes);
		convertNotes($notes);
		$notes = array_unique($notes);
		$final = [];
		
		for ($j = 0;$j < 7;$j++) {
			$scale  = array_slice($_scale,$j,7);
			$map    = array_slice($_map,$j,7);
			$note = $scale[0];
			$scaleMajor = getScaleMajor($scale,$map);
			convertNotes($scaleMajor);
			$scaleMinor = getScaleMinor($scale,$map);
			convertNotes($scaleMinor);

			$scaleMajorSharp = [];
			if ($note != 'E' && $note != 'B') {
				$scaleMajorSharp = getScaleMajor($scale,$map,+1);
			}

			$scaleMinorSharp = [];
			if ($note != 'E' && $note != 'B') {
				$scaleMinorSharp = getScaleMinor($scale,$map,+1);
			}

			convertNotes($scaleMajorSharp);
			convertNotes($scaleMinorSharp);

			if (!array_diff($notes,$scaleMajor)) {
				$final[] = 'M'.$scale[0];
			}
			if (!array_diff($notes,$scaleMinor)) {
				$final[] = 'm'.$scale[0];
			}
			if ($scaleMajorSharp && !array_diff($notes,$scaleMajorSharp)) {
				$final[] = 'M'.$scale[0].'#';
			}
			if ($scaleMinorSharp && !array_diff($notes,$scaleMinorSharp)) {
				$final[] = 'm'.$scale[0].'#';
			}
		}

		if ($final) {
			sort($final);
			$final = implode(' ',$final);
		} else {
			$final = 'None';
		}

		echo 'Case #'.$case.': '.$final.PHP_EOL;
	}

	function convertNotes(&$notes = []){
		global $_mapin;
		global $_scale;
		foreach ($notes as &$note) {
			if (strlen($note) == 3 && $note[1] == '#') {
				$k = array_search($note[0],$_scale);
				if ($k == 0) {$k = 7;}
				$note = $_scale[$k + 1];
			}
			if (strlen($note) == 3 && $note[1] == 'b') {
				$k = array_search($note[0],$_scale);
				if ($k == 0) {$k = 7;}
				$note = $_scale[$k - 1];
			}
			if (strlen($note) == 2 && $note[1] == 'b') {
				$k = array_search($note[0],$_scale);
				if ($k == 0) {$k = 7;}
				$prev = $_scale[$k - 1];
				$note = $prev.'#';
				if ($note == 'E#') {$note = 'F';}
				if ($note == 'B#') {$note = 'C';}
			}
		}
		unset($note);
	}

	function getScaleMajor($scale,$map,$padding = 0){
		$target = [ 2 , 2 , 1 , 2 , 2 , 2 , 1 ];
		$count_target = $padding;
		$count_map    = 0;
		$final  = [];
		foreach ($map as $k=>$tone) {
			$diff = ($count_target - $count_map);
			if ($diff > 0) {
				$final[] = $scale[$k].str_repeat('#',$diff);
			} elseif ($diff < 0) {
				$final[] = $scale[$k].str_repeat('b',abs($diff));
			} else {
				$final[] = $scale[$k];
			}
			$count_target += $target[$k];
			$count_map    += $map[$k];
		}

		if (in_array('E#',$final)) {$final[] = 'F';}
		if (in_array('B#',$final)) {$final[] = 'C';}
		if (in_array('Cb',$final)) {$final[] = 'B';}
		if (in_array('Fb',$final)) {$final[] = 'E';}

		return $final;
	}
	function getScaleMinor($scale,$map,$padding = 0){
		$target = [ 2 , 1 , 2 , 2 , 1 , 2 , 2 ];
		$count_target = $padding;
		$count_map    = 0;
		$final  = [];
		foreach ($map as $k=>$tone) {
			$diff = ($count_target - $count_map);
			if ($diff > 0) {
				$final[] = $scale[$k].str_repeat('#',$diff);
			} elseif ($diff < 0) {
				$final[] = $scale[$k].str_repeat('b',abs($diff));
			} else {
				$final[] = $scale[$k];
			}
			$count_target += $target[$k];
			$count_map    += $map[$k];
		}

		if (in_array('E#',$final)) {$final[] = 'F';}
		if (in_array('B#',$final)) {$final[] = 'C';}
		if (in_array('Cb',$final)) {$final[] = 'B';}
		if (in_array('Fb',$final)) {$final[] = 'E';}

		return $final;
	}
