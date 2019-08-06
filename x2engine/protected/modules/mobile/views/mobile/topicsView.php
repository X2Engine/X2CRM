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




Yii::app()->clientScript->registerScriptFile(
    Yii::app()->controller->assetsUrl.'/js/RecordIndexControllerBase.js');
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->controller->assetsUrl.'/js/RecordViewController.js');
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->controller->assetsUrl.'/js/TopicsViewController.js');

$this->onPageLoad ("
    x2.main.controllers['$this->pageId'] = new x2.TopicsViewController ();
", CClientScript::POS_END);

$authParams['X2Model'] = $model;
if (Yii::app()->user->checkAccess(ucfirst ($this->module->name).'Delete', $authParams)) {
?>

<div data-role='popup' id='settings-menu'>
    <ul data-role='listview' data-inset='true'>
        <li>
            <a class='delete-button requires-confirmation' 
             href='<?php echo $this->createAbsoluteUrl ('mobileDelete', array (
                'id' => $model->id,
             )); ?>'><?php 
                echo CHtml::encode (Yii::t('mobile', 'Delete Topic')); ?></a>
            <div class='confirmation-text' style='display: none;'>
                <?php
                echo CHtml::encode (
                    Yii::t('app', 'Are you sure you want to delete this {type}?', array (
                        '{type}' => Modules::displayName (false, get_class ($model)), 
                    )));
                ?>
            </div>

        </li>
    </ul>
</div>

<?php
}

if (Yii::app()->user->checkAccess(ucfirst ($this->module->name).'Update', $authParams)) {
    ?>

    <div class='refresh-content' data-refresh-selector='.header-content-right'>
        <div class='header-content-right'>
            <div class='edit-button ui-btn icon-btn' 
             data-x2-url='<?php echo $this->createAbsoluteUrl ('mobileUpdate', array (
                'id' => $model->id
             )); ?>'>
            <?php
            echo X2Html::fa ('pencil');
            ?>
            </div>
        </div>
    </div>

    <?php
}

$this->widget (
    'zii.widgets.CListView', 
    array (
        'dataProvider' => $dataProvider,
        'template' => '{items}<div class="pager-container record-list-item"'.
            ($dataProvider->itemCount < $dataProvider->totalItemCount ?
                '' : ' style="display: none;"').
            '>{pager}</div>',
        'pager' => array(
            'class' => 'CLinkPager',
            'header' => '',
            'maxButtonCount' => 0,
        ),
        'itemView' => 'application.modules.mobile.views.mobile._topicsViewItem',
        'viewData' => array (
        ),
        'htmlOptions' => array (
            'class' => 'record-index-list-view topics-view'
        ),
    ));

if (Yii::app()->user->checkAccess(ucfirst ($this->module->name).'ReadOnlyAccess')) {
?>

<div id='footer' data-role="footer" class='reply-box-container fixed-footer control-panel'>
    <div class='reply-box'>
    <?php
    $form = $this->beginWidget ('MobileActiveForm', array (
        'photoAttrName' => 'TopicReplies[upload][]',
        'id' => $this->pageId . '-form',
        'jSClassParams' => array (
            'validate' => 'js:function () {
                return $.trim (this.form$.find ("textarea").val ()) ||
                    this.form$.find (".photo-attachment").length;
            }',
        )
    ));

     
    if (Yii::app()->params->isPhoneGap) {
        echo $form->photoAttachmentsContainer (new TopicReplies, 'attachments', 'upload', array (
            'class' => 'thumbnails',
        ));
    }
     
    ?>
    <div class='row'>
        <?php
         
        if (Yii::app()->params->isPhoneGap) {
            echo $form->photoAttachmentButton ();
        }
         
        ?>
        <div class='textarea-container'>
        <?php
        echo $form->textArea ($reply, 'text', array (
            'placeholder' => 'Add a reply...',
        ));
        ?>
        </div>
        <div class='submit-button disabled'><?php  
            echo CHtml::encode (Yii::t('mobile', 'Post'));
        ?></div>
        <?php
        $this->endWidget ();
        ?>
        </div>

        <!--<a href='<?php echo $this->createAbsoluteUrl ('mobileNewReply'); ?>'>
            <div class='record-create-button fixed-corner-button'>
            <?php
                echo X2Html::fa ('plus');
            ?>
            </div>
        </a>-->
    </div>
<?php
}
?>
</div>
