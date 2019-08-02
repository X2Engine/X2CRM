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




$layoutManager = $this->widget ('RecordViewLayoutManager', array ('staticLayout' => false));

Yii::app()->clientScript->registerCss('recordViewCss', "

#content {
    background: none !important;
    border: none !important;
}
");
Yii::app()->clientScript->registerResponsiveCssFile(
        Yii::app()->theme->baseUrl . '/css/responsiveRecordView.css');


// quotes can be locked meaning they can't be changed anymore
Yii::app()->clientScript->registerScript('LockedQuoteDialog', "
function dialogStrictLock() {
var denyBox = $('<div></div>')
    .html('This quote is locked.')
    .dialog({
    	title: 'Locked',
    	autoOpen: false,
    	resizable: false,
    	buttons: {
    		'OK': function() {
    			$(this).dialog('close');
    		}
    	}
    });

denyBox.dialog('open');
}

function dialogLock() {
var confirmBox = $('<div></div>')
    .html('This quote is locked. Are you sure you want to update this quote?')
    .dialog({
    	title: 'Locked',
    	autoOpen: false,
    	resizable: false,
    	buttons: {
    		'Yes': function() {
    			window.location = '" . Yii::app()->createUrl('/quotes/quotes/update', array('id' => $model->id)) . "';
    			$(this).dialog('close');
    		},
    		'No': function() {
    			$(this).dialog('close');
    		}
    	}
    });
confirmBox.dialog('open');
}

", CClientScript::POS_HEAD);
$modelType = json_encode("Quotes");
$modelId = json_encode($model->id);
Yii::app()->clientScript->registerScript('widgetShowData', "
$(function() {
	$('body').data('modelType', $modelType);
	$('body').data('modelId', $modelId);
});");
if ($contactId) {
    $contact = Contacts::model()->findByPk($contactId); // used to determine if 'Send Email' menu item is displayed
} else {
    $contact = false;
}

$authParams['X2Model'] = $model;
$strict = Yii::app()->settings->quoteStrictLock;
$themeUrl = Yii::app()->theme->getBaseUrl();

$menuOptions = array(
    'index', 'invoices', 'create', 'view', 'email', 'delete', 'attach', 'print', 'convert', 
    'duplicate', 'editLayout',
);
if ($contact)
    $menuOptions[] = 'email';
if ($model->locked)
    if ($strict && !Yii::app()->user->checkAccess('QuotesAdminAccess'))
        $menuOptions[] = 'editStrictLock';
    else
        $menuOptions[] = 'editLock';
else
    $menuOptions[] = 'edit';
if ($model->type !== 'invoice')
    $menuOptions[] = 'convert';
$this->insertMenu($menuOptions, $model, $authParams);
?>

<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">
        <div class="responsive-page-title page-title icon quotes">
            <h2><span class="no-bold"><?php echo ($model->type == 'invoice' ? Yii::t('quotes', 'Invoice:') : Yii::t('quotes', '{module}:', array('{module}' => Modules::displayName(false)))); ?></span> <?php echo $model->name == '' ? '#' . $model->id : CHtml::encode($model->name); ?></h2>
            <?php
            echo ResponsiveHtml::gripButton();
            ?>
            <div class='responsive-menu-items'>
                <?php if ($model->locked) { ?>
                    <?php if ($strict && !Yii::app()->user->checkAccess('QuotesAdminAccess')) { ?>
                        <a class="x2-button icon edit right" href="#" onClick="dialogStrictLock();"><span></span></a>
                    <?php } else { ?>
                        <a class="x2-button icon edit right" href="#" onClick="dialogLock();"><span></span></a>
                    <?php
                    }
                } else {
                    echo X2Html::editRecordButton($model);
                }
                echo X2Html::emailFormButton();
                echo X2Html::inlineEditButtons();


                if ($model->type !== 'invoice') {
                    ?>
                    <a class="x2-button right" href="<?php echo $this->createUrl('convertToInvoice', array('id' => $model->id)); ?>">
                    <?php echo Yii::t('quotes', 'Convert To Invoice'); ?>
                    </a>
                       <?php
                       }
                       ?>
            </div>
        </div>
    </div>
</div>
<div id="main-column" <?php echo $layoutManager->columnWidthStyleAttr (1); ?>>
<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'quotes-form',
    'enableAjaxValidation' => false,
    'action' => array('saveChanges', 'id' => $model->id)
        ));
