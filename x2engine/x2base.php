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
class x2base extends Controller {
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
	
	// Let locale and character encoding.
	public function onBeginRequest() {
		setlocale(LC_ALL, 'en_US.UTF-8');
	}
	
	public $portlets=array(); // This is the array of widgets on the sidebar.
        public $modelClass = 'AdminChild';
        

	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout = '//layouts/column2';

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
			array('allow', // allow authenticated user to perform the following actions
				'actions'=>array('index','view','create','update','search','delete'),
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

	/**
	 * Displays a particular model.  This method is called in child controllers
	 * which pass it a model to display and what type of model it is (i.e. Contact,
	 * Sale, Account).  It also creates an action history and provides appropriate
	 * variables to the view.
	 * 
	 * @param CActiveRecord $model The model to be displayed
	 * @param String $type The type of the module being displayed
	 */
	public function view($model, $type) {

		$actionHistory=new CActiveDataProvider('Actions', array(
			'criteria'=>array(
				'order'=>'(IF (completeDate IS NULL, dueDate, completeDate)) DESC, createDate DESC',
				'condition'=>'associationId='.$model->id.' AND associationType=\''.$type.'\''
		)));

		$users=UserChild::getNames();
		$names=$this->parseType($type);
		$showActionForm = isset($_GET['showActionForm']);
		$this->render('view',array(
			'model'=>$model,
			'actionHistory'=>$actionHistory,
			'users'=>$users,
			'names'=>$names,
			'showActionForm'=>$showActionForm	//tell view whether or not to show 'add action'
		));
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
			'actions'=>'ActionChild',
			'contacts'=>'ContactChild',
			'projects'=>'ProjectChild',
			'accounts'=>'AccountChild',
			'sales'=>'SaleChild',
			'social'=>'SocialChild',
		);
		
		if(array_key_exists($type,$classes))
			return CActiveRecord::model($classes[$type])->findByPk($id);
		else
			return null;
	}
	
