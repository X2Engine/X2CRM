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
 * @package X2CRM.components
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
		foreach($this->hasOptions ? array_keys($this->transformAttributes) : $this->transformAttributes as $name){
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
