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
<h1><?php echo Yii::t('app','Bug Report Form'); ?></h1>
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
    <b><?php echo Yii::t('app','X2CRM Version:');?></b> <?php echo $x2version; ?><br>
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
