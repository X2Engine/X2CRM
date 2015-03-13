<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

/*
View file for weblead and service web form desginer (both pro and open source).
Parameters:
    webFormType - string ('weblead' | 'service' | 'weblist') used to specify whether this
        view file is for the weblead form designer or for the service web form designer
    forms - Saved forms which will be sent to the client and cached with JS
    id - the list id (defaults to null)
*/

if (YII_DEBUG &&
    (!isset ($webFormType) ||
     $webFormType !== 'service' &&
     $webFormType !== 'weblead')) {

    /**/AuxLib::debugLog ('Error: _createWebForm.php: invalid $webFormType type '.$webFormType);
}



    $height = 325;


if ($webFormType === 'weblead') {
    $url = '/contacts/contacts/weblead';
} else if ($webFormType === 'service') {
    $url = '/services/services/webForm';
}


$iframeSource = Yii::app()->createExternalUrl($url);
$externalAbsoluteBaseUrl = Yii::app()->getExternalAbsoluteBaseUrl ();

//get form attributes only for generating json
$formAttrs = array();
foreach ($forms as $form) {
    $formAttrs[] = $form->attributes;
}

$translations = array (
    'formSavedMsg' => 'Form Saved',
    'nameRequiredMsg' => 'Name cannot be blank.'
);



AuxLib::registerTranslationsScript ('webFormDesigner', $translations, 'marketing');

Yii::app()->clientScript->registerCssFile(
    Yii::app()->getTheme()->getBaseUrl().'/css/createWebForm.css');

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->getBaseUrl().'/js/WebFormDesigner/WebFormDesigner.js',CClientScript::POS_END);

    if ($webFormType === 'weblead') {
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl().'/js/WebFormDesigner/WebleadFormDesigner.js',CClientScript::POS_END);
    } else if ($webFormType === 'service') {
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl().'/js/WebFormDesigner/ServiceWebFormDesigner.js',CClientScript::POS_END);
    }


$webFormDesignerProtoName;
if ($webFormType === 'weblead' || $webFormType === 'weblist') {
    $webFormDesignerProtoName = 'WebleadFormDesigner';
} else if ($webFormType === 'service') {
    $webFormDesignerProtoName = 'ServiceWebFormDesigner';
}



?>
<style>
#iframe_example {
    height: <?php echo $height + 25; ?>px;
    width: 200px;
}
</style>
<?php

$saveUrl = '';
if ($webFormType === 'sevice') {
    $saveUrl = Yii::app()->createAbsoluteUrl('/services/createWebForm');
} elseif ($webFormType === 'weblead') {

    $saveUrl = Yii::app()->createAbsoluteUrl('/marketing/marketing/webleadForm');
}

Yii::app()->clientScript->registerScript('webleadForm','
    x2.WebFormDesigner = '.
        'new '.$webFormDesignerProtoName.' ({'.
       'translations: x2.webFormDesigner.translations,
        iframeSrc: "'.addslashes($iframeSource).'",
        externalAbsoluteBaseUrl: "'.addslashes($externalAbsoluteBaseUrl).'",
        saveUrl: "'.addslashes ($saveUrl).'",
        savedForms: '.CJSON::encode($formAttrs).',
        deleteFormUrl: "'.Yii::app()->createAbsoluteUrl (
            '/marketing/marketing/ajaxDeleteWebForm').'",
        fields: ["fg","bgc","font","bs","bc"],
        colorfields: ["fg","bgc","bc"],
        listId: '.(!empty($id) ? $id : 'null').'
    });
',CClientScript::POS_END);
?>

<div class="form" id="web-form">

<div class="row">
    <div class="cell">
        <h4><?php echo Yii::t('marketing','Embed Code') .':'; ?></h4>
        <textarea id="embedcode"></textarea><br/>
        <?php
        echo Yii::t('marketing',
            'Copy and paste this code into your website to include the web lead form.');
        ?>
        <br /><br />
    </div>
