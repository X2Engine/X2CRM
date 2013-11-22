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

$this->pageTitle = Yii::app()->name . ' - Profile';
?>
<div>

	<h1 style="text-align: center;"><?php echo $user->name; ?></h1>
	
	<?php if($user->officePhone) { ?>
		<a href="tel:<?php echo $user->officePhone; ?>" data-role="button"><?php echo Yii::t('profile', 'Office Phone'); ?>: <?php echo $user->officePhone; ?></a>
	<?php } ?>
	
	<?php if($user->cellPhone) { ?>
		<a href="tel:<?php echo $user->cellPhone; ?>" data-role="button"><?php echo Yii::t('profile', 'Cell Phone'); ?>: <?php echo $user->cellPhone; ?></a>
	<?php } ?>
	
	<?php if($user->emailAddress) { ?>
		<a href="mailto:<?php echo $user->emailAddress; ?>" data-role="button"><?php echo Yii::t('profile', 'Email'); ?>: <?php echo $user->emailAddress; ?></a>
	<?php } ?>

    <?php
    $menuItems = array();
    $menuItems[] = array('label' => Yii::t('mobile', 'Back'), 'url' => array('/mobile/site/people'), 'left'=>true);

    //render main menu items
    $this->widget('MenuList', array(
        'id' => 'main-menu',
        'items' => $menuItems
    ));
    ?>
</div>
