<?php
/**
 * @package Ueb.modules.services
 * 
 * @author Bob <zunfengke@gmail.com>
 */
class EbayModule extends CWebModule {

    public function init() {
        // import the module-level models and components
        $this->setImport(array(
            'ebay.models.*',
            'ebay.components.*',
            // 'application.controllers.*',
            'ebay.components.*',
            'application.vendors.ebay.*',
        	'application.modules.orders.models.*',
        ));
    }

}
