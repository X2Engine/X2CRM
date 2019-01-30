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
 * This is the model class for table "x2_users".
 *
 * @property string $alias The user's alias, if set, or username otherwise. The
 *  user's alias is the "human-friendly" username that the user can configure to
 *  be whatever they choose. The username, however, cannot be changed, as there
 *  are references to it everywhere.
 * @property string $fullName The full name of the user, using the format defined
 *  in the general application settings.
 * @package application.modules.users.models
 */
class User extends X2ActiveRecord {

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public static $recentItemTypes = array(
        'a' => 'Accounts',
        'b' => 'BugReports',
        'c' => 'Contacts',
        'd' => 'Docs',
        'f' => 'X2Flow',
        'g' => 'Groups',
        'l' => 'X2Leads',
        'm' => 'Media',
        'o' => 'Opportunity',
        'p' => 'Campaign',
        'q' => 'Quote',
        'r' => 'Product',
        's' => 'Services',
        't' => 'Actions',
        'u' => 'Reports',
        'w' => 'Workflow',
    );

    /**
     * Full name (cached value)
     * 
     * @var type
     */
    private $_fullName;

    /**
     * @var bool If true, grid views displaying models of this type will have their filter and
     *  sort settings saved in the database instead of in the session
     */
    public $dbPersistentGridSettings = false;

    /**
     * Returns the static model of the specified AR class.
     * @return User the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_users';
    }

    /**
     * Gets user behaviors
     * 
     * @return type
     */
    public function behaviors() {
        $viewRoute = '/profile';
        if (!Yii::app()->params->isMobileApp) {
            $viewRoute .= '/view';
        }
        return array_merge(parent::behaviors(), array(
            'LinkableBehavior' => array(
                'class' => 'LinkableBehavior',
                'module' => 'users',
                'viewRoute' => $viewRoute,
            ),
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.behaviors.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
            'MappableBehavior' => array(
                'class' => 'application.components.behaviors.MappableBehavior',
            ),
        ));
    }

    /**
     * Automatically sets the user alias to the username when a user is created.
     */
    public function beforeValidate() {
        if ($this->scenario === 'insert') {
            if ($this->userAlias === null) {
                $this->userAlias = $this->username;
            }
        }
        return parent::beforeValidate();
    }

    /**
     * Defines user rules
     * 
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        $userRules = array(
            array('status', 'required'),
            array('password', 'required', 'on' => 'insert'),
            array('firstName, lastName, username', 'required', 'except' => 'invite'),
            array('userAlias', 'required', 'except' => 'invite'),
            array('status, lastLogin, login', 'numerical', 'integerOnly' => true),
            array('firstName, username, userAlias, title, updatedBy', 'length', 'max' => 20),
            array('lastName, department, officePhone, cellPhone, homePhone', 'length', 'max' => 40),
            array('password, address, emailAddress, recentItems, topContacts', 'length', 'max' => 100),
            array('lastUpdated', 'length', 'max' => 30),
            array('userKey', 'length', 'max' => 32, 'min' => 3),
            array('backgroundInfo', 'safe'),
            array('status', 'validateUserDisable'),
            array('username', 'in', 'not' => true, 'range' => array('Guest', 'Anyone', Profile::GUEST_PROFILE_USERNAME), 'message' => Yii::t('users', 'The specified username is reserved for system usage.')),
            array('username', 'unique', 'allowEmpty' => false),
            array('userAlias', 'unique', 'allowEmpty' => false),
            array('userAlias', 'match', 'pattern' => '/^\s+$/', 'not' => true),
            array(
                'userAlias',
                'match',
                'pattern' => '/^((\s+\S+\s+)|(\s+\S+)|(\S+\s+))$/',
                'not' => true,
                'message' => Yii::t(
                        'users', 'Username cannot contain trailing or leading whitespace.'),
            ),
            array('username,userAlias', 'userAliasUnique'),
            array('username', 'match', 'pattern' => '/^\d+$/', 'not' => true), // No numeric usernames. That will break association with groups.
            array('username', 'match', 'pattern' => '/^\w+$/'), // Username must be alphanumerics/underscores only
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, firstName, lastName, username, password, title, department, officePhone, cellPhone, homePhone, address, backgroundInfo, emailAddress, status, lastUpdated, updatedBy, recentItems, topContacts, lastLogin, login', 'safe', 'on' => 'search'),
        );

        $passwordRule = array('password', 'application.components.X2PasswordValidator', 'on' => 'insert,update');
        $passwordRequirements = Yii::app()->settings->passwordRequirements;
        $passwordRule['min'] = $passwordRequirements['minLength'];
        $passwordRule['requireNumeric'] = $passwordRequirements['requireNumeric'];
        $passwordRule['requireMixedCase'] = $passwordRequirements['requireMixedCase'];
        $passwordRule['requireSpecial'] = $passwordRequirements['requireSpecial'];
        $passwordRule['requireCharClasses'] = $passwordRequirements['requireCharClasses'];
        $userRules[] = $passwordRule;

        return $userRules;
    }

    /**
     * Gets array of user relations
     * 
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'profile' => array(self::HAS_ONE, 'Profile', 'id'),
        );
    }

    /**
     * Gets user scopes
     * 
     * @return type
     */
    public function scopes() {
        return array(
            'active' => array(
                'condition' => 'status=1',
                'order' => 'lastName ASC',
            ),
        );
    }

