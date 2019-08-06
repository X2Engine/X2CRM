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

<div class='message-subject'>
<?php
echo CHtml::encode($message->subject);
?>
</div>
<hr />
<div class='bs-row'>
    <div class='col-xs-6'>
        <div class='from-field' data-from='<?php echo CHtml::encode ($message->from); ?>'>
        <?php
        echo $message->renderFromField ();
        ?>
        </div>
        <div class='to-field' data-to='<?php echo CHtml::encode ($message->to); ?>'>
        <span><?php echo CHtml::encode (Yii::t('emailInboxes', 'to')).'&nbsp;'; ?></span>
        <span>
        <?php
        echo $message->renderToField ();
        ?>
        </span>
        </div>
        <?php
        if (!empty($message->cc)) { ?>
            <div class='cc-field' data-cc='<?php echo CHtml::encode ($message->cc); ?>'>
            <span>cc&nbsp;</span>
            <span>
            <?php
            echo $message->renderCCField ();
            ?>
            </span>
            </div>
        <?php } ?>

    </div>
    <div class='col-xs-6'>
        <div class='date-field'>
        <?php
        echo CHtml::encode ($message->renderDate ('full').' at '.$message->renderDate ('hours'));
        ?>
        </div>
        <div class='real-button-group'>
            <button class='x2-button message-print-button fa fa-print fa-lg' 
             title='<?php echo CHtml::encode (Yii::t('emailInboxes', 'Print')); ?>'></button>
            <button class='x2-button message-reply-button fa fa-reply fa-lg' 
             title='<?php echo CHtml::encode (Yii::t('emailInboxes', 'Reply')); ?>'></button>
            <button class='x2-button message-reply-more-button fa fa-caret-down' 
             title='<?php echo CHtml::encode (Yii::t('emailInboxes', 'More')); ?>'></button>
        </div>
        <ul class='x2-dropdown-list fa-ul reply-more-menu' style='display: none;'>
            <?php if (!empty ($replyAll)) { ?>
            <li class='message-reply-all-button' data-replyAll='<?php echo CHtml::encode ($replyAll)?>'>
                <span class='fa-li fa fa-reply-all'></span><?php 
                    echo CHtml::encode (Yii::t('app', 'Reply all')) ?> </li>
            <?php } ?>
            <li class='message-forward-button'>
                <span class='fa-li fa fa-long-arrow-right'></span><?php 
                    echo CHtml::encode (Yii::t('app', 'Forward')) ?> </li>
        </ul>
    </div>
</div>
<iframe class='message-body'></iframe>
<div class='message-body-temp'>
<?php
echo CHtml::encode ($message->body);
?>
</div>
<div class='message-attachments'>
<?php
echo $message->renderAttachmentLinks ();
?>
</div>
