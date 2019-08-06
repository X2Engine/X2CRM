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




/*
Parameters:
    type - string, the web form type ('weblead' | 'weblist' | 'service')
    model - the model associated with the form, set to Contacts by default
*/

/*
Additional Parameters (Pro only):
    fieldList - array of arrays - child arrays correspond to a field which should be displayed in
        the web form. Defaults to null.
*/


mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');
Yii::app()->params->profile = Profile::model()->findByPk(1);
if (empty($type)) $type = 'weblead';
if (empty($model)) $model = Contacts::model ();

if ($type === 'service') {
    $modelName = 'Services';
} else if ($type === 'weblead' || $type === 'weblist')  {
    $modelName = 'Contacts';
}


$defaultFields;

if ($type === 'weblist') {
    $defaultFields = array (
        array (
            'fieldName' => 'email',
            'position' => 'top',
            'required' => 1
        )
    );
} else if ($type === 'weblead') {
    $defaultFields = array (
        array (
            'fieldName' => 'firstName',
            'position' => 'top',
            'required' => 1
        ),
        array (
            'fieldName' => 'lastName',
            'position' => 'top',
            'required' => 1
        ),
        array (
            'fieldName' => 'email',
            'position' => 'top',
            'required' => 1
        ),
        array (
            'fieldName' => 'phone',
            'position' => 'top',
            'required' => 0
        ),
        array (
            'fieldName' => 'backgroundInfo',
            'position' => 'top',
            'required' => 0
        ),
    );
} else if ($type === 'service') {
    $defaultFields = array (
        array (
            'fieldName' => 'firstName',
            'position' => 'top',
            'required' => 1
        ),
        array (
            'fieldName' => 'lastName',
            'position' => 'top',
            'required' => 1
        ),
        array (
            'fieldName' => 'email',
            'position' => 'top',
            'required' => 1
        ),
        array (
            'fieldName' => 'phone',
            'position' => 'top',
            'required' => 0
        ),
        array (
            'fieldName' => 'description',
            'position' => 'top',
            'required' => 0
        ),
    );
}

$useDefaults = false;
if(empty($fieldList)){
    $useDefaults = true;
    $fieldList = $defaultFields;
}

$fieldTypes = array_map (function ($elem) { 
    if ($elem['required']) return $elem['fieldName']; }, $fieldList);

if ($type === 'service') {
    $contactFields = array('firstName', 'lastName', 'email', 'phone');
} else {
    $contactFields = null;
}

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"
 xml:lang="<?php echo Yii::app()->language; ?>" lang="<?php echo Yii::app()->language; ?>">
<head>
<meta charset="UTF-8" />
<meta name="language" content="<?php echo Yii::app()->language; ?>" />
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
<?php $this->renderGaCode('public'); ?>

<script type="text/javascript"
 src="<?php echo Yii::app()->clientScript->coreScriptUrl . '/jquery.js'; ?>">
</script>

<?php

if (Yii::app()->contEd('pro') && $type !== 'weblist') {
?>
    <link rel="stylesheet" type="text/css"
     href="<?php echo Yii::app()->clientScript->coreScriptUrl . '/jui/css/base/jquery-ui.css'; ?>"
    />
    <script type="text/javascript"
        src="<?php echo Yii::app()->clientScript->coreScriptUrl . '/jui/js/jquery-ui.min.js'; ?>">
    </script>
    <script type="text/javascript"
        src="<?php echo Yii::app()->clientScript->coreScriptUrl .
            '/jui/js/jquery-ui-i18n.min.js'; ?>">
    </script>
    <?php
}


if (Yii::app()->contEd('pla')) {
    if (Yii::app()->settings->enableFingerprinting && (!isset ($_SERVER['HTTP_DNT']) || $_SERVER['HTTP_DNT'] != 1) && $model instanceof Contacts) {
    ?>
    <script type="text/javascript"
        src="<?php echo Yii::app()->getBaseUrl().'/js/fontdetect.js'; ?>">
    </script>
    <script type="text/javascript"
        src="<?php echo Yii::app()->getBaseUrl().'/js/X2Identity.js'; ?>">
    </script>
<?php
    }
}

?>