    /**
     * Returns the attribute labels.
     * 
     * @return array attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => Yii::t('users', 'ID'),
            'firstName' => Yii::t('users', 'First Name'),
            'lastName' => Yii::t('users', 'Last Name'),
            'username' => Yii::t('users', 'Username'),
            'userAlias' => Yii::t('users', 'Username'),
            'password' => Yii::t('users', 'Password'),
            'title' => Yii::t('users', 'Title'),
            'department' => Yii::t('users', 'Department'),
            'officePhone' => Yii::t('users', 'Office Phone'),
            'cellPhone' => Yii::t('users', 'Cell Phone'),
            'homePhone' => Yii::t('users', 'Home Phone'),
            'address' => Yii::t('users', 'Address'),
            'backgroundInfo' => Yii::t('users', 'Background Info'),
            'emailAddress' => Yii::t('users', 'Email'),
            'status' => Yii::t('users', 'Status'),
            'updatePassword' => Yii::t('users', 'Update Password'),
            'lastUpdated' => Yii::t('users', 'Last Updated'),
            'updatedBy' => Yii::t('users', 'Updated By'),
            'recentItems' => Yii::t('users', 'Recent Items'),
            'topContacts' => Yii::t('users', 'Top Contacts'),
            'userKey' => Yii::t('users', 'API Key'),
        );
    }

    /**
     * Sets create time before save
     * 
     * @return type
     */
    public function beforeSave() {
        if ($this->isNewRecord) {
            $this->createDate = time();
        }
        return parent::beforeSave();
    }

