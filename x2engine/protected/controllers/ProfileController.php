<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

class ProfileController extends x2base {
	public $modelClass='ProfileChild';
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','update','search','addPost','deletePost','uploadPhoto','profiles','settings','addComment','setBackground','deleteBackground'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionDeletePost($id) {
		$post = Social::model()->findByPk($id);
		if($post->type=='comment') {
			$postParent = Social::model()->findByPk($post->associationId);
			$user=ProfileChild::model()->findByPk($postParent->associationId);
		} else
			$user=ProfileChild::model()->findByPk($post->associationId);
		if(isset($postParent) && $post->user!=Yii::app()->user->getName()){
			if($postParent->associationId==Yii::app()->user->getId())
				$post->delete();
		}
		if($post->user==Yii::app()->user->getName() || $post->associationId==Yii::app()->user->getId()) {
			if($post->delete()) {
			}
		}
		if(isset($_GET['redirect'])) {
			if($_GET['redirect']=="view")
				$this->redirect(array('view','id'=>$user->id));
			if($_GET['redirect']=="index")
				$this->redirect(array('index'));
		} else
			$this->redirect(array('index'));
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {

		$dataProvider=new CActiveDataProvider('Social', array(
			'criteria'=>array(
				'order'=>'timestamp DESC',
				'condition'=>"type='feed' AND associationId=$id AND (private=0 OR associationId=".Yii::app()->user->getId()." OR user='".Yii::app()->user->getName()."')",
		)));

		$this->render('view',array(
			'model'=>$this->loadModel($id),
			'dataProvider'=>$dataProvider,
		));
	}

