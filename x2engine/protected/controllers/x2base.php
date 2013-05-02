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
 * Base controller for all application controllers with CRUD operations
 *
 * @package X2CRM.controllers
 */
abstract class x2base extends X2Controller {
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
	public $layout = '//layouts/column3';

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
	public $leftPortlets = array(); // additional menu blocks on the left mneu
	public $modelClass = 'Admin';
	public $actionMenu = array();

	/**
	 * @return array action filters
	 */
	public function filters() {
		return array(
			//'accessControl', // perform access control for CRUD operations
			'setPortlets', // performs widget ordering and show/hide on each page
		);
	}

	public function behaviors() {
		return array(
			'CommonControllerBehavior' => array('class' => 'application.components.CommonControllerBehavior')
		);
	}

	protected function beforeAction($action = null) {
		$auth = Yii::app()->authManager;
		$params = array();
		$action = $this->getAction()->getId();
		$exceptions = array('updateStageDetails','deleteList','updateList','userCalendarPermissions','exportList','updateLocation');
        if(class_exists($this->modelClass)){
            $model=X2Model::model($this->modelClass);
        }
		if(isset($_GET['id']) && !in_array($action,$exceptions) && !Yii::app()->user->isGuest && isset($model)) {
			if ($model->hasAttribute('assignedTo')) {
				$model=X2Model::model($this->modelClass)->findByPk($_GET['id']);
                if($model!==null){
                    $params['assignedTo']=$model->assignedTo;
                }
            }
		}

		$actionAccess = ucfirst($this->getId()) . ucfirst($this->getAction()->getId());
		$authItem = $auth->getAuthItem($actionAccess);
		if(Yii::app()->user->checkAccess($actionAccess, $params) || is_null($authItem) || Yii::app()->user->getName() == 'admin')
			return true;
		elseif(Yii::app()->user->isGuest){
			Yii::app()->user->returnUrl = Yii::app()->request->url;
			$this->redirect($this->createUrl('/site/login'));
        }else
			throw new CHttpException(403, 'You are not authorized to perform this action.');
	}

	public function actions() {
		return array(
			'inlineEmail' => array(
				'class' => 'InlineEmailAction',
			),
		);
	}

    /**
     * Renders a view with any attached scripts, WITHOUT the core scripts.
     *
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
    public function renderPartialAjax($view, $data = null, $return = false, $includeScriptFiles = false) {

        if (($viewFile = $this->getViewFile($view)) !== false) {

            // if(class_exists('ReflectionClass')) {
            // $counter = abs(crc32($this->route));
            // $reflection = new ReflectionClass('CWidget');
            // $property = $reflection->getProperty('_counter');
            // $property->setAccessible(true);
            // $property->setValue($counter);
            // }

            $output = $this->renderFile($viewFile, $data, true);

            $cs = Yii::app()->clientScript;
            Yii::app()->setComponent('clientScript', new X2ClientScript);
            $output = $this->renderPartial($view, $data, true);
            $output .= Yii::app()->clientScript->renderOnRequest($includeScriptFiles);
            Yii::app()->setComponent('clientScript', $cs);

            if ($return)
                return $output;
            else
                echo $output;
        } else {
            throw new CException(Yii::t('yii', '{controller} cannot find the requested view "{view}".', array('{controller}' => get_class($this), '{view}' => $view)));
        }
    }

    /**
     * Determines if we have permission to edit something based on the assignedTo field.
     *
     * @param mixed $model The model in question (subclass of {@link CActiveRecord} or {@link X2Model}
     * @return boolean
     */
    public function editPermissions(&$model) {
        if (Yii::app()->user->checkAccess('AdminIndex') || !$model->hasAttribute('assignedTo'))
            return true;
        else
            return $model->assignedTo == Yii::app()->user->getName() || in_array($model->assignedTo, Yii::app()->params->groups);
    }

