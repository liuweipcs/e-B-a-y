<?php
/**
 * Model Helper Class
 * @package Application.components
 * @auther Bob <Foxzeng>
 */
class MHelper {

    /**
     * get msg type config
     */
    public static function getMsgTypeConfig() {
        static $data = array();
        if ( empty($data) ) {
            $list = MsgType::model()->findAll('status=:status', array(':status' => MsgType::ENABLED_STATUS));
            $data = array();
            foreach ($list as $key => $val) {
                $data[$val['code']] = $val['name'];
            }
            $data[MsgType::PERSONAL_MSG_CODE] = Yii::t('system', 'Personal Message');
        }

        return $data;
    }


    /**
     * get msg type
     *
     * @param type $type
     * @return type
     * @throws CException
     */
    public  function getMsgType($type) {
        $data = self::getMsgTypeConfig();

        if (! isset($data[$type]) ) {
            throw new CException(Yii::t('excep','"{class}"  config infomation does not exists or errors.', array(
                '{class}'=> get_class($this),
            )));
        }

        return $data[$type];
    }

    /**
     * get user id - name pairs
     *
     * @staticvar array $pairs
     * @return type
     */
    public static function getUserPairs() {
        static $pairs = array();
        if ( empty($pairs) ) {
        	$pairs = Yii::app()->cache->get('upl');
        	if(empty($pairs)){
	            $pairs = UebModel::model('user')
	                ->queryPairs('id,user_full_name');
	            Yii::app()->cache->set('upl', $pairs,3600*6);
        	}
        }

        return $pairs;
    }
	
	/**
     * get user id - department_id pairs
     *
     * @staticvar array $pairs
     * @return type
     */
    public static function getUserDepartment($id) {
        static $pairs = array();
        if ( empty($pairs) ) {
        	$pairs = Yii::app()->cache->get('upl1');
        	if(empty($pairs)){
	            $pairs = UebModel::model('user')
	                ->queryPairs('id,department_id');
	            Yii::app()->cache->set('upl1', $pairs,3600*6);
        	}
        }
		// var_dump($pairs[1311]);die;
		// $sql="SELECT `id`, `department_id` FROM `ueb_user` WHERE id=$id";
		// $pairs = UebModel::model('user')->getDbConnection()->createCommand($sql)->queryAll();

        return $pairs[$id];
    }
    
	public static function getUserDepartmentname($id) {
		$id=MHelper::getUserDepartment($id);
        static $pairs = array();
        //static $pmpairs = array();
        if ( empty($pairs) ) {
        	$pairs = Yii::app()->cache->get('upl1');
        	if(empty($pairs)){
	            $pairs = UebModel::model('user')
	                ->queryPairs('id,department_id');
	            Yii::app()->cache->set('upl1', $pairs,3600*6);
        	}
        }
		if ( empty($pmpairs) ) {
        	$pmpairs = Yii::app()->cache->get('uplp');
        	if(empty($pmpairs)){
	            $pmpairs = UebModel::model('department')
	                ->queryPairs('id,department_name');
	            Yii::app()->cache->set('uplp', $pmpairs,3600*6);
        	}
        }
		
		 // $pmpairs = UebModel::model('department')
	                 // ->queryPairs('id,department_name');
					 
        // return '总经办';
		$department=$pmpairs[$id];
        return $department;
    }

    /**
     * get user name
     *
     * @param type $id
     * @return type
     * @throws CException
     */
    public static function getUsername($id) {

        if (empty($id)) return '';
        $data = self::getUserPairs();

        if (! isset($data[$id]) ) {
            //echo '<font color="red">unknown</font>';
            //throw new CException(Yii::t('excep','config infomation does not exists or errors.'));
            return '';
        }else{
            return $data[$id];
        }
    }

    public static function getRefundConfig($type=null){

        if (empty($type)) return '';
        $reason = new reasonList();
        $reasonList = $reason->getResonList();
        $reasonConfig = $reasonList[reasonList::REASON_TYPE];
        if ($type !==  null) {
            return $reasonConfig[$type];
        }


    }

    public static function getResendConfig($type){
        $reason = new reasonList(reasonList::RESEND_TYPE);
        $reasonList = $reason->getResonList();
        return $reasonList[$type];
    }


    /**
     * get data type
     */

    public static function getDataType($type='') {
        $options = array(
            ''=>Yii::t('system', 'Please Select'),
            'datetime'=>Yii::t('system', 'Data time'),
            'checkbox'=>Yii::t('system', 'Checkbox'),
            'select'=>Yii::t('system', 'Select'),
            'nums'=>Yii::t('system', 'Nums'),
            'input'=>Yii::t('system', 'Input'),
        );
        if(!empty($type)) {
            return $options[$type];
        }
        return $options;
    }


    /**
     * get provider level id - name pairs
     *
     * @staticvar array $pairs
     * @return type
     */
    public static function getProviderLevelPairs($id = null) {
        static $pairs = array();
        if ( empty($pairs) ) {
            $pairs = UebModel::model('providerLevel')
                ->queryPairs('id,level_name');
        }
        if ( $id !== null ) {
            return $pairs[$id];
        }
        return $pairs;
    }

    /**
     * get product binding type
     */
    public static function getBindType($type=0) {
//     	关联配件，赠送配件，捆绑销售,多数量销售,配件绑定
        $options = array(
            '1'=>Yii::t('products', 'Associated accessories'),
            '2'=>Yii::t('products', 'Gift accessories'),
        );
        if(!empty($type)) {
            return $options[$type];
        }
        return $options;
    }



    /**
     * get user info
     * @return $data
     */
    public static function getUserList($user_id=0) {
        static $data = array();
        if ( empty($data) ) {
            $list = Yii::app()->db->createCommand()
                ->select('id,user_name,user_full_name')
                ->from(User::model()->tableName())
                ->order("id asc")
                ->queryAll();
            $data =array();
            foreach($list as $key=>$val){
                $data[$val['id']] =$val['user_full_name'];
            }
        }
        if(!empty($user_id)) {
            return $data[$user_id];
        }
        return $data;

    }


    /*
     * 
     */
    public static function getPic($sku,$filepath,$search_qty=15,$types=array('jpg','gif')){
        $imageList = array();
        $i = 0;
        while($i<$search_qty){
            if($i==0){
                $filename = $sku;
            }else{
                $filename = $sku.'-'.$i;
            }
            foreach($types as $type){
                $fullname = $filename.'.'.$type;
                $local_path = $filepath.'/'.$fullname;
                if(file_exists($_SERVER['DOCUMENT_ROOT'].$local_path)){
                    $imageList[$fullname] = $local_path;
                    break;
                }
            }
            $i++;
        }
        return $imageList;
    }

