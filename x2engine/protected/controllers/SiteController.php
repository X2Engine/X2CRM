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
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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

/**
 * Primary/default controller for the web application.
 * 
 * @package X2CRM.controllers
 */
class SiteController extends x2base {
	// Declares class-based actions.

	//public $layout = '//layouts/main';
	
	public $portlets = array();
	
	public function filters() {
		return array(
			'setPortlets',
		);
	}
    
    protected function beforeAction($action=null){
        $auth=Yii::app()->authManager;
        $action=ucfirst($this->getId()) . ucfirst($this->getAction()->getId());
        $authItem=$auth->getAuthItem($action);
        if(Yii::app()->user->checkAccess($action) || is_null($authItem)){
            return true;
        }elseif(Yii::app()->user->isGuest){
            $this->redirect($this->createUrl('/site/login'));
        }else{
            throw new CHttpException(403, 'You are not authorized to perform this action.');
        }
    }

	public function accessRules() {
		return array(
			array('allow',
				'actions'=>array('login','index','logout','warning','captcha'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('groupChat','newMessage','getMessages','checkNotifications','updateNotes','addPersonalNote',
				'getNotes','getURLs','addSite','deleteMessage','fullscreen','pageOpacity','widgetState','widgetOrder','saveGridviewSettings','saveFormSettings',
					'saveWidgetHeight','inlineEmail','tmpUpload','upload','uploadProfilePicture','index','error','contact','viewNotifications','inlineEmail', 'toggleShowTags', 'appendTag', 'removeTag', 'addRelationship'),
				'users'=>array('@'),
			),
			// array('allow',
				// 'actions'=>array('index'),
				// 'users'=>array('admin'),
			// ),
			array('deny', 
				'users'=>array('*')
			)
		);
	}
	
	public function actions() {
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
				'testLimit'=>1,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
			'inlineEmail'=>array(
				'class'=>'InlineEmailAction',
			),
		);
	}
	
//	/**
//	 * Obtain the widget list for the current web user.
//	 * 
//	 * @param CFilterChain $filterChain 
//	 */
//	public function filterSetPortletsq($filterChain){
//		if(!Yii::app()->user->isGuest){
//			$this->portlets=array();
//			$this->portlets = ProfileChild::getWidgets();
//			// $this->portlets=array();
//			// $arr=ProfileChild::getWidgets(Yii::app()->user->getId());
//
//			// foreach($arr as $key=>$value){
//				// $config=ProfileChild::parseWidget($value,$key);
//				// $this->portlets[$key]=$config;
//			// }
//		}
//		$filterChain->run();
//	}
	
	/**
	 * Default landing page action for the web application.
	 * 
	 * Displays a feed of new records that have been created since the last
	 * login of the current web user.
	 */
	public function actionWhatsNew(){
		
		if(!Yii::app()->user->isGuest){
		
			$user = User::model()->findByPk(Yii::app()->user->getId());
			$lastLogin = $user->lastLogin;

			$contacts = CActiveRecord::model('Contacts')->findAll("lastUpdated > $lastLogin ORDER BY lastUpdated DESC LIMIT 50");
			$actions = CActiveRecord::model('Actions')->findAll("lastUpdated > $lastLogin AND (assignedTo='".Yii::app()->user->getName()."' OR assignedTo='Anyone') ORDER BY lastUpdated DESC LIMIT 50");
			$opportunities = CActiveRecord::model('Opportunity')->findAll("lastUpdated > $lastLogin ORDER BY lastUpdated DESC LIMIT 50");
			$accounts = CActiveRecord::model('Accounts')->findAll("lastUpdated > $lastLogin ORDER BY lastUpdated DESC LIMIT 50");

			$arr = array_merge($contacts,$actions,$opportunities,$accounts);

			$records = Record::convert($arr);

			$dataProvider=new CArrayDataProvider($records,array(
				'id'=>'id',
				'pagination'=>array(
					'pageSize'=>ProfileChild::getResultsPerPage(),
				),
				'sort'=>array(
					'attributes'=>array(
						 'lastUpdated', 'name',
					),
				),
			));

			$this->render('whatsNew',array(
				'records'=>$records,
				'dataProvider'=>$dataProvider,
			));
		}
		else{
			$this->redirect('login');
		}
	}

	/**
	 * Displays message of the day.
	 */
	public function actionMotd() {
		if(isset($_POST['message'])){
			$motd=$_POST['message'];
			$temp=Social::model()->findByAttributes(array('type'=>'motd'));
			$temp->data=$motd;
			if($temp->update())
				echo $motd;
			else
				echo "An error has occured.";
		}else{
			echo "An error has occured.";
		}
	}

	/**
	 * Renders the group chat.
	 */
	public function actionGroupChat() {
		$this->portlets = array();
		$this->layout='//layouts/column1';
		//$portlets = $this->portlets;
		// display full screen group chat
		$this->render('groupChat');
	}
	
