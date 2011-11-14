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

class ActionChild extends Actions {

	public static function completeAction($id) {
		$action=ActionChild::model()->findByPk($id);
		$action->complete="Yes";
		$action->completedBy=Yii::app()->user->getName();
		$action->completeDate = time();
		$action->update();
	}

	public function behaviors() {
		return array(
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),				/* optional line */
				'defaultStickOnClear'=>false		/* optional line */
			),
		);
	}

	public function parseStatus($dueDate) {

		if (empty($dueDate))	// there is no due date
			return false;
		if (!is_numeric($dueDate))
			$dueDate = strtotime($dueDate);	// make sure $date is a proper timestamp

		//$due = getDate($dueDate);
		//$dueDate = mktime(23,59,59,$due['mon'],$due['mday'],$due['year']); // if there is no time, give them until 11:59 PM to finish the action
		
		//$dueDate += 86399;	
	
		$timeLeft = $dueDate - time();	// calculate how long till due date
		if ($timeLeft < 0)
			return Yii::t('actions','Overdue {time}',array('{time}'=>ActionChild::formatDate($dueDate)));	// overdue by X hours/etc

		else
			return Yii::t('actions','Due {date}',array('{date}'=>ActionChild::formatDate($dueDate)));
	}
	
	public static function formatDate($date) {

		if (!is_numeric($date))
			$date = strtotime($date);	// make sure $date is a proper timestamp

		$now = getDate();			// generate date arrays
		$due = getDate($date);	// for calculations
		//$date = mktime(23,59,59,$due['mon'],$due['mday'],$due['year']);	// give them until 11:59 PM to finish the action
		//$due = getDate($date);
	
		if ($due['year'] == $now['year']) {		// is the due date this year?
			if ($due['yday'] == $now['yday'])		// is the due date today?
				return Yii::t('app','Today');
			else if ($due['yday'] == $now['yday']+1)	// is it tomorrow?
				return Yii::t('app','Tomorrow');
			else 
				return Yii::app()->dateFormatter->format('MMM d',$date);	// any other day this year
		} else {
			return Yii::app()->dateFormatter->format('MMM d, yyyy',$date);	// due date is after this year
		}
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

		if(!(empty($ownerType) || empty($ownerId))) {	// both ID and type must be set
			if($ownerType=='projects')
				return CActiveRecord::model('ProjectChild')->findByPk($ownerId);
			if($ownerType=='contacts')
				return CActiveRecord::model('ContactChild')->findByPk($ownerId);
			if($ownerType=='accounts')
				return CActiveRecord::model('AccountChild')->findByPk($ownerId);
			if($ownerType=='cases')
				return CActiveRecord::model('CaseChild')->findByPk($ownerId);
			if($ownerType=='sales')
				return CActiveRecord::model('SaleChild')->findByPk($ownerId);
		}
		return false;	// either the type is unkown, or there simply is no owner
	}
	
	// creates virtual attribute for owner's name, if exists
	public function getOwnerName() {
		$ownerModel = ActionChild::getOwnerModel($this->ownerType,$this->ownerId);
		if ($ownerModel)
			return $ownerModel->name;	// get name of owner
		else
			return false;
	}
	
	
	public function search() {
		$criteria=new CDbCriteria;
		$parameters=array('condition'=>"(assignedTo='Anyone' OR assignedTo='".Yii::app()->user->getName()."') AND complete!='Yes' AND dueDate <= '".mktime(23,59,59)."'",'limit'=>ceil(ProfileChild::getResultsPerPage()/2));
		$criteria->scopes=array('findAll'=>array($parameters));
		
		return $this->searchBase($criteria);
	}

	public function searchComplete() {
		$criteria=new CDbCriteria;
		$parameters=array("condition"=>"(assignedTo='Anyone' OR assignedTo='".Yii::app()->user->getName()."') AND complete='Yes'","limit"=>ceil(ProfileChild::getResultsPerPage()/2));
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}

	public function searchAll() {
		$criteria=new CDbCriteria;
		$parameters=array("condition"=>"assignedTo='".Yii::app()->user->getName()."' AND complete!='Yes'",'limit'=>ceil(ProfileChild::getResultsPerPage()/2));
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}
	
	public function searchGroup() {
		$criteria=new CDbCriteria;
		$parameters=array("condition"=>"visibility='1' AND complete!='Yes'",'limit'=>ceil(ProfileChild::getResultsPerPage()/2));
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}

	public function searchAllComplete() {
		$criteria=new CDbCriteria;
		$parameters=array("condition"=>"visibility='1' AND complete='Yes'","limit"=>ceil(ProfileChild::getResultsPerPage()/2));
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}

	public function searchAdmin() {
		$criteria=new CDbCriteria;

		return $this->searchBase($criteria);
	}
	
	private function searchBase($criteria) {
		
		//$criteria->compare('id',$this->id);
		$criteria->compare('assignedTo',$this->assignedTo,true);
		//$criteria->compare('actionDescription',$this->actionDescription,true);
		//$criteria->compare('visibility',$this->visibility);
		//$criteria->compare('associationId',$this->associationId);
		//$criteria->compare('associationType',$this->associationType,true);
		$criteria->compare('associationName',$this->associationName,true);
		// $criteria->compare('dueDate',$this->dueDate,true);
		$criteria->compare('priority',$this->priority,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('createDate',$this->createDate,true);
		$criteria->compare('complete',$this->complete,true);
		// $criteria->compare('reminder',$this->reminder,true);
		// $criteria->compare('completedBy',$this->completedBy,true);
		// $criteria->compare('completeDate',$this->completeDate,true);

		$dateRange = Yii::app()->controller->partialDateRange($this->dueDate);
		if($dateRange !== false)
			$criteria->addCondition('dueDate BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);
		
		$dateRange = Yii::app()->controller->partialDateRange($this->createDate);
		if($dateRange !== false)
			$criteria->addCondition('createDate BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);
			
		$dateRange = Yii::app()->controller->partialDateRange($this->completeDate);
		if($dateRange !== false)
			$criteria->addCondition('completeDate BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);
		
		
		$dataProvider=new SmartDataProvider('Actions', array(
			'sort'=>array(
				'defaultOrder'=>'completeDate DESC, dueDate DESC',
			),
			'pagination'=>array(
				'pageSize'=>ceil(ProfileChild::getResultsPerPage()/2)
			),
			'criteria'=>$criteria
		));
	
		return $dataProvider;
	}
	
	
	public function attributeLabels() {
		return array(
			'id' => Yii::t('actions','ID'),
			'assignedTo' => Yii::t('actions','Assigned To'),
			'actionDescription' => Yii::t('actions','Description'),
			'visibility' => Yii::t('actions','Visibility'),
			'associationId' => Yii::t('actions','Contact'),
			'associationType' => Yii::t('actions','Association Type'),
			'associationName' => Yii::t('actions','Association'),
			'dueDate' => Yii::t('actions','Due Date'),
			'priority' => Yii::t('actions','Priority'),
			'type' => Yii::t('actions','Action Type'),
			'createDate' => Yii::t('actions','Create Date'),
			'complete' => Yii::t('actions','Complete'),
			'reminder' => Yii::t('actions','Reminder'),
			'completedBy' => Yii::t('actions','Completed By'),
			'completeDate' => Yii::t('actions','Date Completed'),
			'lastUpdated' => Yii::t('actions','Last Updated'),
			'updatedBy' => Yii::t('actions','Updated By')
		);
	}
}
