<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/16 0016
 * Time: 下午 3:11
 */

Yii::import('application.modules.products.components.amazonproductdata.*');

class AmazonNewProduct
{
	/**
	 * 默认的xsd分类
	 */
	const XSD_DF = 'Default';

	/**
	 * 待刊登的账号
	 * 
	 * @var string
	 */
	public $accountId = '';

	/**
	 * product的xml数据
	 * 
	 * @var string
	 */
	public $xml = '';


	/**
	 * 刊登amazon产品
	 * 
	 * @param  array $form
	 * @return mixed
	 */
	public static function uploadAmazonProduct(array $form = array())
	{
		$instance = new self();

		header('Content-Type:text/xml');
		// echo $instance->handleFormData($form)->echoXmlData();//->httpPost();
		// echo $instance->handleFormData($form)->httpPost();
		echo $instance->handleFormData($form)->directPost();
		exit;
	}

	/**
	 * test xml whether valid ?
	 * 
	 * @param  string $xml 
	 * @noreturn 
	 */
	public function echoXmlData($xml = '')
	{
		ob_clean();
		header('Content-Type:text/xml');

		echo '<?xml version="1.0" ?>';
		echo $xml ? $xml: $this->xml;
		exit;
	}

	/**
	 * 调用service模块接口post数据到amazon平台
	 * 
	 * @return boolean
	 */
	public function httpPost()
	{
		$ch = curl_init();

		if ($this->xml == '') {
			throw new Exception("Error xml Data", 1);
		}

		$post_data = array('product' => $this->xml, 'id' => $this->accountId);

		curl_setopt($ch, CURLOPT_URL, "http://".Yii::app()->request->serverName.Yii::app()->createUrl('/services/amazon/amazonproduct/index'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

		$output = curl_exec($ch);

		curl_close($ch);

		return $output;
	}

	/**
	 * 
	 * 直接调用组件post
	 * 
	 * @noreturn
	 */
	public function directPost()
	{
		Yii::import('application.vendors.amazon.*');
		Yii::import("application.modules.services.components.*");
		Yii::import("application.modules.services.modules.amazon.models.*");
		Yii::import("application.modules.services.modules.amazon.components.*");

		$account    = UebModel::model('AmazonAccount')->findByPk($this->accountId);
		$submitFeed = new SubmitFeedRequest();

		$reqArrList = array(
			'product' => $this->xml,
		);
		// $reqArrList = dataschema::get();
		// echo $this->xml;exit;

        $response = $submitFeed->setAccountName($account->account_name)
            ->setServiceUrl()
            ->setConfig()
            ->setFeedType('_POST_PRODUCT_DATA_')
            ->setBusinessType(SubmitFeedRequest::NEW_PRODUCT)
            ->setReqArrList($reqArrList)
            ->setRequest()
            ->setType('webservice')
            ->setService()
            ->sendHttpRequest()
            ->getResponse();

		//log
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$doc->loadXML($submitFeed->getLastXML());
		$doc->save('D:/wwwerp/protected/data/product.xml');

        $data = $submitFeed->parseResponse($response);
        Yii::p($data);
        exit;
	}

	/**
	 * 获取相应业务处理逻辑类
	 * 
	 * @param  int $xsd2 [description]
	 * 
	 * @return string
	 */
	protected function getHandleClass($xsdId)
	{
		if ($xsdId != '') {
			$xsd2 = UebModel::model('AmazonProdataxsd')->findByPk($xsdId);
			$sfix2 = $xsd2->category;

			$xsd1 = UebModel::model('AmazonProdataxsd')->findByPk($xsd2->pid);
			$sfix1 = $xsd1->category;
		} else {
			$sfix1 = self::XSD_DF;
		}

		return "Amazon" . $sfix1 . $sfix2;
	}

	/**
	 * 处理主体表单数据
	 * 
	 * @param  array  $form 
	 * @return string
	 */
	protected function handleFormData(array $params)
	{
		$xml = '';

		$this->accountId = $params['account_id'];

		/* ##### BASE DATA START #####*/
		$xml .= '<SKU>'.$params['SKU'].'</SKU>';

		if ($params['StandardProductID']['Type'] && $params['StandardProductID']['Value']) {
			$fmt = '<StandardProductID><Type>%s</Type><Value>%s</Value></StandardProductID>';
			$xml .= sprintf($fmt, $params['StandardProductID']['Type'], $params['StandardProductID']['Value']);
		}

		if ($params['GtinExemptionReason']) {
			$xml .= '<GtinExemptionReason>'.$params['GtinExemptionReason'].'</GtinExemptionReason>';
		}

		if ($params['RelatedProductID']['Type'] && $params['RelatedProductID']['Value']) {
			$fmt = '<RelatedProductID><Type>%s</Type><Value>%s</Value></RelatedProductID>';
			$xml .= sprintf($fmt, $params['RelatedProductID']['Type'], $params['RelatedProductID']['Value']);
		}

		if ($params['ProductTaxCode']) {
			$xml .= '<ProductTaxCode>'.$params['ProductTaxCode'].'</ProductTaxCode>';
		}

		if ($params['LaunchDate']) {
			$datetime = new DateTime($params['LaunchDate']);
			$xml .= '<LaunchDate>'.$datetime->format('Y-m-d\TH:i:s').'</LaunchDate>';
		}

		if ($params['DiscontinueDate']) {
			$datetime = new DateTime($params['DiscontinueDate']);
			$xml .= '<DiscontinueDate>'.$datetime->format('Y-m-d\TH:i:s').'</DiscontinueDate>';
		}

		if ($params['ReleaseDate']) {
			$date = new DateTime($params['ReleaseDate']);
			$xml .= '<ReleaseDate>'.$datetime->format('Y-m-d\TH:i:s').'</ReleaseDate>';
		}

		if ($params['ExternalProductUrl']) {
			$xml .= '<ExternalProductUrl>'.$params['ExternalProductUrl'].'</ExternalProductUrl>';
		}

		if ($params['OffAmazonChannel']) {
			$xml .= '<OffAmazonChannel>'.$params['OffAmazonChannel'].'</OffAmazonChannel>';
		}

		if ($params['OnAmazonChannel']) {
			$xml .= '<OnAmazonChannel>'.$params['OnAmazonChannel'].'</OnAmazonChannel>';
		}

		if ($params['Condition']['ConditionType'] && $params['Condition']['ConditionNote']) {
			$fmt = '<ConditionInfo><ConditionType>%s</ConditionType><ConditionNote>%s</ConditionNote></ConditionInfo>';
			$xml .= sprintf($fmt, $params['Condition']['ConditionType'], $params['Condition']['ConditionNote']);
		}

		//返点, 注意欧洲,日本不能用
		if ($params['Rebate']) {
		}

		if ($params['ItemPackageQuantity']) {
			$xml .= '<ItemPackageQuantity>'.$params['ItemPackageQuantity'].'</ItemPackageQuantity>';
		}

		if ($params['NumberOfItems']) {
			$xml .= '<NumberOfItems>'.$params['NumberOfItems'].'</NumberOfItems>';
		}

		if ($params['LiquidVolume']['VolumeUnitOfmeasure'] && $params['LiquidVolume']['Value']) {
			$xml .= sprintf('<LiquidVolume unitOfMeasure="%s">%s</LiquidVolume>', $params['LiquidVolume']['VolumeUnitOfmeasure'], $params['LiquidVolume']['Value']);
		}

		/* ##### BASE DATA END ##### */


		/* ##### The Description Data Start ##### */
		$desc = '';

		if ($params['Title']) {
			$desc .= '<Title>'.$params['Title'].'</Title>';
		}

		if ($params['Brand']) {
			$desc .= '<Brand>'.$params['Brand'].'</Brand>';
		}

		if ($params['Designer']) {
			$desc .= '<Designer>'.$params['Designer'].'</Designer>';
		}

		if ($params['Description']) {
			$desc .= '<Description>'.$params['Description'].'</Description>';
		}

		if (!empty($params['BulletPoint'])) {
			$i = 0;
			foreach ($params['BulletPoint'] as $value) {
				if ($i > 4) break;
				$desc .= '<BulletPoint>'.$value.'</BulletPoint>';
				$i++;
			}
		}

		if ($params['ItemDimensions'] &&
			$params['ItemDimensions']['Length'] &&
			$params['ItemDimensions']['Width'] &&
			$params['ItemDimensions']['Weight']) {

			$fmt = '<ItemDimensions><Length>%s</Length><Width>%s</Width><Height>%s</Height><Weight>%s</Weight></ItemDimensions>';
			$desc .= sprintf($fmt, $params['ItemDimensions']['Length'], $params['ItemDimensions']['Width'], $params['ItemDimensions']['Height'], $params['ItemDimensions']['Weight']);
		}

		if ($params['PackageDimensions'] &&
			$params['PackageDimensions']['Length'] &&
			$params['PackageDimensions']['Width'] &&
			$params['PackageDimensions']['Weight']) {

			$fmt = '<PackageDimensions><Length>%s</Length><Width>%s</Width><Height>%s</Height><Weight>%s</Weight></PackageDimensions>';
			$desc .= sprintf($fmt, $params['PackageDimensions']['Length'], $params['PackageDimensions']['Width'], $params['PackageDimensions']['Height'], $params['PackageDimensions']['Weight']);
		}

		if ($params['PackageWeight'] &&
			$params['PackageWeight']['unitOfMeasure'] &&
			$params['PackageWeight']['Value']) {

			$desc .= '<PackageWeight unitOfMeasure="'.$params['PackageWeight']['unitOfMeasure'].'">'.$params['PackageWeight']['Value'].'</PackageWeight>';
		}

		if ($params['ShippingWeight'] &&
			$params['ShippingWeight']['unitOfMeasure'] &&
			$params['ShippingWeight']['Value']) {

			$desc .= '<ShippingWeight unitOfMeasure="'.$params['ShippingWeight']['unitOfMeasure'].'">'.$params['ShippingWeight']['Value'].'</ShippingWeight>';
		}

		if ($params['MerchantCatalogNumber']) {
			$desc .= '<MerchantCatalogNumber>'.$params['MerchantCatalogNumber'].'</MerchantCatalogNumber>';
		}

		if ($params['MSRP'] &&
			$params['MSRP']['BaseCurrencyCode'] &&
			$params['MSRP']['Value']) {

			$desc .= sprintf('<MSRP currency="%s">%s</MSRP>',$params['MSRP']['BaseCurrencyCode'], $params['MSRP']['Value']);
		}

		if ($params['MSRPWithTax'] &&
			$params['MSRPWithTax']['BaseCurrencyCode'] &&
			$params['MSRPWithTax']['Value']) {

			$desc .= sprintf('<MSRPWithTax currency="%s">%s</MSRPWithTax>', $params['MSRPWithTax']['BaseCurrencyCode'], $params['MSRPWithTax']['Value']);
		}

		if ($params['MaxOrderQuantity']) {
			$desc .= '<MaxOrderQuantity>'.$params['MaxOrderQuantity'].'</MaxOrderQuantity>';
		}

		if ($params['SerialNumberRequired']) {
			$desc .= '<SerialNumberRequired>'.$params['SerialNumberRequired'].'</SerialNumberRequired>';
		}

		if ($params['Prop65']) {
			$desc .= '<Prop65>'.$params['Prop65'].'</Prop65>';
		}

		// CPSIAWarning
		if ($params['CPSIAWarning']) {
		}

		if ($params['CPSIAWarningDescription']) {
			$desc .= '<CPSIAWarningDescription>'.$params['CPSIAWarningDescription'].'</CPSIAWarningDescription>';
		}

		if ($params['LegalDisclaimer']) {
			$desc .= '<LegalDisclaimer>'.$params['LegalDisclaimer'].'</LegalDisclaimer>';
		}

		if ($params['Manufacturer']) {
			$desc .= '<Manufacturer>'.$params['Manufacturer'].'</Manufacturer>';
		}

		if ($params['MfrPartNumber']) {
			$desc .= '<MfrPartNumber>'.$params['MfrPartNumber'].'</MfrPartNumber>';
		}

		// SearchTerms x5
		if ($params['SearchTerms']) {
			$i = 0;
			$segments = explode(',', $params['SearchTerms']);

			foreach ($segments as $value) {
				if ($i > 4) break;

				$desc .= '<SearchTerms>'.$value.'</SearchTerms>';
				$i++;
			}
		}

		// PlatinumKeywords x20
		if ($params['PlatinumKeywords']) {
		}

		if ($params['Memorabilia']) {
			$desc .= '<Memorabilia>'.$params['Memorabilia'].'</Memorabilia>';
		}

		if ($params['Autographed']) {
			$desc .= '<Autographed>'.$params['Autographed'].'</Autographed>';
		}

		if ($params['UsedFor']) {
			$desc .= '<UsedFor>'.$params['UsedFor'].'</UsedFor>';
		}

		if ($params['ItemType']) {
			$desc .= '<ItemType>'.$params['ItemType'].'</ItemType>';
		}

		if ($params['OtherItemAttributes']) {
			$desc .= '<OtherItemAttributes>'.$params['OtherItemAttributes'].'</OtherItemAttributes>';
		}

		// TargetAudience x4
		if ($params['TargetAudience']) {
			$desc .= '<TargetAudience>'.$params['TargetAudience'].'</TargetAudience>';
		}

		// SubjectContent x5
		if ($params['SubjectContent']) {
			$desc .= '<SubjectContent>'.$params['SubjectContent'].'</SubjectContent>';
		}

		if ($params['IsGiftWrapAvailable']) {
			$desc .= '<IsGiftWrapAvailable>'.$params['IsGiftWrapAvailable'].'</IsGiftWrapAvailable>';
		}

		if ($params['IsGiftMessageAvailable']) {
			$desc .= '<IsGiftMessageAvailable>'.$params['IsGiftMessageAvailable'].'</IsGiftMessageAvailable>';
		}

		// PromotionKeywords x10
		if ($params['PromotionKeywords']) {
			$i = 0;
			$temp = explode(',', $params['PromotionKeywords']);

			foreach ($temp as $value) {
				if ($i > 10)
					break;
				$desc .= '<PromotionKeywords>'.$value.'</PromotionKeywords>';
				$i++;
			}
		}

		if ($params['IsDiscontinuedByManufacturer']) {
			$desc .= '<IsDiscontinuedByManufacturer>'.$params['IsDiscontinuedByManufacturer'].'</IsDiscontinuedByManufacturer>';
		}

		if ($params['DeliveryScheduleGroupID']) {
			$desc .= '<DeliveryScheduleGroupID>'.$params['DeliveryScheduleGroupID'].'</DeliveryScheduleGroupID>';
		}

		if ($params['DeliveryChannel']) {
			foreach ($params['DeliveryChannel'] as $value) {
				$desc .= '<DeliveryChannel>'.$value.'</DeliveryChannel>';
			}
		}

		if ($params['PurchasingChannel']) {
			foreach ($params['PurchasingChannel'] as $value) {
				$desc .= '<PurchasingChannel>'.$value.'</PurchasingChannel>';
			}
		}

		if ($params['MaxAggregateShipQuantity']) {
			$desc .= '<MaxAggregateShipQuantity>'.$params['MaxAggregateShipQuantity'].'</MaxAggregateShipQuantity>';
		}

		if ($params['IsCustomizable']) {
			$desc .= '<IsCustomizable>'.$params['IsCustomizable'].'</IsCustomizable>';
		}

		if ($params['CustomizableTemplateName']) {
			$desc .= '<CustomizableTemplateName>'.$params['CustomizableTemplateName'].'</CustomizableTemplateName>';
		}

		// IMPORTANT
		if ($params['RecommendedBrowseNode']) {
			// $desc .= '<RecommendedBrowseNode>'.$params['RecommendedBrowseNode'].'</RecommendedBrowseNode>';
		}

		if ($params['MerchantShippingGroupName']) {
			$desc .= '<MerchantShippingGroupName>'.$params['MerchantShippingGroupName'].'</MerchantShippingGroupName>';
		}

		// It says European only
		if ($params['FEDAS_ID']) {
			$desc .= '<FEDAS_ID>'.$params['FEDAS_ID'].'</FEDAS_ID>';
		}

		if ($params['TSDAgeWarning']) {
			$desc .= '<TSDAgeWarning>'.$params['TSDAgeWarning'].'</TSDAgeWarning>';
		}

		if ($params['TSDWarning']) {
			$desc .= '<TSDWarning>'.$params['TSDWarning'].'</TSDWarning>';
		}

		if ($params['TSDLanguage']) {
			$desc .= '<TSDLanguage>'.$params['TSDLanguage'].'</TSDLanguage>';
		}

		// OptionalPaymentTypeExclusion x2
		if ($params['OptionalPaymentTypeExclusion']) {
			$desc .= '<OptionalPaymentTypeExclusion>'.$params['OptionalPaymentTypeExclusion'].'</OptionalPaymentTypeExclusion>';
		}

		if ($params['DistributionDesignationValues']) {
			$desc .= '<DistributionDesignationValues>'.$params['DistributionDesignationValues'].'</DistributionDesignationValues>';
		}

		// The Description Data
		if ($desc != '') {
			$xml .= '<DescriptionData>'.$desc.'</DescriptionData>';
		}
		/* ##### The Description END ##### */


		/* ##### The DiscoveryData Start ##### */
		$discoverdata = '';

		if ($params['Priority']) {
			$discoverdata .= '<Priority>'.$params['Priority'].'</Priority>';
		}

		if ($params['BrowseExclusion']) {
			$discoverdata .= '<BrowseExclusion>'.$params['BrowseExclusion'].'</BrowseExclusion>';
		}

		if ($params['RecommendationExclusion']) {
			$discoverdata .= '<RecommendationExclusion>'.$params['RecommendationExclusion'].'</RecommendationExclusion>';
		}

		if ($discoverdata != '') {
			$xml .= '<DiscoveryData>'.$discoverdata.'</DiscoveryData>';
		}
		/* #####The DiscoveryData End ##### */

		/* #####The ProductData Start */
		$productdata = '';
		$class = $this->getHandleClass($params['xsd_second']);

		if (class_exists($class)) {
			$instance = new $class($params['productdata']);

			if ($instance instanceof IAmazonProductdata) {
				$productdata = $instance->getProductdataDefinition();
			}

			if ($productdata != '') {
				$xml .= $productdata;
			}
		}
		/* #####The ProductData End*/

		if ($params['ShippedByFreight']) {
			$xml .= '<ShippedByFreight>'.$params['ShippedByFreight'].'</ShippedByFreight>';
		}

		if ($params['EnhancedImageURL']) {
			foreach ($params['EnhancedImageURL'] as $value) {
				$xml .= '<EnhancedImageURL>'.$value.'</EnhancedImageURL>';
			}
		}

		// Amazon-Vendor-Only 以及Amazon-Only查不到其定义,略

		if ($params['RegisteredParameter']) {
			$xml .= '<RegisteredParameter>'.$params['RegisteredParameter'].'</RegisteredParameter>';
		}

		$xml = '<Product>'.$xml.'</Product>';

		$this->xml = $xml;

		return $this;
	}
}