<?php
/**
 * @package Ueb.modules.commons
 * 
 * @author Bob <Foxzeng>
 */
class CommonsModule extends CWebModule {

    public function init() {
        // import the module-level models and components
        $this->setImport(array(
            'commons.models.*',
            'commons.components.*',
            // 'application.controllers.*',
            'application.components.*',
        ));
    }

}
