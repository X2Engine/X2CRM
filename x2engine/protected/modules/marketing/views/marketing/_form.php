<?php
/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/



Yii::app()->clientScript->registerPackage ('emailEditor');
Yii::app()->clientScript->registerCssFile ($this->module->assetsUrl.'/css/campaignForm.css');

$insertableAttributes = array();
foreach(X2Model::model('Contacts')->attributeLabels() as $fieldName => $label)
    $insertableAttributes[$label] = '{'.$fieldName.'}';
$insertableAttributes[Yii::t('profile','Signature')] = '{signature}';

$contacts = Yii::t('contacts','{module} Attributes', array (
    '{module}' => Modules::displayName (false, "Contacts"))
);

$JSParams = CJSON::encode (array(
    'insertableAttributes' => array(
        $contacts => $insertableAttributes
)));

Yii::app()->clientScript->registerScriptFile ($this->module->assetsUrl.'/js/CampaignForm.js');
Yii::app()->clientScript->registerScript('editorSetup',"
    x2.CampaignForm ($JSParams);
", CClientScript::POS_READY);

$contactLists = CHtml::listData (Campaign::getValidContactLists(), 'id', 'name');
if ($model->list && !in_array ($model->list->id, array_keys ($contactLists))) {
    $contactLists[$model->list->id] = $model->list->name;
    $contactLists = ArrayUtil::asorti ($contactLists);
}

$templates = CHtml::listData (Docs::getEmailTemplates2('email', 'Contacts'), 'id', 'name');
$templates[0] = Yii::t('marketing',"Custom");

$form = $this->beginWidget('CActiveForm', array(
    'id'=>'campaign-form',
    'enableAjaxValidation'=>false
));
?>

<div id='top-container'>
    <div id='campaign-basic-form'>
        <div class='row'>
            <label><?php echo Yii::t('marketing', 'Campaign Name:')?></label>
            <?php echo $model->renderInput('name');?>
        </div>
        <div class='row'>
            <label><?php echo Yii::t('marketing', 'Contact List:')?></label>
            <?php 
            if (isset($model->list)) {
                $model->listId = $model->list->id;
            }
            echo X2Html::activeDropDownList ($model, 'listId', $contactLists, array(
                'prompt' => Yii::t('marketing','Select a Contact List'),
            ))?>
            <?php echo X2Html::hint (Yii::t('marketing', 'Choose a contact list to send the campaign out to, or create one here.'));?>
            <span id='quick-create-list'>
                <?php echo X2Html::fa ('plus')?>
            </span>
        </div>
    <div id='quick-create-list-form' style='display:none'>
        <h3><?php echo Yii::t('contacts', 'New Contact List');?></h3>
    </div>
        <div class='row'>
            <label><?php echo Yii::t('marketing', 'Email Template:')?></label>
            <?php echo X2Html::activeDropDownList ($model, 'template', $templates)?>
            <?php echo X2Html::hint (Yii::t('marketing', "Choose a email template to use for this campaign, or create a custom one here.")); ?>
        </div>
    </div>

</div>

<?php 

$this->widget('FormView', array(
    'model'=>$model,
    'form'=>$form,
     // Temporary kludge until system read-only fields are added
    'suppressFields'=>array ('listId'),
));
?>

<div id='bottom-container'>
    
    <div id='save-template-container'>
        <span class='x2-button 'id='save-template'><?php echo Yii::t('marketing', 'Save Email As Template')?></span>
    </div>

    <div id="attachments-container">
        <?php $this->renderPartial ('attachments', array (
            'model' => $model,
            'canUpload' => true
        )); ?>

    </div>

    <div class="row buttons">
        <?php 
        echo CHtml::submitButton(
            $model->isNewRecord ? 
                Yii::t('app','Create') : 
                Yii::t('app','Save'),
            array('class'=>'x2-button','id'=>'save-button','tabindex'=>24)); 
        ?>
    </div>
</div>

<?php $this->endWidget();?>

