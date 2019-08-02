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

<div class="form" id="web-form">
    

<div class="row">
    <div class="cell" style="width:400px;">
        <div style="margin-bottom: 1em;">
            <h4><?php echo Yii::t('marketing','Saved Forms').':'; ?></h4>
            <div class="row">
                <p class="fieldhelp-above" style="width: auto;">
                    <?php
                    echo Yii::t('marketing','Choose an existing form as a starting point.');
                    ?>
                </p>
                <?php
                echo $this->getDropDown();
                ?>
            </div>
            <div class="row">
                <div class='x2-button-group' id='webform-buttons'>
                    <span id='new-form' class='x2-button'><i class='fa fa-file-o'></i> <?php echo Yii::t('app','New'); ?></span
                    ><span id='save-as' class='x2-button'><i class='fa fa-save'></i> <?php echo Yii::t('app','Save As..'); ?></span
                    ><span id='delete-form' class='x2-button'><i class='fa fa-trash'></i> <?php echo Yii::t('app','Delete'); ?></span>
               </div>
            </div>
            <div class="row" id="save-field" style='display:none'>
                <?php
                echo CHtml::label(Yii::t('marketing','Name'), 'web-form-name');
                echo CHtml::textField('name', '', array (
                    "id" => 'web-form-name',
                    "class"=>"left")
                );
                echo CHtml::button (
                    Yii::t('marketing','Save'), 
                    array(
                        'name'=>'save',
                        'id'=>'web-form-submit-button',
                        'class'=>'x2-button highlight x2-small-button'
                    )
                );
                ?>
            </div>
            <div class="row" id="new-field" style='display:none'>
                <?php
                echo CHtml::label(Yii::t('marketing','Name'), 'web-form-name');
                echo CHtml::textField('name', '', array (
                    "id" => 'web-form-new-name',
                    "class"=>"left")
                );
                echo CHtml::button (
                    Yii::t('marketing','Create'), 
                    array(
                        'name'=>'save',
                        'id'=>'web-form-new-button',
                        'class'=>'x2-button highlight x2-small-button'
                    )
                );
                ?>
            </div>

        </div>
    </div>
    <div class="cell" style="max-width:400px;padding:15px">
        <?php echo $this->getDescription(); ?>
    </div>
</div>

<div class="form" id="web-form-inner">

<div class="row" style="overflow: visible;">
<?php

if ($this->edition == 'pro' && $this->type !== 'weblist'):
?>

    <div class="cell">
        <h4><?php echo Yii::t('marketing','Fields') .':'; ?></h4>
        <p class="fieldhelp" style="width: auto;">
            <?php 
            echo Yii::t('marketing', 'Drag and Drop fields from Fields List to Form.');
            ?>
        </p>
        <div>
            <div class="web-form-fields fields-container">
                <div class="fieldListTitle">
                    <?php echo Yii::t('marketing','Field List'); ?>
                </div>
                <div>
                    <ul id="sortable1" class="connectedSortable fieldlist">
                        <?php // get list of all fields, sort by attribute label alphabetically
                        $this->getStoredFields();
                        ?>
                    </ul>
                </div>
            </div>

            <div class="web-form-fields">
                <div class="fieldListTitle">
                    <?php echo Yii::t('app','Form'); ?>
                </div>
                <div>
                    <ul id="sortable2" class="connectedSortable fieldlist">
                        <?php
                        $this->getActiveFields();
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<?php
endif;

?>

    <!-- Web form Preview -->
    <div class="cell">
        <h4><?php echo Yii::t('marketing','Preview') .':'; ?></h4>
        <p class="fieldhelp" style="width: auto;">
            <?php echo Yii::t('marketing', 'Live web form preview.'); ?>
        </p>
        <div id="iframe_example" class='<?php echo $this->type ?>'></div>
    </div>
    <div class='clear'></div>
</div>


<?php echo X2Html::divider('650px', '5px'); ?>

<div class="row">
    <div class="cell">
        <h4><?php echo Yii::t('marketing','Embed Code') .':'; ?></h4>
        <p class='fieldhelp' style="width:auto"><?php
        echo Yii::t('marketing',
            'Copy and paste this code into your website to include the web lead form.');
        ?></p>
        <div id='embed-row'>
            <span class='x2-button highlight' id='generate'><?php 
            echo Yii::t('marketing','Generate HTML & Save') ?></span>
            <i class='fa fa-long-arrow-right'></i>
            <input readonly type='text' id="embedcode" 
            /><span class='x2-button' id='clipboard' title='Select Text'><i class='fa fa-clipboard'></i></span><span style='display:none'id='copy-help'><p class='fieldhelp'>
            <?php $help = Auxlib::isMac() ? "⌘-c to copy" : "ctrl-c to copy"; ?>
            <?php echo Yii::t('app', $help) ?></p></span>
        </div>
    </div>
</div>

<?php echo X2Html::divider('650px', '5px'); ?>

<h4><?php echo Yii::t('marketing', 'Additional Settings')?></h4><br/>

