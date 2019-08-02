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



$authParams['X2Model'] = $model;

$menuOptions = array(
    'index', 'invoices', 'create', 'view', 'edit', 'delete',
);
$this->insertMenu($menuOptions, $model, $authParams);

$title = CHtml::tag(
    'h2',array('class' =>'quotes-section-title'), 
    ($model->type === 'invoice' ? 
        Yii::t('quotes', 'Update Invoice:') : 
        Yii::t('quotes','Update {quote}:', array(
            '{quote}' => Modules::displayName(false),
        ))).'&nbsp;'.CHtml::encode ($model->getName ()));
if ($quick) {
    echo $title;
} else {
    echo CHtml::openTag('div',array('class'=>'page-title icon quotes'));
    echo $title;
    ?>
    <a class="x2-button right" href="javascript:void(0);" 
        onclick="$('#quote-save-button').click();"><?php echo Yii::t('app','Update'); ?></a>
    <?php
    echo CHtml::closeTag ('div');
}

$form=$this->beginWidget('CActiveForm', array(
   'id'=>'quotes-form',
   'enableAjaxValidation'=>false,
));
    

$this->widget ('FormView', array(
    'model' => $model,
    'form' => $form,
    'scenario' => $quick ? 'Inline' : 'Default',
));
//echo $this->renderPartial('application.components.views.@FORMVIEW', 
//  array(
//      'model'=>$model,
//      'form'=>$form,
//      'users'=>$users,
//      'modelName'=>'Quote',
//      'isQuickCreate'=>true, // let us create the CActiveForm in this file
//      'scenario' => $quick ? 'Inline' : 'Default',
//  )
// );

if($model->type == 'invoice') { ?>
    <div class="x2-layout form-view" style="margin-bottom: 0;">
    
        <div class="formSection showSection">
            <div class="formSectionHeader">
                <span class="sectionTitle" title="Invoice"><?php 
                    echo Yii::t('quotes', 'Invoice'); ?></span>
            </div>
            <div class="tableWrapper">
                <table>
                    <tbody>
                        <tr class="formSectionRow">
                            <td style="width: 300px">
                                <div class="formItem leftLabel">
                                    <label><?php echo Yii::t('media', 'Invoice Status'); ?></label>
                                    <div class="formInputBox" style="width: 150px; height: auto;">
                                        <?php echo $model->renderInput('invoiceStatus'); ?>
                                    </div>
                                </div>
                                <div class="formItem leftLabel">
                                    <label><?php echo Yii::t('media', 'Invoice Created'); ?></label>
                                    <div class="formInputBox" style="width: 150px; height: auto;">
                                        <?php echo $model->renderInput('invoiceCreateDate'); ?>
                                    </div>
                                </div>
                            </td>
                            <td style="width: 300px">
                                <div class="formItem leftLabel">
                                    <label><?php echo Yii::t('media', 'Invoice Issued'); ?></label>
                                    <div class="formInputBox" style="width: 150px; height: auto;">
                                        <?php echo $model->renderInput('invoiceIssuedDate'); ?>
                                    </div>
                                </div>
                                <div class="formItem leftLabel">
                                    <label><?php echo Yii::t('media', 'Invoice Paid'); ?></label>
                                    <div class="formInputBox" style="width: 150px; height: auto;">
                                        <?php echo $model->renderInput('invoicePayedDate'); ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
    <br />
<?php }

echo $this->renderPartial('_lineItems',
    array(
        'model'=>$model,
        'products'=>$products,
        'readOnly'=>false,
        'form'=>$form,
        'namespacePrefix' => 'quotes'
    )
);

echo $this->renderPartial('_sharedView', array (
    'quick' => $quick,
    'action' => 'Update',
    'model' => $model,
    'form' => $form,
));

$this->endWidget();
?>
