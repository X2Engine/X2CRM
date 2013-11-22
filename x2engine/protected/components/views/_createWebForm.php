<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

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
     $webFormType !== 'weblead'/* x2prostart */ &&
     $webFormType !== 'weblist'/* x2proend */)) {

    AuxLib::debugLog ('Error: _createWebForm.php: invalid $webFormType type '.$webFormType);
}


Yii::app()->clientScript->registerScriptFile(
    Yii::app()->getBaseUrl().'/js/spectrumSetup.js', CClientScript::POS_END);

/* x2prostart */
if ($webFormType === 'weblist') {
    $height = 100;
} else {
/* x2proend */
    $height = 325;
/* x2prostart */
}
/* x2proend */

if ($webFormType === 'weblead') {
    $url = '/contacts/contacts/weblead';
} else if ($webFormType === 'service') {
    $url = '/services/services/webForm';
}/* x2prostart */ else if ($webFormType === 'weblist') {
    $url = '/marketing/weblist/weblist';
}/* x2proend */


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

/* x2prostart */
if ($webFormType === 'weblead' && PRO_VERSION) {
    $translations = array_merge ($translations, array (
        "Custom HTML cannot be added to the web form until it has been saved." =>
            "Custom HTML cannot be added to the web form until it has been saved.",
        "HTML cannot be empty." => "HTML cannot be empty.",
        "HTML saved" => "HTML saved",
        "HTML removed" => "HTML removed"
    ));
}
/* x2proend */

AuxLib::registerTranslationsScript ('webFormDesigner', $translations, 'marketing');

Yii::app()->clientScript->registerCssFile(
    Yii::app()->getTheme()->getBaseUrl().'/css/createWebForm.css');
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->getBaseUrl().'/js/WebFormDesigner/WebFormDesigner.js',CClientScript::POS_END);
/* x2prostart */
if (PRO_VERSION && $webFormType !== 'weblist') {
    Yii::app()->clientScript->registerScriptFile(
        Yii::app()->getBaseUrl().'/js/WebFormDesigner/WebFormDesignerPro.js',CClientScript::POS_END);
    if ($webFormType === 'weblead') {
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl().'/js/WebFormDesigner/WebleadFormDesignerPro.js',CClientScript::POS_END);
    } else if ($webFormType === 'service') {
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl().'/js/WebFormDesigner/ServiceWebFormDesignerPro.js',CClientScript::POS_END);
    }
} else {
/* x2proend */
    if ($webFormType === 'weblead'/* x2prostart */ ||
        $webFormType === 'weblist'/* x2proend */) {
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl().'/js/WebFormDesigner/WebleadFormDesigner.js',CClientScript::POS_END);
    } else if ($webFormType === 'service') {
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->getBaseUrl().'/js/WebFormDesigner/ServiceWebFormDesigner.js',CClientScript::POS_END);
    }
/* x2prostart */
}
/* x2proend */

$webFormDesignerProtoName;
if ($webFormType === 'weblead' || $webFormType === 'weblist') {
    $webFormDesignerProtoName = 'WebleadFormDesigner';
} else if ($webFormType === 'service') {
    $webFormDesignerProtoName = 'ServiceWebFormDesigner';
}

/* x2prostart */
if (PRO_VERSION && $webFormType !== 'weblist') {
    $webFormDesignerProtoName .= 'Pro';
}
/* x2proend */

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
} elseif ($webFormType === 'weblead'/* x2prostart */ ||
    $webFormType === 'weblist'/* x2proend */) {

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
        fields: ["fg","bgc","font","bs","bc","tags"],
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
                    )); ?>
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
            /*echo CHtml::submitButton(
                Yii::t('marketing','Save'), 
    	    	array(
                    'name'=>'save',
                    'id'=>'web-form-submit-button',
                    'class'=>'x2-button x2-small-button'
                )
            );*/
            echo CHtml::ajaxSubmitButton(
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
            );

            ?>
        </div>
    </div>

</div>

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
        <div class="cell" <?php if ($webFormType !== 'weblead') echo 'style="display: none;"'; ?>>
            <?php echo CHtml::label(Yii::t('marketing','Tags'), 'tags'); ?>
            <?php echo CHtml::textField('tags'); ?>
            <p class="fieldhelp">
                <em><?php echo Yii::t('marketing','Example') .': web,newlead,urgent'; ?></em>
                <br/>
                <?php
                echo Yii::t(
                    'marketing','These tags will be applied to any contact created by the form.');
                ?>
            </p>
        </div>
        <div style="display: none;">
            <?php echo CHtml::hiddenField('type', $webFormType); ?>
        </div>
    </div>
