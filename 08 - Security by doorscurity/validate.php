#!/usr/bin/php
<?php
	ini_set('memory_limit', '5000M');
	$i = file('php://stdin');
	$num = trim(array_shift($i));
	$valids = file_get_contents('validate.csv');

	$case = 0;
	while (($data = trim(array_shift($i))) && ++$case) {
		$_intel_inside = new _intel_inside();

		list($lines,) = explode(' ',$data);
		while ($lines--) {
			$line = trim(array_shift($i));
			$line = explode(' ',$line);
			$door = ['p'=>$line[0],'t'=>$line[1]];
			if ($door['p'] < 2) {continue;}
			$_intel_inside->doors[] = $door;
		}

		preg_match('!Case \#'.$case.': ([0-9]+)!',$valids,$m);
		if (empty($m[1])) {continue;}

		$score = $_intel_inside->start($m[1]);
		echo 'Case #'.$case.': '.$score.PHP_EOL;
	}
	exit;

	class _intel_inside{
		public $doors = [];
		public $secs  = [];
		public $count = 0;
		function start($test){
			$found = false;

			/* INI-init */
			$this->secs = array_map(function($n){
				return $n['t'];
			},$this->doors);
			$this->count = array_sum($this->secs);
			/* END-init */

			$this->step($test);
			foreach ($this->doors as $k=>$door) {
				if ($k == 0) {continue;}
				if (($door['t'] + $k) != $door['p']) {
					print_r($door);
					echo 'caca';
					exit;
				}
			}
			return 'YES!';
			echo 'valid'.PHP_EOL;exit;
			print_r($this->doors);exit;
			exit;
		}
		function padding(){
			$cpy = array_keys($this->doors);
			$cpy = array_slice($cpy,-2,2);
			$cpy = array_reverse($cpy);

			$num1 = $this->doors[$cpy[0]]['p'];
			$num2 = $this->doors[$cpy[1]]['p'];
			$pad  = $this->doors[$cpy[1]]['t'] - (count($cpy) - 1);

			$limit = $this->_lcm($num1, $num2);
			return $this->_padding($num1,$num2,$pad,$limit);
		}
		function _padding($num1,$num2,$pad,$limit = false){
			if ($num1 <= $num2) {
				$c = 1;
				while (true) {
					$t = bcadd(bcmul($num2,$c),$pad);
					if ($limit && bccomp($t,$limit) > 0) {return false;}
					if (bcmod($t,$num1)) {
						$c++;
						continue;
					}
					return $t;
					echo ($c * $num2 + $pad);exit;
					break;
				}
			}

			//$l = microtime(1);
			if (false) {
				$m = 1;
				if ($num1 < $num2) {
					$m = ceil($num2 / $num1);
					$num1 = bcmul($num1,$m);
				}
			}

			$diff = bcsub($num1,$num2);
			$c = 1;
			$t = 0;
			while (true) {
				$t = bcadd($t,$diff);
				if ($limit && bccomp($t,$limit) > 0) {return false;}
				if (bcmod($t,$num2) != $pad) {
					$c++;
					continue;
				}
				return bcmul($c,$num1);
			}
			return false;
		}
		function check(){
			foreach ($this->check as $k=>$v) {
				if ($this->secs[$k] != $v) {return false;}
			}
			return true;
		}
		function step($inc = 0){
			foreach ($this->doors as $k=>&$door) {
				$door['t'] = bcadd($door['t'],$inc);
				if (bccomp($door['t'],$door['p']) > -1) {$door['t'] = bcmod($door['t'],$door['p']);}
				if (bccomp($door['t'],'0') < 0) {
					//$door['t'] = ($door['t'] % $door['p'] + $door['p']) % $door['p'];
					$door['t'] = bcmod(bcadd(bcmod($door['t'],$door['p']),$door['p']),$door['p']);
				}
				$this->secs[$k] = $door['t'];
			}
			unset($door);
		}
		function lcm(){
			$cpy = array_keys($this->doors);
			$cpy = array_slice($cpy,-2,2);
			$key = array_shift($cpy);

			$base = $this->doors[$key]['p'];
			while ($key = array_shift($cpy)) {
				$base = $this->_lcm($base,$this->doors[$key]['p']);
			}
			return $base;
		}
		function _lcm($m, $n) {
			if ($m == 0 || $n == 0) return 0;
			$r = bcdiv(bcmul($m,$n),$this->_gcd($m, $n));
			return $r;
		}
		function _gcd($a, $b) {
			while (bccomp($b,'0') != 0) {
				$t = $b;
				$b = bcmod($a,$b);
				$a = $t;
			}
			return $a;
		}
	}