    /**
     * Delete associated group to user records 
     */
    public function beforeDelete() {
        $adminUser = User::model()->findByPk(1);
        if (!$adminUser) {
            throw new CException(Yii::t('app', 'admin user could not be found'));
        }

        $params = array(
            ':username' => $this->username,
            ':adminUsername' => $adminUser->username
        );

        // reassign associated actions
        Yii::app()->db->createCommand("
            UPDATE x2_actions 
            SET updatedBy=:adminUsername
            WHERE assignedTo=:username AND updatedBy=:username
        ")->execute($params);
        Yii::app()->db->createCommand("
            UPDATE x2_actions 
            SET completedBy=:adminUsername
            WHERE assignedTo=:username AND completedBy=:username
        ")->execute($params);
        Yii::app()->db->createCommand("
            UPDATE x2_actions 
            SET assignedTo='Anyone'
            WHERE assignedTo=:username
        ")->execute(array(
            ':username' => $this->username
        ));

        // reassign related contacts to anyone
        Yii::app()->db->createCommand("
            UPDATE x2_contacts 
            SET updatedBy=:adminUsername
            WHERE assignedTo=:username AND updatedBy=:username
        ")->execute($params);
        Yii::app()->db->createCommand("
            UPDATE x2_contacts 
            SET assignedTo='Anyone'
            WHERE assignedTo=:username
        ")->execute(array(
            ':username' => $this->username
        ));

        return parent::beforeDelete();
    }

    /**
     * Cleans up rest of user data after delete
     */
    public function afterDelete() {
        // delete related social records (e.g. notes)
        $socialByUsernames = Social::model()->findAllByAttributes(array('user' => $this->username));
        foreach ($socialByUsernames as $socialItem) {
            $socialItem->delete();
        }

        $socialByAssociation = Social::model()->findAllByAttributes(array('associationId' => $this->id));
        foreach ($socialByAssociation as $socialItem) {
            $socialItem->delete();
        }

        X2Calendar::model()->deleteAllByAttributes(array('createdBy' => $this->username));

        X2CalendarPermissions::model()->deleteAllByAttributes(
                array(), 'userId=:userId', array(':userId' => $this->id)
        );

        // delete profile
        $prof = Profile::model()->findByAttributes(array('username' => $this->username));
        if ($prof) {
            $prof->delete();
        }

        // delete associated events
        Yii::app()->db->createCommand()
                ->delete('x2_events', "user=:username OR (type='feed' AND associationId=" . $this->id . ")", array(':username' => $this->username));

        // Delete associated group to user records 
        GroupToUser::model()->deleteAll(array(
            'condition' => 'userId=' . $this->id
        ));
        parent::afterDelete();
    }

    /**
     * Checks if user has role
     * 
     * @param type $user
     * @param type $role
     * @return boolean
     */
    public static function hasRole($user, $role) {
        if (is_numeric($role)) {
            $lookup = RoleToUser::model()->findByAttributes(array('userId' => $user, 'roleId' => $role));
            return isset($lookup);
        } else {
            $roleRecord = Roles::model()->findByAttributes(array('name' => $role));
            if (isset($roleRecord)) {
                $lookup = RoleToUser::model()->findByAttributes(array('userId' => $user, 'roleId' => $roleRecord->id));
                return isset($lookup);
            } else {
                return false;
            }
        }
    }

    /**
     * Gets ids of groups to which this user belongs
     * 
     * @return array
     */
    public function getGroupIds() {
        $results = Yii::app()->db->createCommand()
                ->select('groupId')
                ->from('x2_group_to_user')
                ->where('userId=:id', array(':id' => $this->id))
                ->queryAll();
        return array_map(function ($a) {
            return $a['groupId'];
        }, $results);
    }

    /**
     * Gets model of the current user 
     * 
     * @return object
     */
    public static function getMe() {
        return User::model()->findByPk(Yii::app()->getSuId());
    }

    /**
     * Gets data provider of users
     * 
     * @return \CActiveDataProvider
     */
    public static function getUsersDataProvider() {
        $usersDataProvider = new CActiveDataProvider('User', array(
            'criteria' => array(
                'condition' => 'status=1',
                'order' => 'lastName ASC'
            )
        ));
        return $usersDataProvider;
    }

    /**
     * @return array (<username> => <full name>)
     */
    public static function getUserOptions() {
        $userOptions = Yii::app()->db->createCommand("
            select username, concat(firstName, ' ', lastName) as fullName
            from x2_users
            where status=1
            order by lastName asc
        ")->queryAll();
        return array_combine(
                array_map(function ($row) {
                    return $row['username'];
                }, $userOptions), array_map(function ($row) {
                    return $row['fullName'];
                }, $userOptions)
        );
    }

    /**
     * Gets an array of names for an assignment dropdown menu
     * 
     * @return type
     */
    public static function getNames() {
        $userNames = array();
        $userModels = self::model()->findAllByAttributes(array('status' => 1));
        $userNames = array_combine(
                array_map(function($u) {
                    return $u->username;
                }, $userModels), array_map(function($u) {
                    return $u->getName();
                }, $userModels)
        );

        natcasesort($userNames);

        return array('Anyone' => Yii::t('app', 'Anyone')) + $userNames;
    }

    /**
     * Gets an array of user ids for an assignment dropdown menu
     * 
     * @return type
     */
    public static function getUserIds() {
        $userNames = array();
        $query = Yii::app()->db->createCommand()
                ->select('id, CONCAT(firstName," ",lastName) AS name')
                ->from('x2_users')
                ->where('status=1')
                ->order('name ASC')
                ->query();

        while (($row = $query->read()) !== false) {
            $userNames[$row['id']] = $row['name'];
        }
        natcasesort($userNames);

        return array('' => Yii::t('app', 'Anyone')) + $userNames;
    }

    /**
     * Gets user id of user given a username
     * 
     * @staticvar array $cache
     * @param type $username
     * @return type
     */
    public static function getUserId($username) {
        static $cache = array();
        if (!$cache) {
            $records = Yii::app()->db->createCommand()
                    ->select('id, username')
                    ->from('x2_users')
                    ->where('status=1')
                    ->query();
            foreach ($records as $record) {
                $cache[$record['username']] = $record['id'];
            }
        }
        if (isset($cache[$username])) {
            return $cache[$username];
        }
    }

    /**
     * Gets user full name
     * 
     * @return type
     */
    public function getName() {
        if (!isset($this->_fullName)) {
            $this->_fullName = Formatter::fullName($this->firstName, $this->lastName);
        }
        return $this->_fullName;
    }

    /**
     * Gets user profiles
     * 
     * @return string
     */
    public static function getProfiles() {
        $arr = X2Model::model('User')->findAll('status="1"');
        $names = array('0' => Yii::t('app', 'All'));
        foreach ($arr as $user) {
            $names[$user->id] = $user->firstName . " " . $user->lastName;
        }
        return $names;
    }

    /**
     * Gets most recent users
     * 
     * @param type $filter
     * @return type
     */
    public static function getRecentItems($filter = null) {
        $userRecord = X2Model::model('User')->findByPk(Yii::app()->user->getId());

        //get array of type-ID pairs
        $recentItemsTemp = empty($userRecord->recentItems) ?
                array() : explode(',', $userRecord->recentItems);
        $recentItems = array();

        //get record for each ID/type pair
        $validAbbreviations = array_keys(self::$recentItemTypes);
        foreach ($recentItemsTemp as $item) {
            $itemType = strtok($item, '-');
            $itemId = strtok('-');
            if (in_array($itemType, $validAbbreviations)) {
                $recordType = self::$recentItemTypes[$itemType];
                $record = $recordType::model()->findByPk($itemId);

                if (!is_null($record)) {//only include item if the record ID exists
                    array_push($recentItems, array('type' => $itemType, 'model' => $record));
                }
            }
        }
        if (is_callable($filter)) {
            $recentItems = array_filter($recentItems, $filter);
        }
        return $recentItems;
    }
    
    public function getRecentLogin() {
        $userId = Yii::app()->params->profile->id;
        $loginRecord = Locations::model()->findBySql('SELECT * FROM' .
                ' x2_locations WHERE type="login" AND recordType="User" AND recordId=' . $this->id .
                ' ORDER BY createDate DESC LIMIT 1');
        return $loginRecord;
    }

    /**
     * Gets top bookmarked contacts
     * 
     * @return array
     */
    public static function getTopContacts() {
        Yii::import('application.components.leftWidget.TopContacts');
        return TopContacts::getBookmarkedRecords();
    }

    /**
     * @param string $type recent item abbreviation (for backward compatibility)  or model type 
     * @param int record id
     * @param int userId id of user that recent item should be added to
     */
    public static function addRecentItem($type, $itemId, $userId = null) {
        if ($userId === null) {
            $userId = Yii::app()->user->getId();
        }
        if (in_array($type, self::$recentItemTypes)) {
            $validRecordTypes = array_flip(self::$recentItemTypes);
            $type = $validRecordTypes[$type];
        }

        if (in_array($type, array_keys(self::$recentItemTypes))) {
            $newItem = $type . '-' . $itemId;
            $userRecord = X2Model::model('User')->findByPk($userId);
            //create an empty array if recentItems is empty
            $recentItems = ($userRecord->recentItems == '') ?
                    array() : explode(',', $userRecord->recentItems);
            $existingEntry = array_search($newItem, $recentItems); //check for a pre-existing entry
            if ($existingEntry !== false)        //if there is one,
                unset($recentItems[$existingEntry]);    //remove it
            array_unshift($recentItems, $newItem);    //add new entry to beginning

            while (count($recentItems) > 10) { //now if there are more than 10 entries,
                array_pop($recentItems);  //remove the oldest ones
            }
            $userRecord->setAttribute('recentItems', implode(',', $recentItems));
            $userRecord->update(array('recentItems'));
        }
    }

    /**
     * Generates a link or list of links to a user or group.
     *
     * @param integer|array|string $users If array, links to a group; if integer, the group whose 
     *  ID is that value; if keyword "Anyone", not a link but simply displays "anyone".
     * @param boolean $makeLinks Can be set to False to disable creating links but still return the name of the linked-to object
     * @return string The rendered links
     */
    public static function getUserLinks($users, $makeLinks = true, $useFullName = true) {
        $makeGroupLinks = (Yii::app()->params->isMobileApp) ? false : $makeLinks;

        if (!is_array($users)) {
            /* x2temp */
            if (preg_match('/^\d+$/', $users)) {
                $group = Groups::model()->findByPk($users);
                if (isset($group) && $makeGroupLinks) {
                    $link = CHtml::link($group->name, Yii::app()->controller->createAbsoluteUrl('/groups/groups/view', array('id' => $group->id)), array('style' => 'text-decoration:none;'));
                } else if (isset($group) && !$makeGroupLinks) {
                    $link = $group->name;
                } else {
                    $link = '';
                }
                return $link;
            }
            /* end x2temp */
            if ($users == '' || $users == 'Anyone') {
                return Yii::t('app', 'Anyone');
            }

            $users = explode(', ', $users);
        }
        $links = array();
        $userCache = Yii::app()->params->userCache;

        foreach ($users as $user) {
            if ($user == 'Email') {  // skip these, they aren't users
                continue;
            } elseif ($user == 'Anyone') {
                $links[] = Yii::t('app', 'Anyone');
            } else if (is_numeric($user)) {  // this is a group
                if (isset($userCache[$user])) {
                    $group = $userCache[$user];
                    $links[] = $makeGroupLinks ? CHtml::link($group->name, Yii::app()->controller->createAbsoluteUrl('/groups/groups/view', array('id' => $group->id)), array('style' => 'text-decoration:none;')) : $group->name;
                } else {
                    $group = Groups::model()->findByPk($user);
                    if (isset($group)) {
                        $groupLink = $makeGroupLinks ? CHtml::link($group->name, Yii::app()->controller->createAbsoluteUrl('/groups/groups/view', array('id' => $group->id)), array('style' => 'text-decoration:none;')) : $group->name;
                        $userCache[$user] = $group;
                        $links[] = $groupLink;
                    }
                }
            } else {
                if (isset($userCache[$user])) {
                    $model = $userCache[$user];
                    $linkText = $useFullName ? $model->fullName : $model->getAlias();
                    $userLink = $makeLinks ? $model->getLink(array('style' => 'text-decoration:none;')) : $linkText;
                    $links[] = $userLink;
                } else {
                    $model = X2Model::model('User')->findByAttributes(array('username' => $user));
                    if (isset($model)) {
                        $linkText = $useFullName ? $model->fullName : $model->getAlias();
                        $userLink = $makeLinks ? $model->getLink(array('style' => 'text-decoration:none;')) : $linkText;
                        $userCache[$user] = $model;
                        $links[] = $userLink;
                    }
                }
            }
        }
        Yii::app()->params->userCache = $userCache;
        return implode(', ', $links);
    }

    /**
     * Gets user emails
     * 
     * @return type
     */
    public static function getEmails() {
        $userArray = User::model()->findAllByAttributes(array('status' => 1));
        $emails = array('Anyone' => Yii::app()->params['adminEmail']);
        foreach ($userArray as $user) {
            $emails[$user->username] = $user->emailAddress;
        }
        return $emails;
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * 
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('firstName', $this->firstName, true);
        $criteria->compare('lastName', $this->lastName, true);
        $criteria->compare('username', $this->username, true);
        $criteria->compare('password', $this->password, true);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('department', $this->department, true);
        $criteria->compare('officePhone', $this->officePhone, true);
        $criteria->compare('cellPhone', $this->cellPhone, true);
        $criteria->compare('homePhone', $this->homePhone, true);
        $criteria->compare('address', $this->address, true);
        $criteria->compare('backgroundInfo', $this->backgroundInfo, true);
        $criteria->compare('emailAddress', $this->emailAddress, true);
        $criteria->compare('status', $this->status);
        $criteria->compare('lastUpdated', $this->lastUpdated, true);
        $criteria->compare('updatedBy', $this->updatedBy, true);
        $criteria->compare('recentItems', $this->recentItems, true);
        $criteria->compare('topContacts', $this->topContacts, true);
        $criteria->compare('lastLogin', $this->lastLogin);
        $criteria->compare('login', $this->login);
        $criteria->addCondition('(temporary=0 OR temporary IS NULL)');

        return new SmartActiveDataProvider(get_class($this), array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => Profile::getResultsPerPage(),
            ),
        ));
    }

