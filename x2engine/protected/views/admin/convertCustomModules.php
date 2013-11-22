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
?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Convert Custom Modules'); ?></h2></div>
<div class="form">
    <?php
    if(isset($status) && !empty($status)){
        echo Yii::t('admin', 'Status of Module Conversion')."<br><br>";
        foreach($status as $module => $data){
            if(empty($data['error'])){
                echo "<div style='color:green'>";
                echo Yii::t('admin', "Status for: {title}", array(
                    '{title}' => '<b>'.$data['title'].'</b>'
                ))."<br>";
                echo "<ul>";
                foreach($data['messages'] as $message){
                    echo "<li>".$message."</li>";
                }
                echo "</ul>";
                echo "</div>";
            }else{
                echo "<div style='color:red'>";
                echo Yii::t('admin', "Status for: {title}", array(
                    '{title}' => '<b>'.$data['title'].'</b>'
                ))."<br>";
                echo Yii::t('admin', "Error: {error}", array(
                    '{error}' => $data['error']
                ));
                echo "</div>";
            }
        }
        echo Yii::t('admin', 'All module conversions complete.');
    }else{
        ?>
        <?php echo Yii::t('admin', 'This tool is designed to convert all old custom modules to the latest version.'); ?><br><br>
        <?php echo Yii::t('admin', 'All custom modules created prior to version 3.5.5 are incompatible with the software, and must have the conversion run for them to remain functional. Additionally, all custom modules may optionally have their files updated to bring them into line with the current template files. This will wipe all custom changes to the current files and replace them with a set of default files at the current version. This tool may be used whenever there is an update to the template files to carry those changes over to your custom modules.'); ?>
        <br><br>
        <?php echo Yii::t('admin', 'Please press the button below to continue with the conversion.') ?>
<?php } ?>
</div>
<div class="form">
    <?php
    echo CHtml::beginForm();
    echo CHtml::label('Update Files?', 'updateFlag');
    echo CHtml::dropDownList('updateFlag', 'No', array('No' => Yii::t('admin', 'No'), 'Yes' => Yii::t('admin', 'Yes')));
    echo CHtml::submitButton('Convert Modules', array('class' => 'x2-button'));
    echo CHtml::endForm();
    ?>
</div>
<?php
Yii::app()->clientScript->registerScript('update-flag-change', '
    $("#updateFlag").on("change",function(){
        if($("#updateFlag").val()=="Yes"){
            alert("'.Yii::t('admin', "Please be absolutely sure before selecting this option. All of your changes to custom module files will be lost.").'");
        }
    });
');
?>