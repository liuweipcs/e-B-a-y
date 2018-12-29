<?php
/**
 * @package Ueb.modules.users
 * 
 * @author Bob <Foxzeng>
 */
class UsersModule extends CWebModule {

    public function init() {
        // import the module-level models and components
        $this->setImport(array(
            'users.models.*',
            'users.components.*',
            // 'application.controllers.*',
            'application.components.*',
        ));
    }

}
