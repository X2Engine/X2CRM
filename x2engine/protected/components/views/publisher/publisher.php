<?php
/*********************************************************************************
 * Copyright (C) 10011-10014 X2Engine Inc. All Rights Reserved.
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

$form = $this->beginWidget('CActiveForm', array('id' => 'publisher-form')); 

function echoTabRow ($tabs, $rowNum=1) {
    ?> 
        <ul id='publisher-tabs-row-<?php echo $rowNum; ?>' style='display: none;'>
            <?php 
            // Publisher tabs
            foreach ($tabs as $tab) {
                ?> <li> <?php
                $tab->renderTitle ();
                ?> </li> <?php
            }
            ?>
        </ul>
    <?php    
}

?>

<div id="publisher">
    <?php
    $tabsTmp = $tabs;
    if (sizeof ($tabs) > 4) {
        $rowNum = 0;
        while (sizeof ($tabsTmp)) {
            $tabRow = array_slice ($tabsTmp, 0, 3);
            echoTabRow ($tabRow, ++$rowNum);
            $tabsTmp = array_slice ($tabsTmp, 3);
        }
    } else {
        echoTabRow ($tabsTmp);
    }
    ?>
    <div class="form x2-layout-island">
    <?php
    // Publisher tab content 
    foreach ($tabs as $tab) {
        $tab->renderTab (array (
            'form' => $form,
            'model' => $model,
            'associationType' => $associationType,
        ));
    }
    if(Yii::app()->user->isGuest){ 
    ?>
        <div class="row">
            <?php
            $this->widget('CCaptcha', array(
                'captchaAction' => '/actions/actions/captcha',
                'buttonOptions' => array(
                    'style' => 'display:block;',
                ),
            ));
            ?>
            <?php echo $form->textField($model, 'verifyCode'); ?>
        </div>
    <?php 
    } 
    echo CHtml::hiddenField('SelectedTab', ''); // currently selected tab  
    if ($associationType !== 'calendar') {
        echo $form->hiddenField($model, 'associationType'); 
        echo $form->hiddenField($model, 'associationId'); 
    }
    ?>
    <div class='row'>
        <input type='submit' value='Save' id='save-publisher' class='x2-button'>
    </div>
    </div>
</div>

<?php $this->endWidget(); ?>
