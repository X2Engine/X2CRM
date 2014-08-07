<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/Relationships.js');

Yii::app()->clientScript->registerCss('contactRecordViewCss',"

#content {
    background: none !important;
    border: none !important;
}
.show-left-bar .page-title > .x2-button {
    display: none !important;
}

");

Yii::app()->clientScript->registerResponsiveCssFile(
    Yii::app()->theme->baseUrl.'/css/responsiveRecordView.css');

$this->setPageTitle(empty($model->name) ? $model->firstName." ".$model->lastName : $model->name);

Yii::app()->clientScript->registerScript('hints', '
    $(".hint").qtip();
');

// find out if we are subscribed to this contact
$result = Yii::app()->db->createCommand()
        ->select()
        ->from('x2_subscribe_contacts')
        ->where(array('and', 'contact_id=:contact_id', 'user_id=:user_id'), 
            array(':contact_id' => $model->id, 'user_id' => Yii::app()->user->id))
        ->queryAll();
$subscribed = !empty($result); // if we got any results then user is subscribed

$authParams['X2Model'] = $model;
$menuItems = array(
    array('label' => Yii::t('contacts', 'All Contacts'), 'url' => array('index')),
    array('label' => Yii::t('contacts', 'Lists'), 'url' => array('lists')),
    array('label' => Yii::t('contacts', 'Create Contact'), 'url' => array('create')),
    array('label' => Yii::t('contacts', 'View')),
    array(
        'label' => Yii::t('contacts', 'Edit Contact'), 
        'url' => array('update', 'id' => $model->id)),
    array(
        'label' => Yii::t('contacts', 'Share Contact'), 
        'url' => array('shareContact', 'id' => $model->id)),
//    array('label'=>Yii::t('contacts','View Relationships'),'url'=>'#', 'linkOptions'=>array('onclick'=>'toggleRelationshipsForm(); return false;')),
    array(
        'label' => Yii::t('contacts', 'Delete Contact'), 
        'url' => '#', 'linkOptions' => array('submit' => array('delete', 'id' => $model->id),
        'confirm' => 'Are you sure you want to delete this item?')),
    array(
        'label' => Yii::t('app', 'Send Email'), 'url' => '#',
        'linkOptions' => array('onclick' => 'toggleEmailForm(); return false;')),
    array(
        'label' => Yii::t('app', 'Attach A File/Photo'), 'url' => '#',
        'linkOptions' => array('onclick' => 'toggleAttachmentForm(); return false;')),
    array(
        'label' => Yii::t('quotes', 'Quotes/Invoices'), 'url' => 'javascript:void(0)',
        'linkOptions' => array('onclick' => 'x2.inlineQuotes.toggle(); return false;')),
    array(
        'label' => Yii::t('quotes', ($subscribed ? 'Unsubscribe' : 'Subscribe')), 'url' => '#',
        'linkOptions' => array(
            'class' => 'x2-subscribe-button', 'onclick' => 'return subscribe($(this));',
            'title' => Yii::t('contacts', 'Receive email updates every time information for {name} changes',
                    array('{name}' => CHTML::encode($model->firstName.' '.$model->lastName)))
        )),
    array(
        'label' => Yii::t('contacts', 'Google Map'),
        'url' => 'javascript:void(0)',
        'linkOptions' => array (
            'onClick'=>"window.open(
                'https://www.google.com/maps/place/".urlencode ($model->getCityAddress ())."');"
            /*'onClick'=>"window.open(
                'https://www.google.com/maps/place/".urlencode ($model->getCityAddress ())."',
                'Google Map', 'height=200,width=200');"*/
        )
    ),
);
$opportunityModule = Modules::model()->findByAttributes(array('name' => 'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name' => 'accounts'));
$serviceModule = Modules::model()->findByAttributes(array('name' => 'services'));

if($opportunityModule->visible && $accountModule->visible) {
    $menuItems[] = array(
        'label' => Yii::t('app', 'Quick Create'), 
        'url' => array('/site/createRecords', 'ret' => 'contacts'),
        'linkOptions' => array(
            'id' => 'x2-create-multiple-records-button', 'class' => 'x2-hint',
            'title' => Yii::t('app', 'Create a Contact, Account, and Opportunity.')));
}

$menuItems[] = array(
    'label' => Yii::t('app', 'Print Record'),
    'url' => '#',
    'linkOptions' => array (
        'onClick'=>"window.open('".
            Yii::app()->createUrl('/site/printRecord', array (
                'modelClass' => 'Contacts',
                'id' => $model->id,
                'pageTitle' => Yii::t('app', 'Contact').': '.$model->name
            ))."');"
    )
);

$this->actionMenu = $this->formatMenu($menuItems, $authParams);

