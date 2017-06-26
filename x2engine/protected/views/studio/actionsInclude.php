<?php
/* * *********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 * ******************************************************************************** */

/**
 * Before including, set an $actions variable that has a list of actions to
 * display
 */
?>

<ul id="all" class="actions">
    <li class="x2flow-node X2FlowSwitch">
        <span><?php echo Yii::t('studio', 'Condition'); ?></span>
        <div class="icon">
            <div class="x2flow-yes-label"><?php echo Yii::t('app', 'Yes') ?></div>
            <div class="x2flow-no-label"><?php echo Yii::t('app', 'No') ?></div>
        </div>
        <div class="x2flow-branch-wrapper">
            <div class="x2flow-branch">
                <div class="bracket"></div>
                <div class="x2flow-node x2flow-empty"></div>
            </div>
            <div class="x2flow-branch">
                <div class="bracket"></div>
                <div class="x2flow-node x2flow-empty"></div>
            </div>
        </div>
    </li>
    <li class="x2flow-node X2FlowSplitter">
        <span><?php echo Yii::t('studio', 'Split Path'); ?></span>
        <div class="icon">
            <div class="icon-inner">
            </div>
        </div>
        <div class="x2flow-branch-wrapper">
            <div class="x2flow-branch">
                <div class="bracket"></div>
                <div class="x2flow-node x2flow-empty"></div>
            </div>
            <div class="x2flow-branch">
                <div class="bracket"></div>
                <div class="x2flow-node x2flow-empty"></div>
            </div>
        </div>
    </li>

    <?php
    foreach ($actions as $type => $title):
        ?>

        <li class="x2flow-node x2flow-action
        <?php echo $type . ($showLabels ? "" : " no-label") ?>"
            title="<?php echo addslashes(Yii::t('studio', $title)) ?>"
            <?php
            echo ((($type === 'X2FlowPushWebContent' && $model->triggerType !== 'TargetedContentRequestTrigger') || ($type === 'X2FlowPushWebPage' && $model->triggerType !== 'TargetedPageRequestTrigger')) ? 'style="display: none;"' : '')
            ?>>
            <div class="x2flow-icon-label"
                 <?php echo ($showLabels ? "" : "style='display: none;'") ?>>
                 <?php echo Yii::t('studio', $title) ?>
            </div>
            <span> <?php echo Yii::t('studio', $title) ?> </span>
        </li>
        <?php
    endforeach;
    ?>
</ul>