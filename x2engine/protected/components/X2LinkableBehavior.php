<?php

/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * @package application.components
 * @property string $module The module this record "belongs" to
 * @property string $baseRoute The default module/controller path for this record's module
 * @property string $viewRoute The default action to view this record
 * @property string $autoCompleteSource The action to user for autocomplete data
 */
class X2LinkableBehavior extends CActiveRecordBehavior {

    public $baseRoute;
    public $viewRoute;
    public $autoCompleteSource;
    public $icon;

    /**
     * Stores {@link module}
     * @var string
     */
    private $_module;
    
    /**
     * Attaches the behavior object to the model.
     *
     * @param string $owner The component to which the behavior will be applied
     */
    public function attach($owner) {

        parent::attach($owner);

        if ($this->getModule() === null){
            // Resolve the module
            if(isset($this->baseRoute)){
                // Try to extract it from $baseRoute (old custom modules)
                $this->module = preg_replace(
                        '/\/.*/', '', preg_replace('/^\//', '', $this->baseRoute));
            }else{
                // Assume the model name is the same as the module/controller
                // (also true of custom modules)
                $this->module = strtolower(get_class($this->owner));
            }
        }

        if (!isset($this->baseRoute))
            $this->baseRoute = '/' . $this->module;

        if (!isset($this->viewRoute))
            $this->viewRoute = $this->baseRoute;

		if(!isset($this->autoCompleteSource))
			$this->autoCompleteSource = 
                $this->baseRoute.'/getItems?modelType='.get_class ($this->owner);
	}

    /**
     * Gets the {@link module} property.
     * @return string|null
     */
    public function getModule() {
        if(isset($this->_module)) {
            return $this->_module;
        } else if(property_exists($this->owner,'module')) {
            return $this->owner->module;
        } else {
            return null;
        }
    }

	/**
	 * Generates a url to the view of the object.
	 *
	 * @return string a url to the model
	 */
    public function getUrl(){
        $url = null;
        if(Yii::app()->controller instanceof CController) // Use the controller
            $url = Yii::app()->controller->createAbsoluteUrl($this->viewRoute, array('id' => $this->owner->id));
        if(empty($url)) // Construct an absolute URL; no web request data available.
            $url = Yii::app()->absoluteBaseUrl.'/index.php'.$this->viewRoute.'/'.$this->owner->id;
        return $url;
    }

    /**
	 * Generates a link to the view of the object.
	 *
	 * @return string a link to the model
	 */
	public function getUrlLink($htmlOptions=array ()) {
		$name = ($this->owner->hasAttribute('name') || $this->owner->canGetProperty('name') || 
            property_exists($this->owner, 'name')) ? $this->owner->name : '';
		if(trim($name) == '') {
			if ($this->owner->hasAttribute('fileName')) { // for media models
                $name = $this->owner->fileName;
            }
            if(trim($name) == '') {
                $name = $this->owner->hasAttribute('id') ? '#'.$this->owner->id : '';
            }
        }

		$url = $this->url;
	        if($this->owner instanceof Contacts){
        	    return CHtml::link(
                    '<span>'.X2Html::encode($name).'</span>',
                    $url,
                    array_merge (array(
                        'class'=>'contact-name'
                    ), $htmlOptions)
                );
	        }else{
        	    return CHtml::link(
                    '<span>'.X2Html::encode($name).'</span>',
                    $url,
                    $htmlOptions
                );
	        }
	}

	/**
	 * Generates a link to the view of the object.
	 *
	 * @return string a link to the model
	 */
	public function getLink($htmlOptions=array ()) {
		return $this->getUrlLink ($htmlOptions);
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

    /**
     * Get autocomplete options 
     * @param string $term
     */
    public static function getItems($term) {
        $model = X2Model::model(Yii::app()->controller->modelClass);
        if (isset($model)) {
            $tableName = $model->tableName();
            $sql = 'SELECT id, name as value 
                 FROM ' . $tableName . ' WHERE name LIKE :qterm ORDER BY name ASC';
            $command = Yii::app()->db->createCommand($sql);
            $qterm = $term . '%';
            $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
            $result = $command->queryAll();
            echo CJSON::encode($result);
        }
        Yii::app()->end();
    }

    /**
     * Sets the {@link module} property
     * @param string $value
     */
    public function setModule($value) {
        $this->_module = $value;
    }

}
