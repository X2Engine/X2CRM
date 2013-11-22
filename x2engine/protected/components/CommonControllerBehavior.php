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
 * A behavior for controllers; contains methods common to controllers whether or
 * not they are children of x2base.
 *
 * All controllers that use this behavior must declare the "modelClass" property.
 *
 * @property X2Model $model (read-only); in the context of viewing or updating a
 *	record, this contains the active record object corresponding to that record.
 *	Its value is set by calling {@link getModel()} with the ID of the desired record.
 * @property string $resolvedModelClass (read-only) The class name of the model to use
 *  for {@link model}. In some cases (i.e. some older custom modules) the class
 *  name will not be specified in the controller, and thus it is useful to guess
 *  its corresponding model's name based on its own name.
 * @package X2CRM.components
 */
class CommonControllerBehavior extends CBehavior {

	/**
	 * Stores the value of {@link $model}
	 */
	private $_model;

    /**
     * Model class specified by the property {@link x2base.modelClass} or
     * determined automatically based on controller ID, if possible.
     * @var type
     */
    private $_resolvedModelClass;


	public function attach($owner){
		if(!property_exists($owner,'modelClass'))
			throw new CException('Property "modelClass" must be declared in all controllers that use CommonControllerBehavior, but it is not declared in '.get_class($owner));
		parent::attach($owner);
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
            // Special case for Admin: let the ID be the one and only record if unspecified
            if($this->resolvedModelClass == 'Admin' && empty($id))
                $id = 1;

            // ID was never specified, so there's no way to tell which record to
            // load. Thus, redirect to index:
			if($id === null)
				$this->owner->redirect(array('index'));

            // Look up model; ID specified
            $this->_model = CActiveRecord::model($this->resolvedModelClass)->findByPk((int) $id);

            // Model record couldn't be retrieved, so throw a 404:
			if($this->_model === null && $throw)
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
     * Resolve and return the model class, if specified, or a guess.
     * @return string
     */
    public function getResolvedModelClass(){
        if(!isset($this->_resolvedModelClass)){
            if(isset($this->owner->modelClass)){
                // Model class has been specified in the controller:
                $modelClass = $this->owner->modelClass;
            }else{
                // Attempt to find an active record model named after the
                // controller. Typically the case in custom modules.
                $modelClass = ucfirst($this->owner->id);
                if(!class_exists($modelClass)){
                    $modelClass = 'Admin'; // Fall-back default
                }
            }
            $this->_resolvedModelClass = $modelClass;
        }
        return $this->_resolvedModelClass;
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
    

}

?>
