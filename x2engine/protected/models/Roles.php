<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

/**
 * This is the model class for table "x2_roles".
 *
 * @package X2CRM.models
 * @property integer $id
 * @property string $name
 * @property string $users
 */
class Roles extends CActiveRecord {

    private static $_authNames;

    /**
     * Retrieves a list of restricted (non-permissible) role names.
     */
    public static function getAuthNames() {
        if(!isset(self::$_authNames)) {
            self::$_authNames = Yii::app()->db->cache(3600)->createCommand()
                    ->select('name')
                    ->from('x2_auth_item')
                    ->queryColumn();
        }
        return self::$_authNames;
    }

	/**
	 * Returns the static model of the specified AR class.
	 * @return Roles the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_roles';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('name', 'length', 'max'=>250),
			array('name','match',
                'not'=>true,
                'pattern'=> '/^('.implode('|',array_map(function($n){return preg_quote($n);},self::getAuthNames())).')/i',
                'message'=>Yii::t('admin','The name you entered is reserved or belongs to the system.')),
			array('users', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, users', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('admin','ID'),
			'name' => Yii::t('admin','Name'),
			'users' => Yii::t('admin','Users'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('users',$this->users,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}

	/* Looks up roles held by the specified user.
	 * Uses cache to lookup/store roles.
	 *
	 * @param Integer $userId user to look up roles for
	 * @param Boolean $cache whether to use cache
	 * @return Array array of roleIds
	 */
	public static function getUserRoles($userId,$cache=true) {
		$cacheVar = 'user_roles_'.$userId;

		// check the app cache for user's roles
		if($cache === true && ($userRoles = Yii::app()->cache->get($cacheVar)) !== false) {
			if(isset($userRoles[$userId]))
				return $userRoles[$userId];
		} else {
			$userRoles = array();
		}

		$userRoles = Yii::app()->db->createCommand() // lookup the user's roles
			->select('roleId')
			->from('x2_role_to_user')
			->where('type="user" AND userId='.$userId)
			->queryColumn();

		$groupRoles = Yii::app()->db->createCommand()	// lookup roles of all the user's groups
			->select('x2_role_to_user.roleId')
			->from('x2_group_to_user')
			->join('x2_role_to_user','x2_role_to_user.userId=x2_group_to_user.groupId AND x2_group_to_user.userId='.$userId.' AND type="group"')
			->queryColumn();

		$userRoles[$userId] = array_unique($userRoles + $groupRoles);  // combine all the roles, remove duplicates

		if($cache === true)
			Yii::app()->cache->set($cacheVar,$userRoles,259200); // cache user groups for 3 days

		return $userRoles[$userId];
	}
}