    /**
     * get product pic by sku
     * @param string $sku
     * @param string $type:main,assistant,thumb,指定从哪取图片
     * @param int $id :定义产品id
     *
     * @return when $type=='thumb' $string,else return $array
     */
    public static function getProductPicBySku($sku='',$type='',$id) {

        $sku = trim($sku);
        if($sku=='') return false;
        $arr = array();
        //$first = substr($sku,0,1) ? substr($sku,0,1) : 0;
        //$second = substr($sku,1,1) ? substr($sku,1,1) : 0;
        //$child_dir = '/'.$first.$second;//目标文件
        $child_dir = '/'.$sku;//目标文件
        $img_config = UebModel::model('SysConfig')->getPairByType('image');//取ueb_sys_config 表
        $search_qty = $img_config['img_max_qty'];
        $ext_str = $img_config['img_allowed_ext'];
        $types = explode(',',$ext_str);
        $doc_root = Yii::getPathOfAlias('webroot').$img_config['img_local_path'];
        $host = 'http://'.$_SERVER['HTTP_HOST'].$img_config['img_local_path'];

        switch($type){
            case 'main':
                foreach($types as $type_v){
                    $filepath = $host.$type;
                    $file_web = $img_config['img_local_path'].$type;
                    $fullname = $sku.'.'.$type_v;
                    $url = '/products/productimage/view1/sku/'.$sku.'/ext/'.urldecode($type_v);//获取下一张图片url
                    $the_first_zt = UebModel::model('Productimage')->getZtList($sku,0);//获取第一张副图
                    $imageName = array_keys($the_first_zt);
                    $the_first_zt = array_shift($the_first_zt);
                    //$local_path = $filepath.'/'.$first.$second.'/'.$imageName[0].'.jpg';
                    $local_path = $filepath.'/'.$sku.'/'.$imageName[0].'.jpg';//获取第一张副图
                    $local_path2 = $filepath.'/'.$sku.'/'.$imageName[0].'.JPG';//获取第一张副图
                    //$filename = $file_web.'/'.$first.$second.'/'.$fullname;
                    $filename = $file_web.'/'.$sku.'/'.$fullname;
                    if(file_get_contents($local_path,0,null,0,1) || file_get_contents($local_path2,0,null,0,1)){
                        //if(file_exists($local_path) || file_exists($local_path2)){
                        if(file_get_contents($local_path,0,null,0,1)){
                            if(file_get_contents($local_path."@h60_w60",0,null,0,1)){
                                $the_first_ft = $local_path."@h60_w60";
                            }else{
                                $the_first_ft = $local_path;
                            }
                        }elseif(file_get_contents($local_path2,0,null,0,1)){
                            if(file_get_contents($local_path2."@h60_w60",0,null,0,1)){
                                $the_first_ft = $local_path2."@h60_w60";
                            }else{
                                $the_first_ft = $local_path2;
                            }
                        }else{
                            if(file_get_contents($the_first_ft."@h60_w60",0,null,0,1)){
                                $the_first_ft = $the_first_ft."@h60_w60";
                            }else{
                                $the_first_ft = $the_first_ft;
                            }
                        }
                        $arr_img=array("style"=>"border:1px solid #ccc;padding:2px;","width"=>60,
                            "height"=>60,'src'=>$the_first_zt, 'pic-link'=>$url);
                        $arr_href=array('id'=>'product_image_'.$id,'for'=>$sku,'onmouseover'=>'showPreview(event);',
                            'onmouseout'=>'hidePreview(event);','class'=>'product_image');
                        return CHtml::link(CHtml::image(Yii::app()->baseUrl.$the_first_zt,$sku,$arr_img),$url,$arr_href);
                        break;
                    }else{
                        return CHtml::image('/images/nopic.gif',$sku,
                            array("style"=>"border:1px solid #ccc;padding:2px;width:60px;height:60px;"));
                        break;
                        return self::getProductPicBySku($sku,'assistant',$id);
                        break;
                    }
                }
                break;
            case 'assistant':
                foreach($types as $type_v){
                    $filepath = $host.$type;
                    $file_web = $img_config['img_local_path'].$type;
                    $fullname = $sku.'.'.$type_v;
                    $url = '/products/productimage/view1/sku/'.$sku.'/ext/'.urldecode($type_v);//获取下一张图片url
                    $the_first_ft = UebModel::model('Productimage')->getFtLists($sku,0);
                    $imageName = array_keys($the_first_ft);
                    $the_first_ft = array_shift($the_first_ft);

                    //$local_path = $filepath.'/'.$first.$second.'/'.$imageName[0].'.jpg';//获取第一张副图
                    $local_path = $filepath.'/'.$sku.'/'.$imageName[0].'.jpg';//获取第一张副图
                    $local_path2 = $filepath.'/'.$sku.'/'.$imageName[0].'.JPG';//获取第一张副图
                    //$filename = $file_web.'/'.$first.$second.'/'.$fullname;
                    $filename = $file_web.'/'.$sku.'/'.$fullname;
                    if(file_get_contents($local_path,0,null,0,1) || file_get_contents($local_path2,0,null,0,1)){
                        //if(file_exists($local_path) || file_exists($local_path2)){

                        if(file_get_contents($local_path,0,null,0,1)){
                            if(file_get_contents($local_path."@h60_w60",0,null,0,1)){
                                $the_first_ft = $local_path."@h60_w60";
                            }else{
                                $the_first_ft = $local_path;
                            }
                        }elseif(file_get_contents($local_path2,0,null,0,1)){
                            if(file_get_contents($local_path2."@h60_w60",0,null,0,1)){
                                $the_first_ft = $local_path2."@h60_w60";
                            }else{
                                $the_first_ft = $local_path2;
                            }
                        }else{
                            if(file_get_contents($the_first_ft."@h60_w60",0,null,0,1)){
                                $the_first_ft = $the_first_ft."@h60_w60";
                            }else{
                                $the_first_ft = $the_first_ft;
                            }
                        }

                        $arr_img=array("style"=>"border:1px solid #ccc;padding:2px;","width"=>60,
                            "height"=>60,'src'=>$the_first_ft, 'pic-link'=>$url);
                        $arr_href=array('id'=>'product_image_'.$id,'for'=>$sku,'onmouseover'=>'showPreview(event);',
                            'onmouseout'=>'hidePreview(event);','class'=>'product_image');
                        return CHtml::link(CHtml::image(Yii::app()->baseUrl.$the_first_ft,$sku,$arr_img),$url,$arr_href);
                        break;
                    }else{
                        $productInfo = UebModel::model('Product')->getUploadimgsBySku($sku);
                        if($productInfo['uploadimgs']){
                            $uploadimgs = json_decode($productInfo['uploadimgs']);

                            $imageurl = 'http://'.$_SERVER['HTTP_HOST'].'/upload/image/productImages/'.$uploadimgs[0];

                            if(!file_get_contents($imageurl,0,null,0,1)){
                                return CHtml::image('/images/nopic.gif',$sku,
                                    array("style"=>"border:1px solid #ccc;padding:2px;width:60px;height:60px;"));
                                break;
                            }else{
                                if(file_get_contents($imageurl."@h160_w160",0,null,0,1)){
                                    return '<div class="picbig">'.CHtml::image('/upload/image/productImages/'.$uploadimgs[0]."@h160_w160",$sku,
                                            array("style"=>"border:1px solid #ccc;padding:2px;width:60px;height:60px;")).'</div>';
                                    break;
                                }else{
                                    return '<div class="picbig">'.CHtml::image('/upload/image/productImages/'.$uploadimgs[0],$sku,
                                            array("style"=>"border:1px solid #ccc;padding:2px;width:60px;height:60px;")).'</div>';
                                    break;
                                }
                            }
                        }else{
                            return CHtml::image('/images/nopic.gif',$sku,
                                array("style"=>"border:1px solid #ccc;padding:2px;width:60px;height:60px;"));
                            break;
                        }

                    }
                }
                break;
//     		case 'assistant':
//     			$filepath = $doc_root.$type.$child_dir;
//     			$file_web = $img_config['img_local_path'].$type.$child_dir;
//     			echo $the_first_ft = UebModel::model('Productimage')->getFtList($sku,0);//获取第一张副图
//     			//return $arr['assistant'] = self::getPic($filepath,$search_qty,$types);
//     			break;
            case 'thumb':
                foreach($types as $type_v){
                    $filepath = $doc_root.$type;
                    $file_web = $img_config['img_local_path'].$type;
                    $fullname = $sku.'.'.$type_v;
                    $local_path = $filepath.'/'.$fullname;
                    $filename = $file_web.'/'.$fullname;
                    if(file_exists($local_path)){
                        $url = '/products/productimage/view1/sku/'.$sku.'/ext/'.urldecode($type_v);//获取下一张图片url
                        $the_first_ft = UebModel::model('Productimage')->getFtList($sku,0);//获取第一张副图
                        $arr_img=array("style"=>"border:1px solid #ccc;padding:2px;","width"=>60,
                            "height"=>60,'large-src'=>array_shift($the_first_ft), 'pic-link'=>$url);
                        $arr_href=array('id'=>'product_image_'.$id,'for'=>$sku,'onmouseover'=>'showPreview(event);',
                            'onmouseout'=>'hidePreview(event);','class'=>'product_image');
                        //return CHtml::image(Yii::app()->baseUrl.$filename,$sku,array('id'=>$sku,'class'=>'ajax',
                        //"style"=>"border:1px solid #ccc;padding:2px;","width"=>80,"height"=>80));

                        return CHtml::link(CHtml::image(Yii::app()->baseUrl.$filename,$sku,$arr_img),$url,$arr_href);
                        break;
                    }else{
                        return self::getProductPicBySku($sku,'main',$id);
                        break;
                    }
                }
                break;
            default:
                $img_main_path = Yii::getPathOfAlias('webroot').'/upload/image/main'.$child_dir;
                $img_ass_path = Yii::getPathOfAlias('webroot').'/upload/image/assistant'.$child_dir;
                $img_thumb_path = Yii::getPathOfAlias('webroot').'/upload/image/thumb';
                $arr['main'] = self::getPic($sku,$img_main_path,$search_qty,$types);
                $arr['main'] = self::getPic($sku,$img_ass_path,$search_qty,$types);
                $arr['main'] = self::getPic($sku,$img_thumb_path,$search_qty,$types);
        }
        return $arr;

    }


    public static function getProductImageLoading($sku){
        return CHtml::image('/images/wait.gif',$sku,
            array("style"=>"border:0px solid #ccc;padding:2px;width:20px;height:20px;"));

    }

    public static function getProductImageThub($sku = null, $type = null, $id = null)
    {
        $sku = trim($sku);
        if (empty($sku)) {
            return null;
        }
        $the_first_ft = UebModel::model('Productimage')->getFtList($sku,$m=0);
        $webroot = Yii::getPathOfAlias('webroot');
        $thumbnails = $webroot.'/upload/image/Thumbnails/'.$sku;
        if($the_first_ft){
            $firstimage = current($the_first_ft);
            $endx = explode('/',$firstimage);
            $firstimageurl = $thumbnails.'/'.end($endx);
            if(file_get_contents($firstimageurl,0,null,0,1)){
                $oldthumbnailsimage = '/upload/image/Thumbnails/'.$sku.'/'.end($endx);
                return $oldthumbnailsimage;
            }else{
                $thumbnailsimage = MHelper::ProcessingPicture($firstimage,$sku,120,120,2);
                if(file_get_contents($webroot.$thumbnailsimage,0,null,0,1)){
                    return $thumbnailsimage;
                }
            }
        }else{
            $productInfo = UebModel::model('Product')->getUploadimgsBySku($sku);
            if($productInfo['uploadimgs']){
                $uploadimgs = json_decode($productInfo['uploadimgs']);
                $firstimage = current($uploadimgs);
                $firstimageurl = $thumbnails.'/'.$firstimage;
                if(file_get_contents($firstimageurl,0,null,0,1)){
                    $oldthumbnailsimage = '/upload/image/Thumbnails/'.$sku.'/'.$firstimage;
                    return $oldthumbnailsimage;
                }else{
                    $imageurl = $webroot.'/upload/image/productImages/'.$firstimage;
                    if(file_get_contents($imageurl,0,null,0,1)){
                        $uploadimageurl = '/upload/image/productImages/'.$firstimage;
                        $thumbnailsimage = MHelper::ProcessingPicture($uploadimageurl,$sku,120,120,2);
                        if(file_get_contents($webroot.$thumbnailsimage,0,null,0,1)){
                            return $thumbnailsimage;
                        }
                    }
                }
            }
        }
        return '/images/nopic.gif';
    }

    public static function getProductFirstImage($sku='',$type='',$id){
        $sku = trim($sku);
        if($sku=='') return false;
        $the_first_ft = UebModel::model('Productimage')->getFtList($sku,$m=0);
        $webroot = Yii::getPathOfAlias('webroot');
        $thumbnails = $webroot.'/upload/image/Thumbnails/'.$sku;
        if($the_first_ft){
            $firstimage = current($the_first_ft);
            $endx = explode('/',$firstimage);
            $firstimageurl = $thumbnails.'/'.end($endx);
            if(file_get_contents($firstimageurl,0,null,0,1) && filemtime(dirname($webroot.reset($the_first_ft)))<filemtime($firstimageurl)){
                $oldthumbnailsimage = '/upload/image/Thumbnails/'.$sku.'/'.end($endx);
                return '<div class="picbig">'.CHtml::image($oldthumbnailsimage,$sku,
                        array("style"=>"border:1px solid #ccc;padding:2px;width:60px;height:60px;")).'</div>';

            }else{
                $thumbnailsimage = MHelper::ProcessingPicture($firstimage,$sku,120,120,2);
                if(file_get_contents($webroot.$thumbnailsimage,0,null,0,1)){
                    return '<div class="picbig">'.CHtml::image($thumbnailsimage,$sku,
                            array("style"=>"border:1px solid #ccc;padding:2px;width:60px;height:60px;")).'</div>';

                }
            }
        }else{
            $productInfo = UebModel::model('Product')->getUploadimgsBySku($sku);
            if($productInfo['uploadimgs']){
                $uploadimgs = json_decode($productInfo['uploadimgs']);
                $firstimage = current($uploadimgs);
                $firstimageurl = $thumbnails.'/'.$firstimage;
                if(file_get_contents($firstimageurl,0,null,0,1)){
                    $oldthumbnailsimage = '/upload/image/Thumbnails/'.$sku.'/'.$firstimage;
                    return '<div class="picbig">'.CHtml::image($oldthumbnailsimage,$sku,
                            array("style"=>"border:1px solid #ccc;padding:2px;width:60px;height:60px;")).'</div>';

                }else{
                    $imageurl = $webroot.'/upload/image/productImages/'.$firstimage;
                    if(file_get_contents($imageurl,0,null,0,1)){
                        $uploadimageurl = '/upload/image/productImages/'.$firstimage;
                        $thumbnailsimage = MHelper::ProcessingPicture($uploadimageurl,$sku,120,120,2);
                        if(file_get_contents($webroot.$thumbnailsimage,0,null,0,1)){
                            return '<div class="picbig">'.CHtml::image($thumbnailsimage,$sku,
                                    array("style"=>"border:1px solid #ccc;padding:2px;width:60px;height:60px;")).'</div>';

                        }
                    }
                }
            }
        }
        return CHtml::image('/images/nopic.gif',$sku,
            array("style"=>"border:1px solid #ccc;padding:2px;width:60px;height:60px;"));

    }

