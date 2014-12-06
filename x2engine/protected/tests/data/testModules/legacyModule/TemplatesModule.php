<?php

/**
 * @package application.modules.template 
 */
class TemplatesModule extends CWebModule {
	public function init() {
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'templates.models.*',
			'templates.components.*',
			// 'application.controllers.*',
			'application.components.*',
		));
	}
}
