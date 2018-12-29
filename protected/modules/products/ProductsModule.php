<?php
/**
 * @package Ueb.modules.products
 * 
 * @author Bob <Foxzeng>
 */
class ProductsModule extends CWebModule {

    public function init() {
        // import the module-level models and components
        $this->setImport(array(
            'products.models.*',
            'products.components.*',
            // 'application.controllers.*', 
            'application.components.*',
            'application.modules.purchases.models.*',
        	'application.modules.warehouses.models.*',
        	'application.modules.orders.models.*',
        	'application.modules.logistics.models.*',
            'application.modules.services.modules.aliexpress.models.*',
   			'application.modules.services.models.ebay.models.*',
            'application.modules.systems.models.*',
            'application.vendors.aliexpress.*',
            'application.vendors.aliexpress.request.*',
			'application.modules.services.components.*',
        	'application.modules.services.modules.wish.components.*',
        	'application.modules.services.modules.shopee.components.*',
        ));
    }

}
