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
Parameters:
    type - string, the web form type ('weblead' | 'weblist' | 'service')
    model - the model associated with the form, set to Contacts by default
*/
/* x2prostart */
/*
Additional Parameters (Pro only):
    fieldList - array of arrays - child arrays correspond to a field which should be displayed in
        the web form. Defaults to null.
    x2_key - optional string - The visitors's tracking key, set in this iframe's parent element's 
        domain
*/
/* x2proend */


mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');
Yii::app()->params->profile = ProfileChild::model()->findByPk(1);
if (empty($type)) $type = 'weblead';
if (empty($model)) $model = Contacts::model ();
/* x2prostart */
if (empty($fieldList)) $fieldList = null;
/* x2proend */


if ($type === 'service') {
    $modelName = 'Services';
} else if ($type === 'weblead'/* x2prostart */ || $type === 'weblist'/* x2proend */)  {
    $modelName = 'Contacts';
}


$defaultFields;
/* x2prostart */
if ($type === 'weblist') {
    $defaultFields = array (
        array (
            'fieldName' => 'email',
            'position' => 'top',
            'required' => 1
        )
    );
} else /* x2proend */if ($type === 'weblead') {
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

/* x2prostart */
if(!PRO_VERSION || $fieldList === null) {
/* x2proend */
    $fieldList = $defaultFields;
    $useDefaults = true;
/* x2prostart */
}
/* x2proend */

$fieldTypes = array_map (function ($elem) { if ($elem['required']) return $elem['fieldName']; }, $fieldList);

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
/* x2prostart */
if (PRO_VERSION && $type !== 'weblist') {
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
/* x2proend */
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

    if (!empty($_GET['iframeHeight'])) {
        $height = $_GET['iframeHeight'];
        unset($_GET['iframeHeight']);
    } else {
        $height = $type == 'weblist' ? 125 : 325;
    }
    if (!empty($_GET['bs'])) {
        $border = intval(preg_replace('/[^0-9]/', '', $_GET['bs']));
    } else if (!empty($_GET['bc'])) {
        $border = 1;
    } else $border = 0;
    $padding = 36;
    $height = $height - $padding - (2 * $border);

    echo 'border: '. $border .'px solid ';
    if (!empty($_GET['bc'])) echo $_GET['bc'];
    echo ";\n";

    unset($_GET['bs']);
    unset($_GET['bc']);
    ?>

    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
    border-radius: 3px;
    padding-bottom: <?php echo $padding ."px;\n"?>
    height: <?php echo $height ."px;\n"?>
}
body {
    <?php
    if (!empty($_GET['fg'])) {
        echo 'color: '. $_GET['fg'] .";\n";
    }
    unset($_GET['fg']);
    if (!empty($_GET['bgc'])) echo 'background-color: '. $_GET['bgc'] .";\n";
    unset($_GET['bgc']);
    if (!empty($_GET['font'])) {
        echo 'font-family: '. FontPickerInput::getFontCss($_GET['font']) .";\n";
    } else {
        echo "font-family: Arial, Helvetica, sans-serif;\n";
    }
    unset($_GET['font']);
    ?>
    font-size:12px;
}
input {
    border: 1px solid #AAA;
}
textarea {
    width: 166px;
    height: 100px;
}
input[type="text"] {
    width: 170px;
}
#contact-header{
    color:white;
    text-align:center;
    font-size: 16px;
}
#submit {
    float: right;
    margin-top: 7px;
    margin-bottom: 5px;
    margin-right: 0px;
}
.submit-button-row {
    width: 172px;
    height: 30px;
}
<?php
/* x2prostart */
if (PRO_VERSION && $type !== 'weblist') {
?>
div.error label, label.error, span.error {color:#C00;}
div.error input, div.error textarea, div.error select, input.error{
    background:#FEE !important;
    border-color:#C00 !important;
}
div.checkboxWrapper {
    display: inline;
}
<?php echo $css; ?>
<?php
}
/* x2proend */
?>
</style>


<?php
/* x2prostart */
if (PRO_VERSION && $type === 'weblead') {
?>
<?php echo $header; ?>
<?php
}
/* x2proend */
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
        'onSubmit'=> (($useDefaults) ? 'return validate();' : ''),
    ),
));

