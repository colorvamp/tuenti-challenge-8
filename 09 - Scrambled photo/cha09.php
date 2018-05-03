#!/usr/bin/php
<?php
	ini_set('memory_limit', '5000M');

	$_image = new _image();
	$_image->init();

	class _image{
		public $im = false;
		public $w  = 0;
		public $y  = 0;
		public $spectre = [];
		public $order = [];
		function init(){
			$this->im = imagecreatefrompng('scrambled-photo-test-bb113a9ce101.png');
			$this->w  = imagesx($this->im);
			$this->h  = imagesy($this->im);

			$keys = range(0,$this->w - 1);
			$this->spectre['sc'] = [];

			$target = 525; /* Random Pick ;-) */
			if ($this->w > 672) {
				$target = 544; /* Random Pick (this time is true) */
			}
			$top = 10;
			$h   = 532; /* Enough Range */
			$keys = array_diff($keys,[$target]);

			while (($count = count($keys))) {
				echo 'Left: '.$count.PHP_EOL;
				$reference = $this->range($target,$top,$h);
				$this->spectre['sc'][] = $target;
				$adj = [];
				foreach ($keys as $x) {
					if ($x == $target) {continue;}
					$range = $this->range($x,$top,$h);
					//print_r($reference);
					//print_r($range);
					$cf = $this->coeficent($reference,$range);
					$adj[] = ['x'=>$x,'c'=>$cf];
				}

				usort($adj,function($a, $b) {
					if ($a['c'] == $b['c']) {return 0;}
					return ($a['c'] < $b['c']) ? -1 : 1;
				});
				$target = $adj[0]['x'];
				$keys = array_diff($keys,[$target]);
			}

			$this->order = $this->spectre['sc'];
			$this->generate();
		}
		function pixel($x = 0,$y = 0,$rw = false){
			$rgb = imagecolorat($this->im,$x,$y);
			$r = ($rgb >> 16) & 0xFF;
			$g = ($rgb >> 8) & 0xFF;
			$b = $rgb & 0xFF;
			if ($rw) {return [$r,$g,$b];}
			return sprintf("#%02x%02x%02x", $r, $g, $b);
		}
		function range($x,$y,$h){
			$result = [];
			for ($z = 0;$z < $h;$z++) {
				$result[] = $this->pixel($x,$y + $z,true);
			}
			return $result;
		}
		function coeficent($range1 = [],$range2 = []){
			$sum = 0;
			foreach ($range1 as $k=>$color) {
				$r = abs($color[0] - $range2[$k][0]) / 255;
				$g = abs($color[1] - $range2[$k][1]) / 255;
				$b = abs($color[2] - $range2[$k][2]) / 255;

				$n = ($r + $g + $b) / 3;
				//if ($n > 0.5) {continue;}
				$sum += $n;
			}
			return $sum;
		}
		function generate(){
			$dest = imagecreatetruecolor($this->w,$this->h);
			$c = 0;
			foreach ($this->order as $x) {
				//if ($c == 477) {echo $x.PHP_EOL;}
				imagecopy($dest,$this->im,$c,0,$x,0,1,$this->h);
				$c++;
			}

			imagepng($dest,'test.png');
		}
	}


