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

Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_opportunities".
 *
 * @package X2CRM.modules.opportunities.models
 */
class Opportunity extends X2Model {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Opportunity the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_opportunities'; }

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'module'=>'opportunities'
			),
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			)
		));
	}

	/**
	 * Formats data for associatedContacts before saving
	 * @return boolean whether or not to save
	 */
	public function beforeSave() {
		if(isset($this->associatedContacts))
			$this->associatedContacts = self::parseContacts($this->associatedContacts);

		return parent::beforeSave();
	}

	public static function getNames() {
		$arr = Opportunity::model()->findAll();
		$names = array(0=>'None');
		foreach($arr as $opportunity)
			$names[$opportunity->id] = $opportunity->name;

		return $names;
	}

	public static function parseUsers($userArray){
		return implode(', ',$userArray);
	}

	public static function parseUsersTwo($arr){
		$str="";
        if(is_array($arr)){
            $arr=array_keys($arr);
            $str=implode(', ',$arr);
        }

		return $str;
	}

	public static function parseContacts($contactArray){
        if(is_array($contactArray)){
            return implode(' ',$contactArray);
        }else{
            return $contactArray;
        }
	}

	public static function parseContactsTwo($arr){
		$str="";
		foreach($arr as $id=>$contact){
			$str.=$id." ";
		}
		return $str;
	}

	public static function getOpportunityLinks($accountId) {

		$allOpportunities = X2Model::model('Opportunity')->findAllByAttributes(array('accountName'=>$accountId));

		$links = array();
		foreach($allOpportunities as $model) {
			$links[] = CHtml::link($model->name,array('/opportunities/opportunities/view','id'=>$model->id));
		}
		return implode(', ',$links);
	}

	public static function editContactArray($arr, $model) {

        $rels=Relationships::model()->findAllByAttributes(array('firstType'=>'Contacts','secondType'=>'Opportunity','secondId'=>$model->id));
        $pieces=array();
        foreach($rels as $relationship){
            $contact=X2Model::model('Contacts')->findByPk($relationship->firstId);
            if(isset($contact)){
                $pieces[$relationship->firstId]=$contact->name;
            }
        }
		unset($arr[0]);
		foreach($pieces as $id=>$contact){
			if(isset($arr[$id])){
                unset($arr[$id]);
            }
		}

		return $arr;
	}

	public static function editUserArray($arr, $model) {

		$pieces=explode(', ',$model->assignedTo);
		unset($arr['Anyone']);
		unset($arr['admin']);
		foreach($pieces as $user){
			if(array_key_exists($user,$arr)){
				unset($arr[$user]);
			}
		}
		return $arr;
	}

	public static function editUsersInverse($arr) {

		$data=array();

		foreach($arr as $username){
			if($username!='' && !is_numeric($username))
				$data[]=User::model()->findByAttributes(array('username'=>$username));
			elseif(is_numeric($username))
				$data[]=Groups::model()->findByPK($username);
		}

		$temp=array();
		if(isset($data)){
			foreach($data as $item){
				if(isset($item)){
					if($item instanceof User)
						$temp[$item->username]=$item->firstName.' '.$item->lastName;
					else
						$temp[$item->id]=$item->name;
				}
			}
		}
		return $temp;
	}

	public static function editContactsInverse($arr) {
		$data=array();

		foreach($arr as $id){
			if($id!='')
				$data[]=X2Model::model('Contacts')->findByPk($id);
		}
		$temp=array();

		foreach($data as $item){
			$temp[$item->id]=$item->firstName.' '.$item->lastName;
		}
		return $temp;
	}

	public function search() {
		$criteria=new CDbCriteria;
		// $parameters=array("condition"=>"salesStage='Working'",'limit'=>ceil(ProfileChild::getResultsPerPage()));
		$parameters=array('limit'=>ceil(ProfileChild::getResultsPerPage()));
		$criteria->scopes=array('findAll'=>array($parameters));

		return $this->searchBase($criteria);
	}

	public function searchAdmin() {
		$criteria=new CDbCriteria;

		return $this->searchBase($criteria);
	}


}
