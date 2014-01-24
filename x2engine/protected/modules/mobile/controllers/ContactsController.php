<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * @package X2CRM.modules.mobile.controllers
 */
class ContactsController extends MobileController{

	public $modelClass = 'Contacts';

	 public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('index','new','search','view','viewAll'),
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }
	
	public function actionView($id){
		
		$model=$this->loadModel($id);
		
		$this->render('view',array(
			'model'=>$model,
		));
	}
	
	
	public function actionIndex(){
		
		$user=User::model()->findByPk(Yii::app()->user->getId());
		$topList=$user->topContacts;
		$pieces=explode(',',$topList);
		$contacts=array();
		foreach($pieces as $piece){
			$contact=X2Model::model('Contacts')->findByPk($piece); 
			if(isset($contact))
				$contacts[]=$contact;
		}
		$dataProvider=new CActiveDataProvider('Contacts');
		$dataProvider->setData($contacts);
		
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}
	
	public function actionNew(){
		
		$model=new Contacts;
		$attributeLabels = $model->attributeLabels();
		
		/*if(isset($_POST['ajax']) && $_POST['ajax']=='quick-contact-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}*/

		// collect user input data
		if(isset($_POST['Contacts'])) {
			// $this->redirect('http://www.google.com/');
			//$model->attributes = $_POST['Contacts'];
			$model->setX2Fields($_POST['Contacts']);
			//
				// $model->firstName = 'bob';
				// $model->lastName = 'dole';
				// $model->phone = '';
				// $model->email = '';
			$model->visibility = 1;
			
			// reset to blank if it's the default value
			/*if($model->firstName == $attributeLabels['firstName'])
				$model->firstName = '';
			if($model->lastName == $attributeLabels['lastName'])
				$model->lastName = '';
			if($model->phone == $attributeLabels['phone'])
				$model->phone = '';
			if($model->email == $attributeLabels['email'])
				$model->email = '';*/
			
			$model->createDate=time();

			// validate user input and save contact
			if($model->save()) {
				echo "1";
				$this->redirect(array('/mobile/contacts/view','id'=>$model->id));
			} //else print_r($model->getErrors());
		}
		$this->render('quickContact', array (
            'model' => $model
        ));
	}
	
	public function actionSearch(){
		$model=new Contacts;
		$attributeLabels = $model->attributeLabels();
		if(isset($_POST['Contacts'])){
			$model->attributes=$_POST['Contacts'];
			$firstName=true;
			$lastName=true;
			if($model->firstName == $attributeLabels['firstName'])
				$firstName=false;
			if($model->lastName == $attributeLabels['lastName'])
				$lastName=false;

			if($firstName && $lastName){
				$dataProvider=new CActiveDataProvider('Contacts', array(
					'criteria'=>array(
						'order'=>'lastName ASC',
						'condition'=>"firstName='$model->firstName' AND lastName='$model->lastName'"
				)));
			}else if($firstName && !$lastName){
				$dataProvider=new CActiveDataProvider('Contacts', array(
					'criteria'=>array(
						'order'=>'firstName ASC',
						'condition'=>"firstName='$model->firstName'"
				)));
			}else if(!$firstName && $lastName){
				$dataProvider=new CActiveDataProvider('Contacts', array(
					'criteria'=>array(
						'order'=>'lastName ASC',
						'condition'=>"lastName='$model->lastName'"
				)));
			}else{
				$this->redirect($this->createUrl('/mobile/site/home'));
			}
			
			$this->render('viewAll',array(
				'dataProvider'=>$dataProvider,
			));
		}else{
			$this->render('search',array(
				'model'=>$model,
			));
		}
	}
	
	public function actionViewAll(){
		$model=new Contacts;
		
		
	}
}

?>
