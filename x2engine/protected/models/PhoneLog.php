<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2022 X2 Engine Inc.
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
 * @edition: ent
 */


/**
 * This is the model class for table "x2_phone_log".
 * @package application.models
 */
class PhoneLog extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Imports the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_phone_log';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('number, type', 'required'),
        );
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'recordType' => Yii::t('contacts','Record Type'),
			'recordId' => Yii::t('contacts','Record ID'),
			'number' => Yii::t('app', 'Phone Number'),
			'timestamp' => Yii::t('app','call time'),
			'type' => Yii::t('app', 'Phone Service Type'),
		);
	}

	/**
         * @return log calls to the respective record
         */
        public function logCallToRecord($body, $type, $direction){
	    $call_log = new Actions();
            $call_log->actionDescription = '('.$type.') type: <font color="blue">'.$direction.'</font>';
            $call_log->associationType = $body->modelType;
            $call_log->associationId = $body->modelId;
            $call_log->completeDate = $call_log->dueDate = time();
	    $call_log->type = 'call';
            $call_log->assignedTo = 'anyone';
            $call_log->visibility = 1;
            $call_log->save();	
        }

	/**
         * @return custom message or correct record Type
         */
        public function getType($data){
            $render_type = '<font color="red">N/C</font>';
            if(!empty($data->recordType)){
                $render_type = $data->recordType;
            }
            return $render_type;
        }

        /**
         * @return link or message for each phone number
         */
        public function getLink($data){
            $render_link = '<font color="blue">New Phone #</font>';
            if(!empty($data->recordType) && !empty($data->recordId)){
                $record = $data->recordType::model()->findByPk($data->recordId);
                if(isset($record)){
                    $render_link = $record->link;
                }
            }
            return $render_link;
        }

        /**
         * @return array of phone call logs from the database filterd by $type
         */
        public function search($type){
            $criteria = new CDbCriteria;
            $criteria->select = 't.*';
            $criteria->addCondition('t.type = \'' . $type . '\'');
            $dataProvider = new CActiveDataProvider('PhoneLog', array(
                'sort' => array(
                        'defaultOrder' => 't.timestamp DESC',
                ),
                'pagination' => array(
                        'pageSize' => 15
                ),
                'criteria' => $criteria
            ));
            return $dataProvider;
        }

	
	/**
         * @return string for pop up message
         */
	public function getPopUpMessage($number){
            $caller = PhoneNumber::model()->findByAttributes(array('number'=> $number));
            $caller_message = '<tr><td>&nbsp</td></tr><tr><td><center>This is a new caller!</center></td></tr>';
            $caller_message .= '<tr><td><center>No data found for this number.</center></td></tr><tr><td><center>' . 
				CHtml::link('Create Contact', array('contacts/create'), null, array('target'=>'_blank')). '</center></td></tr>';
            if(isset($caller)){
                $caller_info = $caller->modelType::model()->findByPk($caller->modelId);
		if(isset($caller_info)){
                    $caller_message = '<tr><td>&nbsp</td></tr>';
                    $caller_message .= '<tr><td><center>Type of Record: ' . $caller->modelType . '</center></td></tr>';
                    $caller_message .= '<tr><td><center>' . 
				        CHtml::link($caller_info->name, array('contacts/view', 'id'=>$caller_info->id), array('target'=>'_blank')) . 
				        ' is calling you!</center></td></tr>';
		    }
            }
            $message = '<br><table stype="width:100%">';
            $message .= '<tr><td><center>'. X2Html::logo ('menu', array (
                        'id' => 'your-logo',
                        'class' => 'default-logo',
                        )) . '</center></td></tr>';
            $message .= '<tr><td><center>Today\'s Date: ' . Formatter::formatDateTime(time()) . '</center></td></tr>';
            $message .= '<tr><td><center>Phone Number: ' . PhoneNumber::model()->formatPhoneNumber($number) . '</center></td></tr>';
            $message .= $caller_message;
            $message .= '</table>';
            return $message;
        }

}