	/**
	 * Creates a new chat message from the current web user.
	 */
	public function actionNewMessage() {
		if (isset($_POST['chat-message']) && $_POST['chat-message']!=''
			&& $_POST['chat-message']!=Yii::t('app','Enter text here...')) {

			$user=Yii::app()->user->getName();
			$chat=new Social;
			$chat->data = $_POST['chat-message'];;
			$chat->user = $user;
			$chat->timestamp = time();
			$chat->type = 'chat';
			
			if($chat->save()) {
				echo CJSON::encode(array(
					array(
						$chat->id,
						date('g:i:s A',$chat->timestamp),
						'<span class="my-username">'.$chat->user.'</span>',
						$this->convertUrls($chat->data)
					)
				));
			}
		}
	}

	/**
	 * Add a personal note to the list of notes for the current web user.
	 */
	public function actionAddPersonalNote() {
		if (isset($_POST['note-message']) && $_POST['note-message']!='') {
			$user=Yii::app()->user->getName();
			$note=new Social;
			$note->associationId=Yii::app()->user->getId();
			$note->data = $_POST['note-message'];;
			$note->user = $user;
			$note->timestamp=time();
			$note->type = 'note';
			
			if($note->save()) {
				echo "1";
			}
		}
	}

	/**
	 * Adds a new URL 
	 */
	public function actionAddSite(){
		if((isset($_POST['url-title'])&&isset($_POST['url-url'])) &&($_POST['url-title']!=''&&$_POST['url-url']!='')) {
			$site = new URL;
			$site->title = $_POST['url-title'];
			$site->url = $_POST['url-url'];
			$site->userid = Yii::app()->user->getId();
			$site->timestamp = time();
			if ($site->save()){
				echo '1';
			}
		}
	}

	/**
	 * Obtains notes for displaying within the notes widget.
	 * @param string $url The deletion URL for notes
	 */
	public function actionGetNotes($url) {
		$content=Social::model()->findAllByAttributes(array('type'=>'note','associationId'=>Yii::app()->user->getId()),array(
			'order'=>'timestamp DESC',
		));
		$res="";
		foreach($content as $item){
			$res .= $this->convertUrls($item->data)." ".CHtml::link('[x]',array('site/deleteMessage','id'=>$item->id,'url'=>$url)).'<br /><br />';
		}
		if($res==""){
			$res=Yii::t('app',"Feel free to enter some notes!");
		}
		echo $res;
	}

	/**
	 * Gets URLs for "top sites"
	 * @param string $url 
	 */
	public function actionGetURLs($url) {
		$content = URL::model()->findAllByAttributes(array('userid'=>Yii::app()->user->getId()),array(
			'order'=>'timestamp DESC',
		));
		$res ='<table><tr><th>Title</th><th>Link</th></tr>';
		if($content){
			foreach($content as $entry){
				$res .= '<tr><td>'.$entry->title."</td><td><a href='".$entry->url."'>LINK</a></td></tr>";
			}
		}else {
			$res .= "<tr><td>Example</td><td><a href='.'>LINK</a></td></tr>";
		}
		echo $res;
	}
	
	/**
	 * Delete a message from the social feed.
	 * @param integer $id
	 * @param string $url 
	 */
	public function actionDeleteMessage($id,$url){
		$note=Social::model()->findByPk($id);
		$note->delete();
		$this->redirect($url);
	} 

	/**
	 * Sets "Fullscreen" mode for the current web user / session
	 */
	public function actionFullscreen() {
		Yii::app()->session['fullscreen'] = (isset($_GET['fs']) && $_GET['fs'] == 1);
		// echo var_dump(Yii::app()->session['fullscreen']);
		echo 'Success';
	}
	
	/**
	 * Sets the page opacity for the current web user.
	 */
	public function actionPageOpacity() {
		if(isset($_GET['opacity']) && is_numeric($_GET['opacity'])) {

			$opacity = $_GET['opacity'];
			if($opacity > 1)
				$opacity = 1;
			if($opacity < 0.1)
				$opacity = 0.1;
		
			$opacity = round(100*$opacity);
			
			// $profile = CActiveRecord::model('ProfileChild')->findByPk(Yii::app()->user->getId());

			Yii::app()->params->profile->pageOpacity = $opacity;
			if(Yii::app()->params->profile->save()){
				echo "success";
			}
		}
	}

	/**
	 * Checks for the widget's state. 
	 */
	public function actionWidgetState() {
		
		if(isset($_GET['widget']) && isset($_GET['state'])) {
			$widgetName = $_GET['widget'];
			$widgetState = ($_GET['state']==0)? '0' : '1';
			
			// $profile = Yii::app()->params->profile;
			
			$order = explode(":",Yii::app()->params->profile->widgetOrder);
			$visibility = explode(":",Yii::app()->params->profile->widgets);

				// var_dump($order);
				// var_dump($visibility);
			if(array_key_exists($widgetName,Yii::app()->params->registeredWidgets)) {

				$pos = array_search($widgetName,$order);
				$visibility[$pos] = $widgetState;
				// die(var_dump($visibility));
			
				Yii::app()->params->profile->widgets = implode(':',$visibility);
				
				if(Yii::app()->params->profile->save()){
					echo 'success';
				}
			}
		}
	}

