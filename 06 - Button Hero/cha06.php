#!/usr/bin/php
<?php
	ini_set('memory_limit', '5000M');
	$i = file('php://stdin');
	$num = trim(array_shift($i));

	$case = 0;
	while (($data = trim(array_shift($i))) && ++$case) {
		$_button_hero = new _button_hero();

		list($lines,) = explode(' ',$data);
		while ($lines--) {
			$line = trim(array_shift($i));
			$line = explode(' ',$line);
			$note = ['d'=>$line[0],'l'=>$line[1],'v'=>$line[2],'s'=>$line[3]];
			$_button_hero->notes[] = $note;
		}

		$score = $_button_hero->start();
		echo 'Case #'.$case.': '.$score.PHP_EOL;
	}
	exit;

	class _button_hero{
		public $notes = [];
		public $tree_bySeconds = [];
		public $second_last = 0;
		public $places = [];
		public $score  = 0;
		public $path   = [];
		function start(){
			foreach ($this->notes as $note) {
				$push = $note['d'] / $note['v'];
				$note['p'] = $push;
				$note['r'] = $push + ($note['l'] / $note['v']);
				$this->tree_bySeconds[$push]['notes'][] = $note;
				if ($push > $this->second_last) {$this->second_last = $push;}
			}

			/* Group equal notes */
			foreach ($this->tree_bySeconds as $s=>$second) {
				$indexed = [];
				foreach ($second['notes'] as $note) {
					$push    = $note['d'] / $note['v'];
					$release = $push + $note['l'] / $note['v'];
					$indexed[$push.'-'.$release][] = $note;
				}
				foreach ($indexed as $l=>$idx) {
					if (count($idx) > 1) {
						list($push,$release) = explode('-',$l);
						foreach ($this->tree_bySeconds[$s]['notes'] as $k=>$note) {
							if ($note['p'] == $push
							 && $note['r'] == $release) {
								unset($this->tree_bySeconds[$s]['notes'][$k]);
							}
						}

						$finalnote = reset($idx);
						$finalnote['s'] = array_map(function($n){return $n['s'];},$idx);
						$finalnote['s'] = array_sum($finalnote['s']);
						$this->tree_bySeconds[$s]['notes'][] = $finalnote;
					}
				}
			}

			ksort($this->tree_bySeconds);
			//print_r($this->tree_bySeconds);
			//exit;

			$this->move(0);
			//echo 'Score: '.$this->score.PHP_EOL;
			//print_r($this->path);
			return $this->score;
		}
		function move($s,$score = 0,$path = []){
			if ($s > $this->second_last) {
				if ($this->score < $score) {
					$this->score = $score;
					$this->path = $path;
				}
				return false;
			}
			if (isset($this->places[$s]) && ($this->places[$s] >= $score)) {return false;}
			$this->places[$s] = $score;

			if (empty($this->tree_bySeconds[$s])) {
				$this->move($s + 1,$score,$path);
				return false;
			}

			foreach ($this->tree_bySeconds[$s]['notes'] as $note) {
				$release = $note['l'] / $note['v'];
				$this->move($s + $release + 1,$score + $note['s'],$path);
			}

			$this->move($s + 1,$score,$path);
		}
	}


