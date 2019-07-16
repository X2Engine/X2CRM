<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




/**
 * This is the model class for table "x2_workflow_stages".
 *
 * @package application.modules.workflow.models
 * @property integer $id
 * @property integer $workflowId
 * @property integer $stageNumber
 * @property string $name
 * @property string $description
 * @property float $conversionRate
 * @property float $value
 * @property integer $requirePrevious
 * @property integer $requireComment
 */
class WorkflowStage extends CActiveRecord {

    /**
     * Value of $requirePrevious which indicates that all previous stages are required
     */
    const REQUIRE_ALL = 1; 

	public $_roles = array();
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
			array('workflowId, stageNumber, requirePrevious', 'numerical', 'integerOnly'=>true),
			array('requireComment', 'boolean'),
			array('conversionRate, value', 'type', 'type'=>'float'),
			array('conversionRate', 'numerical', 'max'=>100, 'min'=>0),
			array('name', 'length', 'max'=>40),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, workflowId, name, description, conversionRate, value', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array(
			'workflow'=>array(self::BELONGS_TO, 'Workflow', 'workflowId'),
			// 'roles'=>array(self::MANY_MANY, 'Roles','x2_role_to_workflow(stageId, roleId)'),
		);
	}

	public function getRoles() {
		if(empty($this->_roles) && !empty($this->id))
			$this->_roles = Yii::app()->db->createCommand()
                ->select('roleId')
                ->from('x2_role_to_workflow')
                ->where('stageId='.$this->id)
                ->queryColumn();
		return $this->_roles;
	}
	
	public function setRoles($roles) {
		if(is_array($roles) || !empty($roles))
			$this->_roles = $roles;
	}
	
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'workflowId' => Yii::t('workflow','Workflow'),
			// 'stageNumber' => Yii::t('workflow','Stage Number'),
			'name' => Yii::t('workflow','Stage Name'),
			'description' => Yii::t('workflow','Description'),
			'conversionRate' => Yii::t('workflow','Conversion Rate'),
			'value' => Yii::t('workflow','Value'),
			'roles' => Yii::t('workflow','Roles'),
			'requirePrevious' => Yii::t('workflow','Required Stages'),
			'requireComment' => Yii::t('workflow','Require Comment?'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the 
     *  search/filter conditions.
	 */
	public function search($id) {

		$criteria = new CDbCriteria(
            array('condition'=>'workflowId='.$id,'order'=>'stageNumber ASC'));

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>ceil(Profile::getResultsPerPage())
			),
		));
	}

}
