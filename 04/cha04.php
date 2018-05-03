#!/usr/bin/php
<?php
	$i = file('php://stdin');
	//$out = fopen('php://stderr', 'w');
	$num = trim(array_shift($i));

	$case = 0;
	while (($data = trim(array_shift($i))) && ++$case) {
		list($lines,) = explode(' ',$data);
		$blob = '';
		while ($lines--) {
			$blob .= trim(array_shift($i)).PHP_EOL;
		}

		$_map = new _map();
		$_map->parse($blob);
		//$_map->paint();exit;

		$jumps = 0;
		$_dwarfs = new _dwarfs();
		$_dwarfs->matrix = $_map->map;
		$_dwarfs->start();
		if ($_dwarfs->found) {$jumps += $_dwarfs->found;}
		else {$jumps = false;}

		if ($jumps) {
			$_dwarfs->start  = 'P';
			$_dwarfs->target = 'D';
			$path = $_dwarfs->start();
			if ($_dwarfs->found) {$jumps += $_dwarfs->found;}
			else {$jumps = false;}
		}

		if ($jumps === false) {
			$jumps = 'IMPOSSIBLE';
		}

		echo 'Case #'.$case.': '.$jumps.PHP_EOL;
	}
	exit;

	class _map{
		public $map = [];
		function parse($blob = '',$x = false,$y = false) {
			$blob = preg_replace('![\n]+$!','',$blob);
			$lines = explode(PHP_EOL,$blob);
			$count = count($lines);
			foreach ($lines as $k=>$line) {
				$this->map[$k] = str_split($line);
			}

			return $this->map;
		}
		function paint($matrix = [] , $vx = false , $vy = false){
			$limit = 6;
			if( !$matrix ){$matrix = $this->map;}
			foreach ($matrix as $y=>$rows) {
				if( $vy !== false && ( $y < ($vy - $limit) || $y > ($vy + $limit) ) ){continue;}
				$footer = 0;
				echo '┼';
				foreach( $rows as $x=>$v ){
					if( $vx !== false && ( $x < ($vx - $limit) || $x > ($vx + $limit) ) ){continue;}
					$footer += 1;
					echo '───┼';
				}
				echo PHP_EOL;
				echo '|';
				foreach( $rows as $x=>$v ){
					if( $vx !== false && ( $x < ($vx - $limit) || $x > ($vx + $limit) ) ){continue;}
					if( $v == ' ' ){echo $x.'.'.$y.'|';continue;}
					echo ' '.$v.' |';
				}
				echo PHP_EOL;
			}
			if( isset($footer) ){
				echo '┼';
				while( $footer-- ){
					echo '───┼';
				}
				echo PHP_EOL;
			}
		}
		function paintd( $vx = false , $vy = false ){
			$limit = 6;
			$xs = range($vx - $limit,$vx + $limit);
			$ys = range($vy - $limit,$vy + $limit);
			foreach( $ys as $y ){
				echo '┼';
				foreach( $xs as $x){echo '───┼';}
				echo PHP_EOL;
				echo '|';
				foreach( $xs as $x){
					$v = isset($this->map[$y][$x]) ? $this->map[$y][$x] : ' ';
					if( $v == '#' ){$v = "\033[31m".$v."\033[0m";}
					echo ' '.$v.' |';
				}
				echo PHP_EOL;
			}
			echo '┼';
			foreach( $xs as $x){echo '───┼';}
			echo PHP_EOL;
		}
		function paintd_final(){
			foreach( $this->map as $y=>$rows ){
				foreach( $rows as $x=>$v ){
					$v = isset($this->map[$y][$x]) ? $this->map[$y][$x] : ' ';
					echo $v;
				}
				echo PHP_EOL;
			}
			echo PHP_EOL;
		}
	}
	class _dwarfs{
		public $matrix = [];
		public $places = [];
		public $found  = false;
		public $path   = [];
		public $startX = false;
		public $startY = false;
		public $start  = 'S';
		public $target = 'P';
		function findPos(){
			$this->startX = $this->startY = false;
			foreach ($this->matrix as $y=>$row) {
				if (($x = array_search($this->start,$row)) !== false) {
					$this->startX = $x;
					$this->startY = $y;
					return true;
				}
			}
			return false;
		}
		function findTarget(){
			$this->targetX = $this->targetY = false;
			foreach ($this->matrix as $y=>$row) {
				if (($x = array_search($this->target,$row)) !== false) {
					$this->targetX = $x;
					$this->targetY = $y;
					return true;
				}
			}
			return false;
		}
		function getPos(){
			return ['x'=>$this->startX,'y'=>$this->startY];
		}
		function start(){
			$this->found  = false;
			$this->path   = [];
			$this->places = [];
			$r = $this->findPos();
			if (!$r) {echo 'Player not found';exit;}
			$this->findTarget();
			$this->move($this->startX + 2,$this->startY + 1);
			$this->move($this->startX - 2,$this->startY + 1);

			$this->move($this->startX + 2,$this->startY - 1);
			$this->move($this->startX - 2,$this->startY - 1);

			$this->move($this->startX + 1,$this->startY + 2);
			$this->move($this->startX + 1,$this->startY - 2);

			$this->move($this->startX - 1,$this->startY + 2);
			$this->move($this->startX - 1,$this->startY - 2);
			return $this->path;
		}
		function move($x,$y,$mov = 0,$path = []){
			$mov++;
			if ($this->found && ($this->found <= $mov)) {return false;}
			if (!isset($this->matrix[$y][$x])) {return false;}
			if (isset($this->places[$y][$x]) && ($this->places[$y][$x] <= $mov)) {return false;}
			$this->places[$y][$x] = $mov;

			if ($this->matrix[$y][$x] == '#') {return false;}
			if ($this->matrix[$y][$x] == $this->start) {return false;}
			//$path[] = ['x'=>$x,'y'=>$y];
			if ($this->matrix[$y][$x] == $this->target) {
				$this->path  = $path;
				//echo 'found: '.$mov.PHP_EOL;
				$this->found = $mov;
				return false;
			}

			if ($this->matrix[$y][$x] == '*') {
				if (!$this->found) {
					$this->movements = [];
					$this->movements[] = [$x + 4,$y + 2];
					$this->movements[] = [$x - 4,$y + 2];
					$this->movements[] = [$x + 4,$y - 2];
					$this->movements[] = [$x - 4,$y - 2];
					$this->movements[] = [$x + 2,$y + 4];
					$this->movements[] = [$x + 2,$y - 4];
					$this->movements[] = [$x - 2,$y + 4];
					$this->movements[] = [$x - 2,$y - 4];
					$this->heuristic();

					foreach ($this->movements as $m) {
						$this->move($m[0],$m[1],$mov,$path);
					}
				}else{
					$this->move($x + 4,$y + 2,$mov,$path);
					$this->move($x - 4,$y + 2,$mov,$path);

					$this->move($x + 4,$y - 2,$mov,$path);
					$this->move($x - 4,$y - 2,$mov,$path);

					$this->move($x + 2,$y + 4,$mov,$path);
					$this->move($x + 2,$y - 4,$mov,$path);

					$this->move($x - 2,$y + 4,$mov,$path);
					$this->move($x - 2,$y - 4,$mov,$path);
				}
			}else{
				if (!$this->found) {
					$this->movements = [];
					$this->movements[] = [$x + 2,$y + 1];
					$this->movements[] = [$x - 2,$y + 1];
					$this->movements[] = [$x + 2,$y - 1];
					$this->movements[] = [$x - 2,$y - 1];
					$this->movements[] = [$x + 1,$y + 2];
					$this->movements[] = [$x + 1,$y - 2];
					$this->movements[] = [$x - 1,$y + 2];
					$this->movements[] = [$x - 1,$y - 2];
					$this->heuristic();

					foreach ($this->movements as $m) {
						$this->move($m[0],$m[1],$mov,$path);
					}
				}else{
					$this->move($x + 2,$y + 1,$mov,$path);
					$this->move($x - 2,$y + 1,$mov,$path);

					$this->move($x + 2,$y - 1,$mov,$path);
					$this->move($x - 2,$y - 1,$mov,$path);

					$this->move($x + 1,$y + 2,$mov,$path);
					$this->move($x + 1,$y - 2,$mov,$path);

					$this->move($x - 1,$y + 2,$mov,$path);
					$this->move($x - 1,$y - 2,$mov,$path);
				}
			}

		}
		function heuristic(){
			foreach ($this->movements as &$mov) {
				$dx = abs($this->targetX - $mov[0]);
    				$dy = abs($this->targetY - $mov[1]);
				//$w  = $dx + $dy;
				$w = sqrt($dx * $dx + $dy * $dy);
				$mov[] = $w;
			}
			unset($mov);
			usort($this->movements,function($a,$b){
				if ($a[2] == $b[2]) {return 0;}
				return ($a[2] < $b[2]) ? -1 : 1;
			});
		}
	}
