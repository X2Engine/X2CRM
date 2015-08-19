<?php

/**
 * @package application.modules.template 
 */
class TopicsModule extends X2WebModule {
	public function init() {
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'topics.models.*',
			'topics.components.*',
			// 'application.controllers.*',
			'application.components.*',
		));
	}
}
