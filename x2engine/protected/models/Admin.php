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

/**
 * This is the model class for table "x2_admin".
 *
 * The followings are the available columns in table 'x2_admin':
 * @property integer $id
 * @property integer $accounts
 * @property integer $sales
 * @property integer $timeout
 * @property string $webLeadEmail
 * @property string $menuOrder
 * @property string $menuNicknames
 * @property integer $chatPollTime
 * @property string $menuVisibility
 */
class Admin extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Admin the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'x2_admin';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('accounts, sales, timeout, chatPollTime', 'numerical', 'integerOnly'=>true),
			array('webLeadEmail', 'length', 'max'=>200),
			array('menuOrder, menuNicknames', 'length', 'max'=>255),
			array('menuVisibility', 'length', 'max'=>100),
			array('currency', 'length', 'max'=>3),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, accounts, sales, timeout, webLeadEmail, menuOrder, menuNicknames, chatPollTime, menuVisibility, currency', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'accounts' => 'Accounts',
			'sales' => 'Sales',
			'timeout' => 'Timeout',
			'webLeadEmail' => 'Web Lead Email',
			'currency' => 'Currency',
			'menuOrder' => 'Menu Order',
			'menuNicknames' => 'Menu Nicknames',
			'chatPollTime' => Yii::t('admin','Chat Poll Time'),
			'menuVisibility' => 'Menu Visibility',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('accounts',$this->accounts);
		$criteria->compare('sales',$this->sales);
		$criteria->compare('timeout',$this->timeout);
		$criteria->compare('webLeadEmail',$this->webLeadEmail,true);
		$criteria->compare('currency',$this->currency,true);
		$criteria->compare('menuOrder',$this->menuOrder,true);
		$criteria->compare('menuNicknames',$this->menuNicknames,true);
		$criteria->compare('chatPollTime',$this->chatPollTime);
		$criteria->compare('menuVisibility',$this->menuVisibility,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}