    public static function ProcessingPicture($Image,$sku,$Dw=450,$Dh=450,$Type=1,$h=0){
        $webroot = Yii::getPathOfAlias('webroot');
        $Image = $webroot.$Image;
        if(!file_get_contents($Image,0,null,0,1)){
            return false;
        }
        //如果需要生成缩略图,则将原图拷贝一下重新给$Image赋值
        if($Type!=1){
        	if($h!=0){
        	    $thumb = '/upload/image/Thumb_no_logo/'.$sku;
        	}else{
        		$thumb = '/upload/image/Thumbnails/'.$sku;
        	}
        	$thumbnails=$webroot.$thumb;
            if (!file_exists($thumbnails)) {
                mkdir($thumbnails,0777);
            }
            //$newimagename = str_replace(".","_60x60.",$Image);
            $newimagename = $Image;
            $mx = explode('/',$newimagename);
            $newimageurl = $thumbnails.'/'.end($mx);
            copy($Image,$newimageurl);
            $Image = $newimageurl;
            $returnurl = $thumb.'/'.end($mx);  }

        //取得文件的类型,根据不同的类型建立不同的对象
        $ImgInfo=getimagesize($Image); 
        switch($ImgInfo[2]){
            case 1:
                $Img = @imagecreatefromgif($Image);
                break;
            case 2:
                $Img = @imagecreatefromjpeg($Image);
//                 VHelper::dump($Img);
                break;
            case 3:
                $Img = @imagecreatefrompng($Image);
                break;
        }
    
        
        //如果对象没有创建成功,则说明非图片文件
        if(empty($Img)){
            //如果是生成缩略图的时候出错,则需要删掉已经复制的文件
            if($Type!=1){unlink($Image);}
            return false;
        }
        //如果是执行调整尺寸操作则
        if($Type==1){
            $w=imagesx($Img);
            $h=imagesy($Img);
            $width = $w;
            $height = $h;
            if($width>$Dw){
                $Par=$Dw/$width;
                $width=$Dw;
                $height=$height*$Par;
                if($height>$Dh){
                    $Par=$Dh/$height;
                    $height=$Dh;
                    $width=$width*$Par;
                }
            }elseif($height>$Dh){
                $Par=$Dh/$height;
                $height=$Dh;
                $width=$width*$Par;
                if($width>$Dw){
                    $Par=$Dw/$width;
                    $width=$Dw;
                    $height=$height*$Par;
                }
            }else{
                $width=$width;
                $height=$height;
            }
            $nImg = imagecreatetruecolor($width,$height);   //新建一个真彩色画布
            imagecopyresampled($nImg,$Img,0,0,0,0,$width,$height,$w,$h);//重采样拷贝部分图像并调整大小
            imagejpeg ($nImg,$Image);     //以JPEG格式将图像输出到浏览器或文件
            return $returnurl;
            //如果是执行生成缩略图操作则
        }else{
            $w=imagesx($Img);
            $h=imagesy($Img);
            $width = $w;
            $height = $h;
            $nImg = imagecreatetruecolor($Dw,$Dh);
            if($h/$w>$Dh/$Dw){ //高比较大
                $width=$Dw;
                $height=$h*$Dw/$w;
                $IntNH=$height-$Dh;
                imagecopyresampled($nImg, $Img, 0, -$IntNH/1.8, 0, 0, $Dw, $height, $w, $h);
            }Else{   //宽比较大
                $height=$Dh;
                $width=$w*$Dh/$h;
                $IntNW=$width-$Dw;
                imagecopyresampled($nImg, $Img, -$IntNW/1.8, 0, 0, 0, $width, $Dh, $w, $h);
            }
            imagejpeg($nImg,$Image);
            return $returnurl;
        }
    }

    public static function KeywordNewPicture($sku=''){
        $ft = UebModel::model('Productimage')->getFtLists($sku,0);
        $productdesc = UebModel::model('Productdesc')->getSkuDescriptionEn($sku);
        $keywordsA = $productdesc->included;
        $keywordsB = $productdesc->amazon_keyword1;
        $keywordsC = $productdesc->amazon_keyword2;
        $keywordsD = $productdesc->amazon_keyword3;
        $keywordsE = $productdesc->amazon_keyword4;
        $keywordsF = $productdesc->amazon_keyword5;
        $keywords = $keywordsA.",".$keywordsB.",".$keywordsC.",".$keywordsD.",".$keywordsE.",".$keywordsF;
        $keyword = explode(",",$keywords);
        $i = 0;
        foreach($ft as $val){
            $keywordm = str_replace(" ","-",trim($keyword[$i]));
            $keywordm = str_replace(array("#","%", "$", "@", "!", "^", "&", "*", "~", "`"), "", $keywordm);
            $keyimage[$i] = MHelper::ProductPicture($val,$sku,800,800,2,$keywordm);
            $i++;
        }
        return $keyimage;
    }

    public static function ProductPicture($Image,$sku,$Dw=450,$Dh=450,$Type=1,$keyword=""){
        $webroot = Yii::getPathOfAlias('webroot');
        $Image = $webroot.$Image;
        if(!file_get_contents($Image,0,null,0,1)){
            return false;
        }
        //如果需要生成缩略图,则将原图拷贝一下重新给$Image赋值
        if($Type!=1){
            $thumbnails = $webroot.'/image/'.$sku;
            if (!file_exists($thumbnails)) {
                mkdir($thumbnails,0777);
            }
            //$newimagename = str_replace(".",$picname.".",$Image);
            //$newimagename = $Image;
            //$mx = explode('/',$newimagename);
            if(empty(trim($keyword))){
                $keyword = $sku.rand(1,99);
            }
            $newimageurl = $thumbnails.'/'.trim($keyword).".jpg";
            copy($Image,$newimageurl);
            $Image = $newimageurl;
            $returnurl = '/image/'.$sku.'/'.trim($keyword).".jpg";
        }

        //取得文件的类型,根据不同的类型建立不同的对象
        $ImgInfo=getimagesize($Image);
        switch($ImgInfo[2]){
            case 1:
                $Img = @imagecreatefromgif($Image);
                break;
            case 2:
                $Img = @imagecreatefromjpeg($Image);
                break;
            case 3:
                $Img = @imagecreatefrompng($Image);
                break;
        }
        //如果对象没有创建成功,则说明非图片文件
        if(empty($Img)){
            //如果是生成缩略图的时候出错,则需要删掉已经复制的文件
            if($Type!=1){unlink($Image);}
            return false;
        }
        //如果是执行调整尺寸操作则
        if($Type==1){
            $w=imagesx($Img);
            $h=imagesy($Img);
            $width = $w;
            $height = $h;
            if($width>$Dw){
                $Par=$Dw/$width;
                $width=$Dw;
                $height=$height*$Par;
                if($height>$Dh){
                    $Par=$Dh/$height;
                    $height=$Dh;
                    $width=$width*$Par;
                }
            }elseif($height>$Dh){
                $Par=$Dh/$height;
                $height=$Dh;
                $width=$width*$Par;
                if($width>$Dw){
                    $Par=$Dw/$width;
                    $width=$Dw;
                    $height=$height*$Par;
                }
            }else{
                $width=$width;
                $height=$height;
            }
            $nImg = imagecreatetruecolor($width,$height);   //新建一个真彩色画布
            imagecopyresampled($nImg,$Img,0,0,0,0,$width,$height,$w,$h);//重采样拷贝部分图像并调整大小
            imagejpeg ($nImg,$Image);     //以JPEG格式将图像输出到浏览器或文件
            return $returnurl;
            //如果是执行生成缩略图操作则
        }else{
            $w=imagesx($Img);
            $h=imagesy($Img);
            $width = $w;
            $height = $h;
            $nImg = imagecreatetruecolor($Dw,$Dh);
            if($h/$w>$Dh/$Dw){ //高比较大
                $width=$Dw;
                $height=$h*$Dw/$w;
                $IntNH=$height-$Dh;
                imagecopyresampled($nImg, $Img, 0, -$IntNH/1.8, 0, 0, $Dw, $height, $w, $h);
            }Else{   //宽比较大
                $height=$Dh;
                $width=$w*$Dh/$h;
                $IntNW=$width-$Dw;
                imagecopyresampled($nImg, $Img, -$IntNW/1.8, 0, 0, 0, $width, $Dh, $w, $h);
            }
            imagejpeg($nImg,$Image);
            return $returnurl;
        }
    }



