<?php

/**
 * @package application.modules.marketing 
 */
class MarketingModule extends X2WebModule {

	public function init() {
		$this->setImport(array(
			'marketing.models.*',
			'marketing.components.*',
			// 'application.controllers.*',
			'application.components.*',
		));
	}
}
