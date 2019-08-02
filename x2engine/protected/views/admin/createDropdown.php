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
<div class="page-title rounded-top"><h2><?php echo Yii::t('admin','Dropdown Editor'); ?></h2></div>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'field-form',
	'enableAjaxValidation'=>false,
        'action'=>'dropDownEditor',
)); ?>

    <div class="row">
        <?php 
        echo $form->labelEx($model,'name'); 
        echo $form->textField($model,'name');
        echo $form->error($model,'name'); 
        ?>
    </div>
    <div id="dropdown-options">
        <label><?php echo Yii::t('admin','Dropdown Options');?></label>
        <ol>
            <li>
                <input type="text" size="30" name="Dropdowns[options][]" />

                <div class="">
                    <a href="javascript:void(0)" 
                     onclick="x2.dropdownManager.moveOptionUp(this);">[<?php 
                        echo Yii::t('admin','Up'); ?>]</a>
                    <a href="javascript:void(0)" 
                     onclick="x2.dropdownManager.moveOptionDown(this);">[<?php 
                        echo Yii::t('admin','Down'); ?>]</a>
                    <a href="javascript:void(0)" 
                     onclick="x2.dropdownManager.deleteOption(this);">[<?php 
                        echo Yii::t('admin','Del'); ?>]</a>
                </div>
                <br />
            </li>
            <?php
            echo CHtml::activeLabel($model, 'multi', array (
                'class' => 'multi-checkbox-label',
            )).'&nbsp;'.CHtml::activeCheckBox($model, 'multi');
            ?>
        </ol>
    </div>
    <a href="javascript:void(0)" 
     onclick="x2.dropdownManager.addOption();" class="add-dropdown-option">[<?php 
        echo Yii::t('admin','Add Option'); ?>]</a>
    <div class="row buttons">
        <br />
		<?php 
        echo CHtml::submitButton(
            $model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),
            array('class'=>'x2-button')
        ); 
        ?>
    </div>
<?php $this->endWidget();?>
</div>
