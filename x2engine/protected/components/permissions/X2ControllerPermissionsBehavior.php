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
 * Description of ControllerPermissionsBehavior
 *
 * @package application.components.permissions
 */
class X2ControllerPermissionsBehavior extends ControllerPermissionsBehavior {

    /**
     * Extension of a base Yii function, this method is run before every action
     * in a controller. If true is returned, it procedes as normal, otherwise
     * it will redirect to the login page or generate a 403 error.
     * @param string $action The name of the action being executed.
     * @return boolean True if the user can procede with the requested action
     */
    public function beforeAction($action = null) {
        if (is_int(Yii::app()->locked) &&
                !Yii::app()->user->checkAccess('GeneralAdminSettingsTask')) {

            $this->owner->appLockout();
        }
        $auth = Yii::app()->authManager;
        $params = array();
        if (empty($action))
            $action = $this->owner->getAction()->getId();
        elseif (is_string($action)) {
            $action = $this->owner->createAction($action);
        }

        $actionId = $action->getId();

        // These actions all have a model provided with them but its assignment
        // should not be checked for an exception. They either have permission
        // for this action or they do not.
        $exceptions = array(
            'updateStageDetails',
            'deleteList',
            'updateList',
            'userCalendarPermissions',
            'exportList',
            'updateLocation'
        );
        if (($this->owner->hasProperty('modelClass') || property_exists($this->owner, 'modelClass')) && class_exists($this->owner->modelClass)) {
            $staticModel = X2Model::model($this->owner->modelClass);
        }

        if (isset($_GET['id']) && !in_array($actionId, $exceptions) && !Yii::app()->user->isGuest &&
            isset($staticModel)) {

            // Check assignment fields in the current model
            $retrieved = true;
            $model = $staticModel->findByPk($_GET['id']);
            if ($model instanceof X2Model) {
                $params['X2Model'] = $model;
            }
        }

        // Generate the proper name for the auth item
        $actionAccess = ucfirst($this->owner->getId()) . ucfirst($actionId);
        $authItem = $auth->getAuthItem($actionAccess);

        // Return true if the user is explicitly allowed to do it, or if there is no permission 
        // item, or if they are an admin
        if (Yii::app()->params->isAdmin || 
            // access for missing permission item only granted for authenticated users and for
            // API requests (since API controllers have their own layer of authentication)
            ((!Yii::app()->user->isGuest ||
              Yii::app()->controller instanceof ApiController ||
              Yii::app()->controller instanceof Api2Controller) &&
             !($authItem instanceof CAuthItem)) || 
            Yii::app()->user->checkAccess($actionAccess, $params)) {

            return true;
        } elseif (Yii::app()->user->isGuest) {
            Yii::app()->user->returnUrl = Yii::app()->request->url;
            if (Yii::app()->isMobileApp ()) {
                $this->owner->redirect($this->owner->createAbsoluteUrl('/mobile/login'));
            } else {
                $this->owner->redirect($this->owner->createUrl('/site/login'));
            }
        } else {
            $this->owner->denied();
        }
    }

    /**
     * Determines if we have permission to view/edit/delete something based on the assignedTo field.
     *
     * @param mixed $model The model in question (subclass of {@link CActiveRecord} or 
     *  {@link X2Model}
     * @param string $action
     * @return boolean|array
     */
    public function checkPermissions(&$model, $action = null) {
        if (Yii::app()->params->isAdmin) return true;

        $view = false;
        $edit = false;
        $delete = false;
        $module = $model instanceof X2Model ? 
            Yii::app()->getModule($model->module) : Yii::app()->controller->module;
        if (isset($module)) {
            $moduleAdmin = Yii::app()->user->checkAccess(ucfirst($module->name) . 'Admin');
        } else {
            $moduleAdmin = false;
        }
        if ($model->asa('permissions') != null && 
            $module instanceof CModule) {

            // Check assignment and visibility using X2PermissionsBehavior
            $view = (Yii::app()->params->isAdmin || $moduleAdmin) || 
                $model->isVisibleTo(Yii::app()->getSuModel());
            if ($view) { // Only check edit permissions if they're allowed to view
                $edit = (Yii::app()->params->isAdmin || $moduleAdmin) || 
                    Yii::app()->authManager->checkAccess(
                        ucfirst($module->name) . 'Update',
                        Yii::app()->getSuID(),
                        array('X2Model' => $model)
                    );
            }
            if($view){//It's conceivable that a user might be able to delete without being able to edit
                $delete = (Yii::app()->params->isAdmin || $moduleAdmin) ||
                    Yii::app()->authManager->checkAccess(
                        ucfirst($module->name) . 'Delete',
                        Yii::app()->getSuID(),
                        array('X2Model' => $model)
                    );
            }
        } elseif ($model->asa('permissions')) {
            // Only visibility based permissions are possible
            $view = $model->isVisibleTo(Yii::app()->getSuModel());
            $edit = $model->isVisibleTo(Yii::app()->getSuModel());
            $delete = $model->isVisibleTo(Yii::app()->getSuModel());
        } else {
            // No special permissions checks are available
            $view = true;
            $edit = true;
            $delete = true;
        }

        if (!isset($action)) // hash of all permissions if none is specified
            return array('view' => $view, 'edit' => $edit, 'delete' => $delete);
        elseif ($action == 'view')
            return $view;
        elseif ($action == 'edit')
            return $edit;
        elseif ($action == 'delete')
            return $delete;
        else
            return false;
    }

    /**
     * Format the left sidebar menu of links to remove items which a user is not
     * allowed to perform due to role settings.
     * @param array $array An array of menu items to be formatted
     * @param array $params An array of special parameters to be used for a role's biz rule
     * @return array The formatted list of menu items
     */
    function formatMenu($array, $params = array()) {
        $auth = Yii::app()->authManager;
        foreach ($array as &$item) {
            if (isset($item['url']) && is_array($item['url'])) {
                $url = $item['url'][0];
                if (preg_match('/\//', $url)) {
                    $pieces = explode('/', $url);
                    $action = "";
                    foreach ($pieces as $piece) {
                        $action.=ucfirst($piece);
                    }
                } else {
                    $action = ucfirst($this->owner->getId() . ucfirst($item['url'][0]));
                }
                // For special actions within the Admin controller that use the "checkAdminOn" 
                // biz rule method: add a module parameter for proper checking
                if($this->owner->getModule() instanceof CModule)
                    $params['module'] = $this->owner->getModule()->getId();
                $authItem = $auth->getAuthItem($action);
                if (!isset($item['visible']) || $item['visible'] == true) {
                    $item['visible'] = Yii::app()->params->isAdmin || Yii::app()->user->checkAccess($action, $params) || is_null($authItem);
                }
            } else {
                if (isset($item['linkOptions']['submit'])) {
                    $action = ucfirst($this->owner->getId() . ucfirst($item['linkOptions']['submit'][0]));
                    
                    $authItem = $auth->getAuthItem($action);
                    $item['visible'] = Yii::app()->user->checkAccess($this->owner->getId() . ucfirst($item['linkOptions']['submit'][0]), $params) || is_null($authItem);

                    // Add the CSRF Token to all submit links
                    $item['linkOptions']['csrf'] = true;
                }
            }
        }
        
        return $array;
    }

}

?>
