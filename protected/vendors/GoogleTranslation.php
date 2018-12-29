<?php
class GoogleTranslation{
	const GOOGLE_URL = 'http://translate.google.cn/translate_a/t?client=webapp';
	const USER_AGENT = 'Mozilla/5.0 (Android; Mobile; rv:22.0) Gecko/22.0 Firefox/22.0';
	
	static function shr32($x, $bits)  {  
	    if($bits <= 0){  
	        return $x;  
	    }  
	    if($bits >= 32){  
	        return 0;  
	    }  
	    $bin = decbin($x);  
	    $l = strlen($bin);  
	    if($l > 32){  
	        $bin = substr($bin, $l - 32, 32);  
	    }elseif($l < 32){  
	        $bin = str_pad($bin, 32, '0', STR_PAD_LEFT);  
	    }  
	    return bindec(str_pad(substr($bin, 0, 32 - $bits), 32, '0', STR_PAD_LEFT));  
	}   
	
	static function charCodeAt($str, $index)  {  
	    $char = mb_substr($str, $index, 1, 'UTF-8');  
	    if (mb_check_encoding($char, 'UTF-8'))  {  
	        $ret = mb_convert_encoding($char, 'UTF-32BE', 'UTF-8');  
	        return hexdec(bin2hex($ret));  
	    }else{  
	        return null;  
	    }  
	}
	
	static function RL($a, $b){  
	    for($c = 0; $c < strlen($b) - 2; $c +=3) {  
	        $d = $b{$c+2};  
	        $d = $d >= 'a' ? self::charCodeAt($d,0) - 87 : intval($d);  
	        $d = $b{$c+1} == '+' ? self::shr32($a, $d) : $a << $d;  
	        $a = $b{$c} == '+' ? ($a + $d & 4294967295) : $a ^ $d;  
	    }  
	    return $a;  
	}
	
	static function TKK(){  
	    $a = 561666268;  
	    $b = 1526272306;  
	    return 406398 . '.' . ($a + $b);  
	}
	
	static function TL($a){
		$tkk = explode('.', self::TKK());
		$b = $tkk[0];
		for($d = array(), $e = 0, $f = 0; $f < mb_strlen ( $a, 'UTF-8' ); $f ++) {
			$g = self::charCodeAt ( $a, $f );
			if (128 > $g) {
				$d [$e ++] = $g;
			} else {
				if (2048 > $g) {
					$d [$e ++] = $g >> 6 | 192;
				} else {
					if (55296 == ($g & 64512) && $f + 1 < mb_strlen ( $a, 'UTF-8' ) && 56320 == (self::charCodeAt ( $a, $f + 1 ) & 64512)) {
						$g = 65536 + (($g & 1023) << 10) + (self::charCodeAt ( $a, ++ $f ) & 1023);
						$d [$e ++] = $g >> 18 | 240;
						$d [$e ++] = $g >> 12 & 63 | 128;
					} else {
						$d [$e ++] = $g >> 12 | 224;
						$d [$e ++] = $g >> 6 & 63 | 128;
					}
				}
				$d [$e ++] = $g & 63 | 128;
			}
		}
		$a = $b;
		for($e = 0; $e < count ( $d ); $e ++) {
			$a += $d [$e];
			$a = self::RL ( $a, '+-a^+6' );
		}
		$a = self::RL ( $a, "+-3^+b+-f" );
		$a ^= $tkk[1];
		if (0 > $a) {
			$a = ($a & 2147483647) + 2147483648;
		}
		$a = fmod ( $a, pow ( 10, 6 ) );
		return $a . "." . ($a ^ $b);
	}
	
	static function translate($q,$sl='zh-CN',$tl='fr'){
		$tk = self::TL($q);
		$q = urlencode(stripslashes($q));
		
		$url = self::GOOGLE_URL."&sl=".$sl."&tl=".$tl."&hl=".$tl."&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&dt=at&ie=UTF-8&oe=UTF-8&otf=2&ssel=0&tsel=0&kc=1&tk=".$tk;
		$ch = curl_init();
		//长度小于400的，get，大于就post
		if(strlen($q)<400){
			$url .= '&q='.$q;
		}else{
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'q='.$q);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		$result = curl_exec($ch);
		curl_close($ch);
		return trim($result,'"');
	}

}