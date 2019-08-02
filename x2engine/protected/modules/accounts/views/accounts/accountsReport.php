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






$menuOptions = array(
    'all', 'create', 'report', 'import', 'export',
);
$this->insertMenu($menuOptions);

$comparisonList = array(
    '=' => Yii::t('contacts', 'equals'),
    '>' => Yii::t('contacts', 'greater than'),
    '<' => Yii::t('contacts', 'less than'),
    '<>' => Yii::t('contacts', 'not equal to'),
    'list' => Yii::t('contacts', 'in list'),
    'notList' => Yii::t('contacts', 'not in list'),
    'empty' => Yii::t('contacts', 'empty'),
    'notEmpty' => Yii::t('contacts', 'not empty'),
    'contains' => Yii::t('contacts', 'contains'),
    'noContains' => Yii::t('contacts', 'does not contain'),
);
$language = (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage();
$itemModel = new Accounts;
$criteriaArr = array();
if(!empty($_GET)){
    $_SESSION['accountsReport']['GET'] = json_encode($_GET, false);
}else{
    $_SESSION['accountsReport']['GET'] = '';
}
if(isset($_GET['Accounts'])){
    $filterAttributes = $_GET['Accounts'];
    for($i = 0; $i < count($filterAttributes['attribute']); $i++){
        $criteriaArr[$i]['attribute'] = $filterAttributes['attribute'][$i];
        $criteriaArr[$i]['comparison'] = $filterAttributes['comparison'][$i];
        $criteriaArr[$i]['value'] = $filterAttributes['value'][$i];
    }
}
$criteriaAttr = $criteriaArr;
$fieldTypes = array();
$fieldLinkTypes = array();
$fieldOptions = array();
foreach($itemModel->getFields() as $field){
    $fieldTypes[$field->fieldName] = $field->type;
    if(!empty($field->linkType)){
        $fieldLinkTypes[$field->fieldName] = $field->linkType;
    }
    switch($field->type){
        case 'dropdown':
            $fieldOptions[$field->fieldName] = Dropdowns::getItems($field->linkType);
            break;
        case 'assignment':
            $fieldOptions[$field->fieldName] = User::getNames() + Groups::getNames();
            break;
        case 'optionalAssignment':
            $fieldOptions[$field->fieldName] = User::getNames() + Groups::getNames();
            break;
        case 'link':
            $fieldOptions[$field->fieldName] = Yii::app()->request->scriptUrl.X2Model::model($field->linkType)->autoCompleteSource;
            break;
    }
}
$attributeLabels = $itemModel->attributeLabels();
$headjs = "
var fieldTypes = ".json_encode($fieldTypes, false).";
var fieldLinkTypes = ".json_encode($fieldLinkTypes, false).";
var fieldOptions = ".json_encode($fieldOptions, false).";
var comparisonList = ".json_encode($comparisonList, false).";
var attributeLabels = ".json_encode($attributeLabels, false).";
var criteria = ".json_encode($criteriaAttr, false).";
var baseUrl = '".Yii::app()->baseUrl."';
";

$headjs .= <<<EOB
function deleteCriterion(object) {
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
	var label = $('<label for=\"Accounts[attribute][]\">Attribute</label>');
	var dropdown = createDropdown(attributeLabels);
	dropdown.attr('name', 'Accounts[attribute][]').attr('onchange','updateForm(this);');
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
        case 'optionalAssignment':
			ignoreList = ['=','<>','<','>','contains','noContains'];
			break;
		//on the server side, only 'in list' is actually implemented for tags
		case 'tags':
			ignoreList = ['=','<>','<','>','empty','notEmpty','contains','noContains','notList'];
			break;
	}
	var div = $(document.createElement('div'));
	div.attr('class', 'cell');
	var label = $('<label for=\"Accounts[comparison][]\">Comparison</label>');
	var dropdown = createDropdown(comparisonList, ignoreList);
	dropdown.attr('name', 'Accounts[comparison][]');
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
    $.datepicker.setDefaults( $.datepicker.regional[ "$language" ] );
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
				name:'Accounts[value][]'
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
				name:'Accounts[value][]'
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
			input.attr('name', 'Accounts[value][]');
			input.attr('type', 'dropdown');
			break;
		case 'tags':
			//Uses code from http://jqueryui.com/demos/autocomplete/#multiple
			input = $('<input size=\"30\" type=\"text\" value=\"\">');
			input.attr('name', 'Accounts[value][]');
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
		case 'link':
			//autocomplete with hidden id
			hidden = $('<input type="hidden">');
			hidden.attr('name', 'Accounts[value][]');
			input = $('<input size=\"30\" type=\"text\" value=\"\">');
            input.blur(function(){
                hidden.val(input.val());
            });
			input.autocomplete(
				{'minLength': 0,
				 'source': fieldOptions[field],
				 'select':function( event, ui ) {
					$(this).val(ui.item.value);
					hidden.val(ui.item.id);
					return false;
				 },
				}
			);
			input.change(function() {
				//this is for when there is an initial id value supplied,
				//and we want the text to display, not the id
				var current = $(this).val();
				if (isNumber(current)) {
					//we have saved the names of the record in our criteria json
					var match = $.grep(criteria, function(el, i) {
						return current == el.value;
					});
					$(this).val(match[0].name);
				}
			});
			break;
		case 'dropdown':
		case 'assignment':
		case 'optionalAssignment':
			//we maintain a hidden field along with the multiselect to hold a comma
			//separated list of the multiselect values, in order to post them as one field
			hidden = $('<input type="hidden">');
			hidden.attr('name', 'Accounts[value][]');
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
			input.attr('name', 'Accounts[value][]');
			break;
	}
	//prevent false positive, multiselect widgets show the top choice as being selected though nothing
	//actually gets posted without user interaction, set val to blank to unselect
	$(input).val('');
	var div = $(document.createElement('div'));
	div.attr('class', 'cell');
	var label = $('<label for=\"Accounts[value][]\">Value</label>');
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
    $('#filters-link').click(function(e){
        e.preventDefault();
        if($('#list-criteria').is(':hidden')){
            $('#list-criteria').slideDown();
        }else{
            $('#list-criteria').slideUp();
        }
    });
    if (criteria.length > 0) {
		for (var i=0; i < criteria.length; i++) {
			$('#list-criteria ol').append(createPreloadCriteriaForm(criteria[i]));
		}
        $('#list-criteria').show();
	}
});
EOB;
Yii::app()->clientScript->registerScript('listCriteriaJs', $headjs, CClientScript::POS_HEAD);
?>
<div class="page-title"><h2><?php echo Yii::t('accounts', '{module} Report', array('{module}'=>Modules::displayName(true, 'Accounts'))); ?></h2></div>
<div class="form">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'action' => 'accountsReport',
        'id' => 'accountsReport',
        'enableAjaxValidation' => false,
        'method' => 'get'
            ));
    ?>
    <div class="row">
        <div class="cell">
            <?php echo CHtml::label(Yii::t('accounts', 'Date Field'), 'dateField'); ?>
            <?php
            echo CHtml::dropDownList('dateField', 'createDate', $dateFields, array('id' => 'dateField'));
            ?>
        </div>
        <div class="cell">
            <?php echo CHtml::label(Yii::t('charts', 'Start Date'), 'startDate'); ?>
            <?php
            Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');

            $this->widget('CJuiDateTimePicker', array(
                'name' => 'start',
                // 'value'=>$startDate,
                'value' => Formatter::formatDate($dateRange['start']),
                // 'title'=>Yii::t('app','Start Date'),
                // 'model'=>$model, //Model object
                // 'attribute'=>$field->fieldName, //attribute name
                'mode' => 'date', //use "time","date" or "datetime" (default)
                'options' => array(
                    'dateFormat' => Formatter::formatDatePicker(),
                    'changeMonth' => true,
                    'changeYear' => true,
                ), // jquery plugin options
                'htmlOptions' => array('id' => 'startDate', 'width' => 20),
                'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
            ));
            ?>
        </div>
        <div class="cell">
            <?php echo CHtml::label(Yii::t('charts', 'End Date'), 'startDate'); ?>
            <?php
            $this->widget('CJuiDateTimePicker', array(
                'name' => 'end',
                'value' => Formatter::formatDate($dateRange['end']),
                // 'value'=>$endDate,|| (isset($_GET['cellType']) && $_GET['cellType'] == 'frequency')
                'mode' => 'date', //use "time","date" or "datetime" (default)
                'options' => array(
                    'dateFormat' => Formatter::formatDatePicker(),
                    'changeMonth' => true,
                    'changeYear' => true,
                ),
                'htmlOptions' => array('id' => 'endDate', 'width' => 20),
                'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
            ));
            ?>
        </div>
        <div class="cell">
            <?php echo CHtml::label(Yii::t('charts', 'Date Range'), 'range'); ?>
            <?php
            echo CHtml::dropDownList('range', $dateRange['range'], array(
                'custom' => Yii::t('charts', 'Custom'),
                'thisWeek' => Yii::t('charts', 'This Week'),
                'thisMonth' => Yii::t('charts', 'This Month'),
                'lastWeek' => Yii::t('charts', 'Last Week'),
                'lastMonth' => Yii::t('charts', 'Last Month'),
                // 'lastQuarter'=>Yii::t('charts','Last Quarter'),
                'thisYear' => Yii::t('charts', 'This Year'),
                'lastYear' => Yii::t('charts', 'Last Year'),
                'all' => Yii::t('charts', 'All Time'),
                    ), array('id' => 'dateRange'));
            ?>
        </div>
        <div class="cell">
            <a href="#" class="x2-button" id="filters-link" style="margin-top:15px;"><?php echo Yii::t('accounts', 'Advanced Filters'); ?></a>
        </div>
        <div class="right">
            <a href="#" id="export-button" class="x2-button" style="margin-top:15px;"><?php echo Yii::t('accounts', 'Export Data'); ?></a>
        </div>
    </div>
    <div class="row">
        <div class="x2-sortlist" id="list-criteria" style="display:none;">
            <ol>
            </ol>

            <a href="javascript:void(0)" onclick="addCriterion()" class="x2-sortlist-add">[<?php echo Yii::t('app', 'Add'); ?>]</a>
        </div>
    </div>
    <div class="row">
        <div class="cell">
            <?php echo CHtml::submitButton(Yii::t('app', 'Generate Report'), array('class' => 'x2-button')); ?>
        </div>
        <?php if(!empty($dataProvider)){ ?>
            <div class="cell">
                <?php echo CHtml::link(Yii::t('app', 'Create Campaign'), '#', array('id' => 'account-campaign-link', 'class' => 'x2-button', 'style' => 'padding: 5px 20px; font-size: 12px; margin-top: 3px;')); ?>
            </div>
        <?php } ?>
    </div>
    <?php if (Yii::app()->user->hasFlash('error')) { ?>
    <div class="row">
        <div class="cell flash-error"><?php echo Yii::app()->user->getFlash('error'); ?></div>
    </div>
    <?php } ?>
