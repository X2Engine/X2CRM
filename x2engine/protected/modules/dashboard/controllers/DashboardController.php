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
  * @package X2CRM.modules.dashboard.controllers
  */
class DashboardController extends x2base {
    public $modelClass = 'Dashboard';
    public $layout = '//layouts/col2Dash';
	public function accessRules() {
		return array(
            array('allow',
                 'actions'=>array('getItems'),
                 'users'=>array('*'), 
            ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','getItems','moveWidget','setAUTH','update','changeColumns','getGRID','widgetState','widgetOrder','settings','hideIntro','dashSettings'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','testScalability'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actions() {
		return array(
		);
    }
    public function actionDashSettings(){
        var_dump($_POST);
        $item = $_POST["item"];
        $this->render('dashSettings',array('item'=>$item));
        return "success";
    }
    public function actionHideIntro(){
        $uid = Yii::app()->user->getId();
        $sql = "UPDATE x2_dashboard_settings SET hideINTRO = 1 WHERE userID = $uid";
        $command = Yii::app()->db->createCommand($sql);
        $command->execute();
        return "success";
    }
    public function actionSettings(){
        $model = new Dashboard;
        $model = $model->search('prof');
        $this->render('settings', array('dataProvider'=>$model));
    }
    public function actionWidgetOrder(){
        $widgets = array();
        if (isset($_POST['widget'])){
            $widgets = $_POST['widget'];
        }
        $ind = 1;
        foreach ($widgets as $widget){
            $sql = "UPDATE x2_widgets SET posDASH = $ind WHERE name = '$widget'";
            $command = Yii::app()->db->createCommand($sql);
            $command->execute();
            $ind++;
        }
        echo 'success';
    }
    public function actionChangeColumns(){
        $uid = Yii::app()->user->getId();
        $model = new Dashboard;
        if(isset($_POST['menu1'])){
            $item = $_POST["menu1"];
            if ($item != 1) $item = intval($item);
            $sql = "UPDATE x2_dashboard_settings SET numCOLS = $item WHERE userID = $uid";
            $command = Yii::app()->db->createCommand($sql);
            $command->execute();
        }
        $this->renderPartial('list');
    }
    public function actionWidgetState(){
        if(isset($_GET['widget']) && isset($_GET['state'])){
            $widgetName = $_GET['widget'];
            $state = $_GET['state'];
            if ($state == 0) $change = 0;
            else $change = 1;
            $sql = "UPDATE x2_widgets SET showDASH = $change WHERE name = '$widgetName'";
            $command = Yii::app()->db->createCommand($sql);
            $command->execute();
            echo 'success';
        }
    }
    public function actionGetItems(){
        $uid = Yii::app()->user->getId();
		$sql = 'SELECT * FROM x2_widgets WHERE showDASH = 1 AND userid = '.$uid;
		$command = Yii::app()->db->createCommand($sql);
		$result = $command->queryAll();
        echo CJSON::encode($result); 
        exit;
	}
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $id = $_GET['id'];
        $sql = "SELECT * FROM x2_widgets WHERE id=$id";
        $command = Yii::app()->db->createCommand($sql);
        $row = $command->queryRow();
        $this->render("update", array(
            'displayName'=>$row['dispNAME'],
            'name'=>$row['name'],
            'showPROFILE'=>$row['showPROFILE'],
            'posPROFILE'=>$row['posPROF'],
            'userALLOWS'=>$row['userALLOWS'],
            'model'=>$model
        ));
    }
	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
        
        public function create($model,$oldAttributes, $api){
            if($api==0)
                parent::create($model,$oldAttributes,$api);
            else
                return parent::create($model,$oldAttributes,$api);
        }
        
	public function actionCreate() {
		$model=new Dashboard;
		$users=User::getNames();
		unset($users['admin']);
        unset($users['']);
        if(isset($_POST['Dashboard'])){
            $temp = $model->attributes;
            foreach($_POST['Dashboard'] as $name => $value){
                if ($value = $model->getAttributeLabel($name))
                    $value = '';
            }
            foreach(array_keys($model->attributes) as $field){
                if(isset($_POST['Dashboard'][$field])){
                    $model->field = $_POST['Dashboard'][$field];
                    $fieldData = Fields::model()->findByAttributes(array('modelName'=>'Dashboard','fieldName'=>$field));
                }
            }
            $this->create($model,$temp,'0');
        }
		$this->render('create',array(
			'model'=>$model,
			'users'=>$users,
		));
    }
	/*
	public function actionSaveChanges($id) {
		$account=$this->loadModel($id);
		if(isset($_POST['Accounts'])) {
			$temp=$account->attributes;
			foreach($account->attributes as $field=>$value){
                            if(isset($_POST['Accounts'][$field])){
                                $account->$field=$_POST['Accounts'][$field];
                            }
                        }

			// process currency into an INT
			$account->annualRevenue = Formatter::parseCurrency($account->annualRevenue,false);
			$changes=$this->calculateChanges($temp,$account->attributes, $account);
			$account=$this->updateChangelog($account,$changes);
			$account->update();
			$this->redirect(array('view','id'=>$account->id));
		}
    }*/

	public function actionRemoveUser($id) {

		$model=$this->loadModel($id);

		$pieces=explode(', ',$model->assignedTo); 
		$pieces=Opportunity::editUsersInverse($pieces);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Accounts'])) {
			$temp=$model->attributes;
			$model->attributes=$_POST['Accounts'];  
			$arr=$_POST['Accounts']['assignedTo'];

			
			foreach($arr as $id=>$user){
				unset($pieces[$user]);
			}
			
			$temp=Accounts::parseUsersTwo($pieces);

			$model->assignedTo=$temp;
			// $changes=$this->calculateChanges($temp,$model->attributes);
			// $model=$this->updateChangelog($model,$changes);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addUser',array(
			'model'=>$model,
			'users'=>$pieces,
			'action'=>'Remove'
		));
	}
        
        public function delete($id){
            
            $model=$this->loadModel($id);
            $dataProvider=new CActiveDataProvider('Actions', array(
                'criteria'=>array(
                    'condition'=>'associationId='.$id.' AND associationType=\'account\'',
            )));

            $actions=$dataProvider->getData();
            foreach($actions as $action){
                    $action->delete();
            }
            $this->cleanUpTags($model);
            $model->delete();
        }

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id) {
		$model=$this->loadModel($id);
		if(Yii::app()->request->isPostRequest) {
			$dataProvider=new CActiveDataProvider('Actions', array(
				'criteria'=>array(
					'condition'=>'associationId='.$id.' AND associationType=\'account\'',
			)));

			$actions=$dataProvider->getData();
			foreach($actions as $action){
				$action->delete();
			}
                        $this->cleanUpTags($model);
			$model->delete();
		} else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$model=new Dashboard('search');
		$this->render('index', array('model'=>$model));
    }
	/**
	 * Manages all models.
	 */
    public function actionAdmin() {
        $uid = Yii::app()->user->getId();
        $sql = "SELECT * FROM x2_dashboard_settings WHERE userID = $uid";
        $command = Yii::app()->db->createCommand($sql);
        $query = $command->queryRow();
        $model=new Dashboard;
        $model = $model->search('dash');
        $this->render('admin',array(
            'model'=>$model,
            'item'=>$query['numCOLS'],
            'hINT'=>$query['hideINTRO']
        ));
	}

}
