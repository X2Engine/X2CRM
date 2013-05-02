<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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

$menuItems = array(
	array('label'=>Yii::t('services','All Cases'), 'url'=>array('index')),
	array('label'=>Yii::t('services','Create Case'), 'url'=>array('create')),
	array('label'=>Yii::t('services','Create Web Form')),
);

$this->actionMenu = $this->formatMenu($menuItems);

?>
<div class="span-12">
<div class="page-title icon services"><h2><?php echo Yii::t('marketing','Service Cases Web Form'); ?></h2></div>
<div class="form">
<?php echo Yii::t('marketing','Create a public form to receive new services cases. When the form is submitted, a new service case will be created, and the case # will be sent to the email address provided in the form.'); ?>
</div>
<?php
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/modcoder_excolor/jquery.modcoder.excolor.js');

//support both the weblead capture and weblist signup
if (empty($type)) $type = 'weblead';

$height = $type=='weblist' ? 100 : 325;
$url = 'services/webForm';

$embedcode = '<iframe src="'. Yii::app()->createAbsoluteUrl($url) .'" frameborder="0" scrolling="no" width="200" height="'. $height .'"></iframe>'; 
?>

<style type="text/css">
#embedcode {
	width: 95%;
	min-height: 67px;
	border: 1px solid #B9B9B9;
	background: #F6F6F6;
	color: #666;
	-moz-box-shadow: 0 1px 0 #fff,inset 0 1px 1px rgba(0,0,0,.17);
	-ms-box-shadow: 0 1px 0 #fff,inset 0 1px 1px rgba(0,0,0,.17);
	-webkit-box-shadow: 0 1px 0 white,inset 0 1px 1px rgba(0, 0, 0, .17);
	box-shadow: 0 1px 0 white,inset 0 1px 1px rgba(0, 0, 0, .17);
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
}

#embedcode:focus {
	border-color: #4496E7;
	color: #444;
	background: white;
	outline: 0;
}

#iframe_example {
	-moz-border-radius: 7px;
	-webkit-border-radius: 7px;
	border-radius: 7px;
	padding: 9px;
	background: #F0F0F0;
}
p.fieldhelp {
	color: #666;
	font-size: 12px;
	margin: -0.3em 0 0.8em;
	width: 193px;
}
p.fieldhelp.half {
	display: inline-block;
	width: 79px;
	margin: 0;
}
input#font, select#font {
	width: 193px;
}
input.half {
	width: 69px;
}
</style>
<?php
//get form attributes only for generating json
$formAttrs = array();
foreach ($forms as $form) {
	$formAttrs[] = $form->attributes;
}
?>
<script>
var savedforms = <?php echo json_encode($formAttrs); ?>;
var embedcode = '<?php echo $embedcode; ?>';
var listId = <?php echo !empty($id) ? $id : 'null'; ?>;
var fields = ['fg','bgc','font','bs','bc','tags'];
var colorfields = ['fg','bgc','bc'];

