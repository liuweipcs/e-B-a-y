<?php
class ImageUploadTools{
	
	static $token = 'C61B4A2F688F4CE8C13F55E23EEE9A17';
// 	static $siteIp = 'http://47.88.35.136';
 	static $siteIp = 'https://image-us.bigbuy.win';
	static $site = 'http://lazada.doact.online/';
	static $url = 'http://47.88.35.136/server.php';
 	static $spider = 'http://47.88.35.136/image.php';
 	static $spiderTime = 'http://47.88.35.136/imageTime.php';
	
	public static function getAmericaUrl($path,$host=""){
		if(self::isOnline($path)) return $path;
		$host = empty($host)?self::$site:$host;
		if(strpos($host, 'http')!==0){
			$host = 'http://'.$host;
		}
		$host = rtrim($host,'/');
        $path = ltrim($path,'/');
		return $host.'/'.$path;
	}
	
	public static function isOnline($url){
		return strpos($url, self::$site)===0;
	}
	
	public static function resizeImage($url,$width=200,$height=200){
		return strpos($url, self::$site)===0?$url.'@h'.$height.'_w'.$width:$url;
	}
	/*
	 * 访问远程url抓取
	 * */
	public static function  getImageExists($url){
		$ch = curl_init(); 
		$timeout = 60; 
		curl_setopt ($ch, CURLOPT_URL, $url); 
		curl_setopt ($ch, CURLOPT_HEADER, 1); 
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
		curl_setopt($ch, CURLOPT_NOBODY, true);
		$contents = curl_exec($ch);
		$headerInfo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($headerInfo != 200){
		return false;
		}else{
		return true;
		}
	}    
	
