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




/**
 * View file for MediaSelector.php
 */
?>
<div id='image-selector' style='display:none'>
    <div class='top-section'>
        <div id='media-details'>
        </div>

        <form id='media-options'>
            <div class='row'>
                <label><?php echo Yii::t('media', 'Image Width').':' ?></label>
                <input name='width' type='number' placeholder='auto' value='auto' />
            </div>
            <div class='row'>
                <label><?php echo Yii::t('media', 'Image Height').':' ?></label>
                <input name='height' type='number' placeholder='auto' value='auto' />
            </div>
            <div>
                <span class='x2-button x2-red' id='delete'> <?php echo Yii::t('media', 'Delete Selected Image') ?></span>
            </div>
        </form>

        <div class='clear'></div>
    </div>

    <?php 
    if (!$this->update)
        $this->widget('FileUploader', array(
            'id' => 'mediaSelector',
            'viewParams' => array (
                'noPadding' => true,
                'buttonText' => 'Upload a File'
            ),
            'acceptedFiles' => 'image/*',
            'googleDrive' => false
        ));
    ?>

    <?php 
    $this->widget('X2ListView', array(
        'id' => 'media-list',
        'dataProvider'=> $this->dataProvider,
        'itemView'=>'mediaSquare',
    ));

    // Template for media squares. Copied when a new square is created
    $this->render('mediaSquare', array(
        'template' => true,
        'data' => new Media,
        ));
    ?>
</div>
