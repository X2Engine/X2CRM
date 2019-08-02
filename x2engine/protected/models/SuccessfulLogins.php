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






Yii::import ('application.models.X2Model');
/**
 * Model for x2_failed_logins table
 */
class SuccessfulLogins extends CActiveRecord {
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function relations() {
        return array();
    }

    public function behaviors() {
        $max = Yii::app()->settings->maxLoginHistory;
        return array(
            'RecordLimitBehavior' => array(
                'class' => 'application.components.behaviors.RecordLimitBehavior',
                'limit' => $max,
                'timestampField' => 'timestamp',
            ),
        );
    }

    public function tableName() {
        return 'x2_login_history';
    }

    protected function beforeSave() {
        if ($this->isNewRecord) {
            $this->timestamp = time();
        }
        return parent::beforeSave();
    }

    public function getUserLink() {
        $user = User::model()->findByAttributes (array(
            'username' => $this->username,
        ));
        if ($user)
            return CHtml::link ($user->getAlias(), array('/users/view', 'id' => $user->id));
    }

    public function getEmail() {
        $user = User::model()->findByAttributes (array(
            'username' => $this->username,
        ));
        if ($user)
            return $user->emailAddress;
    }

    public function attributeLabels() {
        return array(
            'username' => Yii::t('admin', 'User'),
            'IP' => Yii::t('admin', 'IP Address'),
        );
    }
}

