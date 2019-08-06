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
 * @package application.modules.groups.controllers 
 */
class GroupsController extends x2base {

    public $modelClass='Groups';

//    public function behaviors() {
//        return array_merge(parent::behaviors(), array(
//            'MobileControllerBehavior' => array(
//                'class' => 
//                    'application.modules.mobile.components.behaviors.MobileControllerBehavior'
//            ),
//        ));
//    }
	
	/**
	 * Filters to be used by the controller.
	 * 
	 * This method defines which filters the controller will use.  Filters can be
	 * built in with Yii or defined in the controller (see {@link GroupsController::filterClearGroupsCache}).
	 * See also Yii documentation for more information on filters.
	 * 
	 * @return array An array consisting of the filters to be used. 
	 */
	public function filters() {
		return array(
			'clearGroupsCache - view, index',	// clear the cache, unless we're doing a read-only operation here
            'setPortlets',
        );
	}

//    public function actionMobileView ($id) {
//        $model = $this->loadModel ($id);
//        $this->dataUrl = $model->getUrl ();
//        if ($this->checkPermissions($model, 'view')) {
//            $this->render (
//                $this->pathAliasBase.'views.mobile.recordView',
//                array (
//                    'model' => $model,
//                )
//            );
//        }
//    }

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		$userLinks=GroupToUser::model()->findAllByAttributes(array('groupId'=>$id));
		$str="";
		foreach($userLinks as $userLink){
            $user=X2Model::model('User')->findByPk($userLink->userId);
            if(isset($user)){
                $str.=$user->username.", ";
            }
		}
		$str=substr($str,0,-2);
		$users=User::getUserLinks($str);

		// add group to user's recent item list
        User::addRecentItem('g', $id, Yii::app()->user->getId()); 

		$this->render('view',array(
			'model'=>$this->loadModel($id),
			'users'=>$users,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$model=new Groups;
		$users=User::getNames();
		unset($users['admin']);
		unset($users['']);

		if(isset($_POST['Groups'])){

			$model->attributes=$_POST['Groups'];
			if(isset($_POST['users']))
				$users=$_POST['users'];
			else
				$users=array();
			if($model->save()){
				foreach($users as $user){
					$link=new GroupToUser;
					$link->groupId=$model->id;
					$userRecord=User::model()->findByAttributes(array('username'=>$user));
					if(isset($userRecord)) {
						$link->userId=$userRecord->id;
						$link->username=$userRecord->username;
						$link->save();
					}
				}
				$this->redirect(array('view','id'=>$model->id));
			}
		}

		$this->render('create',array(
				'model'=>$model,
				'users'=>$users,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$model=$this->loadModel($id);
		$users=User::getNames();
		$selected=array();
		$links=GroupToUser::model()->findAllByAttributes(array('groupId'=>$id));
		foreach($links as $link){
			$user=User::model()->findByPk($link->userId);
			if(isset($user)){
				$selected[]=$user->username;
			}
		}
		unset($users['admin']);
		unset($users['']);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Groups']))
		{
			$userLinks=GroupToUser::model()->findAllByAttributes(array('groupId'=>$model->id));
			foreach($userLinks as $userLink){
				$userLink->delete();
			}
			$model->attributes=$_POST['Groups'];
			if(isset($_POST['users']))
				$users=$_POST['users'];
			else
				$users=array();
			if($model->save()){
                $changeMade = false;
				foreach($users as $user){
					$link=new GroupToUser;
					$link->groupId=$model->id;
					$userRecord=User::model()->findByAttributes(array('username'=>$user));
                    if(isset($userRecord)){
                        $link->userId=$userRecord->id;
                        $link->username=$userRecord->username;
                        $test=GroupToUser::model()->findByAttributes(array('groupId'=>$model->id,'userId'=>$userRecord->id));
                        if(!isset($test)) {
                            $link->save();
                            $changeMade = true;
                        }
                    }
				}
                if ($changeMade) Yii::app()->authCache->clear ();
				$this->redirect(array('view','id'=>$model->id));
			}
		}

		$this->render('update',array(
				'model'=>$model,
				'users'=>$users,
				'selected'=>$selected,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id) {
		if(Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			$links=GroupToUser::model()->findAllByAttributes(array('groupId'=>$id));
			foreach($links as $link) {
				$link->delete();
			}
			$contacts=X2Model::model('Contacts')->findAllByAttributes(array('assignedTo'=>$id));
			foreach($contacts as $contact) {
				$contact->assignedTo='Anyone';
				$contact->save();
			}
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
					$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$dataProvider=new CActiveDataProvider('Groups');
		$this->render('index',array(
				'dataProvider'=>$dataProvider,
		));
	}
        
	public function actionGetGroups() {	
		$checked = false;
		if(isset($_POST['checked'])) // coming from a group checkbox?
			$checked = json_decode($_POST['checked']);
		elseif(isset($_POST['group']))
			$checked = true;

		$id = null;
		if(isset($_POST['field']))
			$id = $_POST['field'];
		
		$options = array();
		if($checked) { // group checkbox checked, return list of groups
			echo CHtml::listOptions($id,Groups::getNames(),$options);
		} else { // group checkbox unchecked, return list of user names
			$users = User::getNames();
			if(!in_array($id,array_keys($users)))
				$id = Yii::app()->user->getName();

			echo CHtml::listOptions($id,$users,$options);
		}
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model) {
		if(isset($_POST['ajax']) && $_POST['ajax']==='groups-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	
	/**
	 * A filter to clear the groups cache.
	 * 
	 * This method clears the cache whenever the groups controller is accessed.
	 * Caching improves performance throughout the app, but will occasionally 
	 * need to be cleared. Keeping this filter here allows for cleaning up the
	 * cache when required.
	 * 
	 * @param type $filterChain The filter chain Yii is currently acting on.
	 */
	public function filterClearGroupsCache($filterChain) {
		$filterChain->run();
		Yii::app()->cache->delete('user_groups');
		Yii::app()->cache->delete('user_roles');
	}

    public function actionGetItems ($term) {
        LinkableBehavior::getItems ($term);
    }

    /**
     * Create a menu for Groups
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Group = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'create', 'view', 'edit', 'delete',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'index',
                'label'=>Yii::t('groups','{group} List', array(
                    '{group}' => $Group,
                )),
                'url'=>array('index')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('groups','Create {group}', array(
                    '{group}' => $Group,
                )),
                'url'=>array('create')
            ),
            array(
                'name'=>'view',
                'label'=>Yii::t('groups','View'),
                'url'=>array('view', 'id'=>$modelId)
            ),
            array(
                'name'=>'edit',
                'label'=>Yii::t('groups','Edit {group}', array(
                    '{group}' => $Group,
                )),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('groups','Delete {group}', array(
                    '{group}' => $Group,
                )),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))
            ),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }

}
