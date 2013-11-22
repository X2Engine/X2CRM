<?php
/* * *******************************************************************************
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
 * ****************************************************************************** */

$errorTitle = Yii::t('app', 'Error {code}', array('{code}' => $code));
$this->pageTitle = Yii::app()->name.' - '.$errorTitle;
?>
<div class="page-title">
    <h2><?php echo $errorTitle; ?></h2>
</div>
<div class="error form">
    <?php echo CHtml::encode($message); ?>
    <br><br>
    <?php
    if($code == '404'){
        echo Yii::t('app', 'You have made an invalid request, please do not repeat this.');
    }
    if($code='400' && isset($referer)){
        echo Yii::t('app','If this happened by clicking a Delete button on a Grid, just go back to that page and it should work now. This is a known issue we are working to fix.');
    }
    ?>
</div>
