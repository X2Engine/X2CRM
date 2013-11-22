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

//render home page
$app=Yii::app();
$isGuest=$app->user->isGuest;
$isAdmin = !$isGuest && $app->params->isAdmin;
$isUser = !($isGuest || $isAdmin);
$module = $app->controller->id;

$this->pageTitle = Yii::app()->name . ' - Home';
?>
<div>
    <?php
    if ($isUser || $isAdmin) {
        $menuItems = array(
            array('label' => Yii::t('mobile', 'Activity Feed'), 'url' => array('/mobile/site/activity')),
            array('label' => Yii::t('app', 'Top Contacts'), 'url' => array('/mobile/contacts/index')),
            array('label' => Yii::t('mobile', 'New Record'), 'url' => array('/mobile/contacts/new')),
            array('label' => Yii::t('mobile', 'Find Contacts'), 'url' => array('/mobile/contacts/search')),
            array('label' => Yii::t('mobile', 'People'), 'url' => array('/mobile/site/people')),
            array('label' => Yii::t('mobile', 'Who\'s Online'), 'url' => array('/mobile/site/online')),
        );
    } else {
        $menuItems = array(
            array('label' => Yii::t('app', 'Login'), 'url' => array('/site/login'))
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
    if (!$isGuest) {
        $this->widget('MenuList', array(
            'id' => 'user-menu',
            'items' => $userMenu
        ));
    }
    ?>
</div>
