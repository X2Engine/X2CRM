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

/**
 * @package X2CRM.modules.docs.models
 */
class DocChild extends Docs {

	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		// $criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
        $criteria->compare('subject',$this->subject,true);
		// $criteria->compare('text',$this->text,true);
		$criteria->compare('createdBy',$this->createdBy,true);
		$criteria->compare('createDate',$this->createDate);
		$criteria->compare('updatedBy',$this->updatedBy,true);
		$criteria->compare('lastUpdated',$this->lastUpdated);
		$criteria->compare('type',$this->type);
        
        if(!Yii::app()->user->checkAccess('AdminIndex')){
            $condition = 'visibility="1" OR createdBy="Anyone"  OR createdBy="'.Yii::app()->user->getName().'" OR editPermissions LIKE "%'.Yii::app()->user->getName().'%"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
            if(!empty($groupLinks))
                $condition .= ' OR createdBy IN ('.implode(',',$groupLinks).')';

            $condition .= 'OR (visibility=2 AND createdBy IN 
                (SELECT username FROM x2_group_to_user WHERE groupId IN
                    (SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
            $criteria->addCondition($condition);
        }
		// $criteria->compare('editPermissions',$this->editPermissions,true);

		$dateRange = Yii::app()->controller->partialDateRange($this->createDate);
		if($dateRange !== false)
			$criteria->addCondition('createDate BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);
			
		$dateRange = Yii::app()->controller->partialDateRange($this->lastUpdated);
		if($dateRange !== false)
			$criteria->addCondition('lastUpdated BETWEEN '.$dateRange[0].' AND '.$dateRange[1]);
		
		return new SmartDataProvider(get_class($this), array(
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
            'sort'=>array(
                'defaultOrder'=>'lastUpdated DESC',
            ),
			'criteria'=>$criteria,
		));
	}
	
	public static function getEmailTemplates() {
		$templateLinks = array();
        $criteria=new CDbCriteria(array('order'=>'lastUpdated DESC'));
        if(!Yii::app()->user->checkAccess('AdminIndex')){
            $condition = 'visibility="1" OR createdBy="Anyone"  OR createdBy="'.Yii::app()->user->getName().'"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
            if(!empty($groupLinks))
                $condition .= ' OR createdBy IN ('.implode(',',$groupLinks).')';

            $condition .= 'OR (visibility=2 AND createdBy IN 
                (SELECT username FROM x2_group_to_user WHERE groupId IN
                    (SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
            $criteria->addCondition($condition);
        }
		$templates = X2Model::model('Docs')->findAllByAttributes(array('type'=>'email'),$criteria);
		foreach($templates as &$template)
			$templateLinks[$template->id] = $template->name;
		natcasesort($templateLinks);
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
			'name' => Yii::t('docs','Title'),
			'text' => Yii::t('docs','Text'),
			'createdBy' => Yii::t('docs','Created By'),
			'createDate' => Yii::t('docs','Create Date'),
			'updatedBy' => Yii::t('docs','Updated By'),
			'lastUpdated' => Yii::t('docs','Last Updated'),
			'editPermissions' => Yii::t('docs','Edit Permissions'),
            'subject'=>Yii::t('docs','Subject'),
            'visibility'=>Yii::t('docs','Visibility'),
			
		);
	}
}
?>
