<?php

class EbayshippinglocationdownController extends UebController {
    
    public function actiongetlocation() {
        $locationObj = new Ebayshippingservicelocation();
        $response = $locationObj->getshippinglocation();
        echo "ok";
    }
}