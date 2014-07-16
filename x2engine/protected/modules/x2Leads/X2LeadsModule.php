<?php

/**
 * @package application.modules.x2Leads 
 */
class X2LeadsModule extends CWebModule {
	public function init() {
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'x2Leads.models.*',
			'x2Leads.components.*',
			// 'application.controllers.*',
			'application.components.*',
		));
	}
}
