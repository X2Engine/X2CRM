<?php

/**
 * @package application.modules.quotes
 * @author David Visbal
 */
class QuotesModule extends X2WebModule {

    private $_assetsUrl = null;

    public function getAssetsUrl() {
        if ($this->_assetsUrl === null)
            $this->_assetsUrl = Yii::app()->getAssetManager()->publish(
                Yii::getPathOfAlias('application.modules.quotes.assets'), false, -1, true );
        return $this->_assetsUrl;
    }

	public function init() {
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'quotes.models.*',
			'quotes.components.*',
			// 'application.controllers.*',
			'application.components.*',
		));
	}
}
