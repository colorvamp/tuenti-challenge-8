#!/usr/bin/php
<?php
	ini_set('memory_limit', '5000M');
	$i = file('php://stdin');

	$_conn = true;
	if (!empty($_conn)) {
		$_conn = new _conn();
		$_conn->connect();
		$_conn->write('SUBMIT');
	}
	$case = 0;
	//while (($data = trim(array_shift($i))) && ++$case) {
	while (($data = $_conn->read()) && ++$case) {
		if (!empty($_conn)) {
			$valid = false;
			if ($a = strpos($data,'Start!'.PHP_EOL)) {
				$valid = true;
				$data = substr($data,$a + strlen('Start!'));
			}
			if ($a = strpos($data,'OK, next!'.PHP_EOL)) {
				echo 'OK, next!'.PHP_EOL;
				$valid = true;
				$data = substr($data,$a + strlen('OK, next!'));
			}
			if (!$valid) {
				echo 'Error:'.$data;exit;
			}
		}
		$data = trim($data);
		if (!empty($_conn)) {
			file_put_contents('./testa.txt',$data.PHP_EOL,FILE_APPEND);
		}
		$parts  = explode(' ',$data);
		$values = ['a'=>1,'A'=>2,'c'=>3,'C'=>4,'g'=>5,'G'=>6,'t'=>7,'T'=>8];

		$unique = [];
		$combs  = [];
		$c = 0;
		$max = count($parts);
		foreach (permute($parts) as $comb) {
			$count = count($comb);

			$str = '';
			foreach ($comb as $k) {$str .= $k;}
			if ((strlen($str) % 2)) {continue;}
			$key = str_split($str);
			$key = array_count_values($key);
			foreach ($key as $letter=>$c) {
				if (($c % 2)) {continue 2;}
			}


			if ( true ) {
				$val = [];
				foreach ($comb as $k=>$word) {
					$word = str_split($word);
					foreach ($word as $letter) {
						if (!isset($val[$k])) {$val[$k] = 0;}
						$val[$k] += $values[$letter];
					}
				}
				$total = array_sum($val);
				//if (($total % 2)) {continue;}
				$half  = floor($total / 2);
				$elems = ceil($count / 2);
				$found = [];
				foreach (permute($val) as $order){
					if (count($order) != $elems) {continue;}
					if (array_sum($order) == $half) {
						$compl = array_diff_assoc($val,$order);

						$str1 = '';
						foreach ($order as $k=>$dummy) {
							$str1 .= $comb[$k];
						}
						$str2 = '';
						foreach ($compl as $k=>$dummy) {
							$str2 .= $comb[$k];
						}

						$key1 = str_split($str1);
						$key1 = array_count_values($key1);
						ksort($key1);
						$key2 = str_split($str2);
						$key2 = array_count_values($key2);
						ksort($key2);

						if ($key1 === $key2) {
							$found[] = $order;
							$resolve = array_keys($comb);
							$resolve = array_map(function($n){return $n + 1;},$resolve);
							$resolve = implode(',',$resolve);

							if (!empty($_conn)) {
								$_conn->write($resolve);
							}else{
								echo $resolve;
							}

							continue 3;
						}
					}
				}
				if (!$found) {continue;}
			}
		}
	}
	exit;

	class _conn{
		public $ip   = '52.49.91.111';
		public $port = '3241';
		public $fp   = false;
		public $cr   = "\n";
		function connect(){
			$this->fp = fsockopen($this->ip, $this->port, $errno, $errstr, 30);
			if( !$this->fp ){echo "$errstr ($errno)<br />\n";exit;}
			stream_set_blocking($this->fp,0);
        		stream_set_blocking(STDIN,0);
			usleep(150000);
		}
		function write($text = ''){
			fwrite($this->fp,$text.$this->cr);
			usleep(180000);
		}
		function read(){
			echo 'READ -----'.PHP_EOL;
			$blob = '';
			while( ($buffer = fgets($this->fp,128)) ){
				$blob .= $buffer;
			}
			return $blob;
		}
	}

	function permute($pool = []): iterable{
		$keys  = array_keys($pool);
		$count = count($pool);
		$max   = str_pad('',$count,1);
		$max   = bindec($max);
		yield $pool;
		while( --$max ){
			$t = sprintf('%0'.$count.'b',$max);
			$t = str_split($t);
			$perm = [];
			foreach( $t as $k=>$i ){
				if( !$i ){continue;}
				$perm[$keys[$k]] = $pool[$keys[$k]];
			}
			yield $perm;
		}
	};

