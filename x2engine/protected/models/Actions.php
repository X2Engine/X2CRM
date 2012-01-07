<?php

/**
 * This is the model class for table "x2_actions".
 *
 * The followings are the available columns in table 'x2_actions':
 * @property integer $id
 * @property string $assignedTo
 * @property string $actionDescription
 * @property integer $visibility
 * @property integer $associationId
 * @property string $associationType
 * @property string $associationName
 * @property integer $dueDate
 * @property integer $showTime
 * @property string $priority
 * @property string $type
 * @property integer $createDate
 * @property string $complete
 * @property string $reminder
 * @property string $completedBy
 * @property integer $completeDate
 * @property integer $lastUpdated
 * @property string $updatedBy
 */
class Actions extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Actions the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'x2_actions';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('actionDescription, visibility, associationId', 'required'),
			array('visibility, associationId, dueDate, showTime, createDate, completeDate, lastUpdated', 'numerical', 'integerOnly'=>true),
			array('assignedTo, associationType, type, completedBy, updatedBy', 'length', 'max'=>20),
			array('associationName', 'length', 'max'=>100),
			array('priority', 'length', 'max'=>10),
			array('complete, reminder', 'length', 'max'=>5),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, assignedTo, actionDescription, visibility, associationId, associationType, associationName, dueDate, showTime, priority, type, createDate, complete, reminder, completedBy, completeDate, lastUpdated, updatedBy', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	
	public function attributeLabels() {
		$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Actions'));
                $arr=array();
                foreach($fields as $field){
                    $arr[$field->fieldName]=Yii::t('actions',$field->attributeLabel);
                }
                
                return $arr;
	}


	public static function completeAction($id) {
		$action=Actions::model()->findByPk($id);
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
			return Yii::t('actions','Overdue {time}',array('{time}'=>Actions::formatDate($dueDate)));	// overdue by X hours/etc

		else
			return Yii::t('actions','Due {date}',array('{date}'=>Actions::formatDate($dueDate)));
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
				return CActiveRecord::model('Contacts')->findByPk($ownerId);
			if($ownerType=='accounts')
				return CActiveRecord::model('Accounts')->findByPk($ownerId);
			if($ownerType=='cases')
				return CActiveRecord::model('CaseChild')->findByPk($ownerId);
			if($ownerType=='sales')
				return CActiveRecord::model('Sales')->findByPk($ownerId);
		}
		return false;	// either the type is unkown, or there simply is no owner
	}
	
	// creates virtual attribute for owner's name, if exists
	public function getOwnerName() {
		$ownerModel = Actions::getOwnerModel($this->ownerType,$this->ownerId);
		if ($ownerModel)
			return $ownerModel->name;	// get name of owner
		else
			return false;
	}
	
	
	public function search() {
		$criteria=new CDbCriteria;
		$parameters=array('condition'=>"(assignedTo='Anyone' OR assignedTo='".Yii::app()->user->getName()."' OR assignedTo='') AND complete!='Yes' AND dueDate <= '".mktime(23,59,59)."'",'limit'=>ceil(ProfileChild::getResultsPerPage()/2));
		$criteria->scopes=array('findAll'=>array($parameters));
		
		return $this->searchBase($criteria);
	}

	public function searchComplete() {
		$criteria=new CDbCriteria;
		$parameters=array("condition"=>"completedBy='".Yii::app()->user->getName()."' AND complete='Yes' AND type IS NULL","limit"=>ceil(ProfileChild::getResultsPerPage()/2));
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
		$parameters=array("condition"=>"visibility='1' AND complete='Yes' AND type IS NULL","limit"=>ceil(ProfileChild::getResultsPerPage()/2));
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
		
		$criteria->addCondition('type != "workflow" OR type IS NULL');
		
		
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
}