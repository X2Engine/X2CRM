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



Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/error.css');
$errorTitle = Yii::t('app','Error {code}',array('{code}'=>$code));
$this->pageTitle=Yii::app()->settings->appName . ' - ' . $errorTitle;

?>
<i class="fa fa-exclamation-triangle fa-3x"></i>
<h1 id="h1-error"><?php echo Yii::t('app','Error '.$code); ?></h1>
<div id='x2-php-error' class="form">
    <?php echo Yii::t('app','It looks like the application ran into an unexpected error.');?>
    <br><br>
    <?php echo Yii::t('app','We apologize for the inconvenience and would like to do our best to fix this issue.  If you would like to make a post on our forums we can actively interact with you in getting this resolved.  If not, simply sending the error report helps us immensely and will only improve the quality of the software. Thanks!');?>
</div>
<div id="send-error">
<h2><?php echo Yii::t('app','Send Error Report');?></h2>
<div id="error-form" class="form">
    <?php echo Yii::t('app',"Here's a quick list of what will be included in the report:");?><br><br>
    <b><?php echo Yii::t('app','Error Code:');?></b> <?php echo $code; ?><br>
    <b><?php echo Yii::t('app','Error Message:');?></b> <?php echo CHtml::encode($message);?><br>
    <b><?php echo Yii::t('app','Stack Trace:');?> </b> <a href="#" id="toggle-trace">[<?php echo Yii::t('app','click to toggle display');?>]</a><br><div id="stack-trace"><?php echo nl2br($trace);?></div>
    <b><?php echo Yii::t('app','X2Engine Version:');?> </b> <?php echo $x2version; ?><br>
    <b><?php echo Yii::t('app','PHP Version:');?> </b> <?php echo $phpversion;?><br><br>
    <label><?php echo Yii::t('app','Email Address (optional)');?></label><?php echo CHtml::textField('email','',array('size'=>40)); ?><br><br>
    <label><span ><?php echo Yii::t('app','Include phpinfo()? (optional, but recommended)');?> <a href="#" style="text-decoration:none;" class="x2-hint" title="<?php echo Yii::t('app',"Detailed server and PHP configuration information that is very helpful for debugging purposes.  However, it can contain sensitive information about your server's configuration, and it is not required to be sent with the report.  We do however, highly recommend it.");?>"><i class="fa fa-question-circle"></i></a></span></label>
    <?php echo CHtml::checkBox('phpinfo',true); ?><br><br>
    <b><?php echo Yii::t('app','Please Note:');?> </b><?php echo Yii::t('app','Any information in the $_GET or $_POST arrays included with the request will also be sent with the report.');?>
</div>
</div>
<div id="send-error-button">
<a href="#" id="error-report-link" class="x2-button highlight"><?php echo Yii::t('app','Send Error Report');?></a>
<span id="loading-text" style="display:none;"><img src="<?php echo Yii::app()->theme->getBaseUrl(); ?>/images/loading.gif" /><?php echo Yii::t('app','Sending...');?></span>
<span id="sent-text" style="display:none;color:green;"><?php echo Yii::t('app','Error report sent!');?></span>
</div>
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
