<?php
/**
 * @package Ueb.modules.services
 * 
 * @author Bob <zunfengke@gmail.com>
 */
class ServicesModule extends CWebModule {

    public function init() {
        // import the module-level models and components
        $this->setImport(array(
            'services.models.*',
            'services.components.*',
            // 'application.controllers.*',
            'application.modules.logistics.models.*',
            'application.components.*',
        	'application.modules.products.models.*',
        	'application.modules.systems.models.*',
        	'application.modules.purchases.models.*',
            'application.modules.warehouses.models.*',
        ));
    }

}