	/**
	 * Responds with the order of widgets for the current user.
	 */
	public function actionWidgetOrder() {
		if(isset($_POST['widget'])) {

			$widgetList = $_POST['widget'];
			
			// $profile = Yii::app()->params->profile;
			$order = Yii::app()->params->profile->widgetOrder;
			$visibility = Yii::app()->params->profile->widgets;
			
			$order = explode(":",$order);
			$visibility = explode(":",$visibility);
			
			$newOrder = array();
			
			foreach($widgetList as $item) {
				if(array_key_exists($item,Yii::app()->params->registeredWidgets))
					$newOrder[] = $item;
			}
			$str="";
			$visStr="";
			foreach($newOrder as $item){
				$pos=array_search($item,$order);
				$vis=$visibility[$pos];
				$str.=$item.":";
				$visStr.=$vis.":";
			}
			$str = substr($str,0,-1);
			$visStr = substr($visStr,0,-1);
			
			Yii::app()->params->profile->widgetOrder = $str;
			Yii::app()->params->profile->widgets = $visStr;
			
			if(Yii::app()->params->profile->save()){
				echo 'success';
			}
		}
	}
	
	/**
	 * Save custom gridview settings.
	 * 
	 * Saves the settings (i.e. columns, column position and column width)
	 * for the X2GridView model. 
	 */
	public function actionSaveGridviewSettings() {
		
		$result = false;
		if(isset($_GET['gvSettings']) && isset($_GET['viewName'])) {
			$gvSettings = json_decode($_GET['gvSettings'],true);
			
			if(isset($gvSettings))
				$result = ProfileChild::setGridviewSettings($gvSettings,$_GET['viewName']);
		}
		if($result)
			echo '200 Success';
		else
			echo '400 Failure';
	}
	
	/**
	 * Save settings for a custom form layout.
	 * 
	 * @throws CHttpException 
	 */
	public function actionSaveFormSettings() {
		$result = false;
		if(isset($_GET['formSettings']) && isset($_GET['formName'])) {
			$formSettings = json_decode($_GET['formSettings'],true);
			
			if(isset($formSettings))
				$result = ProfileChild::setFormSettings($formSettings,$_GET['formName']);
		}
		if($result)
			echo 'success';
		else
			throw new CHttpException(400,'Invalid request. Probabaly something wrong with the JSON string.');
	}
	
	/**
	 * Saves the height of a widget. 
	 */
	public function actionSaveWidgetHeight() {
		if( isset($_POST['Widget']) && isset($_POST['Height']) ) {
			$heights = $_POST['Height'];
			$widget = $_POST['Widget'];
			$widgetSettings = ProfileChild::getWidgetSettings();
			
			foreach($heights as $key=>$height) {
				$widgetSettings->$widget->$key = intval($height);
			}
			
			Yii::app()->params->profile->widgetSettings = json_encode($widgetSettings);
			Yii::app()->params->profile->update();
		}
	}

	/**
	 * Uploads a file to a temporary folder.
	 * 
	 * Upload a file to a temp folder, which will presumably be deleted shortly thereafter
	 * Temp files are stored in a temp folder with a randomly generated name. They are stored
	 * in 'uploads/media/temp'
	 */
	public function actionTmpUpload() {
		if(isset($_FILES['upload'])) {
			$upload = CUploadedFile::getInstanceByName('upload');
			
			if($upload) {
			
				$name=$upload->getName();
				$name=str_replace(' ','_',$name);
				
				$temp = TempFile::createTempFile($name);
				
				if($temp && $upload->saveAs($temp->fullpath())) // temp file saved
					echo json_encode(array('status' => 'success', 'id' => $temp->id, 'name' => $name));
				else
					echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file.')));
			} else {
				echo json_encode(array('status' => 'notsent', 'message' => Yii::t('media', 'File was not sent to server.')));
			}
		} else {
			echo json_encode(array('status' => 'fail', 'message' => Yii::t('media', 'Failed to upload file.')));
		}
	}
	
	/**
	 * Remove a temp file and the temp folder that is in.
	 */
	public function actionRemoveTmpUpload() {
		if(isset($_POST['id'])) {
			$id = $_POST['id'];
			if(is_numeric($id)) {
				$tempFile = TempFile::model()->findByPk($id);
				$folder = $tempFile->folder;
				$name = $tempFile->name;
				if(file_exists('uploads/media/temp/'. $folder .'/'. $name))
					unlink('uploads/media/temp/'. $folder .'/'. $name); // delete file
				if(file_exists('uploads/media/temp/'. $folder))
					rmdir('uploads/media/temp/'. $folder); // delete folder
				$tempFile->delete(); // delete database entry tracking temp file
			}
		}
	}

