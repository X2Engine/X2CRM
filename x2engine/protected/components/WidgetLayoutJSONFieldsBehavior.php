<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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

Yii::import('application.components.util.ArrayUtil');
Yii::import('application.components.sortableWidget.*');

/**
 * Allows nested JSON structures with default values to be declared. The JSON structures must be 
 * specified as the property of some class. To use this class, the structure of the
 * transformAttributes field of the behavior configuration array should looks as follows:
 *
 *  'transformAttributes' => array (
 *     <widget name> => array (
 *          'widgetClass' => <widget class name>
 *      )
 *  )
 * 
 * The class called <widget class name> must have a static method called 
 * getJSONPropertiesStructure () which returns the structure of the JSON field.
 * 
 * @package X2CRM.components
 */
class WidgetLayoutJSONFieldsBehavior extends JSONFieldsBehavior {

	protected $_fields;

	/**
	 * Returns an array defining the expected structure of the JSON-bearing
	 * attribute specified by $name.
	 *
	 * @param $name
	 * @return type
	 */
	public function fields($name) {
		if(!isset($this->_fields)) {
			$this->_fields = array();
			foreach($this->transformAttributes as $attr => $fields) {
                $this->_fields[$attr] = array ();
                if (!is_array ($fields)) continue;

                // get JSON structure from widget class property
			    foreach($fields as $widgetName) {
                    if (method_exists ($widgetName, 'getJSONPropertiesStructure')) {
    				    $this->_fields[$attr][$widgetName] = 
                            $widgetName::getJSONPropertiesStructure ();
                    } 
                }
            }
		}
		return $this->_fields[$name];
	}

	/**
	 * Normalizes the attribute array to the structure defined in {@link fields}
	 * and then JSON-encodes it to prepare it for saving. Unlike in JSONFieldsBehavior, 
     * array normalization is performed recursively on array elements.
     *
	 * @param type $name
	 * @return type
	 */
	public function packAttribute($name){
		$fields = $this->fields($name);
		$attribute = $this->getOwner()->$name;
        $attribute = is_array($attribute) ? 
            ArrayUtil::normalizeToArrayR ($fields,$attribute) : $fields;
		return CJSON::encode ($attribute);
	}

	/**
	 * JSON-decodes the value stored in the database column for the attribute,
	 * and then normalizes it to the structure defined in {@link fields}
	 * Unlike in JSONFieldsBehavior, array normalization is performed recursively on array 
     * elements.
     *
	 * @param string $name The attribute to be unpacked
	 * @return type
	 */
	public function unpackAttribute($name){
		$fields = $this->fields($name);
		$attribute = CJSON::decode ($this->getOwner()->$name);
		$attribute = is_array($attribute) ? 
            ArrayUtil::normalizeToArrayR ($fields,$attribute) : $fields;
		return $attribute;
	}
}

?>
