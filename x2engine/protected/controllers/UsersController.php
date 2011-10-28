<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

class UsersController extends x2base {

	public $modelClass = 'UserChild';
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array(
			array('allow',
				'actions'=>array('view','addTopContact','removeTopContact'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('index','create','update','admin','delete','search'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		$user=UserChild::model()->findByPk($id);
		$dataProvider=new CActiveDataProvider('Actions', array(
			'criteria'=>array(
				'order'=>'complete DESC',
				'condition'=>'assignedTo=\''.$user->username.'\'',
		)));
		$actionHistory=$dataProvider->getData();
		$this->render('view',array(
			'model'=>$this->loadModel($id),
			'actionHistory'=>$actionHistory,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$model=new UserChild;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['UserChild'])) {
			$model->attributes=$_POST['UserChild'];
			//$this->updateChangelog($model);
			$model->password = md5($model->password);

			$profile=new ProfileChild;
			$profile->fullName=$model->firstName." ".$model->lastName;
			$profile->username=$model->username;
                        $profile->allowPost=1;
			$profile->emailAddress=$model->emailAddress;
			$profile->status=$model->status;

			if($model->save() && $profile->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Users'])) {
                    $temp=$model->password;
                    $model->attributes=$_POST['Users'];
                    if($model->password!="")
                        $model->password = md5($model->password);
                    else
                        $model->password=$temp;
                    if($model->save())
                        $this->redirect(array('view','id'=>$model->id));
		}
		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Lists all models.
	 */

	/**
	 * Manages all models.
	 */
	public function actionAdmin() {
		$model=new UserChild('search');
		$name='UserChild';
		parent::admin($model, $name);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=UserChild::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('app','The requested page does not exist.'));
		return $model;
	}

	public function actionDelete($id) {
		$model=$this->loadModel($id);
		if(Yii::app()->request->isPostRequest) {
			$dataProvider=new CActiveDataProvider('Actions', array(
			'criteria'=>array(
				'condition'=>"assignedTo='$model->username'",
			)));
			$actions=$dataProvider->getData();
			foreach($actions as $action){
                                if($action->updatedBy==$model->username)
                                    $action->updatedby='admin';
                                if($action->completedBy=$model->username)
                                    $action->completedBy='admin';
				$action->assignedTo="Anyone";
                                $action->save();
			}
                        
                        $prof=ProfileChild::model()->findByAttributes(array('username'=>$model->username));
                        $prof->delete();
                        $model->delete();
			
		} else
			throw new CHttpException(400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	public function actionAddTopContact() {
		if(isset($_GET['contactId']) && is_numeric($_GET['contactId'])) {
		
			//$viewId = (isset($_GET['viewId']) && is_numeric($_GET['viewId'])) ? $_GET['viewId'] : null;
			
			$id = Yii::app()->user->getId();
			$model=$this->loadModel($id);

			$topContacts = empty($model->topContacts)? array() : explode(',',$model->topContacts);

			if(!in_array($_GET['contactId'],$topContacts)) {		// only add to list if it isn't already in there
				array_unshift($topContacts,$_GET['contactId']);
				$model->topContacts = implode(',',$topContacts);
			}
			if ($model->save())
				$this->renderTopContacts();

		}
	}

	public function actionRemoveTopContact() {
		if(isset($_GET['contactId']) && is_numeric($_GET['contactId'])) {
		
			//$viewId = (isset($_GET['viewId']) && is_numeric($_GET['viewId'])) ? $_GET['viewId'] : null;
			
			$id = Yii::app()->user->getId();
			$model=$this->loadModel($id);

			$topContacts = empty($model->topContacts)? array() : explode(',',$model->topContacts);
			$index = array_search($_GET['contactId'],$topContacts);

			if($index!==false)
				unset($topContacts[$index]);

			$model->topContacts = implode(',',$topContacts);
			
			if ($model->save())
				$this->renderTopContacts();
		}
	}
	
	private function renderTopContacts() {
		$this->renderPartial('application.components.views.topContacts',array(
			'topContacts'=>UserChild::getTopContacts(),
			//'viewId'=>$viewId
		));
	}
}
