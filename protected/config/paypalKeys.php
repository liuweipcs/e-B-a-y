<?php
/**
 * paypal keys config
 * 
 * @author Bob <Foxzeng>
 */
switch (YII_ENV) {
    case Env::PRODUCTION;
        return array(         
            'serverUrl' => 'https://www.paypal.com/wsdl/PayPalSvc.wsdl',       
            'version'   => '92.0'
        );
        break;
    case Env::TEST;
    case Env::DEVELOPMENT;
        return array(          
            //'serverUrl' => 'https://www.sandbox.paypal.com/wsdl/PayPalSvc.wsdl', 
            'serverUrl' => 'https://www.sandbox.paypal.com/wsdl/PayPalSvc.wsdl',//  sandbox 
            'version'   => '92.0'
        );
        break;
}
?>