</div>

<?php
/* x2prostart */
if (PRO_VERSION && $webFormType !== 'weblist') {
?>

<div class="row">
    <div class="cell" id="custom-css-input-container">
        <h4><?php echo Yii::t('marketing','CSS') .':'; ?></h4>
        <p class="fieldhelp">
            <?php echo Yii::t('marketing','Enter custom css for the web form.'); ?>
        </p>
        <?php echo CHtml::textArea('css', '', array('id'=>'custom-css')); ?>
    </div>
</div>

<?php
if ($webFormType === 'weblead') {
?>

<div class="row">
    <div class="cell" id="custom-html-input-container">
        <h4>
            <?php echo Yii::t('marketing','Custom &lt;HEAD&gt;') .':'; ?>
        </h4>
        <span id='custom-html-hint'>
        <p class="fieldhelp" style="width: 580px;">
            <?php echo Yii::t('marketing',
                'Enter any HTML you would like inserted into the &lt;HEAD&gt; tag.'); ?>
        </p>
            <?php echo CHtml::textArea('header', '', array('id'=>'custom-html')); ?>
        <br/>
    </div>
</div>

<div class="row">
    <div class="cell">
        <h4><?php echo Yii::t('marketing','Email') .':'; ?></h4>
        <p class="fieldhelp" style="width: auto;">
            <?php
            echo Yii::t(
                'marketing','Select email templates to send to the new weblead and the user '.
                'assigned to the weblead.');
            ?>
            <br />
            <?php
            echo Yii::t(
                'marketing', 'NOTE: The weblead form must be saved for these emails to be sent.');
            ?>
        </p>
        <?php $templateList = array(''=>'------------') + Docs::getEmailTemplates(); ?>
        <div class="cell">
            <?php echo CHtml::label(Yii::t('marketing','User Email'), ''); ?>
            <?php echo CHtml::dropDownList('user-email-template', '', $templateList); ?>
        </div>
        <div class="cell">
            <?php echo CHtml::label(Yii::t('marketing','Weblead Email'), ''); ?>
            <?php echo CHtml::dropDownList('weblead-email-template', '', $templateList); ?>
        </div>
    </div>
</div>

<?php
}
?>

<input type="hidden" name="fieldList" id="fieldList">

<?php
}
/* x2proend */
?>

<?php echo CHtml::endForm(); ?>


