<?php 


/**
 * ebay eub api class
 * @author Mark lin
 *
 */


class EbayeubLogis extends Ilogis
{
	
	private static $instance = null;
	private static $AppInfo = array();
	
	public $ShipTypeKey = 'EbayUebShipFromAdd';

	public $PickupTypeKey = 'EbayUebPickupAdd';
	
	public $ReturnTypeKey = 'EbayUebReturnAdd';
	
	public $EMSPickUpType = '01';
	
	protected $Wsdl = 'http://epacketws.pushauction.net/v3/orderservice.asmx?WSDL';
	
	public function __construct($config)
	{
		if (empty(self::$AppInfo))
		{
			self::$AppInfo = $config;
		}
	
		foreach ($config as $key=>$v)
		{
			$this->{$key} = $v?$v:$this->{$key};
		}
		
		if (self::$instance === null)
		{
			self::$instance = new SoapClient($this->Wsdl);
		}
	}
	
	
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::getHeaders()
	 */
	
	public function getHeaders()
	{
		return array();
	}
	
	/**
	 * upload message to eub
	 * @see Ilogis::upload()
	 */
	
	public function upload($data,$packageid)
	{
		try 
		{
//			$req = array();
//			$req['OrderDetail']['EMSPickUpType'] = $this->EMSPickUpType;
//			$req['OrderDetail']['ShipToAddress'] = $data['ShipToAddress'];
//			$req['OrderDetail']['ShipFromAddress'] = $this->getAddress('from');
//			$req['OrderDetail']['PickUpAddress'] = $this->getAddress('pickup');
//			$req['OrderDetail']['ReturnAddress'] = $this->getAddress('return');
//			$req['OrderDetail']['ItemList']['Item'] = $data['Item'];
			$package = new AddAPACShippingPackage();
			$package->AddAPACShippingPackageRequest = new AddAPACShippingPackageRequest();
			$this->buildUploadrequest(self::$AppInfo,$package->AddAPACShippingPackageRequest);
			$package->AddAPACShippingPackageRequest->MessageID = time() . $packageid;
			$package->AddAPACShippingPackageRequest->OrderDetail = new OrderDetail();
			$package->AddAPACShippingPackageRequest->OrderDetail->ShipFromAddress = new ShipFromAddress();
			$package->AddAPACShippingPackageRequest->OrderDetail->ShipToAddress = new ShipToAddress();
			$package->AddAPACShippingPackageRequest->OrderDetail->PickUpAddress = new PickUpAddress();
			$package->AddAPACShippingPackageRequest->OrderDetail->ReturnAddress = new ReturnAddress();
			$this->buildUploadrequest($data['ShipToAddress'],$package->AddAPACShippingPackageRequest->OrderDetail->ShipToAddress);
			$this->buildUploadrequest($this->getAddress('from'),$package->AddAPACShippingPackageRequest->OrderDetail->ShipFromAddress);
			$this->buildUploadrequest($this->getAddress('pickup'),$package->AddAPACShippingPackageRequest->OrderDetail->PickUpAddress);
			$this->buildUploadrequest($this->getAddress('return'),$package->AddAPACShippingPackageRequest->OrderDetail->ReturnAddress);
			$package->AddAPACShippingPackageRequest->OrderDetail->EMSPickUpType = $this->EMSPickUpType;
			$package->AddAPACShippingPackageRequest->OrderDetail->ItemList = new ItemList();
			foreach ($data['Item'] as $k=>$v)
			{
				$package->AddAPACShippingPackageRequest->OrderDetail->ItemList->Item[$k] = new Item();
				foreach ($v as $kk=>$vv)
				{
					if (is_array($vv)):
					$package->AddAPACShippingPackageRequest->OrderDetail->ItemList->Item[$k]->$kk = new $kk;
					foreach ($vv as $kkk=>$vvv)
					{
						$package->AddAPACShippingPackageRequest->OrderDetail->ItemList->Item[$k]->$kk->$kkk = $vvv;
					}
					else:
					$package->AddAPACShippingPackageRequest->OrderDetail->ItemList->Item[$k]->$kk = $vv;
					endif;
				}
			}

            $response = self::$instance->AddAPACShippingPackage($package);
			if (isset($response->AddAPACShippingPackageResult->Ack) && $response->AddAPACShippingPackageResult->Ack == 'Success')
			{
				return $response->AddAPACShippingPackageResult->TrackCode;
			}
			else 
			{
				if (isset($response->AddAPACShippingPackageResult->Message))
				{
					Yii::ulog($response->AddAPACShippingPackageResult->Message, 'uploadebayEub', null, 'LogisApi');
				}
				return false;
			}
		} 
		catch (Exception $e) 
		{
			Yii::ulog($e->getMessage(), 'uploadebayEub', null, 'LogisApi');
			return false;
		}
		
	}
	