$modelType = json_encode("Contacts");
$modelId = json_encode($model->id);
Yii::app()->clientScript->registerScript('subscribe', "
$(function() {
    $('body').data('subscribed', ".json_encode($subscribed).");
    $('body').data('subscribeText', ".json_encode(Yii::t('contacts', 'Subscribe')).");
    $('body').data('unsubscribeText', ".json_encode(Yii::t('contacts', 'Unsubscribe')).");
    $('body').data('modelType', $modelType);
    $('body').data('modelId', $modelId);


    $('.x2-subscribe-button').qtip();
});

// subscribe or unsubscribe from this contact
function subscribe(link) {
    $('body').data('subscribed', !$('body').data('subscribed')); // subscribe or unsubscribe
    $.post('subscribe', {ContactId: '{$model->id}', Checked: $('body').data('subscribed')}); // tell server to subscribe / unsubscribe
    if( $('body').data('subscribed') )
        link.html($('body').data('unsubscribeText'));
    else
        link.html($('body').data('subscribeText'));
    return false; // stop event propagation
}

", CClientScript::POS_HEAD);

// widget layout
$layout = Yii::app()->params->profile->getLayout();
$themeUrl = Yii::app()->theme->getBaseUrl();
?>
<?php
if(true) {//!IS_ANDROID && !IS_IPAD){
    echo '
<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">';
}
?>
<div class="page-title icon contacts">
    <h2><?php echo CHtml::encode($model->name); ?></h2>
    <?php 
    $this->renderPartial('_vcrControls', array('model' => $model)); 
    if(Yii::app()->user->checkAccess('ContactsUpdate', $authParams)){
        if(!empty($model->company) && is_numeric($model->company)) {
            echo CHtml::link(
                '<span></span>', '#',
                array(
                    'class' => 'x2-button icon sync right hint',
                    'id' => $model->id.'-account-sync',
                    'title' => Yii::t('contacts', 'Clicking this button will pull any relevant '.
                        'fields from the associated Account record and overwrite the Contact '.
                        'data for those fields.  This operation cannot be reversed.'),
                    'submit' => array(
                        'syncAccount',
                        'id' => $model->id
                    ),
                    'confirm' => 'Are you sure you want to overwrite this record\'s fields with '.
                        'relevant Account data?'
                )
            );
        }
        echo CHtml::link(
            '<span></span>', $this->createUrl('update', array('id' => $model->id)),
            array(
                'class' => 'x2-button icon edit right',
                'title' => Yii::t('app', 'Edit contact'),
            )
        );
    }
    echo CHtml::link(
        '<img src="'.Yii::app()->request->baseUrl.'/themes/x2engine/images/icons/email_button.png'.
            '"></img>', '#',
        array(
            'class' => 'x2-button icon right email',
            'title' => Yii::t('app', 'Open email form'),
            'onclick' => 'toggleEmailForm(); return false;'
        )
    );
    ?>
</div>
<?php
if(true){ //!IS_ANDROID && !IS_IPAD){
    echo '
    </div>
</div>
        ';
}
?>
<div id="main-column">
    <div id='contacts-detail-view'> 
    <?php 
    $this->renderPartial(
        'application.components.views._detailView', 
        array('model' => $model, 'modelName' => 'contacts')); 
    ?>
    </div>
    <?php

    $this->widget('InlineEmailForm', array(
        'attributes' => array(
            'to' => '"'.$model->name.'" <'.$model->email.'>, ',
            'modelName' => 'Contacts',
            'modelId' => $model->id,
            'targetModel' => $model,
        ),
        'startHidden' => true,
    ));

    /*     * * Begin Create Related models ** */

    $linkModel = X2Model::model('Accounts')->findByPk($model->company);
    if(isset($linkModel))
        $accountName = ($linkModel->name);
    else
        $accountName = ('');
    $createContactUrl = $this->createUrl('/contacts/contacts/create');
    $createAccountUrl = $this->createUrl('/accounts/accounts/create');
    $createOpportunityUrl = $this->createUrl('/opportunities/opportunities/create');
    $createCaseUrl = $this->createUrl('/services/services/create');
    $assignedTo = ($model->assignedTo);
    $tooltip = (
        Yii::t('contacts', 'Create a new Opportunity associated with this Contact.'));
    $contactTooltip = (
        Yii::t('contacts', 'Create a new Contact associated with this Contact.'));
    $accountsTooltip = (
        Yii::t('contacts', 'Create a new Account associated with this Contact.'));
    $caseTooltip = (
        Yii::t('contacts', 'Create a new Service Case associated with this Contact.'));
    $contactName = ($model->firstName.' '.$model->lastName);
    $phone = ($model->phone);
    $website = ($model->website);
    $leadSource = ($model->leadSource);
    $leadtype = ($model->leadtype);
    $leadStatus = ($model->leadstatus);
//*** End Create Related models ***/

    $this->widget('X2WidgetList', array(
        'model' => $model,
        'widgetParamsByWidgetName' => array (
            'InlineRelationshipsWidget' => array (
                'defaultsByRelatedModelType' => array (
                    'Accounts' => array (
                        'name' => $accountName,
                        'assignedTo' => $assignedTo,
                        'phone' => $phone,
                        'website' => $website
                    ),
                    'Contacts' => array (
                        'company' => $accountName,
                        'assignedTo' => $assignedTo,
                        'leadSource' => $leadSource,
                        'leadtype' => $leadtype,
                        'leadstatus' => $leadStatus
                    ),
                    'Opportunity' => array (
                        'accountName' => $accountName,
                        'assignedTo' => $assignedTo,
                    ),
                    'Services' => array (
                        'contactName' => $contactName,
                        'assignedTo' => $assignedTo,
                    )
                )
            )
        )
    ));

    $this->widget(
        'Attachments', array(
            'associationType' => 'contacts',
            'associationId' => $model->id,
            'startHidden' => true)); ?>
    <div id="quote-form-wrapper">
        <?php
        $this->widget('InlineQuotes', array(
            'startHidden' => true,
            'recordId' => $model->id,
            'contactId' => $model->id,
            'account' => $model->getLinkedAttribute('company', 'name'),
            'modelName' => X2Model::getModuleModelName ()
                )
        );
        ?>
    </div>

</div>
<div class="history half-width">
    <?php
    $this->widget('Publisher', array(
        'associationType' => 'contacts',
        'associationId' => $model->id,
        'assignedTo' => Yii::app()->user->getName(),
        'calendar' => false
            )
    );

    $this->widget('History', array('associationType' => 'contacts', 'associationId' => $model->id));
    ?>
</div>

