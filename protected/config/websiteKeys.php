<?php
/**
 * Magento keys config
 * 
 * @author peter
 */
switch (YII_ENV) {
    case Env::PRODUCTION;
        return array(
        	'NF'=>array(
	            'user'                   => 'api',
	            'apiKey'                 => 'api1234',           
	            'soapUrl'                => 'http://newfrog001/api/?wsdl'
			)
        );
        break;
    case Env::TEST;
    case Env::DEVELOPMENT;
        return array(
           'NF'=>array(
	            'user'                   => 'api',
	            'apiKey'                 => 'api1234',           
	            'soapUrl'                => 'http://newfrog001/api/?wsdl'
			)
        );
        break;
}
?>
