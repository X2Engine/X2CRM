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




Yii::app()->clientScript->registerCss ('exportFormatCss', '
    .compressOutput label { display: inline !important; }

    
    #targetType input[type="radio"], #targetType label {
        display: inline;
    }

    .targetForm { display: none; }
    .targetForm label {
        display: inline-block !important;
        min-width: 80px;
    }
    
'); ?>

<div class="compressOutput exportOption">
<?php
    echo CHtml::label(Yii::t('admin', 'Compress Output?'), 'compressOutput');
    echo CHtml::checkbox('compressOutput', false);
?>
</div>

<br />

<?php
    
    $exportTargets = array(
        'download' => Yii::t ('admin', 'Download in Browser'),
        'server' => Yii::t ('admin', 'Save to Server'),
        'ftp' => Yii::t ('admin', 'FTP to Server'),
        'scp' => Yii::t ('admin', 'SCP to Server'),
        's3' => Yii::t ('admin', 'Upload to Amazon S3'),
        'gdrive' => Yii::t ('admin', 'Upload to Google Drive'),
    );
    echo CHtml::label(Yii::t('admin', 'Destination'), 'targetType');
    echo CHtml::radioButtonList ('targetType', 'download', $exportTargets, array(
        'separator' => '',
    ));
?>

<form id='download' class='targetForm'>
</form>

<form id='server' class='targetForm'>
    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'Path'), 'server-path');
        echo CHtml::textField ('server-path');
    ?>
    </div>
</form><!-- #server -->

<form id='ftp' class='targetForm'>
    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'Remote File'), 'ftp-path');
        echo CHtml::textField ('ftp-path');
    ?>
    </div>

    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'FTP Server'), 'ftp-server');
        echo CHtml::textField ('ftp-server');
    ?>
    </div>

    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'FTP User'), 'ftp-user');
        echo CHtml::textField ('ftp-user');
    ?>
    </div>

    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'FTP Password'), 'ftp-pass');
        echo CHtml::passwordField ('ftp-pass');
    ?>
    </div>
</form><!-- #ftp -->

<form id='scp' class='targetForm'>
    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'Remote File'), 'scp-path');
        echo CHtml::textField ('scp-path');
    ?>
    </div>

    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'SSH Server'), 'scp-server');
        echo CHtml::textField ('scp-server');
    ?>
    </div>

    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'SSH User'), 'scp-user');
        echo CHtml::textField ('scp-user');
    ?>
    </div>

    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'SSH Password'), 'scp-pass');
        echo CHtml::passwordField ('scp-pass');
    ?>
    </div>
</form><!-- #scp -->

<form id='s3' class='targetForm'>
    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'Access Key'), 's3-accessKey');
        echo CHtml::textField ('s3-accessKey');
    ?>
    </div>

    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'Secret Key'), 's3-secretKey');
        echo CHtml::passwordField ('s3-secretKey');
    ?>
    </div>

    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'Bucket'), 's3-bucket');
        echo CHtml::textField ('s3-bucket');
    ?>
    </div>

    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'Path'), 's3-key');
        echo CHtml::textField ('s3-key');
    ?>
    </div>

    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'Region'), 's3-region');
        echo CHtml::dropDownList ('s3-region', '', array_combine (S3Behavior::$AWSRegions, S3Behavior::$AWSRegions));
    ?>
    </div>
</form><!-- #s3 -->

<form id='gdrive' class='targetForm'>
<?php
    $auth = new GoogleAuthenticator();
    if (Yii::app()->settings->googleIntegration && $auth->getAccessToken()) {
?>

    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'Filename'), 'gdrive-path');
        echo CHtml::textField ('gdrive-path');
    ?>
    </div>

    <div class="field">
    <?php
        echo CHtml::label (Yii::t('admin', 'Description'), 'gdrive-description');
        echo CHtml::textField ('gdrive-description', Yii::t('app', 'Exported from X2CRM'));
    ?>
    </div>
<?php } else { ?>
    <?php echo Yii::t ('admin', 'You must first configure Google Integration before you can '.
        'export to Google Drive. Please see the {wiki} section on the wiki for more information.', array(
            '{wiki}' => CHtml::link('Google Integration', 'http://wiki.x2crm.com/wiki/Google_Integration')
        )); ?>
<?php } ?>
</form><!-- #gdrive -->

<?php
    
Yii::app()->clientScript->registerScript ('exportFormatControls', '
    if (typeof x2 === "undefined") x2 = {};
    if (typeof x2.exportFormats === "undefined") x2.exportFormats = {};

    
    // Display the appropriate controls when a target is selected
    $("#targetType").change (function() {
        var type = $(this).children (":checked").val();
        $(".targetForm").hide();
        $("#" + type + ".targetForm").show();
    });
    

    // Build a parameter string of the format controls for the selected type
    x2.exportFormats.readExportFormatOptions = function() {
        var type = $("#targetType").children (":checked").val();
        if (typeof type === "undefined")
            type = "download";
        var compressOutput = $("#compressOutput").is(":checked");
        var params = $("#" + type + ".targetForm").serialize();
        var destination = "exportDestination=" + type;
        var compress = "compressOutput=" + compressOutput;
        return [params, destination, compress].join("&");
    };
', CClientScript::POS_READY);