	public function actionSettings(){
		$model=$this->loadModel(Yii::app()->user->getId());
		
		// get admin model
		$admin = Admin::model()->findByPk(1);

		list($menuItems,$selectedItems) = Admin::getMenuItems(true);
		
		foreach($menuItems as $key=>$value) {
			if(!in_array($key,$selectedItems))
				unset($menuItems[$key]);
		}
		$menuItems = array(''=>Yii::t('app',"What's New")) + $menuItems;
		
		if(isset($_POST['ProfileChild'])) {
			$model->attributes = $_POST['ProfileChild'];
			
			if($model->save()){
				//$this->redirect(array('view','id'=>$model->id));
			}
			$this->refresh();
		}
		$languageDirs = scandir('./protected/messages');	// scan for installed language folders

		$languages = array('en'=>'English');

		foreach ($languageDirs as $code) {		// look for langauges name
				$name = $this->getLanguageName($code,$languageDirs);		// in each item in $languageDirs
				if($name!==false)
						$languages[$code] = $name;	// add to $languages if name is found
		}
		$times=$this->getTimeZones();
		
		$myBackgroundProvider = new CActiveDataProvider('MediaChild',array(
			'criteria'=>array(
				'condition'=>"(associationType = 'bg-private' AND associationId = '".Yii::app()->user->getId()."') OR associationType = 'bg'",
				'order'=>'createDate DESC'
			),
		));
		
		$this->render('settings',array(
			'model'=>$model,
			'languages'=>$languages,
			'times'=>$times,
			'myBackgrounds'=>$myBackgroundProvider,
			'menuItems'=>$menuItems
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		if ($id==Yii::app()->user->getId()) {
                    $model = $this->loadModel($id);
                    $users=UserChild::getNames();
                    $accounts=Accounts::getNames();  
                    
                    if(isset($_POST['ProfileChild'])) {
                            $temp=$model->attributes;
                            foreach($_POST['ProfileChild'] as $name => $value) {
                                    if($value == $model->getAttributeLabel($name)){
                                            $_POST['ProfileChild'][$name] = '';
                                    }
                            }
                            $model->attributes=$_POST['ProfileChild'];

                            if($model->save()){
                                $this->redirect(array('view','id'=>$model->id));
                            }
                    }

                    $this->render('update',array(
                            'model'=>$model,
                            'users'=>$users,
                            'accounts'=>$accounts,
                    ));
		} else {
			$this->redirect('view/'.$model->id);
		}
	}

	public function actionUploadPhoto($id){
		if($id==Yii::app()->user->getId()){
			$prof=ProfileChild::model()->findByPk($id);
			if(isset($_FILES['photo'])){
				if ($_FILES["photo"]["size"] < 2000000){
					if($prof->avatar!='' && isset($prof->avatar)){
						unlink($prof->avatar);
					}
					$temp = CUploadedFile::getInstanceByName('photo');
					$name=$this->generatePictureName();
					$ext=$temp->getExtensionName();
					$temp->saveAs('uploads/'.$name.'.'.$ext);

					$prof->avatar='uploads/'.$name.'.'.$ext;
					if($prof->save()){
						
					}
					
				}else{
					echo "File is too large!";
				}
			}
		}
		$this->redirect(array('view','id'=>$id));
	}
	
	public function actionSetBackground() {
		if(isset($_POST['name'])) {

			$profile = CActiveRecord::model('ProfileChild')->findByPk(Yii::app()->user->getId());

			$profile->backgroundImg = $_POST['name'];

			if($profile->save()) {
				echo "success";
			}
			//$this->redirect(array('profile/settings','id'=>Yii::app()->user->getId()));
		}
	}
	
	public function actionDeleteBackground($id) {

		$image = CActiveRecord::model('MediaChild')->findByPk($id);
		if($image->associationId == Yii::app()->user->getId() && ($image->associationType=='bg' || $image->associationType=='bg-private')) {
			
			$profile = CActiveRecord::model('ProfileChild')->findByPk(Yii::app()->user->getId());

			if($profile->backgroundImg == $image->fileName) {	// if this BG is currently in use, clear user's background image setting
				$profile->backgroundImg = '';
				$profile->save();
			}

			if ($image->delete()) {
				unlink('uploads/'.$image->fileName);	// delete file
				echo 'success';
			}
		}
	}
	
	private function generatePictureName(){
		
		$time=time();
		$rand=chr(rand(65,90));
		$salt=$time.$rand;
		$name=md5($salt.md5($salt).$salt);
		return $name;
	}
	
	public function actionAddPost($id,$redirect){
		$soc = new Social;
		$user = $this->loadModel($id);
		if(isset($_POST['Social']) && $_POST['Social']['data']!=Yii::t('app','Enter text here...')){
			$soc->data = $_POST['Social']['data'];
			if(isset($_POST['Social']['associationId']))
				$soc->associationId = $_POST['Social']['associationId'];
			//$soc->attributes = $_POST['Social'];
			//die(var_dump($_POST['Social']));
			$soc->user = Yii::app()->user->getName();
			$soc->type = 'feed';
			$soc->lastUpdated = time();
			$soc->timestamp = time();
			if(!isset($soc->associationId) || $soc->associationId==0)
				$soc->associationId=$id;
			if($soc->save()){
                            if($soc->associationId!=Yii::app()->user->getId()){
                                $notif=new Notifications;
                                $prof=CActiveRecord::model('ProfileChild')->findByAttributes(array('username'=>$soc->user));
                                $notif->text="$prof->fullName posted on your profile.";
                                $notif->record="profile:$prof->id";
                                $notif->viewed=0;
                                $notif->createDate=time();
                                $subject=CActiveRecord::model('ProfileChild')->findByPk($id);
                                $notif->user=$subject->username;
                                $notif->save();
                            }
			}
			
		}
		if($redirect=="view")
			$this->redirect(array('view','id'=>$id));
		else
			$this->redirect(array('index'));
	}
	
	public function actionAddComment(){
		$soc=new Social;
			if(isset($_GET['comment'])){
				$soc->data=$_GET['comment'];
				$id=$_GET['id'];
				$model=Social::model()->findByPk($id);
				$soc->user=Yii::app()->user->getName();
				$soc->type='comment';
				$soc->timestamp=time();
				$soc->associationId=$id;
				$model->lastUpdated=time();
				if($soc->save() && $model->save()){
                                    $notif=new Notifications;
                                    $prof=CActiveRecord::model('ProfileChild')->findByAttributes(array('username'=>$soc->user));
                                    $notif->text="$prof->fullName added a comment to a post.";
                                    $notif->record="profile:$model->associationId";
                                    $notif->viewed=0;
                                    $notif->createDate=time();
                                    $subject=CActiveRecord::model('ProfileChild')->findByAttributes(array('username'=>$model->user));
                                    $notif->user=$subject->username;
                                    if($notif->user!=Yii::app()->user->getName())
                                        $notif->save();
                                    
                                    $notif=new Notifications;
                                    $prof=CActiveRecord::model('ProfileChild')->findByAttributes(array('username'=>$soc->user));
                                    $subject=CActiveRecord::model('ProfileChild')->findByPk($model->associationId);
                                    $notif->text="$prof->fullName added a comment to a post.";
                                    $notif->record="profile:$model->associationId";
                                    $notif->viewed=0;
                                    $notif->createDate=time();
                                    $notif->user=$subject->username;
                                    if($notif->user!=Yii::app()->user->getName())
                                        $notif->save();
				}
			}
			if(isset($_GET['redirect'])) {
				if($_GET['redirect']=="view")
					$this->redirect(array('view','id'=>$model->associationId));
				if($_GET['redirect']=="index")
					$this->redirect(array('index'));
			} else
				$this->redirect(array('index'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$dataProvider=new CActiveDataProvider('Social',array(
			'criteria'=>array(
				'condition'=>"type='feed' AND (private!=1 OR associationId=".Yii::app()->user->getId()." OR user='".Yii::app()->user->getName()."')",
				'order'=>'lastUpdated DESC'
			),
		));
		$users=UserChild::getProfiles();
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
			'users'=>$users,
		));
	}
	
	public function actionProfiles(){
		$model=new ProfileChild('search');
		$pageParam = ucfirst($this->modelClass). '_page';
		if (isset($_GET[$pageParam])) {
			$page = $_GET[$pageParam];
			Yii::app()->user->setState($this->id.'-page',(int)$page);
		} else {
			$page=Yii::app()->user->getState($this->id.'-page',1);
			$_GET[$pageParam] = $page;
		}

		if (intval(Yii::app()->request->getParam('clearFilters'))==1) {
			EButtonColumnWithClearFilters::clearFilters($this,$model);//where $this is the controller
		}
		$this->render('profiles',array(
			'model'=>$model,
		));
	}
	/**
	 * Manages all models.
	 */


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=ProfileChild::model('ProfileChild')->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('app','The requested page does not exist.'));
		return $model;
	}
	
