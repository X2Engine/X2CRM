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

/**
 * User notifications & social feed controller
 * 
 * @package X2CRM.controllers
 */
class NotificationsController extends CController {

	public function accessRules() {
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('get','delete','deleteAll','newMessage','getMessages','checkNotifications','saveGridviewSettings','saveFormSettings', 'fullScreen', 'pageOpacity', 'widgetState','widgetOrder'),
				'users'=>array('@'),
			),
			array('deny', 
				'users'=>array('*')
			)
		);
	}

	/**
	 * Obtain all current notifications for the current web user. 
	 */
	public function actionGet() {
	
		// import all the models
		Yii::import('application.models.Social');
		Yii::import('application.models.Profile');
		Yii::import('application.models.Notification');
		Yii::import('application.models.Fields');
        Yii::import('application.components.X2WebUser');
		foreach(scandir('protected/modules') as $module){
			if(file_exists('protected/modules/'.$module.'/register.php'))
				Yii::import('application.modules.'.$module.'.models.*');
		}

		if(!isset($_GET['lastNotifId']))	// if the client doesn't specify the last 
			$_GET['lastNotifId'] = 0;		// message ID received, send everything

		$notifications = array();
		$notifCount = 0;
		
		$notifModels = CActiveRecord::model('Notification')->findAll(new CDbCriteria(array(
			'condition'=>'id>:lastId AND user=:user',
			'params'=>array(':user'=>Yii::app()->user->name,':lastId'=>$_GET['lastNotifId']),
			'order'=>'id DESC',
			'limit'=>10
		)));

		if(count($notifModels))
			$notifCount = CActiveRecord::model('Notification')->countByAttributes(array('user'=>Yii::app()->user->name));
		
		foreach($notifModels as &$model) {
			$msg = $model->getMessage();

			if(isset($msg)) {
				$notifications[] = array(
					'id'=>$model->id,
					'viewed'=>$model->viewed,
					'date'=>Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('short'),$model->createDate),
					'text'=>$msg
				);
			}
		}
		
		
		$chatMessages = array();
		$lastChatId = 0;
		if(isset($_GET['lastChatId']) && is_numeric($_GET['lastChatId']))	// if the client specifies the last message ID received,
			$lastChatId = $_GET['lastChatId'];								// only send newer messages

		
		$messages = Yii::app()->db->createCommand()
			->select('x2_social.id,x2_social.user,x2_social.timestamp,x2_social.data,x2_users.id AS userId')
			->from('x2_social')
			->where('x2_social.type="chat" AND x2_social.timestamp > :time AND x2_social.id > :lastId')
			->join('x2_users','x2_social.user=x2_users.username AND x2_users.status=1')
			->order('x2_social.id DESC')
			->limit(10)
			->bindValues(array(':time'=>mktime(0,0,0),':lastId'=>$lastChatId))
			->queryAll();
			// die(var_dump($messages));
			
			
		for($i=count($messages)-1; $i>-1; --$i) {
			if($messages[$i]['userId'] == Yii::app()->user->getId())
				$userLink = '<span class="my-username">'.$messages[$i]['user'].'</span>';
			else
				$userLink = CHtml::link($messages[$i]['user'],array('profile/view','id'=>$messages[$i]['userId']),array('class'=>'username'));
			
			$chatMessages[] = array(
				$messages[$i]['id'],
				date('g:i:s A',$messages[$i]['timestamp']),
				$userLink,
				$this->convertUrls($messages[$i]['data'])
			);
		}

		if(!empty($notifications) || !empty($chatMessages)) {
			echo CJSON::encode(array(
				'notifCount'=>$notifCount,
				'notifData'=>$notifications,
				'chatData'=>$chatMessages
			));
		}
	}

	/**
	 * Mark an action as viewed. 
	 */
	public function actionMarkViewed() {
		if(isset($_GET['id'])) {
			if(!is_array($_GET['id']))
				$_GET['id'] = array($_GET['id']);
				
			foreach($_GET['id'] as &$id) {
				$notif = CActiveRecord::model('Notification')->findByPk($id);
				if(isset($notif) && $notif->user == Yii::app()->user->name) {
					$notif->viewed = 1;
					$notif->update();
				}
			}
		}
	}
	
	/**
	 * Delete an action by its ID 
	 * @param type $id
	 */
	public function actionDelete($id) {
		$model = CActiveRecord::model('Notification')->findByPk($id);
		if(isset($model) && $model->user = Yii::app()->user->name)
			$model->delete();
	}

	/**
	 * Clear all notifications.
	 */
	public function actionDeleteAll() {
		CActiveRecord::model('Notification')->deleteAllByAttributes(array('user'=>Yii::app()->user->name));
		$this->redirect(array('/site/viewNotifications'));
	}

