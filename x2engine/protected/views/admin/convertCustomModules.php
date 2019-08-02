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



?>
<div class="page-title"><h2><?php echo Yii::t('admin', 'Convert Custom Modules'); ?></h2></div>
<div class="form">
    <?php
    if(isset($status) && !empty($status)){
        echo Yii::t('admin', 'Status of Module Conversion')."<br><br>";
        $hasError = false;
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
                $hasError = true;
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
        if (!$hasError) echo Yii::t('admin', 'All module conversions complete.');
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
