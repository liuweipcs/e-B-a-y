<?php
/**
 * ebay keys config
 * 
 * @author Bob <Foxzeng>
 */
switch (YII_ENV) {
    case Env::PRODUCTION;
        return array(
            'devID'                     => '5559c098-3fa1-4f35-95f6-8df1204badd0',
            'appID'                     => 'liveinlo-cbe7-46b1-9077-bf03f19be855',
            'certID'                    => '044d5a35-ad9c-4500-9fd9-821fd066af84',
            'serverUrl'                 => 'https://api.ebay.com/ws/api.dll',
            'compatabilityLevel'        => 849, // eBay API version
            'resolutionsEndpoints'     => "https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService"
        );
        break;
    case Env::TEST;
    case Env::DEVELOPMENT;
        return array(
            'devID'                     => '433b57c3-cc37-4d73-a28d-8cc33791bb4',
            'appID'                     => 'vakindd80-38d6-46c2-9b38-14d6cfd4c64',
            'certID'                    => '97ee6168-6492-4e95-844b-1e15afdf907e',
            'serverUrl'                 => 'https://api.ebay.com/ws/api.dll',
            'compatabilityLevel'        => 849, // eBay API version
            'resolutionsEndpoints'     => "https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService"
        );
        break;
}
?>
