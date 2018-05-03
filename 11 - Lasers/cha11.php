#!/usr/bin/php
<?php
	ini_set('memory_limit', '5000M');
	$i = file('php://stdin');
	$num = trim(array_shift($i));

	$case = 0;
	while (($data = trim(array_shift($i))) && ++$case) {
		$_lazers = new _lazers();

		list($h,$w,$items) = explode(' ',$data);
		while ($items--) {
			$line = trim(array_shift($i));
			$line = explode(' ',$line);

			//sort($line);
			$_lazers->items_by_y_x[$line[0]][$line[1]] = true;
			$_lazers->items_by_x_y[$line[1]][$line[0]] = true;
			$_lazers->items_in_raw[] = ['x'=>$line[1],'y'=>$line[0]];
		}

//if ($case > 40) {exit;}
//if ($case != 5) {continue;}
//echo 'base ('.$case.') '.$w.' '.$h.PHP_EOL;
		$score = $_lazers->start($w,$h);
		$score = ($w + $h) - $score;
		echo 'Case #'.$case.': '.$score.PHP_EOL;
	}
	exit;

	class _lazers{
		public $visited = false;
		public $score = false;
		public $items_in_raw = [];
		public $removey = [];
		public $removex = [];
		function start($w = 0,$h = 0){
			if (empty($this->items_in_raw)) {return 0;}
			$this->w = $w;
			$this->h = $h;
			//$this->drawRule();exit;

			foreach ($this->items_by_y_x as $y=>&$x) {
				$x = $w - count($x);
			}
			unset($x);
			foreach ($this->items_by_x_y as $x=>&$y) {
				$y = $h - count($y);
			}
			unset($y);
			ksort($this->items_by_x_y);
			ksort($this->items_by_y_x);

			while (!empty($this->items_in_raw)) {
				$this->step();

			}

			$removex = $this->removex;
			$removey = $this->removey;

			$this->score = min([$w,$h]);
			$items = $this->items_in_raw;
			$this->move($items,$removex,$removey);
			return $this->score;
		}
		function step(){
			$shouldStay_x = [];
			$shouldStay_y = [];

			$numx = array_count_values($this->items_by_x_y);
			$numy = array_count_values($this->items_by_y_x);
			$numx = $numx[($this->h - 1)] ?? 0;
			$numy = $numy[($this->w - 1)] ?? 0;

			if ($numy >= $numx) {
				$max = max($this->items_by_y_x);
				foreach ($this->items_by_y_x as $y=>$qy) {
					if ($qy != $max) {continue;}
					$shouldStay_y[] = $y;
				}
				foreach ($this->items_in_raw as $k=>$item) {
					if (in_array($item['y'],$shouldStay_y)) {
						$this->removex[$item['x']] = true;
						unset($this->items_in_raw[$k]);
					}
				}
			}else{
				$max = max($this->items_by_x_y);
				foreach ($this->items_by_x_y as $x=>$qx) {
					if ($qx != $max) {continue;}
					$shouldStay_x[] = $x;
				}
				foreach ($this->items_in_raw as $k=>$item) {
					if (in_array($item['x'],$shouldStay_x)) {
						$this->removey[$item['y']] = true;
						unset($this->items_in_raw[$k]);
					}
				}
			}
			foreach ($this->items_in_raw as $k=>$item) {
				if (isset($this->removex[$item['x']])
				 || isset($this->removey[$item['y']])) {
					unset($this->items_in_raw[$k]);
				}
			}

			$this->items_by_x_y = [];
			$this->items_by_y_x = [];
			foreach ($this->items_in_raw as $k=>$item) {
				if (empty($this->items_by_x_y[$item['x']])) {$this->items_by_x_y[$item['x']] = 0;}
				if (empty($this->items_by_y_x[$item['y']])) {$this->items_by_y_x[$item['y']] = 0;}
				$this->items_by_x_y[$item['x']]++;
				$this->items_by_y_x[$item['y']]++;
			}
			foreach ($this->items_by_y_x as $y=>&$x) {
				$x = $this->w - $x;
			}
			unset($x);
			foreach ($this->items_by_x_y as $x=>&$y) {
				$y = $this->h - $y;
			}
			unset($y);
			ksort($this->items_by_x_y);
			ksort($this->items_by_y_x);
		}
		function is_resolved($removex = [],$removey = []){
			foreach ($this->items_in_raw as $item) {
				if (isset($removex[$item['x']])) {continue;}
				if (isset($removey[$item['y']])) {continue;}
				return false;
			}
			return true;
		}
		function move($items = [],$removex = [],$removey = []){
			$index = count($items);
			$score = count($removex) + count($removey);
			if ($this->score !== false
			 && $this->score <= $score) {return false;}

			if (!$items) {
				if ($this->is_resolved($removex,$removey)) {
					if ($this->score === false
					 || $this->score > $score) {
						$this->score = $score;
					}
					if (false) {
						echo 'x:'.PHP_EOL;
						print_r($removex);
						echo 'y:'.PHP_EOL;
						print_r($removey);
						echo 'resuelto en '.$score.'!'.PHP_EOL;
					}
					return false;
				}				
				return false;
			}

			$item = array_shift($items);

			if (isset($removex[$item['x']])
			 || isset($removey[$item['y']])) {
				$this->move($items,$removex,$removey);
				return false;
				echo 'sin conflicto, que hacer?';
				exit;
			}


			$this->move($items,$removex + [$item['x']=>true],$removey);
			$this->move($items,$removex,$removey + [$item['y']=>true]);
		}
		function drawRule(){
			if (false){ 
				foreach ($this->items_by_y_x as $y=>&$x) {
					$x = count($x);
				}
				unset($x);
				foreach ($this->items_by_x_y as $x=>&$y) {
					$y = count($y);
				}
				unset($y);
				asort($this->items_by_y_x);
				asort($this->items_by_x_y);
				$y_keys = array_keys($this->items_by_y_x);
				$x_keys = array_keys($this->items_by_x_y);

				echo '<div style="position:relative;">';
				foreach ($this->items_in_raw as $item) {
					$pos_top  = array_search($item['y'],$y_keys);
					$pos_left = array_search($item['x'],$x_keys);
					echo '<div style="position:absolute;background:black;width:10px;height:10px;left:'.($pos_left * 10 + 10).';top:'.($pos_top * 10 + 10).';"></div>';
				}
				foreach ($this->items_by_y_x as $y=>$x) {
					$pos_top  = array_search($y,$y_keys);
					echo '<div style="position:absolute;font-size:8;width:10px;height:10px;left:0;top:'.($pos_top * 10 + 10).';">'.$y.'</div>';
				}
				foreach ($this->items_by_x_y as $x=>$y) {
					$pos_left = array_search($x,$x_keys);
					echo '<div style="position:absolute;font-size:8;width:10px;height:10px;top:0;left:'.($pos_left * 10 + 10).';">'.$x.'</div>';
				}
				echo '</div>';
				//print_r($y_keys);
				exit;
			}



			echo '<div style="position:relative;">';
			foreach ($this->items_in_raw as $item) {
				echo '<div style="position:absolute;background:black;width:10px;height:10px;left:'.($item['x'] * 10 + 10).';top:'.($item['y'] * 10 + 10).';"></div>';
			}
			foreach ($this->items_by_y_x as $y=>$x) {
				echo '<div style="position:absolute;font-size:8;width:10px;height:10px;left:0;top:'.($y * 10 + 10).';">'.$y.'</div>';
			}
			foreach ($this->items_by_x_y as $x=>$y) {
				echo '<div style="position:absolute;font-size:8;width:10px;height:10px;top:0;left:'.($x * 10 + 10).';">'.$x.'</div>';
			}
			echo '</div>';
		}
	}


