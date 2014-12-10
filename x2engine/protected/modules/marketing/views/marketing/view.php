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

ThemeGenerator::removeBackdrop();
Yii::app()->clientScript->registerCss('recordViewCss',"

#content {
    background: none !important;
    border: none !important;
}
");

Yii::app()->clientScript->registerResponsiveCssFile(
    Yii::app()->theme->baseUrl.'/css/responsiveRecordView.css');


Yii::app()->clientScript->registerCss('campaignContentCss', '
#attachments-title {
	margin-left: 5px;
}
#Campaign_content_inputBox {min-height:300px;}
#Campaign_content_field {float:none;}
#Campaign_content_field .formInputBox {float:none;width:auto !important;margin-left:80px;}
#Campaign_content_field .formInputBox iframe {width:100%;background:#fff;border:0;}
');

// if the campaign has been launched, hide all collapsables
if($model->launchDate){
    Yii::app()->clientScript->registerScript('hide-all-collapsables', "
	$(function() {
		$('.formSection.collapsible').each(function() {
			if($(this).hasClass('showSection')) {
				$(this).removeClass('showSection');
				$(this).find('.tableWrapper').css('display', 'none');
			}
		});
	});
	");
}

$this->pageTitle = $model->name;
$themeUrl = Yii::app()->theme->getBaseUrl();
$authParams['X2Model'] = $model;

$menuOptions = array(
    'all', 'create', 'view', 'edit', 'delete', 'lists', 'newsletters',
    'weblead', 'x2flow',
);
$this->insertMenu($menuOptions, $model, $authParams);

?>

<div class="page-title-placeholder"></div>
<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">
<div class="page-title icon marketing">
    <h2><?php echo CHtml::encode($model->name); ?></h2>
    <?php if(Yii::app()->user->checkAccess('MarketingUpdate', $authParams)){ 
        echo X2Html::editRecordButton($model);
    } 
    echo X2Html::inlineEditButtons();
    ?>
</div>
</div>
</div>
<div id="main-column" class="half-width">
    <?php
    foreach(Yii::app()->user->getFlashes() as $key => $message){
        echo '<div class="flash-'.$key.'">'.$message."</div>\n";
    }
    ?>

    <?php
// var_dump($model->attributes);
    $partialParams = array(
        'model' => $model,
        'modelName' => 'Campaign',
        'specialFields' => array(
            'content' => '<div style="height:350px;"><iframe src="'.$this->createUrl('/marketing/marketing/viewContent',array('id'=>$model->id)).'" id="docIframe" frameBorder="0" style="height:100%;background:#fff;"></iframe></div>'
        )
    );
    $campaignType = $model->type;
    switch($campaignType){
        case "Email":
            break;
        case "Call List":
        case "Physical Mail":
            $partialParams['suppressFields'] = array('template', 'subject');
            break;
    }

    $this->renderPartial('application.components.views._detailView', $partialParams);
    ?>
    <div style="overflow: auto;">
        <?php
        if(!$model->complete && Yii::app()->user->checkAccess('MarketingLaunch')){

            if($model->launchDate == 0){
                echo CHtml::beginForm(array('launch', 'id' => $model->id));
                echo CHtml::submitButton(
                        Yii::t('marketing', 'Launch Now'), array('class' => 'x2-button highlight left', 'style' => 'margin-left:0;'));
                echo CHtml::endForm();
                if($model->type == 'Email')
                    echo CHtml::Button(
                            Yii::t('marketing', 'Send Test Email'), array(
                        'id' => 'test-email-button',
                        'class' => 'x2-button left',
                        'onclick' => 'toggleEmailForm(); return false;'
                            )
                    );
            } elseif($model->active){
                echo CHtml::beginForm(array('toggle', 'id' => $model->id));
                echo CHtml::submitButton(
                        Yii::t('app', 'Stop'), array('id'=>'campaign-toggle-button','class' => 'x2-button left urgent', 'style' => 'margin-left:0;'));
                echo CHtml::endForm();
                echo CHtml::beginForm(array('complete', 'id' => $model->id));
                echo CHtml::submitButton(
                        Yii::t('marketing', 'Complete'), array('id'=>'campaign-complete-button','class' => 'x2-button highlight left', 'style' => 'margin-left:0;'));
                echo CHtml::endForm();
            }else{ //active == 0
                echo CHtml::beginForm(array('toggle', 'id' => $model->id));
                echo CHtml::submitButton(
                        Yii::t('app', 'Resume'), array('class' => 'x2-button highlight left', 'style' => 'margin-left:0;'));
                echo CHtml::endForm();
                echo CHtml::beginForm(array('complete', 'id' => $model->id));
                echo CHtml::submitButton(
                        Yii::t('marketing', 'Complete'), array('class' => 'x2-button left', 'style' => 'margin-left:0;'));
                echo CHtml::endForm();
                if($model->type == 'Email')
                    echo CHtml::Button(
                            Yii::t('marketing', 'Send Test Email'), array(
                        'id' => 'test-email-button',
                        'class' => 'x2-button left',
                        'onclick' => 'toggleEmailForm(); return false;'
                            )
                    );
            }
        }
        ?>
    </div>
    <?php
    $staticLinkModel = Contacts::model();
    $this->widget('InlineEmailForm', array(
        'attributes' => array(
            'subject' => $model->subject,
            'message' => $model->content,
            'modelName' => 'Campaign',
            'modelId' => $model->id,
            'credId' => $model->sendAs
        ),
        'postReplace' => 1,
        'skipEvent' => 1,
        'template' => Fields::id ($model->template),
        'insertableAttributes' => array(),
        'startHidden' => true,
        'associationType' => 'Contacts',
        'specialFields' => 
            '<div class="row">'.
                CHtml::label(
                    Yii::t('contacts','{module}', array(
                        '{module}' => Modules::displayName(false, "Contacts")
                    )),
                    'Contacts[name]',
                    array('class'=>'x2-email-label')
                ).$this->widget('zii.widgets.jui.CJuiAutoComplete', 
                    array(
                        'model' => Contacts::model(), // dummy
                        'attribute' => 'name', // dummy
                        'source' => $linkSource = Yii::app()->controller->createUrl(
                            $staticLinkModel->autoCompleteSource),
                        'options' => array(
                            'minLength' => '1',
                            'select' => 'js:function( event, ui ) {
                                $("#InlineEmail_modelId").val(ui.item.id);
                                $(this).val(ui.item.value);
                                $("#InlineEmail_modelName").val("Contacts");
                                return false;
                            }',
                            'create' => 'js:function(event, ui) {
                                $(this).data( "uiAutocomplete" )._renderItem = function(ul,item) {
                                    return $("<li>").data("item.autocomplete",item).append(
                                        x2.forms.renderContactLookup(item)).appendTo(ul);
                                };
                            }',
                        ),
                        'htmlOptions' => array('style'=>'max-width:200px;')
                    ), true).
                CHtml::tag(
                    'span', 
                    array(
                        'class' => 'x2-hint',
                        'style'=>'display:inline-block; margin-left:5px;',
                        'title' => Yii::t(
                            'marketing',
                            'The {contact} you enter here will be used for variable replacement, ' .
                            'i.e. for "John Doe" the token {firstName} will get replaced with ' .
                            '"John"', array(
                                '{contact}' => Modules::displayName(false, "Contacts"),
                            )
                        )
                    ),'[?]').'</div>',
    ));


    if($model->type === 'Email'){
        ?>
        <?php
        if($model->launchDate && $model->active && !$model->complete){
            $this->widget('EmailProgressControl',array(
                'campaign' => $model,
            ));
        }
        ?>
        <?php
        // find out if attachments are minimized
        $showAttachments = true;
        $formSettings = Profile::getFormSettings('campaign');
        $layout = FormLayout::model()->findByAttributes(array('model' => 'Campaign', 'defaultView' => 1));
        if(isset($layout)){
            $layoutData = json_decode($layout->layout, true);
            $count = count($layoutData['sections']);
            if(isset($formSettings[$count])){
                $showAttachments = $formSettings[$count];
            }
        }
        ?>

        <div id="campaign-attachments-wrapper" class="x2-layout form-view">
            <div class="formSection collapsible <?php echo $showAttachments ? 'showSection' : ''; ?>">
                <div class="formSectionHeader">
                    <a href="javascript:void(0)" class="formSectionHide">
                        <?php echo X2Html::fa('fa-caret-down')?>
                    </a>
                    <a href="javascript:void(0)" class="formSectionShow">
                        <?php echo X2Html::fa('fa-caret-right')?>
                    </a>
                    <span class="sectionTitle"><?php echo Yii::t('app', 'Attachments'); ?></span>
                </div>
                <div id="campaign-attachments" class="tableWrapper" style="padding: 5px;
    <?php echo $showAttachments ? '' : 'display: none;'; ?>">
                    <div style="min-height: 100px;">
                     <?php $attachments = $model->attachments; ?>
                        <?php if($attachments){ ?>
                            <?php foreach($attachments as $attachment){ ?>
                                <?php $media = $attachment->mediaFile; ?>
                                <?php if($media && $media->fileName){ ?>
                                    <div style="font-weight: bold;">
                                    <?php echo $media->fileName; ?>
                                    </div>
                                    <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
}
$this->widget('X2WidgetList', array(
    'block' => 'center',
    'model' => $model,
    'modelType' => 'Marketing'
));

?>

    <div style="margin-top: 23px;">
<?php
if(isset($contactList) && $model->launchDate){
    //these columns will be passed to gridview, depending on the campaign type
    $displayColumns = array(
        array(
            'name' => 'name',
            'header' => Yii::t('contacts', 'Name'),
            'headerHtmlOptions' => array('style' => 'width: 15%;'),
            'value' => 'CHtml::link($data["firstName"] . " " . $data["lastName"],array("/contacts/contacts/view","id"=>$data["id"]))',
            'type' => 'raw',
        ),
    );
    if($model->type == 'Email' && ($contactList->type == 'campaign')){
        $displayColumns = array_merge($displayColumns, array(
            array(
                'name' => 'email',
                'header' => Yii::t('contacts', 'Email'),
                'headerHtmlOptions' => array('style' => 'width: 20%;'),
                //email comes from contacts table, emailAddress from list items table, we could 
                // have either one or none
                'value' => '!empty($data["email"]) ? 
                    $data["email"] : (!empty($data["emailAddress"]) ? $data["emailAddress"] : "")',
            ),
            array(
                'name' => 'sent',
                'header' => Yii::t('marketing', 'Sent').': '.$contactList->statusCount('sent'),
                'class' => 'CCheckBoxColumn',
                'checked' => '$data["sent"] > 0',
                'selectableRows' => 0,
                'htmlOptions' => array('style' => 'text-align: center;'),
                'headerHtmlOptions' => array('style' => 'width: 7%;', 'title' => $contactList->statusCount('sent'))
            ),
            array(
                'name' => 'opened',
                'value' => '$data["opened"]',
                'header' => Yii::t('marketing', 'Opened').': '.$contactList->statusCount('opened'),
                // this is a raw CDataColumn because CCheckboxColumns are not sortable
                'class' => 'CDataColumn', 
                'type' => 'raw',
                'value' => 'CHtml::checkbox(
                    "", $data["opened"] != 0, array("onclick"=>"return false;"))',
                'htmlOptions' => array('style' => 'text-align: center;'),
                'headerHtmlOptions' => array(
                    'style' => 'width: 7%;', 'title' => $contactList->statusCount('opened'))
            ),
            array(
                'name' => 'clicked',
                'header'=>
                    Yii::t('marketing','Clicked') .': ' . $contactList->statusCount('clicked'),
                'class'=>'CCheckBoxColumn',
                'checked'=>'$data["clicked"] != 0',
                'selectableRows'=>0,
                'htmlOptions'=>array('style'=>'text-align: center;'),
                'headerHtmlOptions'=>array('style'=>'width: 7%;')
            ),
            array(
                'name' => 'unsubscribed',
                'header' => Yii::t('marketing', 'Unsubscribed').': '.$contactList->statusCount('unsubscribed'),
                'class' => 'CCheckBoxColumn',
                'checked' => '$data["unsubscribed"] != 0',
                'selectableRows' => 0,
                'htmlOptions' => array('style' => 'text-align: center;'),
                'headerHtmlOptions' => array('style' => 'width: 9%;', 'title' => $contactList->statusCount('unsubscribed'))
            ),
            array(
                'name' => 'doNotEmail',
                'header' => Yii::t('contacts', 'Do Not Email'),
                'class' => 'CCheckBoxColumn',
                'checked' => '$data["doNotEmail"] == 1',
                'selectableRows' => 0,
                'htmlOptions' => array('style' => 'text-align: center;'),
                'headerHtmlOptions' => array('style' => 'width: 7%;')
            ),
                ));
    }elseif($model->type == 'Call List'){
        $displayColumns = array_merge($displayColumns, array(
            array(
                'name' => 'phone',
                'header' => Yii::t('contacts', 'Phone'),
                'headerHtmlOptions' => array('style' => 'width: 10%;'),
            ),
                ));
    }elseif($model->type == 'Physical Mail'){
        $displayColumns = array_merge($displayColumns, array(
            array(
                'name' => 'address',
                'header' => Yii::t('contacts', 'Address'),
                'headerHtmlOptions' => array('style' => 'width: 25%;'),
                'value' => '$data["address"]." ".$data["address2"]." ".$data["city"]."'.
                ' ".$data["state"]." ".$data["zipcode"]." ".$data["country"]'
            ),
        ));
    }
    ?>
    <div class='x2-layout-island'>
    <?php
    $this->widget('X2GridViewGeneric', array(
        'defaultGvSettings' => array (
            'name' => 140,
            'email' => 140,
            'opened' => 80,
            'clicked' => 80,
            'unsubscribed' => 80,
            'doNotEmail' => 80,
            'sent' => 80,
        ),
        'id' => 'campaign-grid',
	    'template'=> '<div class="page-title">{title}'
		    .'{buttons}{summary}</div>{items}{pager}',
        'buttons' => array ('autoResize'),
        'dataProvider' => $contactList->campaignDataProvider(Profile::getResultsPerPage()),
        'columns' => $displayColumns,
        'enablePagination' => true,
        'gvSettingsName' => 'campaignProgressGrid',
    ));
}
?>
    </div>
    </div>

</div>
<div class="history half-width">
<?php
$this->widget('Publisher', array(
    'associationType' => 'marketing',
    'associationId' => $model->id,
    'assignedTo' => Yii::app()->user->getName(),
    'calendar' => false
));

$this->widget('History', array('associationType' => 'marketing', 'associationId' => $model->id));
?>
</div>

