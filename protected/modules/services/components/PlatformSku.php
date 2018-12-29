<?php


class PlatformSku extends ServicesModule{  

    /**
     * platform and sku
     * $a = new PlatfromSku('EB');
	   $sku = $a->createSku($sku);
     */
	public $_platform = '';
	
	public function __construct($platformCode){
		$this->_platform = $platformCode;
	}
	
	public function createSku($sku){
		return call_user_func_array(array("self","createEncryptSku".$this->_platform),array('sku'=>$sku));
	}
	
	public function realSku($sku){
		return call_user_func_array(array("self","realSku".$this->_platform),array('sku'=>$sku));
	}
	
	//EBAY 加密
	public function createEncryptSkuEB($sku){
		
		$encryptSku = '';
		$sku = trim($sku);
		$length = strlen($sku);
		$i = 0;
		$GlobalAddchar = true;
		while($i<$length){
			$encryptSku .= substr($sku,$i,1);
			if(substr($sku,$i,1)=='.'){
				$GlobalAddchar = false;
			}
			$addchr = true;
			if($i>=$length-1){//最后一个不加
				$addchr = false;
			}
			if($i==$length-2){//倒数第二个
				$LastAscll = ord(substr($sku,$i+1,1));
				if($LastAscll>=ord('A') && $LastAscll<=ord('Z') || $LastAscll>=ord('a') && $LastAscll<=ord('z')){
					//如果最后一个是字符,那么倒数第二个后面也不添加
					$addchr = false;
				}
			}
			if($GlobalAddchar && $addchr){
				//				$rand = rand(1,2);
				$rand = 2;
				$encryptSku .= $this->getRandomChars($rand);
			}
			$i++;
		}
// 		$temps = getFieldBySimple($encryptSku);      //表未构造
		$stemp=false;   //测试
		if($temp){
			return $this->getEncryptSku($sku);
		}else{
			return $encryptSku;
		}		
	}

	//getFieldBySimple 查询表（表未构造）
// 	public function getFieldBySimple($encryptSku){
		
// 		$result = $this->getDbConnection()->createCommand()
// 		->select('encryptsku')
// 		->from('ebay_product')
// 		//->join($location.' l','a.warehouse_location_id=l.id')
// 		->where("encryptsku={$encryptSku}")
// 		->queryRow();
// 		return $result;
// 	}
	
	
	
	public function getRandomChars($length){
		$chars = '';
		while($length--){
			$chars .= $this->getUpperChar();
		}
		return $chars;
	}
	
	public function getUpperChar(){
		$rand = rand(65,90);
		$chr = chr($rand);
		if(in_array($chr,array('I','O'))){
			return $this->getUpperChar();
		}else{
			return $chr;
		}
	}
	
	
  //EBAY 解密
	public function realSkuEB($encryptSku){
		$realSku = '';
		$encryptSku = trim($encryptSku);
		$length = strlen($encryptSku);
		$i = 0;
		while($i<$length){
			$addchr = false;
			$char = substr($encryptSku,$i,1);
			if($i>=$length-1){//最后一个需要
				$addchr = true;
			}else{
				$charAscll = ord($char);
				if($charAscll>=ord('A') && $charAscll<=ord('Z') || $charAscll>=ord('a') && $charAscll<=ord('z')){
					$addchr = false;
				}else{
					$addchr = true;
				}
			}
			if($addchr){
				$realSku .= $char;
			}
			$i++;
		}
		return $realSku;
	}
//end EBAY
	
	
	
