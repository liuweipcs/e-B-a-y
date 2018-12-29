<?php
/**
 * ebay keys config
 * 
 * @author Bob <Foxzeng>
 */
switch (YII_ENV) {
    case Env::PRODUCTION;
        return array(
            'appKey'                    => '29851206',
            'appSecret'                 => 'xgB3ssH824',
            'gatewayAuthorizeUrl'       => 'http://gw.api.alibaba.com/auth/authorize.htm',     
            'gatewayOpenApiUrl'         => "https://gw.api.alibaba.com/openapi"
        );
        break;
    case Env::TEST;
    case Env::DEVELOPMENT;
        return array(
            'appKey'                    => '29851206',
            'appSecret'                 => 'xgB3ssH824',
            'gatewayAuthorizeUrl'       => 'http://gw.api.alibaba.com/auth/authorize.htm',     
            'gatewayOpenApiUrl'         => "https://gw.api.alibaba.com/openapi"
        );
        break;
}
?>
