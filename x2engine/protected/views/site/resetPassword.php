<?php

/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * California 95067, USA. or at email address contact@x2engine.com.
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
 *****************************************************************************************/
?>

<div id="password-reset-form-outer">
    <div class="container" id="login-page">
        <div id="login-box">
            <div id='login-title-container'>
                <h1 id='app-title'>
                    <?php echo $title; ?>
                </h1>
                <p><?php echo $message; ?></p>
            </div>
            <?php if($scenario != 'message') { ?>
            <?php echo CHtml::beginForm(); ?>
            <div class="form" id="login-form">
                <div class="row">
                    <?php if($scenario=='new') {
                        echo CHtml::activeTextField($request, 'email').'<br />';
                        echo CHtml::errorSummary($request);
                    } else if($scenario == 'apply') {
                        echo CHtml::activeLabel($resetForm, 'password');
                        echo CHtml::activePasswordField($resetForm, 'password').'<br />';
                        echo CHtml::activeLabel($resetForm, 'confirm');
                        echo CHtml::activePasswordField($resetForm, 'confirm').'<br />';
                        echo CHtml::errorSummary($resetForm);
                    }
                    echo CHtml::submitButton(Yii::t('app','Submit'),
                            array(
                                'class'=>'x2-button x2-blue',
                                'style'=>'color:white; margin: 0 auto;'));
                    ?>
                </div>
            </div><!-- #login-form -->
            <?php echo CHtml::endForm(); ?>
            <?php } else {
                echo '<hr />'.CHtml::link(Yii::t('app','Sign In'),
                                        array('/site/login'),
                                        array(
                                            'class'=>'x2-button x2-blue',
                                            'style'=>'color:white;'
                                        )
                                        );
            } ?>
        </div><!-- #login-box -->
    </div><!-- #login-page -->
</div><!-- #password-reset-form-outer -->
<div id="racing-stripe">
</div>