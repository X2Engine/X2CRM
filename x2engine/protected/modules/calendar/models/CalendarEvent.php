<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/** Copy of Actions
 *	Used for diferentiating between calendar event and actions (same in the database, difforent to users)
 */

/**
 * This is the model class for table "x2_actions".
 *
 * @package X2CRM.modules.calendar.models
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
 * @property integer $workflowId
 * @property integer $stageNumber
 */
class CalendarEvent extends CFormModel
{
	public $dueDate;
	public $completeDate;
	public $associationType;
	public $associationId;
	public $assignedTo;
	public $priority;
	public $visibility;
	public $reminder;
	public $type;
	public $recurrence;
	public $endRecurrence;
	public $allDay;
	public $color;
	
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
            
                $fields=Fields::model()->findAllByAttributes(array('modelName'=>'Calendar'));
                $arr=array(
                    'varchar'=>array(),
                    'text'=>array(),
                    'date'=>array(),
                    'dropdown'=>array(),
                    'int'=>array(),
                    'email'=>array(),
                    'currency'=>array(),
                    'url'=>array(),
                    'float'=>array(),
                    'boolean'=>array(),
                    'required'=>array(),
                    
                );
                $return=array();
                foreach($fields as $field){
                    $arr[$field->type][]=$field->fieldName;
                    if($field->required) {
						if(!($field->fieldName == 'actionDescription' && $this->scenario == 'workflow'))
							$arr['required'][]=$field->fieldName;
					}
                }
                foreach($arr as $key=>$array){
                    switch($key){
                        case 'email':
                            $return[]=array(implode(", ",$array),$key);
                            break;
                        case 'required':
                            $return[]=array(implode(", ",$array),$key);
                            break;
                        case 'int':
                            $return[]=array(implode(", ",$array),'numerical','integerOnly'=>true);
                            break;
                        case 'float':
                            $return[]=array(implode(", ",$array),'type','type'=>'float');
                            break;
                        case 'boolean':
                            $return[]=array(implode(", ",$array),$key);
                            break;
                        default:
                            break;
                        
                    }
                    
                } 
                return $return;
	}
	
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	
	public function attributeLabels() {
		$fields=Fields::model()->findAllByAttributes(array('modelName'=>'Actions'));
		$arr=array();
		foreach($fields as &$field)
			$arr[$field->fieldName] = Yii::t('actions',$field->attributeLabel);
		$arr['startDate']=Yii::t('actions','Start Date');
		$arr['endDate']=Yii::t('actions','End Date');
		return $arr;
	}
	
	public function getRecurrence() {
		return array(
			1=>Yii::t('calendar', 'Once'),
			2=>Yii::t('calendar', 'Daily'),
			3=>Yii::t('calendar', 'Weekly'),
			4=>Yii::t('calendar', 'Monthly'),
			5=>Yii::t('calendar', 'Yearly'),
		);
	}
}