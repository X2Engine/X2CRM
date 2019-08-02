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
<div class='record-list-item'>
    <div class='reply-description'>
        <div class='avatar'>
        <?php
            echo CHtml::link (
                Profile::renderFullSizeAvatar ($data->profile->id, 30),
                $data->profile->url
            );
        ?>
        </div>
        <div class='author-name'>
        <?php
            echo CHtml::link ($data->profile->fullName, $data->profile->url);
        ?>
        </div>
        <?php
        $menuId = 'edit-reply-menu-'.$data->id;
        ?>
        <a class='dropdown-menu-button' href='#<?php echo $menuId; ?>'
         data-rel='popup' data-transition='pop'>
        <?php
            echo X2Html::fa ('ellipsis-v')
        ?>
        </a>
        <div data-role='popup' id='<?php echo $menuId; ?>'<?php 
         echo $data->isOriginalPost () ? ' class="original-post"' : ''; ?>>
            <ul data-role='listview' data-inset='true'>
                <?php 
                if ($data->isDeletable ()) {
                ?> 
                <li>
                    <a class='delete-reply-button requires-confirmation' href='<?php
                     echo $this->createAbsoluteUrl ('/topics/mobileDeleteReply', array (
                        'id' => $data->id,
                     )); ?>'><?php 
                        echo CHtml::encode (Yii::t('mobile', 'Delete')); ?></a>
                    <div class='confirmation-text' style='display: none;'>
                    <?php
                        echo CHtml::encode (
                            Yii::t('app', 'Are you sure you want to delete this reply?'));
                    ?>
                    </div>
                </li>
                <?php
                }
                ?>
                <li>
                    <a class='edit-reply-button' href='<?php 
                     echo $this->createAbsoluteUrl ('/topics/mobileUpdateReply', array (
                        'id' => $data->id,
                     )); ?>'><?php 
                        echo CHtml::encode (Yii::t('mobile', 'Edit')); ?></a>
                </li>
            </ul>
        </div>
        <div class='create-date'>
        <?php
            echo Formatter::formatDateTime($data->createDate, 'medium'); 
        ?>
        </div>

        <!--<div class='clearfix'></div>-->
    </div>
    <div class='reply-text'>
        <?php echo Formatter::convertLineBreaks(x2base::convertUrls($data->text)); ?>
    </div>

    <div class='reply-attachments'>
    <?php
        /*$found = false;
        foreach ($data->attachments as $attachment) {
            if (!$attachment->isImage ()) {
                if (!$found) {
                   ?>
                    <div class='file-attachments-container'>
                   <?php
                }
            ?>
                <div class='file-attachment-container'>
                    <?php 
                    echo $attachment->getDownloadLink ($attachment->fileName, array (
                        'class' => 'file-download-link'
                    )); 
                    echo X2Html::fa('download');
                    ?>
                </div>
            <?php
                if (!$found) {
                   ?>
                    </div>
                   <?php
                   $found = true;
                }
            }
        }*/
        foreach ($data->attachments as $attachment) {
            if ($attachment->isImage ()) {
        ?>
            <div class='photo-attachment-container'>
                <?php 
                echo $attachment->getImage (false, array ('photo-attachment')); 
                ?>
            </div>
        <?php
            }
        }
    ?>
    </div>
</div>
