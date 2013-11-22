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
 * Search entries management behavior
 * 
 * A CModelBehavior subclass which provides methods for managing entries in 
 * x2_search_keywords and x2_search.
 *
 * @package X2CRM.components
 * @property string $baseRoute The default module/controller this model "belongs" to
 * @property string $viewRoute The default action to view this model
 * @property string $autoCompleteSource The action to user for autocomplete data
 */
class SearchIndexBehavior extends CModelBehavior {

	// public $baseRoute;
	// public $viewRoute;
	// public $autoCompleteSource;
	// public $icon;
	
	public function attach($owner) {
	
		parent::attach($owner);
		
		if(!isset($this->baseRoute))
			$this->baseRoute = '/'.strtolower(get_class($this->owner)); 	// assume the model name is the same as the controller
			
		if(!isset($this->viewRoute))
			$this->viewRoute = $this->baseRoute;
			
		if(!isset($this->autoCompleteSource))
			$this->autoCompleteSource = $this->baseRoute.'/getItems';	// assume the model name is the same as the controller
	}
}