	//根据sku,图片路径上传
	public static function uploadImageUrl($sku,$imageUrl,$filePath = null,$platform = null){
		if(strpos($imageUrl, 'http')===0){
			return $imageUrl;
		}
		$imageModel = UebModel::model('ImageUpload');
		$baseDir = dirname(Yii::app()->basePath);
		$fileName = !empty($filePath)?$filePath:realpath($baseDir.$imageUrl);
		$image = $imageModel->find('image_url=:i',array(
				':i'=>$imageUrl
		));
        $host = empty($platform) ? '' : self::$siteIp;
		if(empty($image)){
			$fields = array(
					'f'=>new CURLFile($fileName),
					'key'=>self::$token,
					'image_name'=>basename($imageUrl),
					'image_url'=>dirname($imageUrl)
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, self::$url);
			curl_setopt($ch, CURLOPT_POST, 1 );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields );
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			$result =curl_exec($ch);
			curl_close($ch);
			if($result==1){
				if($imageModel->getDbConnection()->createCommand()->insert($imageModel->tableName(), array(
						'sku'=>$sku,
						'image_url'=>$imageUrl,
						'upload_status'=>1,
						'upload_time'=>date('Y-m-d H:i:s'),
						'user_id'=>date('Y-m-d H:i:s'),
				))){
					return self::getAmericaUrl($imageUrl,$host);
				}
			}
			return false;
		}else{
			return self::getAmericaUrl($image->image_url,$host);
		}
	}
	//上传的文件，直接上传到服务器
	public static function uploadImageFile($sku,$imageName,$imageUrl,$filePath){
// 		$imageName = preg_replace('/[\w\\.-]{1,}\\.[a-zA-Z]{2,}/', '', $imageName);
		$path = '/^[\w\-]{1,}\\.[a-zA-Z]{2,}$/';
		if(!preg_match($path,$imageName)){
			$imageName = md5_file($filePath).'.'.pathinfo($imageName, PATHINFO_EXTENSION);
		}
		$imagePath = dirname($imageUrl.'/'.$imageName).'/'.$imageName;
		$filePath = realpath($filePath);
 		if(!is_dir(dirname('.'.$imagePath))){
 			@mkdir(dirname('.'.$imagePath),0777);
 		}
		if(!copy($filePath,'.'.$imagePath)){
			return false;
		}
		return self::uploadImageUrl($sku, $imagePath,$filePath);
	}
	
	//获取当前sku的所有在美国的地址
	public static function getUploadImages($sku){
		$model = new ImageUpload();
		$imageList = $model->queryPairs('id,image_url','sku=:s',array(
				':s'=>$sku
		));
		$result = array();
		if(!empty($imageList)){
			foreach ($imageList as $val){
				$result[$val] = self::getAmericaUrl($val);
			}
		}
		return $result;
	}
	
	//批量上传图片：需要完整的地址：域名+图片路径
	public static function UploadImagesBatch($sku,array $imageList){
		$model = new ImageUpload();
		$imageInfo = array();
		if(!empty($imageList)){
			foreach ($imageList as $key=>$val){
				$url = parse_url($val);
				$imageInfo[$key] = $url['path'];
			}
		}
		$imageArray = $model->getDbConnection()->createCommand()
			->select('*')->from($model->tableName())->where(array(
					'AND','sku="'.$sku.'"',array('IN','image_url',$imageInfo)
					))->queryAll();
		if(!empty($imageArray)){
			foreach ($imageArray as $val){
				$key = array_search($val['image_url'], $imageInfo);
				if(false !== $key){ unset($imageList[$key]);unset($imageInfo[$key]);}
			}
		}
		if(empty($imageList)) return true;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$spider);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
				'token'=>self::$token,
				'urls'=>$imageList
		)));
		$data = curl_exec($ch);
		curl_close($ch);
		if($data==1){
			$flag = true;
			foreach ($imageInfo as $val){
				$flag = $model->getDbConnection()->createCommand()->insert($model->tableName(), array(
						'image_url'=>$val,
						'sku'=>$sku,
						'upload_status'=>1,
						'user_id'=>!empty(Yii::app()->user->id)?Yii::app()->user->id:0,
						'upload_time'=>date('Y-m-d H:i:s'),
				));
				if(!$flag) break;
			}
			if($flag) return true;
		}
		return false;
	}
	
	//批量上传图片：需要完整的地址：域名+图片路径
	public static function UploadImagesBatchTime($sku,array $imageList){
		$model = new ImageUpload();
		$imageInfo = array();
		if(!empty($imageList)){
			foreach ($imageList as $key=>$val){
				$url = parse_url($val);
				$imageInfo[$key] = $url['path'];
			}
		}
		$imageArray = $model->getDbConnection()->createCommand()
		->select('*')->from($model->tableName())->where(array(
				'AND','sku="'.$sku.'"',array('IN','image_url',$imageInfo)
				))->queryAll();
		if(!empty($imageArray)){
			foreach ($imageArray as $val){
				$key = array_search($val['image_url'], $imageInfo);
				if(false !== $key){ unset($imageList[$key]);unset($imageInfo[$key]);}
			}
		}
		if(empty($imageList)) return true;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$spiderTime);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
				'token'=>self::$token,
				'urls'=>$imageList
		)));
		$data = curl_exec($ch);
		curl_close($ch);
		if($data==1){
			$flag = true;
			foreach ($imageInfo as $val){
				$flag = $model->getDbConnection()->createCommand()->insert($model->tableName(), array(
						'image_url'=>$val,
						'sku'=>$sku,
						'upload_status'=>1,
						'user_id'=>Yii::app()->user->id,
						'upload_time'=>date('Y-m-d H:i:s'),
				));
				if(!$flag) break;
			}
			if($flag) return true;
		}
	
		return false;
	}
	
	//获取产品图片：美国服务器+本地的图片
	public static function getImageBySku($sku){
		$imageLocal = UebModel::model('Productimage')->getFtLists($sku);
		$imageOnline = self::getUploadImages($sku);
		if(!empty($imageOnline)){
			foreach ($imageOnline as $key=>$val){
				if(!in_array($key, $imageLocal)){
					array_push($imageLocal, $val);
				}
			}
		}
		return array_values($imageLocal);
	}
	
}