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




Yii::app()->clientScript->registerCss('attachmentsCss',"

#attachment-form input,
#attachment-form select {
    margin-right: 11px;
}

");

?>
<div id="attachment-form-top"></div>
<div id="attachment-form"<?php if($startHidden) echo ' style="display:none;"'; ?>>
    <div class="form x2-layout-island">
        <?php
        if (!$mobile) {
        ?>
        <b><?php echo Yii::t('app', 'Attach a File'); ?></b><br />
        <?php
        }
        echo CHtml::form(
            array('/site/upload'), 'post', 
            array(
                'enctype' => 'multipart/form-data', 'id' => 'attachment-form-form'
            )
        );
        echo "<div class='row'>";
        echo CHtml::hiddenField('associationType', $this->associationType);
        echo CHtml::hiddenField('associationId', $this->associationId);
        echo CHtml::hiddenField('attachmentText', '');
        if (isset ($profileId))
            echo CHtml::hiddenField('profileId', $profileId);
        $visibilityHtmlAttrs = array ();
        if ($mobile)
            $visibilityHtmlAttrs['data-mini'] = 'true';
        echo CHtml::dropDownList(
            'private', 'public', 
            array(
                '0' => Yii::t('actions', 'Public'), 
                '1' => Yii::t('actions', 'Private')
            ),
            $visibilityHtmlAttrs
        );
        $fileFieldHtmlAttrs = array (
            'id' => 'upload', 
            'onchange' => "x2.attachments.checkName(event)"
        );
        if ($mobile) {
            $fileFieldHtmlAttrs['data-inline'] = 'true';
            $fileFieldHtmlAttrs['data-mini'] = 'true';
        }
        echo CHtml::fileField(
            'upload', '', $fileFieldHtmlAttrs
        );
        if ($mobile) 
            echo '<div style="display:none;">';
        echo CHtml::submitButton(
            Yii::t('app','Submit'), 
            array(
                'id' => 'submitAttach', 'disabled' => 'disabled', 'class' => 'x2-button',
                'style' => 'display:inline'
            )
        );
        if ($mobile) 
            echo "</div>";
        echo "</div>";
        if(Yii::app()->settings->googleIntegration){
            $auth = new GoogleAuthenticator();
            if($auth->getAccessToken()){
                echo "<div class='row'>";
                echo CHtml::label(Yii::t('app','Save to Google Drive?'), 'drive');
                echo CHtml::checkBox('drive');
                echo "</div>";
            }
        }
        echo CHtml::endForm();
        ?>
    </div>
</div>
