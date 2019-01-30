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




/** Copy of Actions
 *	Used for diferentiating between calendar event and actions (same in the database, difforent to users)
 */

/**
 * This is the model class for table "x2_actions".
 *
 * @package application.modules.calendar.models
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