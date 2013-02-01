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

$errorTitle = Yii::t('app','Error {code}',array('{code}'=>$code));
$this->pageTitle=Yii::app()->name . ' - ' . $errorTitle;
?>
<h1 style="font-weight:bold;color:#f00;"><?php echo Yii::t('app','Oops!'); ?></h1>
<div class="form" style="width:600px;">
    It looks like the application ran into an unexpected error.
    <br><br>
    We apologize for
    the inconvenience and would like to do our best to fix this issue.  If you would like to make a post on our 
    forums we can actively interact with you in getting this resolved.  If not, simply sending the error report
    helps us immensely and will only improve the quality of the software.
    
    Thanks!
</div>
<h2>Send Error Report</h2>
<div id="error-form" class="form" style="width:600px;">
    Here's a quick list of what will be included in the report:<br><br>
    <b>Error Code:</b> <?php echo $code; ?><br>
    <b>Error Message:</b> <?php echo CHtml::encode($message);?><br>
    <b>Stack Trace: </b> <a href="#" id="toggle-trace" style="text-decoration:none;">[click to toggle display]</a><br><div id="stack-trace" style="display:none;"><?php echo $trace;?></div>
    <b>X2CRM Version: </b> <?php echo $x2version; ?><br>
    <b>PHP Version: </b> <?php echo $phpversion;?><br><br>
    <label><span >Include phpinfo()? (optional, but recommended) <a href="#" class="x2-hint" title="Detailed server and PHP configuration information that is very hepful for debugging purposes.  However, it can contain sensitive information about your server's configuration, and it is not required to be sent with the report.  We do however, highly recommend it.">[?]</a></span></label>
    <?php echo CHtml::checkBox('phpinfo',true); ?><br><br>
    <b>Please Note: </b>Any information in the $_GET or $_POST arrays included with the request will
    also be sent with the report.
</div>
<a href="#" id="error-report-link" class="x2-button highlight">Send Error Report</a>
<span id="loading-text" style="display:none;"><img src="<?php echo Yii::app()->theme->getBaseUrl(); ?>/images/loading.gif" />Sending...</span>
<span id="sent-text" style="display:none;color:green;">Bug report sent!</span>

<script>
    var errorReport="<?php echo $errorReport; ?>";
    var phpInfoErrorReport="<?php echo $phpInfoErrorReport; ?>";
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
        $.ajax({
            url:'<?php echo $this->createUrl('site/sendErrorReport'); ?>',
            type:'POST',
            data:{'report':data},
            success:function(){
                $('#loading-text').hide();
                $('#sent-text').show();
            }
        });
    });
</script>