	/**
	 * Upload a file.
	 */
	public function actionUpload() {
		if(isset($_FILES['upload'])) {
			$model=new Media;
			$temp = CUploadedFile::getInstanceByName('upload');
			$name=$temp->getName();
			$name=str_replace(' ','_',$name);
			$check=Media::model()->findAllByAttributes(array('fileName'=>$name));
			if(count($check)!=0) {
				$count=1;
				$newName=$name;
				$arr=explode('.',$name);
				$name=$arr[0];
				while(count($check)!=0){
						$newName=$name.'('.$count.').'.$temp->getExtensionName();
						$check=Media::model()->findAllByAttributes(array('fileName'=>$newName));
						$count++;
				}
				$name=$newName;
			}
			$username = Yii::app()->user->name;
			if($this->ccopy($temp->getTempName(),"uploads/media/$username/$name")) {
				if(isset($_POST['associationId']))
					$model->associationId = $_POST['associationId'];
				if(isset($_POST['associationType']))
					$model->associationType = $_POST['associationType'];
				if(isset($_POST['private']))
					$model->private = $_POST['private'];
				$model->uploadedBy=Yii::app()->user->getName();
				$model->createDate=time();
				$model->lastUpdated = time();
				$model->fileName=$name;
				if($model->save()){

				}
				if($model->associationType=='feed') {
					$soc = new Social;
					$soc->user = Yii::app()->user->getName();
					$soc->data = Yii::t('app','Attached file: ').
					$soc->type = 'feed';
					$soc->timestamp = time();
					$soc->lastUpdated = time();
					$soc->associationId = $model->associationId;
					$soc->data = $model->getMediaLink();
					if($soc->save()) {
							$this->redirect(array('profile/'.$model->associationId));
					} else {
							unlink('uploads/'.$name);
					}
					$this->redirect(array($model->associationType.'/'.$model->associationId));

				} else if($model->associationType=='bg' || $model->associationType=='bg-private') {

					$profile=CActiveRecord::model('ProfileChild')->findByPk(Yii::app()->user->getId());
					$profile->backgroundImg = $name;
					$profile->save();
					$this->redirect(array('profile/settings','id'=>Yii::app()->user->getId()));
				} else if($model->associationType=='docs'){
					$this->redirect(array('docs/index'));
	//			} else if($model->private) {
	//				if($model->associationType == 'product')
	//					$this->redirect(array('/products/'.$model->associationId));
	//				$this->redirect(array($model->associationType.'/'.$model->associationId));
				}else {
					$note=new Actions;
					$note->createDate = time();
					$note->dueDate = time();
					$note->completeDate = time();
					$note->complete='Yes';
					$note->visibility='1';
					$note->completedBy=Yii::app()->user->getName();
					if($model->private) {
						$note->assignedTo = Yii::app()->user->getName();
						$note->visibility = '0';
					} else {
						$note->assignedTo='Anyone';
					}
					$note->type='attachment';
					$note->associationId=$_POST['associationId'];
					$note->associationType=$_POST['associationType'];

					$association = $this->getAssociation($note->associationType,$note->associationId);
					if($association != null)
							$note->associationName = $association->name;

					$note->actionDescription = $model->fileName . ':' . $model->id;
					if($note->save()){
					} else {
							unlink('uploads/'.$name);
					}
					if($model->associationType == 'product')
						$this->redirect(array('/products/'.$model->associationId));
					$this->redirect(array($model->associationType.'/'.$model->associationId));
				}
			}
		}
	}

