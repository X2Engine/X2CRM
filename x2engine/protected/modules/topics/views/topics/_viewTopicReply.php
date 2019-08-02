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
<div class="topic-reply" id="<?php echo $data->id;?>-topic-reply">
    <div class="reply-header page-title">
        <div class="reply-post-number">
            <?php echo X2Html::link("#" . (($page * Topics::PAGE_SIZE) + ($index + 1)), Yii::app()->controller->createUrl('/topics/topics/view', array('id' => $data->topicId, 'replyId' => $data->id))); ?>
        </div>
        <div class="reply-delete-button">
            <?php
            if ($data->isDeletable()) {
                echo X2Html::ajaxLink(X2Html::fa('close'), Yii::app()->controller->createUrl('/topics/topics/deleteReply', array('id' => $data->id)), array(
                    'method' => 'POST',
                    'data' => array('YII_CSRF_TOKEN' => Yii::app()->request->csrfToken),
                    'success' => 'function() { window.location = window.location }',
                        ), array('class' => 'x2-button right', 'confirm' => Yii::t('topics', 'Are you sure you want to delete this post?')));
            }
            ?>
        </div>
        <div class="reply-edit-button">
            <?php if ($data->isEditable()) {
                echo X2Html::link('', Yii::app()->controller->createUrl('/topics/topics/updateReply', array('id' => $data->id)), array('class' => 'x2-button icon edit right'));
            } ?>
        </div>
        <div class="clear-fix"></div><br>
    </div>
    <div class="topic-content clear-fix">
        <div class="reply-user-info">
            <div class="reply-username">
                <?php echo User::getUserLinks($data->assignedTo); ?>
            </div>
            <div class="img-box user-avatar">
                <?php echo Profile::renderFullSizeAvatar($data->getAuthorId(), 45); ?>
            </div>
        </div>
        <div class='topic-text'>
            <div class='topic-timestamp-text'>
                <?php
                echo Yii::t('topics', 'Posted {datetime}.', array(
                    '{datetime}' => Formatter::formatDateTime($data->createDate, 'medium'),
                ));
                ?>
            </div>
            <br>
            <?php echo Formatter::convertLineBreaks(x2base::convertUrls($data->text)); ?>
            <div class='topic-timestamp-text'>
                <br>
                <?php
                if ($data->isEdited()) {
                    echo Yii::t('topics', 'Edited by {user} - {datetime}.', array(
                        '{user}' => User::getUserLinks($data->updatedBy, false, true),
                        '{datetime}' => Formatter::formatDateTime($data->lastUpdated, 'medium'),
                    ));
                }
                ?>
            </div>
        </div>

    </div>
    <div class='topic-footer'>
            <?php
                if(count($data->attachments) > 0){
                    echo "<div class='topic-attachment-label'>";
                    echo Yii::t('topics','Attachments');
                    echo "</div>";
                    echo "<div class='topic-attachment-list'>";
                    foreach($data->attachments as $media){
                        echo '<span class="x2-pillbox topic-attachment">'.
                            $media->getMediaLink().'</span>';
                    }
                    echo "</div>";
                }
            ?> 
    </div>
</div>
