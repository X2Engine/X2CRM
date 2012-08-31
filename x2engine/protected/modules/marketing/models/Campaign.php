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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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

/**
 * This is the model class for table "x2_campaigns".
 */
class Campaign extends X2Model {
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName()	{ return 'x2_campaigns'; }

	public function behaviors() {
		return array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'baseRoute'=>'/marketing'
			),
			'ERememberFiltersBehavior' => array(
				'class'=>'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			)
		);
	}

	public function relations() {
		return array(
			'list'=>array(self::BELONGS_TO, 'X2List', 'listId')
		);
	}

	//Similar to X2Model but we had a special case with 'marketing'
	public function attributeLabels() {
		$this->queryFields();
		
		$labels = array();
			
		foreach(self::$_fields[$this->tableName()] as &$_field)
			$labels[ $_field->fieldName ] = Yii::t('marketing',$_field->attributeLabel);

		return $labels;
	}
		
	//Similar to X2Model but we had a special case with 'marketing'
	public function getAttributeLabel($attribute) {
	
		$this->queryFields();
		
		// don't call attributeLabels(), just look in self::$_fields
		foreach(self::$_fields[$this->tableName()] as &$_field) {
			if($_field->fieldName == $attribute)
				return Yii::t('marketing',$_field->attributeLabel);
		}
		// original Yii code
		if(strpos($attribute,'.')!==false) {
			$segs=explode('.',$attribute);
			$name=array_pop($segs);
			$model=$this;
			foreach($segs as $seg) {
				$relations=$model->getMetaData()->relations;
				if(isset($relations[$seg]))
					$model=CActiveRecord::model($relations[$seg]->className);
				else
					break;
			}
			return $model->getAttributeLabel($name);
		} else
			return $this->generateAttributeLabel($attribute);
	}

	public static function load($id) {
		$model = self::model()->with('list')->findByPk((int)$id, 'x2_checkViewPermission(t.visibility,t.assignedTo,"'.Yii::app()->user->getName().'") > 0');
		if($model===null)
			throw new CHttpException(404,Yii::t('app', 'The requested page does not exist.'));
		return $model;
	}

	public function search() {
		$criteria=new CDbCriteria;
		$criteria->addCondition('x2_checkViewPermission(visibility,assignedTo,"'.Yii::app()->user->getName().'") > 0');
		return $this->searchBase($criteria);
	}
}
