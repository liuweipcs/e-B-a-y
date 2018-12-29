<?php

/**
 * @package Ueb.modules.systems
 * 
 * @author Bob <Foxzeng>
 */
class SystemsModule extends CWebModule {

    private $_assetsUrl;

    public function init() {
        // import the module-level models and components
        $this->setImport(array(
            'systems.models.*',
            'systems.components.*',
            // 'application.controllers.*',
            'application.components.*',
			'application.modules.products.models.*',
            'application.modules.purchases.models.*',
        	'application.modules.warehouses.models.*',	
        	'application.modules.orders.models.*',
        	'application.modules.logistics.models.*',		
        	'application.modules.logistics.components.*',
        	'application.modules.pda.models.*',		
        	'application.modules.users.models.*',
        	'application.modules.services.models.*',
        	'application.modules.services.components.*',
        	'application.vendors.*',
        	'services.modules.ebay.components.*',
        	'services.modules.ebay.controllers.*',
        	'services.modules.ebay.models.*',
        	'services.modules.warehouse.components.*',
        	'services.modules.warehouse.controllers.*',
        	'services.modules.warehouse.models.*',
        ));
    }

    public function getAssetsUrl() {

        if ($this->_assetsUrl === null)
            $this->_assetsUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.modules.systems.assets'));

        return $this->_assetsUrl;
    }

    public function setAssetsUrl($value) {

        $this->_assetsUrl = $value;
    }

}
