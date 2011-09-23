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

class SaleChild extends Sales {

	public static function getNames() {
		$arr=SaleChild::model()->findAll();
		$names=array(0=>"None");
		foreach($arr as $sale){
			$names[$sale->id]=$sale->name;
		}
		return $names;
	}


	public static function parseUsers($arr){
		$str="";
		foreach($arr as $user){
			 $str.=$user.", ";
		}
		$str=substr($str,0,strlen($str)-2);
		return $str;
	}

	public static function parseUsersTwo($arr){
		$str="";
		foreach($arr as $user=>$name){
			$str.=$user.", ";
		}
		$str=substr($str,0,strlen($str)-2);
						
		return $str;
	}

	public static function parseContacts($arr){
		$str="";
		foreach($arr as $contact){
			$str.=$contact." ";
		}
		return $str;
	}

	public static function parseContactsTwo($arr){
		$str="";
		foreach($arr as $id=>$contact){
			$str.=$id." ";
		}
		return $str;
	}

	public static function getSalesLinks($accountId) {

		$salesList = CActiveRecord::model('SaleChild')->findAllByAttributes(array('accountId'=>$accountId));
		// $salesList = $this->model()->findAllByAttributes(array('accountId'),'=',array($accountId));
		
		$links = array();
		foreach($salesList as $model) {
			$links[] = CHtml::link($model->name,array('sales/view','id'=>$model->id));
		}
		return implode(', ',$links);
	}

	public static function editContactArray($arr, $model) {

		$pieces=explode(" ",$model->associatedContacts);
		unset($arr[0]);

		foreach($pieces as $contact){
			if(array_key_exists($contact,$arr)){
				unset($arr[$contact]);
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
			if($username!='')
				$data[]=UserChild::model()->findByAttributes(array('username'=>$username));
		}
		
		$temp=array();
		if(isset($data)){
			foreach($data as $item){
				if(isset($item))
					$temp[$item->username]=$item->firstName.' '.$item->lastName;
			}
		}
		return $temp;
	}

	public static function editContactsInverse($arr) {
		$data=array();
		
		foreach($arr as $id){
			if($id!='')
				$data[]=ContactChild::model()->findByPk($id);
		}
		$temp=array();
		
		foreach($data as $item){
			$temp[$item->id]=$item->firstName.' '.$item->lastName;
		}
		return $temp;
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
		$parameters=array("condition"=>"salesStage='Working'",'limit'=>ceil(ProfileChild::getResultsPerPage()));
		$criteria->scopes=array('findAll'=>array($parameters));

		$criteria->compare('id',$this->id);
		//$criteria->compare('assignedTo',$this->assignedTo,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('quoteAmount',$this->quoteAmount);
		$criteria->compare('salesStage',$this->salesStage,true);
		$criteria->compare('expectedCloseDate',$this->expectedCloseDate,true);
		$criteria->compare('probability',$this->probability);
		$criteria->compare('leadSource',$this->leadSource,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('createDate',$this->createDate,true);

		return new SmartDataProvider('Sales', array(
			'sort'=>array(
				'defaultOrder'=>'createDate ASC',
			),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}

	public function searchAdmin() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		//$criteria->compare('assignedTo',$this->assignedTo,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('quoteAmount',$this->quoteAmount);
		$criteria->compare('salesStage',$this->salesStage,true);
		$criteria->compare('expectedCloseDate',$this->expectedCloseDate,true);
		$criteria->compare('probability',$this->probability);
		$criteria->compare('leadSource',$this->leadSource,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('createDate',$this->createDate,true);

		return new SmartDataProvider(get_class($this), array(
			'sort'=>array(
				'defaultOrder'=>'createDate ASC',
			),
			'pagination'=>array(
				'pageSize'=>ProfileChild::getResultsPerPage(),
			),
			'criteria'=>$criteria,
		));
	}

	public function attributeLabels() {
		return array(
			'id' => Yii::t('sales','ID'),
			'name' => Yii::t('sales','Name'),
			'accountId' => Yii::t('sales','Account ID'),
			'accountName' => Yii::t('sales','Account'),
			'quoteAmount' => Yii::t('sales','Quote Amount'),
			'salesStage' => Yii::t('sales','Sales Stage'),
			'expectedCloseDate' => Yii::t('sales','Expected Close Date'),
			'probability' => Yii::t('sales','Probability'),
			'leadSource' => Yii::t('sales','Lead Source'),
			'description' => Yii::t('sales','Description'),
			'assignedTo' => Yii::t('sales','Assigned To'),
			'createDate' => Yii::t('sales','Create Date'),
			'associatedContacts' => Yii::t('sales','Contacts'),
			'lastUpdated' => Yii::t('sales','Last Updated'),
			'updatedBy' => Yii::t('sales','Updated By'),
		);
	}
}
?>
