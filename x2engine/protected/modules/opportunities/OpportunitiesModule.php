<?php

/**
 * @package application.modules.opportunities 
 */
class OpportunitiesModule extends CWebModule {
	public function init() {
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'opportunities.models.*',
			'opportunities.components.*',
			// 'application.controllers.*',
			'application.components.*',
		));
	}
}
