#!/usr/bin/php
<?php
	ini_set('memory_limit', '5000M');
	$i = file('php://stdin');
	$num = trim(array_shift($i));

	$case = 0;
	while (($data = trim(array_shift($i))) && ++$case) {
		$_just_dance = new _just_dance();
		$_just_dance->level = 1;

/*$_just_dance->dancers = 6;
$_just_dance->hates_in_raw[] = [4,5];
$score = $_just_dance->start();
echo $score;
exit;*/

		list($people,$grunges) = explode(' ',$data);
		$_just_dance->dancers = $people;
		while ($grunges--) {
			$line = trim(array_shift($i));
			$line = explode(' ',$line);

			$_just_dance->hates[$line[0]][$line[1]] = true;
			sort($line);
			$_just_dance->hates_in_raw[] = $line;
		}

if ($case != 1) {continue;}
//if ($people != 10) {continue;}
//echo $case.PHP_EOL;
//$_just_dance->hates_in_raw[] = [1,4];
//$_just_dance->hates_in_raw[] = [2,3];

		//print_r($_just_dance->hates_in_raw);exit;
		$_tree = new _tree($_just_dance->hates_in_raw);
		$_just_dance->tree = $_tree->tree;
		$score = $_just_dance->start();
		echo 'Case #'.$case.': '.$score.PHP_EOL;
	}
	exit;

	class _tree{
		public $tree = ['childs'=>[]];
		function __construct($data = []){
			usort($data,function($a, $b){
				$d1 = abs($a[0] - $a[1]);
				$d2 = abs($b[0] - $b[1]);
				if ($d1 == $d2) {return 0;}
				return ($d1 > $d2) ? -1 : 1;
			});

			$dist = [];
			foreach ($data as $_node){
				$d = abs($_node[0] - $_node[1]);
				if (!($d % 2)) {continue;}
				$maxd = [];

				$this->_append($_node,$this->tree);
			}
			//print_r($this->tree);exit;
		}
		function _append($node,&$root){
			$d = abs($node[0] - $node[1]);

			$found = false;
			if (!empty($root['childs'])) {
				foreach ($root['childs'] as &$folder) {
					if (!$this->inside($node,$folder['node'])) {continue;}
					$this->_append($node,$folder);
					$found = true;
				}
				unset($folder);
			}

			$this->dist[$d] = true;
			$root['childs'][implode('-',$node)] = [
				 'node'=>$node
				,'childs'=>[]
			];

			return true;
		}
		function inside($node,$folder){
			if (array_intersect($node,$folder)) {return false;}
			if ($node[0] > $folder[0] && $node[1] < $folder[1]) {return true;}

			return false;
		}
		function intersects($pos1,$pos2){
			if (array_intersect($pos1,$pos2)) {return true;}
			if ($pos1[0] < $pos2[0]
			 && $pos2[0] < $pos1[1]
			 && $pos1[1] < $pos2[1]) {return true;}

			if ($pos2[0] < $pos1[0]
			 && $pos1[0] < $pos2[1]
			 && $pos2[1] < $pos1[1]) {return true;}

			return false;
		}
	}

	class _just_dance{
		public $dancers = 0;
		public $hates = [];
		public $paths = [];
		public $hates_in_raw = [];
		public $debug = true;
		function start(){
			$this->number  = ($this->dancers / 2);
			$catalan = $this->catalan($this->number);

			$c = 1;
			$j = 1;
			$this->groups = [];
			//FIXME: simplemente pasar dancers / 2
			while ($c <= $this->number) {
				if (!($c % 2)) {$c++;continue;}
				$this->groups[$c] = $this->groups[$this->dancers - $c] = [
					 'total'=>$this->catalan($this->number - $j) * $this->catalan($j - 1)
					,'childs'=>$this->catalan($this->number - $j -2) * $this->catalan($j - 1)
					,'offset'=>$this->catalan($j - 1)
					,'level'=>$j
				];
				$c++;
				$j++;
			}

			if ($this->debug) {
				echo 'dancers in level '.($this->level).': '.$this->dancers.PHP_EOL;
				echo 'catalan: '.$catalan.PHP_EOL;
			}
			if ($this->debug) {
				//$this->file = file_get_contents($this->dancers.'.txt');
			}

//echo $this->how_many_in_ranges([[2,3]],7).PHP_EOL;
//echo $this->how_many_in_ranges([[2,5]],7).PHP_EOL;
//echo $this->how_many_in_ranges([[1,6]],7).PHP_EOL;
//exit;

			if (!empty($this->tree['node'])) {
				$distance = abs($this->tree['node'][0] - $this->tree['node'][1]);
				$catalan = $this->groups[$distance]['total'];
				echo 'sub-catalan: '.$catalan.PHP_EOL;
			}

if ($this->level == 1) {
//print_r($this->tree);exit;
}

			foreach ($this->tree['childs'] as &$folder){
				$should = 0;

				$distance = abs($folder['node'][0] - $folder['node'][1]);
if ($this->level == 2) {
//echo $distance;exit;
}
				$should = $this->groups[$distance]['total'];

				if (!empty($folder['childs'])) {
					foreach ($folder['childs'] as $d=>&$subfolder) {
						//$r = $this->how_many_in_ranges($ranges = [],$distance);
//echo $should.PHP_EOL;
//print_r($subfolder);
//exit;
//echo $this->dancers - 6;exit;
						$_just_dance = new _just_dance();
						//FIXME: esto está mal
//$_just_dance->dancers = 3 * 2;
						$_just_dance->dancers = $distance - 1;
						//$_just_dance->dancers = ($this->number - 2) * 2;

						$_just_dance->level = $this->level + 1;
						$_just_dance->tree  = $subfolder;
						$_just_dance->debug = $this->debug;
						$subfolder = $_just_dance->start();
					}
					unset($subfolder);
					$should -= array_sum($folder['childs']);

				}


				$folder = $should;
				$catalan -= $should;
			}
			unset($folder);

			
if ($this->level == 1) {
//echo $catalan;
print_r($this->tree);exit;
}

			return $catalan;

			$appliedHates = [];
			$getCompatible = function($ahate,$debug = true) use (&$appliedHates,&$hate){
				$c = 0;
				foreach ($appliedHates as $bhate) {
					if (array_intersect($ahate,$bhate)) {continue;}
					if ($ahate[0] < $bhate[0]
					 && $bhate[0] < $ahate[1]
					 && $ahate[1] < $bhate[1]) {continue;}

					if ($bhate[0] < $ahate[0]
					 && $ahate[0] < $bhate[1]
					 && $bhate[1] < $ahate[1]) {continue;}

if ($debug) {
//print_r($ahate);
print_r($bhate);
}

					$dist = ($bhate[1] - $bhate[0]);
					$c += $this->how_many_in_ranges([$ahate],$dist);

				}
				return $c;
			};

			usort($this->hates_in_raw,function($a, $b){
				$d1 = abs($a[0] - $a[1]);
				$d2 = abs($b[0] - $b[1]);
				if ($d1 == $d2) {return 0;}
				return ($d1 > $d2) ? -1 : 1;
			});

			foreach ($this->hates_in_raw as $hate) {
				sort($hate);
				$dist = abs($hate[0] - $hate[1]);
				if (!($dist % 2)) {continue;}
				if ($dist > 1) {continue;}


				$compatible = 0;
				//$compatible = $getCompatible($hate);
				//if ($compatible > $this->groups[$dist]['total']) {$compatible = $this->groups[$dist]['total'];}
				$should = $this->groups[$dist]['total'] - $compatible;

				if (false && $this->debug) {
					preg_match_all('!\[(|.*?,)'.$hate[0].'\-'.$hate[1].'(|,.*?)\]!',$this->file,$m);
					echo 'Se tienen que eliminar '.count($m[0]).PHP_EOL;
					if ($should != count($m[0])) {
						print_r($m[0]);
						print_r($hate);
						echo 'error: '.$should.PHP_EOL;
						echo $getCompatible($hate,true).PHP_EOL;
						file_put_contents('abc',$this->file);
						exit;
					}
					$this->file = preg_replace('!\[(|.*?,)'.$hate[0].'\-'.$hate[1].'(|,.*?)\] => 1\n!','',$this->file);
				}

				$catalan -= $should;
				if ($this->debug) {
					//print_r($hate);
					//echo 'Quitando '.$this->groups[$dist]['total'].' menos '.$compatible.PHP_EOL;
				}
				$appliedHates[] = $hate;
			}
			return $catalan;
		}
		function intersects($pos1,$pos2){
			if (array_intersect($pos1,$pos2)) {return true;}
			if ($pos1[0] < $pos2[0]
			 && $pos2[0] < $pos1[1]
			 && $pos1[1] < $pos2[1]) {return true;}

			if ($pos2[0] < $pos1[0]
			 && $pos1[0] < $pos2[1]
			 && $pos2[1] < $pos1[1]) {return true;}

			return false;
		}
		function how_many_in_ranges($ranges = [],$intrange = 1){
			$total = 0;
			$min = false;
			$max = false;

			foreach ($ranges as $range) {
				$total += $this->groups[($range[1] - $range[0])]['total'];
				if ($this->debug) {
					$should = $this->groups[($range[1] - $range[0])]['total'];
					//echo 'Para ('.$range[0].','.$range[1].') debe haber: '.$should.' repartidos'.PHP_EOL;
					if (false && $this->file) {
						preg_match_all('!(\[|,)'.$range[0].'-'.$range[1].'(\]|,)!',$this->file,$m);
						if (count($m[0]) != $should) {
							echo 'Tus suposiciones son incorrectas'.PHP_EOL;
							exit;
						}
					}
				}
				$m = min($range);
				$M = max($range);
				if ($min == false || $m < $min) {$min = $m;}
				if ($max == false || $M > $max) {$max = $M;}
			}

			$subgroups = [];
			$keys = [];
			$c = 1;
			foreach ($this->groups as $g=>$group) {
				foreach ($ranges as $range){
					if ($this->intersects($range,[0,$g])) {continue 2;}
				}
				$subgroups[] = [
					 'g'=>$g
				];
				$keys[] = $c;
				$c += 2;
			}

			usort($subgroups,function($a, $b){
				if ($a['g'] == $b['g']) {return 0;}
				return ($a['g'] < $b['g']) ? -1 : 1;
			});

			$subgroups = array_combine($keys,$subgroups);
//print_r($subgroups);

//FIXME: esta distancia depende del numero de $ranges
//FIXME: puede haber 2
$dist = $max - $min;
$corr = $this->catalan((($dist + 1) / 2) - 1);

			$number = count($subgroups) * 2;
			$c = 1;
			$j = 1;
			while ($c <= $number) {
				if (!($c % 2)) {$c++;continue;}
				$v = $this->catalan($number/2 - $j) * $this->catalan($j - 1) * $corr;
				$subgroups[$c]['total'] = $subgroups[$number - $c]['total'] = $v;
				$c++;
				$j++;
			}

//print_r($ranges);
//
//print_r($this->groups);
//exit;
			//echo '  Factor de corrección = '.$corr.PHP_EOL;
			foreach ($subgroups as &$group) {
				if ($this->debug) {
					$should = $group['total'];
					preg_match_all('!\[0-'.$group['g'].'(,.*?|),'.$range[0].'-'.$range[1].'(\]|,)!',$this->file,$m);
					if (false) {
						if (count($m[0]) != $should) {
							echo '  Tus suposiciones son incorrectas en ['.$group['g'].'] dices '.$should.' y parece que hay '.count($m[0]).PHP_EOL;
							exit;
						}else{
							echo '  Correcto para ['.$group['g'].'] ('.$should.')'.PHP_EOL;
						}
					}
				}
			}
			unset($group);
//print_r($subgroups);

			if (false && $this->debug) {
				print_r($ranges);
				print_r($subgroups);
			}

			$c = 0;
			foreach ($subgroups as $group) {
				if ($group['g'] == $intrange) {$c += $group['total'];}
			}
			return $c;
		}

		function brute(){
			if ($this->dancers % 2) {return 0;}
			//for ($i = $dancer;$i < $this->dancers;$i++) {
			$this->move(0);
			//return count($this->paths);
			print_r($this->paths);
			exit;
		}
		function move($dancer = [],$path = [],$pairs = []){
			$path[$dancer] = true;
			$count = count($path);

			if ($count == $this->dancers) {
				$str = array_chunk(array_keys($path),2);
				$str = array_map(function($n){sort($n);return implode('-',$n);},$str);
				sort($str);
				$str = implode(',',$str);
				if (isset($this->paths[$str])) {return false;}
				$this->paths[$str] = true;
				//if ($str == '0-7,1-4,2-5,3-6') {print_r($path);}
				return false;
			}

			$keys = array_keys($path);
			sort($keys);
			$f = array_search($dancer,$keys);
			$max = $keys[$f + 1] ?? false;
			$min = $keys[$f - 1] ?? false;

			for ($i = 0;$i < $this->dancers;$i++) {
				if ($i == $dancer) {continue;}
				if (isset($path[$i])) {continue;}
				if (isset($this->hates[$dancer][$i])
				 || isset($this->hates[$i][$dancer])) {continue;}

				if ($count % 2 && $max && $i > $max) {continue;}
				if ($count % 2 && $min && $i < $min) {continue;}

				$this->move($i,$path);
			}
		}
		function catalan($n){
			if ($n <= 1) {return 1;}
 
			$res = 0;
			for ($i=0; $i<$n; $i++){
				$res += $this->catalan($i) * $this->catalan($n - $i - 1);
			}
 
			return $res;
		}
	}