function renderFields ($fieldList, $type, $form, $model, $contactFields=null) {
    foreach($fieldList as $field) {
        if(!isset($field['type']) || $field['type']!='hidden'){
            if(isset($field['label']) && $field['label'] != '') {
                $label = '<label>' . $field['label'] . '</label>';
            } else {
                if($type === 'service' && in_array($field['fieldName'], $contactFields)){
                    $label = Contacts::model()->getAttributeLabel($field['fieldName']);
                }else{
                    $label = $form->labelEx($model,$field['fieldName']);
                }
            }

            // label already has a '*' to indicate it is required
            $starred = strpos($label, '*') !== false;
            ?>
            <div class="row">
                <b>
                    <?php
                    echo $label;
                    echo ($field['required'] && !$starred ? '*' : '');
                    ?>
                </b>
                <?php
            if($field['position'] == 'top') { ?>
                <br />
            <?php
            }
            echo $form->error($model, $field['fieldName']);

            if($type === 'service' && in_array($field['fieldName'], $contactFields)){ ?>
                <input type="text" name="Services[<?php echo $field['fieldName']; ?>]"
                value="<?php echo isset($_POST['Services'][$field['fieldName']]) ?
                $_POST['Services'][$field['fieldName']] : ''; ?>" />
            <?php
            } else {
                /* x2prostart */
                $f = $model->getField($field['fieldName']);
                // if date field: indicate this field needs javascript to add a date picker
                if ($f && $f->type == 'date') {  ?>
                <span class="needsDatePicker">
                    <?php echo $model->renderInput($field['fieldName']); ?>
                </span>
                <?php
                } else {
                /* x2proend */
                    echo $model->renderInput($field['fieldName']);
                /* x2prostart */
                }
                /* x2proend */
            } ?>
            </div>
<?php
        }else{
            $model->{$field['fieldName']}=$field['label'];
            echo $form->hiddenField($model, $field['fieldName']);
        }
    }
}

renderFields ($fieldList, $type, $form, $model, $contactFields);

// renders hidden tracking key field
foreach ($_GET as $key=>$value) { ?>
    <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
    <?php
}

/* x2prostart */
if (isset($x2_key)) {
?>
    <input type="hidden" name="x2_key" value="<?php echo $x2_key; ?>" />
<?php
}
/* x2proend */

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

var defaultFields = <?php echo CJSON::encode ($fieldTypes); ?>;

/*
Sets input to empty string unless it contains the default value
*/
function clearText(field){
    if (typeof field !== "undefined" && $(field).prop ('defaultValue') === $(field).val ())
        $(field).val ("");
}

/*
Add error styling if field input is invalid.
Returns: false if field input is invalid, true otherwise
*/
function validateField(field) {
    var input = $('#<?php echo $type; ?>').find (
        '[name="<?php echo $modelName; ?>[' + field + ']"]');

    if (!$(input).val () || // field is empty
        $(input).val ().match (/^\s+$/) || // field contains only whitespace
        (field == "email" && // invalid email format
         $(input).val ().match(/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/) == null)) {

        // add error styling
        $(input).css ({"border-color": "#c00"});
        $(input).css ({"background-color": "#fee"});
        return false;
    } else {

        // remove error styling
        $(input).css ({"border-color": ""});
        $(input).css ({"background-color": ""});
        return true;
    }
}

/*
Clear and validate all default input fields
*/
function validate() {

    clearText ($('#<?php echo $type; ?>').find (
        '[name="<?php echo $modelName; ?>[backgroundInfo]"]'));

    var valid = true;
    for (var i in defaultFields) {
        if (defaultFields[i] === null) continue;
        if (!validateField(defaultFields[i])) {
            valid = false;
        }
    }
    return valid;
}

<?php
/* x2prostart */
if (PRO_VERSION && $type !== 'weblist') {
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
/* x2proend */
?>
</script>

</body>
</html>
