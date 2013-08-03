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
 * A behavior for controllers; contains methods common to controllers whether or
 * not they are children of x2base.
 *
 * All controllers that use this behavior must declare the "modelClass" property.
 *
 * @property X2Model $model (read-only); in the context of viewing or updating a
 *	record, this contains the active record object corresponding to that record.
 *	Its value is set by calling {@link getModel()} with the ID of the desired record.
 * @package X2CRM.components
 */
class CommonControllerBehavior extends CBehavior {

	/**
	 * Stores the value of {@link $model}
	 */
	private $_model;

	public function attach($owner){
		if(!property_exists($owner,'modelClass'))
			throw new CException('Property "modelClass" must be declared in all controllers that use CommonControllerBehavior, but it is not declared in '.get_class($owner));
		parent::attach($owner);
	}
	
	/**
	 * Renders Google Analytics tracking code, if enabled.
	 * 
	 * @param type $location Named location in the app 
	 */
	public function renderGaCode($location) {
		$propertyId = Yii::app()->params->admin->{"gaTracking_" . $location};
		if (!empty($propertyId))
			$this->owner->renderPartial('application.components.views.gaTrackingScript', array('propertyId' => $propertyId));
	}

	/**
	 * Obtain the IP address of the current web client.
	 * @return string
	 */
	function getRealIp() {
		foreach(array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR'
		) as $var) {
			if(array_key_exists($var,$_SERVER)){
				foreach(explode(',',$_SERVER[$var]) as $ip) {
					$ip = trim($ip);
					if(filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
						return $ip;
				}
			}
		}
		return false;
	}

	/**
	 * Returns the data model based on the primary key given.
	 *
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded. Note, it is assumed that
	 *	when this value is null, {@link _model} is set already; otherwise there's
	 *	nothing that can be done to correctly resolve the model.
	 * @param bool $throw Whether to throw a 404 upon not finding the model
	 */
	public function getModel($id=null,$throw=true){
		if(!isset($this->_model)) {
			if($this->owner->modelClass == 'Admin' && empty($id)) // Special case for Admin: let the ID be the one and only record if unspecified
				$id = 1;
			if(empty($id)) // ID was never specified, so there's no way to tell which record to load.
				$this->owner->redirect(array('index'));
			else { // ID was specified, so load it.
				$this->_model = CActiveRecord::model($this->owner->modelClass)->findByPk((int) $id);
			}
			if($this->_model === null && $throw) // Model record couldn't be retrieved
				throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
		} else if($this->_model->id != $id && !empty($id)) { // A different record is being requested
			// Change the ID and load the different record.
			// Note that setting the attribute to null will cause isset to return false.
			$this->_model = null;
			return $this->getModel($id);
		}
		return $this->_model;
	}

	/**
	 * Kept for backwards compatibility with controllers (including custom ones)
	 * that use loadModel.
	 *
	 * @return type
	 */
	public function loadModel($id) {
		return $this->getModel($id);
	}

}

?>