    /**
     * get product pic by sku  (Purchase)
     * @param string $sku
     * @param string $type:main,assistant,thumb,指定从哪取图片
     * @param int $id :定义产品id
     *  采购单管理新建  文字弹出缩略图main
     * @return when $type=='thumb' $string,else return $array
     */
    public static function getProductPicBySkuToPurchase($sku='',$type='',$id,$cnname) {
        $sku = trim($sku);
        if($sku=='') return false;
        $arr = array();
        $first = substr($sku,0,1) ? substr($sku,0,1) : 0;
        $second = substr($sku,1,1) ? substr($sku,1,1) : 0;
        //$child_dir = '/'.$first.$second;//目标文件
        $child_dir = '/'.$sku;//目标文件
        $img_config = UebModel::model('SysConfig')->getPairByType('image');//取ueb_sys_config 表
        $search_qty = $img_config['img_max_qty'];
        $ext_str = $img_config['img_allowed_ext'];
        $types = explode(',',$ext_str);
        $doc_root = Yii::getPathOfAlias('webroot').$img_config['img_local_path'];
        switch($type){
            case 'main':
                foreach($types as $type_v){
                    $filepath = $doc_root.$type;
                    $file_web = $img_config['img_local_path'].$type;
                    $fullname = $sku.'.'.$type_v;
                    $url = '/products/productimage/view1/sku/'.$sku.'/ext/'.urldecode($type_v);//获取下一张图片url
                    $the_first_zt = UebModel::model('Productimage')->getZtList($sku,0);//获取第一张副图
                    $imageName = array_keys($the_first_zt);
                    $the_first_zt = array_shift($the_first_zt);
                    //$local_path = $filepath.'/'.$first.$second.'/'.$imageName[0].'.jpg';
                    $local_path = $filepath.'/'.$sku.'/'.$imageName[0].'.jpg';//获取第一张副图
                    $local_path2 = $filepath.'/'.$sku.'/'.$imageName[0].'.JPG';//获取第一张副图
                    //$filename = $file_web.'/'.$first.$second.'/'.$fullname;
                    $filename = $file_web.'/'.$sku.'/'.$fullname;
                    if(file_exists($local_path) || file_exists($local_path2)){
                        $arr_img=array("style"=>"border:1px solid #ccc;padding:2px;","width"=>60,
                            "height"=>60,'src'=>$the_first_zt, 'pic-link'=>$url);
                        $arr_href=array('id'=>'product_image_'.$id,'for'=>$sku,'onmouseover'=>'showPreview(event);',
                            'onmouseout'=>'hidePreview(event);','class'=>'product_image',"title"=>$cnname,);
                        return CHtml::link($cnname,$url,$arr_href);
                        break;
                    }else{
                        return self::getProductPicBySkuToPurchase($sku,'assistant',$id,$cnname);
                        break;
                    }
                }
                break;
            case 'assistant':
                foreach($types as $type_v){
                    $filepath = $doc_root.$type;
                    $file_web = $img_config['img_local_path'].$type;
                    $fullname = $sku.'.'.$type_v;
                    $url = '/products/productimage/view1/sku/'.$sku.'/ext/'.urldecode($type_v);//获取下一张图片url
                    $the_first_ft = UebModel::model('Productimage')->getFtLists($sku,0);
                    $imageName = array_keys($the_first_ft);
                    $the_first_ft = array_shift($the_first_ft);
                    $local_path = $filepath.'/'.$first.$second.'/'.$imageName[0].'.jpg';//获取第一张副图
                    $local_path2 = $filepath.'/'.$first.$second.'/'.$imageName[0].'.JPG';//获取第一张副图
                    $filename = $file_web.'/'.$first.$second.'/'.$fullname;
                    if(file_exists($local_path) || file_exists($local_path2)){
                        $arr_img=array("style"=>"border:1px solid #ccc;padding:2px;","width"=>60,
                            "height"=>60,'src'=>$the_first_ft, 'pic-link'=>$url);
                        $arr_href=array('id'=>'product_image_'.$id,'for'=>$sku,'onmouseover'=>'showPreview(event);',
                            'onmouseout'=>'hidePreview(event);','class'=>'product_image',"title"=>$cnname,);
                        return CHtml::link($cnname,$url,$arr_href);
                        break;
                    }else{
                        return CHtml::link($cnname,"",array("title"=>$cnname,"style"=>"text-decoration:none;"));
                        break;
                    }
                }
                break;
//     		case 'thumb':
//     			foreach($types as $type_v){
//     				$filepath = $doc_root.$type;
//     				$file_web = $img_config['img_local_path'].$type;
//     				$fullname = $sku.'.'.$type_v;
//     				$local_path = $filepath.'/'.$fullname;
//     				$filename = $file_web.'/'.$fullname;
//     				if(file_exists($local_path)){
//     					$url = '/products/productimage/view1/sku/'.$sku.'/ext/'.urldecode($type_v);//获取下一张图片url
//     					$the_first_ft = UebModel::model('Productimage')->getFtList($sku,0);//获取第一张副图
//     					$arr_img=array("style"=>"border:1px solid #ccc;padding:2px;","width"=>60,
//     							"height"=>60,'large-src'=>array_shift($the_first_ft), 'pic-link'=>$url);
//     					$arr_href=array('id'=>'product_image_'.$id,'for'=>$sku,'onmouseover'=>'showPreview(event);',
//     							'onmouseout'=>'hidePreview(event);','class'=>'product_image',"title"=>$cnname);
//     					//return CHtml::image(Yii::app()->baseUrl.$filename,$sku,array('id'=>$sku,'class'=>'ajax',
//     					//"style"=>"border:1px solid #ccc;padding:2px;","width"=>80,"height"=>80));

//     					return CHtml::link($cnname,$url,$arr_href);
//     					break;
//     				}else{
//     					return self::getProductPicBySkuToPurchase($sku,'main',$id,$cnname);
//     					break;
//     				}
//     			}
//     			break;
            default:
                $img_main_path = Yii::getPathOfAlias('webroot').'/upload/image/main'.$child_dir;
                $img_ass_path = Yii::getPathOfAlias('webroot').'/upload/image/assistant'.$child_dir;
                $img_thumb_path = Yii::getPathOfAlias('webroot').'/upload/image/thumb';
                $arr['main'] = self::getPic($img_main_path,$search_qty,$types);
                $arr['main'] = self::getPic($img_ass_path,$search_qty,$types);
                $arr['main'] = self::getPic($img_thumb_path,$search_qty,$types);
        }
        return $arr;




//     	if($sku=='') return false;
//     	$arr = array();
//     	$first = substr($sku,0,1) ? substr($sku,0,1) : 0;
//     	$second = substr($sku,1,1) ? substr($sku,1,1) : 0;
//     	$child_dir = '/'.$first.'/'.$second;//目标文件
//     	$img_config = UebModel::model('SysConfig')->getPairByType('image');//取ueb_sys_config 表
//     	$search_qty = $img_config['img_max_qty'];
//     	$ext_str = $img_config['img_allowed_ext'];
//     	$types = explode(',',$ext_str);
//     	$doc_root = Yii::getPathOfAlias('webroot').$img_config['img_local_path'];
//     	switch($type){
//     		case 'main':
//     			foreach($types as $type_v){
//     				$filepath = $doc_root.$type;
//     				$file_web = $img_config['img_local_path'].$type;
//     				$fullname = $sku.'.'.$type_v;
//     				$url = '/products/productimage/view1/sku/'.$sku.'/ext/'.urldecode($type_v);//获取下一张图片url
//     				$the_first_zt = UebModel::model('Productimage')->getZtList($sku,0);//获取第一张副图
//     				$imageName = array_keys($the_first_zt);
//     				$the_first_zt = array_shift($the_first_zt);
//     				$local_path = $filepath.'/'.$first.'/'.$second.'/'.$imageName[0].'.jpg';
//     				$filename = $file_web.'/'.$first.'/'.$second.'/'.$fullname;
//     				if(file_exists($local_path)){
//     					$arr_img=array("style"=>"border:1px solid #ccc;padding:2px;","width"=>10,
//     							"height"=>10,'src'=>$the_first_zt, 'pic-link'=>$url);
//     					$arr_href=array('id'=>'product_image_'.$id,'for'=>$sku,'onmouseover'=>'showPreview(event);',
//     							'onmouseout'=>'hidePreview(event);','class'=>'product_image',"title"=>$cnname,);
//     					return CHtml::link($cnname,$url,$arr_href);
//     					break;
//     				}else{
//     					return CHtml::link($cnname,"",array("title"=>$cnname,"style"=>"text-decoration:none;"));
//     					break;
//     				}
//     			}
//     			break;

//     		case 'thumb':
//     			foreach($types as $type_v){
//     				$filepath = $doc_root.$type;
//     				$file_web = $img_config['img_local_path'].$type;
//     				$fullname = $sku.'.'.$type_v;
//     				$local_path = $filepath.'/'.$fullname;   
//     				$filename = $file_web.'/'.$fullname;
//     				if(file_exists($local_path)){
//     					$url = '/products/productimage/view1/sku/'.$sku.'/ext/'.urldecode($type_v);//获取下一张图片url
//     					$the_first_ft = UebModel::model('Productimage')->getFtList($sku,0);//获取第一张副图
//     					$arr_img=array("style"=>"border:1px solid #ccc;padding:2px;","width"=>10,
//     							"height"=>10,'large-src'=>array_shift($the_first_ft), 'pic-link'=>$url);
//     					$arr_href=array('id'=>'product_image_'.$id,'for'=>$sku,'onmouseover'=>'showPreview(event);',
//     							'onmouseout'=>'hidePreview(event);','class'=>'product_image',"title"=>$cnname,"mask"=>1,);
//     					//return CHtml::image(Yii::app()->baseUrl.$filename,$sku,array('id'=>$sku,'class'=>'ajax',
//     					//"style"=>"border:1px solid #ccc;padding:2px;","width"=>80,"height"=>80));
//     					return CHtml::link($cnname,$url,$arr_href);
//     					break;
//     				}else{
//     					return self::getProductPicBySkuToPurchase($sku,'main',$id,$cnname);
//     					break;
//     				}
//     			}
//     			break;
//     		default:
//     			$img_main_path = Yii::getPathOfAlias('webroot').'/upload/image/main'.$child_dir;
//     			$img_ass_path = Yii::getPathOfAlias('webroot').'/upload/image/assistant'.$child_dir;
//     			$img_thumb_path = Yii::getPathOfAlias('webroot').'/upload/image/thumb';
//     			$arr['main'] = self::getPic($img_main_path,$search_qty,$types);
//     			$arr['main'] = self::getPic($img_ass_path,$search_qty,$types);
//     			$arr['main'] = self::getPic($img_thumb_path,$search_qty,$types);
//     	}
//     	return $arr;

    }


    /**
     *  format update field log
     *
     * @param string $label
     * @param string $oldValue
     * @param string $value
     * @return string
     */
    public static function formatUpdateFieldLog($label, $oldValue, $value) {
        return Yii::t('system', '{label} : {oldval} to {val}<br/> ', array(
            'label' => $label, 'oldval' => $oldValue, 'val' => $value));
    }

    /**
     * format insert field log
     *
     * @param string $label
     * @param string $value
     * @return string
     */
    public static function formatInsertFieldLog($label, $value) {
        return Yii::t('system', '{label} : {val}<br/>', array(
            'label' => $label,  'val' => $value));
    }

    /**
     *  format delete log
     *
     * @param string $label
     * @param string $value
     * @return string
     */
    public static function formatDeleteLog($label, $value ) {
        return Yii::t('system', '{label} : {val} delete<br/>', array(
            'label' => $label,  'val' => $value));
    }
    /**
     * @desc 根据当前用户角色获取用户列表
     * @param string or array: $role_code
     * @return multitype:
     */
    public static function getUserByRole($role_code){
        $userList =array();
        if(isset($role_code) && !empty($role_code) && is_string($role_code)){
            $role_code = array($role_code);
        }
        if(User::isAdmin()){
            $roles = $role_code;
            $userList = AuthAssignment::model()->getUlist($roles,1);
        }else{
            //获取当前角色
            $roleL = User::getLoginUserRoles();
            //获取当前角色子角色
            $childRole = AuthItemChild::getChildRoleByParent($roleL);
            if($childRole){//如果存在，则取所有子角色人员
                $roles = array_merge($role_code, $childRole);
                $userList = AuthAssignment::model()->getUlist($roles,1);
            }else{
                $userList = User::getUserNameArrById(Yii::app()->user->id);
            }
        }
        return $userList;
    }


    /**
     * get all users under the role
     * $role_code:string,
     * @return $array
     */
    public static function getUsersByRoleCode($role_code) {
        if(isset($role_code) && !empty($role_code) && is_string($role_code)){
            $roles = array($role_code);
        }elseif(is_array($role_code)){
            $roles = $role_code;
//     		$roleP = $role_code;
//     		$roleL = User::getLoginUserRoles();
//     		$roles = array_intersect($roleP, $roleL);
        }elseif(!isset($role_code) || empty($role_code)){
            $roles = User::getLoginUserRoles();
        }else{}
        $arr = AuthAssignment::model()->getUlist($roles,1);
        return $arr;
    }

    public static function getUsersByDevelopersCode(){
        $roles = array('amazondev','XX','directordev','product_developer','ali_productdeveloper','haiwaicangdev','amazondev','wish_productdevelop','chinapurer','developclerks','developchecker','ebay_groupleader');
        $arr = AuthAssignment::model()->getUlist($roles,1);
        return $arr;
    }


    public static function getUsersByAmazonDevelopersCode(){
    	$roles = array('amazondev','XX');
        $arr = AuthAssignment::model()->getUlist($roles,1);
        return $arr;
    }

    public static function getUsersByEditorialstaff(){
        $roles = array('Copywriter_Commissioner','groupbuying_service');
        $arr = AuthAssignment::model()->getUlist($roles,1);
        return $arr;
    }
    public static function getUsersByqualityertaff(){
        $roles = array('qualitygroupleader','qualityuser');
        $arr = AuthAssignment::model()->getUlist($roles,1);
        return $arr;
    }
    public static function getUsersByPhotographer(){
        $roles = array('photographer','photographer_total');
        $arr = AuthAssignment::model()->getUlist($roles,1);
        return $arr;
    }
    public static function getUsersByRepairphoto(){
        $roles = array('repairphoto');
        $arr = AuthAssignment::model()->getUlist($roles,1);
        return $arr;
    }
    public static function getUsersByMainphoto(){
        $roles = array('mainphoto');
        $arr = AuthAssignment::model()->getUlist($roles,1);
        return $arr;
    }
    
    public static function getSonAuth($parent_name){
    	$arr = AuthItemChild::model()->getChildRoleByParent($parent_name);
    	$result = array();
    	if(!empty($arr)){
    		foreach ($arr as $val){
    			array_push($result, Role::getRoleNameByRoleCode($val));
    		}
    	}
    	return $result;
    }
    
    /**
     * get all users under the role:获取当前登录人所在角色能管理的人员
     * $role_code:string,
     * @return $array
     */
    public static function getUsersByRoleCodeCopy($role_code='') {
        if(isset($role_code) && !empty($role_code)){
            $roleP = array($role_code);
            $roleL = User::getLoginUserRoles();
            $roles = array_intersect($roleP, $roleL);
        }else{
            $roles = User::getLoginUserRoles();
        }
        $arr = AuthAssignment::model()->getUlist($roles,1);
        return $arr;
    }

