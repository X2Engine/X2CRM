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
abstract class x2base extends Controller {
	/*
	 * Class design:
	 * Basic create method (mostly overridden, but should have basic functionality to avoid using Gii
	 * Index method: Ability to pass a data provider to filter properly
	 * Delete method -> unviersal.
	 * Basic user permissions (access rules)
	 * Update method -> Similar to create
	 * View method -> Similar to index
	 * 
	 * 
	*/

	// Set locale and character encoding.
	public function onBeginRequest() {
		setlocale(LC_ALL, 'en_US.UTF-8');
	}
	
	public $portlets=array(); // This is the array of widgets on the sidebar.
        public $modelClass = 'Admin';
        

	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout = '//layouts/column2';
        
        public $varString="\$themeURL = Yii::app()->theme->getBaseUrl();
                Yii::app()->clientScript->registerScript('logos',\"
                $(window).load(function(){
                    if((!$('#main-menu-icon').length) || (!$('#x2touch-logo').length) || (!$('#x2crm-logo').length)){
                        $('a').removeAttr('href');
                        alert('Please put the logo back');
                        window.location='http://www.x2engine.com';
                    }
                    var touchlogosrc = $('#x2touch-logo').attr('src');
                    var logosrc=$('#x2crm-logo').attr('src');
                    if(logosrc!='\$themeURL/images/x2footer.png'|| touchlogosrc!='\$themeURL/images/x2touch.png'){
                        $('a').removeAttr('href');
                        alert('Please put the logo back');
                        window.location='http://www.x2engine.com';
                    }
                });";
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

	// determines if we have permission to edit something based on the assignedTo field
	public function editPermissions(&$model) {
		if(Yii::app()->user->getName() == 'admin' || !$model->hasAttribute('assignedTo'))
			return true;
		else
			return $model->assignedTo == Yii::app()->user->getName() || in_array($model->assignedTo,Yii::app()->params->groups);
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
		$actionHistory=new CActiveDataProvider('Actions', array(
			'criteria'=>array(
				'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
				'condition'=>'associationId='.$model->id.' AND associationType=\''.$type.'\' AND (visibility="1" OR assignedTo="admin" OR assignedTo="'.Yii::app()->user->getName().'")'
			)
		));
		if(isset($_GET['history'])) {
			$history=$_GET['history'];
		} else {
			$history='all';
		}
		if($history=='actions') {
			$actionHistory=new CActiveDataProvider('Actions', array(
				'criteria'=>array(
					'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
					'condition'=>'associationId='.$model->id.' AND associationType=\''.$type.'\' AND type IS NULL'
				)
			));
		} elseif($history=='comments') {
			$actionHistory=new CActiveDataProvider('Actions', array(
				'criteria'=>array(
					'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
					'condition'=>'associationId='.$model->id.' AND associationType=\''.$type.'\' AND type="note"'
				)
			));
		} elseif($history=='attachments') {
			$actionHistory = new CActiveDataProvider('Actions', array(
				'criteria'=>array(
					'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
					'condition'=>'associationId='.$model->id.' AND associationType=\''.$type.'\' AND type="attachment"'
				)
			));
		}

		$users=UserChild::getNames();
		$showActionForm = isset($_GET['showActionForm']);
		$this->render('view',array_merge($params,array(
			'model'=>$model,
			'actionHistory'=>$actionHistory,
			'users'=>$users,
			'currentWorkflow'=>$this->getCurrentWorkflow($model->id,$type),
		)));
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

	// Replaces any URL in text with an html link (supports mailto links)
	public static function convertUrls($text, $convertLineBreaks = true) {
		$text = preg_replace(
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
		);
		// $urlRegex = '/(https?|ftp)://[^\s/$.?#].[^\s]*/iSu';
		// $matches = array();
		// preg_match_all($urlRegex, $text, $matches);
		// $usedPatterns = array();
		// foreach($matches as $pattern) {
			// if(!array_key_exists($pattern, $usedPatterns)) {
				// $usedPatterns[$pattern]=true;
				// $text = mb_ereg_replace($pattern, '<a href="$1">$1</a>', $text);
			// }
		// }
		$template="<a href=".Yii::app()->getBaseUrl().'/index.php/search/search?term=%23\\2'.">\\1#\\2\\3</a>";
		$text = preg_replace('/(^|[>\s\.])#(\w\w+)($|[<\s\.])/u',$template,$text);
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
		if($model->save()) {
			$changes=$this->calculateChanges($oldAttributes, $model->attributes, $model);
			$this->updateChangelog($model,$changes);
			if(($model instanceof Product) == false) // products are not assigned to anyone
				if($model->assignedTo!=Yii::app()->user->getName()) {
					$notif=new Notifications;
					if($api == 0) {
						$profile = CActiveRecord::model('ProfileChild')->findByAttributes(array('username'=>$model->assignedTo));
						if(isset($profile))
							$notif->text="$profile->fullName has created a(n) ".$name." for you";
					} else {
						$notif->text="An API request has created a(n) ".$name." for you";
					}
					$notif->user=$model->assignedTo;
					$notif->createDate=time();
					$notif->viewed=0;
					$notif->record="$name:$model->id";
					$notif->save();
				
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
		$temp = $oldAttributes;
		$changes = $this->calculateChanges($temp, $model->attributes, $model);
		$model = $this->updateChangelog($model,$changes);
		if($model->save()) {
			if($model instanceof Actions && $api == 0) {
				if(isset($_GET['redirect']) && $model->associationType != 'none')	// if the action has an association
					$this->redirect(array($model->associationType.'/view','id'=>$model->associationId));	// go back to the association
				else	// no association
					$this->redirect(array('actions/view','id'=>$model->id));	// view the action
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
			$this->render('index',array(
			'model'=>$model,
			// 'gvSettings'=>$gvSettings,
		));
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
                $model->createDate=time();
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
					if(isset($newPieces[1]))
						$changes[]=$newPieces[1];

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
					$tag->tag=$match;
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
					if(substr($tag->tag,0,1)=='#' || substr($tag->tag,1,1)=='#'){
							if(substr($tag->tag,1,1)=='#')
									$tag->tag=substr($tag->tag,1);
							if($tag->save()){

							}
					}
			}
		}
		return $model;
	}
	
	public function cleanUpTags($model){
		$type=get_class($model);
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
				$oldTag=Tags::model()->findByAttributes(array('tag'=>substr($match,1),'type'=>$type,'itemId'=>$model->id));
				if(isset($oldTag))
					$oldTag->delete();
			}
		}
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
						if($criteria->type=='notification'){
							$pieces=explode(", ",$criteria->users);
							foreach($pieces as $piece){
								$notif=new Notifications;
								$profile=CActiveRecord::model('ProfileChild')->findByAttributes(array('username'=>Yii::app()->user->getName()));
								if($criteria->comparisonOperator=="="){
									$notif->text="A(n) ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue";
								}else if($criteria->comparisonOperator==">"){
									$notif->text="A(n) ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue";
								}else if($criteria->comparisonOperator=="<"){
									$notif->text="A(n) ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue";
								}else if($criteria->comparisonOperator=="change"){
									$notif->text="A(n) ".$this->modelClass." has had field $criteria->modelField changed";
								}
								$notif->user=$piece;
								$notif->createDate=time();
								$notif->viewed=0;
								$notif->record=$this->modelClass.":".$new['id'];
								$notif->save();
							}
						}else if($criteria->type=='action'){
							$pieces=explode(", ",$criteria->users);
							foreach($pieces as $piece){
								$action=new Actions;
								$action->assignedTo=$piece;
								if($criteria->comparisonOperator=="="){
									$action->actionDescription="A(n) ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue";
								}else if($criteria->comparisonOperator==">"){
									$action->actionDescription="A(n) ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue";
								}else if($criteria->comparisonOperator=="<"){
									$action->actionDescription="A(n) ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue";
								}else if($criteria->comparisonOperator=="change"){
									$action->actionDescription="A(n) ".$this->modelClass." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue";
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
						}else if($criteria->type=='assignment'){
							$model->assignedTo=$criteria->users;
							$model->save();
							$notif=new Notifications;  
							$notif->text="A(n)".$this->modelClass." has been re-assigned to you.";
							$notif->user=$model->assignedTo;
							$notif->createDate=time();
							$notif->viewed=0;
							$notif->record=$this->modelClass.":".$new['id'];
							$notif->save();
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

	public function sendUserEmail($to,$subject,$message) {

		$user = CActiveRecord::model('UserChild')->findByPk(Yii::app()->user->getId());

		require_once('protected/components/phpMailer/class.phpmailer.php');
		// require_once('protected/components/phpMailer/class.phpmailer.php');
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
				
				$mail->Host			= Yii::app()->params->admin->emailHost;
				$mail->Port			= Yii::app()->params->admin->emailPort;
				$mail->SMTPSecure	= Yii::app()->params->admin->emailSecurity;
				
				if(Yii::app()->params->admin->emailUseAuth == 'admin') {
					$mail->SMTPAuth		= true;
					$mail->Username		= Yii::app()->params->admin->emailUser;
					$mail->Password		= Yii::app()->params->admin->emailPass;
				}
				break;
			case 'mail':
			default:
				$phpMail->IsMail();
		}

		try {
			if(empty(Yii::app()->params->profile->emailAddress))
				throw new Exception('<b>'.Yii::t('app','Your profile doesn\'t have a valid email address.').'</b>');
		
			$phpMail->AddReplyTo(Yii::app()->params->profile->emailAddress,$user->name);
			$phpMail->SetFrom(Yii::app()->params->profile->emailAddress,$user->name);
			
			
			if(isset($to['to'])) {
				foreach($to['to'] as $target) {
					if(count($target) == 2)
						$phpMail->AddAddress($target[1],$target[0]);
				}
			} else {
				if(count($to) == 2 && !is_array($to[0]))	// this is just an array of [name, address],
					$phpMail->AddAddress($to[1],$to[0]);	// not an array of arrays
				else {
					foreach($to as $target) {					//this is an array of [name, address] subarrays
						if(count($target) == 2)
							$phpMail->AddAddress($target[1],$target[0]);
					}
				}
			}
			if(isset($to['cc'])) {
				foreach($to['cc'] as $target) {
					if(count($target) == 2)
						$phpMail->AddCC($target[1],$target[0]);
				}
			}
			if(isset($to['bcc'])) {
				foreach($to['bcc'] as $target) {
					if(count($target) == 2)
						$phpMail->AddBCC($target[1],$target[0]);
				}
			}
			
			// $dropbox = Yii::app()->params->admin->dropboxEmail;
			$dropbox = 'dropbox@'.preg_replace('/^www\./','',$_SERVER['HTTP_HOST']);	// determine the dropbox email
			if(PHPMailer::ValidateAddress($dropbox))
				$phpMail->AddCC($dropbox);
			
			$phpMail->Subject = $subject;
//			$phpMail->AltBody = $message;
			$phpMail->MsgHTML($message);
			// $phpMail->Body = $message;
			$phpMail->Send();
			
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
	
	function formatDate($timestamp) {
		if(empty($timestamp))
		    return '';
		else
			if(Yii::app()->language == 'en')
			    return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('long'), $timestamp);
			else
			    return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('short'), $timestamp);
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
			    return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('medium'), $timestamp) .' 23:59';
			else
			    return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('short'), $timestamp) .' 23:59';
	}
	
	function formatDateTime($timestamp) {
		if(empty($timestamp))
		    return '';
		else
			if(Yii::app()->language == 'en')
				return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('medium') .' HH:mm', $timestamp);
			else
			    return Yii::app()->dateFormatter->format(Yii::app()->locale->getDateFormat('short') .' HH:mm', $timestamp);
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