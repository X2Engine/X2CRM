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




$js = "
$('#contacts-form input, #contacts-form select, #contacts-form textarea').change(function() {
    $('#save-button, #save-button1, #save-button2').addClass('highlight'); //css('background','yellow');
});
";
$assets = Yii::app()->getAssetManager()->publish(
    Yii::getPathOfAlias('application.extensions.CJuiDateTimePicker').DIRECTORY_SEPARATOR.'assets'
);
$cs = Yii::app()->getClientScript();
$cs->registerScript('highlightSaveContact',$js,CClientScript::POS_READY);
$cs->registerCssFile($assets.'/jquery-ui-timepicker-addon.css');
$cs->registerScriptFile($assets.'/jquery-ui-timepicker-addon.js',CClientScript::POS_END);

// $cs->registerScript(__CLASS__,     $this->defaultOptions?'jQuery.{$this->mode}picker.setDefaults('.CJavaScript::encode($this->defaultOptions).');':'');
// $cs->registerScript(__CLASS__.'#'.$id, $js);





$fieldTypes = array();
$fieldLinkTypes = array();
$fieldOptions = array();
foreach($itemModel->getFields() as $field) {
    $fieldTypes[$field->fieldName] = $field->type;
    if(!empty($field->linkType)) {
        $fieldLinkTypes[$field->fieldName] = $field->linkType;
    }
    switch ($field->type) {
        case 'dropdown':
            $fieldOptions[$field->fieldName] = Dropdowns::getItems($field->linkType);
            break;
        case 'assignment':
            $fieldOptions[$field->fieldName] = User::getNames() + Groups::getNames();
            break;
        case 'link':
            $fieldOptions[$field->fieldName] = Yii::app()->request->scriptUrl. X2Model::model($field->linkType)->autoCompleteSource;
            break;
    }
}
$attributeLabels = $model->itemAttributeLabels;

//hack tags in
$fieldTypes['tags'] = 'tags';
$fieldOptions['tags'] = Tags::getAllTags();
$attributeLabels['tags'] = Yii::t('contacts','Tags');
natcasesort($attributeLabels);

$comparisonList = array(
    '='=>Yii::t('contacts','equals'),
    '>'=>Yii::t('contacts','greater than or equal to'),
    '<'=>Yii::t('contacts','less than or equal to'),
    '<>'=>Yii::t('contacts','not equal to'),
    'list'=>Yii::t('contacts','in list'),
    'notList'=>Yii::t('contacts','not in list'),
    'empty'=>Yii::t('contacts','empty'),
    'notEmpty'=>Yii::t('contacts','not empty'),
    'contains'=>Yii::t('contacts','contains'),
    'noContains'=>Yii::t('contacts','does not contain'),
);

$criteriaAttr = array();
foreach ($criteriaModels as $criterion) {
    $attr = $criterion->getAttributes();
    //for any link types, look up the name belonging to the id
    if (isset($fieldTypes[$attr['attribute']]) && $fieldTypes[$attr['attribute']] == 'link') {
        $record = X2Model::model(ucfirst($fieldLinkTypes[$attr['attribute']]))->findByPk($attr['value']);
        if (isset($record) && isset($record->name)) $attr['name'] = $record->name;
    }
    $criteriaAttr[] = $attr;
}

$headjs = "
var fieldTypes = ".json_encode($fieldTypes,false).";
var fieldLinkTypes = ".json_encode($fieldLinkTypes,false).";
var fieldOptions = ".json_encode($fieldOptions,false).";
var comparisonList = ".json_encode($comparisonList, false).";
var attributeLabels = ".json_encode($attributeLabels, false).";
var criteria = ".json_encode($criteriaAttr, false).";
var baseUrl = '". Yii::app()->baseUrl ."';
";

$headjs .= <<<EOB
function deleteCriterion(object) {
    if($('#list-criteria li').length == 2)    // prevent people from deleting the last criterion
        $('#list-criteria a.del').fadeOut(300);

    $(object).closest('li').animate({
        opacity: 0,
        height: 0
    }, 200, function() { $(this).remove(); });
}

function addCriterion() {
    //$('#list-criteria ol').append($('#list-criteria li:first').clone().hide());
    $('#list-criteria ol').append(createCriteriaForm().hide());
    $('#list-criteria a.del').fadeIn(300);
    $('#list-criteria li:last-child').find(':input').val('');
    $('#list-criteria li:last-child').slideDown(300);
}

function updateForm(object) {
    var attributeCell = $(object).closest('.cell');
    attributeCell.siblings('.cell').remove();
    var compCell = createComparisonCell(object.value);
    var valueCell = createValueCell(object.value);
    attributeCell.after(valueCell).after(compCell);
}