/* 
	public function actionCheckNotifications(){
		Yii::import('application.models.Notifications');
		Yii::import('application.models.NotificationChild');
		
		$list=CActiveRecord::model('NotificationChild')->findAllByAttributes(array('user'=>Yii::app()->user->getName(),'viewed'=>'0'));
		if(count($list)>0){
			echo json_encode(count($list));
		}else{
			echo null;
		}
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
			
			// $profile = CActiveRecord::model('ProfileChild')->findByPk(Yii::app()->user->getId());

			Yii::app()->params->profile->pageOpacity = $opacity;
			if(Yii::app()->params->profile->save()){
				echo "success";
			}
		}
	}

	public function actionWidgetState() {
		
		if(isset($_GET['widget']) && isset($_GET['state'])) {
			$widgetName = $_GET['widget'];
			$widgetState = ($_GET['state']==0)? 0 : 1;
			
			// $profile = Yii::app()->params->profile;
			
			$order = explode(":",Yii::app()->params->profile->widgetOrder);
			$visibility = explode(":",Yii::app()->params->profile->widgets);

			if(array_key_exists($widgetName,Yii::app()->params->registeredWidgets)) {

				$pos = array_search($widgetName,$order);
				$visibility[$pos] = $widgetState;
			
				Yii::app()->params->profile->widgets = implode(':',$visibility);
				
				if(Yii::app()->params->profile->save()){
					echo 'success';
				}
			}
		}
	}

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
	 */

	/**
	 * Used in function convertUrls
	 * 
	 * @todo refactor this out of controllers
	 * @param mixed $a
	 * @param mixed $b
	 * @return mixed
	 */
	protected static function compareChunks($a,$b) {
		return $a[1] - $b[1];
	}

	/**
	 *  Replaces any URL in text with an html link (supports mailto links)
	 * 
	 * @todo refactor this out of controllers
	 */
	public function convertUrls($text, $convertLineBreaks = true) {
		/*$text = preg_replace(
			array(
				'/(?(?=<a[^>]*>.+<\/a>)(?:<a[^>]*>.+<\/a>)|([^="\']?)((?:https?|ftp|bf2|):\/\/[^<> \n\r]+))/iex',
				'/<a([^>]*)target="?[^"\']+"?/i',
				'/<a([^>]+)>/i',
				'/(^|\s|>)(www.[^<> \n\r]+)/iex',
				'/(([_A-Za-z0-9-]+)(\\.[_A-Za-z0-9-]+)*@([A-Za-z0-9-]+)(\\.[A-Za-z0-9-]+)*)/iex'
			),
			array(
				"stripslashes((strlen('\\2')>0?'\\1<a href=\"\\2\">\\2</a>\\3':'\\0'))",
				'<a\\1',
				'<a\\1 target="_blank">',
				"stripslashes((strlen('\\2')>0?'\\1<a href=\"http://\\2\">\\2</a>\\3':'\\0'))",
				"stripslashes((strlen('\\2')>0?'<a href=\"mailto:\\0\">\\0</a>':'\\0'))"
			),
			$text
		);*/



		/* URL matching regex from the interwebs:
		 * http://www.regexguru.com/2008/11/detecting-urls-in-a-block-of-text/
		 */
		$url_pattern = '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/i';
		$email_pattern = '/(([_A-Za-z0-9-]+)(\\.[_A-Za-z0-9-]+)*@([A-Za-z0-9-]+)(\\.[A-Za-z0-9-]+)*)/i';

		/* First break the text into two arrays, one containing <a> tags and the like
		 * which should not have any replacements, and another with all the text that
		 * should have URLs activated.  Each piece of each array has its offset from 
		 * original string so we can piece it back together later
		 */
		
		//add any additional tags to be passed over here	
		$tags_with_urls = "/(<a[^>]*>.*<\/a>)|(<img[^>]*>)/i";
		$text_to_add_links = preg_split($tags_with_urls, $text, NULL, PREG_SPLIT_OFFSET_CAPTURE);
		$matches = array();
		preg_match_all($tags_with_urls, $text, $matches, PREG_OFFSET_CAPTURE);
		$text_to_leave = $matches[0];

		// Convert all URLs into html links
		foreach ($text_to_add_links as $i => $value) {
			$text_to_add_links[$i][0] = preg_replace(
				array(	$url_pattern,
					$email_pattern),
				array(	"<a href=\"\\0\">\\0</a>",
					"<a href=\"mailto:\\0\">\\0</a>"),
				$text_to_add_links[$i][0]
			);
		}

		// Merge the arrays and sort to be in the original order
		$all_text_chunks = array_merge($text_to_add_links, $text_to_leave);
		
		usort($all_text_chunks,'NotificationsController::compareChunks');

		$new_text = "";
		foreach ($all_text_chunks as $chunk) {
			$new_text = $new_text . $chunk[0];
		}
		$text = $new_text;	

		// Make sure all links open in new window, and have http:// if missing
		$text = preg_replace(
			array(	'/<a([^>]+)target=("[^"]+"|\'[^\']\'|[^\s]+)([^>]+)/i',	
				'/<a([^>]+)>/i',
				'/<a([^>]+href="?\'?)(www\.|ftp\.)/i'), 
			array(	'<a\\1\\3',	
				'<a\\1 target="_blank">',
				'<a\\1http://\\2'),
			$text
		);

		//convert any tags into links
		$template="\\1<a href=".Yii::app()->createUrl('/search/search').'?term=%23\\2'.">#\\2</a>";
		//$text = preg_replace('/(^|[>\s\.])#(\w\w+)($|[<\s\.])/u',$template,$text);
		$text = preg_replace('/(^|[>\s\.])#(\w\w+)/u',$template,$text);


		if($convertLineBreaks)
			return $this->convertLineBreaks($text,true,false);
		else
			return $text;
	}

	/**
	 * Normalize linebreaks in output.
	 * 
	 * @todo refactor this out of controllers
	 * @param string $text
	 * @param boolean $allowDouble
	 * @param boolean $allowUnlimited
	 * @return string 
	 */
	public static function convertLineBreaks($text,$allowDouble = true,$allowUnlimited = false) {
		$text = mb_ereg_replace("\r\n","\n",$text);		//convert microsoft's stupid CRLF to just LF

		if(!$allowUnlimited)
			$text = mb_ereg_replace("[\r\n]{3,}","\n\n",$text);	// replaces 2 or more CR/LF chars with just 2
		if($allowDouble)
			$text = mb_ereg_replace("[\r\n]",'<br />',$text);	// replaces all remaining CR/LF chars with <br />
		else
			$text = mb_ereg_replace("[\r\n]+",'<br />',$text);

		return $text;
	}
}
