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

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('quotes','Quotes List'),'url'=>array('index')),
	array('label'=>Yii::t('quotes','Invoice List'), 'url'=>array('indexInvoice')),
	array('label'=>Yii::t('quotes','Create')),
));

$title = CHtml::tag('h2',array(),Yii::t('quotes','Create Quote'));
echo $quick?$title:CHtml::tag('div',array('class'=>'page-title'),$title);
?>

<?php 
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'quotes-form',
	'enableAjaxValidation'=>false,
));

if($model->hasLineItemErrors): ?>
<div class="errorSummary">
	<h3><?php echo Yii::t('quotes','Could not save quote due to line item errors:'); ?></h3>
	<ul>
	<?php foreach($model->lineItemError as $error): ?>
		<li><?php echo CHtml::encode($error); ?></li>
	<?php endforeach; ?>
	</ul>
</div>
<?php endif;

echo $this->renderPartial('application.components.views._form', 
	array(
		'model'=>$model,
		'form'=>$form,
		'users'=>$users,
		'modelName'=>'Quote',
		'isQuickCreate'=>true, // let us create the CActiveForm in this file
	)
);

echo $this->renderPartial('_lineItems', array(
	'model' => $model,
	'products' => $products,
	'readOnly' => false
		)
);

$templateRec = Yii::app()->db->createCommand()->select('id,name')->from('x2_docs')->where("type='quote'")->queryAll();
$templates = array();
$templates[null] = '(none)';
foreach($templateRec as $tmplRec){
	$templates[$tmplRec['id']] = $tmplRec['name'];
}
echo '<div style="display:inline-block">';
echo '<strong>'.$form->label($model,'template').'</strong>&nbsp;';
echo $form->dropDownList($model,'template',$templates).'&nbsp;'.CHtml::tag('span', array('class' => 'x2-hint','title' => Yii::t('quotes', 'To create a template for quotes and invoices, go to the Docs module and select "{crQu}".',array('{crQu}'=>Yii::t('docs','Create Quote')))), '[?]');
echo '</div><br />';
echo '	<div class="row buttons" style="padding-left:0">'."\n";
echo CHtml::submitButton(Yii::t('app', 'Create'), array('class' => 'x2-button'.($quick?' highlight':''), 'id' => 'quote-save-button', 'tabindex' => 25))."\n";
echo $quick?CHtml::button(Yii::t('app','Cancel'),array('class'=>'x2-button right','id'=>'quote-cancel-button','tabindex'=>24))."\n":'';
echo "	</div>\n";
$this->endWidget();

if($quick){
	echo '<br /><br /><hr />';
	$scripts = '<script id="quick-quote-form">'."\n";
	$scripts .= implode(";\n",Yii::app()->clientScript->scripts[CClientScript::POS_READY]);
	echo $scripts."\n</script>";
}

?>
