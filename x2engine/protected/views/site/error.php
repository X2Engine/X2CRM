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

$errorTitle = Yii::t('app','Error {code}',array('{code}'=>$code));
$this->pageTitle=Yii::app()->name . ' - ' . $errorTitle;
?>
<h1 style="font-weight:bold;color:#f00;"><?php echo Yii::t('app','Oops!'); ?></h1>
<div class="form" style="width:600px;">
    <?php echo Yii::t('app','It looks like the application ran into an unexpected error.');?>
    <br><br>
    <?php echo Yii::t('app','We apologize for the inconvenience and would like to do our best to fix this issue.  If you would like to make a post on our forums we can actively interact with you in getting this resolved.  If not, simply sending the error report helps us immensely and will only improve the quality of the software. Thanks!');?>
</div>
<h2><?php echo Yii::t('app','Send Error Report');?></h2>
<div id="error-form" class="form" style="width:600px;">
    <?php echo Yii::t('app',"Here's a quick list of what will be included in the report:");?><br><br>
    <b><?php echo Yii::t('app','Error Code:');?></b> <?php echo $code; ?><br>
    <b><?php echo Yii::t('app','Error Message:');?></b> <?php echo CHtml::encode($message);?><br>
    <b><?php echo Yii::t('app','Stack Trace:');?> </b> <a href="#" id="toggle-trace" style="text-decoration:none;">[<?php echo Yii::t('app','click to toggle display');?>]</a><br><div id="stack-trace" style="display:none;"><?php echo $trace;?></div>
    <b><?php echo Yii::t('app','X2CRM Version:');?> </b> <?php echo $x2version; ?><br>
    <b><?php echo Yii::t('app','PHP Version:');?> </b> <?php echo $phpversion;?><br><br>
    <label><?php echo Yii::t('app','Email Address (optional)');?></label><?php echo CHtml::textField('email','',array('size'=>40)); ?><br><br>
    <label><span ><?php echo Yii::t('app','Include phpinfo()? (optional, but recommended)');?> <a href="#" style="text-decoration:none;" class="x2-hint" title="<?php echo Yii::t('app',"Detailed server and PHP configuration information that is very helpful for debugging purposes.  However, it can contain sensitive information about your server's configuration, and it is not required to be sent with the report.  We do however, highly recommend it.");?>">[?]</a></span></label>
    <?php echo CHtml::checkBox('phpinfo',true); ?><br><br>
    <b><?php echo Yii::t('app','Please Note:');?> </b><?php echo Yii::t('app','Any information in the $_GET or $_POST arrays included with the request will also be sent with the report.');?>
</div>
<a href="#" id="error-report-link" class="x2-button highlight"><?php echo Yii::t('app','Send Error Report');?></a>
<span id="loading-text" style="display:none;"><img src="<?php echo Yii::app()->theme->getBaseUrl(); ?>/images/loading.gif" /><?php echo Yii::t('app','Sending...');?></span>
<span id="sent-text" style="display:none;color:green;"><?php echo Yii::t('app','Error report sent!');?></span>

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
        $('#error-report-link').hide();
        $('#loading-text').show();
        var email=$('#email').val();
        $.ajax({
            url:'<?php echo $this->createUrl('/site/sendErrorReport'); ?>',
            type:'POST',
            data:{'report':data,'email':email},
            success:function(){
                $('#loading-text').hide();
                $('#sent-text').show();
            }
        });
    });
</script>