<div id="webform-tabs">
<?php echo CHtml::beginForm('', 'post', array ('id'=>'web-form-designer-form')); ?>
    <ul>
    </ul>

    <div class="webform-tab" id='style-tab' data-title='<?php echo Yii::t('marketing','Style'); ?>'>
        <div class='tab-content'>
            <div id="settings" class="cell">
                <h4><?php echo Yii::t('marketing','Style') .':'; ?></h4>
                <div class="cell">
                    <?php echo CHtml::label(Yii::t('marketing','Text Color'),'fg'); ?>
                    <?php echo CHtml::textField('fg', '#000000'); ?>
                    <p class="fieldhelp">
                        <?php echo Yii::t('marketing','Default') .': '. Yii::t('marketing','black'); ?>
                    </p>

                    <?php echo CHtml::label(Yii::t('marketing','Background Color'), 'bgc'); ?>
                    <?php echo CHtml::textField('bgc', '#f0f0f0'); ?>
                    <p class="fieldhelp">
                        <?php
                        echo Yii::t('marketing','Default') .': '. Yii::t('marketing','transparent');
                        ?>
                    </p>
                </div>
                <?php $fontInput = new FontPickerInput(array('name'=>'font')); ?>
                <div class="cell">
                    <?php echo CHtml::label(Yii::t('marketing','Font'), 'font'); ?>
                    <?php echo $fontInput->render(); ?>
                    <p class="fieldhelp">
                        <?php echo Yii::t('marketing','Default') .': Arial, Helvetica'; ?>
                    </p>

                    <?php echo CHtml::label(Yii::t('marketing','Border'), 'border'); ?>
                    <p class="fieldhelp half">
                        <?php echo Yii::t('marketing','Size') .' ('. Yii::t('marketing','pixels') .')'; ?>
                    </p>
                    <p class="fieldhelp half"><?php echo Yii::t('marketing','Color'); ?></p><br/>
                    <?php echo CHtml::textField('bs', '', array('class'=>'half')); ?>
                    <?php echo CHtml::textField('bc', '#f0f0f0', array('class'=>'half')); ?>
                    <p class="fieldhelp">
                        <?php echo Yii::t('marketing','Default') .': '. Yii::t('marketing','none'); ?>
                    </p>
                </div>
                <div class="cell">
                    <?php echo CHtml::label(Yii::t('marketing','Customize Thank You Text'), 'thankYouText'); ?>
                    <?php echo CHtml::textArea('thankYouText', '', array('class'=>'half')); ?>
                </div>
                <div style="display: none;">
                    <?php echo CHtml::hiddenField('type', $this->type); ?>
                </div>
            </div>
        </div>
    </div>

    <?php  if ($this->edition == 'pro'):  ?>
    <div class="webform-tab" id='advanced-tab' data-title='<?php echo Yii::t('app','Advanced'); ?>'>
        <div class='tab-content'>
            <div class="row">
                <label class='left-label' for='requireCaptcha'>
                    <?php echo Yii::t('app', 'Require CAPTCHA: '); ?>
                </label>
                <input id='require-captcha-checkbox' type='checkbox'  name='requireCaptcha'>
            </div>

            <?php if ($this->type != 'weblist'): ?>
                <div class="row" id="custom-css-input-container">
                    <h4><?php echo Yii::t('marketing','CSS') .':'; ?></h4>
                    <p class="fieldhelp">
                        <?php echo Yii::t('marketing','Enter custom css for the web form.'); ?>
                    </p>
                    <?php echo CHtml::textArea('css', '/* custom css */', array(
                        'class' => 'code',
                        'id'=>'custom-css',
                        'data-mode'=> 'css'
                    )); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif;  ?>

    <input type="hidden" name="fieldList" id="fieldList">

    <?php $this->renderSpecific() ?>
    <?php echo CHtml::endForm(); ?>
</div>

</div> <!-- web-form-inner  -->

<!-- Web form Preview -->
    <!--<div class="cell">-->
        <h4><?php echo Yii::t('marketing','Preview') .':'; ?></h4>
        <p class="fieldhelp" style="width: auto;">
            <?php echo Yii::t('marketing', 'Unsubscription form preview.'); ?>
        </p>
        <div id="iframe_unsub" class='<?php echo $this->type ?>'></div>
<div class="row">
    <div class="cell">
        <h4><?php echo Yii::t('marketing','Embed Code') .':'; ?></h4>
        <p class='fieldhelp' style="width:auto"><?php
        echo Yii::t('marketing',
            'Copy and paste this code into your website to include the web lead form.');
        ?></p>
        <div id='embed-row'>
            <input readonly type='text' id="unsubembedcode"/>
            <span class='x2-button' id='unsubclipboard' title='Select Text'><i class='fa fa-clipboard'></i></span>
            <span style ='display:none' id='unsub-copy-help'><p style = 'display:inline' class='fieldhelp'>
            <?php $help = Auxlib::isMac() ? "⌘-c to copy" : "ctrl-c to copy"; ?>
            <?php echo Yii::t('app', $help) ?></p></span>
        </div>
    </div>
</div>

</div> <!-- Web form outer -->

    <!--</div>-->
    
    
