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
abstract class x2base extends CController {
	/*
	 * Class design:
	 * Basic create method (mostly overridden, but should have basic functionality to avoid using Gii
	 * Index method: Ability to pass a data provider to filter properly
	 * Delete method -> unviersal.
	 * Basic user permissions (access rules)
	 * Update method -> Similar to create
	 * View method -> Similar to index
	*/

	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column3';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu = array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs = array();
	
	public $portlets = array(); // This is the array of widgets on the sidebar.
	public $modelClass = 'Admin';
	public $actionMenu = array();

	/**
	 * @return array action filters
	 */
	public function filters() {
		return array(
			'accessControl', // perform access control for CRUD operations
			'setPortlets', // performs widget ordering and show/hide on each page
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array(
			array('allow',
				'actions'=>array('getItems'),
				'users'=>array('*'), 
			),
			array('allow', // allow authenticated user to perform the following actions
				'actions'=>array('index','view','create','update','search','delete','inlineEmail'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' action
				'actions'=>array('admin'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actions() {
		return array(
			'inlineEmail'=>array(
				'class'=>'InlineEmailAction',
			),
		);
	}

	/**
	 * Renders a view with any attached scripts, WITHOUT the core scripts.
	 * This method fixes the problem with {@link renderPartial()} where an AJAX request with 
	 * $processOutput=true includes the core scripts, breaking everything on the page
	 * in rendering a partial view, or an AJAX response.
	 *
	 * @param string $view name of the view to be rendered. See {@link getViewFile} for details
	 * about how the view script is resolved.
	 * @param array $data data to be extracted into PHP variables and made available to the view script
	 * @param boolean $return whether the rendering result should be returned instead of being displayed to end users
	 * @return string the rendering result. Null if the rendering result is not required.
	 * @throws CException if the view does not exist
	 */

	public function renderPartialAjax($view,$data=null,$return=false,$includeScriptFiles=false) {

		if(($viewFile=$this->getViewFile($view))!==false) {

			// if(class_exists('ReflectionClass')) {
			
				// $counter = abs(crc32($this->route));
				// $reflection = new ReflectionClass('CWidget');
				// $property = $reflection->getProperty('_counter');
				// $property->setAccessible(true);
				
				// $property->setValue($counter);
			// }
		
			$output=$this->renderFile($viewFile,$data,true);

			$cs = Yii::app()->clientScript;
			Yii::app()->setComponent('clientScript', new X2ClientScript);
			$output = $this->renderPartial($view,$data,true);
			$output .= Yii::app()->clientScript->renderOnRequest($includeScriptFiles);
			Yii::app()->setComponent('clientScript', $cs);
			
			if($return)
				return $output;
			else
				echo $output;
				
		} else {
			throw new CException(Yii::t('yii','{controller} cannot find the requested view "{view}".',
				array('{controller}'=>get_class($this), '{view}'=>$view)));
		}
	}

	// determines if we have permission to edit something based on the assignedTo field
	public function editPermissions(&$model) {
		if(Yii::app()->user->getName() == 'admin' || !$model->hasAttribute('assignedTo'))
			return true;
		else
			return $model->assignedTo == Yii::app()->user->getName() || in_array($model->assignedTo,Yii::app()->params->groups);
	}
	
	// determines if we have permission to edit something based on the assignedTo field
	public function checkPermissions(&$model,$action = null) {
	
		$view = false;
		$edit = false;
		// if we're the admin, visibility is public, there is no visibility/assignedTo, or it's directly assigned to the user, then we're done
		if(Yii::app()->user->getName() == 'admin' || !$model->hasAttribute('assignedTo') || $model->assignedTo=='Anyone' || $model->assignedTo == Yii::app()->user->getName()) {
		
			$edit = true;
			
		} elseif(!$model->hasAttribute('visibility') || $model->visibility == 1) {
		
			$view = true;
			
		} else {
			if(ctype_digit($model->assignedTo) && !empty(Yii::app()->params->groups)) {		// if assignedTo is numeric, it's a group
				
				$edit = in_array($model->assignedTo,Yii::app()->params->groups);	// if we're in the assignedTo group we act as owners
				
			} elseif($model->visibility == 2) {		// if record is shared with owner's groups, see if we're in any of those groups
				$groupMembers = Yii::app()->db->createCommand()
					->select('userId')
					->distinct()
					->from('x2_group_to_user')
					->where('groupId IN ('.implode(',',Yii::app()->params->groups).')')
					->queryColumn();
				
				$view =  in_array(Yii::app()->user->getName(),$groupMembers);
			}
		}
		
		$view = $view || $edit;	// edit permission implies view permission
		
		if(!isset($action))	// hash of all permissions if none is specified
			return array('view'=>$view,'edit'=>$edit,'delete'=>$edit);
		elseif($action == 'view')
			return $view;
		elseif($action == 'edit')
			return $edit;
		elseif($action == 'delete')
			return $edit;
		else
			return false;
	}

	
	/**
	 * Displays a particular model.  This method is called in child controllers
	 * which pass it a model to display and what type of model it is (i.e. Contact,
	 * Sale, Account).  It also creates an action history and provides appropriate
	 * variables to the view.
	 * 
	 * @param CActiveRecord $model The model to be displayed
	 * @param String $type The type of the module being displayed
	 */
	public function view($model, $type, $params = array()) {
                // eval($this->varString);
				
		$actionHistory = $this->getHistory($model,$type);
		// $actionHistory=new CActiveDataProvider('Actions', array(
			// 'criteria'=>array(
				// 'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
				// 'condition'=>'associationId='.$model->id.' AND associationType=\''.$type.'\' AND (visibility="1" OR assignedTo="admin" OR assignedTo="'.Yii::app()->user->getName().'")'
			// )
		// ));
		// if(isset($_GET['history'])) {
			// $history=$_GET['history'];
		// } else {
			// $history='all';
		// }
		// if($history=='actions') {
			// $actionHistory=new CActiveDataProvider('Actions', array(
				// 'criteria'=>array(
					// 'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
					// 'condition'=>'associationId='.$model->id.' AND associationType=\''.$type.'\' AND type IS NULL'
				// )
			// ));
		// } elseif($history=='comments') {
			// $actionHistory=new CActiveDataProvider('Actions', array(
				// 'criteria'=>array(
					// 'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
					// 'condition'=>'associationId='.$model->id.' AND associationType=\''.$type.'\' AND type="note"'
				// )
			// ));
		// } elseif($history=='attachments') {
			// $actionHistory = new CActiveDataProvider('Actions', array(
				// 'criteria'=>array(
					// 'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
					// 'condition'=>'associationId='.$model->id.' AND associationType=\''.$type.'\' AND type="attachment"'
				// )
			// ));
		// }

		$users=User::getNames();
		$showActionForm = isset($_GET['showActionForm']);
		$this->render('view',array_merge($params,array(
			'model'=>$model,
			'actionHistory'=>$actionHistory,
			'users'=>$users,
			'currentWorkflow'=>$this->getCurrentWorkflow($model->id,$type),
		)));
	}
	
	public function getHistory(&$model, $type = null) {
	
		if(!isset($type))
			$type = get_class($model);
	
		$history = 'all';
		if(isset($_GET['history']))
			$history = $_GET['history'];
	
		switch($history) {
			case 'actions':
				return new CActiveDataProvider('Actions', array(
					'criteria'=>array(
						'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
						'condition'=>'associationId='.$model->id.' AND associationType=\''.$type.'\' AND type IS NULL'
					)
				));
			case 'comments':
				return new CActiveDataProvider('Actions', array(
					'criteria'=>array(
						'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
						'condition'=>'associationId='.$model->id.' AND associationType=\''.$type.'\' AND type="note"'
					)
				));
			case 'attachments':
				return new CActiveDataProvider('Actions', array(
					'criteria'=>array(
						'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
						'condition'=>'associationId='.$model->id.' AND associationType=\''.$type.'\' AND type="attachment"'
					)
				));
			default:
			return new CActiveDataProvider('Actions', array(
				'criteria'=>array(
					'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
					'condition'=>'associationId='.$model->id.' AND associationType=\''.$type.'\' AND (visibility="1" OR assignedTo="admin" OR assignedTo="'.Yii::app()->user->getName().'")'
				)
			));
		}
	}
	
	public function getCurrentWorkflow($id,$type) {
		$currentWorkflowActions = CActiveRecord::model('Actions')->findAllByAttributes(
			array('associationType'=>$type,'associationId'=>$id,'type'=>'workflow'),
			new CDbCriteria(array('condition'=>'completeDate = 0 OR completeDate IS NULL','order'=>'createDate DESC'))
		);
		if(count($currentWorkflowActions)) {	// are there any?
			// $actionData = explode(':',$currentWorkflowActions[0]->actionDescription);
			// if(count($actionData) == 2)
				// $currentWorkflow = $actionData[0];
			// else
				// $currentWorkflow = 0;
			$currentWorkflow = $currentWorkflowActions[0]->workflowId;
		} else {							// if not, then check for completed stages
			$completedWorkflowActions = CActiveRecord::model('Actions')->findAllByAttributes(
				array('associationType'=>$type,'associationId'=>$id,'type'=>'workflow'),
				new CDbCriteria(array('order'=>'createDate DESC'))
			);
			if(count($completedWorkflowActions)) {	// are there any?
				// $actionData = explode(':',$completedWorkflowActions[0]->actionDescription);
				// if(count($actionData) == 2)
					// $currentWorkflow = $actionData[0];
				// else
					// $currentWorkflow = 0;
				$currentWorkflow = $completedWorkflowActions[0]->workflowId;
			} else
				$currentWorkflow = 0;
		}
		//$default = CActiveRecord::model('Workflow')->findByPk(1);
		//if(isset($default))
		//		$currentWorkflow = 1;
		
		return $currentWorkflow;
	}
	/**
	 * Returns a model of the appropriate type with a particular record loaded.
	 * 
	 * @param String $type The type of the model to load
	 * @param Integer $id The id of the record to load
	 * @return CActiveRecord A database record with the requested type and id
	 */
	protected function getAssociationModel($type,$id) {
	
		$classes = array(
			'actions'=>'Actions',
			'contacts'=>'Contacts',
			'projects'=>'ProjectChild',
			'accounts'=>'Accounts',
			'sales'=>'Sales',
			'social'=>'SocialChild',
		);
		
		if(array_key_exists($type,$classes))
			return CActiveRecord::model($classes[$type])->findByPk($id);
		else
			return null;
	}
	
	/**
	 * Convert currency to the proper format
	 * 
	 * @param String $str The currency string
	 * @param Boolean $keepCents Whether or not to keep the cents
	 * @return String $str The modified currency string.
	 */
	public static function parseCurrency($str,$keepCents) {

		$cents = '';
		if($keepCents) {
			$str = mb_ereg_match('[\.,]([0-9]{2})$',$str,$matches);	// get cents
			$cents = $matches[1];
		}
		$str = mb_ereg_replace('[\.,][0-9]{2}$','',$str);	// remove cents
		$str = mb_ereg_replace('[^0-9]','',$str);		//remove all non-numbers
		
		if(!empty($cents))
			$str .= ".$cents";
			
		return $str;
	}
	
	/**
	 * A function to convert a timestamp into a string stated how long ago an object
	 * was created.
	 * 
	 * @param $timestamp The time that the object was posted.
	 * @return String How long ago the object was posted.
	 */
	public static function timestampAge($timestamp) {
		$age = time()- strtotime($timestamp);
			//return $age;
		if($age < 60)
			return Yii::t('app','Just now');	// less than 1 min ago
		if($age < 3600)
			return Yii::t('app','{n} minutes ago',array('{n}'=>floor($age/60)));	// minutes (less than an hour ago)
		if($age < 86400)
			return Yii::t('app','{n} hours ago',array('{n}'=>floor($age/3600)));	// hours (less than a day ago)

		return Yii::t('app','{n} days ago',array('{n}'=>floor($age/86400)));	// days (more than a day ago)
	}
	
	/**
	 * Converts a record's Description or Background Info to deal with the discrepancy
	 * between MySQL/PHP line breaks and HTML line breaks.
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

	// Used in function convertUrls
	protected static function compareChunks($a,$b) {
		return $a[1] - $b[1];
	}

	// Replaces any URL in text with an html link (supports mailto links)
	//TODO: refactor this out of controllers
	public static function convertUrls($text, $convertLineBreaks = true) {
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
		
		usort($all_text_chunks,'x2base::compareChunks');

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
		$template="\\1<a href=".Yii::app()->getBaseUrl().'/index.php/search/search?term=%23\\2'.">#\\2</a>";
		//$text = preg_replace('/(^|[>\s\.])#(\w\w+)($|[<\s\.])/u',$template,$text);
		$text = preg_replace('/(^|[>\s\.])#(\w\w+)/u',$template,$text);

		//TODO: separate convertUrl and convertLineBreak concerns
		if($convertLineBreaks)
			return x2base::convertLineBreaks($text,true,false);
		else
			return $text;
	}
	
	// Deletes a note action
	public function actionDeleteNote($id) {
		$note = CActiveRecord::model('Actions')->findByPk($id);
		if($note->delete()) {
			$this->redirect(array('view','id'=>$note->associationId));
		}
	}
	
	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function create($model, $oldAttributes, $api) {
		$name=$this->modelClass;
		$model->createDate=time();
		if($model->save()) {
			if(!($model instanceof Actions)) {
				$fields=Fields::model()->findAllByAttributes(array('modelName'=>$name,'type'=>'link'));
				foreach($fields as $field) {
					$fieldName=$field->fieldName;
					if(isset($model->$fieldName) && is_numeric($model->$fieldName)) {
						if(is_null(Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE 
							(firstType='$name' AND firstId='$model->id' AND secondType='".ucfirst($field->linkType)."' AND secondId='".$model->$fieldName."') 
							OR (secondType='$name' AND secondId='$model->id' AND firstType='".ucfirst($field->linkType)."' AND firstId='".$model->$fieldName."')"))) {
							$rel=new Relationships;
							$rel->firstType=$name;
							$rel->secondType=ucfirst($field->linkType);
							$rel->firstId=$model->id; 
							$rel->secondId=$model->$fieldName;
							if($rel->save()) {
								$lookup=Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE 
									(firstType='$name' AND firstId='$model->id' AND secondType='".ucfirst($field->linkType)."' AND secondId='".$oldAttributes[$fieldName]."') 
									OR (secondType='$name' AND secondId='$model->id' AND firstType='".ucfirst($field->linkType)."' AND firstId='".$oldAttributes[$fieldName]."')");
								if(isset($lookup))
									$lookup->delete();
							}
						}
					}
				}
			}
			$changes = $this->calculateChanges($oldAttributes, $model->attributes, $model);
			$this->updateChangelog($model,$changes);
			if($model->hasAttribute('assignedTo')) {
				if($model->assignedTo!=Yii::app()->user->getName()) {

					$notif = new Notification;
					$notif->user = $model->assignedTo;
					$notif->createdBy = ($api==0)? 'API' : Yii::app()->user->getName();
					$notif->createDate = time();
					$notif->type = 'create';
					$notif->modelType = $name;
					$notif->modelId = $model->id;
					$notif->save();
				}
			}
			if($model instanceof Actions && $api==0) {
				if(isset($_GET['inline']) || $model->type=='note')
					if($model->associationType == 'product')
						$this->redirect(array("/".$model->associationType.'s/default/view','id'=>$model->associationId));
					else
						$this->redirect(array("/".$model->associationType.'/default/view','id'=>$model->associationId));
				else
					$this->redirect(array('view','id'=>$model->id));
			} else if($api==0) {
				$this->redirect(array('view','id'=>$model->id));
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function update($model, $oldAttributes, $api) {
		$name=$this->modelClass;
		$temp = $oldAttributes;
		$changes = $this->calculateChanges($temp, $model->attributes, $model);
		$model = $this->updateChangelog($model,$changes);
		if($model->save()) {
			if(!($model instanceof Actions)){
				$fields=Fields::model()->findAllByAttributes(array('modelName'=>$name,'type'=>'link'));
				foreach($fields as $field){
					$fieldName=$field->fieldName;
					if(isset($model->$fieldName) && $model->$fieldName!=""){
						if(is_null(Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE 
								(firstType='$name' AND firstId='$model->id' AND secondType='".ucfirst($field->linkType)."' AND secondId='".$model->$fieldName."') 
								OR (secondType='$name' AND secondId='$model->id' AND firstType='".ucfirst($field->linkType)."' AND firstId='".$model->$fieldName."')"))){
							$rel=new Relationships;
							$rel->firstType=$name;
							$rel->secondType=ucfirst($field->linkType);
							$rel->firstId=$model->id;
							$rel->secondId=$model->$fieldName;
							if($rel->save()){
								if($field->linkType!='contacts' && $field->linkType !='Contacts')
									$oldRel=CActiveRecord::model(ucfirst($field->linkType))->findByAttributes(array('name'=>$oldAttributes[$fieldName]));
								else{
									$pieces=explode(" ",$oldAttributes[$fieldName]);
									if(count($pieces)>1)
										$oldRel=CActiveRecord::model(ucfirst($field->linkType))->findByAttributes(array('firstName'=>$pieces[0],'lastName'=>$pieces[1]));
								}
								if(isset($oldRel)){
									$lookup=Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE 
									(firstType='$name' AND firstId='$model->id' AND secondType='".ucfirst($field->linkType)."' AND secondId='".$oldRel->id."') 
									OR (secondType='$name' AND secondId='$model->id' AND firstType='".ucfirst($field->linkType)."' AND firstId='".$oldRel->id."')");
									if(isset($lookup)){
										$lookup->delete();
									}
								}
							}
						}
					}elseif($model->$fieldName==""){
						if($field->linkType!='contacts' && $field->linkType !='Contacts')
									$oldRel=CActiveRecord::model(ucfirst($field->linkType))->findByAttributes(array('name'=>$oldAttributes[$fieldName]));
								else{
									$pieces=explode(" ",$oldAttributes[$fieldName]);
									if(count($pieces)>1)
										$oldRel=CActiveRecord::model(ucfirst($field->linkType))->findByAttributes(array('firstName'=>$pieces[0],'lastName'=>$pieces[1]));
								}
								if(isset($oldRel)){
									$lookup=Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE 
									(firstType='$name' AND firstId='$model->id' AND secondType='".ucfirst($field->linkType)."' AND secondId='".$oldRel->id."') 
									OR (secondType='$name' AND secondId='$model->id' AND firstType='".ucfirst($field->linkType)."' AND firstId='".$oldRel->id."')");
									if(isset($lookup)){
										$lookup->delete();
									}
								}
					}
				}
			}
			if($model instanceof Actions && $api == 0) {
				if(isset($_GET['redirect']) && $model->associationType != 'none')	// if the action has an association
					$this->redirect(array('/'.$model->associationType.'/default/view','id'=>$model->associationId));	// go back to the association
				else	// no association
					$this->redirect(array('/actions/default/view','id'=>$model->id));	// view the action
			} else if($api==0) {
				$this->redirect(array('view','id'=>$model->id));
			} else {
				return true;
			}
		} else {
			return false;
		}

	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */

	/**
	 * Lists all models.
	 */
	public function index($model,$name) {
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

		$this->render('index', array('model'=>$model));
	}

	/**
	 * Manages all models.
	 * @param $model The model to use admin on, created in a controller subclass.  The model must be constucted with the parameter 'search'
	 * @param $name The name of the model being viewed (Sales, Actions, etc.)
	 */
	public function admin($model, $name) {

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
		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Search for a term.  Defined in X2Base so that all Controllers can use, but 
	 * it makes a call to the SearchController.
	 */
	public function actionSearch() {
		$term=$_GET['term'];
		$this->redirect(Yii::app()->request->baseUrl.'/index.php/search/search?term='.$term);
	}
	
	/**
	 * Sets the lastUpdated and updatedBy fields to reflect recent changes.
	 * @param type $model The model to be updated
	 * @return type $model The model with modified attributes
	 */
	protected function updateChangelog($model, $change) {
		$model->lastUpdated=time();
		$model->updatedBy=Yii::app()->user->getName();
		$model->save();
		$type=get_class($model);
		if(substr($type,-1)!="s"){
			$type=substr($type,0,-5)."s";
		}
		
		$changelog=new Changelog;
		$changelog->type=$type;
		if(!isset($model->id)){
			if($model->save()){}
		}
		$changelog->itemId=$model->id;
		$changelog->changedBy=Yii::app()->user->getName();
		$changelog->changed=$change;
		$changelog->timestamp=time();
		
		if($changelog->save()){
			
		}
		$changes=array();
		if($change!='Create' && $change!='Completed' && $change!='Edited'){
			if($change!=""){
				$pieces=explode("<br />",$change);
				foreach($pieces as $piece){
					$newPieces=explode("TO:",$piece);
					$forDeletion=$newPieces[0];
					if(isset($newPieces[1]) && preg_match('/<b>'.Yii::t('actions','color').'<\/b>/',$piece) == false) {
						$changes[]=$newPieces[1];
					}

					preg_match_all('/(^|\s|)#(\w\w+)/',$forDeletion,$deleteMatches);
					$deleteMatches=$deleteMatches[0];
					foreach($deleteMatches as $match){
							$oldTag=Tags::model()->findByAttributes(array('tag'=>substr($match,1),'type'=>$type,'itemId'=>$model->id));
							if(isset($oldTag))
									$oldTag->delete();

					}
				}
			}
		}else if($change=='Create' || $change=='Edited'){
			if($model instanceof Contacts)
				$change=$model->backgroundInfo;
			else if($model instanceof Actions)
				$change=$model->actionDescription;
			else if($model instanceof Docs)
				$change=$model->text;
			else
				$change=$model->name;
		} 
		foreach($changes as $change){
			preg_match_all('/(^|\s|)#(\w\w+)/',$change,$matches);
			$matches=$matches[0];
			foreach($matches as $match){
				$tag=new Tags;
				$tag->type=$type;
				$tag->taggedBy=Yii::app()->user->getName();
				$tag->type=$type;
				//cut out leading whitespace
				$tag->tag=trim($match);
				if($model instanceof Contacts)
					$tag->itemName=$model->firstName." ".$model->lastName;
				else if($model instanceof Actions)
					$tag->itemName=$model->actionDescription;
				else if($model instanceof Docs)
					$tag->itemName=$model->title;
				else
					$tag->itemName=$model->name;
				if(!isset($model->id)){
					$model->save();
				}
				$tag->itemId=$model->id;
				$tag->timestamp=time();
				//save tags including # sign
				if($tag->save()){

				}
			}
		}
		return $model;
	}
	
	/**
	 * Delete all tags associated with a model
	 */
	public function cleanUpTags($model) {
		Tags::model()->deleteAllByAttributes(array('itemId'=>$model->id));
		/*$type=get_class($model);
		if(substr($type,-1)!="s"){
			$type=substr($type,0,-5)."s";
		}
		$change="";
		if($model instanceof Contacts)
			$change=$model->backgroundInfo;
		else if($model instanceof Actions)
			$change=$model->actionDescription;
		else if($model instanceof Docs)
			$change=$model->text;
		else
			$change=$model->description;
		if($change!=""){
			$forDeletion=$change;
			preg_match_all('/(^|\s|)#(\w\w+)/',$forDeletion,$deleteMatches);
			$deleteMatches=$deleteMatches[0];
			foreach($deleteMatches as $match){
				$oldTag=Tags::model()->findByAttributes(array('tag'=>$match,'type'=>$type,'itemId'=>$model->id));
				if(isset($oldTag)) {
					$oldTag->delete();
				}
			}
		}*/
	}
	
	protected function calculateChanges($old, $new, &$model=null){
		$arr=array();
		$keys=array_keys($new);
		for($i=0;$i<count($keys);$i++){
			if($old[$keys[$i]]!=$new[$keys[$i]]){
				$arr[$keys[$i]]=$new[$keys[$i]];
				$allCriteria=Criteria::model()->findAllByAttributes(array('modelType'=>$this->modelClass,'modelField'=>$keys[$i]));
				foreach($allCriteria as $criteria){
					if(($criteria->comparisonOperator=="=" && $new[$keys[$i]]==$criteria->modelValue)
								|| ($criteria->comparisonOperator==">" && $new[$keys[$i]]>=$criteria->modelValue)
								|| ($criteria->comparisonOperator=="<" && $new[$keys[$i]]<=$criteria->modelValue)
								|| ($criteria->comparisonOperator=="change" && $new[$keys[$i]]!=$old[$keys[$i]])){
								
						$users = explode(", ",$criteria->users);
								
						if($criteria->type=='notification') {
							foreach($users as $user) {

								$notif = new Notification;
								$notif->type = 'change';
								$notif->fieldName = $keys[$i];
								$notif->modelType = get_class($model);
								$notif->modelId = $model->id;
								
								if($criteria->comparisonOperator == 'change') {
									$notif->comparison = 'change';				// if the criteria is just 'changed'
									$notif->value = $new[$keys[$i]];			// record the new value
								} else {
									$notif->comparison = $criteria->comparisonOperator;		// otherwise record the operator type
									$notif->value = substr($criteria->modelValue,0,250);	// and the comparison value
								}
								$notif->user = $user;
								$notif->createdBy = Yii::app()->user->name;
								$notif->createDate = time();
								
								$notif->save();
							}
						} elseif($criteria->type=='action') {
							$users=explode(", ",$criteria->users);
							foreach($users as $user){
								$action=new Actions;
								$action->assignedTo=$user;
								if($criteria->comparisonOperator=="="){
									$action->actionDescription="A record of type ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue"." by ".Yii::app()->user->getName();
								}else if($criteria->comparisonOperator==">"){
									$action->actionDescription="A record of type ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue"." by ".Yii::app()->user->getName();
								}else if($criteria->comparisonOperator=="<"){
									$action->actionDescription="A record of type ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue"." by ".Yii::app()->user->getName();
								}else if($criteria->comparisonOperator=="change"){
									$action->actionDescription="A record of type ".$this->modelClass." has had its $criteria->modelField field changed from ".$old[$keys[$i]]." to ".$new[$keys[$i]]." by ".Yii::app()->user->getName();
								}
								$action->dueDate=mktime('23','59','59');
								$action->createDate=time();
								$action->lastUpdated=time();
								$action->updatedBy='admin';
								$action->visibility=1;
								$action->associationType=strtolower($this->modelClass);
								$action->associationId=$new['id'];
								$model=CActiveRecord::model($this->modelClass)->findByPk($new['id']);
								$action->associationName=$model->name;
								$action->save();
							}
						} elseif($criteria->type=='assignment') {
							$model->assignedTo = $criteria->users;
							
							if($model->save()) {
								$notif = new Notification;
								$notif->user = $model->assignedTo;
								$notif->createDate = time();
								$notif->type = 'assignment';
								$notif->modelType = $this->modelClass;
								$notif->modelId = $new['id'];
								$notif->save();
							}
						} 
					}
				}
			}
		}
		$str='';
		foreach($arr as $key=>$item){
				$str.="<b>$key</b> <u>FROM:</u> $old[$key] <u>TO:</u> $item <br />";
		}
		return $str;
	}

	public function partialDateRange($input) {
		$datePatterns = array(
			array('/^(0-9)$/',									'000-01-01',	'999-12-31'),
			array('/^([0-9]{2})$/',								'00-01-01',		'99-12-31'),
			array('/^([0-9]{3})$/',								'0-01-01',		'9-12-31'),
			array('/^([0-9]{4})$/',								'-01-01',		'-12-31'),
			array('/^([0-9]{4})-$/',							'01-01',		'12-31'),
			array('/^([0-9]{4})-([0-1])$/',						'0-01',			'9-31'),
			array('/^([0-9]{4})-([0-1][0-9])$/',				'-01',			'-31'),
			array('/^([0-9]{4})-([0-1][0-9])-$/',				'01',			'31'),
			array('/^([0-9]{4})-([0-1][0-9])-([0-3])$/',		'0',			'9'),
			array('/^([0-9]{4})-([0-1][0-9])-([0-3][0-9])$/',	'',				''),
		);

		$inputLength = strlen($input);

		$minDateParts = array();
		$maxDateParts = array();

		if($inputLength > 0 && preg_match($datePatterns[$inputLength-1][0],$input)) {

			$minDateParts = explode('-',$input . $datePatterns[$inputLength-1][1]);
			$maxDateParts = explode('-',$input . $datePatterns[$inputLength-1][2]);
			
			$minDateParts[1] = max(1,min(12,$minDateParts[1]));
			$minDateParts[2] = max(1,min(cal_days_in_month(CAL_GREGORIAN, $minDateParts[1], $minDateParts[0]),$minDateParts[2]));
			
			$maxDateParts[1] = max(1,min(12,$maxDateParts[1]));
			$maxDateParts[2] = max(1,min(cal_days_in_month(CAL_GREGORIAN, $maxDateParts[1], $maxDateParts[0]),$maxDateParts[2]));
			
			$minTimestamp = mktime(0,0,0,$minDateParts[1],$minDateParts[2],$minDateParts[0]);
			$maxTimestamp = mktime(23,59,59,$maxDateParts[1],$maxDateParts[2],$maxDateParts[0]);
		
			return array($minTimestamp, $maxTimestamp);
		} else
			return false;
	}

	public function decodeQuotes($str) {
		return preg_replace('/&quot;/u','"',$str);
	}
	public function encodeQuotes($str) {
		// return htmlspecialchars($str);
		return preg_replace('/"/u','&quot;',$str);
	}

	public static function cleanUpSessions(){
		Session::model()->deleteAll('lastUpdated < :cutoff', array(':cutoff'=>time() - Yii::app()->params->admin->timeout));
	}

	
	public function getPhpMailer() {
	
		require_once('protected/components/phpMailer/class.phpmailer.php');
		
		$phpMail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
		$phpMail->CharSet = 'utf-8';
		
		switch(Yii::app()->params->admin->emailType) {
			case 'sendmail':
				$phpMail->IsSendmail();
				break;
			case 'qmail':
				$phpMail->IsQmail();
				break;
			case 'smtp':
				$phpMail->IsSMTP();
				
				$phpMail->Host			= Yii::app()->params->admin->emailHost;
				$phpMail->Port			= Yii::app()->params->admin->emailPort;
				$phpMail->SMTPSecure	= Yii::app()->params->admin->emailSecurity;
				
				if(Yii::app()->params->admin->emailUseAuth == 'admin') {
					$phpMail->SMTPAuth		= true;
					$phpMail->Username		= Yii::app()->params->admin->emailUser;
					$phpMail->Password		= Yii::app()->params->admin->emailPass;
				}
				break;
			case 'mail':
			default:
				$phpMail->IsMail();
		}
		return $phpMail;
	}
	
	public function addEmailAddresses(&$phpMail,$addresses) {
		
			if(isset($addresses['to'])) {
				foreach($addresses['to'] as $target) {
					if(count($target) == 2)
						$phpMail->AddAddress($target[1],$target[0]);
				}
			} else {
				if(count($addresses) == 2 && !is_array($addresses[0]))	// this is just an array of [name, address],
					$phpMail->AddAddress($addresses[1],$addresses[0]);	// not an array of arrays
				else {
					foreach($addresses as $target) {					//this is an array of [name, address] subarrays
						if(count($target) == 2)
							$phpMail->AddAddress($target[1],$target[0]);
					}
				}
			}
			if(isset($addresses['cc'])) {
				foreach($addresses['cc'] as $target) {
					if(count($target) == 2)
						$phpMail->AddCC($target[1],$target[0]);
				}
			}
			if(isset($addresses['bcc'])) {
				foreach($addresses['bcc'] as $target) {
					if(count($target) == 2)
						$phpMail->AddBCC($target[1],$target[0]);
				}
			}
		
		
	}
	
	function throwException($message) {
		throw new Exception($message);
	}
	
	
	public function sendUserEmail($addresses,$subject,$message,$attachments=null) {

		$user = CActiveRecord::model('User')->findByPk(Yii::app()->user->getId());

		$phpMail = $this->getPhpMailer();

		try {
			if(empty(Yii::app()->params->profile->emailAddress))
				throw new Exception('<b>'.Yii::t('app','Your profile doesn\'t have a valid email address.').'</b>');
		
			$phpMail->AddReplyTo(Yii::app()->params->profile->emailAddress,$user->name);
			$phpMail->SetFrom(Yii::app()->params->profile->emailAddress,$user->name);

			$this->addEmailAddresses($phpMail,$addresses);
			
			$phpMail->Subject = $subject;
			// $phpMail->AltBody = $message;
			$phpMail->MsgHTML($message);
			// $phpMail->Body = $message;
			
			// add attachments, if any
			if ($attachments) {
				foreach($attachments as $attachment) {
					if($attachment['temp']) { // stored as a temp file?
						$file = 'uploads/media/temp/' . $attachment['folder'] . '/' . $attachment['filename'];
						if(file_exists($file)) // check file exists
							if(filesize($file) <= (10 * 1024 * 1024)) // 10mb file size limit
								$phpMail->AddAttachment($file);
							else
								$this->throwException("Attachment '{$attachment['filename']}' exceeds size limit of 10mb.");
					}
				}
			}

			$phpMail->Send();
			
			// delete temp attachment files, if they exist
			if($attachments) {
				foreach($attachments as $attachment) {
					if($attachment['temp']) {
						$file = 'uploads/media/temp/' . $attachment['folder'] . '/' . $attachment['filename'];
						$folder = 'uploads/media/temp/' . $attachment['folder'];
						if(file_exists($file))
							unlink($file); // delete temp file
						if(file_exists($folder))
							rmdir($folder); // delete temp folder
						TempFile::model()->deleteByPk($attachment['id']);
					}
				}
			}
			
			$status[] = '200';
			$status[] = Yii::t('app','Email Sent!');

		} catch (phpmailerException $e) {
			$status[] = '<span class="error">'.$e->errorMessage().'</span>'; //Pretty error messages from PHPMailer
		} catch (Exception $e) {
			$status[] = '<span class="error">'.$e->getMessage().'</span>'; //Boring error messages from anything else!
		}
		
		return $status;
	}
	
	
	public function parseEmailTo($string) {
	
		if(empty($string))
			return false;
		$mailingList = array();
		$splitString = explode(',',$string);
		
		require_once('protected/components/phpMailer/class.phpmailer.php');
		
		foreach($splitString as &$token) {

			$token = trim($token);
			if(empty($token))
				continue;
			
			$matches = array();
			
			if(PHPMailer::ValidateAddress($token)) {	// if it's just a simple email, we're done!
				$mailingList[] = array('',$token);
			} else if(preg_match('/^"?([^"]*)"?\s*<(.+)>$/i',$token,$matches)) {
				if(count($matches) == 3 && PHPMailer::ValidateAddress($matches[2]))
					$mailingList[] = array($matches[1],$matches[2]);
				else
					return false;
			} else
				return false;
				
			// if(preg_match('/^"(.*)"/i',$token,$matches)) {		// if there is a name like <First Last> at the beginning,
				// $token = trim(preg_replace('/^".*"/i','',$token));	// remove it
				// if(isset($matches[1]))
					// $name = trim($matches[1]);						// and put it in $name
			// }
			// $address = trim(preg_replace($token);
			
			// if(PHPMailer::ValidateAddress($address))
				// $mailingList[] = array($address,$name);
			// else
				// return false;
		}
		// echo var_dump($mailingList);
		
		if(count($mailingList) < 1)
			return false;

		return $mailingList;
	}

	public function mailingListToString($list,$encodeQuotes = false) {
		$string = '';
		if(is_array($list)) {
			foreach($list as &$value) {
				if(!empty($value[0]))
					$string .= '"'.$value[0].'" <'.$value[1].'>, ';
				else
					$string .= $value[1].', ';
			}
		}
		return $encodeQuotes? $this->encodeQuotes($string) : $string;
	}

	/**
	 * Sets widgets on the page on a per user basis.
	 */
	public function filterSetPortlets($filterChain) {
		$themeURL = Yii::app()->theme->getBaseUrl();
			Yii::app()->clientScript->registerScript('logos',"
			$(window).load(function(){
				if((!$('#main-menu-icon').length) || (!$('#x2touch-logo').length) || (!$('#x2crm-logo').length)){
					$('a').removeAttr('href');
					alert('Please put the logo back');
					window.location='http://www.x2engine.com';
				}
				var touchlogosrc = $('#x2touch-logo').attr('src');
				var logosrc=$('#x2crm-logo').attr('src');
				if(logosrc!='$themeURL/images/x2footer.png'|| touchlogosrc!='$themeURL/images/x2touch.png'){
					$('a').removeAttr('href');
					alert('Please put the logo back');
					window.location='http://www.x2engine.com';
				}
			});    
			");
		$this->portlets = ProfileChild::getWidgets();
		// foreach($widgets as $key=>$value) {
			// $options = ProfileChild::parseWidget($value,$key);
			// $this->portlets[$key] = $options;
		// }
		$filterChain->run();
	}

	
	function getRealIp() {
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
			return $_SERVER['HTTP_CLIENT_IP'];
		else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		else
			return $_SERVER['REMOTE_ADDR'];
	}

	// This function needs to be made in your extensions of the class with similar code. 
	// Replace "Sales" with the Model being used.
	/**public function loadModel($id)
	{
		$model=Sales::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}*/

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model) {
		if(isset($_POST['ajax'])){
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	/*** Date Format Functions ***/
	
	/**
	 * Format a date to be long (September 25, 2011)
	 * @param timestamp unix time stamp
	 */
	function formatLongDate($timestamp) {
		if(empty($timestamp))
			return '';
		else
			return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $timestamp);
	}
	
	function formatDate($timestamp, $width='') {
		if(empty($timestamp))
		    return '';
		else {
			if(Yii::app()->language == 'en')
				if($width == 'medium')
					return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('medium'), $timestamp);
				else
			    	return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $timestamp);
			else
			    return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('short'), $timestamp);
		}
	}
		
	function formatDatePicker($width = '') {
		if(Yii::app()->language == 'en') {
			if($width == 'medium')
				return "M d, yy";
			else
		    	return "MM d, yy";
		} else {
		    $format = Yii::app()->locale->getDateFormat('short'); // translate Yii date format to jquery
		    $format = str_replace('yy', 'y', $format);
		    $format = str_replace('MM', 'mm', $format);
		    $format = str_replace('M','m', $format);
		    return $format;
		}
	}
	
	function formatTimePicker($width = '') {
		$format = Yii::app()->locale->getTimeFormat('short');
		$format = strtolower($format); // jquery specifies hours/minutes as hh/mm instead of HH//MM
		$format = str_replace('a', 'TT', $format); // yii and jquery have different format to specify am/pm
		return $format;
	}
	
	/* check if am/pm is being used in this locale */
	function formatAMPM() {
		if (strstr(Yii::app()->locale->getTimeFormat(), "a") === false)
			return false;
		else
			return true;
	}
	
	function parseDate($date) {
		if(Yii::app()->language == 'en')
		    return strtotime($date);
		else
		    return CDateTimeParser::parse($date, Yii::app()->locale->getDateFormat('short'));
	}
	
	
	/*** Date Time Format Functions ***/
	
	function formatLongDateTime($timestamp) {
		if(empty($timestamp))
			return '';
		else
			return Yii::app()->dateFormatter->formatDateTime($timestamp, 'long', 'short');
	}
	
	function formatDateEndOfDay($timestamp) {
		if(empty($timestamp))
		    return '';
		else
			if(Yii::app()->language == 'en')
			    return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('medium') . ' ' . Yii::app()->locale->getTimeFormat('short'), strtotime("tomorrow", $timestamp) - 60);
			else
			    return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('short') . ' ' . Yii::app()->locale->getTimeFormat('short'), strtotime("tomorrow", $timestamp) - 60);
	}
	
	function formatDateTime($timestamp) {
		if(empty($timestamp))
		    return '';
		else
			if(Yii::app()->language == 'en')
				return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('medium') . ' ' . Yii::app()->locale->getTimeFormat('short'), $timestamp);
			else
			    return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('short') . ' ' . Yii::app()->locale->getTimeFormat('short'), $timestamp);
	}
	
	function parseDateTime($date) {
		if(Yii::app()->language == 'en')
		    return strtotime($date);
		else
		    return CDateTimeParser::parse($date, Yii::app()->locale->getDateFormat('short') .' hh:mm');
	}
	
	function truncateText($str,$length = 30) {
		
		if(strlen($str) > $length - 3) {
			if($length < 3)
				$str = '';
			else
				$str = substr($str,0,$length-3);
			$str .= '...';
		}
		return $str;
	}
}
?>
