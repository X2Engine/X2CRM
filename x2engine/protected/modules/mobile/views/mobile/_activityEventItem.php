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




$showComments = !isset ($showComments) ? true : $showComments;
$showRecipient = !isset ($showRecipient) ? true : $showRecipient;

$attrs['data-x2-event-subtype'] = 
    X2Html::sanitizeAttribute (preg_replace ('/ /', '-', strtolower ($data->subtype)));
$attrs['data-x2-event-type'] = 
    X2Html::sanitizeAttribute (preg_replace ('/ /', '-', strtolower ($data->type)));

echo CHtml::openTag ('div', array_merge (array (
    'class' => 'record-list-item',
), $attrs));
?>
    <?php
    if (($data->isTypeFeed () || in_array ($data->type, array ('comment', 'media'))) && 
        $data->profile) {
    ?>
    <div class='event-author'>
        <div class='avatar'>
        <?php
            echo CHtml::link (
                Profile::renderFullSizeAvatar ($data->profile->id, 45),
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
        if ($showRecipient && ($recipient = $data->getRecipient ())) {
        ?>
        <div class='recipient-name'>
        <?php
            echo $recipient;
        ?>
        </div>
        <?php
        }
        ?>
    </div>
    <?php
    }
    ?>
    <?php
        $currentUrl = Yii::app()->request->url;
        if (strpos($currentUrl, '/profile/mobileViewEvent/id/') !== false 
                && $this->hasMobileAction ('mobileDeleteEvent')) {
    ?>
            <a class="delete-button requires-confirmation" style="float:right;"
               href="<?php echo Yii::app()->createAbsoluteUrl ('profile/mobileDeleteEvent',
                    array('id'=>$data->id,)); ?>">
                <?php echo X2Html::fa ("fa-trash"); ?>
            </a>
            <div class="confirmation-text" style="display: none;">
                Are you sure you want to delete this?
            </div>
    <?php
        }
    ?>
    <div class='event-text'>
    <?php
        echo MobileActivityFeed::getText ($data);
    ?>
    </div>
    <div class='event-attachments'>
    <?php
        foreach ($data->media as $media) {
        ?>
        <div class='photo-attachment-container'>
            <?php echo $media->getImage (false, array ('photo-attachment')); ?>
        </div>
        <?php
        }
        if ($data->type === 'media' && $data->legacyMedia) {          
        ?>
        <div class='photo-attachment-container'>
            <?php echo Media::attachmentSocialText(
                $data->legacyMedia->getMediaLink(), true, true); ?>
        </div>
        <?php
        }
    ?>
    </div>
    <div class='bottom-bar'>
        <div class='event-time'>
        <?php
            echo MobileFormatter::formatDateRelative ($data->timestamp);
        ?>
        </div>
        <div class='controls'>
            <?php
            if ($showComments) {
            ?>
            <div data-x2-url='<?php echo  
               $this->createAbsoluteUrl ('/profile/mobileViewEvent/id/'.$data->id); ?>'
               class='comments'>
                <div>
                <?php
                echo X2Html::fa ('comment');
                ?>
                </div>
            </div>
            <div class='comment-count'>
                <?php
                if ($commentCount = $data->comments ()->count ()) {
                    echo $commentCount;
                }
                ?>
            </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>