function createCriteriaForm() {
    var li = $('<li><div class="handle"></div><div class="content"></div><a href="javascript:void(0)" onclick="deleteCriterion(this);" class="del"></a></li>');
    var attrCell = createAttributeCell();
    var field = attrCell.find('select').val();
    var cmpCell = createComparisonCell(field);
    var valCell = createValueCell(field);
    li.find('.content').append(attrCell).append(cmpCell).append(valCell);
    return li;
}

function createPreloadCriteriaForm(criteria) {
    var li = $('<li><div class="handle"></div><div class="content"></div><a href="javascript:void(0)" onclick="deleteCriterion(this);" class="del"></a></li>');
    var attrCell = createAttributeCell();
    attrCell.find('select').val(criteria.attribute);
    var field = attrCell.find('select').val();
    var cmpCell = createComparisonCell(field);
    cmpCell.find('select').val(criteria.comparison);
    var valCell = createValueCell(field);
    valCell.find('select').val(criteria.value);
    valCell.find('input').val(criteria.value).change();
    li.find('.content').append(attrCell).append(cmpCell).append(valCell);
    return li;
}

function createAttributeCell() {
    var div = $(document.createElement('div'));
    div.attr('class', 'cell');
    var label = $('<label for=\"X2List[attribute][]\">Attribute</label>');
    var dropdown = createDropdown(attributeLabels);
    dropdown.attr('name', 'X2List[attribute][]').attr('onchange','updateForm(this);');
    return div.append(label).append(dropdown);
}

function createComparisonCell(field) {
    var ignoreList;
    switch(fieldTypes[field]) {
        case 'varchar':
        case 'email':
        case 'url':
        case 'text':
            ignoreList = [];
            break;
        case 'date':
            ignoreList = ['contains','noContains','list','notList'];
            break;
        case 'rating':
        case 'currency':
            ignoreList = ['contains','noContains'];
            break;
        case 'boolean':
        case 'visibility':
            ignoreList = ['<','>','contains','noContains','list','notList'];
            break;
        case 'link':
            ignoreList = ['<','>','contains','noContains'];
            break;
        case 'dropdown':
        case 'assignment':
            ignoreList = ['=','<>','<','>','contains','noContains'];
            break;
        //on the server side, only 'in list' is actually implemented for tags
        case 'tags':
            ignoreList = ['=','<>','<','>','empty','notEmpty','contains','noContains','notList'];
            break;
    }
    var div = $(document.createElement('div'));
    div.attr('class', 'cell');
    var label = $('<label for=\"X2List[comparison][]\">Comparison</label>');
    var dropdown = createDropdown(comparisonList, ignoreList);
    dropdown.attr('name', 'X2List[comparison][]');
    dropdown.on('change', function() {
        //if set to empty or notempty, hide the value cell
        if ($(this).val() == 'empty' || $(this).val() == 'notEmpty') {
            $(this).closest('.cell').next('.cell').hide(222);
        } else {
            $(this).closest('.cell').next('.cell').show(222);
        }
    });
    return div.append(label).append(dropdown);
}

function split(val) {
    return val.split(/,\s*/);
}

function extractLast(term) {
    return split(term).pop();
}

//http://stackoverflow.com/questions/18082/validate-numbers-in-javascript-isnumeric
function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