<style type="text/css">
html {
    <?php
    /* Dear future editors:
      The pixel height of the iframe containing this page
      should equal the sum of height, padding-bottom, and 2x border size
      specified in this block, else the bottom border will not be at the
      bottom edge of the frame. Now it is based on 325px height for weblead,
      and 100px for weblist */

    if (isset ($iframeHeight)) {
        $height = $iframeHeight;
    } else {
        $height = $type == 'weblist' ? 125 : 325;
    }
    if (isset ($bs)) {
        $border = intval(preg_replace('/[^0-9]/', '', $bs));
    } else if (isset ($bc)) {
        $border = 0;
    } else $border = 0;
    $padding = 36;
    $height = $height - $padding - (2 * $border);

    echo 'border: '. $border .'px solid ';
    if (isset ($bc)) echo addslashes ($bc);
    echo ";\n";

    ?>

    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
    border-radius: 3px;
    padding-bottom: <?php echo addslashes ($padding) ."px;\n"?>
    height: <?php echo addslashes ($height) ."px;\n"?>
}
body {
    <?php
    if (isset ($fg)) {
        echo 'color: '. addslashes ($fg) .";\n";
    }
    if (isset ($bgc)) echo 'background-color: '. addslashes ($bgc) .";\n";
    if (isset ($font)) {
        echo 'font-family: '. addslashes (FontPickerInput::getFontCss($font)) .";\n";
    } else {
        echo "font-family: Arial, Helvetica, sans-serif;\n";
    }
    ?>
    font-size:12px;
}

input {
    border: 1px solid #AAA;
    font-family: inherit;
}
.row {
    margin-bottom: 10px;
}
textarea {
    box-sizing: border-box;
    width: 100%;
    height: 100px;
    border-radius: 2px;
    font-family: inherit;
}
input[type="text"] {
    box-sizing: border-box;
    padding: 2px;
    border-radius: 2px;
    line-height: 1.5em;
}
input[type="text"] {
    width: 100%;
}
input[type="file"] {
    width: 100%;
}
#captcha-image {
    margin-left: auto;
    margin-right: auto;
}
#contact-header{
    color:white;
    text-align:center;
    font-size: 16px;
}
#submit {
    box-sizing:border-box;
    float: right;
    margin-top: 7px;
    margin-bottom: 5px;
    padding: 7px;
    border-radius: 2px;
    width: 100%;
    font-family: inherit;
}
#submit:hover {
    
}
.submit-button-row {
    height: 30px;
}
<?php

if (Yii::app()->contEd('pro') && $type !== 'weblist') {
?>
div.error label, label.error, span.error, div.error, label.error + .asterisk, .errorMessage {
    color:#C00;
}
div.error input, div.error textarea, div.error select, input.error{
    background:#FEE !important;
    border-color:#C00 !important;
}
div.checkboxWrapper {
    display: inline;
}
<?php 
echo $css; 
}

?>
</style>

<?php

if (Yii::app()->contEd('pro') && $type === 'weblead') {
    echo $header; 
}

?>
</head>
<body>

<?php
foreach(Yii::app()->user->getFlashes() as $key => $message) {
    echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
}

$form = $this->beginWidget('CActiveForm', array(
    'id'=>$type,
    'enableAjaxValidation'=>false,
    'htmlOptions'=>array(
        'enctype' => 'multipart/form-data'
    ),
));

