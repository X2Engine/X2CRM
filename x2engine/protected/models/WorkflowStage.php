<?php

/**
 * This is the model class for table "x2_workflow_stages".
 *
 * The followings are the available columns in table 'x2_workflow_stages':
 * @property integer $id
 * @property integer $workflowId
 * @property integer $stageNumber
 * @property string $name
 * @property float $conversionRate
 * @property float $value
 */
class WorkflowStage extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @return WorkflowStage the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_workflow_stages';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('workflowId, stageNumber', 'numerical', 'integerOnly'=>true),
			array('conversionRate, value', 'type', 'type'=>'float'),
			array('conversionRate', 'numerical', 'max'=>100, 'min'=>0),
			array('name', 'length', 'max'=>40),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, workflowId, stageNumber, name, conversionRate, value', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'workflowId' => Yii::t('workflow','Workflow'),
			'stageNumber' => Yii::t('workflow','Stage Number'),
			'name' => Yii::t('workflow','Stage Name'),
			'conversionRate' => Yii::t('workflow','Conversion Rate'),
			'value' => Yii::t('workflow','Value'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search($id) {

		$criteria = new CDbCriteria(array('condition'=>'workflowId='.$id,'order'=>'stageNumber ASC'));

		return new CActiveDataProvider(get_class($this), array('criteria'=>$criteria));
	}
}