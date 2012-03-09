<?php

class ProductsModule extends CWebModule
{
	public function init()
	{
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'products.models.*',
			'products.components.*',
                        'application.controllers.*',
                        'application.components.*',
		));
                $trueWebRoot=explode('/',Yii::app()->params->trueWebRoot);
                unset($trueWebRoot[count($trueWebRoot)-1]);
                $trueWebRoot=implode('/',$trueWebRoot);
                $this->layout = 'webroot.themes.'.Yii::app()->theme->name.'.views.layouts.column2';
	}

	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			$controller->layout=$this->layout;
			return true;
		}
		else
			return false;
	}
}
