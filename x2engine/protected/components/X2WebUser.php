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




class X2WebUser extends CWebUser {

    /**
     * Roles that the user currently has
     * @var type
     */
    private $_roles;

    public function checkAccess($operation, $params = array(), $allowCaching = true) {
        return Yii::app()->getAuthManager()->checkAccess($operation, $this->getId(), $params);
    }

    /**
     * Runs the user_login automation trigger
     *
     * @param $fromCookie whether the login was automatic (cookie-based)
     */
    protected function afterLogin($fromCookie) {
        if (!$fromCookie) {
            X2Flow::trigger('UserLoginTrigger', array(
                'user' => $this->getName()
            ));
        }
    }

    /**
     * Runs the user_logout automation trigger
     *
     * @return boolean whether or not to logout
     */
    protected function beforeLogout() {
        X2Flow::trigger('UserLogoutTrigger', array(
            'user' => $this->getName()
        ));
        return parent::beforeLogout();
    }

    /**
     * Retrieves roles for the user
     */
    public function getRoles() {
        if (!isset($this->_roles)) {
            $this->_roles = Roles::getUserRoles($this->getId());
        }
        return $this->_roles;
    }


    /**********************
     * The introduction of portal users has led to necessary changes in how
     * the app deals with guest users. According to the framework definition,
     * a "guest" is simply a user who is not currently logged in. However,
     * assumptions have been (and may continue to be) made that non-guest users are
     * authenticated users. While this dichotomy was always true in the past,
     * it is now no longer always the case. At worst, this assumption could grant
     * portal users functionality intended for authenticated users. In an effort to
     * minimize the effort and number of changes needed to account for portal users,
     * I have decided to include portal users under the general blanket of "guests".
     * This way, developers can continue to use isGuest safely, even if the
     * function name is now slightly less accurate.
     * 
     * From now on, developers should use isLoggedOut rather than isGuest in order to
     * check whether users have been logged out.
     * 
     * - Justin Law
     **********************/

    /**
     * Returns a value indicating whether the user is logged out.
     * Takes functionality of CWebUser's getIsGuest(), which checks
     * if the current user id is null.
     */
    public function getIsLoggedOut() {
        return parent::getIsGuest();
    }

    /**
     * Returns a value indicating whether the user is a "guest" user.
     * Portal users will be treated as guests.
     */
    public function getIsGuest() {
        return $this->getIsPortal() || parent::getIsGuest();
    }

    /**
     * Returns a value indicating whether the user is a portal user.
     */
    public function getIsPortal() {
        if (Yii::app()->edition=='opensource') return false;
        return User::model()->findByPk($this->getId(), 'portal=1') ? true : false;
    }

}
