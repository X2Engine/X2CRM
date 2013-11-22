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
 * X2TimestampBehavior class file.
 * 
 * @package X2CRM.components 
 * X2TimestampBehavior automatically fills in lastUpdated and createDate (if these fields exist)
 */
class X2TimestampBehavior extends CActiveRecordBehavior {

	protected $_hasCreateDate;
	protected $_hasLastUpdated;
	protected $_hasLastActivity;

	protected $_oldCreateDate = null;
	protected $_oldLastUpdated = null;
	protected $_oldLastActivity = null;

	public function attach($owner) {
		parent::attach($owner);
		
		$this->_hasCreateDate = $this->getOwner()->hasAttribute('createDate');
		$this->_hasLastUpdated = $this->getOwner()->hasAttribute('lastUpdated');
		$this->_hasLastActivity = $this->getOwner()->hasAttribute('lastActivity');
	}

	public function afterFind($event) {
		if(!$this->getOwner()->getIsNewRecord()) {	// if we're updating a model, get the old attributes
			if($this->_hasCreateDate)
				$this->_oldCreateDate = $this->getOwner()->createDate;
			
			if($this->_hasLastUpdated)
				$this->_oldLastUpdated = $this->getOwner()->lastUpdated;
			
			if($this->_hasLastActivity)
				$this->_oldLastActivity = $this->getOwner()->lastActivity;
		}
	}

	/**
	* Responds to {@link CModel::onBeforeValidate} event.
	* Sets the values of the creation or modified attributes as configured.
	* Does not set the value if a value has already been set manually (i.e., the current value != the original value, or the new value is null)
	*
	* @param CModelEvent $event event parameter
	*/
	public function beforeValidate($event) {
		if($this->_hasCreateDate && ($this->getOwner()->getIsNewRecord() && $this->getOwner()->createDate === null))	// only fill createDate if we're creating it
			$this->getOwner()->createDate = time();
		
		if($this->_hasLastUpdated && ($this->getOwner()->lastUpdated === null || $this->getOwner()->lastUpdated === $this->_oldLastUpdated))
			$this->getOwner()->lastUpdated = time();
		
		if($this->_hasLastActivity && ($this->getOwner()->lastActivity === null || $this->getOwner()->lastActivity === $this->_oldLastActivity))
			$this->getOwner()->lastActivity = time();
	}

	/**
	* Manually sets the lastActivity attribute
	*
	* @param CModelEvent $event event parameter
	*/
	public function updateLastActivity() {
		if($this->_hasLastActivity) {
			// $this->getOwner()->disableBehavior('changelog');
			$this->getOwner()->lastActivity = time();
			$this->getOwner()->update(array('lastActivity'));	// don't use save() so it doesn't trigger beforeValidate()
			// $this->getOwner()->enableBehavior('changelog');
		}
	}
}