function sanitizeInput(value) {
	return encodeURIComponent(value.trim().replace(/[^a-zA-Z0-9#,]/g, ''));
}

function generateQuery(params) {
	var query = '';
	var first = true;

	for (var i=0; i<params.length; i++) {
		if (params[i].search(/^[^=]+=[^=]+$/) != -1) {
			if (first) {
				query += '?'; first = false;
			} else {
				query += '&';
			}

			query += params[i];	
		}
	}

	return query;
}

function updateParams() {
	var params = [];
	if (listId != null) {
		params.push('lid='+listId);
	}

	$.each(fields, function(i, field) {
		var value = sanitizeInput($('#'+field).val());
		if (value.length > 0) { params.push(field+'='+value); }
	});

	var query = generateQuery(params);
	var newembed = embedcode.replace(/(src=\"[^\"]*)/, "$1" + query);

	$('#embedcode').val(newembed);
	$('#iframe_example').html(newembed);
}

function clearFields() {
	$('#name').val('');
	$.each(fields, function(i, field) {
		$('#'+field).val('');
	});
	$('.modcoder_excolor_clrbox').css('background-color','').css('background-image','url(<?php echo Yii::app()->getBaseUrl().'/js/modcoder_excolor/transp.gif'; ?>)');
}

function updateFields(form) {
	$('#name').val(form.name);
	$.each(form.params, function(key, value) {
		if ($.inArray(key, fields) != -1) {
			$('#'+key).val(value);
		}
		if ($.inArray(key, colorfields) != -1) {
			$('#'+key).next('.modcoder_excolor_clrbox').css('background-image','').css('background-color', value);
		}
	});
}
	
function saved(data, status, xhr) {
	var newForm = $.parseJSON(data);
	if (typeof newForm.errors !== "undefined") { return; }
	newForm.params = $.parseJSON(newForm.params);
	var index = -1;
	$.each(savedforms, function(i, el) {
		if (newForm.id == el.id) {
			index = i;
		}
	});
	if (index != -1) {
		savedforms.splice(index, 1, newForm);
	} else {
		savedforms.push(newForm);
		$('#saved-forms').append('<option value="'+newForm.id+'">'+newForm.name+'</option>');
	}
	$('#saved-forms').val(newForm.id);
	alert("<?php echo Yii::t('marketing','Form Saved'); ?>");
}

$(function() {
	$('#embedcode').focus(function() {
		$(this).select();
	});
	$('#embedcode').mouseup(function(e) {
		e.preventDefault();
	});
	$('#embedcode').focus();

	$.each(colorfields, function(i, field) {
		$('#'+field).modcoder_excolor({
			callback_on_ok: function() { updateParams(); }
		});
	});
	
	$.each(fields, function(i, field) {
		$('#'+field).on('change', function() { updateParams(); });
	});
	
	$('#save').click(function(e) {
		if ($.trim($('#name').val()).length == 0) {
			$('#name').addClass('error');
			$('[for="name"]').addClass('error');
			$('#save').after('<div class="errorMessage"><?php echo Yii::t('marketing','Name cannot be blank.'); ?></div>');
			e.preventDefault(); //has no effect
		}
	});

	$('#saved-forms').on('change', function() {
		var id = $(this).val();
		clearFields();
		if (id != 0) {
			var match = $.grep(savedforms, function(el, i) {
				return id == el.id;
			});
			updateFields(match[0]);
		} 
		updateParams();
		$('#embedcode').focus();
	});

	if (listId != null) { updateParams(); }
});
</script>

<div class="form">

<div class="cell">
	<h4><?php echo Yii::t('marketing','Embed Code') .':'; ?></h4>
	<textarea id="embedcode"><?php echo $embedcode; ?></textarea>
	<?php echo Yii::t('marketing','Copy and paste this code into your website to include the web lead form.'); ?><br /><br />
</div>

<div style="margin-bottom: 1em;">
	<h4 style="display: inline;"><?php echo Yii::t('marketing','Saved Forms').':'; ?></h4>
	<?php array_unshift($formAttrs, array('id'=>'0', 'name'=>'------------')); /* so the dropdown will have a blank choice */?>
	<?php echo CHtml::dropDownList('saved-forms', '', CHtml::encodeArray(CHtml::listData($formAttrs, 'id', 'name'))); ?>
	<?php echo CHtml::link(Yii::t('marketing','Reset Form'), '', array('onclick'=>'$("#saved-forms").val("0").change();', 'class'=>'x2-button')); ?>
	<p class="fieldhelp" style="width: auto;"><?php echo Yii::t('marketing','Choose an existing form as a starting point.'); ?></p>
</div>

<div id="settings" class="cell">
	<?php echo CHtml::beginForm(); ?>
	<h4><?php echo Yii::t('marketing','Settings') .':'; ?></h4>
	<div class="row">
		<?php echo CHtml::label(Yii::t('marketing','Text Color'),'fg'); ?>
		<?php echo CHtml::textField('fg'); ?>
		<p class="fieldhelp"><?php echo Yii::t('marketing','Default') .': '. Yii::t('marketing','black'); ?></p>
	</div>
	<div class="row">
		<?php echo CHtml::label(Yii::t('marketing','Background Color'), 'bgc'); ?>
		<?php echo CHtml::textField('bgc'); ?>
		<p class="fieldhelp"><?php echo Yii::t('marketing','Default') .': '. Yii::t('marketing','transparent'); ?></p>
	</div> 
	<?php $fontInput = new FontPickerInput(array('name'=>'font')); ?>
	<div class="row">
		<?php echo CHtml::label(Yii::t('marketing','Font'), 'font'); ?>
		<?php echo $fontInput->render(); ?>
		<p class="fieldhelp"><?php echo Yii::t('marketing','Default') .': Arial, Helvetica'; ?></p>
	</div> 
	<div class="row">
		<?php echo CHtml::label(Yii::t('marketing','Border'), 'border'); ?>
		<p class="fieldhelp half"><?php echo Yii::t('marketing','Size') .' ('. Yii::t('marketing','pixels') .')'; ?></p>
		<p class="fieldhelp half"><?php echo Yii::t('marketing','Color'); ?></p><br/>
		<?php echo CHtml::textField('bs', '', array('class'=>'half')); ?>
		<?php echo CHtml::textField('bc', '', array('class'=>'half')); ?>
		<p class="fieldhelp"><?php echo Yii::t('marketing','Default') .': '. Yii::t('marketing','none'); ?></p>
	</div> 
	<div class="row" <?php if ($type != 'weblead') echo 'style="display: none;"'; ?>>
		<?php echo CHtml::label(Yii::t('marketing','Tags'), 'tags'); ?>
		<?php echo CHtml::textField('tags'); ?>
		<p class="fieldhelp"><em><?php echo Yii::t('marketing','Example') .': web,newlead,urgent'; ?></em><br/><?php echo Yii::t('marketing','These tags will be applied to any contact created by the form.'); ?></p>
	</div> 
	<div style="display: none;">
		<?php echo CHtml::hiddenField('type', $type); ?>
	</div>
	<h4><?php echo Yii::t('marketing','Save') .':'; ?></h4>
	<div class="row">
		<p class="fieldhelp" style="margin-top:0;"><?php echo Yii::t('marketing','Enter a name and save this form to edit later.'); ?></p>
		<?php echo CHtml::label(Yii::t('marketing','Name'), 'name'); ?>
		<?php echo CHtml::textField('name'); ?>
		<?php echo CHtml::ajaxSubmitButton(Yii::t('marketing','Save'), Yii::app()->createAbsoluteUrl('services/createWebForm'), array('success'=>'function(data, status, xhr) { saved(data, status, xhr); }'), array('name'=>'save')); ?>
	</div>
	<?php echo CHtml::endForm(); ?>
</div>

<div class="cell">
	<h4><?php echo Yii::t('marketing','Preview') .':'; ?></h4>
	<div id="iframe_example">
		<?php echo $embedcode; ?>
	</div>
</div>

</div>

</div>