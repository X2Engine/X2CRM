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




$canUpload = isset($canUpload) && $canUpload;

if (empty($model->attachments) && !$canUpload) {
    return;
}

// find out if attachments are minimized
$showAttachments = true;
$formSettings = Profile::getFormSettings('campaign');
$layout = FormLayout::model()->findByAttributes(array('model' => 'Campaign', 'defaultView' => 1));
if(isset($layout)){
    $layoutData = json_decode($layout->layout, true);
    $count = count($layoutData['sections']);
    if(isset($formSettings[$count])){
        $showAttachments = $formSettings[$count];
    }
}


?>

<div id="campaign-attachments-wrapper" class="x2-layout form-view x2-hint">
    <div class="formSection collapsible <?php echo $showAttachments ? 'showSection' : ''; ?>">
        <div class="formSectionHeader">
            <a href="javascript:void(0)" class="formSectionHide">
                <?php echo X2Html::fa('fa-caret-down')?>
            </a>
            <a href="javascript:void(0)" class="formSectionShow">
                <?php echo X2Html::fa('fa-caret-right')?>
            </a>
            <span class="sectionTitle"><?php echo Yii::t('app', 'Attachments'); ?></span>
        </div>
        <div id="campaign-attachments" class="tableWrapper" style="padding: 5px;
        <?php echo $showAttachments ? '' : 'display: none;'; ?>">
            <div style="min-height: 100px;">
             <?php foreach($model->attachments as $attachment):
                    $media = $attachment->mediaFile;
                    if (!$media || !$media->fileName) continue; ?>

                    <div class='attachment-input' style="font-weight: bold;">
                        <span class="filename"><?php echo $media->fileName; ?></span>
                        <?php if ($canUpload): ?>
                            <input type="hidden" value="<?php echo $media->id; ?>" 
                             name="AttachmentFiles[id][]" class="AttachmentFiles">
                            <span class="remove fa fa-times x2-icon-gray"></span>
                        <?php endif; ?>
                    </div>

               <?php endforeach; ?>

                <?php if ($canUpload): ?>
                    <div class="next-attachment" style='font-weight: bold; display: none;'>
                        <span class="filename"></span>
                        <span class="remove fa fa-times x2-icon-gray"></span>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
