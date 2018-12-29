<?php 


/**
 * Bls api class
 * @author Super
 *
 */


class BlsLogis extends Ilogis
{
	
	private $account;
	
	private $password;
	
	private $api_url;
	private $type = 'LvsParcels';
	
	public function __construct($config)
	{
		foreach ($config as $key=>$v)
		{
			$this->{$key} = $v?$v:$this->{$key};
		}
	}
	
	
	
	public function getHeaders()
	{
		return array(
			"Content-type: text/json;charset=\"utf-8\"",
			"Accept: text/json",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"SOAPAction: \"run\"",
			"Content-length: ".strlen($this->data),
			"Authorization: Basic " . base64_encode($this->account.':'.$this->password)////帐号：密码
		);
	}
	
	/**
	 * upload message to bls
	 * @see Ilogis::upload()
	 */
	
	public function upload($data,$packageIds,$abs = '')
	{
		$this->data = $data;
		$url = $this->api_url . $this->type;
		return $this->curl_post($data,$url);
	}
	
	
	/**
	 * get a package id by all package ids
	 */
	public function getPackageId($packageIds){
		$result = array();
		foreach ($packageIds as $packageId){
			$ret = $this->getOrderPackageInfoBypackageId($packageId);
			if($ret['result'] != 'success'){
                $result[$packageid] = 'error-%%'.$ret['log'];
            }
		}
		return $result;
	}
	/**
	 * get order package info by packageid
	 * @param string $packageIds
	 * @return array 
	 */
	public function getOrderPackageInfoBypackageId($packageId){
		$return = array(
				'result' => '',
				'log' => ''
		);
    	$orderPackageModel = UebModel::model('OrderPackage');
    	$packageInfo = $orderPackageModel->getAnOrderPackageInfoByPackageId($packageId);//包裹信息
    
    	if($packageInfo['ship_status'] == $orderPackageModel->getShipStatusEnd()){
    		$return['result'] = 'error';
    		$return['log'] = "<li>包裹号为{$packageId}的<font color=red><b>已出货</b></font></li>";
    	}
    
//TODO     	if($packageInfo['ship_code'] != getModel('ship_type')->CODE_BE){//如果不是比利时邮政包裹
//     		$return['result'] = 'error';
// 			$return['log'] = "<li>包裹号为{$packageId}的<font color=red><b>不是比利时邮政包裹</b></font></li>";
// 			return $return;
//     	}
    	if($packageInfo['upload_ship'] == $orderPackageModel->getUploadShipYes()){//已上传
    		$return['result'] = 'error';
			$return['log'] = "<li>包裹号为{$packageId}的<font color=red><b>包裹已上传</b></font></li>";
    	}
    	$details = UebModel::model('OrderPackageDetail')->getDetailByPackageId($packageId);//包裹明细
    	$packageDetails = $details[0];
    	$sale_price = '';
    	foreach($details as $item){
    		$productInfo = UebModel::model('Product')->getBySku($item['sku']);
    		$sale_price += floatval($productInfo->product_cost)*intval($item['quantity']);
    	}
    	$total_price = round($sale_price/10,2);
    	$upload_arr = array(
    			"ContractId" => 1,
    			"OrderNumber" => $packageInfo['package_id'],
    			"RecipientName" => $packageInfo['ship_name'],
    			"RecipientStreet" => $orderPackageModel->htmlspecialcharsAddress($packageInfo['ship_street1'].' '.$packageInfo['ship_street2']),
    			"RecipientHouseNumber" => '',
    			"RecipientBusnumber" => '',
    			"RecipientZipCode" => $packageInfo['ship_zip'],
    			"RecipientCity" => $packageInfo['ship_city_name'],
    			"RecipientState" => $packageInfo['ship_stateorprovince'],
    			"RecipientCountry" => strtoupper($packageInfo['ship_country_name']),
    			"PhoneNumber" => $packageInfo['ship_phone'],
//    			"Email" => '',
    			"SenderName" => 'Universal E-Bussiness',
    			"SenderAddress" => 'GuangDong Shenzhen',
//    			"SenderSequence" => '1',
    	);
		$upload_arr['Customs'] = array();//产品详情
    	$product_info = UebModel::model('Product')->getBySku($packageDetails['sku']);//产品信息
    	$productDescriptionInfo = UebModel::model('Productdesc')->getDescriptionInfoBySkuAndLanguageCode($packageDetails['sku'],'english');
    	$cnames=UebModel::model('Product')->getProductInfoBySku($packageDetails['sku'],CN);
    	$productCategoryInfo = UebModel::model('ProductCategory')->getProductCategoryById($product_info->product_category_id);
    	if($productCategoryInfo['category_en_name'] == 'Cable'){
    		$item_content = 'Cable';
    	}else if(stripos($productDescriptionInfo['title'],"iphone")){
    		$item_content = 'Accessories for Cell Phone';
    	}else if(stripos($productDescriptionInfo['title'],"ipad")){
    		$item_content = 'Accessories for Tablet';
    	}else{
    		$item_content = $productDescriptionInfo['title'];
    	}
		$upload_arr['Customs'][] = array(
    			'Sku' =>$packageDetails['sku'],
    			'Value' => $total_price < 22 ? $total_price : 20,
				'ItemContent' => $item_content,
				'ItemCount' => $packageDetails['quantity'],
				'Currency' => 'EUR',
				"Weight" => ceil($product_info['product_weight']),
				'ChineseContentDescription' => trim($cnames['title']),
//				"SkuInInvoice" => $package_details['product_code'],
    	);
    	//var_dump($upload_arr);
		$return_data = $this->upload(json_encode($upload_arr),$packageInfo['package_id']);
		
		if($return_data['ProductBarcode']){//成功返回
			$TrackCode = $return_data['ProductBarcode'];
		}else{
			$TrackCode = false;
			$err_msg = '';
			if($return_data['Message']){
				$err_msg .= $return_data['Message'];
			}else{
				foreach($return_data as $key=>$item){
					$err_msg .= $key;
					foreach($item as $itm){
						$err_msg .= $itm.';';
					}
					$err_msg .= '<br/>';
				}
			}
		}
    
	    if($TrackCode){
	    	//标识成功
	    	$return['result'] = 'success';
	    	$return['log'] .= "<li>包裹号为{$packageId}的产品上传包裹成功,取得Track Code:".$TrackCode."...";
	    	$model = $orderPackageModel->findByPk($packageId);
			$model->setAttribute('track_num', $TrackCode);
			$model->setAttribute('upload_ship',  $orderPackageModel->getUploadShipYes());
	    	if($model->save()){
	    		$return['log'] .= "<li>保存上传记录<font color=green><b>成功</b></font></li>";
	    	}else{
	    		$return['log'] .= "<li>保存上传记录<font color=red><b>失败</b></font></li>";
	    	}
	    }else{
	    	//标识失败
	    	$return['result'] = 'error';
	    	$return['log'] .= "<li><font color=red>".$err_msg."</font></li>";
	    	
	    }
	    
	    return $return;
	}
	
	/**
	 * download a file
	 * @see Ilogis::download()
	 */
	
	public function getLabels($nums)
	{	
		return true;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::getALabel()
	 */
	
	public function getALabel($num)
	{
		return true;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::trace()
	 */
	
	public function trace($no,$lang='cn')
	{
		return true;
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::cancel()
	 */
	
	public function cancel($no)
	{
		return true;
	}
	
	
	/**
	 * 确认发货
	 * @param array $data
	 * @param int $packageid
	 */
	
	public function validate($data,$packageid)
	{
		return true;
	}
	
	
	
	/**
	 * 运单信息
	 * @param string $mailnum
	 * 
	 */
	
	public function order($mailnum)
	{
		return true;
	}
	
	
    /**
     * request api
     * 
     */
	protected function curl_post($post_data,$url){
		$headers = $this->getHeaders();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	
		$data = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($data,true);
		return $data;
	}
	
    private function _exec($url,$data,$method='get')
    {
    	return json_decode(parent::execute($url, $data, $method));
    }
	

	
}