        /**
	 * Upload contact profile picture from Facebook.
	 */
	public function actionUploadProfilePicture() {
		if(isset($_POST['photourl'])) {
			$photourl = $_POST['photourl'];
			$name = 'profile_picture_'.$_POST['associationId'].'.jpg';
			$model = new Media;
			$check=Media::model()->findAllByAttributes(array('fileName'=>$name));
			if(count($check)!=0) {
				$count=1;
				$newName=$name;
				$arr=explode('.',$name);
				$name=$arr[0];
				while(count($check)!=0){
						$newName=$name.'('.$count.').jpg';
						$check=Media::model()->findAllByAttributes(array('fileName'=>$newName));
						$count++;
				}
				$name=$newName;
			}
			$model->associationId=$_POST['associationId'];
			$model->associationType=$_POST['type'];
			$model->createDate=time();
			$model->fileName=$name;
			
			// download and save picture
			$img = file_get_contents($photourl);
			file_put_contents('uploads/'.$name, $img);
			$model->save();
			
			// put picture into new action
			$note = new Actions;
			$note->createDate = time();
			$note->dueDate = time();
			$note->completeDate = time();
			$note->complete='Yes';
			$note->visibility='1';
			$note->completedBy="Web Lead";
			$note->assignedTo='Anyone';
			$note->type='attachment';
			$note->associationId=$_POST['associationId'];
			$note->associationType=$_POST['type'];

			$association = $this->getAssociation($note->associationType,$note->associationId);
			if($association != null) {
			    $note->associationName = $association->name;
			}
			$note->actionDescription = $model->fileName . ':' . $model->id;
			if($note->save()){
			} else {
			    	unlink('uploads/'.$name);
			}
			$this->redirect(array($model->associationType.'/'.$model->associationId));

		}
	}

	
	/**
	 * Index action.
	 * 
	 * This is the default 'index' action that is invoked when an action 
	 * is not explicitly requested by users.
	 */
	// 
	public function actionIndex() {
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		
		// check if we are on a mobile browser
		if(isset($_GET['mobile']) && $_GET['mobile'] == 'false') {
			$cookie = new CHttpCookie('x2mobilebrowser', 'false'); // create cookie
			$cookie->expire = time() + 31104000; // expires in 1 year
			Yii::app()->request->cookies['x2mobilebrowser'] = $cookie; // save cookie
		} else {
			$mobileBrowser = Yii::app()->request->cookies->contains('x2mobilebrowser') ? Yii::app()->request->cookies['x2mobilebrowser']->value : '';
			if($mobileBrowser == 'true')
			    $this->redirect(array('/mobile/site/index'));
		}
		
		if(Yii::app()->user->isGuest)
			$this->redirect(array('/site/login'));
		else {
			$profile = CActiveRecord::model('profile')->findByPk(Yii::app()->user->getId());
			if($profile->username=='admin'){
				$admin = &Yii::app()->params->admin;
				if(Yii::app()->session['versionCheck']==false && $admin->updateInterval > -1 && ($admin->updateDate + $admin->updateInterval < time()))
					Yii::app()->session['alertUpdate']=true;
				else
					Yii::app()->session['alertUpdate']=false;

			}else{
				Yii::app()->session['alertUpdate']=false;
			}
			
			if(empty($profile->startPage)) {
				$this->redirect(array('site/whatsNew'));
			} else {
				$controller = Yii::app()->file->set('protected/controllers/'.ucfirst($profile->startPage).'Controller.php');
				$module = Yii::app()->file->set('protected/modules/'.$profile->startPage.'/controllers/'.ucfirst($profile->startPage).'Controller.php');
				if($controller->exists || $module->exists){
					if($controller->exists)
						$this->redirect(array($profile->startPage.'/index'));
					if($module->exists)
						$this->redirect(array($profile->startPage.'/'.$profile->startPage.'/index'));
				} else {
					$page=DocChild::model()->findByAttributes(array('title'=>ucfirst($profile->startPage)));
					if(isset($page)) {
						$id=$page->id;
						$menuItems[$key] = array('label' =>ucfirst($value),'url' => array('/admin/viewPage/'.$id),'active'=>Yii::app()->request->requestUri==Yii::app()->request->baseUrl.'/index.php/admin/viewPage/'.$id?true:null);
				
					} else {
					$this->redirect(array('site/whatsNew'));
					}
				}
			}
		}
			
	}
        
         

