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
 * Description of X2ControllerPermissionsBehavior
 *
 * @package X2CRM.components.permissions
 */
class X2ControllerPermissionsBehavior extends ControllerPermissionsBehavior {

    public function beforeAction($action = null) {
        if(is_int(Yii::app()->locked) && !Yii::app()->user->checkAccess('GeneralAdminSettingsTask')) {
            $this->owner->appLockout();
        }
		$auth = Yii::app()->authManager;
		$params = array();
		$action = $this->owner->getAction()->getId();
		$exceptions = array('updateStageDetails','deleteList','updateList','userCalendarPermissions','exportList','updateLocation');
        if(class_exists($this->owner->modelClass)){
            $model=X2Model::model($this->owner->modelClass);
        }
		if(isset($_GET['id']) && !in_array($action,$exceptions) && !Yii::app()->user->isGuest && isset($model)) {
			if ($model->hasAttribute('assignedTo')) {
				$model=X2Model::model($this->owner->modelClass)->findByPk($_GET['id']);
                if($model!==null){
                    $params['assignedTo']=$model->assignedTo;
                }
            }
		}

		$actionAccess = ucfirst($this->owner->getId()) . ucfirst($this->owner->getAction()->getId());
		$authItem = $auth->getAuthItem($actionAccess);
		if(Yii::app()->user->checkAccess($actionAccess, $params) || is_null($authItem) || Yii::app()->user->getName() == 'admin')
			return true;
		elseif(Yii::app()->user->isGuest){
			Yii::app()->user->returnUrl = Yii::app()->request->url;
			$this->owner->redirect($this->owner->createUrl('/site/login'));
        }else
			$this->owner->denied();
	}

    /**
     * Determines if we have permission to edit something based on the assignedTo field.
     *
     * @param mixed $model The model in question (subclass of {@link CActiveRecord} or {@link X2Model}
     * @param string $action
     * @return boolean
     */
    public function checkPermissions(&$model, $action = null) {

        $view = false;
        $edit = false;
        $module = Yii::app()->controller->module;
        $visField=X2Model::model('Fields')->findByAttributes(array('modelName'=>get_class($model),'type'=>'visibility'));
        if(isset($module)){
            $moduleAdmin = Yii::app()->user->checkAccess(ucfirst($module->name).'Admin');
        }else{
            $moduleAdmin = false;
        }
        // if we're the admin, visibility is public, there is no visibility/assignedTo, or it's directly assigned to the user, then we're done
        if ((Yii::app()->params->isAdmin || $moduleAdmin) || !$model->hasAttribute('assignedTo') || ($model->assignedTo == 'Anyone' && ($model->hasAttribute('visibility') && $model->visibility!=0) || !$model->hasAttribute('visibility')) || $model->assignedTo == Yii::app()->user->getName()) {

            $edit = true;
        } elseif (!isset($visField) || $model->{$visField->fieldName} == 1) {

            $view = true;
        } else {
            if (ctype_digit((string)$model->assignedTo) && !empty(Yii::app()->params->groups)) {  // if assignedTo is numeric, it's a group
                $edit = in_array($model->assignedTo, Yii::app()->params->groups); // if we're in the assignedTo group we act as owners
            } elseif ($model->visibility == 2) {  // if record is shared with owner's groups, see if we're in any of those groups
                $view = (bool) Yii::app()->db->createCommand('SELECT COUNT(*) FROM x2_group_to_user A JOIN x2_group_to_user B
																ON A.groupId=B.groupId AND A.username=:user1 AND B.username=:user2')
                                ->bindValues(array(':user1' => $model->assignedTo, ':user2' => Yii::app()->user->getName()))
                                ->queryScalar();
            }
        }

        $view = $view || $edit; // edit permission implies view permission

        if (!isset($action)) // hash of all permissions if none is specified
            return array('view' => $view, 'edit' => $edit, 'delete' => $edit);
        elseif ($action == 'view')
            return $view;
        elseif ($action == 'edit')
            return $edit;
        elseif ($action == 'delete')
            return $edit;
        else
            return false;
    }

    function formatMenu($array, $params = array()) {
        $auth = Yii::app()->authManager;
        foreach ($array as &$item) {
            if (isset($item['url']) && $item['url'] != '#') {
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
                $authItem = $auth->getAuthItem($action);
				if(!isset($item['visible']) || $item['visible'] == true)
					$item['visible'] = Yii::app()->user->checkAccess($action, $params) || is_null($authItem);
            } else {
                if (isset($item['linkOptions']['submit'])) {
                    $action = ucfirst($this->owner->getId() . ucfirst($item['linkOptions']['submit'][0]));
                    $authItem = $auth->getAuthItem($action);
                    $item['visible'] = Yii::app()->user->checkAccess($this->owner->getId() . ucfirst($item['linkOptions']['submit'][0]), $params) || is_null($authItem);
                }
            }
        }
        return $array;
    }
}

?>
