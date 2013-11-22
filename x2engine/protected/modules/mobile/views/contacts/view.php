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
$menuItems = array(
            array('label' => Yii::t('app', 'Main Menu'), 'url' => array('/mobile/site/home')),
        );

$this->widget('MenuList', array(
        'id' => 'main-menu',
        'items' => $menuItems
    ));

?>
<br />
<div class="view">

    <b><?php echo CHtml::encode("Name"); ?>:</b>
    <?php echo CHtml::encode($model->firstName." ".$model->lastName); ?>
    <br />
    
    <b><?php echo CHtml::encode("Phone"); ?>:</b>
    <?php echo "<a href='tel:".CHtml::encode($model->phone)."'>".$model->phone."</a>"; ?>
    <br />
    <b><?php echo CHtml::encode("E-Mail");?>:</b>
    <?php echo "<a href='mailto:".CHtml::encode($model->email)."'>".$model->email."</a>"; ?><br />
    
    <b><?php echo CHtml::encode("Address");?>:</b>
    <?php 
    if (isset ($model->address) || isset ($model->city) || isset ($model->state) || 
        isset ($model->zipcode)) 
        echo "$model->address, $model->city, $model->state, $model->zipcode"; 
    ?>
    <br />
    <?php if(isset($data->address))
            echo "<a href='http://maps.google.com/maps?q=$data->address,+$data->city,+$data->state,+$data->zipcode,+$data->country'>Map it!</a>"; ?>

    <?php /*
    <b><?php echo CHtml::encode($data->getAttributeLabel('IM')); ?>:</b>
    <?php echo CHtml::encode($data->IM); ?>
    <br />

    <b><?php echo CHtml::encode($data->getAttributeLabel('website')); ?>:</b>
    <?php echo CHtml::encode($data->website); ?>
    <br />

    <b><?php echo CHtml::encode($data->getAttributeLabel('visibility')); ?>:</b>
    <?php echo CHtml::encode($data->visibility); ?>
    <br />

    */ ?> 

</div>