	/**
	 * Returns an array of names of database entries for a given type.
	 * @param String $type The type of record to return
	 * @return Array An array with id=>name of records for the type provided
	 */
	protected function parseType($type) {
		switch($type) {
			case 'contacts':
				return ContactChild::getAllNames(); 
			case 'projects':
				return ProjectChild::getNames();
			case 'accounts':
				return AccountChild::getNames();
			case 'cases':
				return CaseChild::getNames();
			case 'sales':
				return SaleChild::getNames();
			default:
				return array('0'=>'None');
		}
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
            $changes=$this->calculateChanges($oldAttributes, $model->attributes);
            if(substr($this->modelClass,-5)=="Child")
                $name=substr($this->modelClass,0,-5)."s";
            if($model->save()){
                $this->updateChangelog($model,$changes);
                if($model->assignedTo!=Yii::app()->user->getName()){
                    $notif=new Notifications;
                    if($api==0){
                        $profile=CActiveRecord::model('ProfileChild')->findByAttributes(array('username'=>Yii::app()->user->getName()));
                        $notif->text="$profile->fullName has created a(n) ".$name." for you";
                    }else{
                        $notif->text="An API request has created a(n) ".$name." for you";
                    }
                    $notif->user=$model->assignedTo;
                    $notif->createDate=time();
                    $notif->viewed=0;
                    $notif->record="$name:$model->id";
                    $notif->save();

                }
                if($model instanceof Actions && $api==0){
                    if(isset($_GET['inline']) || $model->type=='note')
                        $this->redirect(array($model->associationType.'/view','id'=>$model->associationId));
                    else
                        $this->redirect(array('view','id'=>$model->id));
                }else if($api==0){
                    $this->redirect(array('view','id'=>$model->id));
                }else{
                    return true;
                }
            }else{
                return false;
            }
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function update($model, $oldAttributes, $api) {
            $temp=$oldAttributes;
            $changes=$this->calculateChanges($temp, $model->attributes);
            $model=$this->updateChangelog($model,$changes);
            if($model->save()){
                if($model instanceof Actions && $api==0){
                    if(isset($_GET['redirect']) && $model->associationType!='none')	// if the action has an association
					$this->redirect(array($model->associationType.'/view','id'=>$model->associationId));	// go back to the association
				else	// no association
					$this->redirect(array('actions/view','id'=>$model->id));	// view the action
                }else if($api==0){
                    $this->redirect(array('view','id'=>$model->id));
                }else{
                    return true;
                }
            }else{
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
		
		
		
		if($change!='Create' && $change!='Completed' && $change!='Edited'){
			
			if($change!=""){
				$pieces=explode("TO:",$change);
				$change=$pieces[1]; 
				$forDeletion=$pieces[0];
				preg_match_all('/(^|\s|)#(\w\w+)/',$forDeletion,$deleteMatches);
				$deleteMatches=$deleteMatches[0];
				foreach($deleteMatches as $match){
					$oldTag=Tags::model()->findByAttributes(array('tag'=>substr($match,1),'type'=>$type,'itemId'=>$model->id));
					if(isset($oldTag))
						$oldTag->delete();
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
					
				}else{
					print_r($tag->getErrors());
					exit;
				}
			}else{
				print_r($tag->getErrors());
				exit;
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
	
	protected function calculateChanges($old, $new){
		$arr=array();
		$keys=array_keys($new);
		for($i=0;$i<count($keys);$i++){
                    if($old[$keys[$i]]!=$new[$keys[$i]]){
                        $arr[$keys[$i]]=$new[$keys[$i]];
                        $allCriteria=Criteria::model()->findAllByAttributes(array('modelType'=>substr($this->modelClass,0,-5)."s",'modelField'=>$keys[$i]));
                        foreach($allCriteria as $criteria){
                            if($criteria->comparisonOperator=="="){
                                if($new[$keys[$i]]==$criteria->modelValue){
                                    $pieces=explode(", ",$criteria->users);
                                    foreach($pieces as $piece){
                                        $notif=new Notifications;
                                        $profile=CActiveRecord::model('ProfileChild')->findByAttributes(array('username'=>Yii::app()->user->getName()));
                                        $notif->text="A(n) ".substr($this->modelClass,0,-5)." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue";
                                        $notif->user=$piece;
                                        $notif->createDate=time();
                                        $notif->viewed=0;
                                        $notif->record=substr($this->modelClass,0,-5)."s:".$new['id'];
                                        $notif->save();
                                    }
                                }
                            }
                            else if($criteria->comparisonOperator==">"){
                                if($new[$keys[$i]]>=$criteria->modelValue){
                                    $pieces=explode(":",$criteria->users);
                                    foreach($pieces as $piece){
                                        $notif=new Notifications;
                                        $profile=CActiveRecord::model('ProfileChild')->findByAttributes(array('username'=>Yii::app()->user->getName()));
                                        $notif->text="A(n) ".substr($this->modelClass,0,-5)." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue";
                                        $notif->user=$piece;
                                        $notif->createDate=time();
                                        $notif->viewed=0;
                                        $notif->record=substr($this->modelClass,0,-5)."s:".$new['id'];
                                        $notif->save();
                                    }
                                }
                            }
                            else if($criteria->comparisonOperator=="<"){
                                if($new[$keys[$i]]<=$criteria->modelValue){
                                    $pieces=explode(":",$criteria->users);
                                    foreach($pieces as $piece){
                                        $notif=new Notifications;
                                        $profile=CActiveRecord::model('ProfileChild')->findByAttributes(array('username'=>Yii::app()->user->getName()));
                                        $notif->text="A(n) ".substr($this->modelClass,0,-5)." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue";
                                        $notif->user=$piece;
                                        $notif->createDate=time();
                                        $notif->viewed=0;
                                        $notif->record=substr($this->modelClass,0,-5)."s:".$new['id'];
                                        $notif->save();
                                    }
                                }
                            }
                            else if($criteria->comparisonOperator=="change"){
                                if($new[$keys[$i]]!=$old[$keys[$i]]){
                                    $pieces=explode(":",$criteria->users);
                                    foreach($pieces as $piece){
                                        $notif=new Notifications;
                                        $profile=CActiveRecord::model('ProfileChild')->findByAttributes(array('username'=>Yii::app()->user->getName()));
                                        $notif->text="A(n) ".substr($this->modelClass,0,-5)." has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue";
                                        $notif->user=$piece;
                                        $notif->createDate=time();
                                        $notif->viewed=0;
                                        $notif->record=substr($this->modelClass,0,-5)."s:".$new['id'];
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
	
	
	/**
	 * Sets widgets on the page on a per user basis.
	 */
	public function filterSetPortlets($filterChain) {
		$this->portlets = ProfileChild::getWidgets();
		// foreach($widgets as $key=>$value) {
			// $options = ProfileChild::parseWidget($value,$key);
			// $this->portlets[$key] = $options;
		// }
		$filterChain->run();
	}


	// This function needs to be made in your extensions of the class with similar code. 
	// Replace "SaleChild" with the Model being used.
	/**public function loadModel($id)
	{
		$model=SaleChild::model()->findByPk((int)$id);
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
}
?>