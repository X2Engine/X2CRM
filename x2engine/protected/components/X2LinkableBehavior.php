<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/


/**
 * CModelBehavior class for route lookups on classes.
 *
 * X2LinkableBehavior is a CModelBehavior which allows consistent lookup of Yii
 * routes, HTML links and autcomplete sources.
 *
 * @package X2CRM.components
 * @property string $module The module this record "belongs" to
 * @property string $baseRoute The default module/controller path for this record's module
 * @property string $viewRoute The default action to view this record
 * @property string $autoCompleteSource The action to user for autocomplete data
 */
class X2LinkableBehavior extends CActiveRecordBehavior {

	public $module;
	public $baseRoute;
	public $viewRoute;
	public $autoCompleteSource;
	public $icon;

	/**
	 * Attaches the behavior object to the model.
	 *
	 * @param string $owner The component to which the behavior will be applied
	 */
	public function attach($owner) {

		parent::attach($owner);

		if(!isset($this->module)){
			if(!Yii::app()->params->noSession){
				if(isset($this->baseRoute))
					$this->module = preg_replace('/\/.*/', '', preg_replace('/^\//', '', $this->baseRoute)); // try to extract it from $baseRoute (old custom modules)
				else
					$this->module = strtolower(get_class($this->owner));  // assume the model name is the same as the controller
			} else {
				if(!isset($this->baseRoute,$this->autoCompleteSource))
					throw new Exception('Class '.get_class($owner).' has not declared properties "baseRoute" or "autoCompleteSource" for using X2LinkableBehavior, yet neither has it declared "module". There is thus no way of resolving links.');
			}
		}

		if(!isset($this->baseRoute))
			$this->baseRoute = '/'.$this->module;

		if(!isset($this->viewRoute))
			$this->viewRoute = $this->baseRoute;

		if(!isset($this->autoCompleteSource))
			$this->autoCompleteSource = $this->baseRoute.'/getItems';
	}

	/**
	 * Generates a url to the view of the object.
	 *
	 * @return string a url to the model
	 */
	public function getUrl() {

		//$url = array($this->viewRoute.'/'.$this->owner->id);
		$url = null;
		if(Yii::app()->hasProperty('controller')) // Use the controller
			if((bool) Yii::app()->controller)
				$url = Yii::app()->controller->createAbsoluteUrl ($this->viewRoute,array('id'=>$this->owner->id));
		if(empty($url)) // Construct an absolute URL; no web request data available.
			$url = Yii::app()->absoluteBaseUrl.'/index.php'.$this->viewRoute.'/'.$this->owner->id;
		return $url;
	}

	/**
	 * Generates a link to the view of the object.
	 *
	 * @return string a link to the model
	 */
	public function getUrlLink() {
		$name = ($this->owner->hasAttribute('name') || method_exists ($this->owner, 'getName')) ? $this->owner->name : '';
		if($name == '')
			$name = $this->owner->hasAttribute('id') ? '&#35;'.$this->owner->id : '';

		$url = $this->url;
	        if($this->owner instanceof Contacts){
        	    return CHtml::link('<span>'.$name.'</span>',$url,array('class'=>'contact-name'));
	        }else{
        	    return CHtml::link('<span>'.$name.'</span>',$url);
	        }
	}

	/**
	 * Generates a link to the view of the object.
	 *
	 * @return string a link to the model
	 */
	public function getLink() {
		return $this->urlLink;
	}

	/**
	 * @return string a link to the model view, or just the name if no ID is set
	 */
	public function createLink() {
		if(isset($this->owner->id))
			return $this->getLink();
		else
			return $this->owner->name;
    }

	/**
	 * Accessor method for $autoCompleteSource
	 *
	 * @return string $autoCompleteSource
	 */
	public function getAutoCompleteSource() {
		return $this->autoCompleteSource;
	}
}