</div>

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

                // so the dropdown will have a blank choice
                array_unshift($formAttrs, array('id'=>'0', 'name'=>'------------'));
                echo CHtml::dropDownList(
                    'saved-forms', '',
                    CHtml::encodeArray(CHtml::listData($formAttrs, 'id', 'name')),
                    array (
                        'class' => 'left'
                    ));
        		echo CHtml::button (
                    Yii::t('marketing','Reset Form'),
                    array(
                        'id' => 'reset-form',
                        'class'=>'x2-button x2-small-button'
                    )); 
        		/*echo CHtml::button (
                    Yii::t('marketing','Delete Form'),
                    array(
                        'id' => 'delete-form',
                        'class'=>'x2-button x2-small-button',
                        'style' => 'display: none;' 
                    )); */
                ?>
            </div>
        </div>
    </div>

    <?php echo CHtml::beginForm('', 'post', array ('id'=>'web-form-designer-form')); ?>

    <div class="cell">
        <h4 style="margin-bottom: 0;"><?php echo Yii::t('marketing','Save') .':'; ?></h4>
        <div class="row">
            <p class="fieldhelp-above" style="width: auto;">
                <?php echo Yii::t('marketing','Enter a name and save this form to edit later.'); ?>
            </p>
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
                    'class'=>'x2-button x2-small-button'
                )
            );
            /*echo CHtml::ajaxSubmitButton(
                Yii::t('marketing','Save'), $saveUrl,
                array(
                    'success'=>'function(data, status, xhr) {
                        x2.WebFormDesigner.saved(data, status, xhr);
                    }',
                ),
    	    	array(
                    'id'=>'web-form-save-button',
                    'name'=>'save',
                    'class'=>'x2-button x2-small-button'
                )
            );*/

            ?>
        </div>
    </div>

</div>

<?php
if ($webFormType === 'weblead') {
?>
<div class='row'>
    <div class="cell">
        <label class='left-label' 
         for='generateLead'><?php echo Yii::t('app', 'Generate {Lead}: ', array(
             '{Lead}'=>Modules::displayName(false, 'X2Leads')
         )); ?></label>
        <input id='generate-lead-checkbox' type='checkbox'  name='generateLead'>
        <?php
        echo X2Html::hint (
            Yii::t('app', 'If you have this box checked, a new {lead} record will be associated '.
                'with the new {contact} when the web lead form is submitted. The web lead form '.
                'must be saved for this feature to take effect.', array(
                    '{lead}'=>strtolower(Modules::displayName(false, 'X2Leads')),
                    '{contact}'=>strtolower(Modules::displayName(false, 'Contacts'))
                )), false, null, true);
        ?>
        <div id='generate-lead-form' style='display: none;'>
        <?php
        echo CHtml::activeLabel (X2Model::model ('Contacts'), 'leadSource');
        echo X2Model::model ('X2Leads')->renderInput (
            'leadSource', array ('class' => 'left-label', 'name' => 'leadSource'));
        ?>
        </div>
    </div>
</div>
<div class='row'>
    <div class="cell">
        <label class='left-label' 
         for='generateAccount'><?php echo Yii::t('app', 'Generate {Account}: ', array(
             '{Account}'=>Modules::displayName(false, 'Accounts')
        )); ?></label>
        <input id='generate-account-checkbox' type='checkbox'  name='generateAccount'>
        <?php
        echo X2Html::hint (
            Yii::t('app', 'If you have this box checked, a new {account} record will be generated '.
                'using the new {contact}\'s company field when the web lead form is submitted. The '.
                'web lead form must be saved for this feature to take effect.', array(
                    '{account}'=>strtolower(Modules::displayName(false, 'Accounts')),
                    '{contact}'=>strtolower(Modules::displayName(false, 'Contacts'))
                )), false, null, true);
        ?>
    </div>
</div>
<?php
}
?>

<div class="row">
    <div id="settings" class="cell">
        <h4><?php echo Yii::t('marketing','Settings') .':'; ?></h4>
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
        <div style="display: none;">
            <?php echo CHtml::hiddenField('type', $webFormType); ?>
        </div>
    </div>
</div>

<?php

?>

<?php echo CHtml::endForm(); ?>


<?php

?>
<?php
$disclaimer;
if ($webFormType === 'service') {
    $disclaimer = Yii::t('marketing', 
        'The web form must be saved for custom fields to get '.
        'included. Changes made to the custom fields will '.
        'not be reflected in the preview until the web form is saved.');
} else if ($webFormType === 'weblead') {
    $disclaimer = Yii::t('marketing', 
        'The web form must be saved for your custom fields or custom HTML to '.
        'get included. Changes made to the custom fields or custom HTML will '.
        'not be reflected in the preview until the web form is saved.');
}
?>
    <div class="cell">
        <h4><?php echo Yii::t('marketing','Preview') .':'; ?></h4>
        <p class="fieldhelp" style="width: auto;">
            <?php echo Yii::t('marketing', 'Live web form preview.'); ?>
            <?php
            if (Yii::app()->contEd('pro') && isset($disclaimer)) {
            ?>
            <span class='x2-hint' title='<?php 
             echo $disclaimer; ?>'>[<span class='x2-hint-asterisk'>*</span>]</span>
            <?php
            }
            ?>
        </p>
        <div id="iframe_example"></div>
    </div>

<?php

?>
</div>
