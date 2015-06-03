<?php

/**
 * @package application.modules.users
 */
class UsersModule extends X2WebModule {
	public function init() {
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'users.models.*',
			'users.components.*',
			// 'application.controllers.*',
			'application.components.*',
		));
	}
}
