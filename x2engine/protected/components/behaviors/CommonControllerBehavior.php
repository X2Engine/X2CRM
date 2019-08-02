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
 * A behavior for controllers; contains methods common to controllers whether or
 * not they are children of x2base.
 *
 * All controllers that use this behavior must declare the "modelClass" property.
 *
 * @property X2Model $model (read-only); in the context of viewing or updating a
 * 	record, this contains the active record object corresponding to that record.
 * 	Its value is set by calling {@link getModel()} with the ID of the desired record.
 * @property string $resolvedModelClass (read-only) The class name of the model to use
 *  for {@link model}. In some cases (i.e. some older custom modules) the class
 *  name will not be specified in the controller, and thus it is useful to guess
 *  its corresponding model's name based on its own name.
 * @package application.components
 */
class CommonControllerBehavior extends CBehavior {

    /**
     * Stores the value of {@link $model}
     */
    private $_model;
    public $redirectOnNullModel = true;
    public $throwOnNullModel = true;

    /**
     * Model class specified by the property {@link x2base.modelClass} or
     * determined automatically based on controller ID, if possible.
     * @var type
     */
    private $_resolvedModelClass;

    public function attach($owner) {
        if (!property_exists($owner, 'modelClass'))
            throw new CException('Property "modelClass" must be declared in all controllers that use CommonControllerBehavior, but it is not declared in ' . get_class($owner));
        parent::attach($owner);
    }

    /**
     * Returns the data model based on the primary key given.
     *
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded. Note, it is assumed that
     * 	when this value is null, {@link _model} is set already; otherwise there's
     * 	nothing that can be done to correctly resolve the model.
     * @param bool $throw Whether to throw a 404 upon not finding the model
     */
    public function getModel($id = null) {
        $throw = $this->throwOnNullModel;
        $redirect = $this->redirectOnNullModel;
        if (!isset($this->_model)) {
            // Special case for Admin: let the ID be the one and only record if unspecified
            if ($this->resolvedModelClass == 'Admin' && empty($id))
                $id = 1;

            // Last-ditch effort:
            if (empty($id) && isset($_GET['id'])) {
                $id = $_GET['id'];
            }

            // ID was never specified, so there's no way to tell which record to
            // load. Redirect or throw an exception based on function args.
            if ($id === null) {
                if ($redirect) {
                    $this->owner->redirect(array('index'));
                } elseif ($throw) {
                    throw new CHttpException(401, Yii::t('app', 'Invalid request; no record ID specified.'));
                }
            }

            // Look up model; ID specified
            $this->_model = CActiveRecord::model($this->resolvedModelClass)->findByPk((int) $id);

            if ($this->resolvedModelClass === 'Profile') {
                $this->_model = CActiveRecord::model('Profile')->findByAttributes(array('id' => $id));
            }
            if ($this->_model === null) {
                $userModel = CActiveRecord::model('User')->findByPk((int) $id);
                if(isset($userModel)){
                    $this->_model = CActiveRecord::model('Profile')->findByAttributes(array('username' => $userModel->username));
                }
            }

            // Model record couldn't be retrieved, so throw a 404:
            if ($this->_model === null && $throw)
                throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        } else if ($this->_model->id != $id && !empty($id)) { // A different record is being requested
            // Change the ID and load the different record.
            // Note that setting the attribute to null will cause isset to return false.
            $this->_model = null;
            return $this->getModel($id);
        }
        return $this->_model;
    }

    public function lookUpModel($id, $modelClass) {
        $throw = $this->throwOnNullModel;
        $model = null;

        // Look up model; ID specified
        $model = CActiveRecord::model($modelClass)->findByPk((int) $id);

        // Model record couldn't be retrieved, so throw a 404:
        if ($model === null && $throw)
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        return $model;
    }

    /**
     * Obtain the IP address of the current web client.
     * @return string
     */
    public function getRealIp() {
        foreach (array(
    'HTTP_CLIENT_IP',
    'HTTP_X_FORWARDED_FOR',
    'HTTP_X_FORWARDED',
    'HTTP_X_CLUSTER_CLIENT_IP',
    'HTTP_FORWARDED_FOR',
    'HTTP_FORWARDED',
    'REMOTE_ADDR'
        ) as $var) {
            if (array_key_exists($var, $_SERVER)) {
                foreach (explode(',', $_SERVER[$var]) as $ip) {
                    $ip = trim($ip);
                    $filters = FILTER_FLAG_IPV4 | FILTER_FLAG_NO_RES_RANGE;
                    if (!YII_DEBUG)
                        $filters = $filters | FILTER_FLAG_NO_PRIV_RANGE;
                    if (filter_var($ip, FILTER_VALIDATE_IP, $filters) !== false)
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
    public function getResolvedModelClass() {
        if (!isset($this->_resolvedModelClass)) {
            if (isset($this->owner->modelClass)) {
                // Model class has been specified in the controller:
                $modelClass = $this->owner->modelClass;
            } else {
                // Attempt to find an active record model named after the
                // controller. Typically the case in custom modules.
                $modelClass = ucfirst($this->owner->id);
                if (!class_exists($modelClass)) {
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
        $propertyId = Yii::app()->settings->{"gaTracking_" . $location};
        if (!empty($propertyId))
            $this->owner->renderPartial('application.components.views.gaTrackingScript', array('propertyId' => $propertyId));
    }

    /**
     * For server config or other expected errors which are not bugs but
     * prevent the software from functioning properly.
     * @param type $error
     */
    public function errorMessage($message, $code = 500, $type = "PHP Error") {
        $error = array(
            'code' => $code,
            'type' => $type,
            'message' => $message,
            'file' => '',
            'line' => '',
            'trace' => '',
            'source' => ''
        );
        $this->owner->render('/site/errorDisplay', $error);
        Yii::app()->end();
    }

}

?>
