<?php

/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




/**
 * CModelBehavior class for route lookups on classes.
 *
 * LinkableBehavior is a CModelBehavior which allows consistent lookup of Yii
 * routes, HTML links and autcomplete sources.
 *
 * @package application.components
 * @property string $module The module this record "belongs" to
 * @property string $baseRoute The default module/controller path for this record's module
 * @property string $viewRoute The default action to view this record
 * @property string $autoCompleteSource The action to user for autocomplete data
 */
class LinkableBehavior extends CActiveRecordBehavior {

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

        if (!isset($this->viewRoute)) {
            $this->viewRoute = $this->baseRoute;
        }

		if(!isset($this->autoCompleteSource))
			$this->autoCompleteSource = 
                $this->baseRoute.'/getItems?modelType='.get_class ($this->owner);
	}

    public function getViewRoute () {
        if (Yii::app()->params->isMobileApp) {
            return $this->viewRoute . '/mobileView';
        } else {
            return $this->viewRoute;
        }
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
        // Use the controller
        if(!ResponseUtil::isCli() && Yii::app()->controller instanceof CController && !Yii::app()->controller instanceof ProfileController) {
            $url = Yii::app()->controller->createAbsoluteUrl($this->getViewRoute (), array('id' => $this->owner->id));
        }
        if(empty($url)) { // Construct an absolute URL; no web request data available.
            $url = Yii::app()->absoluteBaseUrl.(YII_UNIT_TESTING?'/index-test.php':'/index.php').$this->getViewRoute ().'/'.$this->owner->id;
        }
        return $url;
    }

    /**
     * @return array keys corresponding to names of record types which are linkable from within 
     *  X2Touch
     */
    private static $_mobileLinkableRecordTypes;
    public static function getMobileLinkableRecordTypes () {
        if (!isset (self::$_mobileLinkableRecordTypes)) {
            self::$_mobileLinkableRecordTypes = array_flip (array_merge (array (
                'Contacts',
                'Accounts',
                'X2Leads',
                'Opportunity',
                'User',
                'Product',
                'Quote',
                'BugReports',
                'Services',
            ), Yii::app()->db->createCommand ("
                select name
                from x2_modules
                where custom and name
            ")->queryColumn ()));
        }
        return self::$_mobileLinkableRecordTypes;
    }

    public static function isMobileLinkableRecordType ($type) {
        $mobileLinkableRecordTypes = self::getMobileLinkableRecordTypes ();
        return isset ($mobileLinkableRecordTypes[$type]);
    }

    /**
	 * Generates a link to the view of the object.
	 *
	 * @return string a link to the model
	 */
	public function getUrlLink($htmlOptions=array ()) {
        if (Yii::app()->params->isMobileApp && 
            !self::isMobileLinkableRecordType (get_class ($this->owner))) {

            return $this->owner->renderAttribute ('name');
        }

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
    public static function getItems($term, $valueAttr='name', $nameAttr='id', $modelClass=null) {
        if (!$modelClass)
            $modelClass = Yii::app()->controller->modelClass;
        $model = X2Model::model($modelClass);

        if (isset($model)) {
            $modelClass::checkThrowAttrError (array ($valueAttr, $nameAttr));
            $tableName = $model->tableName();
            $qterm = $term . '%';
            $params = array (
                ':qterm' => $qterm,
            );
            $sql = "
                SELECT $nameAttr as id, $valueAttr as value 
                FROM " . $tableName . " as t
                WHERE $valueAttr LIKE :qterm";
            if ($model->asa ('permissions')) {
                list ($accessCond, $permissionsParams) = $model->getAccessSQLCondition ();
                $sql .= ' AND '.$accessCond;
                $params = array_merge ($params, $permissionsParams);
            }
                
            $sql .= "ORDER BY $valueAttr ASC";
            $command = Yii::app()->db->createCommand($sql);
            $result = $command->queryAll(true, $params);
            echo CJSON::encode($result);
        }
        Yii::app()->end();
    }

    /**
     * Improved version of getItems which enables use of empty search string, pagination, and
     * configurable option values/names.
     * @param string $prefix name prefix of items to retrieve
     * @param int $page page number of results to retrieve (ignored if limit is -1)
     * @param int $limit max number of results to retrieve (-1 to disable limit)
     * @param string|array $valueAttr attribute(s) used to popuplate the option values. If an 
     *  array is passed, value will composed of values of each of the attributes specified, joined
     *  by commas
     * @param string $nameAttr attribute used to popuplate the option names
     * @return array name, value pairs
     */
    public function getItems2 (
        $prefix='', $page=0, $limit=20, $valueAttr='name', $nameAttr='name') {

        $modelClass = get_class ($this->owner);
        $model = CActiveRecord::model ($modelClass);
        $table = $model->tableName ();
        $offset = intval ($page) * intval ($limit);

        AuxLib::coerceToArray ($valueAttr);
        $modelClass::checkThrowAttrError (array_merge ($valueAttr, array ($nameAttr)));
        $params = array ();
        if ($prefix !== '') {
            $params[':prefix'] = $prefix . '%';
        }
        if ($limit !== -1) {
            $offset = abs ((int) $offset);
            $limit = abs ((int) $limit);
            $limitClause = "LIMIT $offset, $limit";
        } 

        if ($model->asa ('permissions')) {
            list ($accessCond, $permissionsParams) = $model->getAccessSQLCondition ();
            $params = array_merge ($params, $permissionsParams);
        }

        $command = Yii::app()->db->createCommand ("
            SELECT " . implode (',', $valueAttr) . ", $nameAttr as __name
            FROM $table as t
            WHERE " . ($prefix === '' ? 
               '1=1' : ($nameAttr . ' LIKE :prefix')
            ) . (isset ($accessCond) ? " AND $accessCond" : '') . "
            ORDER BY __name
            ". (isset ($limitClause) ? $limitClause : '') ."
        ");
        $rows = $command->queryAll (true, $params);

        $items = array ();
        foreach ($rows as $row) {
            $name = $row['__name'];
            unset ($row['__name']);
            $items[] = array ($name, $row);
        }

        return $items;
    }

    /**
     * Sets the {@link module} property
     * @param string $value
     */
    public function setModule($value) {
        $this->_module = $value;
    }

}
