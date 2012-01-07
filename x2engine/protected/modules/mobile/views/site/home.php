<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

$this->pageTitle = Yii::app()->name . ' - Home';
?>
<div>
    <?php
    //render home page
    $app=Yii::app();
    $isGuest=$app->user->isGuest;
    $isAdmin = !$isGuest && $app->user->getName() == 'admin';
    $isUser = !($isGuest || $isAdmin);
    $module = $app->controller->id;

    if ($isUser || $isAdmin) {
        $menuItems = array(
            array('label' => Yii::t('app', 'Top Contacts'), 'url' => array('contacts/index/')),
            array('label' => Yii::t('app', 'Chat'), 'url' => array('site/chat/')),
            array('label' => Yii::t('mobile', 'New Record'), 'url' => array('contacts/new/')),
            array('label' => Yii::t('mobile', 'Find Contacts'), 'url' => array('contacts/search/')),
            array('label' => Yii::t('mobile', 'More'), 'url' => array('site/more/')),
        );
    } else {
        $menuItems = array(
            array('label' => Yii::t('app', 'Login'), 'url' => array('site/login/'))
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
        array('label' => Yii::t('mobile', 'Logout ({username})', array('{username}' => Yii::app()->user->name)), 'url' => array('site/logout/'), 'left'=>true)
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