    /**
     * Validator for usernames and userAliases that enforces uniqueness across
     * both fields.
     *
     * @param type $attribute
     * @param type $params
     */
    public function userAliasUnique($attribute, $params = array()) {
        $otherAttribute = $attribute == 'username' ? 'userAlias' : 'username';
        if (!empty($this->$attribute) &&
                self::model()->exists(
                        (isset($this->id) ? "id != $this->id AND " : '') . "`$otherAttribute` = BINARY :u", array(':u' => $this->$attribute))) {

            $this->addError($attribute, Yii::t('users', 'That name is already taken.'));
        }
    }

    /**
     * Static instance method to find by username or userAlias
     *
     * @param string $name
     */
    public function findByAlias($name) {
        if (empty($name)) {
            return null;
        }
        return self::model()->findBySql('SELECT * FROM `' . $this->tableName() . '` '
                        . 'WHERE `username` = BINARY :n1 OR `userAlias` = BINARY :n2', array(
                    ':n1' => $name,
                    ':n2' => $name
        ));
    }

    /**
     * Gets the user alias if set, and the username otherwise.
     *
     * @param boolean $encode
     */
    public function getAlias() {
        return (empty($this->userAlias)) ? $this->username : $this->userAlias;
    }
    
    /**
     * Returns the full name of the user.
     */
    public function getFullName(){
        if(!isset($this->_fullName)){
            $this->_fullName = Formatter::fullName($this->firstName, $this->lastName);
        }
        return $this->_fullName;
    }

    /**
     * Gets user display name
     * 
     * @param type $plural
     * @param type $ofModule
     * @return type
     */
    public function getDisplayName($plural = true, $ofModule = true) {
        return Yii::t('users', '{user}', array(
                    '{user}' => Modules::displayName($plural, 'Users'),
        ));
    }

    // check if user profile has a list to remember which calendars the user has checked
    // if not, create the list
    public function initCheckedCalendars() {
        // calendar list not initialized?
        if (is_null($this->showCalendars)) {
            $showCalendars = array(
                'userCalendars' => array(),
            );
            $this->showCalendars = CJSON::encode($showCalendars);

            $this->save();
        }
    }

    /**
     * Custom validation rule to ensure the primary admin account cannot be disabled
     */
    public function validateUserDisable() {
        if ($this->status === '0' && $this->id == X2_PRIMARY_ADMIN_ID) {
            $this->addError('status', Yii::t('users', 'The primary admin account cannot be disabled'));
        }
    }

}
