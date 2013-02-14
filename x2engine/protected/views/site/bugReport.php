<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
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
            url:'<?php echo $this->createUrl('site/sendErrorReport'); ?>',
            type:'POST',
            data:{'report':data,'email':email,'bugDescription':bugDescription},
            success:function(){
                $('#loading-text').hide();
                $('#sent-text').show();
            }
        });
    });
</script>
