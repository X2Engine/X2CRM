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

Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_actions".
 * @package X2CRM.modules.actions.models
 */
class Actions extends X2Model {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Actions the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_actions';
	}

	public function behaviors() {
        return array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'module'=>'actions'
			),
			'X2TimestampBehavior' => array('class'=>'X2TimestampBehavior'),
			'tags' => array('class'=>'TagBehavior'),
            'ERememberFiltersBehavior' => array(
				'class'=>'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			),
		);
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return array(
			array('allDay','boolean'),
			array('createDate, completeDate, lastUpdated', 'numerical', 'integerOnly'=>true),
			array('id,assignedTo,actionDescription,visibility,associationId,associationType,associationName,dueDate,
				priority,type,createDate,complete,reminder,completedBy,completeDate,lastUpdated,updatedBy,color','safe')
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array_merge(parent::relations(),array(
			'workflow'=>array(self::BELONGS_TO, 'Workflow', 'workflowId'),
            'actionText'=>array(self::HAS_ONE, 'ActionText', 'actionId'),
		));
	}

	/**
	 * Fixes up record association, parses dates (since this doesn't use {@link X2Model::setX2Fields()})
	 * @return boolean whether or not to save
	 */
	public function beforeSave() {
		if($this->scenario !== 'workflow') {
			$association = self::getAssociationModel($this->associationType, $this->associationId);

			if($association === null) {
				$this->associationName = 'None';
				$this->associationId = 0;
			} else {
				if($association->hasAttribute('name'))
					$this->associationName = $association->name;
				$association->updateLastActivity();
			}

			if($this->associationName == 'None' && $this->associationType != 'none')
				$this->associationName = ucfirst($this->associationType);

			$this->dueDate = Formatter::parseDateTime($this->dueDate);
			$this->completeDate = Formatter::parseDateTime($this->completeDate);
		}
		return parent::beforeSave();
	}

	/**
	 * Creates an action reminder event.
	 * Fires the onAfterCreate event in {@link X2Model::afterCreate}
	 */
	public function afterCreate() {
		if(empty($this->type) && $this->complete !== 'Yes' && ($this->reminder==1 || $this->reminder=='Yes')) {
			$event = new Events;
			$event->timestamp = $this->dueDate;
			$event->visibility = $this->visibility;
			$event->type = 'action_reminder';
			$event->associationType = 'Actions';
			$event->associationId = $this->id;
			$event->user = $this->assignedTo;
			$event->save();
		}
		parent::afterCreate();
	}

	/**
	 * Deletes the action reminder event, if any
	 * Fires the onAfterDelete event in {@link X2Model::afterDelete}
	 */
	public function afterDelete() {
		X2Model::model('Events')->deleteAllByAttributes(array('associationType'=>'Actions','associationId'=>$this->id,'type'=>'action_reminder'));
		parent::afterDelete();
	}

    public function setActionDescription($value){
        if(is_null($this->actionText) || X2Model::model('ActionText')->updateByPk($this->id,array('text'=>$value))==0 && count(X2Model::model('ActionText')->findByPk($this->id))==0){
            $actionText=new ActionText;
            if(empty($this->id)){
                $this->save();
            }
            $actionText->actionId=$this->id;
            $actionText->text=$value;
            $actionText->save();
        }
    }

    public function getActionDescription(){
        if(isset($this->actionText))
            return $this->actionText->text;
        else
            return "";
    }

	/**
	 * return an array of possible colors for an action
	 */
	public static function getColors() {
		return array(
		    'Green'=>Yii::t('actions', 'Green'),
		    '#3366CC'=>Yii::t('actions', 'Blue'),
		    'Red'=>Yii::t('actions', 'Red'),
		    'Orange'=>Yii::t('actions', 'Orange'),
		    'Black'=>Yii::t('actions', 'Black'),
		);
	}

	/**
	 * Marks the action complete and updates the record.
	 * @param string $completedBy the user completing the action (defaults to currently logged in user)
	 * @return boolean whether or not the action updated successfully
	 */
	public function complete($completedBy=null, $notes=null) {
		if($completedBy === null){
			$completedBy = Yii::app()->user->getName();
        }
        if(!is_null($notes)){
            $this->actionDescription.="\n\n".$notes;
        }

		$this->complete = 'Yes';
		$this->completedBy = $completedBy;
		$this->completeDate = time();

		$this->disableBehavior('changelog');

		if($result = $this->update()) {

			X2Flow::trigger('ActionCompleteTrigger',array(
				'model'=>$this,
				'user'=>$completedBy
			));

			// delete the action reminder event
			X2Model::model('Events')->deleteAllByAttributes(array('associationType'=>'Actions','associationId'=>$this->id,'type'=>'action_reminder'),'timestamp > NOW()');

			$event = new Events;
			$event->type = 'action_complete';
			$event->visibility = $this->visibility;
			$event->associationType = 'Actions';
			$event->user=Yii::app()->user->getName();
			$event->associationId = $this->id;

			// notify the admin
			if($event->save() && Yii::app()->user->getName() !== 'admin') {
				$notif = new Notification;
				$notif->type = 'action_complete';
				$notif->modelType = 'Actions';
				$notif->modelId = $this->id;
				$notif->user = 'admin';
				$notif->createdBy = $completedBy;
				$notif->createDate = time();
				$notif->save();
			}
		}
		$this->enableBehavior('changelog');

		return $result;
	}

	/**
	 * Marks the action incomplete and updates the record.
	 * @return boolean whether or not the action updated successfully
	 */
	public function uncomplete() {
		$this->complete = 'No';
		$this->completedBy = null;
		$this->completeDate = null;

		$this->disableBehavior('changelog');

		if($result = $this->update()) {
			X2Flow::trigger('ActionUncompleteTrigger',array(
				'model'=>$this,
				'user'=>Yii::app()->user->getName()
			));
		}
		$this->enableBehavior('changelog');

		return $result;
	}

	public function getName() {
        if(!empty($this->subject)){
            return $this->subject;
        }else{
            return Formatter::truncateText($this->actionDescription,40);
        }
	}

	public function getLink($length = 30) {

		$text = $this->name;
        $pieces = explode("\n", $text);
        $text=$pieces[0];
		if($length && strlen($text) > $length)
			$text = CHtml::encode(mb_substr($text,0,$length,'UTF-8').'...');
		return CHtml::link($text,'#',array('class'=>'action-frame-link','data-action-id'=>$this->id));
	}

	public function getAssociationLink() {
		$model = self::getAssociationModel($this->associationType, $this->associationId);
		if($model !== null)
			return $model->getLink();
		return false;
	}

	public static function parseStatus($dueDate) {

		if (empty($dueDate))	// there is no due date
			return false;
		if (!is_numeric($dueDate))
			$dueDate = strtotime($dueDate);	// make sure $date is a proper timestamp

		//$due = getDate($dueDate);
		//$dueDate = mktime(23,59,59,$due['mon'],$due['mday'],$due['year']); // if there is no time, give them until 11:59 PM to finish the action

		//$dueDate += 86399;

		$timeLeft = $dueDate - time();	// calculate how long till due date
		if ($timeLeft < 0)
			return "<span class='overdue'>".Formatter::formatDueDate($dueDate)."</span>";	// overdue by X hours/etc

		else
			return Formatter::formatDueDate($dueDate);
	}

	public static function formatTimeLength($seconds) {
		$seconds = abs($seconds);
		if($seconds < 60)
			return Yii::t('app','{n} second|{n} seconds',$seconds);	// less than 1 min
		if($seconds < 3600)
			return Yii::t('app','{n} minute|{n} minutes',floor($seconds/60));	// minutes (less than an hour)
		if($seconds < 86400)
			return Yii::t('app','{n} hour|{n} hours',floor($seconds/3600));	// hours (less than a day)
		if($seconds < 5184000)
			return Yii::t('app','{n} day|{n} days',floor($seconds/86400));	// days (less than 60 days)
		else
			return Yii::t('app','{n} month|{n} months',floor($seconds/2592000));	// months (more than 90 days)
	}

	// finds record for the "owner" of a action, using the owner type and ID
	public static function getOwnerModel($ownerType,$ownerId) {
		if(!(empty($ownerType) || empty($ownerId)) && X2Model::getModelName($ownerType)) {	// both ID and type must be set
			return X2Model::model(X2Model::getModelName($ownerType))->findByPk($ownerId);

			// if($ownerType=='projects')
				// return X2Model::model('ProjectChild')->findByPk($ownerId);
			// if($ownerType=='contacts')
				// return X2Model::model('Contacts')->findByPk($ownerId);
			// if($ownerType=='accounts')
				// return X2Model::model('Accounts')->findByPk($ownerId);
			// if($ownerType=='cases')
				// return X2Model::model('CaseChild')->findByPk($ownerId);
			// if($ownerType=='opportunities')
				// return X2Model::model('Opportunity')->findByPk($ownerId);
		}
		return null;	// either the type is unkown, or there simply is no owner
	}

	// creates virtual attribute for owner's name, if exists
	public function getOwnerName() {
		$ownerModel = Actions::getOwnerModel($this->ownerType,$this->ownerId);
		if($ownerModel !== null)
			return $ownerModel->name;	// get name of owner
		else
			return false;
	}

    public static function createCondition($filters){
        Yii::app()->params->profile->actionFilters=json_encode($filters);
        Yii::app()->params->profile->update(array('actionFilters'));
        $criteria=X2Model::model('Actions')->getAccessCriteria();
        $criteria->addCondition("(type !='workflow' AND type!='email' AND type!='event' AND type!='emailFrom' AND type!='attachment' AND type!='webactivity' AND type!='quotes' AND type!='emailOpened' AND type!='note') OR type IS NULL");
        if(isset($filters['complete'],$filters['assignedTo'],$filters['dateType'],$filters['dateRange'],$filters['order'],$filters['orderType'])){
            switch($filters['complete']){
                    case "No":
                        $criteria->addCondition("complete='No' OR complete IS NULL");
                        break;
                    case "Yes":
                        $criteria->addCondition("complete='Yes'");
                        break;
                    case 'all':
                        break;
                }
                switch($filters['assignedTo']){
                    case 'me':
                       $criteria->addCondition("assignedTo='".Yii::app()->user->getName()."'");
                        break;
                    case 'both':
                        $criteria->addCondition("assignedTo='".Yii::app()->user->getName()."' OR assignedTo='Anyone' OR assignedTo=''");
                        break;
                }
                switch($filters['dateType']){
                    case 'due':
                        $dateField='dueDate';
                        break;
                    case 'create':
                        $dateField='createDate';
                }
                switch($filters['dateRange']){
                    case 'today':
                        if($dateField=='dueDate'){
                            $criteria->addCondition("IFNULL(dueDate, createDate) <= ".strtotime('today 11:59 PM'));
                        }else{
                            $criteria->addCondition("$dateField >= ".strtotime('today')." AND $dateField <= ".strtotime('today 11:59 PM'));
                        }
                        break;
                    case 'tomorrow':
                        if($dateField=='dueDate'){
                            $criteria->addCondition("IFNULL(dueDate, createDate) <= ".strtotime("tomorrow 11:59 PM"));
                        }else{
                            $criteria->addCondition("$dateField >= ".strtotime('tomorrow')." AND $dateField <= ".strtotime("tomorrow 11:59 PM"));
                        }
                        break;
                    case 'week':
                        if($dateField=='dueDate'){
                            $criteria->addCondition("IFNULL(dueDate, createDate) <= ".strtotime("Sunday 11:59 PM"));
                        }else{
                            $criteria->addCondition("$dateField >= ".strtotime('Monday')." AND $dateField <= ".strtotime("Sunday 11:59 PM"));
                        }
                        break;
                    case 'month':
                        if($dateField=='dueDate'){
                            $criteria->addCondition("IFNULL(dueDate, createDate) <= ".strtotime("last day of this month 11:59 PM"));
                        }else{
                            $criteria->addCondition("$dateField >= ".strtotime('first day of this month')." AND $dateField <= ".strtotime("last day of this month 11:59 PM"));
                        }
                        break;
                    case 'range':
                        if(!empty($filters['start']) && !empty($filters['end'])){
                            if($dateField=='dueDate'){
                                $criteria->addCondition("IFNULL(dueDate, createDate) >= ".strtotime($filters['start'])." AND IFNULL(dueDate, createDate) <= ".strtotime($filters['end'].' 11:59 PM'));
                            }else{
                                $criteria->addCondition("$dateField >= ".strtotime($filters['start'])." AND $dateField <= ".strtotime($filters['end']));
                            }
                        }
                        break;
                }
                switch($filters['order']){
                    case 'due':
                        $orderField="IFNULL(dueDate, createDate)";
                        break;
                    case 'create':
                        $orderField='createDate';
                        break;
                    case 'priority':
                        $orderField='priority';
                        break;
                }
                switch($filters['orderType']){
                    case 'desc':
                        $criteria->order="$orderField DESC";
                        break;
                    case 'asc':
                        $criteria->order="$orderField ASC";
                        break;
                }
            }
            return $criteria;
    }


	public function search($criteria=null) {
        if(!$criteria instanceof CDbCriteria){
            $criteria=$this->getAccessCriteria();
            $criteria->addCondition('(type != "workflow" AND type!="email" AND type!="event" AND type!="emailFrom") OR type IS NULL');
            $criteria->addCondition("assignedTo='".Yii::app()->user->getName()."' AND complete!='Yes' AND IFNULL(dueDate, createDate) <= '".strtotime('today 11:59 PM')."'");
        }
		return $this->searchBase($criteria);
	}
    /*
	public function searchComplete() {
		$criteria=new CDbCriteria;
        if(!Yii::app()->user->checkAccess('ActionsAdmin')){
            $parameters=array("condition"=>"completedBy='".Yii::app()->user->getName()."' AND complete='Yes'","limit"=>ceil(ProfileChild::getResultsPerPage()/2));
            $criteria->scopes=array('findAll'=>array($parameters));
        }
		return $this->searchBase($criteria);
	}

	public function searchAll() {
		$criteria=new CDbCriteria;
        $parameters=array("condition"=>"(assignedTo='".Yii::app()->user->getName()."' OR assignedTo IN (SELECT groupId FROM x2_group_to_user WHERE userId='".Yii::app()->user->getId()."'))",'limit'=>ceil(ProfileChild::getResultsPerPage()/2));
        $criteria->scopes=array('findAll'=>array($parameters));
		return $this->searchBase($criteria);
	}

	public function searchGroup() {
		$criteria=new CDbCriteria;
        if(!Yii::app()->user->checkAccess('ActionsAdmin')){
            $parameters=array("condition"=>"(visibility='1' OR assignedTo='".Yii::app()->user->getName()."' OR assignedTo IN (SELECT groupId FROM x2_group_to_user WHERE userId='".Yii::app()->user->getId()."')) AND complete!='Yes'",'limit'=>ceil(ProfileChild::getResultsPerPage()/2));
            $criteria->scopes=array('findAll'=>array($parameters));
        }
		return $this->searchBase($criteria);
	}

	public function searchAllGroup() {
		$criteria=new CDbCriteria;
        if(!Yii::app()->user->checkAccess('ActionsAdmin')){
            $parameters=array("condition"=>"(visibility='1' OR assignedTo='".Yii::app()->user->getName()."' OR assignedTo IN (SELECT groupId FROM x2_group_to_user WHERE userId='".Yii::app()->user->getId()."'))",'limit'=>ceil(ProfileChild::getResultsPerPage()/2));
            $criteria->scopes=array('findAll'=>array($parameters));
        }
		return $this->searchBase($criteria);
	}

	public function searchAllComplete() {
		$criteria=new CDbCriteria;
        if(!Yii::app()->user->checkAccess('ActionsAdmin')){
            $parameters=array("condition"=>"(visibility='1' OR assignedTo='".Yii::app()->user->getName()."' OR assignedTo IN (SELECT groupId FROM x2_group_to_user WHERE userId='".Yii::app()->user->getId()."')) AND complete='Yes'",'limit'=>ceil(ProfileChild::getResultsPerPage()/2));
            $criteria->scopes=array('findAll'=>array($parameters));
        }
		return $this->searchBase($criteria);
	}*/

	public function searchAdmin() {
		$criteria=new CDbCriteria;

		return $this->searchBase($criteria);
	}

	public function searchBase($criteria) {

		$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Actions'));
		foreach($fields as $field){
			$fieldName=$field->fieldName;
			switch($field->type){
				case 'boolean':
					$criteria->compare($field->fieldName,$this->compareBoolean($this->$fieldName), true);
					break;
				case 'link':
					$criteria->compare($field->fieldName,$this->compareLookup($field, $this->$fieldName), true);
					break;
				case 'assignment':
					$criteria->compare($field->fieldName,$this->compareAssignment($this->$fieldName), true);
					break;
				default:
					$criteria->compare($field->fieldName,$this->$fieldName,true);
			}

		}

        if(!empty($criteria->order)){
            $criteria->order=$order="sticky DESC, ".$criteria->order;
        }else{
            $order='sticky DESC, IF(complete="No", IFNULL(dueDate, IFNULL(createDate,0)), GREATEST(createDate, IFNULL(completeDate,0), IFNULL(lastUpdated,0))) DESC';
        }
		$dataProvider=new CActiveDataProvider('Actions', array(
			'sort'=>array(
				'defaultOrder'=>$order,
			),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage()
			),
			'criteria'=>$criteria,
		));

		return $dataProvider;
	}
	protected function compareLookup($field, $data){
		if(is_null($data) || $data=="") return null;
		$type=ucfirst($field->linkType);
		if($type=='Contacts'){
			eval("\$lookupModel=$type::model()->findAllBySql('SELECT * FROM x2_$field->linkType WHERE CONCAT(firstName,\' \', lastName) LIKE \'%$data%\'');");
		}else{
			eval("\$lookupModel=$type::model()->findAllBySql('SELECT * FROM x2_$field->linkType WHERE name LIKE \'%$data%\'');");
		}
		if(isset($lookupModel) && count($lookupModel)>0){
			$arr=array();
			foreach($lookupModel as $model){
				$arr[]=$model->id;
			}
			return $arr;
		}else
			return -1;
	}

	protected function compareBoolean($data){
		if(is_null($data) || $data=='') return null;
		if(is_numeric($data)) return $data;
		if($data==Yii::t('actions',"Yes"))
			return 1;
		elseif($data==Yii::t('actions',"No"))
			return 0;
		else
			return -1;
	}

	protected function compareAssignment($data){
		if(is_null($data)) return null;
		if(is_numeric($data)){
			$models=Groups::model()->findAllBySql("SELECT * FROM x2_groups WHERE name LIKE '%$data%'");
			$arr=array();
			foreach($models as $model){
				$arr[]=$model->id;
			}
			return count($arr)>0?$arr:-1;
		}else{
			$models=User::model()->findAllBySql("SELECT * FROM x2_users WHERE CONCAT(firstName,' ',lastName) LIKE '%$data%'");
			$arr=array();
			foreach($models as $model){
				$arr[]=$model->username;
			}
			return count($arr)>0?$arr:-1;
		}
	}

	public function syncGoogleCalendar($operation) {
		$profiles = array();

		if(!is_numeric($this->assignedTo)) {	// assigned to user
			$profiles[] = X2Model::model('Profile')->findByAttributes(array('username'=>$this->assignedTo));
		} else {	// Assigned to group
			$groups = Yii::app()->db->createCommand()
				->select('userId')
				->from('x2_group_to_user')
				->where('groupId=:assignedTo',array(':assignedTo'=>$this->assignedTo))
				->queryAll();
			foreach($groups as $group)
				$profile[] = X2Model::model('Profile')->findByPk($group['userId']);
		}

		foreach($profiles as &$profile) {
			if($profile !== null) {
				if($operation === 'create')
					$profile->syncActionToGoogleCalendar($this);	// create action to Google Calendar
				elseif($operation === 'update')
					$profile->deleteGoogleCalendarEvent($this);	// update action to Google Calendar
				elseif($operation === 'delete')
					$profile->updateGoogleCalendarEvent($this); // delete action in Google Calendar
			}
		}
	}

    function truncateText($str, $length = 30) {

        if (strlen($str) > $length - 3) {
            if ($length < 3)
                $str = '';
            else
                $str = substr($str, 0, $length - 3);
            $str .= '...';
        }
        return $str;
    }
}