	/**
	 * Error printing.
	 * 
	 * This is the action to handle external exceptions.
	 */
	public function actionError() { 
		if($error=Yii::app()->errorHandler->error) {
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}


	/**
	 *  Displays the About page
	 */
	public function actionContact() {
		$model=new ContactForm;
		if(isset($_POST['ContactForm'])) {
			$model->attributes=$_POST['ContactForm'];
			if($model->validate()) {
				$headers="From: {$model->email}\r\nReply-To: {$model->email}";
				mail(Yii::app()->params['adminEmail'],$model->subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Obtains the record association type for an object, i.e. contacts.
	 *
	 * @param string $type
	 * @param integer $id
	 * @return mixed 
	 */
	protected function getAssociation($type,$id) {
	
		$classes = array(
			'actions'=>'Actions',
			'contacts'=>'Contacts',
			'projects'=>'ProjectChild',
			'accounts'=>'Accounts',
			'product'=>'Product',
			'products'=>'Product',
			'Campaign'=>'Campaign',
			'quote'=>'Quote',
			'quotes'=>'Quote',
			'opportunities'=>'Opportunity',
			'social'=>'SocialChild',
		);
		
		if(array_key_exists($type,$classes) && $id != 0)
			return CActiveRecord::model($classes[$type])->findByPk($id);
		else
			return null;
	}

	/**
	 * View all notifications for the current web user.
	 */
	public function actionViewNotifications(){
		$dataProvider = new CActiveDataProvider('Notification',array(
			'criteria'=>array(
			// 'order'=>'viewed ASC',
				'condition'=>'user="'.Yii::app()->user->name.'"'
			),
			'sort'=>array(
				'defaultOrder'=>'createDate DESC'
			),
		));
		$this->render('viewNotifications',array(
			'dataProvider'=>$dataProvider,
		));
	}

	public function actionWarning() {
		header("Content-type: image/gif");
		$img = 'R0lGODlhZABQAPcAANgAAP///w';
		for($i=0; $i<203; $i++)
			$img .= 'AAAAA';
		$img .= 'CwAAAAAZABQAAAI/wABCBxIsKDBgwgTKlzIsKHDhxAjSpxIsaLFixgzatzIsaPHjwkDiBS5cKRAkiBTKkQJIMBKlwNZqpxJUOZJkzdNjiS5s+VOnDph9qT5EqbPmzGNHk3acilSny6jClVK1CDQqUyzIpUKFaXMn1SrWuXq9CtVnmS9KrUptqBRtE+dLsUKd+7at23dDu2aFCdUpmrB+t2bt7Dhw4gTK57pV+viqmwfG7ZJ+GfOqYOHEn4cmaVnu1vTipYct+bbs5aPovWcWmPYiJ/jUg5bl29kjLcfxi6LNzZctWU3NpYItmvg04GNZ85NfDjptrWfiwUuHfrp6pDvTryKNaRzvWNdo//e3l33a9Mp2TL3HtqsWdPHMd+N/5KvXN0n0T9lfZC6ftCqnXdcafg1pZV/7vl3YHfR6bfXev01xh9vWSkoG4NkgXeZY+ZpCCCFAF63H12vsfYebMhxJ198mF024F/DJfgfdhlJGON5NDan12w54jabejj22FBnJQYpZFERIgShcS5eJ1VqKq5oZHhJVskQXgYaSFd+TWHpZUwQfXffjCGB2WVNSqLJ5ZdTUqnjlWbeeJaaWsa5JHxKijnmWJ8JSGeW8oHZJpnw4QihUGammSigay7qkJ6beahonWsiuuiXjXJ55aD1eZciUHLZ2GKTbeoZJqfY3bnpkZ4K5uqrrbHwWiistAom6624klchizC+aFlQtiKGKaVaWoqlpnNpiupHUfWXn3bEuuXgs5KV2BeazWYpraDFLpueVdiGa+xuZyLqLbPazilttseme6a2iQ1rKaVeXapmvcjmxd+v/Ga43GoBRprrwAQXbPDBCOMqZ6HXtvrkX/f1VNyfPDm60rbEzmukdl/RqTHG5XGqbrTKloRenxZnS/GkJnucL6MXc9tbyurtiHHLiY6r8sbicltps7SxPKixcRb98s1eamY0uM5arOjHMLfbdMlRL73WtE7zaSKeDecJrHIQR3m1oCMnjDPLZsdcZtpst+22QQEBADs=';
		echo base64_decode($img);
	}
	
	/**
	 * Displays the login page
	 */
	public function actionLogin() {	
		$this->layout = '//layouts/login';
	
		// echo var_dump(Session::getOnlineUsers());
		if(Yii::app()->user->isInitialized && !Yii::app()->user->isGuest) {
			$this->redirect(Yii::app()->homeUrl);
			return;
		}
		
		$model = new LoginForm;
		$model->useCaptcha = false;

		// collect user input data
		if(isset($_POST['LoginForm'])) {
			$model->attributes = $_POST['LoginForm'];
			$activeCheck=User::model()->findByAttributes(array('username'=>$model->username));
                        if(isset($activeCheck)){
                            $model->username=$activeCheck->username;
                        }
			if(isset($activeCheck) && $activeCheck->status=='1')
				$activeCheck=true;
			else
				$activeCheck=false;
			$ip = $this->getRealIp();
			x2base::cleanUpSessions();
			$session = CActiveRecord::model('Session')->findByAttributes(array('user'=>$model->username,'IP'=>$ip));
			if(isset($session)) {
				$session->lastUpdated = time();
				
				if($session->status < 1) {
					if($session->status > -3)
						$session->status -= 1;
				} else {
					$session->status = -1;
				}
				if($session->status < -1)
					$model->useCaptcha = true;
				if($session->status < -2)
					$model->setScenario('loginWithCaptcha');
			} else if($activeCheck) {
				$session = new Session;
				$session->user = $model->username;
				$session->lastUpdated = time();
				$session->status = 1;
				$session->IP = $ip;
			}

			if($model->validate() && $model->login()) {
				$user = User::model()->findByPk(Yii::app()->user->getId());
                $user->lastLogin=$user->login;
				$user->login = time();
				$user->save();
				if($user->username=='admin'){
					if(ini_get('allow_url_fopen') == 1) {
						$context = stream_context_create(array(
							'http' => array('timeout' => 2)		// set request timeout in seconds
						));
						$updateSources = array('http://x2planet.com/installs/updates/versionCheck');
						if (in_array(Yii::app()->params->admin['edition'],array('opensource',Null))) {
							$updateSources = array(
								'http://x2planet.com/updates/versionCheck.php',
								'http://x2base.com/updates/versionCheck.php'
							);
						}
						$newVersion = '';
						
						foreach($updateSources as $url) {
							$sourceVersion = @file_get_contents($url,0,$context);
							if($sourceVersion !== false) {
								$newVersion = $sourceVersion;
								break;
							}
						}
						if(empty($newVersion))
							$newVersion = Yii::app()->params->version;
						/* 
						// check X2Planet for updates
						$x2planetVersion = @file_get_contents('http://x2planet.com/updates/versionCheck.php',0,$context);
						if($x2planetVersion !== false)
							$newVersion = $x2planetVersion;
						else {
							// try X2Base if that didn't work
							$x2baseVersion = @file_get_contents('http://x2base.com/updates/versionCheck.php',0,$context);
							if($x2baseVersion !== false)
								$newVersion=$x2baseVersion;
							else
								$newVersion=Yii::app()->params->version;
						} */
						$unique_id = Yii::app()->params->admin['unique_id'];
						if(version_compare($newVersion,Yii::app()->params->version) > 0 && !in_array($unique_id, array('none',Null))) {	// if the latest version is newer than our version
							Yii::app()->session['versionCheck']=false;
							Yii::app()->session['newVersion']=$newVersion;
						}
						else
							Yii::app()->session['versionCheck']=true;
					}
					else
						Yii::app()->session['versionCheck']=true;
				} else
					Yii::app()->session['versionCheck']=true;
					
				Yii::app()->session['loginTime']=time();
                                $session->status=1;
				$session->save();

				if(Yii::app()->user->returnUrl=='site/index')
					$this->redirect('index');
				else
					$this->redirect(Yii::app()->user->returnUrl);
			} else if($activeCheck) {
				$session->save();
				$model->verifyCode = '';
				if($model->hasErrors())
					$model->addError('username',Yii::t('app','Incorrect username or password.'));
					$model->addError('password',Yii::t('app','Incorrect username or password.'));
			}
		}
		
		// display the login form
		$this->render('login',array('model'=>$model));
	}
	
	/**
	 * Log in using a Google account.
	 */
	public function actionGoogleLogin(){
		$this->layout = '//layouts/login';
		$model = new LoginForm;
		$model->useCaptcha = false;
	
		// echo var_dump(Session::getOnlineUsers());
		if(Yii::app()->user->isInitialized && !Yii::app()->user->isGuest) {
			$this->redirect(Yii::app()->homeUrl);
			return;
		}
		if(isset($_SESSION['access_token'])){
			require_once 'protected/extensions/google-api-php-client/src/apiClient.php';
			require_once 'protected/extensions/google-api-php-client/src/contrib/apiOauth2Service.php';

			$client = new apiClient();
			$client->setApplicationName("X2Engine CRM");
			// Visit https://code.google.com/apis/console to generate your
			// oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.
                        $admin=Admin::model()->findByPk(1);
			$client->setClientId($admin->googleClientId);
                        $client->setClientSecret($admin->googleClientSecret);
			$client->setRedirectUri('http://www.x2developer.com/x2jake/site/googleLogin');
			//$client->setDeveloperKey('insert_your_developer_key');
			$oauth2 = new apiOauth2Service($client);
			
			$client->setAccessToken($_SESSION['access_token']);
			
			$user = $oauth2->userinfo->get();
			$email = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
			
			$userRecord=User::model()->findByAttributes(array('emailAddress'=>$email));
			$profileRecord=Profile::model()->findByAttributes(array(), "emailAddress='$email' OR googleId='$email'");
			if(isset($userRecord) || isset($profileRecord)){
				if(!isset($userRecord)){
					$userRecord=User::model()->findByPk($profileRecord->id);
				}
				$username=$userRecord->username;
				$password=$userRecord->password;
				$model->username=$username;
				$model->password=$password;
				if($model->login(true)){
					$ip = $this->getRealIp();
					x2base::cleanUpSessions();
					$session = CActiveRecord::model('Session')->findByAttributes(array('user'=>$userRecord->username,'IP'=>$ip));
					if(isset($session)) {
						$session->lastUpdated = time();
					} else {
						$session = new Session;
						$session->user = $model->username;
						$session->lastUpdated = time();
						$session->status = 1;
						$session->IP = $ip;
					}
					$session->save();
					$userRecord->login = time();
					$userRecord->save();
					Yii::app()->session['versionCheck']=true;
					
					Yii::app()->session['loginTime']=time();
						$session->status=1;

					if(Yii::app()->user->returnUrl=='site/index')
						$this->redirect('index');
					else
						$this->redirect(Yii::app()->user->returnUrl);
				}else{
					print_r($model->getErrors());
				}
			}else{
				$this->render('googleLogin',array(
					'failure'=>'email',
					'email'=>$email,
				));
			}
		}else{
			$this->render('googleLogin');
		}
	}
	
	
	/**
	 * Toggle display of tags.
	 */
	public function actionToggleShowTags($tags) {
		if($tags == 'allUsers') {
			Yii::app()->params->profile->tagsShowAllUsers = true;
			Yii::app()->params->profile->update();
		} else if($tags == 'justMe') {
			Yii::app()->params->profile->tagsShowAllUsers = false;
			Yii::app()->params->profile->update();
		}
	}
	
	/**
	 * Add a tag to a module.
	 * 
	 * Echoes true if tag was created (and was not a duplicate)
	 */
	public function actionAppendTag() {
		if( isset($_POST['Type']) && isset($_POST['Id']) && isset($_POST['Tag']) ) {
			$type = ucfirst($_POST['Type']);
			$id = $_POST['Id'];
			$value = $_POST['Tag'];
			
			if($type == 'Quotes' || $type == 'Products') // fix for products and quotes
				$model = CActiveRecord::model(rtrim($type, 's'))->findByPk($id);
			elseif($type == 'Opportunities')
				$model = CActiveRecord::model('Opportunity')->findByPk($id);
			else
				$model = CActiveRecord::model($type)->findByPk($id);
			if($model) {
				
				// check for duplicate tag
				$tag = Tags::model()->findByAttributes(array('type'=>$type, 'itemId'=>$id, 'tag'=>$value));
				if($tag) {
					echo json_encode(false); // tag was a duplicate
					return;
				} else {
					// create tag
					$tag = new Tags;
					$tag->taggedBy = Yii::app()->user->name;
					$tag->timestamp = time();
					$tag->type = $type;
					$tag->itemId = $id;
					$tag->tag = $value;
					
					if($type == 'Contacts')
						$tag->itemName = $model->firstName." ".$model->lastName;
					else if($type == 'Actions')
						$tag->itemName = $model->actionDescription;
					else if($type == 'Docs')
						$tag->itemName = $model->title;
					else
						$tag->itemName = $model->name;
						
					if($tag->save()) {
						echo json_encode(true);
						return;
					}
				}
			}
		}
		json_encode(false);
	}
	
	/**
	 * Remove a tag from a module.
	 * 
	 * Echoes true if tag was removed.
	 */
	public function actionRemoveTag() {
		if( isset($_POST['Type']) && isset($_POST['Id']) && isset($_POST['Tag']) ) {
			$type = ucfirst($_POST['Type']);
			$id = $_POST['Id'];
			$value = $_POST['Tag'];
			if($type == 'Quotes' || $type == 'Products') // fix for products and quotes
				$model = CActiveRecord::model(rtrim($type, 's'))->findByPk($id);
			elseif($type == 'Opportunities')
				$model = CActiveRecord::model('Opportunity')->findByPk($id);
			else
				$model = CActiveRecord::model($type)->findByPk($id);
			if($model) {
				
				// make sure tag exists
				$tag = Tags::model()->findByAttributes(array('type'=>$type, 'itemId'=>$id, 'tag'=>$value));
				if($tag) {
					if($tag->delete()) {
						echo json_encode(true); // tag was removed
						return;
					}
				} else {
					// tag doesn't exist
					echo json_encode(false);
					return;
				}
			}
		}
		echo json_encode(false);
	}
	
	
	/**
	 * Add a record to record relationship
	 *
	 * A record can be a contact, opportunity, or account. This function is
	 * called via ajax from the Relationships Widget.
	 *
	 */
	public function actionAddRelationship() {
		//check if relationship already exits
		if(isset($_POST['ModelName']) && isset($_POST['ModelId']) && isset($_POST['RelationshipModelName']) && isset($_POST['RelationshipModelId'])) {
		
			$modelName = $_POST['ModelName'];
			$modelId = $_POST['ModelId'];
			$relationshipModelName = $_POST['RelationshipModelName'];
			$relationshipModelId = $_POST['RelationshipModelId'];
		
			$relationship = Relationships::model()->findByAttributes(array(
				'firstType'=>$_POST['ModelName'],
				'firstId'=>$_POST['ModelId'],
				'secondType'=>$_POST['RelationshipModelName'],
				'secondId'=>$_POST['RelationshipModelId'],
			));
			if($relationship) {
				echo "duplicate";
				Yii::app()->end();
			}
			$relationship = Relationships::model()->findByAttributes(array(
				'firstType'=>$_POST['RelationshipModelName'],
				'firstId'=>$_POST['RelationshipModelId'],
				'secondType'=>$_POST['ModelName'],
				'secondId'=>$_POST['ModelId'],
			));
			if($relationship) {
				echo "duplicate";
				Yii::app()->end();
			}
			
			$relationship = new Relationships;
			$relationship->firstType = $_POST['ModelName'];
			$relationship->firstId = $_POST['ModelId'];
			$relationship->secondType = $_POST['RelationshipModelName'];
			$relationship->secondId = $_POST['RelationshipModelId'];
			$relationship->save();
			if($relationshipModelName == "Contacts") {
				$results = Yii::app()->db->createCommand("SELECT * from x2_relationships WHERE (firstType='Contacts' AND firstId=$relationshipModelId AND secondType='Accounts') OR (secondType='Contacts' AND secondId=$relationshipModelId AND firstType='Accounts')")->queryAll();
				if(sizeof($results) == 1) {
					$model = Contacts::model()->findByPk($relationshipModelId);
					if($model) {
						$model->company = $modelId;
						$model->update();
					}
				}
			}
			echo "success";
			Yii::app()->end();
		}
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout() {
		$user = User::model()->findByPk(Yii::app()->user->getId());
		if(isset($user)) {
			$user->lastLogin=time();
			// $session = Session::model()->findByAttributes(array('user'=>$user->username));
			$session = Session::model()->deleteAllByAttributes(array('user'=>$user->username));
			// if(isset($session))
				// $session->delete();
			$user->save();
		}
		if(isset($_SESSION['access_token'])){
			unset($_SESSION['access_token']);
		}
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}
