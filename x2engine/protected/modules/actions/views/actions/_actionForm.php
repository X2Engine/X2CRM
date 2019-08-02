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




$submitButton = isset ($submitButton) ? $submitButton : true;
$htmlOptions = !isset ($htmlOptions) ? array () : $htmlOptions;
$namespace = !isset ($namespace) ? null : $namespace;

$form = $this->beginWidget ('ActionActiveForm', array (
    'formModel' => $model,
    'htmlOptions' => $htmlOptions,
    'namespace' => $namespace,
));
    echo $form->label ($model,'Subject'); 
    echo $form->textArea ($model, 'subject');
    echo $form->label ($model,'Action Description'); 
    echo $form->textArea ($model, 'actionDescription');

?>
    <div class='row'>
        <div class='cell'>
<?php

    echo $form->label ($model,'dueDate', array('class' =>  'action-due-date-label')); 
    echo $form->renderInput ($model, 'dueDate');

    echo $form->label ($model,'priority'); 
    echo $form->renderInput ($model, 'priority');

    echo $form->label ($model, 'visibility'); 
    echo $form->renderInput ($model, 'visibility');
?>
        </div>
        <div class='cell'>
<?php

    echo $form->label ($model, 'assignedTo'); 
    echo $form->renderInput ($model, 'assignedTo');
?>
        </div>
    </div>

    <br>
    <div class='row'>
    <div class='cell'><?php
    echo $form->label ($model, 'reminder', array (
        'class' => 'reminder-label',
    )); 
    echo $form->renderInput ($model, 'reminder', array (
        'style' => 'display: none;'
    ));
    echo CHtml::label (Yii::t('actions', 'Add to Calendar'), 'calendarId');
    $editableCalendars =
        array('' => Yii::t('actions', 'None')) +
        X2CalendarPermissions::getEditableUserCalendarNames();
    echo CHtml::activeDropDownList($model, 'calendarId', $editableCalendars);
    echo '</div>';

    if (!Yii::app()->user->isGuest &&
        isset($_POST['showAssociationControls']) && $_POST['showAssociationControls']) {
            // Render association controls when adding action from email client
            echo "<div class='cell'>";
            echo $form->label($model, 'associationType', array(
                'style' => 'display: inline',
            ));
            echo X2Html::hint2(Yii::t('actions', 'By default, the Contact who sent the '.
                'email will be associated.'));
            echo $form->renderInput($model, 'associationType');
            echo '</div>';
    } else {
        echo $form->hiddenField($model, 'associationType');
        echo $form->hiddenField($model, 'associationId');
    }
    echo '</div>';

    if(Yii::app()->user->isGuest){ 
    ?>
        <div class="row">
            <?php
            $this->widget('CCaptcha', array(
                'captchaAction' => '/actions/actions/captcha',
                'buttonOptions' => array(
                    'style' => 'display:block;',
                ),
            ));
            echo $form->textField($model, 'verifyCode'); 
            ?>
        </div>
    <?php 
    } 

    if ($submitButton) echo $form->submitButton ();

$this->endWidget ();

?>
