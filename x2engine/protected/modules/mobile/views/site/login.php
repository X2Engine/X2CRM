<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

$this->pageTitle = Yii::app()->name . ' - Login';

?>

<div class="form">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'login-form',
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
            ));
    ?>
    <div data-role="fieldcontain">
        <?php
        $for=CHtml::resolveName($model, $name);
        echo $form->label($model, 'username', array(
            'for' => CHtml::resolveName($model, $a='username')
        ));
        ?>
        <?php echo $form->textField($model, 'username'); ?>
        <?php echo $form->error($model, 'username'); ?>
    </div>
    <div data-role="fieldcontain">
        <?php
        echo $form->label($model, 'password', array(
            'for' => CHtml::resolveName($model, $a='password')
        ));
        ?>
        <?php echo $form->passwordField($model, 'password'); ?>
        <?php echo $form->error($model, 'password'); ?>
    </div>
<!--
    <div data-role="fieldcontain">
        <?php
        echo $form->label($model, 'rememberMe', array(
            'for' => CHtml::resolveName($model, $a='rememberMe')
        ));
        ?>
        <?php
        echo $form->dropDownList($model, 'rememberMe', array(
            '0' => Yii::t('app', 'Off'),
            '1' => Yii::t('app', 'On')
                ), array(
            'data-role' => 'slider'
                )
        );
        ?>
        <?php echo $form->error($model, 'rememberMe'); ?>
    </div>
-->
        <?php echo CHtml::submitButton(Yii::t('app', 'Login')); ?>

    <?php $this->endWidget(); ?>
</div>