</div>
<?php $this->endWidget(); ?>
<?php if(!empty($dataProvider)){ ?>
    <div id="account-campaign-form" style="display:none;" class="row form">
        <?php
        echo "<h3>".Yii::t('accounts','Campaign Form')."</h3>";
        echo Yii::t('accounts', 'This form will allow you to generate a campaign to mail related {contacts} of the {accounts} displayed in the report below. Select "Primary {contacts}" to only email {contacts} which are set as the Primary {contact} for an {account}. Select "All" to email all related {contacts} on each {account}.', array(
                '{account}'=>Modules::displayName (false ,'Accounts'),
                '{accounts}'=>Modules::displayName (true, 'Accounts'),
                '{contact}'=>Modules::displayName (false, 'Contacts'),
                '{contacts}'=>Modules::displayName (true, 'Contacts'),
            ));
        echo "<br><br>";
        echo CHtml::beginForm('accountsCampaign');
        echo CHtml::dropDownList('listType', '', array(
            'primary' => Yii::t('accounts', 'Primary {module}', array('{module}'=>Modules::displayName (true, 'Contacts'))),
            'all' => Yii::t('accounts', 'All {module}', array('{module}'=>Modules::displayName (true, 'Contacts'))),
        ));
        echo CHtml::submitButton('Create Campaign', array('class' => 'x2-button small'));
        echo CHtml::endForm();
        ?>
    </div>
<?php } ?>
<div id="report-data">
    <?php
    if(!empty($dataProvider)){
        $this->widget('X2GridView', array(
            'id' => 'accounts-grid',
            'title' => Modules::displayName (true, 'Accounts'),
            'buttons' => array('columnSelector','autoResize'),
            'template' => '<div class="page-title icon accounts rounded-top">{title}{buttons}{summary}</div>{items}{pager}',
            'dataProvider' => $dataProvider,
            // 'enableSorting'=>false,
            // 'model'=>$model,
            'filter' => null,
            // 'columns'=>$columns,
            'modelName' => 'Accounts',
            'viewName' => 'accounts',
            // 'columnSelectorId'=>'contacts-column-selector',
            'defaultGvSettings' => array(
                'name' => 184,
                'type' => 153,
                'annualRevenue' => 108,
                'phone' => 115,
                'lastUpdated' => 77,
                'assignedTo' => 99,
            ),
            'specialColumns' => array(
                'name' => array(
                    'name' => 'name',
                    'header' => Yii::t('accounts', 'Name'),
                    'value' => 'CHtml::link($data["name"],array("view","id"=>$data["id"]))',
                    'type' => 'raw',
                ),
            ),
            'enableControls' => false,
            'fullscreen' => true,
        ));
    }
    ?>
