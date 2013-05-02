<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

 
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
		
		if(!isset($this->module)) {
			if(isset($this->baseRoute))
				$this->module = preg_replace('/\/.*/','',preg_replace('/^\//','',$this->baseRoute));	// try to extract it from $baseRoute (old custom modules)
			else
				$this->module = strtolower(get_class($this->owner)); 	// assume the model name is the same as the controller
		}
		
		if(!isset($this->baseRoute))
			$this->baseRoute = '/'.$this->module;
			
		if(!isset($this->viewRoute))
			$this->viewRoute = $this->baseRoute;
			
		if(!isset($this->autoCompleteSource))
			$this->autoCompleteSource = $this->baseRoute.'/getItems';
	}

	/**
	 * Generates a link to the view of the object.
	 * 
	 * @return string a link to the model
	 */
	public function getLink() {
	
		$tag = '<span>';
		// if(!empty($this->icon))
			// $tag = '<span style="background-image:url('.Yii::app()->theme->baseUrl.'/images/'.$this->icon.');">';
        if($this->owner->hasAttribute('name')){
            return CHtml::link($tag.($this->owner->name==''?('&#35;'.$this->owner->id):($this->owner->name)).'</span>',
                // array($this->viewRoute.'/'.$this->owner->id),array('class'=>'x2-link'));
                array($this->viewRoute.'/'.$this->owner->id));
        }else{
            return '';
        }
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