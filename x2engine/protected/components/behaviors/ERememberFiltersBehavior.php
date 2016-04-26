<?php
/**
 * ERememberFiltersBehavior class.
 * The ERememberFiltersBehavior extension adds up some functionality to the default
 * possibilites of CActiveRecord/Model implementation.
 *
 * It will detect the search scenario and it will save the filters from the GridView.
 * This comes handy when you need to store them for later use. For heavy navigation and
 * heavy filtering this function can be activated by just a couple of lines.
 *
 * To use this extension, just copy this file to your components/ directory,
 * add 'import' => 'application.components.behaviors.ERememberFiltersBehavior', [...] to your
 * config/main.php and paste the following code to your behaviors() method of your model
 *
 * public function behaviors() {
 *        return array(
 *            'ERememberFiltersBehavior' => array(
 *                'class' => 'application.components.behaviors.ERememberFiltersBehavior',
 *                'defaults'=>array(),
 *                'defaultStickOnClear'=>false 
 *            ),
 *        );
 * }
 * 
 * 'defaults' is a key value pair array, that will hold the defaults for your filters. 
 * For example when you initially want to display `active products`, you set to array('status'=>'active'). 
 * You can of course put multiple default values.
 *
 * 'defaultStickOnClear'=>true can be used, if you want the default values to be put back when the user clears the filters
 * The default set is 'false' so if the user clears the filters, also the defaults are cleared out.
 *
 * You can use `scenarios` to remember the filters on multiple states of the same model. This is helpful when you use the same 
 * model on different views and you want to remember the state separated from each other.
 * Known limitations: the views must be in different actions (not on the same view)
 *
 * To set a scenario add the set call after the instantiation
 * Fragment from actionAdmin():
 *
 * $model=new Persons('search');
 * $model->setRememberScenario('scene1');
 *
 *
 * CHANGELOG
 *
 * 2011-06-02
 * v1.2 
 * Added support for 'scenarios'. 
 * You can now tell your model to use custom scenario. 
 *
 * 2011-03-06
 * v1.1 
 * Added support for 'defaults' and 'defaultStickOnClear'. 
 * You can now tell your model to set default filters for your form using this extension.
 *
 * 2011-01-31
 * v1.0 
 * Initial release
 *
 * This extension has also a pair Clear Filters Gridview
 * http://www.yiiframework.com/extension/clear-filters-gridview
 *
 * Please VOTE this extension if helps you at:
 * http://www.yiiframework.com/extension/remember-filters-gridview
 * 
 * @author Marton Kodok http://www.yiiframework.com/forum/index.php?/user/8824-pentium10/
 * @link http://www.yiiframework.com/
 * @version 1.2
 * @package application.components
 */
/* x2modstart */
Yii::import ('application.components.X2Settings.X2Settings');
Yii::import ('application.components.behaviors.GridViewDbSettingsBehavior');
Yii::import ('application.components.behaviors.GridViewSessionSettingsBehavior');
/* x2modend */
class ERememberFiltersBehavior extends CActiveRecordBehavior {

    /* x2modstart */ 
	/**
     * Feature disabled for simplicity's sake. Property kept around to support legacy code.
	 * @var array
	 */
	public $defaults = array();
    /* x2modend */ 

    /**
     * @var $disablePersistentGridSettings
     */
    private $_disablePersistentGridSettings; 

	/**
	 * When this flag is true, the default values will be used also when the user clears the filters
	 *
	 * @var boolean
	 */
	public $defaultStickOnClear = false;

    public function attach($owner) {
        parent::attach ($owner);
        $this->_disablePersistentGridSettings = 
            isset ($this->owner->disablePersistentGridSettings) ? 
            $this->owner->disablePersistentGridSettings : false;
        $this->attachBehaviors (array (
            'settingsBehavior' => array ('class' => 
                (!isset ($this->owner->dbPersistentGridSettings) || 
                 $this->owner->dbPersistentGridSettings ? 
                    'GridViewDbSettingsBehavior' :
                    'GridViewSessionSettingsBehavior'),
                'uid' => property_exists ($this->owner, 'uid') ? $this->owner->uid : null,
                'modelClass' => get_class ($this->owner),
            )
        ));
    }

