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

class DocChild extends Docs {

	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		// $criteria->compare('id',$this->id);
		$criteria->compare('title',$this->title,true);
		// $criteria->compare('text',$this->text,true);
		$criteria->compare('createdBy',$this->createdBy,true);
		$criteria->compare('createDate',$this->createDate);
		$criteria->compare('updatedBy',$this->updatedBy,true);
		$criteria->compare('lastUpdated',$this->lastUpdated);
		$criteria->compare('type',$this->type);
		// $criteria->compare('editPermissions',$this->editPermissions,true);

		$dateRange = Yii::app()->controller->partialDateRange($this->createDate);
		if($dateRange !== false)
			$criteria->addCondition('createDate BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);
			
		$dateRange = Yii::app()->controller->partialDateRange($this->lastUpdated);
		if($dateRange !== false)
			$criteria->addCondition('lastUpdated BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);
		
		return new CActiveDataProvider(get_class($this), array(
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}
	
	public static function getEmailTemplates() {
		$templateLinks = array();
		$templates = CActiveRecord::model('Docs')->findAllByAttributes(array('type'=>'email'),new CDbCriteria(array('order'=>'lastUpdated DESC')));
		foreach($templates as &$template)
			$templateLinks[$template->id] = $template->title;
		return $templateLinks;
	}
	
	public function parseType() {
		if(!isset($this->type))
			$this->type = '';
		switch($this->type) {
			case 'email':
				return Yii::t('docs','Template');
			default:
				return Yii::t('docs','Document');
		}
	}
	
	public function attributeLabels() {
		return array(
			'id' => Yii::t('docs','ID'),
			'type' => Yii::t('docs','Doc Type'),
			'title' => Yii::t('docs','Title'),
			'text' => Yii::t('docs','Text'),
			'createdBy' => Yii::t('docs','Created By'),
			'createDate' => Yii::t('docs','Create Date'),
			'updatedBy' => Yii::t('docs','Updated By'),
			'lastUpdated' => Yii::t('docs','Last Updated'),
			'editPermissions' => Yii::t('docs','Edit Permissions'),
			
		);
	}
}
?>
