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




Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_opportunities".
 *
 * @package application.modules.opportunities.models
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
			'LinkableBehavior'=>array(
				'class'=>'LinkableBehavior',
				'module'=>'opportunities'
			),
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.behaviors.ERememberFiltersBehavior',
				'defaults'=>array(),
				'defaultStickOnClear'=>false
			),
                   'MappableBehavior' => array(
                'class' => 'application.components.behaviors.MappableBehavior',
            ),
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

	public function search($resultsPerPage=null, $uniqueId=null) {

		// $parameters=array("condition"=>"salesStage='Working'",'limit'=>ceil(Profile::getResultsPerPage()));
	         $criteria=new CDbCriteria;
                if ($resultsPerPage === null) {
                    if (!Yii::app()->user->isGuest) {
                        $resultsPerPage = Profile::getResultsPerPage();
                    } else {
                        $resultsPerPage = 20;
                    }
                }

		return $this->searchBase($criteria, $resultsPerPage);
	}

	public function searchAdmin() {
		$criteria=new CDbCriteria;

		return $this->searchBase($criteria);
	}
        
    public function searchList($id, $pageSize = null) {
        $list = X2List::model()->findByPk($id);
        
        if (isset($list)) {
            $search = $list->queryCriteria();
            
            $this->compareAttributes($search);
           
            return new SmartActiveDataProvider('Opportunity', array(
                'criteria' => $search,
                'sort' => array(
                    'defaultOrder' => 't.lastUpdated DESC' // true = ASC
                ),
                'pagination' => array(
                    'pageSize' => isset($pageSize) ? $pageSize : Profile::getResultsPerPage(),
                ),
            ));
        } else { //if list is not working, return all contacts
            return $this->searchBase();
        }
    }


}
