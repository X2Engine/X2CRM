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
 * TimestampBehavior class file.
 * 
 * @package application.components 
 * TimestampBehavior automatically fills in lastUpdated and createDate (if these fields exist)
 */
class TimestampBehavior extends CActiveRecordBehavior {

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