	private function getLanguageName($code,$languageDirs) {	// lookup language name for the language code provided

		if (in_array($code,$languageDirs)) {	// is the language pack here?
				$appMessageFile = "protected/messages/$code/app.php";
				if(file_exists($appMessageFile)) {	// attempt to load 'app' messages in
						$appMessages = include($appMessageFile);					// the chosen language
						if (is_array($appMessages) and isset($appMessages['languageName']) && $appMessages['languageName']!='Template')
								return $appMessages['languageName'];							// return language name
				}
		}
		return false;	// false if languge pack wasn't there
	}
	
	
	
	private function getTimeZones(){
		return array(
			'Pacific/Midway'    => "(GMT-11:00) Midway Island",
			'US/Samoa'          => "(GMT-11:00) Samoa",
			'US/Hawaii'         => "(GMT-10:00) Hawaii",
			'US/Alaska'         => "(GMT-09:00) Alaska",
			'US/Pacific'        => "(GMT-08:00) Pacific Time (US & Canada)",
			'America/Tijuana'   => "(GMT-08:00) Tijuana",
			'US/Arizona'        => "(GMT-07:00) Arizona",
			'US/Mountain'       => "(GMT-07:00) Mountain Time (US & Canada)",
			'America/Chihuahua' => "(GMT-07:00) Chihuahua",
			'America/Mazatlan'  => "(GMT-07:00) Mazatlan",
			'America/Mexico_City' => "(GMT-06:00) Mexico City",
			'America/Monterrey' => "(GMT-06:00) Monterrey",
			'Canada/Saskatchewan' => "(GMT-06:00) Saskatchewan",
			'US/Central'        => "(GMT-06:00) Central Time (US & Canada)",
			'US/Eastern'        => "(GMT-05:00) Eastern Time (US & Canada)",
			'US/East-Indiana'   => "(GMT-05:00) Indiana (East)",
			'America/Bogota'    => "(GMT-05:00) Bogota",
			'America/Lima'      => "(GMT-05:00) Lima",
			'America/Caracas'   => "(GMT-04:30) Caracas",
			'Canada/Atlantic'   => "(GMT-04:00) Atlantic Time (Canada)",
			'America/La_Paz'    => "(GMT-04:00) La Paz",
			'America/Santiago'  => "(GMT-04:00) Santiago",
			'Canada/Newfoundland'  => "(GMT-03:30) Newfoundland",
			'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
			'Greenland'         => "(GMT-03:00) Greenland",
			'Atlantic/Stanley'  => "(GMT-02:00) Stanley",
			'Atlantic/Azores'   => "(GMT-01:00) Azores",
			'Atlantic/Cape_Verde' => "(GMT-01:00) Cape Verde Is.",
			'Africa/Casablanca' => "(GMT) Casablanca",
			'Europe/Dublin'     => "(GMT) Dublin",
			'Europe/Lisbon'     => "(GMT) Lisbon",
			'Europe/London'     => "(GMT) London",
			'Africa/Monrovia'   => "(GMT) Monrovia",
			'UTC'				=> "(UTC)",
			'Europe/Amsterdam'  => "(GMT+01:00) Amsterdam",
			'Europe/Belgrade'   => "(GMT+01:00) Belgrade",
			'Europe/Berlin'     => "(GMT+01:00) Berlin",
			'Europe/Bratislava' => "(GMT+01:00) Bratislava",
			'Europe/Brussels'   => "(GMT+01:00) Brussels",
			'Europe/Budapest'   => "(GMT+01:00) Budapest",
			'Europe/Copenhagen' => "(GMT+01:00) Copenhagen",
			'Europe/Ljubljana'  => "(GMT+01:00) Ljubljana",
			'Europe/Madrid'     => "(GMT+01:00) Madrid",
			'Europe/Paris'      => "(GMT+01:00) Paris",
			'Europe/Prague'     => "(GMT+01:00) Prague",
			'Europe/Rome'       => "(GMT+01:00) Rome",
			'Europe/Sarajevo'   => "(GMT+01:00) Sarajevo",
			'Europe/Skopje'     => "(GMT+01:00) Skopje",
			'Europe/Stockholm'  => "(GMT+01:00) Stockholm",
			'Europe/Vienna'     => "(GMT+01:00) Vienna",
			'Europe/Warsaw'     => "(GMT+01:00) Warsaw",
			'Europe/Zagreb'     => "(GMT+01:00) Zagreb",
			'Europe/Athens'     => "(GMT+02:00) Athens",
			'Europe/Bucharest'  => "(GMT+02:00) Bucharest",
			'Africa/Cairo'      => "(GMT+02:00) Cairo",
			'Africa/Harare'     => "(GMT+02:00) Harare",
			'Europe/Helsinki'   => "(GMT+02:00) Helsinki",
			'Europe/Istanbul'   => "(GMT+02:00) Istanbul",
			'Asia/Jerusalem'    => "(GMT+02:00) Jerusalem",
			'Europe/Kiev'       => "(GMT+02:00) Kyiv",
			'Europe/Minsk'      => "(GMT+02:00) Minsk",
			'Europe/Riga'       => "(GMT+02:00) Riga",
			'Europe/Sofia'      => "(GMT+02:00) Sofia",
			'Europe/Tallinn'    => "(GMT+02:00) Tallinn",
			'Europe/Vilnius'    => "(GMT+02:00) Vilnius",
			'Asia/Baghdad'      => "(GMT+03:00) Baghdad",
			'Asia/Kuwait'       => "(GMT+03:00) Kuwait",
			'Europe/Moscow'     => "(GMT+03:00) Moscow",
			'Africa/Nairobi'    => "(GMT+03:00) Nairobi",
			'Asia/Riyadh'       => "(GMT+03:00) Riyadh",
			'Europe/Volgograd'  => "(GMT+03:00) Volgograd",
			'Asia/Tehran'       => "(GMT+03:30) Tehran",
			'Asia/Baku'         => "(GMT+04:00) Baku",
			'Asia/Muscat'       => "(GMT+04:00) Muscat",
			'Asia/Tbilisi'      => "(GMT+04:00) Tbilisi",
			'Asia/Yerevan'      => "(GMT+04:00) Yerevan",
			'Asia/Kabul'        => "(GMT+04:30) Kabul",
			'Asia/Yekaterinburg' => "(GMT+05:00) Ekaterinburg",
			'Asia/Karachi'      => "(GMT+05:00) Karachi",
			'Asia/Tashkent'     => "(GMT+05:00) Tashkent",
			'Asia/Kolkata'      => "(GMT+05:30) Kolkata",
			'Asia/Kathmandu'    => "(GMT+05:45) Kathmandu",
			'Asia/Almaty'       => "(GMT+06:00) Almaty",
			'Asia/Dhaka'        => "(GMT+06:00) Dhaka",
			'Asia/Novosibirsk'  => "(GMT+06:00) Novosibirsk",
			'Asia/Bangkok'      => "(GMT+07:00) Bangkok",
			'Asia/Jakarta'      => "(GMT+07:00) Jakarta",
			'Asia/Krasnoyarsk'  => "(GMT+07:00) Krasnoyarsk",
			'Asia/Chongqing'    => "(GMT+08:00) Chongqing",
			'Asia/Hong_Kong'    => "(GMT+08:00) Hong Kong",
			'Asia/Irkutsk'      => "(GMT+08:00) Irkutsk",
			'Asia/Kuala_Lumpur' => "(GMT+08:00) Kuala Lumpur",
			'Australia/Perth'   => "(GMT+08:00) Perth",
			'Asia/Singapore'    => "(GMT+08:00) Singapore",
			'Asia/Taipei'       => "(GMT+08:00) Taipei",
			'Asia/Ulaanbaatar'  => "(GMT+08:00) Ulaan Bataar",
			'Asia/Urumqi'       => "(GMT+08:00) Urumqi",
			'Asia/Seoul'        => "(GMT+09:00) Seoul",
			'Asia/Tokyo'        => "(GMT+09:00) Tokyo",
			'Asia/Yakutsk'      => "(GMT+09:00) Yakutsk",
			'Australia/Adelaide' => "(GMT+09:30) Adelaide",
			'Australia/Darwin'  => "(GMT+09:30) Darwin",
			'Australia/Brisbane' => "(GMT+10:00) Brisbane",
			'Australia/Canberra' => "(GMT+10:00) Canberra",
			'Pacific/Guam'      => "(GMT+10:00) Guam",
			'Australia/Hobart'  => "(GMT+10:00) Hobart",
			'Australia/Melbourne' => "(GMT+10:00) Melbourne",
			'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
			'Australia/Sydney'  => "(GMT+10:00) Sydney",
			'Asia/Vladivostok'  => "(GMT+10:00) Vladivostok",
			'Asia/Magadan'      => "(GMT+11:00) Magadan",
			'Pacific/Auckland'  => "(GMT+12:00) Auckland",
			'Pacific/Fiji'      => "(GMT+12:00) Fiji",
			'Asia/Kamchatka'    => "(GMT+12:00) Kamchatka",
		);
	}
}
