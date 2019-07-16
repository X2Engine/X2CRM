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
<div class="page-title"><h2><?php echo Yii::t('admin','Revert Package');?></h2></div>

<div class="form" style="width:600px;">
    <?php echo Yii::t('admin','To begin reverting this package, click the button below and wait '.
            'for the completion message. Note that if a package contained default fields that '.
            'were modified, they will not be removed.'); ?>
    <br><br>
    <?php echo Yii::t('admin','Package: '); ?><strong><?php echo $package['name'];?></strong>
    <br>
    <?php echo Yii::t('admin','Records to be Deleted: '); ?><strong><?php echo $package['count']; ?></strong>
    <br>
    <?php echo Yii::t('admin','Modules to be Deleted: '); ?><strong><?php echo (empty($package['modules'])) ? Yii::t('admin', 'None') : implode(',', $package['modules']); ?></strong>
    <br>
    <?php echo Yii::t('admin','Roles to be Deleted: '); ?><strong><?php echo (empty($package['roles'])) ? Yii::t('admin', 'None') : implode(',', $package['roles']); ?></strong>
    <br>
    <?php echo Yii::t('admin','Media to be Deleted: '); ?><strong><?php echo (empty($package['media'])) ? Yii::t('admin', 'None') : implode(',', $package['media']); ?></strong>
    <br>
    <br><br>
    <?php echo CHtml::link('Begin Revert','#',array('id'=>'revert-link','class'=>'x2-button'));?>
    <?php echo CHtml::link('Back','packager',array('id'=>'back-link','class'=>'x2-button'));?>
</div>
<div class="form" style="width:600px;color:green;display:none;" id="status-box">

</div>
<?php 
Yii::app()->clientScript->registerScript('pkg-revert', '
    var models='.CJSON::encode($typeArray).';
    var importId='.$package['importId'].';
    var stages=new Array("tags","relationships","actions","records","import");
    var throbber;

    var rollbackStage = function(model, stage) {
        $.ajax({
            url:"rollbackStage",
            type:"GET",
            data:{model:models[model],stage:stages[stage],importId:importId},
            success:function(data){
                if(stages[stage]=="import"){
                    $("#status-box").append("<br>"+data+" <b>"+models[model]+"</b> successfully removed.");
                }
                if(model<models.length){
                    if(stage<stages.length-1){
                        rollbackStage (model,stage+1);
                    }else{
                        if(model!=models.length-1){
                            rollbackStage (model+1,0);
                        }else{
                            finishRevert();
                        }
                    }
                }else{
                    finishRevert();
                }
            }
        });
    };

    var finishRevert = function() {
        $.ajax({
            url: "'.$this->createUrl ('finishPackageRevert', array('name' => $package['name'])).'",
            type: "post",
            success: function() {
                $("#status-box").append("<br><br><b>'.Yii::t('admin', 'Finished reverting package').'</b>");
                $("#revert-link").hide();
                throbber.remove();
                alert("'. Yii::t('admin', 'Finished!').'");
            }
        });
    };

    $("#revert-link").click(function(e){
        e.preventDefault();
        $("#status-box").show();
        $("#status-box").append("'.Yii::t('admin', 'Beginning to revert package...').'<br />");
        throbber = auxlib.pageLoading();

        $.ajax({
            url: "'.$this->createUrl ('beginPackageRevert', array('name' => $package['name'])).'",
            type: "post",
            success: function() {
                $("#status-box").append("'.Yii::t('admin', 'Finished removing modules and media...').'<br />");
                if ('.(is_numeric($package['count']) ? $package['count'] : 0).' > 0) {
                    $("#status-box").append("'.Yii::t('admin', 'Beginning to rollback records...').'<br />");
                    rollbackStage(0,0);
                } else {
                    finishRevert();
                }
            }
        });

    });
', CClientScript::POS_READY);