    /**
     * get table names config
     */
    public static function getTableNamesConfig() {
        static $result = array();
        if ( empty($result) ) {
            $dbNamesConfig = Configuration::getDbNamesConfig();
            foreach ($dbNamesConfig as $key => $val) {
                $tableNames = self::getTableNamesByDbKey($key);
                $result[$key] = $tableNames;
            }
        }

        return $result;
    }

    /**
     * get table names by db key
     * @param type $dbKey
     * @return array
     */
    public static function getTableNamesByDbKey($dbKey) {
        return Yii::app()->getComponent($dbKey)->schema->getTableNames();
    }

    /**
     * get model by table namne
     *
     * @param string $tableName
     * @return type
     */
    public static function getModelByTableName($tableName) {
        $className = self::getModelNameByTableName($tableName);
        return UebModel::model($className);
    }

    /**
     * get model name by table name
     *
     * @param string $tableName
     * @return string
     */
    public static function getModelNameByTableName($tableName) {
        $className = "";
        $tableModelMap = Yii::app()->params['tableToModel'];
        if ( strpos($tableName, ".") !== false ) {//ueb_product.ueb_product
            $dbNameAndTableName = explode('.', $tableName);
            $tableName = $dbNameAndTableName[1];//clear db name
        }

        if ( ! empty($tableModelMap) && isset($tableModelMap[$tableName])) {
            $className = $tableModelMap[$tableName];
        } else {
            if ( strpos($tableName, "_") !== false ) {
                $arr = explode("_", $tableName);
                array_shift($arr);
                foreach ( $arr as $val ) {
                    $className .= ucfirst($val);
                }
            } else {
                $className = ucfirst($tableName);
            }
        }

        return $className;
    }


    /**
     * get columns pairs by table name
     *
     * @param string $tableName
     * @return type
     */
    public static function getColumnsPairsByTableName($tableName) {
        $result = array();
        $model = self::getModelByTableName($tableName);
        $columnsArr = $model->getMetaData()->columns;
        foreach ($columnsArr as $column => $columnObj) {
            $result[$column] = empty($columnObj->comment) ? $column : $columnObj->comment;
        }

        return $result;
    }
    /**
     * get columns List by table name
     *
     * @param string $tableName
     * @return array('id','name'...);
     */
    public static function getColumnsArrByTableName($tableName) {
        $result = array();
        $model = self::getModelByTableName($tableName);
        $columnsArr = $model->getMetaData()->columns;
        foreach ($columnsArr as $column => $columnObj) {
            $result[] = $column;
        }

        return $result;
    }

    /**
     * create key - value
     *
     * @param  object $list
     * @param string $key
     * @param string $value
     * @return array $result
     */
    public static function createKeyValue($list, $key, $value = ''){
        if(! $value){
            $value = $key;
        }

        $result = array();
        foreach($list as $val){
            $result[$val[$key]] = $val[$value];
        }

        return $result;
    }

    /**
     * create value group
     *
     * @param object $list
     * @param array $keys
     * @return array $result
     */
    public static function createValueGroup($list, $keys = array()) {
        $result = array();
        foreach($list as $val){
            foreach ($keys as $key) {
                $result[$key][] = $val[$key];
            }
        }

        return $result;
    }

    /*
     * get the days between date
     * @$fromDate:2012-8-8 12:12:12
     * @$toDate:2013-8-8 12:12:12
     * return $days:int
     */
    public static function getDayFromDateToDate($fromDate,$toDate){
        $fromTime =  self::getYmdFromYmdHis($fromDate);
        $toTime =  self::getYmdFromYmdHis($toDate);
        $unix = $fromTime - $toTime;
        $days = $unix/86400;
        return $days;
    }
    /*
     * get Y-m-d from Y-m-d H:i:s
     * @$date:2013-11-18 9:45:45
     * return timestamp:  strtotime('2013-11-18')
     */
    public static function getYmdFromYmdHis($date){
        $unix = strtotime($date);
        $date = date("Y-m-d",$unix);
        return strtotime($date);
    }
    /*
     * array to object
     * @param array() $e
     * return object
     */
    public static function arrayToObject($e){
        if( gettype($e)!='array' ) return;
        foreach($e as $k=>$v){
            if( gettype($v)=='array' || getType($v)=='object' )
                $e[$k]=(object)arrayToObject($v);
        }
        return (object)$e;
    }


    /*
     * object to array
    * @param: object $e
    * return array
    */
    public static function objectToArray($e){
        $e=(array)$e;
        foreach($e as $k=>$v){
            if( gettype($v)=='resource' ) return;
            if( gettype($v)=='object' || gettype($v)=='array' )

                $e[$k]=(array)self::objectToArray($v);
        }
        return $e;
    }

    public static function getCurrentHour() {
        return substr(date('Y-m-d H:i:s'), 11, 2);
    }


    /**
     * @desc 亚马逊sku加密
     * 规则： 原sku：2422.02 加密： 2fd4hg2ee2D02
     *      '.'前面每个数字中间插两位随机小写字母， 如果.前面一位是字母则前面不加随机
     *      '.'换成大写D
     *      '.'后面不变
     * @param  string $sku
     * @return string $encryptSku
     * @since 2015-03-31
     * @author Super
     *
     */
    public static function getAmazonEncryptSku($sku)
    {
        $encryptSku = '';//加密后的sku
        $sku = trim($sku);
        $rand = 1;//插入两位
        $len =strlen($sku);
        $sku_lastOne = substr($sku,$len-1,1);//截取sku最后一位数
        for($i = 0;$i<$len-1;$i++)
        {
            $encryptSku.= substr($sku,$i,1);
            $encryptSku.= self::getRandomChars($rand);
        }
        $encryptSku.= $sku_lastOne;
        if($back_sku)
        {
            $encryptSku.= D.$back_sku;//小数点用D代替
        }
        return $encryptSku;
    }

    /**
     * @desc 亚马逊sku解密
     * 规则： 原sku：2fd4hg2ee2D02 加密： 2422.02
     * 		去掉数字之间的小写字母
     *      大写D换成'.'
     * @param  string $sku
     * @return string $encryptSku
     *
     */

    function getAmazonRealSku($encryptSku){
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
            $encryptSku = $sku;
            $sku = '';

            $len = strlen($encryptSku);
            for($i = 0; $i<$len;$i++){
                $str = substr($encryptSku,$i,1);
                $sku.=$str;
            }
            if($back_sku!=''){
                $sku.= $back_sku;
            }
        }elseif(strpos($encryptSku,".") !== false){
            return $encryptSku;
        }else{
            $sku = '';


            $len = strlen($encryptSku);
            for($i = 0; $i<$len;$i++){
                $str = substr($encryptSku,$i,1);
                if(preg_match('/\d/',$str)){
                    $sku.=$str;
                }
            }
            $last_ascll=ord(substr($encryptSku,$len-($len+1),1));//如果加密后的 最后一个字符是 英文字母  说明这个sku 本身最后一个就是字母
            if(($last_ascll >= ord('A') && $last_ascll <= ord('Z')) || ($last_ascll >= ord('a') && $last_ascll <= ord('z'))){
                $sku.=substr($encryptSku,$len-($len+1),1);
            }
            if($back_sku!=''){
                $sku.= $back_sku;
            }
        }
        if(mb_substr_count($sku,' ')){
            $sku = str_replace(' ','', $sku);
        }
        return $sku;
    }
    /**
     *
     * @param int $length sku加密随机长度
     * @return string
     */
    public static function getRandomChars($length){
        $chars = '';
        while($length--){
            $chars .= self::getUpperChar();
        }
        return $chars;
    }

    /**
     * @desc 随机获取大写字母
     * @return string $chr
     * @author Super
     */
    public static function getUpperChar(){
        $rand = rand(65,90);
        $chr = chr($rand);
        if(in_array($chr,array('I','O'))){
            return self::getUpperChar();
        }else{
            return $chr;
        }
    }




    public static function getRealSku($encryptSku){
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


    /**
     *
     * get one or more fields by SearchCondition
     * @param mixed(Array/String) $fields
     */
    public static function getFieldsBySearchCondition($fields){
        if($fields!=null){
            $fields_str = '';
            if(is_array($fields)){
                $fields_str = '';
                foreach($fields_str as $value){
                    $fields_str .= $value.',';
                }
                $fields = trim($fields_str);
            }
        }else{
            $fields = '*';
        }
        return $fields;
    }
    public static function runThread($url,$hostname='',$port=80) {
        if(!$hostname){
            $hostname=$_SERVER['HTTP_HOST'];
        }
        $fp=fsockopen($hostname,$port,$errno,$errstr,600);
        if (!$fp)
        {
            echo "$errstr ($errno)<br />\n";
            return;
        }
        fputs($fp,"GET ".$url."\r\n");
		while (!feof($fp)){
			echo fgets($fp,2048);
		}

        fclose($fp);
    }
    public static function runThreads($url,$hostname='',$port=80) {
        if(!$hostname){
            $hostname=$_SERVER['HTTP_HOST'];
        }
        $fp=fsockopen($hostname,$port,$errno,$errstr,600);
        if (!$fp)
        {
            echo "$errstr ($errno)<br />\n";
            return;
        }
        fputs($fp,"GET ".$url."\r\n");
        while (!feof($fp)){
            echo fgets($fp,2048);
        }

        fclose($fp);
    }

    /**
     * 欠货统计报表新取老
     * @param unknown $url
     * @param string $hostname
     * @param number $port
     */
    public function oldPendingAll($url,$hostname='',$port=80) {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);//.'?type=update'

        curl_setopt($curl, CURLOPT_HEADER, 0);//不显示文件头，1显示
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//把输出转化为字符串，而不是直接输出到屏幕
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,array('order_info' => json_encode(array('own_day' => 'desc')))); // Post提交的数据包

        $result = curl_exec($curl);

        curl_close($curl);
