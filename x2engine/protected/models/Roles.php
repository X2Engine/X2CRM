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




/**
 * This is the model class for table "x2_roles".
 *
 * @package application.models
 * @property integer $id
 * @property string $name
 * @property string $users
 */
class Roles extends CActiveRecord {

    private static $_authNames;

    /**
     * Runtime storage array of user roles indexed by user ID
     * @var type
     */
    private static $_userRoles;
    private $_tmpUsers = null;
    private $_tmpEditPermissions = null;
    private $_tmpViewPermissions = null;

    /**
     * Retrieves a list of restricted (non-permissible) role names.
     */
    public static function getAuthNames() {
        if (!isset(self::$_authNames)) {
            $x2Roles = Yii::app()->db->createCommand()
                    ->select('name')
                    ->from('x2_roles')
                    ->queryColumn();
            $authRoles = Yii::app()->db->createCommand()
                    ->select('name')
                    ->from('x2_auth_item')
                    ->queryColumn();
            self::$_authNames = array_diff($authRoles, $x2Roles);
        }
        return self::$_authNames;
    }

    /**
     * Returns the static model of the specified AR class.
     * @return Roles the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * Get roles from cache 
     */
    public static function getCachedUserRoles($userId) {
        // check the app cache for user's roles
        return Yii::app()->cache->get(self::getUserCacheVar($userId));
    }

    /**
     * Clear role cache for specified user 
     */
    public static function clearCachedUserRoles($userId) {
        if (isset(self::$_userRoles[$userId]))
            unset(self::$_userRoles[$userId]);
        Yii::app()->cache->delete(self::getUserCacheVar($userId));
    }

    /**
     * Determines roles of the specified user, including group-inherited roles.
     *
     * Uses cache to lookup/store roles.
     *
     * @param integer $userId user for which to look up roles. Note, null user ID
     *  implies guest.
     * @param boolean $cache whether to use cache
     * @return Array array of roleIds
     */
    public static function getUserRoles($userId, $cache = true) {
        if ($cache && isset(self::$_userRoles[$userId])){
            return self::$_userRoles[$userId];
        }
        // check the app cache for user's roles
        if ($cache === true && ($userRoles = self::getCachedUserRoles($userId)) !== false) {
            self::$_userRoles[$userId] = $userRoles;
            return $userRoles;
        }
        $userRoles = array();

        if ($userId !== null) { // Authenticated user
            $userRoles = Yii::app()->db->createCommand() // lookup the user's roles
                    ->select('roleId')
                    ->from('x2_role_to_user')
                    ->where('`type`="user" AND `userId`=:userId')
                    ->queryColumn(array(':userId' => $userId));

            $groupRoles = Yii::app()->db->createCommand() // lookup roles of all the user's groups
                    ->select('rtu.roleId')
                    ->from('x2_group_to_user gtu')
                    ->join('x2_role_to_user rtu', 'rtu.userId=gtu.groupId '
                            . 'AND gtu.userId=:userId '
                            . 'AND type="group"')
                    ->queryColumn(array(':userId' => $userId));
        } else { // Guest
            $groupRoles = array();
            $userRoles = array();
            $guestRole = self::model()->findByAttributes(array('name' => 'Guest'));
            if (!empty($guestRole))
                $userRoles = array($guestRole->id);
        }

        // Combine all the roles, remove duplicates:
        $userRoles = array_unique($userRoles + $groupRoles);
        
        // Cache/store:
        self::$_userRoles[$userId] = $userRoles;
        if ($cache === true)
            Yii::app()->cache->set(self::getUserCacheVar($userId), $userRoles, 259200); // cache user groups for 3 days

        return $userRoles;
    }

    /**
     * Returns the timeout of the current user.
     *
     * Selects and returns the maximum timeout between the timeouts of the
     * current user's roles and the default timeout.
     * @return Integer Maximum timeout value
     */
    public static function getUserTimeout($userId, $cache = true) {
        $cacheVar = 'user_roles_timeout' . $userId;
        if ($cache === true && ($timeout = Yii::app()->cache->get($cacheVar)) !== false)
            return $timeout;


        $userRoles = Roles::getUserRoles($userId, $cache);
        $availableTimeouts = array();
        foreach ($userRoles as $role) {
            $timeout = Yii::app()->db->createCommand()
                    ->select('timeout')
                    ->from('x2_roles')
                    ->where('id=:role', array(':role' => $role))
                    ->queryScalar();
            if (!is_null($timeout))
                $availableTimeouts[] = (integer) $timeout;
        }

        $availableTimeouts[] = Yii::app()->settings->timeout;
        $timeout = max($availableTimeouts);
        if ($cache === true)
            Yii::app()->cache->set($cacheVar, $timeout, 259200);
        return $timeout;
    }