    /**
     * @return array filters set for this model 
     */
    public function getGridFilters () {
        if (!$this->_disablePersistentGridSettings) return $this->getSetting ('filters');
    }

    /**
     * Saves grid filters to session/db 
     * @param array $filters attr values indexed by attr name
     */
    public function setGridFilters ($filters) {
        if (!$this->_disablePersistentGridSettings) $this->saveSetting ('filters', $filters);
    }

    /**
     * Set model attributes (if scenario is 'search') and clear filters 
     * (if clearFilters param is set).
     */
	public function afterConstruct($event) {
		if(intval(Yii::app()->request->getParam('clearFilters')) == 1) {
			$this->unsetAllFilters();
			if(isset($_GET['id'])) {
				Yii::app()->controller->redirect(
                    array(
                        Yii::app()->controller->action->ID,
                        'id' => Yii::app()->request->getParam('id')));
			} else {
				Yii::app()->controller->redirect(array(Yii::app()->controller->action->ID));
            }
		}
        $this->doReadSave();
	}

	/**
	 * Method is called when we need to unset the filters
	 *
	 * @return owner
	 */
	public function unsetAllFilters() {
		$modelName = get_class($this->owner);
		$attributes = $this->owner->getSafeAttributeNames();

        $filters = $this->getGridFilters ();
        if (is_array ($filters)) {
		    foreach($attributes as $attribute) {
                unset ($filters[$attribute]);
            }
        }
        $this->setGridFilters ($filters);
		return $this->owner;
	}

    public function getId () {
        if (isset ($this->owner->uid)) return $this->owner->uid;
        return get_class ($this->owner);
    }

    /**
     * Save models attributes as filters
     */
	private function saveSearchValues() {
		$modelName = get_class($this->owner);
		$attributes = $this->owner->getSafeAttributeNames();
        $filters = array ();
		foreach($attributes as $attribute) {
			if(isset($this->owner->$attribute)) {
                $filters[$attribute] = $this->owner->$attribute;
            }
		}
        $this->setGridFilters ($filters);
	}

    /**
     * Set owner attributes either with GET params or saved filters
     */
	private function doReadSave() {
		if($this->owner->scenario === 'search') {
			$this->owner->unsetAttributes();

            /* x2tempstart */ 
            // violates abstraction by referring to internals of SmartDataProviderBehavior
            // also doesn't belong here since it has to do with sorting, not filtering
            //
            // if sort order is explicitly set to empty string, remove it
            $sortKey = $this->getId () . '_sort';
            if (in_array ($sortKey, array_keys ($_GET)) && isset ($_GET[$sortKey]) &&
                $_GET[$sortKey] === '') {

                unset ($_GET[$sortKey]);
                if (!$this->owner->disablePersistentGridSettings) $this->saveSetting ('sort', '');
            }
            /* x2tempend */ 

			if(isset($_GET[get_class($this->owner)])) { 
                // grid refresh, set attributes with GET params

				$this->owner->attributes = $_GET[get_class($this->owner)];
				$this->saveSearchValues();
			} else { // initial page load, set attributes with saved filters
				$this->readSearchValues();
			}
		}
	}

    /**
     * Set owner attributes with saved filters
     */
	private function readSearchValues() {
		$modelName = get_class($this->owner);
		$attributes = $this->owner->getSafeAttributeNames();

        $filters = $this->getGridFilters ();
        if (is_array ($filters)) {
		    foreach($attributes as $attribute) {
                if (isset ($filters[$attribute])) {
                    try {
                        $this->owner->$attribute = $filters[$attribute];
                    } catch (Exception $e) {
                    }
                }
            }
        }
	}
}
?>
