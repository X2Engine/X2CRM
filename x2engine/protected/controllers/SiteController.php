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

class SiteController extends x2base {
	// Declares class-based actions.

	//public $layout = '//layouts/main';
	
	public $portlets = array();
	
	public function filters() {
		return array(
			'setPortlets',
		);
	}
	
	public function accessRules() {
		return array();
	}
	
	public function filterSetPortlets($filterChain){
		if(!Yii::app()->user->isGuest){
			$this->portlets=array();
			$this->portlets = ProfileChild::getWidgets();
			// $this->portlets=array();
			// $arr=ProfileChild::getWidgets(Yii::app()->user->getId());

			// foreach($arr as $key=>$value){
				// $config=ProfileChild::parseWidget($value,$key);
				// $this->portlets[$key]=$config;
			// }
		}
		$filterChain->run();
	}
	
	public function actions() {
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}
	
	public function actionWhatsNew(){
		
		if(!Yii::app()->user->isGuest){
		
			$user=UserChild::model()->findByPk(Yii::app()->user->getId());
			$lastLogin=$user->lastLogin;

			$contacts=ContactChild::model()->findAll("lastUpdated > $lastLogin");
			$actions=ActionChild::model()->findAll("lastUpdated > $lastLogin AND (assignedTo='".Yii::app()->user->getName()."' OR assignedTo='Anyone')");
			$sales=SaleChild::model()->findAll("lastUpdated > $lastLogin");
			$accounts=AccountChild::model()->findAll("lastUpdated > $lastLogin");

			$arr=array_merge($contacts,$actions,$sales,$accounts);
			//$arr=array_merge($arr,$sales);
			//$arr=array_merge($arr,$accounts);

			$records=Record::convert($arr);

			$dataProvider=new CArrayDataProvider($records,array(
				'id'=>'id',
				'pagination'=>array(
					'pageSize'=>10,
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

	public function actionGroupChat() {
		$this->layout='//layouts/column2';
		//$portlets = $this->portlets;
		// display full screen group chat
		$this->render('groupChat');
	}
	
	
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
				echo '1';
			}
		}
	}

	public function actionGetMessages() {
	
		$lastIdCriterion = '';
		if(isset($_POST['latestId']) && is_numeric($_POST['latestId']))	// if the client specifies the last message ID received,
			$lastIdCriterion = ' AND id > '.$_POST['latestId'];		// only send newer messages

		
		$time=time();
		$chatLog=new CActiveDataProvider('Social', array(
			'criteria'=>array(
				'order'=>'timestamp ASC',														// only get messages from today,
				'condition'=>"type='chat' AND timestamp > " . mktime(0,0,0) . $lastIdCriterion	// and (optionally) only new messages
			),
			'pagination'=>false,
		));
		$records = $chatLog->getData(); //array_reverse($chatLog->getData());
		$messages = array();

		foreach($records as $model) {
			if(isset($model)){
				$user=UserChild::model()->findByAttributes(array('username'=>$model->user));
				if(isset($user)){
					$html = '<div class="message">';
					if($user->id == Yii::app()->user->getId())	// if it's me, then make it grey and not a link
						$html.='<span class="my-username">'.$user->username.'</span>';
					else
						$html.=CHtml::link($user->username,array('profile/view','id'=>$user->id),array('class'=>'username'));

					$html .= '<span class="chat-timestamp"> ('.date('g:i:s A',$model->timestamp).')</span>';
					$html .= ': '.$this->convertUrls($model->data)."</div>\n";

					//$html = "[$lastIdCriterion]";
					$messages[] = array(
						'message' => $html,
						'id' => $model->id,
					);
				}
		}
		}
		echo json_encode($messages);
	}

	public function actionUpdateNotes(){
		$content=Social::model()->findAllByAttributes(array('type'=>'note','associationId'=>Yii::app()->user->getId()), 'order timestamp DESC');
		$res="";
		foreach($content as $item){
			$res.=$item->data."<br /><br />";
		}
	}
	
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
	
	public function actionGetNotes($url) {
		$content=Social::model()->findAllByAttributes(array('type'=>'note','associationId'=>Yii::app()->user->getId()),array(
			'order'=>'timestamp DESC',
		));
		$res="";
		foreach($content as $item){
			$res .= $this->convertUrls($item->data)." ".CHtml::link('[x]',array('site/deleteMessage','id'=>$item->id,'url'=>$url))."<br /><br />";
		}
		if($res==""){
			$res=Yii::t('app',"Feel free to enter some notes!");
		}
		echo $res;
	}
	
	public function actionDeleteMessage($id,$url){
		$note=Social::model()->findByPk($id);
		$note->delete();
		$this->redirect($url);
	} 

	public function actionFullscreen() {
		Yii::app()->session['fullscreen'] = (isset($_GET['fs']) && $_GET['fs'] == 1);
		// echo var_dump(Yii::app()->session['fullscreen']);
		echo 'Success';
	}
	
	public function actionPageOpacity() {
		if(isset($_GET['opacity']) && is_numeric($_GET['opacity'])) {

			$opacity = $_GET['opacity'];
			if($opacity > 1)
				$opacity = 1;
			if($opacity < 0.1)
				$opacity = 0.1;
		
			$opacity = round(100*$opacity);
			
			$profile = CActiveRecord::model('ProfileChild')->findByPk(Yii::app()->user->getId());

			$profile->pageOpacity = $opacity;
			if($profile->save()){
				echo "success";
			}
		}
	}
	
	public function actionWidgetState() {
		
		if(isset($_GET['widget']) && isset($_GET['state'])) {
			$widgetName = $_GET['widget'];
			$widgetState = ($_GET['state']==0)? 0 : 1;
			
			$profile = CActiveRecord::model('ProfileChild')->findByPk(Yii::app()->user->getId());
			
			$order = explode(":",$profile->widgetOrder);
			$visibility = explode(":",$profile->widgets);

			if(array_key_exists($widgetName,Yii::app()->params->registeredWidgets)) {

				$pos = array_search($widgetName,$order);
				$visibility[$pos] = $widgetState;
			
				$profile->widgets = implode(':',$visibility);
				
				if($profile->save()){
					echo "success";
				}
			}
		}
	}

	public function actionWidgetOrder() {
		if(isset($_POST['widget'])) {

			$widgetList = $_POST['widget'];
			
			$prof = CActiveRecord::model('ProfileChild')->findByPk(Yii::app()->user->getId());
			$order = $prof->widgetOrder;
			$visibility=$prof->widgets;
			
			$order = explode(":",$order);
			$visibility = explode(":",$visibility);
			
			$newOrder = array();
			
			foreach($widgetList as $item) {
				if(array_key_exists($item,Yii::app()->params->registeredWidgets))
					$newOrder[] = $item;
				// if($item=='quickContact'){
					// $newOrder[]='QuickContact';
				// }else if($item=='actionMenu'){
					// $newOrder[]='ActionMenu';
				// }else if($item=='chat'){
					// $newOrder[]="ChatBox";
				// }else if($item=='motd'){
					// $newOrder[]="MessageBox";
				// }else if($item=='noteForm'){
					// $newOrder[]='NoteBox';
				// }
			}
			$str="";
			$visStr="";
			foreach($newOrder as $item){
				$pos=array_search($item,$order);
				$vis=$visibility[$pos];
				$str.=$item.":";
				$visStr.=$vis.":";
			}
			$str=substr($str,0,-1);
			$visStr=substr($visStr,0,-1);
			
			$prof->widgetOrder=$str;
			$prof->widgets=$visStr;
			
			if($prof->save()){
				
			}
		}
	}

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
			if($temp->saveAs('uploads/'.$name)) {
				if(isset($_POST['associationId']))
					$model->associationId=$_POST['associationId'];
				$model->associationType=$_POST['type'];
				$model->uploadedBy=Yii::app()->user->getName();
				$model->createDate=time();
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
					$soc->data = CHtml::link($model->fileName,array('media/view','id'=>$model->id));
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
				}else {
					$note=new ActionChild;
					$note->createDate = time();
					$note->dueDate = time();
					$note->completeDate = time();
					$note->complete='Yes';
					$note->visibility='1';
					$note->completedBy=Yii::app()->user->getName();
					$note->assignedTo='Anyone';
					$note->type='attachment';
					$note->associationId=$_POST['associationId'];
					$note->associationType=$_POST['type'];

					$association = $this->getAssociation($note->associationType,$note->associationId);
					if($association != null)
							$note->associationName = $association->name;

					$note->actionDescription = $model->fileName . ':' . $model->id;
					if($note->save()){
					} else {
							unlink('uploads/'.$name);
					}
					$this->redirect(array($model->associationType.'/'.$model->associationId));
				}
			}
		}
	}
	
	// This is the default 'index' action that is invoked
	// when an action is not explicitly requested by users.
	public function actionIndex() {
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		if(Yii::app()->user->isGuest)
			$this->redirect('index.php/site/login');
		else {
			$profile = CActiveRecord::model('profile')->findByPk(Yii::app()->user->getId());
			if(empty($profile->startPage)) {
				$this->redirect(array('site/whatsNew'));
			} else {
				$file = Yii::app()->file->set('protected/controllers/'.ucfirst($profile->startPage).'Controller.php');
				if($file->exists)
					$this->redirect(array(ucfirst($profile->startPage).'/index'));
				else {
					$page=DocChild::model()->findByAttributes(array('title'=>ucfirst($key)));
					if(isset($page)) {
						$id=$page->id;
						$menuItems[$key] = array('label' =>ucfirst($value),		'url' => array('/admin/viewPage/'.$id),		'active'=>Yii::app()->request->requestUri==Yii::app()->request->baseUrl.'/index.php/admin/viewPage/'.$id?true:null);
				
					} else {
					$this->redirect(array('site/whatsNew'));
					}
				}
			}
		}
			
	}

	// This is the action to handle external exceptions.
	public function actionError() {
		if($error=Yii::app()->errorHandler->error) {
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}


	// Displays the About page
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

	protected function getAssociation($type,$id) {
	
		$classes = array(
			'action'=>'ActionChild',
			'contact'=>'ContactChild',
			'project'=>'ProjectChild',
			'account'=>'AccountChild',
			'sale'=>'SaleChild',
		);
		
		if(array_key_exists($type,$classes) && $id != 0)
			return CActiveRecord::model($classes[$type])->findByPk($id);
		else
			return null;
	}
	
/* 	protected function parseName($arr) {
		$type=$arr[0]; 
		$id=$arr[1];
		if(isset($id) || true) {
			if($type=='project') {
				 $data=CActiveRecord::model('ProjectChild')->findByPk($id);
				 $name=$data->name;
			} else if($type=='contact') {
				 $data=CActiveRecord::model('ContactChild')->findByPk($id);
				 $name=$data->name;
			} else if($type=='account') {
				 $data=CActiveRecord::model('AccountChild')->findByPk($id);
				 $name=$data->name;
			} else if($type=='case') {
				 $data=CActiveRecord::model('CaseChild')->findByPk($id);
				 $name=$data->name;
			} else if($type=='sale') {
				 $data=CActiveRecord::model('SaleChild')->findByPk($id);
				 $name=$data->name;
			} else {
				$data='None';
				$name='None';
			}
		} else {
			 $data='None';
			 $name='None';
		}
		$info=array($name,$data);
		return $info;
	} */

	public function actionWarning() {
		header("Content-type: image/gif");
		$img = 'R0lGODlhZABQAPcAANgAAP///w';
		for($i=0; $i<203; $i++)
			$img .= 'AAAAA';
		$img .= "CwAAAAAZABQAAAI/wABCBxIsKDBgwgTKlzIsKHDhxAjSpxIsaLFixgzatzIsaPHjwkDiBS5cKRAkiBTKkQJIMBKlwNZqpxJUOZJkzdNjiS5s+VOnDph9qT5EqbPmzGNHk3acilSny6jClVK1CDQqUyzIpUKFaXMn1SrWuXq9CtVnmS9KrUptqBRtE+dLsUKd+7at23dDu2aFCdUpmrB+t2bt7Dhw4gTK57pV+viqmwfG7ZJ+GfOqYOHEn4cmaVnu1vTipYct+bbs5aPovWcWmPYiJ/jUg5bl29kjLcfxi6LNzZctWU3NpYItmvg04GNZ85NfDjptrWfiwUuHfrp6pDvTryKNaRzvWNdo//e3l33a9Mp2TL3HtqsWdPHMd+N/5KvXN0n0T9lfZC6ftCqnXdcafg1pZV/7vl3YHfR6bfXev01xh9vWSkoG4NkgXeZY+ZpCCCFAF63H12vsfYebMhxJ198mF024F/DJfgfdhlJGON5NDan12w54jabejj22FBnJQYpZFERIgShcS5eJ1VqKq5oZHhJVskQXgYaSFd+TWHpZUwQfXffjCGB2WVNSqLJ5ZdTUqnjlWbeeJaaWsa5JHxKijnmWJ8JSGeW8oHZJpnw4QihUGammSigay7qkJ6beahonWsiuuiXjXJ55aD1eZciUHLZ2GKTbeoZJqfY3bnpkZ4K5uqrrbHwWiistAom6624klchizC+aFlQtiKGKaVaWoqlpnNpiupHUfWXn3bEuuXgs5KV2BeazWYpraDFLpueVdiGa+xuZyLqLbPazilttseme6a2iQ1rKaVeXapmvcjmxd+v/Ga43GoBRprrwAQXbPDBCOMqZ6HXtvrkX/f1VNyfPDm60rbEzmukdl/RqTHG5XGqbrTKloRenxZnS/GkJnucL6MXc9tbyurtiHHLiY6r8sbicltps7SxPKixcRb98s1eamY0uM5arOjHMLfbdMlRL73WtE7zaSKeDecJrHIQR3m1oCMnjDPLZsdcZtpst+22QQEBADs=";
		echo base64_decode($img);
	}
	
	// Displays the login page
	public function actionLogin() {
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm'])) {
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login()){
                                $user = UserChild::model()->findByPk(Yii::app()->user->getId());
                                $user->login=time();
                                $user->save();
                                if($user->username=='admin'){
                                    $version=file_get_contents('http://www.x2base.com/updates/versionCheck.php');
                                    if($version!=Yii::app()->params->version)
                                        Yii::app()->session['versionCheck']=false;
                                    else
                                        Yii::app()->session['versionCheck']=true;
                                }
                                else
                                    Yii::app()->session['versionCheck']=true;
				Yii::app()->session['loginTime']=time();
				$this->redirect('index');
			}
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	// Logs out the current user and redirect to homepage.
	public function actionLogout() {
		$user = UserChild::model()->findByPk(Yii::app()->user->getId());
		$user->lastLogin=time();
		$user->save();
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}