    private static function getUserCacheVar($userId) {
        return 'user_roles_' . ($userId === null ? 'guest' : $userId);
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
            array('name', 'length', 'max' => 250),
            array('name', 'match',
                'not' => true,
                'pattern' => '/^(' . implode('|', array_map(function($n) {
                                    return preg_quote($n);
                                }, self::getAuthNames())) . ')/i',
                'message' => Yii::t('admin', 'The name you entered is reserved or belongs to the system.')),
            array('timeout', 'numerical', 'integerOnly' => true, 'min' => 5),
            array('users', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, name, users', 'safe', 'on' => 'search'),
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
            'id' => Yii::t('admin', 'ID'),
            'name' => Yii::t('admin', 'Name'),
            'users' => Yii::t('admin', 'Users'),
            'timeout' => Yii::t('admin','Timeout'),
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('users', $this->users, true);

        return new CActiveDataProvider(get_class($this), array(
            'criteria' => $criteria,
        ));
    }

    public function getUsers() {
        $users = Yii::app()->db->createCommand()
                ->select('userId')
                ->from('x2_role_to_user')
                ->where('roleId=:roleId', array(':roleId' => $this->id))
                ->queryColumn();

        return $users;
    }

    public function getFieldPermissions($permission = null) {
        $permissions = array();
        $where = 'roleId=:roleId';
        $params = array(':roleId' => $this->id);
        if (!is_null($permission)) {
            $where.=' AND permission=:permission';
            $params[':permission'] = $permission;
        }
        $fieldData = Yii::app()->db->createCommand()
                ->select('fieldId, permission')
                ->from('x2_role_to_permission')
                ->where($where, $params)
                ->queryAll();
        foreach ($fieldData as $row) {
            $permissions[$row['fieldId']] = $row['permission'];
        }
        return $permissions;
    }

    public function afterSave() {
        $this->updateUsers();
        $this->updatePermissions();
        parent::afterSave();
    }

    public function updateUsers() {
        if (!is_null($this->_tmpUsers)) {
            Yii::app()->db->createCommand()
                    ->delete('x2_role_to_user', 'roleId = :roleId', array(':roleId' => $this->id));
            $insertData = array();
            foreach ($this->_tmpUsers as $user) {
                $userId = $user;
                $type = 'group';
                if (!is_numeric($user)) {
                    $userRecord = User::model()->findByAttributes(array('username' => $user));
                    $userId = isset($userRecord) ? $userRecord->id : null;
                    $type = 'user';
                }
                if (!is_null($userId)) {
                    $insertData[] = array('roleId' => $this->id, 'userId' => $userId, 'type' => $type);
                }
            }
            if (!empty($insertData)) {
                $builder = Yii::app()->db->schema->commandBuilder;
                $command = $builder->createMultipleInsertCommand('x2_role_to_user', $insertData);
                $command->execute();
            }
        }
    }

    public function updatePermissions() {
        if (!is_null($this->_tmpEditPermissions) && !is_null($this->_tmpViewPermissions)) {
            $newPermissions = $this->calculatePermissions();
            $this->executeMassPermissionsInsertUpdateQuery($newPermissions);
        }
    }

    public function setUsers(Array $users) {
        $this->_tmpUsers = $users;
    }

    public function setEditPermissions(Array $editPermissions) {
        $this->_tmpEditPermissions = $editPermissions;
    }

    public function setViewPermissions(Array $viewPermissions) {
        $this->_tmpViewPermissions = $viewPermissions;
    }

    private function calculatePermissions() {
        $ret = array(
            'edit' => array(),
            'view' => array(),
            'none' => array()
        );

        $fieldIds = Yii::app()->db->createCommand()
            ->select('id')
            ->from('x2_fields')
            ->queryColumn();

        $ret['edit'] = array_intersect($this->_tmpViewPermissions, $this->_tmpEditPermissions);
        $ret['view'] = array_diff($this->_tmpViewPermissions, $this->_tmpEditPermissions);
        $ret['none'] = array_diff($fieldIds, $this->_tmpViewPermissions);

        return $ret;
    }

    private function executeMassPermissionsInsertUpdateQuery($permissions) {
        $sql = "REPLACE INTO x2_role_to_permission (`roleId`, `fieldId`, `permission`) VALUES ";
        $editBindParams = AuxLib::bindArray($permissions['edit'], 'edit_');
        foreach (array_keys($editBindParams) as $bind) {
            $sql.="\n    ({$this->id}, $bind, 2),";
        }
        $viewBindParams = AuxLib::bindArray($permissions['view'], 'view_');
        foreach (array_keys($viewBindParams) as $bind) {
            $sql.="\n    ({$this->id}, $bind, 1),";
        }
        $noneBindParams = AuxLib::bindArray($permissions['none'], 'none_');
        foreach (array_keys($noneBindParams) as $bind) {
            $sql.="\n    ({$this->id}, $bind, 0),";
        }
        $sql = substr($sql, 0, -1) . ';';
        $cmd = Yii::app()->db->createCommand()
                ->setText($sql);

        $cmd->execute(array_merge($editBindParams, $viewBindParams, $noneBindParams));
    }

}