	/**
	 * download a file
	 * @see Ilogis::download()
	 */
	
	public function getLabels($nums)
	{	
		try 
		{
			$labels = new GetAPACShippingLabels();
			$labels->GetAPACShippingLabelsRequest = new GetAPACShippingLabelsRequest();
			$this->buildUploadrequest(self::$AppInfo,$labels->GetAPACShippingLabelsRequest);
			$labels->GetAPACShippingLabelsRequest->MessageID = time() . rand(0, 10000);
			$labels->GetAPACShippingLabelsRequest->TrackCodeList = new TrackCodeList();
			foreach ($labels->GetAPACShippingLabelsRequest->TrackCodeList as $k=>$v)
			{
				$labels->GetAPACShippingLabelsRequest->TrackCodeList->$k = $nums;
			}
			$labels->GetAPACShippingLabelsRequest->PageSize = '1';
			$response = self::$instance->GetAPACShippingLabels($labels);
			if (isset($response->GetAPACShippingLabelsResult->Ack) && $response->GetAPACShippingLabelsResult->Ack == 'Success')
			{
				return $response->GetAPACShippingLabelsResult->Label;
			}
			else 
			{
				if (isset($response->GetAPACShippingLabelsResult->Message))
				{
					Yii::ulog($response->GetAPACShippingLabelsResult->Message, 'getEbayEubLabels', null, 'LogisApi');
				}
				return false;
			}
		} 
		catch (Exception $e) 
		{
			Yii::ulog($e->getMessage(), 'getEbayEubLabels', null, 'LogisApi');
			return false;
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::getALabel()
	 */
	
	public function getALabel($num)
	{	
		try 
		{
			$labels = new GetAPACShippingLabel();
			$labels->GetAPACShippingLabelRequest = new GetAPACShippingLabelRequest();
			$this->buildUploadrequest(self::$AppInfo,$labels->GetAPACShippingLabelRequest);
			$labels->GetAPACShippingLabelRequest->MessageID = time() . rand(0, 10000);
			$labels->GetAPACShippingLabelRequest->TrackCode = $num;
			$labels->GetAPACShippingLabelRequest->PageSize = '1';
			$response = self::$instance->GetAPACShippingLabel($labels);
			if (isset($response->GetAPACShippingLabelResult->Ack) && $response->GetAPACShippingLabelResult->Ack == 'Success')
			{
				return $response->GetAPACShippingLabelResult->Label;
			}
			else 
			{
				if (isset($response->GetAPACShippingLabelResult->Message))
				{
					Yii::ulog($response->GetAPACShippingLabelResult->Message, 'getAEbayEubLabel', null, 'LogisApi');
				}
				return false;
			}
		} 
		catch (Exception $e) 
		{
			Yii::ulog($e->getMessage(), 'getAEbayEubLabel', null, 'LogisApi');
			return false;
		}
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::trace()
	 */
	
	public function trace($no,$lang='cn')
	{	
		try 
		{
			$labels = new GetAPACShippingPackageStatus();
			$labels->GetAPACShippingPackageStatusRequest = new GetAPACShippingPackageStatusRequest();
			$this->buildUploadrequest(self::$AppInfo,$labels->GetAPACShippingPackageStatusRequest);
			$labels->GetAPACShippingPackageStatusRequest->MessageID = time() . rand(0, 10000);
			$labels->GetAPACShippingPackageStatusRequest->TrackCode = $no;
			$response = self::$instance->GetAPACShippingPackageStatus($labels);
			if (isset($response->GetAPACShippingPackageStatusResult->Ack) && $response->GetAPACShippingPackageStatusResult->Ack == 'Success')
			{
				return $response->GetAPACShippingPackageStatusResult->Status;
			}
			else 
			{
				if (isset($response->GetAPACShippingPackageStatusResult->Message))
				{
					Yii::ulog($response->GetAPACShippingPackageStatusResult->Message, 'traceEbayEub', null, 'LogisApi');
				}
				return false;
			}
		} 
		catch (Exception $e) 
		{
			Yii::ulog($e->getMessage(), 'traceEbayEub', null, 'LogisApi');
			return false;
		}
		
	}
	
	
	/**
	 * (non-PHPdoc)
	 * @see Ilogis::cancel()
	 */
	
	public function cancel($no)
	{
		try 
		{
			$labels = new CancelAPACShippingPackage();
			$labels->CancelAPACShippingPackageRequest = new CancelAPACShippingPackageRequest();
			$this->buildUploadrequest(self::$AppInfo,$labels->CancelAPACShippingPackageRequest);
			$labels->CancelAPACShippingPackageRequest->MessageID = time() . rand(0, 10000);
			$labels->CancelAPACShippingPackageRequest->TrackCode = $mailnum;
			$labels->CancelAPACShippingPackageRequest->PageSize = '1';
			$response = self::$instance->CancelAPACShippingPackage($labels);
			if (isset($response->CancelAPACShippingPackageResult->Ack) && $response->CancelAPACShippingPackageResult->Ack == 'Success')
			{
				return true;
			}
			else 
			{
				if (isset($response->CancelAPACShippingPackageResult->Message))
				{
					Yii::ulog($response->CancelAPACShippingPackageResult->Message, 'CancelEbayEub', null, 'LogisApi');
				}
				return false;
			}
		} 
		catch (Exception $e) 
		{
			Yii::ulog($e->getMessage(), 'CancelEbayEub', null, 'LogisApi');
			return false;
		}	
	}
	
	
	/**
	 * 确认发货
	 * @param array $data
	 * @param int $packageid
	 */
	
	public function validate($mailnum)
	{	
		try 
		{
			$labels = new ConfirmAPACShippingPackage();
			$labels->ConfirmAPACShippingPackageRequest = new ConfirmAPACShippingPackageRequest();
			$this->buildUploadrequest(self::$AppInfo,$labels->ConfirmAPACShippingPackageRequest);
			$labels->ConfirmAPACShippingPackageRequest->MessageID = time() . rand(0, 10000);
			$labels->ConfirmAPACShippingPackageRequest->TrackCode = $mailnum;
			$labels->ConfirmAPACShippingPackageRequest->PageSize = '1';
			$response = self::$instance->ConfirmAPACShippingPackage($labels);
			if (isset($response->ConfirmAPACShippingPackageResult->Ack) && $response->ConfirmAPACShippingPackageResult->Ack == 'Success')
			{
				return true;
			}
			else 
			{
				if (isset($response->ConfirmAPACShippingPackageResult->Message))
				{
					Yii::ulog($response->ConfirmAPACShippingPackageResult->Message, 'validateEbayEub', null, 'LogisApi');
				}
				return false;
			}
		} 
		catch (Exception $e) 
		{
			Yii::ulog($e->getMessage(), 'validateEbayEub', null, 'LogisApi');
			return false;
		}
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
	 * geren obj
	 * @param array $order
	 */
	
	public function buildUploadrequest($data,$obj)
	{
		
        foreach ($obj as $k=>$v)
        {
        	$obj->$k = isset($data[$k])?$data[$k]:'';
        }
        return $obj;
	}
	
	
	
	
    /**
     * request api
     * 
     */
    
    private function _exec($obj,$method='get')
    {
    	return self::$instance->{$method}($obj);
    }
	

	
}




class AddAPACShippingPackage 
{
	/**
	* @access public
	* @var AddAPACShippingPackageRequest
	*/
	public $AddAPACShippingPackageRequest;
}


class VerifyAPACShippingUser 
{
	/**
	* @access public
	* @var VerifyAPACShippingUserRequest
	*/
	public $VerifyAPACShippingUserRequest;
}


class ConfirmAPACShippingPackage {
	/**
	* @access public
	* @var ConfirmAPACShippingPackageRequest
	*/
	public $ConfirmAPACShippingPackageRequest;
}


class GetAPACShippingLabel 
{
	/**
	* @access public
	* @var GetAPACShippingLabelRequest
	*/
	public $GetAPACShippingLabelRequest;

}

class GetAPACShippingPackage 
{
	/**
	* @access public
	* @var GetAPACShippingPackageRequest
	*/
	public $GetAPACShippingPackageRequest;
}


class GetAPACShippingPackageStatus 
{
	/**
	* @access public
	* @var GetAPACShippingPackageStatusRequest
	*/
	public $GetAPACShippingPackageStatusRequest;
}


class GetAPACShippingRate 
{
	/**
	* @access public
	* @var GetAPACShippingRateRequest
	*/
	public $GetAPACShippingRateRequest;
}


class VerifyAPACShippingUserRequest extends BaseRequest 
{
}


class RecreateAPACShippingPackage 
{
	/**
	* @access public
	* @var RecreateAPACShippingPackageRequest
	*/
	public $RecreateAPACShippingPackageRequest;
}


class GetAPACShippingTrackCode 
{
	/**
	* @access public
	* @var GetAPACShippingTrackCodeRequest
	*/
	public $GetAPACShippingTrackCodeRequest;
}


class GetAPACShippingLabels 
{
	/**
	* @access public
	* @var GetAPACShippingLabelsRequest
	*/
	public $GetAPACShippingLabelRequest;
}

class BaseRequest 
{
	
	public $Version;
	
	public $APIDevUserID;
	
	public $APISellerUserToken;
	
	public $AppID;
	
	public $AppCert;
	
	public $APISellerUserID;
	
	public $MessageID;
	
	public $Carrier;
	
}

class OrderDetail 
{
	/**
	* @access public
	* @var PickUpAddress
	*/
	public $PickUpAddress;
	/**
	* @access public
	* @var ShipFromAddress
	*/
	public $ShipFromAddress;
	/**
	* @access public
	* @var ShipToAddress
	*/
	public $ShipToAddress;
	/**
	* @access public
	* @var ItemList
	*/
	public $ItemList;
	/**
	* @access public
	* @var sint
	*/
	public $EMSPickUpType;
	/**
	* @access public
	* @var ReturnAddress
	*/
	public $ReturnAddress;
	/**
	* @access public
	* @var sstring
	*/
	public $ExternalRefId;
}


class Address 
{
	/**
	* @access public
	* @var sstring
	*/
	public $Contact;
	/**
	* @access public
	* @var sstring
	*/
	public $Company;
	/**
	* @access public
	* @var sstring
	*/
	public $Street;
	/**
	* @access public
	* @var sstring
	*/
	public $District;
	/**
	* @access public
	* @var sstring
	*/
	public $City;
	/**
	* @access public
	* @var sstring
	*/
	public $Province;
	/**
	* @access public
	* @var sstring
	*/
	public $Country;
	/**
	* @access public
	* @var sstring
	*/
	public $Postcode;
	/**
	* @access public
	* @var sstring
	*/
	public $Phone;
	/**
	* @access public
	* @var sstring
	*/
	public $Mobile;
	/**
	* @access public
	* @var sstring
	*/
	public $Email;
}

class ShipFromAddress extends Address {
}

class ShipToAddress extends Address {
/**
* @access public
* @var sstring
*/
public $CountryCode;
}

class ItemList {
/**
* @access public
* @var Item[]
*/
public $Item;
}

class Item {
/**
* @access public
* @var sstring
*/
public $CurrencyCode;
/**
* @access public
* @var sstring
*/
public $EBayEmail;
/**
* @access public
* @var sstring
*/
public $EBayBuyerID;
/**
* @access public
* @var sstring
*/
public $EBayItemID;
/**
* @access public
* @var sstring
*/
public $EBayItemTitle;
/**
* @access public
* @var sstring
*/
public $EBayMessage;
/**
* @access public
* @var sint
*/
public $EBaySiteID;
/**
* @access public
* @var sstring
*/
public $EBayTransactionID;
/**
* @access public
* @var sstring
*/
public $EBayOrderID;
/**
* @access public
* @var sstring
*/
public $Note;
/**
* @access public
* @var sint
*/
public $OrderSalesRecordNumber;
/**
* @access public
* @var sdateTime
*/
public $PaymentDate;
/**
* @access public
* @var sstring
*/
public $PayPalEmail;
/**
* @access public
* @var sstring
*/
public $PayPalMessage;
/**
* @access public
* @var sint
*/
public $PostedQTY;
/**
* @access public
* @var sdecimal
*/
public $ReceivedAmount;
/**
* @access public
* @var sint
*/
public $SalesRecordNumber;
/**
* @access public
* @var sdateTime
*/
public $SoldDate;
/**
* @access public
* @var sdecimal
*/
public $SoldPrice;
/**
* @access public
* @var sint
*/
public $SoldQTY;
/**
* @access public
* @var SKU
*/
public $SKU;
}


class SKU {
/**
* @access public
* @var sstring
*/
public $SKUID;
/**
* @access public
* @var sdecimal
*/
public $Weight;
/**
* @access public
* @var sstring
*/
public $CustomsTitleCN;
/**
* @access public
* @var sstring
*/
public $CustomsTitleEN;
/**
* @access public
* @var sdecimal
*/
public $DeclaredValue;
/**
* @access public
* @var sstring
*/
public $OriginCountryName;
/**
* @access public
* @var sstring
*/
public $OriginCountryCode;
}


class ReturnAddress extends Address {
}


class BaseResponse {
/**
* @access public
* @var sstring
*/
public $Version;
/**
* @access public
* @var tnsEnumAck
*/
public $Ack;
/**
* @access public
* @var sstring
*/
public $Message;
/**
* @access public
* @var sdateTime
*/
public $Timestamp;
/**
* @access public
* @var sstring
*/
public $InvocationID;
}


class EnumAck {
}

class CancelAPACShippingPackage {
/**
* @access public
* @var CancelAPACShippingPackageRequest
*/
public $CancelAPACShippingPackageRequest;
}


class CancelAPACShippingPackageRequest extends BaseRequest {
/**
* @access public
* @var sstring
*/
public $TrackCode;
}


class CancelAPACShippingPackageResponse extends BaseResponse {
}



class ConfirmAPACShippingPackageRequest extends BaseRequest {
/**
* @access public
* @var sstring
*/
public $TrackCode;
}


class ConfirmAPACShippingPackageResponse extends BaseResponse {
}




class GetAPACShippingLabelRequest extends BaseRequest {
/**
* @access public
* @var sstring
*/
public $TrackCode;
/**
 * @access public
 * @var sint
 */
public $PageSize;
}


class GetAPACShippingLabelResponse extends BaseResponse {
	/**
	 * @access public
	 * @var sbase64Binary
	 */
	public $Label;
}




class GetAPACShippingLabelsRequest extends BaseRequest {
	/**
	 * @access public
	 * @var TrackCodeList
	 */
	public $TrackCodeList;
	/**
	 * @access public
	 * @var sint
	 */
	public $PageSize;
}


class TrackCodeList {
	/**
	 * @access public
	 * @var sstring[]
	 */
	public $TrackCode;
}


class GetAPACShippingLabelsResponse extends BaseResponse {
	/**
	 * @access public
	 * @var sbase64Binary
	 */
	public $Label;
}




class GetAPACShippingPackageRequest extends BaseRequest {
	/**
	 * @access public
	 * @var sstring
	 */
	public $TrackCode;
}


class GetAPACShippingPackageResponse extends BaseResponse {
	/**
	 * @access public
	 * @var OrderDetail
	 */
	public $OrderDetail;
}




class GetAPACShippingPackageStatusRequest extends BaseRequest {
	/**
	 * @access public
	 * @var sstring
*/
public $TrackCode;
}


class GetAPACShippingPackageStatusResponse extends BaseResponse {
/**
* @access public
* @var sint
*/
public $Status;
/**
* @access public
* @var sstring
*/
public $Note;
}




class GetAPACShippingRateRequest extends BaseRequest {
/**
* @access public
* @var sint
*/
public $ShipCode;
/**
* @access public
* @var sstring
*/
public $CountryCode;
/**
* @access public
* @var sdecimal
*/
public $Weight;
/**
* @access public
* @var sint
*/
public $InsuranceType;
/**
* @access public
* @var sdecimal
*/
public $InsuranceAmount;
/**
* @access public
* @var sint
*/
public $MailType;
}


class GetAPACShippingRateResponse extends BaseResponse {
/**
* @access public
* @var sdecimal
*/
public $DeliveryCharge;
/**
* @access public
* @var sdecimal
*/
public $AdditionalCharge;
/**
* @access public
* @var sdecimal
*/
public $InsuranceFee;
}


class VerifyAPACShippingUserResponse extends BaseResponse {
}




class RecreateAPACShippingPackageRequest extends BaseRequest {
/**
* @access public
* @var sstring
*/
public $TrackCode;
}


class RecreateAPACShippingPackageResponse extends BaseResponse {
/**
* @access public
* @var sstring
*/
public $TrackCode;
}



class GetAPACShippingTrackCodeRequest extends BaseRequest {
/**
* @access public
* @var sstring
*/
public $EBayItemID;
/**
* @access public
* @var sstring
*/
public $EBayTransactionID;
}


class GetAPACShippingTrackCodeResponse extends BaseResponse {
/**
* @access public
* @var sstring
*/
public $TrackCode;
}


class AddAPACShippingPackageRequest extends BaseRequest {
/**
* @access public
* @var OrderDetail
*/
public $OrderDetail;
}


class PickUpAddress extends Address {
}


class AddAPACShippingPackageResponse extends BaseResponse {
/**
* @access public
* @var sstring
*/
public $TrackCode;
}