	/*
	 * 获取amazon加密sku
	 * */
	public function createEnctyptSkuAMAZON($sku){
		$encryptSku = '';//加密后的sku
		$sku = trim($sku);
		$rand = 2;//插入两位
		if(strpos($sku,'.'))//判断sku是否有'.';
		{
			$frontSku = strstr($sku,'.',true);	//截取sku'.'	前的;
			//	$position_num=strpos($sku,'.');
			$backSku = ltrim($sku,$frontSku);//截取sku'.'后的;
			$backSku = ltrim($backSku,'.');
			$len =strlen($frontSku);
			$sku = $frontSku;
		}
		else
		{
			$len =strlen($sku);
		}
		$skuLastOne = substr($sku,$len-1,1);//截取sku最后一位数
		for($i = 0;$i<$len-1;$i++)
		{
		$encryptSku.= substr($sku,$i,1);
		$encryptSku.=strtolower($this->getRandomChars($rand));
		}
		$encryptSku.= $skuLastOne;
		if($backSku)
		{
		$encryptSku.= D.$backSku;
		}	//小数点用D代替
		return $encryptSku;
	}
	
	
	//Amazon 解密
	public function realSkuAMAZON($encryptSku){
		$encryptSku =trim($encryptSku);
		if(strpos($encryptSku,"--") !== false){
			$sku = explode("--",$encryptSku);
			$sku = $sku[0];
			if(strlen($sku) < 4){
				$sku = sprintf("%04d",$sku);//格式化产品号.不足位的在前加0
			}
		}elseif(strpos($encryptSku,"-") !== false){
			$sku = explode("-",$encryptSku);
			$sku = $sku[0];
			if(strpos($encryptSku,'-2')){
				$encryptSku = $sku;
				$sku = '';
				if(strpos($encryptSku,'D'))
				{
					$backSku = strstr($encryptSku,'D');
					$backSku = str_replace('D','.',$backSku);//后面的D替换成'.';
					$encryptSku = strstr($encryptSku,'D',true);
				}
				$len = strlen($encryptSku);
				for($i = 0; $i<$len;$i++)
				{
				$str = substr($encryptSku,$i,1);
				if(preg_match('/\d/',$str))
				{
						$sku.=$str;
				}
				}
				if($backSku!='')
				{
				$sku.= $backSku;
				}
				}else{
				if(strlen($sku) < 4){
				$sku = sprintf("%04d",$sku);//格式化产品号.不足位的在前加0
				}
				}					
				}elseif(strpos($encryptSku,".") !== false){
				return $encryptSku;
		}
				else{
					$sku = '';
					if(strpos($encryptSku,'_'))//判断是否有'_';
					{
					$encryptSku = strstr($encryptSku,'_',true);//去掉'-'后面的内容
					}
					if(strpos($encryptSku,'D'))
					{
					$backSku = strstr($encryptSku,'D');
						$backSku = str_replace('D','.',$backSku);//后面的D替换成'.';
						$encryptSku = strstr($encryptSku,'D',true);
					}
					$len = strlen($encryptSku);
					for($i = 0; $i<$len;$i++)
					{
					$str = substr($encryptSku,$i,1);
					if(preg_match('/\d/',$str))
					{
					$sku.=$str;
					}
					}
					if($backSku!='')
						{
						$sku.= $backSku;
					}
					}
					//add by Tom 去掉业务人员因上架产品误操作而导致seller_sku产生多余空格的问题
					if(mb_substr_count($sku,' ')){
					$sku = str_replace(' ','', $sku);
					}
		
		
					return $sku;
	
	}	
//end amazon	
	
	
	
	
	//Wish加密
	public function createEnctyptSkuKF($sku){
		$encryptSku = '';
		$sku = trim($sku);
		$length = strlen($sku);
		$i = 0;
		$GlobalAddchar = true;
		while($i<$length){
			$encryptSku .= substr($sku,$i,1);
			if(substr($sku,$i,1)=='.'){
				$GlobalAddchar = false;
			}
			$addchr = true;
			if($i>=$length-1){//最后一个不加
				$addchr = false;
			}
			if($i==$length-2){//倒数第二个
				$LastAscll = ord(substr($sku,$i+1,1));
				if($LastAscll>=ord('A') && $LastAscll<=ord('Z') || $LastAscll>=ord('a') && $LastAscll<=ord('z')){
					//如果最后一个是字符,那么倒数第二个后面也不添加
					$addchr = false;
				}
			}
			if($GlobalAddchar && $addchr){
				//				$rand = rand(1,2);
				$rand = 2;
				$encryptSku .= $this->getRandomChars($rand);
			}
			$i++;
		}
		$temp = false;//getModel('wish_product')->getFieldBySimple("encryptsku='$encryptSku'",'encryptsku'); //表未构造
		if($temp){
			return $this->getWishEncryptSku($sku);
		}else{
			return $encryptSku;
		}
	}
	
	//Wish解密
	public function realSkuKF($encryptSku){
		$realSku = '';
		$encryptSku = trim($encryptSku);
		$length = strlen($encryptSku);
		$i = 0;
		while($i<$length){
			$addchr = false;
			$char = substr($encryptSku,$i,1);
			if($i>=$length-1){//最后一个需要
				$addchr = true;
			}else{
				$charAscll = ord($char);
				if($charAscll>=ord('A') && $charAscll<=ord('Z') || $charAscll>=ord('a') && $charAscll<=ord('z')){
					$addchr = false;
				}else{
					$addchr = true;
				}
			}
			if($addchr){
				$realSku .= $char;
			}
			$i++;
		}
		return $realSku;
	}
	
	
	
	
}
	?>	