</div>
<div id="export-wrapper" class="form" style="display:none;">
    <div id="status-text" style="color:green">

    </div>

    <div style="display:none" id="download-link-box">
        <?php echo Yii::t('accounts', 'Please click the link below to download this report.'); ?><br><br>
        <a class="x2-button" id="download-link" href="#"><?php echo Yii::t('app', 'Download'); ?>!</a>
    </div>
</div>
<script>
    $('#account-campaign-link').on('click',function(e){
        e.preventDefault();
        if($('#account-campaign-form').is(":hidden")){
            $('#account-campaign-form').slideDown();
        }else{
            $('#account-campaign-form').slideUp();
        }
    });
    $('#export-button').on('click',function(){
        exportAccountsReport(0);
    });
    function exportAccountsReport(page){
        if($('#export-status').length==0){
            $('#report-data').hide();
            $('#export-wrapper').show();
            $('#status-text').append("<div id='export-status'>Exporting <b><?php echo Modules::displayName(false, 'Accounts'); ?></b> data...<br></div>");
        }
        $.ajax({
            url:'exportAccountsReport?page='+page,
            success:function(data){
                if(data>0){
                    $('#export-status').html(((data)*100)+" records from <b><?php echo Modules::displayName(true, 'accounts'); ?></b> successfully exported.<br>");
                    exportAccountsReport(data);
                }else{
                    $('#export-status').html("All report data successfully exported.<br>");
                    $('#download-link-box').show();
                    alert("Export Complete!");
                }
            }
        });
    }
    $('#download-link').click(function(e) {
        e.preventDefault();  //stop the browser from following
        window.location.href = '<?php echo $this->createUrl('/admin/downloadData', array('file' => $_SESSION['accountsReport']['accountsReportFile'])); ?>';
    });</script>