function createValueCell(field) {
    var input;
    var hidden;
    // console.debug(fieldTypes[field]);
    switch(fieldTypes[field]) {
        case 'date':
            //calendar widget following http://jqueryui.com/demos/datepicker
            input = $(document.createElement('input')).attr({
                size:'30',
                type:'text',
                value:'',
                name:'X2List[value][]'
            }).datepicker({
                //these button options aren't working as expected
                //showOn: 'both',
                //buttonImage: baseUrl + '/images/flags/ja.png',
                //buttonImageOnly: true,
                constrainInput: false,
                showOtherMonths: true,
                selectOtherMonths: true,
                dateFormat:yii.datePickerFormat
            });
            break;
        case 'dateTime':
            input = $(document.createElement('input')).attr({
                size:'30',
                type:'text',
                value:'',
                name:'X2List[value][]'
            }).datetimepicker({
                constrainInput: false,
                showOtherMonths: true,
                selectOtherMonths: true,
                dateFormat:yii.datePickerFormat,
                timeFormat:yii.timePickerFormat,
                minDate:null,
                maxDate:null
            });
            break;
        case 'boolean':
        case 'visibility':
            //true/false
            input = createDropdown({'1':"True","0":"False"});
            input.attr('name', 'X2List[value][]');
            input.attr('type', 'dropdown');
            break;
        case 'tags':
            //Uses code from http://jqueryui.com/demos/autocomplete/#multiple
            input = $('<input size=\"30\" type=\"text\" value=\"\">');
            input.attr('name', 'X2List[value][]');
            input.bind("keydown", function( event ) {
                if (event.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) {
                    event.preventDefault();
                }
            });
            input.autocomplete({
                minLength: 0,
                source: function( request, response ) {
                    // delegate back to autocomplete, but extract the last term
                    response($.ui.autocomplete.filter(fieldOptions[field], extractLast(request.term)));
                },
                focus: function() {
                    // prevent value inserted on focus
                    return false;
                },
                select: function( event, ui ) {
                    var terms = split( this.value );
                    // remove the current input
                    terms.pop();
                    // add the selected item
                    terms.push( ui.item.value );
                    // add placeholder to get the comma-and-space at the end
                    terms.push( "" );
                    this.value = terms.join( ", " );
                    return false;
                }
            });
            //input.autocomplete( {'minLength':'1', 'source': fieldOptions[field] });
            break;
        case 'dropdown':
        case 'assignment':
        case 'optionalAssignment':
            //we maintain a hidden field along with the multiselect to hold a comma
            //separated list of the multiselect values, in order to post them as one field
            hidden = $('<input type="hidden">');
            hidden.attr('name', 'X2List[value][]');
            input = createDropdown(fieldOptions[field]);
            input.attr('type', 'dropdown');
            input.attr('multiple', 'multiple');
            input.on('change', function(e) {
                //user change - update hidden field
                hidden.val(input.val());
            });
            hidden.on('change', function(e) {
                //programatic change - update the multiselect
                input.val($(this).val().split(','));
            });
            break;
        case 'varchar':
        case 'email':
        case 'url':
        case 'text':
        case 'currency':
        case 'rating':
        default:
            //text field
            input = $('<input size=\"30\" type=\"text\" value=\"\">');
            input.attr('name', 'X2List[value][]');
            break;
    }
    //prevent false positive, multiselect widgets show the top choice as being selected though nothing
    //actually gets posted without user interaction, set val to blank to unselect
    $(input).val('');
    var div = $(document.createElement('div'));
    div.attr('class', 'cell');
    var label = $('<label for=\"X2List[value][]\">Value</label>');
    return div.append(label).append(input).append(hidden);
}

function createDropdown(list, ignore) {
    var sel = $(document.createElement('select'));
    $.each(list, function(key, value) {
        if ($.inArray(key, ignore) == -1) {
            sel.append('<option value=\"' + key  + '\">' + value + '</option>');
        }
    });
    return sel;
}

$(function() {
    $('#list-criteria ol').sortable({
        // tolerance:'intersect',
        // items:'.formSection',
        // placeholder:'formSectionPlaceholder',
        handle:'.handle',
        // opacity:0.5,
        axis:'y',
        distance:10,
    });

    $('#listType').change(function() {
        if($(this).val() == 'static')
            $('#list-criteria').fadeOut(300);
        else if($('#list-criteria').length)
            $('#list-criteria').fadeIn(300);
        else
            window.location.reload();
    });

    //for each criteria create one, or a new blank one if none
    if (criteria.length == 0) {
        $('#list-criteria ol').append(createCriteriaForm());
    } else {
        for (var i=0; i < criteria.length; i++) {
            $('#list-criteria ol').append(createPreloadCriteriaForm(criteria[i]));
        }
    }

    if ($('#list-criteria li').length == 1)    // prevent people from deleting the last criterion
        $('#list-criteria a.del').hide();
});
EOB;
Yii::app()->clientScript->registerScript('listCriteriaJs', $headjs, CClientScript::POS_HEAD);

//    JavaScript case entry for autocomplete for link-type fields in the criteria
//    value input
//
// This all is disabled because it's horribly broken with "in list" or "not in
// list" comparison. Maybe in the future when we have a way of transforming
// autocomplete results into pills we can assemble the list properly instead of
// assuming the whole of the list is the intended input for one item/record.
//
// For now the user must type in the full names separated by commas (and hope
// there are no records with names that contain commas).
//
//
//      case 'link':
//            //autocomplete with hidden id
            //
//            hidden = $('<input type="hidden">');
//          hidden.attr('name', 'X2List[value][]');
//            input = $('<input size=\"30\" type=\"text\" value=\"\">');
//            input.blur(function(){
//                hidden.val(input.val());
//            });
//            input.autocomplete(
//                {'minLength': 0,
//                 'source': fieldOptions[field],
//                 'select':function( event, ui ) {
//                    $(this).val(ui.item.value);
//                    hidden.val(ui.item.id);
//                    return false;
//                 },
//                }
//            );
//            input.change(function() {
//                //this is for when there is an initial id value supplied,
//                //and we want the text to display, not the id
//                var current = $(this).val();
//                if (isNumber(current)) {
//                    //we have saved the names of the record in our criteria json
//                    var match = $.grep(criteria, function(el, i) {
//                        return current == el.value;
//                    });
//                    $(this).val(match[0].name);
//                }
//            });
//            break;
?>

