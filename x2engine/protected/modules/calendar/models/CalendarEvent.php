<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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