function renderFields ($fieldList, $type, $form, $model, $contactFields=null) {
    foreach($fieldList as $field) {
        $fieldName = $field['fieldName'];
        if(!isset($field['type']) || $field['type']==='normal'){
            if(isset($field['label']) && $field['label'] != '') {
                $label = '<label>' . $field['label'] . '</label>';
            } else {
                if($type === 'service' && in_array($field['fieldName'], $contactFields)){
                    $contact = clone Contacts::model ();
                    if ($model->hasErrors ($fieldName)) {
                        $contact->addError ($fieldName, $model->getError ($fieldName));
                    }
                    $label = $form->labelEx ($contact, $fieldName);
                }else{
                    $label = $form->labelEx($model,$field['fieldName']);
                }
            }

            // label already has a '*' to indicate it is required
            $starred = strpos($label, '*') !== false;
            ?>
            <div class="row">
                <?php
                echo $label;
                echo ($field['required'] && !$starred ? 
                    '<span class="asterisk"> *</span>' : '');
                if($field['position'] == 'top') { ?>
                    <br />
                <?php
                }
            echo $form->error($model, $field['fieldName']);

            if($type === 'service' && in_array($field['fieldName'], $contactFields)){ 
                echo CHtml::tag ('input', array (
                    'type' => 'text',
                    'name' => 'Services['.$field['fieldName'].']',
                    'class' => $model->hasErrors ($field['fieldName']) ? 'error' : '',
                    'value' => isset ($_POST['Services'][$field['fieldName']]) ?
                        $_POST['Services'][$field['fieldName']] : '',
                ));
            } else {
                
                $f = $model->getField($field['fieldName']);
                // if date field: indicate this field needs javascript to add a date picker
                if ($f && $f->type == 'date') {  ?>
                <span class="needsDatePicker">
                    <?php echo $model->renderInput($field['fieldName']); ?>
                </span>
                <?php
                } else {
                
                    echo $model->renderInput($field['fieldName']);
                
                }
                
            } ?>
            </div>
<?php
        }elseif ($field['type'] === 'tags') {
            ?>
            <input type="hidden" name="tags" value="<?php echo $field['label']?>" />
            <?php
        }elseif ($field['type'] === 'hidden') {
            $model->{$field['fieldName']}=$field['label'];
            echo $form->hiddenField($model, $field['fieldName']);
        }
    }
}

renderFields ($fieldList, $type, $form, $model, $contactFields);

// Render CAPTCHA if requested
if (isset($requireCaptcha) && $requireCaptcha && CCaptcha::checkRequirements()) {
    echo '<div id="captcha-container">';
    $form->widget('CCaptcha', array(
        'captchaAction' => 'site/webleadCaptcha',
        'imageOptions' => array(
            'id' => 'captcha-image',
            'style' => 'display:block',
        )
    )); echo '</div>';
    echo '<p class="hint">'.Yii::t('app', 'Please enter the letters in the image above.').'</p>';
    echo $form->error($model, 'verifyCode');
    echo $form->textField($model, 'verifyCode');
}

if ($type !== 'service' && Yii::app()->settings->enableFingerprinting && (!isset ($_SERVER['HTTP_DNT']) || $_SERVER['HTTP_DNT'] != 1)) {
?>
    <input type="hidden" name="fingerprint" id="fingerprint"></input>
    <input type="hidden" name="fingerprintAttributes" id="fingerprintAttributes"></input>
    <script>
        (function () {
            var fingerprintData = x2Identity.fingerprint();
            $("#fingerprint").val(fingerprintData["fingerprint"]);
            $("#fingerprintAttributes").val(JSON.stringify (fingerprintData["attributes"]));
        }) ();
    </script>
<?php
}

if ($type === 'weblead' && !empty ($_SERVER['HTTPS']) && (!isset ($_SERVER['HTTP_DNT']) || $_SERVER['HTTP_DNT'] != 1)) {
?>
    <input type="hidden" name="geoCoords" id="geoCoords"></input>
    <script>
        (function () {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(function(position) {
                var pos = {
                  lat: position.coords.latitude,
                  lon: position.coords.longitude
                };

                $("#geoCoords").val(JSON.stringify (pos));
              }, function() {
                console.log("error fetching geolocation data");
              });
            }
        }) ();
    </script>
<?php
}

?>
<div class="submit-button-row row">
<?php
echo CHtml::submitButton(Yii::t('app','Submit'),array('id'=>'submit'));
?>
</div>

<?php
$this->endWidget();
?>

<script>
(function () {
    // prevent duplicate submissions
    $('form').submit (function () {
        $(this).find ('#submit').attr ('disabled', 'disabled');
    });
}) ();

<?php


if (Yii::app()->contEd('pro') && $type !== 'weblist') {
// TODO: move web form html into a layout so that this JS gets registered automatically by CJuiDateTimePicker
?>
$(function() {
    $('span.needsDatePicker input').datepicker(
        jQuery.extend(
            {showMonthAfterYear:false},
            jQuery.datepicker.regional[
                '<?php echo (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(); ?>'],
            {
                'dateFormat':'<?php echo Formatter::formatDatePicker(); ?>',
                'changeMonth':true,
                'changeYear':true
            }
        )
    );
});
<?php
}

?>
</script>

</body>
</html>
