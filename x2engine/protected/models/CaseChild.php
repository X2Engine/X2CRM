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

class CaseChild extends Cases {

	public static function getNames(){
		$arr=CaseChild::model()->findAll();
		$names=array(0=>"None");
		foreach($arr as $case){
			$names[$case->id]=$case->name;
		}
		return $names;
	}

	public static function parseContacts($model) {
		
		$arr=$model->associatedContacts;
		$str="";
		foreach($arr as $contact){
			$str.=" ".$contact;
		}
		$str=substr($str,1);
		$model->associatedContacts=$str;
		
		return $model;
	}

    public function behaviors() {
               return array(
                   'ERememberFiltersBehavior' => array(
                       'class' => 'application.components.ERememberFiltersBehavior',
                       'defaults'=>array(),           /* optional line */
                       'defaultStickOnClear'=>false   /* optional line */
                   ),
               );
        }
    
    public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;
		$parameters=array("condition"=>"status='Open'");
		$criteria->scopes=array('findAll'=>array($parameters));

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('priority',$this->priority,true);
		$criteria->compare('assignedTo',$this->assignedTo,true);
		$criteria->compare('endDate',$this->endDate,true);
		$criteria->compare('timeframe',$this->timeframe,true);
		$criteria->compare('createDate',$this->createDate,true);
		$criteria->compare('associatedContacts',$this->associatedContacts,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('resolution',$this->resolution,true);

		return new SmartDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'createDate ASC',
			),
			'criteria'=>$criteria,
		));
	}

	public function searchAdmin() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('priority',$this->priority,true);
		$criteria->compare('assignedTo',$this->assignedTo,true);
		$criteria->compare('endDate',$this->endDate,true);
		$criteria->compare('timeframe',$this->timeframe,true);
		$criteria->compare('createDate',$this->createDate,true);
		$criteria->compare('associatedContacts',$this->associatedContacts,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('resolution',$this->resolution,true);

		return new SmartDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'createDate ASC',
			),
			'criteria'=>$criteria,
		));
	}

	public function attributeLabels() {
		return array(
			'id'=>Yii::t('projects','ID'),
			'name'=>Yii::t('projects','Name'),
			'status'=>Yii::t('projects','Status'),
			'type'=>Yii::t('projects','Type'),
			'priority'=>Yii::t('projects','Priority'),
			'assignedTo'=>Yii::t('projects','Assigned To'),
			'endDate'=>Yii::t('projects','End Date'),
			'timeframe'=>Yii::t('projects','Timeframe'),
			'createDate'=>Yii::t('projects','Create Date'),
			'associatedContacts'=>Yii::t('projects','Associated Contacts'),
			'description'=>Yii::t('projects','Description'),
			'resolution'=>Yii::t('projects','Resolution'),
			'lastUpdated'=>Yii::t('projects','Last Updated'),
			'updatedBy'=>Yii::t('projects','Updated By'),
		);
	}
}
?>