<div class="form">
    <?php
    $form=$this->beginWidget('CActiveForm', array(
        'id'=>'contacts-form',
        'enableAjaxValidation'=>false,
    ));
    ?>
    <em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em>
<?php echo $form->errorSummary($model); ?>

<div class="row">
    <div class="cell">
        <?php echo $form->labelEx($model,'name'); ?>
        <?php echo $form->textField($model,'name',array('size'=>30,'maxlength'=>100)); ?>
        <?php echo $form->error($model,'name'); ?>
    </div>
    <?php if($model->isNewRecord) { ?>
    <div class="cell">
        <?php echo $form->labelEx($model,'type'); ?>
        <?php echo $form->dropDownList($model,'type',$listTypes,array('id'=>'listType')); ?>
        <?php echo $form->error($model,'type'); ?>
    </div>
    <?php } ?>
    <div class="cell">
        <?php echo $form->labelEx($model,'assignedTo'); ?>
        <?php
            if(empty($model->assignedTo))
                $model->assignedTo = Yii::app()->user->getName();
            echo $form->dropDownList($model,'assignedTo',$users,array('tabindex'=>null)); ?>
        <?php echo $form->error($model,'assignedTo'); ?>
    </div>
    <div class="cell">
        <?php echo $form->labelEx($model,'visibility'); ?>
        <?php
            echo $form->dropDownList($model,'visibility',array(
                1=>Yii::t('contacts','Public'),
                0=>Yii::t('contacts','Private')
            ),array('tabindex'=>null));
        ?>
    </div>
    <div class="cell">
        <?php echo $form->labelEx($model,'logicType'); ?>
        <?php
            echo $form->dropDownList($model,'logicType',array(
                'AND'=>Yii::t('contacts','AND'),
                'OR'=>Yii::t('contacts','OR')
            ),array('tabindex'=>null));
        ?>
    </div>
</div>

<div class="row">
    <?php //echo $form->labelEx($model,'description'); ?>
    <?php //echo $form->textArea($model,'description',array('style'=>'width:440px;height:60px;')); ?>
    <?php //echo $form->error($model,'description'); ?>
</div>

<?php if($model->type == 'dynamic') { ?>
<div class="x2-sortlist" id="list-criteria">
    <ol>
    <?php /* foreach($criteriaModels as &$criterion) { ?>
    <li>
        <div class="handle"></div>
        <div class="content">
            <div class="cell">
                <?php echo CHtml::label($criterion->getAttributeLabel('attribute'),'X2List[attribute][]'); ?>
                <?php echo CHtml::dropDownList('X2List[attribute][]',$criterion->attribute,$attributeLabels, array('onchange'=>'updateForm(this);')); ?>
            </div>
            <div class="cell">
                <?php echo CHtml::label($criterion->getAttributeLabel('comparison'),'X2List[comparison][]'); ?>
                <?php echo CHtml::dropDownList('X2List[comparison][]',$criterion->comparison,$comparisonList,array('encode'=>false)); ?>
            </div>
            <div class="cell">
                <?php echo CHtml::label($criterion->getAttributeLabel('value'),'X2List[value][]'); ?>
                <?php echo CHtml::textField('X2List[value][]',$criterion->value,array('size'=>'30')); ?>
            </div>
            <a href="javascript:void(0)" onclick="deleteCriterion(this);" title="<?php echo Yii::t('app','Del'); ?>" class="del"></a>
        </div>
        </li>
    <?php } */ ?>
    </ol>

    <a href="javascript:void(0)" onclick="addCriterion()" class="x2-sortlist-add">[<?php echo Yii::t('app','Add'); ?>]</a>
</div>
<?php } ?>

<?php
$validateName = <<<EOE
$('#save-button').click(function(e) {
    if ($.trim($('#X2List_name').val()).length == 0) {
        $('#X2List_name').addClass('error');
        $('[for="X2List_name"]').addClass('error');
        $('#X2List_name').after('<div class="errorMessage">Name cannot be blank.</div>');
        e.preventDefault();
    }
});
EOE;
Yii::app()->clientScript->registerScript('validateName', $validateName, CClientScript::POS_READY);
?>

<div class="row buttons">
    <?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24)); ?>
</div>

<?php
$this->endWidget();
?>

</div>