$this->widget ('DetailView', array(
    'model' => $model
));
//$this->renderPartial('application.components.views.@DETAILVIEW', array('model' => $model, 'modelName' => 'Quote'));
?>
    <?php if ($model->type == 'invoice') { ?>
        <div class="x2-layout form-view">
            <div class="formSection showSection">
                <div class="formSectionHeader">
                    <span class="sectionTitle" title="Invoice"><?php echo Yii::t('quotes', 'Invoice'); ?></span>
                </div>
                <div class="tableWrapper">
                    <table>
                        <tbody>
                            <tr class="formSectionRow">
                                <td style="width: 300px">
                                    <div class="formItem leftLabel">
                                        <label><?php echo Yii::t('quotes', 'Invoice Status'); ?></label>
                                        <div class="formInputBox" style="width: 150px; height: auto;">
    <?php echo $model->renderAttribute('invoiceStatus'); ?>
                                        </div>
                                    </div>
                                    <div class="formItem leftLabel">
                                        <label><?php echo Yii::t('quotes', 'Invoice Created'); ?></label>
                                        <div class="formInputBox" style="width: 150px; height: auto;">
    <?php echo $model->renderAttribute('invoiceCreateDate'); ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 300px">
                                    <div class="formItem leftLabel">
                                        <label><?php echo Yii::t('quotes', 'Invoice Issued'); ?></label>
                                        <div class="formInputBox" style="width: 150px; height: auto;">
    <?php echo $model->renderAttribute('invoiceIssuedDate'); ?>
                                        </div>
                                    </div>
                                    <div class="formItem leftLabel">
                                        <label><?php echo Yii::t('quotes', 'Invoice Paid'); ?></label>
                                        <div class="formInputBox" style="width: 150px; height: auto;">
    <?php echo $model->renderAttribute('invoicePayedDate'); ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
<?php
}

$productField = Fields::model()->findByAttributes(array('modelName' => 'Quote', 'fieldName' => 'products'));
?>
    <div class="x2-layout form-view">
        <div class="formSection showSection">
            <div class="formSectionHeader">
                <span class="sectionTitle"><?php echo Yii::t('products',$productField->attributeLabel); ?></span>
            </div>
            <div class="tableWrapper">
<?php
$this->renderPartial('_lineItems', array(
    'model' => $model, 'readOnly' => true, 'namespacePrefix' => 'quotesView'
));
?>
            </div>

        </div>
    </div>
<?php
$this->endWidget();

$this->widget('InlineEmailForm', array(
    'attributes' => array(
        'to' => !empty($contact) && $contact instanceof Contacts ? '"' . $contact->name . '" <' . $contact->email . '>, ' : '',
        // 'subject'=>'hi',
        // 'redirect'=>'contacts/'.$model->id,
        'modelName' => 'Quote',
        'modelId' => $model->id,
        'message' => $this->getPrintQuote($model->id, true),
        'subject' => $model->type == ('invoice' ? Yii::t('quotes', 'Invoice') : Yii::t('quotes', '{quote}', array('{quote}' => Modules::displayName(false)))) . '(' . Yii::app()->settings->appName . '): ' . $model->name,
    ),
    'startHidden' => true,
    'templateType' => 'quote',
        )
);
?>

<?php $this->widget ('ModelFileUploader', array(
    'associationType' => 'quotes',
    'associationId' => $model->id,
));
?>

</div>
<?php 
$this->widget(
    'X2WidgetList', 
    array(
        'layoutManager' => $layoutManager,
        'block' => 'center',
        'model' => $model,
        'modelType' => 'Quote'
    )); 
?>
