<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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

class ProjectChild extends Projects{

	public static function getNames() {
		$arr=ProjectChild::model()->findAll();
		$names=array(0=>'None');
		foreach($arr as $project){
			$names[$project->id]=$project->name;
		}
		return $names;
	}

	public static function parseUsers($arr) {
		$str='';
		foreach($arr as $user){
			$str.=$user.', ';
		}
		$str=substr($str,0,strlen($str)-2);
		
		return $str;
	}

	public static function parseContacts($arr) {
		$str='';
		foreach($arr as $contact) {
			 $str.=' '.$contact;
		}
		$str=substr($str,1);
	
		return $str;
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
        
        
        
        
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;
		$parameters=array('condition'=>"status!='Complete'");
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
			'lastUpdated'=>Yii::t('projects','Last Updated'),
			'updatedBy'=>Yii::t('projects','Updated By'),
		);
	}
}