    /**
     * Determines if we have permission to edit something based on the assignedTo field.
     *
     * @param mixed $model The model in question (subclass of {@link CActiveRecord} or {@link X2Model}
     * @param string $action
     * @return boolean
     */
    public function checkPermissions(&$model, $action = null) {

        $view = false;
        $edit = false;
        // if we're the admin, visibility is public, there is no visibility/assignedTo, or it's directly assigned to the user, then we're done
        if (Yii::app()->user->checkAccess('AdminIndex') || !$model->hasAttribute('assignedTo') || ($model->assignedTo == 'Anyone' && ($model->hasAttribute('visibility') && $model->visibility!=0) || !$model->hasAttribute('visibility')) || $model->assignedTo == Yii::app()->user->getName()) {

            $edit = true;
        } elseif (!$model->hasAttribute('visibility') || $model->visibility == 1) {

            $view = true;
        } else {
            if (ctype_digit((string)$model->assignedTo) && !empty(Yii::app()->params->groups)) {  // if assignedTo is numeric, it's a group
                $edit = in_array($model->assignedTo, Yii::app()->params->groups); // if we're in the assignedTo group we act as owners
            } elseif ($model->visibility == 2) {  // if record is shared with owner's groups, see if we're in any of those groups
                $view = (bool) Yii::app()->db->createCommand('SELECT COUNT(*) FROM x2_group_to_user A JOIN x2_group_to_user B
																ON A.groupId=B.groupId AND A.username=:user1 AND B.username=:user2')
                                ->bindValues(array(':user1' => $model->assignedTo, ':user2' => Yii::app()->user->getName()))
                                ->queryScalar();
            }
        }

        $view = $view || $edit; // edit permission implies view permission

        if (!isset($action)) // hash of all permissions if none is specified
            return array('view' => $view, 'edit' => $edit, 'delete' => $edit);
        elseif ($action == 'view')
            return $view;
        elseif ($action == 'edit')
            return $edit;
        elseif ($action == 'delete')
            return $edit;
        else
            return false;
    }

    /**
     * Displays a particular model.
     *
     * This method is called in child controllers
     * which pass it a model to display and what type of model it is (i.e. Contact,
     * Opportunity, Account).  It also creates an action history and provides appropriate
     * variables to the view.
     *
     * @param mixed $model The model to be displayed (subclass of {@link CActiveRecord} or {@link X2Model}
     * @param String $type The type of the module being displayed
     */
    public function view(&$model,$type=null,$params=array()) {

		if($type === null)	// && $model->asa('X2LinkableBehavior') !== null)	// should only happen when the model is known to have X2LinkableBehavior
			$type = $model->module;

		if(!isset($_GET['ajax']))
			X2Flow::trigger('RecordViewTrigger',array('model'=>$model));

		$this->render('view', array_merge($params,array(
			'model' => $model,
			'actionHistory' => $this->getHistory($model,$type),
			'currentWorkflow' => $this->getCurrentWorkflow($model->id,$type),
		)));
    }

    /**
     * Obtain the history of actions associated with a model.
     *
     * Returns the data provider that references the history.
     * @param mixed $model The model in question (subclass of {@link CActiveRecord} or {@link X2Model}
     * @param mixed $type The association type (type of the model)
     * @return CActiveDataProvider
     */
    public function getHistory(&$model, $type = null) {

		if (!isset($type))
			$type = get_class($model);

		$filters = array(
			'actions'=>' AND type IS NULL',
			'comments'=>' AND type="note"',
			'attachments'=>' AND type="attachment"',
			'all'=>''
		);

		$history = 'all';
		if(isset($_GET['history']) && array_key_exists($_GET['history'],$filters))
			$history = $_GET['history'];

		return new CActiveDataProvider('Actions',array(
			'criteria'=>array(
				'order'=>'GREATEST(createDate, IFNULL(completeDate,0), IFNULL(dueDate,0), IFNULL(lastUpdated,0)) DESC',
				'condition'=>'associationId='.$model->id.' AND associationType="'.$type.'" '.$filters[$history].' AND (visibility="1" OR assignedTo="admin" OR assignedTo="'.Yii::app()->user->getName().'")'
			)
		));
	}

	/**
	 * Obtains the current worflow for a model of given type and id.
	 * Prioritizes incomplete workflows over completed ones.
	 * @param integer $id the ID of the record
	 * @param string $type the associationType of the record
	 * @return int the ID of the current workflow (0 if none are found)
	 */
	public function getCurrentWorkflow($id, $type) {
		$currentWorkflow = Yii::app()->db->createCommand()
			->select('workflowId,completeDate,createDate')
			->from('x2_actions')
			->where('type="workflow" AND associationType=:type AND associationId=:id',array(':type'=>$type,':id'=>$id))
			->order('IF(completeDate = 0 OR completeDate IS NULL,1,0) DESC, createDate DESC')
			->limit(1)
			->queryRow(false);

		if($currentWorkflow === false || !isset($currentWorkflow[0]))
			return 0;

		return $currentWorkflow[0];
    }

    /**
     * Convert currency to the proper format
     *
     * @param String $str The currency string
     * @param Boolean $keepCents Whether or not to keep the cents
     * @return String $str The modified currency string.
     */
    public static function parseCurrency($str, $keepCents) {

        $cents = '';
        if ($keepCents) {
            $str = mb_ereg_match('[\.,]([0-9]{2})$', $str, $matches); // get cents
            $cents = $matches[1];
        }
        $str = mb_ereg_replace('[\.,][0-9]{2}$', '', $str); // remove cents
        $str = mb_ereg_replace('[^0-9]', '', $str);  //remove all non-numbers

        if (!empty($cents))
            $str .= ".$cents";

        return $str;
    }

    /**
     * Used in function convertUrls
     *
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    protected static function compareChunks($a, $b) {
        return $a[1] - $b[1];
    }

    /**
     * Replaces any URL in text with an html link (supports mailto links)
     *
     * @todo refactor this out of controllers
     * @param string $text Text to be converted
     * @param boolean $convertLineBreaks
     */
    public static function convertUrls($text, $convertLineBreaks = true) {
        /* $text = preg_replace(
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
          ); */



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
        $tags_with_urls = "/(<a[^>]*>.*<\/a>)|(<img[^>]*>)|(<iframe[^>]*>.*<\/iframe>)/i";
        $text_to_add_links = preg_split($tags_with_urls, $text, NULL, PREG_SPLIT_OFFSET_CAPTURE);
        $matches = array();
        preg_match_all($tags_with_urls, $text, $matches, PREG_OFFSET_CAPTURE);
        $text_to_leave = $matches[0];

        // Convert all URLs into html links
        foreach ($text_to_add_links as $i => $value) {
            $text_to_add_links[$i][0] = preg_replace(
                    array($url_pattern,
                $email_pattern), array("<a href=\"\\0\">\\0</a>",
                "<a href=\"mailto:\\0\">\\0</a>"), $text_to_add_links[$i][0]
            );
        }

        // Merge the arrays and sort to be in the original order
        $all_text_chunks = array_merge($text_to_add_links, $text_to_leave);

        usort($all_text_chunks, 'x2base::compareChunks');

        $new_text = "";
        foreach ($all_text_chunks as $chunk) {
            $new_text = $new_text . $chunk[0];
        }
        $text = $new_text;

        // Make sure all links open in new window, and have http:// if missing
        $text = preg_replace(
                array('/<a([^>]+)target=("[^"]+"|\'[^\']\'|[^\s]+)([^>]+)/i',
            '/<a([^>]+href="?\'?)(www\.|ftp\.)/i'), array('<a\\1\\3',
            '<a\\1http://\\2'), $text
        );

        //convert any tags into links
        $template = "\\1<a href=" . Yii::app()->createUrl('/search/search') . '?term=%23\\2' . ">#\\2</a>";
        //$text = preg_replace('/(^|[>\s\.])#(\w\w+)($|[<\s\.])/u',$template,$text);
        $text = preg_replace('/(^|[>\s\.])#(\w\w+)/u', $template, $text);

        //TODO: separate convertUrl and convertLineBreak concerns
        if ($convertLineBreaks)
            return Formatter::convertLineBreaks($text, true, false);
        else
            return $text;
    }

    // Deletes a note action
    public function actionDeleteNote($id) {
        $note = X2Model::model('Actions')->findByPk($id);
        if ($note->delete()) {
            $this->redirect(array('view', 'id' => $note->associationId));
        }
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
	public function create($model, $oldAttributes, $api) {
		// $name = get_class($model);
		// $model->createDate = time();
		// if($model->hasAttribute('lastUpdated'))
			// $model->lastUpdated=time();
		// if($model->hasAttribute('lastActivity'))
			// $model->lastActivity = time();

		// if ($model->save()) {

			// relationships (now in X2Model::afterSave())
			/* if (!($model instanceof Actions)) {
				$fields = Fields::model()->findAllByAttributes(array('modelName' => $name, 'type' => 'link'));
				foreach ($fields as $field) {
					$fieldName = $field->fieldName;
					if (isset($model->$fieldName) && is_numeric($model->$fieldName)) {
						if (is_null(Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE
							(firstType='$name' AND firstId='$model->id' AND secondType='" . ucfirst($field->linkType) . "' AND secondId='" . $model->$fieldName . "')
							OR (secondType='$name' AND secondId='$model->id' AND firstType='" . ucfirst($field->linkType) . "' AND firstId='" . $model->$fieldName . "')"))) {
							$rel = new Relationships;
							$rel->firstType = $name;
							$rel->secondType = ucfirst($field->linkType);
							$rel->firstId = $model->id;
							$rel->secondId = $model->$fieldName;
							if ($rel->save()) {
								$lookup = Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE
									(firstType='$name' AND firstId='$model->id' AND secondType='" . ucfirst($field->linkType) . "' AND secondId='" . $oldAttributes[$fieldName] . "')
									OR (secondType='$name' AND secondId='$model->id' AND firstType='" . ucfirst($field->linkType) . "' AND firstId='" . $oldAttributes[$fieldName] . "')");
								if (isset($lookup))
									$lookup->delete();
							}
						}
					}
				}
			} */
			// $changes = $this->calculateChanges($oldAttributes, $model->attributes, $model);
			// $this->updateChangelog($model, $changes);


			// create event, and notification if record was reassigned - now in X2ChangeLogBehavior::afterSave()
			/* $event=new Events;
			if($model->hasAttribute('visibility')){
				$event->visibility=$model->visibility;
			}
			$event->associationType=$name;
			$event->associationId=$model->id;
			$event->user=Yii::app()->user->getName();
			$event->type='record_create';
			if(!$model instanceof Contacts || $api==0){ // Event creation already handled by web lead.
				$event->save();
			}
			if ($model->hasAttribute('assignedTo')) {
				if (!empty($model->assignedTo) && $model->assignedTo != Yii::app()->user->getName() && $model->assignedTo != 'Anyone') {

					$notif = new Notification;
					$notif->user = $model->assignedTo;
					$notif->createdBy = ($api == 0) ? 'API' : Yii::app()->user->getName();
					$notif->createDate = time();
					$notif->type = 'create';
					$notif->modelType = $name;
					$notif->modelId = $model->id;
					$notif->save();
				}
			} */
			// if ($model instanceof Actions) {
				// create reminder - now in Actions::afterCreate()
				/* if(empty($model->type)){
					$event=new Events;
					$event->timestamp=$model->dueDate;
					$event->visibility=$model->visibility;
					$event->type='action_reminder';
					$event->associationType="Actions";
					$event->associationId=$model->id;
					$event->user=$model->assignedTo;
					$event->save();
				} */
				// if($api==0){
					// now in ActionsController::actionCreate
					/* if (isset($_GET['inline']) || $model->type == 'note')
						if ($model->associationType == 'product' || $model->associationType == 'products')
							$this->redirect(array('/products/products/view', 'id' => $model->associationId));
						//TODO: avoid such hackery
						else if ($model->associationType == 'Campaign')
							$this->redirect(array('/marketing/marketing/view', 'id' => $model->associationId));
						else
							$this->redirect(array('/' . $model->associationType . '/' . $model->associationType . '/view', 'id' => $model->associationId));
					else
						$this->redirect(array('view', 'id' => $model->id)); */
				// }
			// } else if ($api == 0) {
		if($model->save()) {
			if($api == 0)
				$this->redirect(array('view', 'id' => $model->id));
			else
				return true;
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
		// $name = $this->modelClass;
		// if($model->hasAttribute('lastActivity'))
			// $model->lastActivity = time();

		// $temp = $oldAttributes;
		// $changes = $this->calculateChanges($temp, $model->attributes, $model);
		// $model = $this->updateChangelog($model, $changes);
/*		if($model->save()) {
			if( $model instanceof Contacts) {
			// now in Contacts::afterUpdate()
				// send subscribe emails if anyone has subscribed to this contact
				$result = Yii::app()->db->createCommand()
						->select()
						->from('x2_subscribe_contacts')
						->where("contact_id={$model->id}")
						->queryAll();
				
				$datetime = Formatter::formatLongDateTime(time());
				$modelLink = CHtml::link($model->name, $this->createAbsoluteUrl('/contacts/' . $model->id));
				$subject = "X2CRM: {$model->name} updated";
				$message = "Hello,<br>\n<br>\n";
				$message .= "You are receiving this email because you are subscribed to changes made to the contact $modelLink in X2CRM. ";
				$message .= "The following changes were made on $datetime:<br>\n<br>\n";

				foreach($changes as $attribute=>$change) {
					if($attribute != 'lastActivity') {
						$old = $change['old'] == ''? '-----' : $change['old'];
						$new = $change['new'] == ''? '-----' : $change['new'];
						$label = $model->getAttributeLabel($attribute);
						$message .= "$label: $old => $new<br>\n";
					}
				}

				$message .="<br>\nYou can unsubscribe to these messages by going to $modelLink and clicking Unsubscribe.<br>\n<br>\n";

				$adminProfile = Profile::model()->findByPk(1);
				foreach($result as $subscription) {
					$profile = Profile::model()->findByPk($subscription['user_id']);
					if($profile && $profile->emailAddress && $adminProfile && $adminProfile->emailAddress) {
						$to = array($profile->fullName, $profile->emailAddress);
						$from = array('name'=> $adminProfile->fullName, 'address'=>$adminProfile->emailAddress);
						$this->sendUserEmail($to, $subject, $message, null, $from);
					}
				}

			}*/
			// relationships, now in X2Model::afterSave()
			/* if (!($model instanceof Actions)) {

				$fields = Fields::model()->findAllByAttributes(array('modelName' => $name, 'type' => 'link'));
				foreach ($fields as $field) {
					$fieldName = $field->fieldName;
					if (isset($model->$fieldName) && $model->$fieldName != "") {
						if (is_null(Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE
								(firstType=:name AND firstId=:id AND secondType=:linktype AND secondId=:fieldname)
								OR (secondType=:name AND secondId=:id AND firstType=:linktype AND firstId=:fieldname)",array(':name'=>$name,':id'=>$model->id,':linktype'=>ucfirst($field->linkType),':fieldname'=>$model->$fieldName)))) {

							$rel = new Relationships;
							$rel->firstType = $name;
							$rel->secondType = ucfirst($field->linkType);
							$rel->firstId = $model->id;
							$rel->secondId = $model->$fieldName;
							if ($rel->save()) {
								if ($field->linkType != 'contacts' && $field->linkType != 'Contacts') {
									if (is_numeric($oldAttributes[$fieldName]))
										$oldRel = X2Model::model(ucfirst($field->linkType))->findByPk($oldAttributes[$fieldName]);
									else
										$oldRel = X2Model::model(ucfirst($field->linkType))->findByAttributes(array('name' => $oldAttributes[$fieldName]));
								}
								else {
									$pieces = explode(" ", $oldAttributes[$fieldName]);
									if (count($pieces) > 1) {
										if (is_numeric($oldAttributes[$fieldName]))
											$oldRel = X2Model::model(ucfirst($field->linkType))->findByPk($oldAttributes[$fieldName]);
										else
											$oldRel = X2Model::model(ucfirst($field->linkType))->findByAttributes(array('firstName' => $pieces[0], 'lastName' => $pieces[1]));
									}
								}
								if (isset($oldRel)) {
									$lookup = Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE
									(firstType=:name AND firstId=:id AND secondType=:linktype AND secondId=:oldid)
									OR (secondType=:name AND secondId=:id AND firstType=:linktype AND firstId=:oldid)",array(':name'=>$name,':id'=>$model->id,':linktype'=>ucfirst($field->linkType),':oldid'=>$oldRel->id));
									if (isset($lookup)) {
										$lookup->delete();
									}
								}
							}
						}
					} elseif ($model->$fieldName == "") {
						if ($field->linkType != 'contacts' && $field->linkType != 'Contacts') {
							if (is_numeric($oldAttributes[$fieldName]))
								$oldRel = X2Model::model(ucfirst($field->linkType))->findByPk($oldAttributes[$fieldName]);
							else
								$oldRel = X2Model::model(ucfirst($field->linkType))->findByAttributes(array('name' => $oldAttributes[$fieldName]));
						}else {
							$pieces = explode(" ", $oldAttributes[$fieldName]);
							if (count($pieces) > 1) {
								if (is_numeric($oldAttributes[$fieldName]))
									$oldRel = X2Model::model(ucfirst($field->linkType))->findByPk($oldAttributes[$fieldName]);
								else
									$oldRel = X2Model::model(ucfirst($field->linkType))->findByAttributes(array('firstName' => $pieces[0], 'lastName' => $pieces[1]));
							}
						}
						if (isset($oldRel)) {
							$lookup = Relationships::model()->findBySql("SELECT * FROM x2_relationships WHERE
									(firstType=:name AND firstId=:id AND secondType=:linktype AND secondId=:oldid)
									OR (secondType=:name AND secondId=:id AND firstType=:linktype AND firstId=:oldid)",array(':name'=>$name,':id'=>$model->id,':linktype'=>ucfirst($field->linkType),':oldid'=>$oldRel->id));
							if (isset($lookup)) {
								$lookup->delete();
							}
						}
					}
				}
			} */
			/* if ($model instanceof Actions && $api == 0) {
				if (isset($_GET['redirect']) && $model->associationType != 'none') { // if the action has an association
					if ($model->associationType == 'product' || $model->associationType == 'products')
						$this->redirect(array('/products/products/view', 'id' => $model->associationId));
					//TODO: avoid such hackery
					else if ($model->associationType == 'Campaign')
						$this->redirect(array('/marketing/marketing/view', 'id' => $model->associationId));
					else
						$this->redirect(array('/' . $model->associationType . '/' . $model->associationType . '/view', 'id' => $model->associationId)); // go back to the association
				} else // no association
					$this->redirect(array('/actions/' . $model->id)); // view the action
			} else if ($api == 0) { */

		if($model->save()) {
			if($api == 0)
				$this->redirect(array('view', 'id' => $model->id));
			else
				return true;
		} else {
			return false;
		}
	}

    /**
     * Lists all models.
     */
    public function index($model, $name) {
        $this->render('index', array('model' => $model));
    }

    /**
     * Manages all models.
     * @param $model The model to use admin on, created in a controller subclass.  The model must be constucted with the parameter 'search'
     * @param $name The name of the model being viewed (Opportunities, Actions, etc.)
     */
    public function admin($model, $name) {
        $this->render('admin', array('model' => $model));
    }

    /**
     * Search for a term.  Defined in X2Base so that all Controllers can use, but
     * it makes a call to the SearchController.
     */
    public function actionSearch() {
        $term = $_GET['term'];
        $this->redirect(Yii::app()->request->scriptUrl . '/search/search?term=' . $term);
    }

	/**
	 * DUMMY METHOD: left to avoid breaking old custom modules (now done in X2ChangeLogBehavior)
	 */
    protected function updateChangelog($model, $changes) {
		return $model;
	}

	/**
	 * DUMMY METHOD: left to avoid breaking old custom modules (now done in X2ChangeLogBehavior)
	 */
	protected function calculateChanges($old, $new, &$model = null) {
		return array();
	}

	/**
	 * Sets the lastUpdated and updatedBy fields to reflect recent changes.
	 * @param type $model The model to be updated
	 * @return type $model The model with modified attributes
	 */
/* 	protected function updateChangelog($model, $changes) {
		$model->lastUpdated = time();
		$model->updatedBy = Yii::app()->user->getName();
		$model->save();
		$type = get_class($model);
		if(is_array($changes)){
			foreach($changes as $field=>$array){
				$changelog = new Changelog;
				$changelog->type = $type;
				if (!isset($model->id)) {
					if ($model->save()) {

					}
				}
				$changelog->itemId = $model->id;
				if($model->hasAttribute('name')){
					$changelog->recordName=$model->name;
				}else{
					$changelog->recordName=$type;
				}
				$changelog->changedBy = Yii::app()->user->getName();
				$changelog->fieldName = $field;
				$changelog->oldValue=$array['old'];
				$changelog->newValue=$array['new'];
				$changelog->timestamp = time();

				if ($changelog->save()) {

				}
			}
		}

		if ($changes != 'Create' && $changes != 'Completed' && $changes != 'Edited') {
			if ($changes != "" && !is_array($changes)) {
				$pieces = explode("<br />", $change);
				foreach ($pieces as $piece) {
					$newPieces = explode("TO:", $piece);
					$forDeletion = $newPieces[0];
					if (isset($newPieces[1]) && preg_match('/<b>' . Yii::t('actions', 'color') . '<\/b>/', $piece) == false) {
						$changes[] = $newPieces[1];
					}

                    preg_match_all('/(^|\s|)#(\w\w+)/', $forDeletion, $deleteMatches);
                    $deleteMatches = $deleteMatches[0];
                    foreach ($deleteMatches as $match) {
                        $oldTag = Tags::model()->findByAttributes(array('tag' => substr($match, 1), 'type' => $type, 'itemId' => $model->id));
                        if (isset($oldTag))
                            $oldTag->delete();
                    }
                }
            }
        }else if ($changes == 'Create' || $changes == 'Edited') {
            if ($model instanceof Contacts)
                $change = $model->backgroundInfo;
            else if ($model instanceof Actions)
                $change = $model->actionDescription;
            else if ($model instanceof Docs)
                $change = $model->text;
            else
                $change = $model->name;
        }
        if(is_array($changes)){
            foreach ($changes as $field=>$array) {
                if(is_string($array['new'])){
                    preg_match_all('/(^|\s|)#(\w\w+)/', $array['new'], $matches);
                    $matches = $matches[0];
                }else{
                    $matches=array();
                }
                foreach ($matches as $match) {
                    if(!preg_match('/\&(^|\s|)#(\w\w+);/',$match)){
                        $tag = new Tags;
                        $tag->type = $type;
                        $tag->taggedBy = Yii::app()->user->getName();
                        $tag->type = $type;
                        //cut out leading whitespace
                        $tag->tag = trim($match);
                        if ($model instanceof Contacts)
                            $tag->itemName = $model->firstName . " " . $model->lastName;
                        else if ($model instanceof Actions)
                            $tag->itemName = $model->actionDescription;
                        else if ($model instanceof Docs)
                            $tag->itemName = $model->title;
                        else
                            $tag->itemName = $model->name;
                        if (!isset($model->id)) {
                            $model->save();
                        }
                        $tag->itemId = $model->id;
                        $tag->timestamp = time();
                        //save tags including # sign
                        if ($tag->save()) {

                        }
                    }
                }
            }
        }
        return $model;
    }

    /**
     * Delete all tags associated with a model
     */
    public function cleanUpTags($model) {
        Tags::model()->deleteAllByAttributes(array('itemId' => $model->id));
    }

    /* protected function calculateChanges($old, $new, &$model = null) {
        $arr = array();
        $keys = array_keys($new);
        for ($i = 0; $i < count($keys); $i++) {
            if ($old[$keys[$i]] != $new[$keys[$i]]) {
                $arr[$keys[$i]] = $new[$keys[$i]];
                $allCriteria = Criteria::model()->findAllByAttributes(array('modelType' => $this->modelClass, 'modelField' => $keys[$i]));
                foreach ($allCriteria as $criteria) {
                    if (($criteria->comparisonOperator == "=" && $new[$keys[$i]] == $criteria->modelValue)
                            || ($criteria->comparisonOperator == ">" && $new[$keys[$i]] >= $criteria->modelValue)
                            || ($criteria->comparisonOperator == "<" && $new[$keys[$i]] <= $criteria->modelValue)
                            || ($criteria->comparisonOperator == "change" && $new[$keys[$i]] != $old[$keys[$i]])) {

                        $users = explode(", ", $criteria->users);

                        if ($criteria->type == 'notification') {
                            foreach ($users as $user) {
                                $event=new Events;
                                $event->user=$user;
                                $event->associationType='Notifications';
                                $event->type='notif';

                                $notif = new Notification;
                                $notif->type = 'change';
                                $notif->fieldName = $keys[$i];
                                $notif->modelType = get_class($model);
                                $notif->modelId = $model->id;

                                if ($criteria->comparisonOperator == 'change') {
                                    $notif->comparison = 'change';    // if the criteria is just 'changed'
                                    $notif->value = $new[$keys[$i]];   // record the new value
                                } else {
                                    $notif->comparison = $criteria->comparisonOperator;  // otherwise record the operator type
                                    $notif->value = substr($criteria->modelValue, 0, 250); // and the comparison value
                                }
                                $notif->user = $user;
                                $notif->createdBy = Yii::app()->user->name;
                                $notif->createDate = time();

                                if($notif->save()){
                                    $event->associationId=$notif->id;
                                    $event->save();
                                }
                            }
                        } elseif ($criteria->type == 'action') {
                            $users = explode(", ", $criteria->users);
                            foreach ($users as $user) {
                                $action = new Actions;
                                $action->assignedTo = $user;
                                if ($criteria->comparisonOperator == "=") {
                                    $action->actionDescription = "A record of type " . $this->modelClass . " has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue" . " by " . Yii::app()->user->getName();
                                } else if ($criteria->comparisonOperator == ">") {
                                    $action->actionDescription = "A record of type " . $this->modelClass . " has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue" . " by " . Yii::app()->user->getName();
                                } else if ($criteria->comparisonOperator == "<") {
                                    $action->actionDescription = "A record of type " . $this->modelClass . " has been modified to meet $criteria->modelField $criteria->comparisonOperator $criteria->modelValue" . " by " . Yii::app()->user->getName();
                                } else if ($criteria->comparisonOperator == "change") {
                                    $action->actionDescription = "A record of type " . $this->modelClass . " has had its $criteria->modelField field changed from " . $old[$keys[$i]] . " to " . $new[$keys[$i]] . " by " . Yii::app()->user->getName();
                                }
                                $action->dueDate = mktime('23', '59', '59');
                                $action->createDate = time();
                                $action->lastUpdated = time();
                                $action->updatedBy = 'admin';
                                $action->visibility = 1;
                                $action->associationType = strtolower($this->modelClass);
                                $action->associationId = $new['id'];

                                $action->associationName = $model->name;
                                $action->save();
                            }
                        } elseif ($criteria->type == 'assignment') {
                            $model->assignedTo = $criteria->users;

                            if ($model->save()) {
                                $event=new Events;
                                $event->type='notif';
                                $event->user=$model->assignedTo;
                                $event->associationType='Notifications';

                                $notif = new Notification;
                                $notif->user = $model->assignedTo;
                                $notif->createDate = time();
                                $notif->type = 'assignment';
                                $notif->modelType = $this->modelClass;
                                $notif->modelId = $new['id'];
                                if($notif->save()){
                                    $event->associationId=$notif->id;
                                    $event->save();
                                }
                            }
                        }
                    }
                }
            }
        }
        $changes=array();
        foreach ($arr as $key => $item) {
			if(is_array($old[$key]))
				$old[$key] = implode(', ',$old[$key]);
			$changes[$key]=array('old'=>$old[$key],'new'=>$new[$key]);
		}
		return $changes;
	} */

    public function partialDateRange($input) {
        $datePatterns = array(
            array('/^(0-9)$/', '000-01-01', '999-12-31'),
            array('/^([0-9]{2})$/', '00-01-01', '99-12-31'),
            array('/^([0-9]{3})$/', '0-01-01', '9-12-31'),
            array('/^([0-9]{4})$/', '-01-01', '-12-31'),
            array('/^([0-9]{4})-$/', '01-01', '12-31'),
            array('/^([0-9]{4})-([0-1])$/', '0-01', '9-31'),
            array('/^([0-9]{4})-([0-1][0-9])$/', '-01', '-31'),
            array('/^([0-9]{4})-([0-1][0-9])-$/', '01', '31'),
            array('/^([0-9]{4})-([0-1][0-9])-([0-3])$/', '0', '9'),
            array('/^([0-9]{4})-([0-1][0-9])-([0-3][0-9])$/', '', ''),
        );

        $inputLength = strlen($input);

        $minDateParts = array();
        $maxDateParts = array();

        if ($inputLength > 0 && preg_match($datePatterns[$inputLength - 1][0], $input)) {

            $minDateParts = explode('-', $input . $datePatterns[$inputLength - 1][1]);
            $maxDateParts = explode('-', $input . $datePatterns[$inputLength - 1][2]);

            $minDateParts[1] = max(1, min(12, $minDateParts[1]));
            $minDateParts[2] = max(1, min(cal_days_in_month(CAL_GREGORIAN, $minDateParts[1], $minDateParts[0]), $minDateParts[2]));

            $maxDateParts[1] = max(1, min(12, $maxDateParts[1]));
            $maxDateParts[2] = max(1, min(cal_days_in_month(CAL_GREGORIAN, $maxDateParts[1], $maxDateParts[0]), $maxDateParts[2]));

            $minTimestamp = mktime(0, 0, 0, $minDateParts[1], $minDateParts[2], $minDateParts[0]);
            $maxTimestamp = mktime(23, 59, 59, $maxDateParts[1], $maxDateParts[2], $maxDateParts[0]);

            return array($minTimestamp, $maxTimestamp);
        } else
            return false;
    }

    public function decodeQuotes($str) {
        return preg_replace('/&quot;/u', '"', $str);
    }

    public function encodeQuotes($str) {
        // return htmlspecialchars($str);
        return preg_replace('/"/u', '&quot;', $str);
    }

    public static function cleanUpSessions() {
        $sessions=X2Model::model('Session')->findAllByAttributes(array(),'lastUpdated < :cutoff', array(':cutoff' => time() - Yii::app()->params->admin->timeout));
        foreach($sessions as $session){
            SessionLog::logSession($session->user,$session->id,'passiveTimeout');
            $session->delete();
        }
    }

    public function getPhpMailer() {

        require_once('protected/components/phpMailer/class.phpmailer.php');

        $phpMail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
        $phpMail->CharSet = 'utf-8';

        switch (Yii::app()->params->admin->emailType) {
            case 'sendmail':
                $phpMail->IsSendmail();
                break;
            case 'qmail':
                $phpMail->IsQmail();
                break;
            case 'smtp':
                $phpMail->IsSMTP();

                $phpMail->Host = Yii::app()->params->admin->emailHost;
                $phpMail->Port = Yii::app()->params->admin->emailPort;
                $phpMail->SMTPSecure = Yii::app()->params->admin->emailSecurity;
                if (Yii::app()->params->admin->emailUseAuth == 'admin') {
                    $phpMail->SMTPAuth = true;
                    $phpMail->Username = Yii::app()->params->admin->emailUser;
                    $phpMail->Password = Yii::app()->params->admin->emailPass;
                }
                break;
            case 'mail':
            default:
                $phpMail->IsMail();
        }
        return $phpMail;
    }

    function throwException($message) {
        throw new Exception($message);
    }

    /**
	 *	Send an email from X2CRM
	 *
	 *	@param addresses
	 *	@param $subject the subject for the email
	 *	@param $message the body of the email
	 *	@param $attachments array of attachments to send
	 *	@param $from from and reply to address for the email array(name, address)
	 */
    public function sendUserEmail($addresses, $subject, $message, $attachments = null, $from = null) {
		$eml = new InlineEmail();
		$eml->mailingList = $addresses;
		$eml->subject = $subject;
		$eml->message = $message;
		$eml->attachments = $attachments;
		$eml->from = $from;
		return $eml->deliver();
	}

    public function parseEmailTo($string) {

        if (empty($string))
            return false;
        $mailingList = array();
        $splitString = explode(',', $string);

        require_once('protected/components/phpMailer/class.phpmailer.php');

        foreach ($splitString as &$token) {

            $token = trim($token);
            if (empty($token))
                continue;

            $matches = array();

            if (PHPMailer::ValidateAddress($token)) { // if it's just a simple email, we're done!
                $mailingList[] = array('', $token);
            } else if (preg_match('/^"?([^"]*)"?\s*<(.+)>$/i', $token, $matches)) {
                if (count($matches) == 3 && PHPMailer::ValidateAddress($matches[2]))
                    $mailingList[] = array($matches[1], $matches[2]);
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

        if (count($mailingList) < 1)
            return false;

        return $mailingList;
    }

    public function mailingListToString($list, $encodeQuotes = false) {
        $string = '';
        if (is_array($list)) {
            foreach ($list as &$value) {
                if (!empty($value[0]))
                    $string .= '"' . $value[0] . '" <' . $value[1] . '>, ';
                else
                    $string .= $value[1] . ', ';
            }
        }
        return $encodeQuotes ? $this->encodeQuotes($string) : $string;
    }

    /**
     * Obtain the widget list for the current web user.
     *
     * @param CFilterChain $filterChain
     */
    public function filterSetPortlets($filterChain) {
		if (!Yii::app()->user->isGuest) {
			$themeURL = Yii::app()->theme->getBaseUrl();

			if ($this->action->id != 'webLead' && $this->action->id != 'login' && $this->action->id != 'googleLogin')
				Yii::app()->clientScript->registerScript('logos', 'var _0xa525=["\x6C\x65\x6E\x67\x74\x68","\x23\x6D\x61\x69\x6E\x2D\x6D\x65\x6E\x75\x2D\x69\x63\x6F\x6E","\x23\x78\x32\x74\x6F\x75\x63\x68\x2D\x6C\x6F\x67\x6F","\x23\x78\x32\x63\x72\x6D\x2D\x6C\x6F\x67\x6F","\x68\x72\x65\x66","\x72\x65\x6D\x6F\x76\x65\x41\x74\x74\x72","\x61","\x50\x6C\x65\x61\x73\x65\x20\x70\x75\x74\x20\x74\x68\x65\x20\x6C\x6F\x67\x6F\x20\x62\x61\x63\x6B","\x73\x72\x63","\x61\x74\x74\x72","\x2F\x69\x6D\x61\x67\x65\x73\x2F\x78\x32\x66\x6F\x6F\x74\x65\x72\x2E\x70\x6E\x67","\x2F\x69\x6D\x61\x67\x65\x73\x2F\x78\x32\x74\x6F\x75\x63\x68\x2E\x70\x6E\x67","\x6C\x6F\x61\x64"];$(window)[_0xa525[12]](function (){if((!$(_0xa525[1])[_0xa525[0]])||(!$(_0xa525[2])[_0xa525[0]])||(!$(_0xa525[3])[_0xa525[0]])){$(_0xa525[6])[_0xa525[5]](_0xa525[4]);alert(_0xa525[7]);} ;var _0x3addx1=$(_0xa525[2])[_0xa525[9]](_0xa525[8]);var _0x3addx2=$(_0xa525[3])[_0xa525[9]](_0xa525[8]);if(_0x3addx2!=("$themeURL"+_0xa525[10])||_0x3addx1!=("$themeURL"+_0xa525[11])){$(_0xa525[6])[_0xa525[5]](_0xa525[4]);alert(_0xa525[7]);} ;} );');
			$this->portlets = Profile::getWidgets();
			// foreach($widgets as $key=>$value) {
			// $options = ProfileChild::parseWidget($value,$key);
			// $this->portlets[$key] = $options;
			// }
		}
        $filterChain->run();
    }


    // This function needs to be made in your extensions of the class with similar code.
    // Replace "Opportunities" with the Model being used.
    /*     * public function loadModel($id)
      {
      $model=Opportunity::model()->findByPk((int)$id);
      if($model===null)
      throw new CHttpException(404,'The requested page does not exist.');
      return $model;
      } */

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax'])) {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }


    function formatMenu($array, $params = array()) {
        $auth = Yii::app()->authManager;
        foreach ($array as &$item) {
            if (isset($item['url']) && $item['url'] != '#') {
                $url = $item['url'][0];
                if (preg_match('/\//', $url)) {
                    $pieces = explode('/', $url);
                    $action = "";
                    foreach ($pieces as $piece) {
                        $action.=ucfirst($piece);
                    }
                } else {
                    $action = ucfirst($this->getId() . ucfirst($item['url'][0]));
                }
                $authItem = $auth->getAuthItem($action);
				if(!isset($item['visible']) || $item['visible'] == true)
					$item['visible'] = Yii::app()->user->checkAccess($action, $params) || is_null($authItem);
            } else {
                if (isset($item['linkOptions']['submit'])) {
                    $action = ucfirst($this->getId() . ucfirst($item['linkOptions']['submit'][0]));
                    $authItem = $auth->getAuthItem($action);
                    $item['visible'] = Yii::app()->user->checkAccess($this->getId() . ucfirst($item['linkOptions']['submit'][0]), $params) || is_null($authItem);
                }
            }
        }
        return $array;
    }

    function Array_Search_Preg($find, $in_array, $keys_found = Array()) {
        if (is_array($in_array)) {
            foreach ($in_array as $key => $val) {
                if (is_array($val))
                    $this->Array_Search_Preg($find, $val, $keys_found);
                else {
                    if (preg_match('/' . $find . '/', $val))
                        $keys_found[] = $key;
                }
            }
            return $keys_found;
        }
        return false;
    }

    public function getDateRange() {

        $dateRange = array();
        $dateRange['strict'] = false;
        if (isset($_GET['strict']) && $_GET['strict'])
            $dateRange['strict'] = true;

        $dateRange['range'] = 'custom';
        if (isset($_GET['range']))
            $dateRange['range'] = $_GET['range'];

        switch ($dateRange['range']) {

            case 'thisWeek':
                $dateRange['start'] = strtotime('mon this week'); // first of this month
                $dateRange['end'] = time(); // now
                break;
            case 'thisMonth':
                $dateRange['start'] = mktime(0, 0, 0, date('n'), 1); // first of this month
                $dateRange['end'] = time(); // now
                break;
            case 'lastWeek':
                $dateRange['start'] = strtotime('mon last week'); // first of last month
                $dateRange['end'] = strtotime('mon this week') - 1;  // first of this month
                break;
            case 'lastMonth':
                $dateRange['start'] = mktime(0, 0, 0, date('n') - 1, 1); // first of last month
                $dateRange['end'] = mktime(0, 0, 0, date('n'), 1) - 1;  // first of this month
                break;
            case 'thisYear':
                $dateRange['start'] = mktime(0, 0, 0, 1, 1);  // first of the year
                $dateRange['end'] = time(); // now
                break;
            case 'lastYear':
                $dateRange['start'] = mktime(0, 0, 0, 1, 1, date('Y') - 1);  // first of last year
                $dateRange['end'] = mktime(0, 0, 0, 1, 1, date('Y')) - 1;   // first of this year
                break;
            case 'all':
                $dateRange['start'] = 0;        // every record
                $dateRange['end'] = time();
                if (isset($_GET['end'])) {
                    $dateRange['end'] = Formatter::parseDate($_GET['end']);
                    if ($dateRange['end'] == false)
                        $dateRange['end'] = time();
                    else
                        $dateRange['end'] = strtotime('23:59:59', $dateRange['end']);
                }
                break;

            case 'custom':
            default:
                $dateRange['end'] = time();
                if (isset($_GET['end'])) {
                    $dateRange['end'] = Formatter::parseDate($_GET['end']);
                    if ($dateRange['end'] == false)
                        $dateRange['end'] = time();
                    else
                        $dateRange['end'] = strtotime('23:59:59', $dateRange['end']);
                }

                $dateRange['start'] = strtotime('1 month ago', $dateRange['end']);
                if (isset($_GET['start'])) {
                    $dateRange['start'] = Formatter::parseDate($_GET['start']);
                    if ($dateRange['start'] == false)
                        $dateRange['start'] = strtotime('-30 days 0:00', $dateRange['end']);
                    else
                        $dateRange['start'] = strtotime('0:00', $dateRange['start']);
                }
        }
        return $dateRange;
    }

    function ucwords_specific ($string, $delimiters = '', $encoding = NULL)
    {

        if ($encoding === NULL) { $encoding = mb_internal_encoding();}

        if (is_string($delimiters))
        {
            $delimiters =  str_split( str_replace(' ', '', $delimiters));
        }

        $delimiters_pattern1 = array();
        $delimiters_replace1 = array();
        $delimiters_pattern2 = array();
        $delimiters_replace2 = array();
        foreach ($delimiters as $delimiter)
        {
            $ucDelimiter=$delimiter;
            $delimiter=strtolower($delimiter);
            $uniqid = uniqid();
            $delimiters_pattern1[]   = '/'. preg_quote($delimiter) .'/';
            $delimiters_replace1[]   = $delimiter.$uniqid.' ';
            $delimiters_pattern2[]   = '/'. preg_quote($ucDelimiter.$uniqid.' ') .'/';
            $delimiters_replace2[]   = $ucDelimiter;
            $delimiters_cleanup_replace1[]   = '/'. preg_quote($delimiter.$uniqid).' ' .'/';
            $delimiters_cleanup_pattern1[]   = $delimiter;
        }
        $return_string = mb_strtolower($string, $encoding);
        //$return_string = $string;
        $return_string = preg_replace($delimiters_pattern1, $delimiters_replace1, $return_string);

        $words = explode(' ', $return_string);

        foreach ($words as $index => $word)
        {
            $words[$index] = mb_strtoupper(mb_substr($word, 0, 1, $encoding), $encoding).mb_substr($word, 1, mb_strlen($word, $encoding), $encoding);
        }
        $return_string = implode(' ', $words);

        $return_string = preg_replace($delimiters_pattern2, $delimiters_replace2, $return_string);
        $return_string = preg_replace($delimiters_cleanup_replace1, $delimiters_cleanup_pattern1, $return_string);

        return $return_string;
    }
}
