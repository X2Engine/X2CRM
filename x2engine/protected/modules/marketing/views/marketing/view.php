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

Yii::app()->clientScript->registerScript('mailer-status-update', '
$("#docIframe").parent().resizable({
	minHeight:100,
	handles:"s,se",
	resize: function(event, ui) {
		$(this).css("width","");

		$(\'<div class="ui-resizable-iframeFix" style="background: #fff;"></div>\')
			.css({
				width: this.offsetWidth+"px", height: this.offsetHeight+"px",
				position: "absolute", opacity: "0.001", zIndex: 1000
			})
			.css($(this).offset())
			.appendTo("body");
	},
	stop: function(event, ui) {
		$(this).css("width","");
		$("div.ui-resizable-iframeFix").each(function() { this.parentNode.removeChild(this); }); //Remove frame helpers
	}
});
', CClientScript::POS_READY);

$this->pageTitle = $model->name;
$themeUrl = Yii::app()->theme->getBaseUrl();
$authParams['assignedTo'] = $model->createdBy;
$this->actionMenu = $this->formatMenu(array(
    array('label' => Yii::t('marketing', 'All Campaigns'), 'url' => array('index')),
    array('label' => Yii::t('module', 'Create'), 'url' => array('create')),
    array('label' => Yii::t('module', 'View')),
    array('label' => Yii::t('module', 'Update'), 'url' => array('update', 'id' => $model->id)),
    array('label' => Yii::t('module', 'Delete'), 'url' => '#', 'linkOptions' => array('submit' => array('delete', 'id' => $model->id), 'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'))),
    array('label' => Yii::t('contacts', 'Contact Lists'), 'url' => array('/contacts/lists')),
    array('label' => Yii::t('marketing', 'Newsletters'), 'url' => array('weblist/index')),
    array('label' => Yii::t('marketing', 'Web Lead Form'), 'url' => array('webleadForm')),
    array('label' => Yii::t('app', 'X2Flow'), 'url' => array('/studio/flowIndex'), 'visible' => (Yii::app()->params->edition === 'pro')),
        ), $authParams);
?>
<div class="page-title icon marketing">
    <h2><?php echo $model->name; ?></h2>
    <?php if(Yii::app()->user->checkAccess('MarketingUpdate', $authParams)){ ?>
        <a class="x2-button icon edit right" href="<?php echo $this->createUrl('update',array('id'=>$model->id)); ?>"><span></span></a>
    <?php } ?>
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
            'content' => '<div style="height:350px;"><iframe src="'.$this->createUrl('/marketing/viewContent/',array('id'=>$model->id)).'" id="docIframe" frameBorder="0" style="height:100%;background:#fff;"></iframe></div>'
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
        if(!$model->complete && in_array($model->type, array('Email', 'Call List')) &&
                Yii::app()->user->checkAccess('MarketingLaunch')){

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
                        Yii::t('app', 'Stop'), array('class' => 'x2-button left urgent', 'style' => 'margin-left:0;'));
                echo CHtml::endForm();
                echo CHtml::beginForm(array('complete', 'id' => $model->id));
                echo CHtml::submitButton(
                        Yii::t('marketing', 'Complete'), array('class' => 'x2-button highlight left', 'style' => 'margin-left:0;'));
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
            //'to'=>'"'.$model->name.'" <'.$model->email.'>, ',
            'subject' => $model->subject,
            'message' => $model->content,
            // 'template'=>'campaign',
            // 'redirect'=>'contacts/'.$model->id,
            'modelName' => 'Campaign',
            'modelId' => $model->id,
            'credId' => $model->sendAs
        ),
        'postReplace' => 1,
        'skipEvent' => 1,
        'insertableAttributes' => array(),
        'startHidden' => true,
        'specialFields' => '<div class="row">'.CHtml::label(Yii::t('contacts','Contact'),'Contacts[name]',array('class'=>'x2-email-label')).$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
            'model' => Contacts::model(), // dummy
            'attribute' => 'name', // dummy
            'source' => $linkSource = Yii::app()->controller->createUrl($staticLinkModel->autoCompleteSource),
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
                        return $("<li>").data("item.autocomplete",item).append(renderContactLookup(item)).appendTo(ul);
                    };
                }',
            ),
            'htmlOptions' => array('style'=>'max-width:200px;')

        ), true).CHtml::tag('span', array('class' => 'x2-hint', 'style'=>'display:inline-block; margin-left:5px;', 'title' => Yii::t('marketing', 'The contact you enter here will be used for variable replacement, i.e. for "John Doe" the token {firstName} will get replaced with "John"')),'[?]').'</div>',
    ));


    if($model->type === 'Email'){
        ?>
        <h2 id='attachments-title'><?php echo Yii::t('app', 'Attachments'); ?></h2>
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
                    <a href="javascript:void(0)" class="formSectionHide">[–]</a>
                    <a href="javascript:void(0)" class="formSectionShow">[+]</a>
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
    <?php if($model->launchDate && $model->active && !$model->complete && $model->type == 'Email'){ ?>
        <div id="mailer-status" class="wide form" style="max-height: 150px; margin-top:13px;">
        </div>
    <?php
    Yii::app()->clientScript->registerScript('mailer-status-update', '
		function tryMail() {
			newEl = $("<div id=\"mailer-status-active\">'.Yii::t('marketing', 'Attempting to send email').'...</div>");
			newEl.prependTo($("#mailer-status")).slideDown(232);
			$.ajax({
                url:'.CJSON::encode($this->createUrl('mail', array('id' => $model->id))).',
                dataTye:"json"
            }).done(function(data) {
				var htmlStr = "";
				for (var i=0; i < data.messages.length; i++) {
                    if(data.messages[i]!="")
    					htmlStr += data.messages[i] + "<br/>";
				}
				newEl.html(htmlStr);
				$("#mailer-status-active").removeAttr("id");
				$.fn.yiiGridView.update("contacts-grid");
                //console.log ("hello, wait = " + data.wait);
				window.setTimeout(tryMail, data.wait * 1000);
			});
		}
		tryMail();
		');
}
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
            'value' => 'CHtml::link($data["firstName"] . " " . $data["lastName"],array("/contacts/view/".$data["id"]))',
            'type' => 'raw',
        ),
    );
    if($model->type == 'Email' && ($contactList->type == 'campaign')){
        $displayColumns = array_merge($displayColumns, array(
            array(
                'name' => 'email',
                'header' => Yii::t('contacts', 'Email'),
                'headerHtmlOptions' => array('style' => 'width: 20%;'),
                //email comes from contacts table, emailAddress from list items table, we could have either one or none
                'value' => '!empty($data["email"]) ? $data["email"] : (!empty($data["emailAddress"]) ? $data["emailAddress"] : "")',
            ),
            array(
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
                'class' => 'CDataColumn', // this is a raw CDataColumn because CCheckboxColumns are not sortable
                'type' => 'raw',
                'value' => 'CHtml::checkbox("", $data["opened"] != 0, array("onclick"=>"return false;"))',
                'htmlOptions' => array('style' => 'text-align: center;'),
                'headerHtmlOptions' => array('style' => 'width: 7%;', 'title' => $contactList->statusCount('opened'))
            ),
            /* disable this for now
              array(
              'header'=>Yii::t('marketing','Clicked') .': ' . $contactList->statusCount('clicked'),
              'class'=>'CCheckBoxColumn',
              'checked'=>'$data["clicked"] != 0',
              'selectableRows'=>0,
              'htmlOptions'=>array('style'=>'text-align: center;'),
              'headerHtmlOptions'=>array('style'=>'width: 7%;')
              ),
              /* disable end */
            array(
                'header' => Yii::t('marketing', 'Unsubscribed').': '.$contactList->statusCount('unsubscribed'),
                'class' => 'CCheckBoxColumn',
                'checked' => '$data["unsubscribed"] != 0',
                'selectableRows' => 0,
                'htmlOptions' => array('style' => 'text-align: center;'),
                'headerHtmlOptions' => array('style' => 'width: 9%;', 'title' => $contactList->statusCount('unsubscribed'))
            ),
            array(
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
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'contacts-grid',
        'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
        'template' => '{summary}{items}{pager}',
        'summaryText' => Yii::t('app', 'Displaying {start}-{end} result(s).')
        .'<div class="form no-border" style="margin: 0; padding: 2px 3px; display: inline-block;'.
        ' vertical-align: middle; overflow: hidden;"> '
        .CHtml::dropDownList('resultsPerPage', Profile::getResultsPerPage(), Profile::getPossibleResultsPerPage(), array(
            'ajax' => array(
                'url' => $this->createUrl('/profile/setResultsPerPage'),
                'complete' => "function(response) { $.fn.yiiGridView.update('contacts-grid', {data: {'id_page': 1}}) }",
                'data' => "js: {results: $(this).val()}",
            ),
            'style' => 'margin: 0;',
        ))
        .' </div>',
        'dataProvider' => $contactList->campaignDataProvider(Profile::getResultsPerPage()),
        'columns' => $displayColumns,
        'enablePagination' => true
    ));
}
?>
    </div>

</div>
<div class="history half-width">
<?php
$this->widget('Publisher', array(
    'associationType' => 'marketing',
    'associationId' => $model->id,
    'assignedTo' => Yii::app()->user->getName(),
    'halfWidth' => true
));

$this->widget('History', array('associationType' => 'marketing', 'associationId' => $model->id));
?>
</div>

