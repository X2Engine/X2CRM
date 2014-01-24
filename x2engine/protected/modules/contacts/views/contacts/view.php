<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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
$this->setPageTitle(empty($model->name) ? $model->firstName." ".$model->lastName : $model->name);

Yii::app()->clientScript->registerScript('hints', '
    $(".hint").qtip();
');

// find out if we are subscribed to this contact
$result = Yii::app()->db->createCommand()
        ->select()
        ->from('x2_subscribe_contacts')
        ->where(array('and', 'contact_id=:contact_id', 'user_id=:user_id'), array(':contact_id' => $model->id, 'user_id' => Yii::app()->user->id))
        ->queryAll();
$subscribed = !empty($result); // if we got any results then user is subscribed

$authParams['assignedTo'] = $model->assignedTo;
$menuItems = array(
    array('label' => Yii::t('contacts', 'All Contacts'), 'url' => array('index')),
    array('label' => Yii::t('contacts', 'Lists'), 'url' => array('lists')),
    array('label' => Yii::t('contacts', 'Create Contact'), 'url' => array('create')),
    array('label' => Yii::t('contacts', 'View')),
    array('label' => Yii::t('contacts', 'Edit Contact'), 'url' => array('update', 'id' => $model->id)),
    array('label' => Yii::t('contacts', 'Share Contact'), 'url' => array('shareContact', 'id' => $model->id)),
//	array('label'=>Yii::t('contacts','View Relationships'),'url'=>'#', 'linkOptions'=>array('onclick'=>'toggleRelationshipsForm(); return false;')),
    array('label' => Yii::t('contacts', 'Delete Contact'), 'url' => '#', 'linkOptions' => array('submit' => array('delete', 'id' => $model->id), 'confirm' => 'Are you sure you want to delete this item?')),
    array('label' => Yii::t('app', 'Send Email'), 'url' => '#', 'linkOptions' => array('onclick' => 'toggleEmailForm(); return false;')),
    array('label' => Yii::t('app', 'Attach A File/Photo'), 'url' => '#', 'linkOptions' => array('onclick' => 'toggleAttachmentForm(); return false;')),
    array('label' => Yii::t('quotes', 'Quotes/Invoices'), 'url' => 'javascript:void(0)', 'linkOptions' => array('onclick' => 'x2.inlineQuotes.toggle(); return false;')),
    array('label' => Yii::t('quotes', ($subscribed ? 'Unsubscribe' : 'Subscribe')), 'url' => '#', 'linkOptions' => array('class' => 'x2-subscribe-button', 'onclick' => 'return subscribe($(this));', 'title' => Yii::t('contacts', 'Receive email updates every time information for {name} changes', array('{name}' => $model->firstName.' '.$model->lastName)))),
);
$opportunityModule = Modules::model()->findByAttributes(array('name' => 'opportunities'));
$accountModule = Modules::model()->findByAttributes(array('name' => 'accounts'));
$serviceModule = Modules::model()->findByAttributes(array('name' => 'services'));

if($serviceModule->visible){
    $createCaseButton = array(array('label' => Yii::t('services', 'Create Case'), 'url' => '#', 'linkOptions' => array('onclick' => 'return false;', 'id' => 'create-case')));
    array_splice($menuItems, 6, 0, $createCaseButton);
}

if($opportunityModule->visible && $accountModule->visible)
    $menuItems[] = array('label' => Yii::t('app', 'Quick Create'), 'url' => array('/site/createRecords', 'ret' => 'contacts'), 'linkOptions' => array('id' => 'x2-create-multiple-records-button', 'class' => 'x2-hint', 'title' => Yii::t('app', 'Create a Contact, Account, and Opportunity.')));

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
if(!IS_ANDROID && !IS_IPAD){
    echo '
<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
	<div class="page-title-fixed-inner">
		';
}
?>
<div class="page-title icon contacts">
    <h2><?php echo $model->name; ?></h2>
    <?php $this->renderPartial('_vcrControls', array('model' => $model)); ?>
    <?php
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
if(!IS_ANDROID && !IS_IPAD){
    echo '
	</div>
</div>
		';
}
?>
<div id="main-column">
    <?php $this->renderPartial('application.components.views._detailView', array('model' => $model, 'modelName' => 'contacts')); ?>

    <?php
// echo CJSON::encode($insertableAttributes);
// var_dump($insertableAttributes);

    $this->widget('InlineEmailForm', array(
        'attributes' => array(
            'to' => '"'.$model->name.'" <'.$model->email.'>, ',
            // 'subject'=>'hi',
            // 'redirect'=>'contacts/'.$model->id,
            'modelName' => 'Contacts',
            'modelId' => $model->id,
            'targetModel' => $model,
        ),
        'startHidden' => true,
            )
    );
    ?>

    <?php
    $this->widget('X2WidgetList', array(
        'block' => 'center',
        'model' => $model,
        'modelType' => 'contacts'
    ));
    ?>
    <?php
//$this->widget('InlineRelationships', array('model'=>$model, 'modelName'=>'contacts'));


    /*     * * Begin Create Related models ** */

// bellow is the javascript code that is executed when clicking:
//		"Create Opportunity"
//		"Create Account"
//		"Create Case"
// It creates a new model with a relationship to this contact. The creation is
// done via ajax calls to each models actionCreate method.
// the full javascript functions can be found in relationships.js
// urls, contact info, etc, is json encoded and then added as parameters to the
// javascript function that calls ajax to create the new model
    $linkModel = X2Model::model('Accounts')->findByPk($model->company);
    if(isset($linkModel))
        $accountName = json_encode($linkModel->name);
    else
        $accountName = json_encode('');
    $createContactUrl = $this->createUrl('/contacts/contacts/create');
    $createAccountUrl = $this->createUrl('/accounts/accounts/create');
    $createOpportunityUrl = $this->createUrl('/opportunities/opportunities/create');
    $createCaseUrl = $this->createUrl('/services/services/create');
    $assignedTo = json_encode($model->assignedTo);
    $tooltip = json_encode(Yii::t('contacts', 'Create a new Opportunity associated with this Contact.'));
    $contactTooltip = json_encode(Yii::t('contacts', 'Create a new Contact associated with this Contact.'));
    $accountsTooltip = json_encode(Yii::t('contacts', 'Create a new Account associated with this Contact.'));
    $caseTooltip = json_encode(Yii::t('contacts', 'Create a new Service Case associated with this Contact.'));
    $contactName = json_encode($model->firstName.' '.$model->lastName);
    $phone = json_encode($model->phone);
    $website = json_encode($model->website);
    $leadSource = json_encode($model->leadSource);
    $leadType = json_encode($model->leadtype);
    $leadStatus = json_encode($model->leadstatus);
    Yii::app()->clientScript->registerScript('create-model', "
	$(function() {
		// init create opportunity button
		$('#create-opportunity').initCreateOpportunityDialog('$createOpportunityUrl', 'Contacts', {$model->id}, $accountName, $assignedTo, $tooltip);

		// init create account button
		$('#create-account').initCreateAccountDialog2('$createAccountUrl', 'Contacts', {$model->id}, $accountName, $assignedTo, $phone, $website, $accountsTooltip);

		// init create contact button
		$('#create-contact').initCreateContactDialog('$createContactUrl', 'Contacts', '{$model->id}', $accountName, $assignedTo, '', '', $contactTooltip, $leadSource, $leadType, $leadStatus);

		// init create case button
		$('#create-case').initCreateCaseDialog('$createCaseUrl', 'Contacts', {$model->id}, $contactName, $assignedTo, $caseTooltip);
	});
");
//*** End Create Related models ***/
    ?>


    <?php $this->widget('Attachments', array('associationType' => 'contacts', 'associationId' => $model->id, 'startHidden' => true)); ?>
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