//		
//		if(!$hostname){
//			$hostname=$_SERVER['HTTP_HOST'];
//		};
//		$fp=fsockopen($hostname,$port,&$errno,&$errstr,600);
//		if (!$fp)
//		{
//			echo "$errstr ($errno)<br />\n";
//			return;
//		}
//		fputs($fp,"GET ".$url."\r\n");
//		$result=fgets($fp); 		 
//		fclose($fp);

        return $result;
    }

    public static function runThreadSOCKET($urls,$hostname='',$port=80) {
        if(!$hostname){
            $hostname=$_SERVER['HTTP_HOST'];
        }
        if(!is_array($urls)){
            $urls = (array)$urls;
        }
        foreach ($urls as $url) {
            var_dump($hostname);
            var_dump($url);
            $fp=fsockopen($hostname, $port,  $errno, $errstr, 18000);
            stream_set_blocking ( $fp, true );
            stream_set_timeout ( $fp, 18000 );
            fputs($fp,"GET ".$url." HTTP/1.1\r\n");
            fputs($fp,"Host: ".$hostname."\r\n\r\n");
            fclose($fp);
        }
    }

    //copy array
    public static function copyArray($array,$copy_keys,&$newArray) {

        !is_array($copy_keys) && $copy_keys = explode(',',$copy_keys);

        foreach ($copy_keys as $key){
            $newArray[$key] = $array[$key];
        }
    }
    /**
     *
     * make a time to formate of Greenwich
     * @param Int $time
     */
    public static function getGreenwichTime($time=''){
        if(!$time){
            $time = time();
        }
        return $time - 28800;
    }

    /**
     * get format log
     * @param $log
     * @return array
     */
    public static function formatLog($log){
        // {Model:Att} {oldVal} to {newVal}

        $key = $oldVal = $newVal = '';
        preg_match_all("/{([^}]*)}/", $log, $matches);
        if (isset($matches[1][0])){
            $key = isset($matches[1][0])  ? $matches[1][0] : '';
            $oldVal = isset($matches[1][1]) ? $matches[1][1] : '';
            $newVal = isset($matches[1][2]) ? $matches[1][2] : '';

            $modelName = strstr($key,':',true);
            $att   = substr(strstr($key,':'),1);


            if (class_exists(ucfirst($modelName))) {
                $modelObj = UebModel::model($modelName);
                $log = str_replace(
                    array('{','}',$modelName,$att,' to '),
                    array(
                        '',
                        '',
                        '<font color=blue>'. $modelObj->getAttributeLabel($modelName).'</font>',
                        '<font color=green>'.$modelObj->getAttributeLabel($att).'</font>',
                        ' '.Yii::t('orderlog', 'to').' ',
                    ),
                    $log
                );

                if (method_exists($modelObj, 'replaceLogMsg')) {
                    $replaceArr = $modelObj->replaceLogMsg($att,$oldVal,$newVal);
                    if (!empty($replaceArr)) {
                        $log = str_replace($replaceArr[0],$replaceArr[1], $log);
                    }
                }
            }
        }

        return $log;
    }

    public static  function getNowTime(){
        return date('Y-m-d H:i:s');
    }


    /**
     * add by Tom 2014-02-18
     * 从源对象中复制若干Key，形成新的对象
     * @param Ojbect $sourceObj
     * @param String $keys
     * @retrun Object
     */
    public  static function copyObject($sourceObj,$targetKeys,$scoureKeys=''){
        $targetObj = null;
        !is_array($targetKeys) && $targetKeys = explode(',',$targetKeys);
        if(!empty($scoureKeys)){
            !is_array($scoureKeys) && $scoureKeys = explode(',',$scoureKeys);
        }

        foreach ($targetKeys as $key=>$value){

            //echo $value.'--'.$sourceObj->$scoureKeys[$key].'<br/>';
            $targetObj->$value = $sourceObj->$scoureKeys[$key];
        }
        return $targetObj;
    }

    public static function simplode($ids) {
        return "'".implode("','", $ids)."'";
    }
    /**
     *
     * add By Tom 2014-02-21
     * @param mix $source
     * @param String $keyword
     */
    public static function newArrayByKey($source,$keyword){
        $newArray = array();
        foreach($source as $key=>$value){

            if(is_array($value)){
                $newArray[] = $value[$keyword];
            }elseif(is_object($value)){
                $newArray[] = $value->$keyword;
            }

        }
        if($newArray) $newArray = array_unique($newArray);
        return $newArray;
    }

    /**
     * 获取服务器IP地址
     * add by Tom 2014-02-24
     */
    public static function getSysHostName(){
        return 'http://localhost';
    }

    /**
     *
     * 功能:将正常的时间转成速卖通请求的时间格式
     * 例子: 2014-02-24 00:00:00 将转成 02/24/2014 00:00:00
     * @param date $time
     */
    public static function formateAliexpressTime($time){
        $tmp = explode(' ', $time);
        $data = explode('-', $tmp[0]);
        $time = $data[1] . '/' . $data[2] . '/' . $data[0];
        return $time.' '.$tmp[1];

    }

    /**
     * add By Tom 2014-02-24
     * 功能 将一个时间戮格式的变量转化成正常的日期格式
     */
    public static function getDateFormateByUnixTime($time){
        return date('Y-m-d H:i:s',$time);
    }

    public static function getDbNameByModelName($modeName){
        $dbkey = UebModel::model($modelName)->getDbKey();
        $env = new Env();

        return $env->getDbNameByDbKey($dbkey);
    }
    /**
     *
     */
    public static function getDateFormat($timeType){
        $format = '';
        switch ($timeType){
            case ExcelSchemeColumn::_MONTH:
                $format = '%Y/%m';
                break;
            case ExcelSchemeColumn::_DAY:
                $format = '%Y/%m/%d';
                break;
            case ExcelSchemeColumn::_WEEK:
                $format = '%w';
                break;
            case ExcelSchemeColumn::_YEAR:
                $format = '%Y/%m';
                break;
            case ExcelSchemeColumn::_HOUR:
                $format = '%Y/%m/%d %H';
                break;
            default:
                $format = '%Y/%m';
        }
        return $format;
    }

    /**
     *
     * const _DAY = 1;
    const _WEEK = 2;
    const _MONTH = 3;
    const _YEAR = 4;
     */
    public static function getDateDiff( $timeType = ExcelSchemeColumn::_MONTH){
        $date = array();
        switch ($timeType){
            case ExcelSchemeColumn::_DAY:
                $time_s=mktime(0,0,0,date('m'),date('d'),date('Y'));
                $time_e=mktime(23,59,59,date('m'),date('d'),date('Y'));
                $date[] = date('Y-m-d H:i:s',$time_s);
                $date[] = date('Y-m-d H:i:s',$time_e);
                break;
            case ExcelSchemeColumn::_WEEK:
                $date[] = date("Y-m-d 00:00:00",strtotime("last week"));
                $date[] = date("Y-m-d 23:59:59",strtotime("this week"));;
                break;
            case ExcelSchemeColumn::_MONTH:
                $time_s=mktime(0,0,0,date('m'),1,date('Y'));
                $time_e=mktime(23,59,59,date('m'),date('t'),date('Y'));

                $date[] = date('Y-m-d H:i:s',$time_s);
                $date[] = date('Y-m-d H:i:s',$time_e);
                break;
            case ExcelSchemeColumn::_YEAR:
                $time_s=mktime(0,0,0,1,1,date('Y')-1);

                $date[] = date('Y-m-d 00:00:00',$time_s);
                $date[] = date('Y-m-d H:i:s',time());

                break;
            default:

        }
        return $date;
    }

    public static function getWeek($day=null){
        $config = array(
            1 => Yii::t('common','Monday'),
            2 => Yii::t('common','Tuesday'),
            3 => Yii::t('common','Wednesday'),
            4 => Yii::t('common','Thursday'),
            5 => Yii::t('common','Friday'),
            6 => Yii::t('common','Satursday'),
            0 => Yii::t('common','Sunday'),
        );
        if ($day !== null) return $config[$day];
        return $config;
    }

    public static function microtime_float(){
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * 更新新系统在途后，接着api更改老系统在途
     * @param string $sku
     * @param number $warehouseId
     * @param int $change_qty
     * @param int $change_type：默认0:表示需在原来上面添加; 1表示完全覆盖更改
     */
    public static function api_update_online_qty_to_old($sku,$warehouseId=1,$change_qty,$change_type=0){
        //调用api去更改老系统在途数
        $url = Yii::app()->request->hostInfo.'/newtooldmain/update_to_old.php?ac=online&sku='.$sku.'&warehouse_id='.$warehouseId.'&change_qty='.$change_qty.'&change_type='.$change_type;
        MHelper::runThread($url);
    }

    /**
     * 删除新系统采购明细后，接着api删除老系统明细
     * @param string $sku
     * @param string $pur_code
     */
    public static function api_delete_purchase_sku_to_old($pur_code,$sku){
        //调用api去删除老系统明细
        $url = Yii::app()->request->hostInfo.'/newtooldmain/update_to_old.php?ac=deletesku&sku='.$sku.'&pur_code='.$pur_code;
        MHelper::runThread($url);
    }


    /**
     * 删除新系统采购明细后，接着api删除老系统明细(特殊处理，不检测请款/付款状态，仅用于删除新系统采购详情成功api删除老系统采购详情失败，然后又从老系统同步过来的采购单)
     * @param string $sku
     * @param string $pur_code
     */
    public static function api_delete_purchase_detail_to_old($pur_code,$sku){
        //调用api去删除老系统明细
        $url = Yii::app()->request->hostInfo.'/newtooldmain/update_to_old.php?ac=deleteskunotchecknewsystem&sku='.$sku.'&pur_code='.$pur_code;
        MHelper::runThread($url);
    }


    /**
     * get user_full_name
     * @return $data
     */
    public static function getUserIdByUserFullName($userName='') {
        static $data = array();
        if ( empty($data) ) {
            $list = Yii::app()->db->createCommand()
                ->select('id,user_name,user_full_name')
                ->from(User::model()->tableName())
                ->order("id asc")
                ->queryAll();
            $data =array();
            foreach($list as $key=>$val){
                $data[$val['user_full_name']] =$val['id'];
            }
        }
        if(!empty($userName)) {
            return $data[$userName];
        }
        return $data;

    }

    /**
     * $method：方法名
     * $data=array(
     * 'purchase_order_no'=> '采购单号',
     * 'warehouse_id'     => '仓库id',
     * 'package_id'       => '包裹号',
     * )
     * @param unknown $method
     * @param unknown $data
     */
    public static function cancelStock($method,$data=array('purchase_order_no'=>'','warehouse_id'=>'','package_id'=>'')){
        $model=new Wmsapiset();
        $wmsConfigs=$model->getConfig();
        $urls=$wmsConfigs['erp_url'];//'http://vakind.f3322.org:8089/api/index';
        $param = array('col' => json_encode(array('client' => 'wms','key' => $wmsConfigs['wms_key'],'method' => $method,'data' => array(
            'purchase_order_no'=> $data['purchase_order_no'],
            'warehouse_id'     => $data['warehouse_id'],
            'package_id'       => $data['package_id'],
// 			'OrderShippedtime'=>$data['OrderShippedtime'],
        ))));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urls);//.'?type=update'
        curl_setopt($curl, CURLOPT_HEADER,0);//不显示文件头，1显示
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);//把输出转化为字符串，而不是直接输出到屏幕
        curl_setopt($curl, CURLOPT_POST,1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,$param); // Post提交的数据包
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result);

    }

    /**
     * 取消退款同步删除老系统退款信息
     * @param unknown $url
     * @param string $hostname
     * @param number $port
     */
    public function deleteCancelRefund($url,$hostname='',$port=80) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);//不显示文件头，1显示
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//把输出转化为字符串，而不是直接输出到屏幕
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,array()); // Post提交的数据包
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public static function writeLog($content, $filename) {
        $day = date('d');
// 		if ($day<=10) {
// 			$day = '1-10';
// 		}elseif ($day<=20) {
// 			$day = '11-20';
// 		}else {
// 			$day = '21-30';
// 		}
        $file2 = date('Ym').'_'.$day;
        error_log(date('Y-m-d H:i:s')."\r\n".$content."\r\n", 3, 'log/'.str_replace('.txt', '', $filename).'_'.$file2.'.txt');
    }

    public static function getCountrySimpleCode(){
        $country_abbr_list = Array
        (
            'AF' => 'Afghanistan',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'VG' => 'British Virgin Islands',
            'BN' => 'Brunei',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos [Keeling] Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo - Brazzaville',
            'CD' => 'Congo - Kinshasa',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'CI' => 'Côte d’Ivoire',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island and McDonald Islands',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong SAR China',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Laos',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macau SAR China',
            'MK' => 'Macedonia',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'FM' => 'Micronesia',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar [Burma]',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'AN' => 'Netherlands Antilles',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'KP' => 'North Korea',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territories',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn Islands',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'RE' => 'Réunion',
            'BL' => 'Saint Barthélemy',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin',
            'PM' => 'Saint Pierre and Miquelon',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'KR' => 'South Korea',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard and Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syria',
            'ST' => 'São Tomé and Príncipe',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'UM' => 'U.S. Minor Outlying Islands',
            'VI' => 'U.S. Virgin Islands',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VA' => 'Vatican City',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'WF' => 'Wallis and Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
            'AX' => 'Åland Islands',
        );
    }

    public  function getlistdir($dir){
        $result = array();
        if (is_dir($dir)){
            $file_dir = scandir($dir);
            foreach($file_dir as $file){
                if ($file == '.' || $file == '..'){
                    continue;
                }
				elseif (is_dir($dir.$file)){
                    $result = array_merge($result, $this->getlistdir($dir.$file.'/'));
                }
                else{
                    array_push($result, $dir.$file);
                }
            }
        }
        return $result;
    }

    public  function addFileToZip($path,$zip){
        $handler=opendir($path); //打开当前文件夹由$path指定。

        while(($filename=readdir($handler))!==false){

            if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作

                if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                    $this->addFileToZip($path."/".$filename, $zip);
                }else{ //将文件加入zip对象
                    $zip->addFile($path."/".$filename,$path."/".$filename);
                }
            }
        }
        @closedir($path);
    }

    /**
     * 图片加水印（适用于png/jpg/gif格式）
     *
     * @author flynetcn
     *
     * @param $srcImg 原图片
     * @param $waterImg 水印图片
     * @param $savepath 保存路径
     * @param $savename 保存名字
     * @param $positon 水印位置
     * 1:顶部居左, 2:顶部居右, 3:居中, 4:底部局左, 5:底部居右
     * @param $alpha 透明度 -- 0:完全透明, 100:完全不透明
     *
     * @return 成功 -- 加水印后的新图片地址
     *          失败 -- -1:原文件不存在, -2:水印图片不存在, -3:原文件图像对象建立失败
     *          -4:水印文件图像对象建立失败 -5:加水印后的新图片保存失败
     */
    public static function img_water_mark($srcImg, $waterImg, $savepath=null, $savename=null, $positon=5, $alpha=30){
        $temp = pathinfo($srcImg);
        $name = $temp['basename'];
        $path = $temp['dirname'];
        $exte = $temp['extension'];
        $savename = $savename ? $savename : $name;
        $savepath = $savepath ? $savepath : $path;
        $savefile = $savepath .'/'. $savename;
        $srcinfo = @getimagesize($srcImg);
        if (!$srcinfo) {
            return -1; //原文件不存在
        }
        $waterinfo = @getimagesize($waterImg);
        if (!$waterinfo) {
            return -2; //水印图片不存在
        }
        $srcImgObj = MHelper::image_create_from_ext($srcImg);

        if (!$srcImgObj) {
            return -3; //原文件图像对象建立失败
        }
        $waterImgObj = MHelper::image_create_from_ext($waterImg);
        if (!$waterImgObj) {
            return -4; //水印文件图像对象建立失败
        }
        switch ($positon) {
            //1顶部居左
            case 1: $x=$y=0; break;
            //2顶部居右
            case 2: $x = $srcinfo[0]-$waterinfo[0]; $y = 0; break;
            //3居中
            case 3: $x = ($srcinfo[0]-$waterinfo[0])/2; $y = ($srcinfo[1]-$waterinfo[1])/2; break;
            //4底部居左
            case 4: $x = 0; $y = $srcinfo[1]-$waterinfo[1]; break;
            //5底部居右
            case 5: $x = $srcinfo[0]-$waterinfo[0]; $y = $srcinfo[1]-$waterinfo[1]; break;
            default: $x=$y=0;
        }

        imagecopymerge($srcImgObj, $waterImgObj, $x, $y, 0, 0, $waterinfo[0], $waterinfo[1], $alpha);
        switch ($srcinfo[2]) {
            case 1: imagegif($srcImgObj, $savefile); break;
            case 2: imagejpeg($srcImgObj, $savefile); break;
            case 3: imagepng($srcImgObj, $savefile); break;
            default: return -5; //保存失败
        }
        imagedestroy($srcImgObj);
        imagedestroy($waterImgObj);
        return $savefile;
    }


    public static function image_create_from_ext($imgfile){
        $info = getimagesize($imgfile);
        $im = null;
        switch ($info[2]) {
            case 1: $im=imagecreatefromgif($imgfile); break;
            case 2: $im=imagecreatefromjpeg($imgfile); break;
            case 3: $im=imagecreatefrompng($imgfile); break;
        }
        return $im;
    }


    public static function watermark($source, $water,$savepath,$savename,$posX=0,$posY=0,$alpha=30,$newWidth= null,$newHeight = null) {
        //检查文件是否存在
        if (!file_exists($source) || !file_exists($water)){
            return false;
        }
        $temp = pathinfo($source);
        $name = $temp['basename'];
        $path = $temp['dirname'];
        $exte = $temp['extension'];
        $savename = $savename ? $savename : $name;
        $savepath = $savepath ? $savepath : $path;
        $savefile = $savepath .'/'. $savename;
        $srcinfo = @getimagesize($source);
        //图片信息
        $sInfo = MHelper::getImageInfo($source);
        $wInfo = MHelper::getImageInfo($water);

        //如果图片小于水印图片，不生成图片
        if ($sInfo["width"] < $wInfo["width"] || $sInfo['height'] < $wInfo['height'])
            return false;

        if ( $sInfo["width"] > $wInfo["width"] || $sInfo['height'] > $wInfo['height'] ) {

            $bigPath = MHelper::getBigImagePath($water);
            if (! is_file($bigPath) ) {
                MHelper::changeImageSize($water, $wInfo['type'], 800, 800, $bigPath);
            }
            $wInfo["width"] = $sInfo["width"];
            $wInfo['height'] = $sInfo["height"];
            $water = $bigPath;
        }

        //建立图像
        $sCreateFun = "imagecreatefrom" . $sInfo['type'];
        $sImage = $sCreateFun($source);
        $wCreateFun = "imagecreatefrom" . $wInfo['type'];
        $wImage = $wCreateFun($water);

        //设定图像的混色模式
        imagealphablending($wImage, true);

        //图像位置,默认为右下角右对齐
        !$posY && $posY = $sInfo["height"] - $wInfo["height"];
        !$posX && $posX = $sInfo["width"] - $wInfo["width"];

        //生成混合图像
        imagecopymerge($sImage, $wImage, $posX, $posY, 0, 0, $wInfo['width'], $wInfo['height'], $alpha);

        //输出图像
        $ImageFun = 'Image' . $sInfo['type'];
        //如果没有给出保存文件名，默认为原图像名
        if (!$savename) {
            $savename = $source;
            @unlink($source);
        }
        //保存图像

        $ImageFun($sImage, $savefile);
        imagedestroy($sImage);
        //MHelper::changeImageSize($savename, $sInfo['type'], $sInfo['width'], $sInfo['height'],$savefile);
        return $savefile;
    }

    public static function changeImageSize($sourcePath, $type, $width, $height, $targetPath = null) {
        switch ($type) {
            case 'jpeg':
            case 'jpg':
                $temp_img = imagecreatefromjpeg($sourcePath);
                $o_width = imagesx($temp_img);
                $o_height = imagesy($temp_img);
                $new_img = imagecreatetruecolor($width, $height);
                imagecopyresampled($new_img, $temp_img, 0, 0, 0, 0, $width, $height, $o_width, $o_height);
                $filePath = empty($targetPath) ?  $sourcePath : $targetPath;
                imagejpeg($new_img , $filePath);
                imagedestroy($new_img);
                break;
            case 'gif':
                $temp_img = imagecreatefromgif($sourcePath);
                $o_width = imagesx($temp_img);
                $o_height = imagesy($temp_img);
                $new_img = imagecreatetruecolor($width, $height);
                imagecopyresampled($new_img, $temp_img, 0, 0, 0, 0, $width, $height, $o_width, $o_height);
                $filePath = empty($targetPath) ?  $sourcePath : $targetPath;
                imagegif($new_img , $filePath);
                imagedestroy($new_img);
                break;
            case 'png':
                $temp_img = imagecreatefrompng($sourcePath);
                $o_width = imagesx($temp_img);
                $o_height = imagesy($temp_img);
                $new_img = imagecreatetruecolor($width, $height);
                /*$c = imagecolorallocatealpha($new_img , 0 , 0 , 0 ,127);
                imagealphablending($new_img ,false);
                imagefill($new_img , 0 , 0, $c);
                imagesavealpha($new_img ,true);*/
                $white= imagecolorallocate($new_img , 255 , 255 ,255);
                imagefill($new_img , 0 , 0, $white);
                imagecolortransparent($new_img ,$white);
                imagecopyresampled($new_img, $temp_img, 0, 0, 0, 0, $width, $height, $o_width, $o_height);
                $filePath = empty($targetPath) ?  $sourcePath : $targetPath;
                imagepng($new_img , $filePath);
                imagedestroy($new_img);
                break;
        }

    }
    public static function getImageInfo($img) {
        $imageInfo = getimagesize($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($img);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
            return $info;
        } else {
            return false;
        }
    }
    public static function getBigImagePath($filePath) {
        if ( strpos($filePath, "/") === false ||
            strpos($filePath, ".") === false) {
            throw new Exception('图片路径不正确');
        }
        $pathArr = explode("/", $filePath);
        $imageName = explode('.', array_pop($pathArr));
        $imageName = $imageName[0].'-big.'.$imageName[1];
        $pathArr[] = $imageName;
        $path = implode("/", $pathArr);
        return  $path;
    }
    /**
     * PHP发送Json对象数据
     *
     * @param $url 请求url
     * @param $jsonStr 发送的json字符串
     * @return array
     */
    public static function http_post_json($url, $jsonStr)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return array($httpCode, $response);
    }
    static function curlPost1($url,$post_data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        //返回获得的数据
        return $output;
    }
    static function curlPost($url,$post_data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        //返回获得的数据
        return $output;
    }

    /**
     * PHP发送Json对象数据
     *
     * @param $url 请求url
     * @param $jsonStr 发送的json字符串
     * @return array
     */
    public static function http_post_json_cont($url, $jsonStr, $token, $name)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $name . '=' . $jsonStr . '&token='.$token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;




    }
    /**
     * PHP发送Json对象数据
     *	作用于mbb
     * @param $url 请求url
     * @param $jsonStr 发送的json字符串
     * @return array
     */
    public static function http_post_json_mbb($url, $jsonStr,$token=null,$company=null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr),
                'Authorization-Operator:'.$token,
                'Authorization-Company:'.$company,
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return array($httpCode, $response);
    }
    /**
     * PHP发送Json对象数据
     *	作用于mbb
     * @param $url 请求url
     *
     * @return array
     */
    public static function http_get_json_mbb($url,$token=null,$company=null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Authorization-Operator:'.$token,
                'Authorization-Company:'.$company,
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return array($httpCode, $response);
    }

    /***
     * @param $token
     * @param $actionValue
     * @param $app_keyValue
     * @param $dataValue
     * @param $formatValue
     * @param $platformValue
     * @param $sign_methodValue
     * @param $timestampValue
     * @param $version
     * @return 构造签名
     */
    public static function  signature($token,$actionValue,$app_keyValue,$dataValue,$formatValue,$platformValue,$sign_methodValue,$timestampValue,$version)
    {
        //方法名
        $action      = 'action'.$actionValue;
        //用户名
        $app_key     = 'app_key'.$app_keyValue;
        //业务参数
        $data        = 'data'.json_encode($dataValue,JSON_UNESCAPED_UNICODE);
        //格式
        $format      = 'format'.$formatValue;
        //平台
        $platform 	 = 'platform'.$platformValue;
        //签名方式
        $sign_method = 'sign_method'.$sign_methodValue;
        //时间
        $timestamp   = 'timestamp'.$timestampValue;
        //版本
        $version 	 = 'version'.$version;

        $sig 		 = $token.$action.$app_key.$data.$format.$platform.$sign_method.$timestamp.$version.$token;

        return strtoupper(md5($sig));



    }
    /**
     * 从mongodb中取拉取订单上数据
     */
    public static function get_order_data_from_mongo($url,$data=null,$type='POST',$headers=array(),$data_type='')
    {
        $ch = curl_init();

        //判断ssl连接方式  
        if (stripos($url, 'https://') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);   
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        }

        list($connttime,$timeout,$querystring) = array(300,15000,"");//连接等待时间500毫秒//超时时间15秒

        //构造请求参数
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $val2) {
                        $querystring .= urlencode($key).'='.urlencode($val2).'&';
                    }
                } else {
                    $querystring .= urlencode($key).'='.urlencode($val).'&';
                }
            }
            $querystring = substr($querystring, 0, -1);
        } else {
            $querystring = $data;
        }

        curl_setopt ($ch, CURLOPT_URL, $url); //请求地址 

        //设置头部信息
        //$headers[] = "CLIENT-IP:61.144.244.173";
        //$headers[] = "X-FORWARDED-FOR:61.144.244.173";

        if (!empty($headers)) {
            curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
        }


        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);//反馈信息  
        curl_setopt ($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); //http 1.1版本  
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT_MS, $connttime);//连接等待时间  
        curl_setopt ($ch, CURLOPT_TIMEOUT_MS, $timeout);//超时时间  

        switch ($type) {
            case "GET":
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
                break;
            case "PUT":
                curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
                break;
            case "DELETE":
                curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
                break;
        }

        $file_contents = curl_exec($ch);//获得返回值 
        //var_dump($file_contents);die();
        //$status = curl_getinfo($ch);  
        //var_dump($status);
        curl_close($ch);
        return  $file_contents;
    }
    /**
     * 字符串截取
     * @param $string
     * @param $length
     * @param string $etc
     * @return string
     */
    public static function utf8_cut($string, $length, $etc = '...')
    {
        $result = '';
        $string = html_entity_decode(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
        $strlen = strlen($string);
        for ($i = 0; (($i < $strlen) && ($length > 0)); $i++)
        {
            if ($number = strpos(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0'))
            {
                if ($length < 1.0)
                {
                    break;
                }
                $result .= substr($string, $i, $number);
                $length -= 1.0;
                $i += $number - 1;
            }
            else
            {
                $result .= substr($string, $i, 1);
                $length -= 0.5;
            }
        }
        $result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
        if ($i < $strlen)
        {
            $result .= $etc;
        }
        return $result;
    }

    public static function htmltoolBar($toolBar){
        foreach ($toolBar as $item) {
            $urls = $item['url'];
            if(empty($urls)){
                $urls = $item['htmlOptions']['urlname'];
            }
            if(strstr($urls, 'javascript:')){
                $urls = $item['htmlOptions']['urlname'];
            }

            //$urls = substr($urls,0,strlen($urls)-1);
            if(!UebModel::model('user')->isAdmin()){
                $urlarr = array();
                $urlarr = explode('/',$urls);
                $newurl = '/'.$urlarr[1].'/'.$urlarr[2].'/'.$urlarr[3];
                if(!Menu::model()->exists("menu_url = '$newurl'")){
                    continue;
                }
            }

            if( isset( $item['items'] ) ){//Drop List
                echo '<li>';
                if( isset($item['title']) ){
                    $dropDownArr = array(
                        '' => $item['title'],
                    );
                }else{
                    $dropDownArr = array();
                }
                $i = 0;
                $aHidden = '';
                foreach( $item['items'] as $itm ){
                    $i++;
                    $action = '';
                    if( !isset($itm['htmlOptions']) ){
                        $itm['htmlOptions'] = array();
                    }
                    if( isset($itm['actionUrl']) ){
                        $action = '$(this).parent().find(\'a.action_hidden'.$i.'\').click()';
                        $itm['htmlOptions']['style'] = isset($itm['htmlOptions']['style']) ? $itm['htmlOptions']['style'].'display:none;' : 'display:none;';
                        $itm['htmlOptions']['class'] = isset($itm['htmlOptions']['class']) ? $itm['htmlOptions']['class'].' action_hidden'.$i : 'action_hidden'.$i;
                        $aHidden .= CHtml::link('', $itm['actionUrl'], $itm['htmlOptions']);
                    }elseif( isset($itm['actionScript']) ) {
                        $action = $itm['actionScript'];
                    }
                    $dropDownArr[$action] = $itm['text'];
                }
                if (! isset($item['htmlOptions'])) {
                    $item['htmlOptions'] = array();
                }
                if( !isset($item['title']) ){
                    $item['htmlOptions']['empty'] = Yii::t('system', 'Please Select');
                }

                $item['htmlOptions']['onChange'] = 'eval(this.value)';
                echo CHtml::dropDownList('', '', $dropDownArr, $item['htmlOptions']);
                echo $aHidden;
                echo '</li>';
            }else{
                $showLink = true;

                $text = '<span>'.$item['text'].'</span>';
                if (! isset($item['url']) ) {
                    $item['url'] = 'javascript::void(0);' ;
                }else{
                    if(strpos($item['url'],'http')!==0 && strpos($item['url'],'javascript')===false){
                        $dep = explode('/',rtrim($item['url'],'/'));
                        $action = end($dep);$controller = prev($dep);
                        $module = str_replace('/'.$controller.'/'.$action, '', rtrim($item['url'],'/'));
                        $resourse = 'resource_'.ltrim($module,'/').'_'.$controller.'_'.$action;
                    }
                }

                if (! isset($item['htmlOptions'])) {
                    $item['htmlOptions'] = array();
                }
                echo '<li>';
                if(isset($item['type']) && $item['type'] == 'button') {
                    echo CHtml::button($item['text'], $item['htmlOptions']);
                }else{
                    if($showLink){
                        echo CHtml::link($text, $item['url'], $item['htmlOptions']);
                    }
                }
                echo '</li>';
            }
        }

    }
    public static function renderOrderBar($orderBar) {
        echo '<li class="orderBar" style="float:right;padding-right:20px;" >';
        if (! isset($orderBar)) {
            $orderBar = array();
        }
        echo  '<span>';
        echo CHtml::label(Yii::t('system', 'Order').'：', 'order_field', array( 'style' => 'font-size:13px;line-height: 200%;'));;
        echo '</span>';
        echo  '<span>';
        $target = "";
        $orderOptions = array_merge(array( "" => Yii::t('system', 'Please Select')), $orderBar);
        echo CHtml::dropDownList('searchOrderField', @$_REQUEST['orderField'], $orderOptions, $orderBar);
        $ascHtmlOptions = array( 'id' => 'asc', 'onclick' => "$.searchOrder(this, '$target')");
        $descHtmlOptions = array( 'id' => 'desc', 'onclick' => "$.searchOrder(this, '$target')");

        if ( isset($_REQUEST['orderDirection']) && !empty($_REQUEST['orderField']) &&
            in_array($_REQUEST['orderField'], array_keys($orderOptions))) {
            if ( $_REQUEST['orderDirection'] == 'asc' ) {
                $ascHtmlOptions['style'] = 'color:blue;font-weight: bold;';
            } else {
                $descHtmlOptions['style'] = 'color:blue;font-weight: bold;';
            }
        }

        echo CHtml::button(Yii::t('system', 'Asc'), $ascHtmlOptions);
        echo CHtml::button(Yii::t('system', 'Desc'), $descHtmlOptions);
        echo '</span>';
        //echo '<a href="javascript:void(0);"  class="btn_filter_column" title="'. Yii::t('system', 'Filter Column') .'"><span>》</span></a>';
        echo '</li>';
    }

    /**
     * 匹配亚马逊各国域名
     * @return array
     */
    public static function getSite(){
        return $item=[
            'us'=> "http://www.amazon.com/",
            'uk'=> "http://www.amazon.co.uk/",
            //'gb'=> "英国",
            'fr'=> "http://www.amazon.fr/",
            'de'=> "http://www.amazon.de/",
            'ca'=> "http://www.amazon.ca/",
            'es'=> "http://www.amazon.es/",
            'it'=> "http://www.amazon.it/",
            'jp'=> "http://www.amazon.co.jp/",
            'br'=> "http://www.amazon.com.br/",
            'in'=> "http://www.amazon.in/",
            'mx'=> "http://www.amazon.com.mx/",
            'au'=> "http://www.amazon.com.au/",
            //'nl'=> "" //荷兰
        ];
    }

    /**
     * 获取访问者公网IP
     * @return string
     */
    public static function getOutIp(){
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        }
        elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        elseif(!empty($_SERVER["REMOTE_ADDR"])){
            $cip = $_SERVER["REMOTE_ADDR"];
        }
        else{
            $cip = gethostbyname($_ENV['COMPUTERNAME']); //获取本机的局域网IP
        }
        return $cip;
    }

    /**
     * 写入亚马逊操作日志
     * @param $type
     * @param $content
     */
    public static function setAmazonLog($type,$content){
        $uname=User::model()->findByPk(Yii::app()->user->id)->user_full_name;
        $logdata['type']=$type;
        $logdata['username']=$uname;
        $logdata['content']=$content;
        $logdata['uid']=Yii::app()->user->id;
        $logdata['ip']=MHelper::getOutIp();
        $logdata['create_date']=date('Y-m-d H:i:s');
        AmazonOperatLog::saveData($logdata);
    }
    
    /**
     * @desc 获取捆绑sku对应具体sku
     * @param string $bindSku
     * A*2+B*3，A*2+B,A+B*3,A+B,A*2,A 
     */
    public static function getBindSkuMap($bindSku=''){
    	if( !$bindSku ) return null;
    	$bindSku = trim($bindSku);
    	$rltArr = array();
    	if( stripos($bindSku,'+') !== false ){
    		$skuQtyArr = explode('+',$bindSku);
    		foreach( $skuQtyArr as $val ){
    		    $val = trim($val);
    		    if (empty($val)) continue;
    			if(stripos($val,'*') !== false){
    				$tmp = explode('*',$val);
    				if (isset($tmp[1]) && !is_numeric($tmp[1]))
    				    $rltArr[] = array( 'sku'=>$val,'quantity'=>1 );
    				else
    				    $rltArr[] = array( 'sku'=>$tmp[0],'quantity'=>!empty($tmp[1])?$tmp[1]:1 );
    			}else{
    				$rltArr[] = array( 'sku'=>$val,'quantity'=>1 );
    			}
    		}
    	}else{
    		if(stripos($bindSku,'*') !== false){
    			$tmp = explode('*',$bindSku);
    			if (isset($tmp[1]) && !is_numeric($tmp[1]))
    			    $rltArr[] = array( 'sku'=>$bindSku,'quantity'=>1 );
    			else
    			     $rltArr[] = array( 'sku'=>$tmp[0],'quantity'=>!empty($tmp[1])?$tmp[1]:1 );
    		}else{
    			$rltArr[] = array( 'sku'=>$bindSku,'quantity'=>1 );
    		}
    	}
    	return $rltArr;
    }
    
}
?>
