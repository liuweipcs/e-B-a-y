<?php
/** 
 *  PHP Version 5
 *
 *  @category    Amazon
 *  @package     FBAInventoryServiceMWS
 *  @copyright   Copyright 2009 Amazon.com, Inc. All Rights Reserved.
 *  @link        http://mws.amazon.com
 *  @license     http://aws.amazon.com/apache2.0  Apache License, Version 2.0
 *  @version     2010-10-01
 */
/******************************************************************************* 
 * 
 *  FBA Inventory Service MWS PHP5 Library
 *  Generated: Fri Oct 22 09:52:21 UTC 2010
 * 
 */

/**
 * List Inventory Supply By Next Token  Sample
 */

include_once ('.config.inc.php'); 

/************************************************************************
* Configuration settings are:
*
* - MWS endpoint URL: it defined in the .config.inc.php located in the 
*                     same directory as this sample.
* - Proxy host and port.
* - MaxErrorRetry.
***********************************************************************/
$config = array (
  'ServiceURL' => MWS_ENDPOINT_URL,
  'ProxyHost' => null,
  'ProxyPort' => -1,
  'MaxErrorRetry' => 3
);

/************************************************************************
 * Instantiate Implementation of FBAInventoryServiceMWS
 * 
 * ACCESS_KEY_ID and SECRET_ACCESS_KEY constants 
 * are defined in the .config.inc.php located in the same 
 * directory as this sample
 ***********************************************************************/
 $service = new FBAInventoryServiceMWS_Client(
     ACCESS_KEY_ID, 
     SECRET_ACCESS_KEY, 
     $config,
     APPLICATION_NAME,
     APPLICATION_VERSION);
 
/************************************************************************
 * Uncomment to try out Mock Service that simulates FBAInventoryServiceMWS
 * responses without calling FBAInventoryServiceMWS service.
 *
 * Responses are loaded from local XML files. You can tweak XML files to
 * experiment with various outputs during development
 *
 * XML files available under FBAInventoryServiceMWS/Mock tree
 *
 ***********************************************************************/
 // $service = new FBAInventoryServiceMWS_Mock();

/************************************************************************
 * Setup request parameters and uncomment invoke to try out 
 * sample for List Inventory Supply By Next Token Action
 ***********************************************************************/
 // @TODO: set request. Action can be passed as FBAInventoryServiceMWS_Model_ListInventorySupplyByNextTokenRequest
 $request = new FBAInventoryServiceMWS_Model_ListInventorySupplyByNextTokenRequest();
 // $request->setSellerId(SELLER_ID);

 // invokeListInventorySupplyByNextToken($service, $request);

                        
