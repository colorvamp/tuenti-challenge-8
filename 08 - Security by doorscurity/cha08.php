#!/usr/bin/php
<?php
	ini_set('memory_limit', '5000M');
	$i = file('php://stdin');
	$num = trim(array_shift($i));

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

		$score = $_intel_inside->start();
		echo 'Case #'.$case.': '.$score.PHP_EOL;
	}
	exit;

	class _intel_inside{
		public $doors = [];
		public $secs  = [];
		public $count = 0;
		function start(){
			if (count($this->doors) < 2) {
				return $this->doors[0]['p'] - $this->doors[0]['t'] - 1;
			}

			$found = false;

			foreach ($this->doors as $k=>$door) {
				$cpy = $this->doors;
				$this->step($door['p']);
				foreach ($this->doors as $j=>$test) {
					if ($j == $k) {continue;}
					if ($cpy[$j]['t'] == $test['t']) {return 'NEVER';}
				}
				$this->step(-$door['p']);
			}

			/* INI-init */
			$this->secs = array_map(function($n){
				return $n['t'];
			},$this->doors);
			$this->count = array_sum($this->secs);
			/* END-init */

			//print_r($this->doors);exit;
			$sub = end($this->doors);
			$inc = $sub['p'];
			$sub = $sub['t'];
			if ($sub) {
				$this->step($sub * -1);
			}

			$count = count($this->doors);
			$padding = false;
			$steps = 0;

			//echo 'buscar eu'.PHP_EOL;
			$padding = $this->padding();
			if ($padding === false) {return 'NEVER';}
			//echo 'padding '.$padding.PHP_EOL;
			$lcm = $this->lcm();
			$next  = bcsub($lcm,$padding);
			$steps = bcadd($steps,$next);
			//echo 'stage: '.$next.PHP_EOL;
			$this->step($next);

			$p = 0;
			while (count($this->doors) > 2) {
				$p++;
				//echo 'quedan '.count($this->doors).PHP_EOL;
				array_splice($this->doors,-2);
				$this->doors[] = ['p'=>$lcm,'t'=>1];
				$this->step(-1);

				$padding = $this->padding();
				if ($padding === false) {return 'NEVER';}
				//echo 'padding '.$padding.PHP_EOL;
				$lcm = $this->lcm();
				$next = bcsub($lcm,$padding);
				$steps = bcadd($steps,$next);
				//echo 'stage: '.$steps.PHP_EOL;
				$this->step($next);
			}

			return bcsub($steps,$sub + $count - 1);
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

