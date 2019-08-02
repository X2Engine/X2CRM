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




Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/views/admin/createPage.css');

Yii::app()->clientScript->registerScript('adminCreatePageJS',"
;(function () {

var form$ = $('#admin-add-top-link-form');
form$.find ('.choice-container').click (function () {
    if ($(this).hasClass ('selected-choice')) {
    } else {
        form$.find ('.choice-container').toggleClass ('selected-choice');
    }
});

$('#create-top-bar-link').click (function () {
    form$.find ('.choice-container').not ('.selected-choice').find (':input').
        attr ('disabled', 'disabled');
    form$.find ('.choice-container.selected-choice').find (':input').
        removeAttr ('disabled');
});

}) ();
", CClientScript::POS_END);

?>
<div class='page-title'>
<h2><?php echo Yii::t('admin','Add Top Bar Link'); ?></h2>
</div>
<div class='admin-form-container form' id='admin-add-top-link-form'>
    <?php
    $form = $this->beginWidget ('X2ActiveForm', array (
            'formModel' => $model,
            'instantiateJSClassOnInit' => false,
        ));
        X2Html::getFlashes ();
        echo $form->errorSummary ($model);
        echo Yii::t(
            'admin',
            'Add a link to the top bar, either to a specific URL, or to a record in X2CRM.').
            "<br />";
        ?>
        <div class='choice-container-outer'>
            <div class='url-specification-container choice-container<?php  
                echo $model->getSelection () === 'topLinkUrl' ? ' selected-choice' : '';
            ?>'>
            <?php
            echo CHtml::tag ('h3', array (), CHtml::encode (Yii::t('admin', 'Specify a URL:')));
            echo $form->label ($model, 'topLinkUrl');
            echo $form->textField ($model, 'topLinkUrl');
            echo $form->label ($model, 'topLinkText');
            echo $form->textField ($model, 'topLinkText');
            echo '<br />';
            echo $form->checkBox ($model, 'openInFrame');
            echo $form->label ($model, 'openInFrame', array (
                'style' => 'display: inline;',
            ));
            ?>
            </div>
            <?php
            echo '<div class="alternation-text">-&nbsp;'.CHtml::encode (Yii::t('app', 'OR')).
                '&nbsp;-</div>';
            ?>
            <div class='record-specification-container choice-container<?php
                echo $model->getSelection () !== 'topLinkUrl' ? ' selected-choice' : '';
            ?>'>
            <?php
            echo CHtml::tag ('h3', array (), CHtml::encode (Yii::t('admin', 'Select a record:')));
            echo $form->label ($model, 'recordName');
            if (!isset ($model->recordType)) $model->recordType = 'Contacts';
            echo $form->multiTypeAutocomplete ($model, 'recordType', 'recordId',
                X2Model::getModelTypes (true, function ($elem) {
                    return X2Model::model ($elem)->asa ('LinkableBehavior');
                }),
                array (
                    'autocompleteName' => 'recordName',
                    'autocompleteValue' => $model->recordName,
                    'htmlOptions' => array (
                        'class' => 'all-form-input-style',
                    )
                )
            );
            ?>
            </div>
            <div class='extra-options-container'>
            <?php
            echo $form->checkBox ($model, 'openInNewTab');
            echo $form->label ($model, 'openInNewTab', array (
                'style' => 'display: inline;',
            ));
            ?>
            </div>
        </div>
        <?php
        echo CHtml::submitButton(Yii::t('admin', "Create"), array(
            'class' => 'x2-button',
            'id' => 'create-top-bar-link',
        ));
    $this->endWidget ();
    ?>
</div>
