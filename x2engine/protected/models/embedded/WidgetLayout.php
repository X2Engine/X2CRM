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




abstract class WidgetLayout extends JSONEmbeddedModel {

    /**
     * @var string $alias
     */
    protected $alias; 

    protected $_fields;

    public $whitelist;

    protected function widgetOrder () {
        return array ();
    }

    /**
     * Ensures that each subarray in $currentFields corresponds to a JSON properties structure
     * definition defined in some SortableWidget subclass. 
     *
	 * @param array $expectedFields The array with key => default value pairs
	 * @param array $currentFields The array to copy values from
	 * @return array
     */
    private function normalizeToWidgetJSONPropertiesStructures ($expectedFields, $currentFields) {
        $fields = array ();
        foreach ($currentFields as $key => $val) {
            // widget class name can optionally be followed by a sequence of digits. This is
            // used for widget cloning
            $widgetClassName = preg_replace ("/_\w+$/", '', $key);
            if (is_array ($val) && isset ($currentFields[$key]) && 
                isset ($expectedFields[$widgetClassName]) &&
                is_array ($expectedFields[$widgetClassName])) {

                // JSON property structure definitions can be nested 
                $fields[$key] = ArrayUtil::normalizeToArrayR (
                    $expectedFields[$widgetClassName], $currentFields[$key]);
            } 
        }

        foreach ($expectedFields as $widgetClassName => $val) {
            if (!$widgetClassName::$createByDefault) {
                continue;
            }

            if (!isset ($fields[$widgetClassName])) {
                $fields[$widgetClassName] = $expectedFields[$widgetClassName];
            }
        }

        return $fields;
    }
        

	/**
	 * Returns an array defining the expected structure of the JSON-bearing
	 * attribute 
	 * @return array
	 */
	public function fields() {
		if(!isset($this->_fields)) {
			$this->_fields = array();

            // get expected fields from contents of widget directory
            $that = $this;
            $widgetClasses = array_map (function ($file) {
                return preg_replace ('/\.php$/', '', $file);
            }, array_filter (
                scandir(Yii::getPathOfAlias($this->alias)), function ($file) use ($that){

                return preg_match ('/\.php$/', $file) && 
                    (!$that->whitelist ||
                    in_array (preg_replace ('/.php$/', '', $file), $that->whitelist));
            }));

            // get JSON structure from widget class property
            $unordered = array ();
            foreach($widgetClasses as $widgetName) {
                if (method_exists ($widgetName, 'getJSONPropertiesStructure')) {
                    $unordered[$widgetName] = 
                        $widgetName::getJSONPropertiesStructure ();
                } 
            }
            $orderedFields = array ();
            if ($this->widgetOrder ()) {
                $widgetOrder = $this->widgetOrder ();
                foreach ($widgetOrder as $widgetName) {
                    $orderedFields[$widgetName] = $unordered[$widgetName];
                }
            }
            foreach (array_diff ($widgetClasses, array_keys ($orderedFields)) as $widgetName) {
                $orderedFields[$widgetName] = $unordered[$widgetName];
            }
            $this->_fields = $orderedFields;
		}
		return $this->_fields;
	}

    /**
     * Removes fields which have JSON properties structures (for the purposes of array 
     * normalization) but which should not be saved
     */
    private function removeExcludedFields (&$attribute) {
        // Templates Summary can be in saved json object but should not be added by default.
        // This is because templates summaries can be created but don't exist by default 
        $excludeList = array (
            'TemplatesGridViewProfileWidget',
            'TransactionalViewWidget',
            'RecordViewWidget',
        );
        $attribute = array_diff_key ($attribute, array_flip ($excludeList));
    }

	/**
     * Normalize attribute to properties array structures defined in widget classes
	 * @return string
	 */
    private $_attributes = null;
	public function setAttributes ($values, $safeOnly=true){
		$fields = $this->fields();
        $attribute = is_array ($values) ? 
		    $this->normalizeToWidgetJSONPropertiesStructures ($fields, $values) : $fields; 
        $this->removeExcludedFields ($attribute);
        $this->_attributes = $attribute;
	}

	/**
     * Normalize attribute to properties array structures defined in widget classes
	 * @return $attribute
	 */
    public function getAttributes ($names=null) {
		$fields = $this->fields();
        $exoAttr = $this->exoAttr;
		$attribute = $this->_attributes;
        $attribute = is_array ($attribute) ? 
		    $this->normalizeToWidgetJSONPropertiesStructures ($fields, $attribute) : $fields; 
        $this->removeExcludedFields ($attribute);
		return $attribute;
    }

}

?>
