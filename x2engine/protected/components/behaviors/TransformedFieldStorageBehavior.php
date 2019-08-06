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
 * Base class for behaviors which store attributes in a different form than
 * when the model and its attributes are loaded.
 *
 * Its purpose is to be used in cases whenever the stored value in the database
 * will be different in some way from the value of the attribute when it is
 * loaded in the model. For example, storing a JSON string in a database record,
 * and having the corresponding model attribute be the decoded JSON object as
 * an associative array. Thus, the transformation is transparent and requires no
 * extra action in the code where the model is being used.
 *
 * In all child classes, methods {@link unpackAttribute()} and
 * {@link packAttribute()} must be inverses of each other. In other words, given
 * a value X, the value returned by unpackAttribute(packAttribute(X)) should be
 * identical to X. This ensures that the storage of the data does not modify the
 * data, one critical requirement of all database-driven software. Exceptions
 * can be made only if any loss or addition of data is intentional and stops
 * after a certain number of iterations of packing and unpacking.
 *
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
abstract class TransformedFieldStorageBehavior extends CActiveRecordBehavior {

	/**
	 * Array of attributes to transform.
	 * @var array
	 */
	public $transformAttributes = array();

	/**
	 * If true, specifies that the array {@link transformAttributes} has keys
	 * that refer to the attribute names and values referring to options for
	 * each attribute. Otherwise, it is a simple array containing attribute names.
	 * @var type
	 */
	protected $hasOptions = false;

	/**
	 * In child classes, this method takes the "working"/"unpacked" value of the
	 * attribute, and returns the value that is to be stored in the database.
	 */
	public abstract function packAttribute($name);

	/**
	 * In child classes, this method returns the "working" value, after
	 * retrieval from the database.
	 */
	public abstract function unpackAttribute($name);

	/**
	 * Prepares all attributes for storage
	 */
	public function packAll(){
		$owner = $this->getOwner();
		foreach($this->hasOptions ? array_keys($this->transformAttributes) : $this->transformAttributes as $name){
			$owner->$name = $this->packAttribute($name);
		}
	}

	/**
	 * Prepares all attributes for usage in the code, after database interaction
	 */
	public function unpackAll(){
		$owner = $this->getOwner();
		foreach($this->hasOptions ? 
            array_keys($this->transformAttributes) : $this->transformAttributes as $name){
			 $owner->$name = $this->unpackAttribute($name);
		}
	}

	public function beforeSave($event){
		$this->packAll();
	}

	public function afterSave($event){
		$this->unpackAll();
	}

	public function afterFind($event){
		$this->unpackAll();
	}

}

?>
