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

/**
 * A Campaign represents a one time mailing to a list of contacts.
 *
 * When a campaign is created, a contact list must be specified. When a campaing is 'launched'
 * a duplicate list is created leaving the original unchanged. The duplicate 'campaign' list
 * will keep track of which contacts were sent email, who opened the mail, and who unsubscribed.
 * A campaign is 'active' after it has been launched and ready to send mail. A campaign is 'complete'
 * when all applicable email has been sent. This is the model class for table "x2_campaigns".
 *
 * @package X2CRM.modules.marketing.models
 */
class Campaign extends X2Model {
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName()	{ return 'x2_campaigns'; }

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'module'=>'marketing'
			),
			'ERememberFiltersBehavior' => array(
				'class'=>'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			)
		));
	}

	public function relations() {
		return array_merge(parent::relations(),array(
			'list'=>array(self::BELONGS_TO, 'X2List', 'listId'),
			'attachments'=>array(self::HAS_MANY, 'CampaignAttachment', 'campaign'),
		));
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
					$model=X2Model::model($relations[$seg]->className);
				else
					break;
			}
			return $model->getAttributeLabel($name);
		} else
			return $this->generateAttributeLabel($attribute);
	}

	/**
	 * Convenience method to retrieve a Campaign model by id. Filters by the current user's permissions.
	 *
	 * @param integer $id Model id
	 * @return Campaign
	 */
	public static function load($id) {
		$model = X2Model::model('Campaign');
		return $model->with('list')->findByPk((int)$id,$model->getAccessCriteria());
	}

	/**
	 * Search all Campaigns using this model's attributes as the criteria
	 *
	 * @return Array Set of matching Campaigns
	 */
	public function search() {
		$criteria=new CDbCriteria;
		$condition = '';
		if(!Yii::app()->user->checkAccess('MarketingAdminAccess')) {
			$condition = 't.visibility="1" OR t.assignedTo="Anyone"  OR t.assignedTo="'.Yii::app()->user->getName().'"';
				/* x2temp */
				$groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
				if(!empty($groupLinks))
					$condition .= ' OR t.assignedTo IN ('.implode(',',$groupLinks).')';

				$condition .= 'OR (t.visibility=2 AND t.assignedTo IN
					(SELECT username FROM x2_group_to_user WHERE groupId IN
						(SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
		}
        if(!Yii::app()->user->checkAccess('MarketingAdminAccess'))
            $criteria->addCondition($condition);
		return $this->searchBase($criteria);
	}

	/**
	 * Returns a CDbCriteria containing record-level access conditions.
	 * @return CDbCriteria
	 */
	public function getAccessCriteria() {
		$criteria = new CDbCriteria;

		$accessLevel = 0;
		if(Yii::app()->user->checkAccess('MarketingAdmin'))
			$accessLevel = 3;
		elseif(Yii::app()->user->checkAccess('MarketingView'))
			$accessLevel = 2;
		elseif(Yii::app()->user->checkAccess('MarketingViewPrivate'))
			$accessLevel = 1;

        $conditions=$this->getAccessConditions($accessLevel);
		foreach($conditions as $arr){
            $criteria->addCondition($arr['condition'],$arr['operator']);
        }

		return $criteria;
	}
}