/**
  * List Inventory Supply By Next Token Action Sample
  * Continues pagination over a resultset of inventory data for inventory
  * items.
  * 
  * This operation is used in conjunction with ListUpdatedInventorySupply.
  * Please refer to documentation for that operation for further details.
  *   
  * @param FBAInventoryServiceMWS_Interface $service instance of FBAInventoryServiceMWS_Interface
  * @param mixed $request FBAInventoryServiceMWS_Model_ListInventorySupplyByNextToken or array of parameters
  */
  function invokeListInventorySupplyByNextToken(FBAInventoryServiceMWS_Interface $service, $request) 
  {
      try {
              $response = $service->listInventorySupplyByNextToken($request);
              
                echo ("Service Response\n");
                echo ("=============================================================================\n");

                echo("        ListInventorySupplyByNextTokenResponse\n");
                if ($response->isSetListInventorySupplyByNextTokenResult()) { 
                    echo("            ListInventorySupplyByNextTokenResult\n");
                    $listInventorySupplyByNextTokenResult = $response->getListInventorySupplyByNextTokenResult();
                    if ($listInventorySupplyByNextTokenResult->isSetInventorySupplyList()) { 
                        echo("                InventorySupplyList\n");
                        $inventorySupplyList = $listInventorySupplyByNextTokenResult->getInventorySupplyList();
                        $memberList = $inventorySupplyList->getmember();
                        foreach ($memberList as $member) {
                            echo("                    member\n");
                            if ($member->isSetSellerSKU()) 
                            {
                                echo("                        SellerSKU\n");
                                echo("                            " . $member->getSellerSKU() . "\n");
                            }
                            if ($member->isSetFNSKU()) 
                            {
                                echo("                        FNSKU\n");
                                echo("                            " . $member->getFNSKU() . "\n");
                            }
                            if ($member->isSetASIN()) 
                            {
                                echo("                        ASIN\n");
                                echo("                            " . $member->getASIN() . "\n");
                            }
                            if ($member->isSetCondition()) 
                            {
                                echo("                        Condition\n");
                                echo("                            " . $member->getCondition() . "\n");
                            }
                            if ($member->isSetTotalSupplyQuantity()) 
                            {
                                echo("                        TotalSupplyQuantity\n");
                                echo("                            " . $member->getTotalSupplyQuantity() . "\n");
                            }
                            if ($member->isSetInStockSupplyQuantity()) 
                            {
                                echo("                        InStockSupplyQuantity\n");
                                echo("                            " . $member->getInStockSupplyQuantity() . "\n");
                            }
                            if ($member->isSetEarliestAvailability()) { 
                                echo("                        EarliestAvailability\n");
                                $earliestAvailability = $member->getEarliestAvailability();
                                if ($earliestAvailability->isSetTimepointType()) 
                                {
                                    echo("                            TimepointType\n");
                                    echo("                                " . $earliestAvailability->getTimepointType() . "\n");
                                }
                                if ($earliestAvailability->isSetDateTime()) 
                                {
                                    echo("                            DateTime\n");
                                    echo("                                " . $earliestAvailability->getDateTime() . "\n");
                                }
                            } 
                            if ($member->isSetSupplyDetail()) { 
                                echo("                        SupplyDetail\n");
                                $supplyDetail = $member->getSupplyDetail();
                                $member1List = $supplyDetail->getmember();
                                foreach ($member1List as $member1) {
                                    echo("                            member\n");
                                    if ($member1->isSetQuantity()) 
                                    {
                                        echo("                                Quantity\n");
                                        echo("                                    " . $member1->getQuantity() . "\n");
                                    }
                                    if ($member1->isSetSupplyType()) 
                                    {
                                        echo("                                SupplyType\n");
                                        echo("                                    " . $member1->getSupplyType() . "\n");
                                    }
                                    if ($member1->isSetEarliestAvailableToPick()) { 
                                        echo("                                EarliestAvailableToPick\n");
                                        $earliestAvailableToPick = $member1->getEarliestAvailableToPick();
                                        if ($earliestAvailableToPick->isSetTimepointType()) 
                                        {
                                            echo("                                    TimepointType\n");
                                            echo("                                        " . $earliestAvailableToPick->getTimepointType() . "\n");
                                        }
                                        if ($earliestAvailableToPick->isSetDateTime()) 
                                        {
                                            echo("                                    DateTime\n");
                                            echo("                                        " . $earliestAvailableToPick->getDateTime() . "\n");
                                        }
                                    } 
                                    if ($member1->isSetLatestAvailableToPick()) { 
                                        echo("                                LatestAvailableToPick\n");
                                        $latestAvailableToPick = $member1->getLatestAvailableToPick();
                                        if ($latestAvailableToPick->isSetTimepointType()) 
                                        {
                                            echo("                                    TimepointType\n");
                                            echo("                                        " . $latestAvailableToPick->getTimepointType() . "\n");
                                        }
                                        if ($latestAvailableToPick->isSetDateTime()) 
                                        {
                                            echo("                                    DateTime\n");
                                            echo("                                        " . $latestAvailableToPick->getDateTime() . "\n");
                                        }
                                    } 
                                }
                            } 
                        }
                    } 
                    if ($listInventorySupplyByNextTokenResult->isSetNextToken()) 
                    {
                        echo("                NextToken\n");
                        echo("                    " . $listInventorySupplyByNextTokenResult->getNextToken() . "\n");
                    }
                } 
                if ($response->isSetResponseMetadata()) { 
                    echo("            ResponseMetadata\n");
                    $responseMetadata = $response->getResponseMetadata();
                    if ($responseMetadata->isSetRequestId()) 
                    {
                        echo("                RequestId\n");
                        echo("                    " . $responseMetadata->getRequestId() . "\n");
                    }
                } 

     } catch (FBAInventoryServiceMWS_Exception $ex) {
         echo("Caught Exception: " . $ex->getMessage() . "\n");
         echo("Response Status Code: " . $ex->getStatusCode() . "\n");
         echo("Error Code: " . $ex->getErrorCode() . "\n");
         echo("Error Type: " . $ex->getErrorType() . "\n");
         echo("Request ID: " . $ex->getRequestId() . "\n");
         echo("XML: " . $ex->getXML() . "\n");
     }
 }
            