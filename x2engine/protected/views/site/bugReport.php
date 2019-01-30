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
<h1 id='bug-report-header'><?php echo Yii::t('app','Bug Report Form'); ?></h1>
<div class="form" style="width:600px;">
    <?php echo Yii::t('app','This is the form to manually report bugs.'); ?>
    <br><br>
    <?php echo Yii::t('app',"If you have a bug to report that's not caused by an actual error (which would render the Error Report Form) please fill out any information you can give us and hit \"Send.\" We'll look into the issue and if you also include your email address we'll get back to you as soon as possible. Thanks!");?>
</div>
<h2><?php echo Yii::t('app','Send Bug Report');?></h2>
<div id="error-form" class="form" style="width:600px;">
    <?php echo Yii::t('app',"Here's a quick list of what will be included in the report:");?><br><br>
    <label><?php echo Yii::t('app','Email Address');?></label><?php echo CHtml::textField('email','',array('size'=>40)); ?><br>
    <label><?php echo Yii::t('app','Bug Description');?></label><?php echo CHtml::textArea('bugDescription','',array('style'=>'height:100px;')); ?>
    <b><?php echo Yii::t('app','X2Engine Version:');?></b> <?php echo $x2version; ?><br>
    <b><?php echo Yii::t('app','PHP Version:');?></b> <?php echo $phpversion;?><br><br>
    <label><span><?php echo Yii::t('app','Include phpinfo()? (optional, but recommended)');?> <a href="#" style="text-decoration:none;" class="x2-hint" title="<?php echo Yii::t('app','Detailed server and PHP configuration information that is very helpful for debugging purposes.  However, it can contain sensitive information about your server\'s configuration, and it is not required to be sent with the report.  We do however, highly recommend it.');?>">[?]</a></span></label>
    <?php echo CHtml::checkBox('phpinfo',true); ?><br><br>
</div>
<a href="#" id="error-report-link" class="x2-button highlight"><?php echo Yii::t('app','Send Bug Report');?></a>
<span id="loading-text" style="display:none;"><img src="<?php echo Yii::app()->theme->getBaseUrl(); ?>/images/loading.gif" /><?php echo Yii::t('app','Sending...');?></span>
<span id="sent-text" style="display:none;color:green;"><?php echo Yii::t('app','Bug report sent!');?></span>

<script>
    var errorReport="<?php echo addslashes($errorReport); ?>";
    var phpInfoErrorReport="<?php echo addslashes($phpInfoErrorReport); ?>";
    $('#toggle-trace').click(function(e){
        e.preventDefault();
        $('#stack-trace').toggle();
        if($('#stack-trace').is(":visible")){
            $('#error-form').css({'width':'95%'});
        }else{
            $('#error-form').css({'width':'600px'});
        }
    });
    $('#error-report-link').click(function(e){
        e.preventDefault();
        if($('#phpinfo').attr('checked')=='checked'){
            data=phpInfoErrorReport;
        }else{
            data=errorReport;
        }
        var email=$('#email').val();
        var bugDescription=$('#bugDescription').val();
        $('#error-report-link').hide();
        $('#loading-text').show();
        $.ajax({
            url:'<?php echo $this->createUrl('/site/sendErrorReport'); ?>',
            type:'POST',
            data:{'report':data,'email':email,'bugDescription':bugDescription},
            success:function(){
                $('#loading-text').hide();
                $('#sent-text').show();
            }
        });
    });
</script>
