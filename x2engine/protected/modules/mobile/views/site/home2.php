<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

$this->pageTitle = Yii::app()->settings->appName . ' - Home';
?>
<div>
    <?php
    //render home page
    $app=Yii::app();
    $isGuest=$app->user->isGuest;
    $isAdmin = !$isGuest && $app->params->isAdmin;
    $isUser = !($isGuest || $isAdmin);
    $module = $app->controller->id;

    if ($isUser || $isAdmin) {
        $menuItems = array(
            array('label' => Yii::t('mobile', 'Who\'s Online'), 'url' => array('/mobile/site/online')),
            array('label' => Yii::t('mobile', 'Back'), 'url' => array('/mobile/site/index'), 'left'=>true),
        );
    } else {
        $menuItems = array(
            array('label' => Yii::t('app', 'Login'), 'url' => array('/mobile/site/login'))
        );
    }

    //check if menu has too many items to fit nicely
    $menuItemCount = count($menuItems);
    if ($menuItemCount > 6) {
        $moreMenuItems = array();
        //move the last few menu items into the "More" dropdown
        for ($i = 0; $i < $menuItemCount - 5; $i++) {
            array_unshift($moreMenuItems, array_pop($menuItems));
        }
        //add "More" to main menu
        array_push($menuItems, array('label' => Yii::t('app', 'More'), 'items' => $moreMenuItems));
    }

    $userMenu = array(
        array('label' => Yii::t('mobile', 'Logout ({username})', array('{username}' => Yii::app()->user->name)), 'url' => array('/mobile/site/logout'), 'left'=>true)
    );

    //render main menu items
    $this->widget('MenuList', array(
        'id' => 'main-menu',
        'items' => $menuItems
    ));
    //render user menu items if logged in
    if (!($isGuest || $isAdmin)) {
        $this->widget('MenuList', array(
            'id' => 'user-menu',
            'items' => $userMenu
        ));
    }
    ?>
</div>
