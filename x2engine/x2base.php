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
	public function actionView($model, $type) {

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
		$template="<a href=".Yii::app()->getBaseUrl().'/index.php/search/search?term=%23\\2'."> #\\2</a>";
		$text = mb_ereg_replace('(^|\s)#(\w\w+)',$template,$text);
                $text = mb_ereg_replace('(>)#(\w\w+)',">".$template,$text);
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
	public function actionCreate($model, $name) {
		$users=UserChild::getNames();

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST[$name])) {
			
			$model->attributes=$_POST[$name];
			if(isset($_POST['auto_select']) && $model instanceof Contacts){
				$model->company=$_POST['auto_select'];
			}
			$model=$this->updateChangelog($model);
			$model->createDate=time();
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
			else{
			}
		}
		$this->render('create',array(
			'model'=>$model,
			'users'=>$users,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($model, $name) {
		$users=UserChild::getNames();
		$accounts=AccountChild::getNames();
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST[$name])) {
			$model->attributes=$_POST[$name];
			$model=$this->updateChangelog($model);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
			'users'=>$users,
			'accounts'=>$accounts,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */

	/**
	 * Lists all models.
	 */
	public function actionIndex($model,$name) {

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
	public function actionAdmin($model, $name) {

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
                
                $changelog=new Changelog;
                $changelog->type=get_class($model);
                $changelog->itemId=$model->id;
                $changelog->changedBy=Yii::app()->user->getName();
                $changelog->changed=$change;
                $changelog->timestamp=time();
                
                $changelog->save();
                
		return $model;
	}
        
        protected function calculateChanges($old, $new){
            
            $arr=array();
            $keys=array_keys($new);
            for($i=0;$i<count($new);$i++){
                if($old[$keys[$i]]!=$new[$keys[$i]]){
                    $arr[$keys[$i]]=$new[$keys[$i]];
                }
            }
            $str='';
            foreach($arr as $key=>$item){
                $str.="<b>$key</b> <u>FROM:</u> $old[$key] <u>TO:</u> $item <br />";
            }
            return $str;
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