<?php
/* x2prostart */
if (PRO_VERSION && $webFormType !== 'weblist') {
?>

<?php

if ($webFormType === 'weblead') {
    $defaultList = array('firstName', 'lastName', 'email', 'phone', 'backgroundInfo');
    $exclude = array('account', 'assignedTo', 'dupeCheck', 'id', 'visibility', 'trackingKey');
} else if ($webFormType === 'service') {
    $defaultList = array('firstName', 'lastName', 'email', 'phone');
    $exclude = array('description');
}


/*
Inserts a single custom field element into the DOM
*/
function displayCustomField ($field, $type, $item, $editable=false) {
    echo '<li class="um-state-default" name="'.$field->fieldName.'">';
    echo "<label class=\"$type\">".
        Yii::t('services',$field->attributeLabel)."</label>";
    if ($editable) {
        echo '<div style="display: inline;">';
    } else {
        echo '<div style="display: none;">';
    }
    if($field->required) {
        echo CHtml::checkbox(
            $field->fieldName . '_checkbox', true,
            array(
                'style'=>'margin-left: 5px;',
                //'onclick'=> ($editable ? '' : 'return false;'),
                'onclick'=> 'return false;',
                'onkeydown'=>'return false;'
            )
        );
    } else if ($editable && $item == 'email') {
        echo CHtml::checkbox(
            $field->fieldName . '_checkbox', true,
            array(
                'style'=>'margin-left: 5px;',
                'onchange'=>'x2.WebFormDesigner._onFieldUpdate (); return false;'
            )
        );
    } else {
        echo CHtml::checkbox(
            $field->fieldName . '_checkbox', false,
            array(
                'style'=>'margin-left: 5px;',
                'onchange'=>'x2.WebFormDesigner._onFieldUpdate (); return false;'
            )
        );
    }

    echo CHtml::label(
        Yii::t('app','Required'),
        $field->fieldName . '_checkbox',
        array('style'=>'display: inline; padding-left: 3px',)
    );
    echo '<br />';
    echo CHtml::label(
        Yii::t('marketing','Label:').' ',
        $field->fieldName . '_label',
        array(
            'style'=>'display: inline; padding: 0;',
            'id'=>$field->fieldName.'_label_text'
        )
    );
    echo CHtml::textField(
        $field->fieldName . '_label', '',
        array('style'=>'width: 100px; padding: 0; margin: 0;')
    );
    echo CHtml::label(
        Yii::t('marketing','Position:').' ',
        $field->fieldName . '_label',
        array('style'=>'display: inline; padding: 0;')
    );
    echo CHtml::dropDownList(
        $field->fieldName . '_position', 'top',
        array('top'=>Yii::t('app','top'), 'left'=>Yii::t('app','left')),
        array(
            'class'=>'field-position',
            'onchange'=>'x2.WebFormDesigner._onFieldUpdate (); return false;'
        )
    );
    echo "<br>";
    echo CHtml::label(
        Yii::t('marketing','Type:').' ',
        $field->fieldName . '_label',
        array('style'=>'display: inline; padding: 0;')
    );
    echo CHtml::dropDownList(
        $field->fieldName . '_type', 'normal',
        array('normal'=>Yii::t('app','normal'), 'hidden'=>Yii::t('app','hidden')),
        array(
            'class'=>'field-type',
            'onchange'=>
                'x2.WebFormDesigner._onFieldUpdate ();
                if($(this).val()=="hidden"){
                    $("#'.$field->fieldName.'_label_text").html("'.Yii::t('marketing',"Value:").'");
                }else{
                    $("#'.$field->fieldName.'_label_text").html("'.Yii::t('marketing',"Label:").'");
                }'.
                'return false;'
        )
    );
    echo '</div>';
    echo '</li>';
}

/*
Used to construct the custom fields editor ui elements
*/
function buildSortableCustomFields (
    $fields, $item=null, $editable=false, $defaultList=null, $exclude=null) {

    foreach($fields as &$field) {

    if((!$editable &&
        (!in_array($field->fieldName, $defaultList) &&
         !in_array($field->fieldName, $exclude) && $field->readOnly == false)) ||
      ($editable &&
       $field->fieldName == $item)) {
            $type = '';
            switch($field->type) {
                case 'email':
                    $type = 'emailIcon';
                    break;
                case 'phone':
                    $type = 'phoneIcon';
                    break;
                case 'boolean':
                    $type = 'booleanIcon';
                    break;
                case 'dropdown':
                    $type = 'dropdownIcon';
                    break;
                case 'date':
                    $type = 'dateIcon';
                    break;
                case 'text':
                    $type = 'textIcon';
                    break;
                default:
                    $type = 'varcharIcon';
            }
            displayCustomField ($field, $type, $item, $editable);
        }
    }
}

?>

<br />
<div class="row" style="overflow: visible;">

    <div class="cell">
        <h4><?php echo Yii::t('marketing','Fields') .':'; ?></h4>
        <p class="fieldhelp" style="width: auto;">
            <?php echo Yii::t('marketing', 'Drag and Drop fields from Fields List to Form.'); 
            ?>
        </p>
        <div>
            <div class="web-form-fields">
                <div class="fieldListTitle">
					<?php echo Yii::t('marketing','Field List'); ?>
                </div>
                <div>
                    <ul id="sortable1" class="connectedSortable fieldlist">
                        <?php // get list of all fields, sort by attribute label alphabetically
                        if ($webFormType === 'weblead') {
                            $modelName = 'Contacts';
                        } else if ($webFormType === 'service') {
                            $modelName = 'Services';
                        }
                        $fields = Fields::model()->findAllByAttributes(
                            array(
                                'modelName'=> $modelName
                            ),
                            new CDbCriteria(array('order'=>'attributeLabel ASC'))
                        );
                        buildSortableCustomFields ($fields, null, false, $defaultList, $exclude);
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

                        if ($webFormType === 'service') {
                            $fields = Fields::model()->findAllByAttributes(
                                array('modelName'=>'Contacts'),
                                new CDbCriteria(array('order'=>'attributeLabel ASC'))
                            );
                        }
                        foreach($defaultList as $item) {
                            buildSortableCustomFields ($fields, $item, true);
                        }

                        if ($webFormType === 'service') {
                            $field = Fields::model()->findAllByAttributes(
                                array('modelName'=>'Services', 'fieldName'=>'description'));
                            $field = $field[0];
                            $type = 'textIcon';
                            displayCustomField ($field, $type, $item, true);
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="cell">

<?php
}
/* x2proend */
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
            if (PRO_VERSION && isset($disclaimer)) {
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
/* x2prostart */
if (PRO_VERSION && $webFormType !== 'weblist') {
?>

    </div>
</div>

<?php
}
/* x2proend */
?>
</div>
