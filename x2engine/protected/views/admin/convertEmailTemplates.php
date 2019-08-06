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
<div class="page-title"><h2><?php echo Yii::t('admin', 'Convert Email Template Image Links'); ?></h2></div>
<div class="form">
    <?php
    if(isset($status) && !is_null($status)){
        echo Yii::t('admin', '{number} email template(s) had at least one image link converted to the new format.', array(
            '{number}' => $status
        ));
    }else{
        echo Yii::t('admin', 'This tool is designed to fix broken image links in email templates resulting from the 5.2/5.3 media module changes.').'<br><br>';
        echo Yii::t('admin', 'Any images in email templates uploaded to the media module of this instance of X2CRM will be dead links as of 5.3. As a result of this, we are providing this tool to attempt to convert these links to the new format.').'<br><br>';
        echo Yii::t('admin', 'As of version 5.2, both the old and new systems of links will be functional in an effort to assist administrators and give them time to make the necessary changes for the new system in 5.3').'<br><br>';
        echo Yii::t('admin', 'This tool is intentionally conservative in its changes so it may not resolve every dead link in your templates. Spot-checking your templates after the 5.3 update is recommended.').'<br><br>';
        echo Yii::t('admin', 'To manually fix an image in your email templates, simply delete the old image in the template and drag the replacement over from the media widget or using the image insertion function of the document editor.')
            .'<br><br>';
        echo Yii::t('admin', 'Please press the button below to continue with the conversion. This action cannot be undone.');
    } 
    ?>
</div>
<div class="form">
    <?php
    echo CHtml::beginForm();
    echo CHtml::submitButton(Yii::t('admin','Convert Templates'), array('class' => 'x2-button'));
    echo CHtml::endForm();
    ?>
</div>

