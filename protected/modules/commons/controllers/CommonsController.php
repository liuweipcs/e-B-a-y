<?php
/**
 * @package Ueb.modules.commons.controllers
 * 
 * @author Bob <Foxzeng>
 */
class CommonsController extends UebController {

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array();
	}
    
    public function actionIndex() {
        die('commons');     
    }       

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {}	
}
