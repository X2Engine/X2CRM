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
<div id="emailProgressControl" class="x2-layout form-view">
    <div class="formSection">
        <div class="formSectionHeader">
            <span class="sectionTitle"><?php echo Yii::t('marketing', 'Email Delivery Progress'); ?></span>
        </div>
        <div class="tableWrapper">
            <div class="emailProgressControl-section"><div id="emailProgressControl-bar"><div id="emailProgressControl-text"></div></div></div>
            <div class="emailProgressControl-section">
                <div id="emailProgressControl-toolbar">
                    <button class="startPause x2-button">
                    
                        <?php echo X2Html::fa('fa-pause'); ?>
                        <?php echo X2Html::fa('fa-play'); ?>
                        <span class='button-text'>
                            <?php echo Yii::t('marketing', 'Pause') ?>
                        </span>
                    </button>
                    <button class="refresh x2-button" title="<?php echo CHtml::encode(Yii::t('marketing','Click to refresh displays of campaign data on this page.')); ?>">
                        <?php echo X2Html::fa('fa-refresh'); ?>
                        <?php echo Yii::t('marketing', 'Refresh'); ?>
                    </button>
                    <span id="emailProgressControl-throbber" style="display: none;" class='load8 x2-loader loader' ></span>
                    <div id="emailProgressControl-textStatus"></div>
                </div>
            </div>
            <div class="emailProgressControl-section">
                <div id="emailProgressControl-errorContainer">
                    <hr />
                    <strong><?php echo Yii::t('marketing','Errors that occurred when attempting to send email:') ?></strong>
                    <div id="emailProgressControl-errors"></div>
                </div>
            </div>
        </div>
    </div>
</div>


