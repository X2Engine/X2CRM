<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

Yii::app()->clientScript->registerCss('campaignContentCss',"#Campaign_content_inputBox {min-height:300px;}");
$this->pageTitle = $model->name; 

$authParams['assignedTo']=$model->createdBy;
$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('marketing','All Campaigns'), 'url'=>array('index')),
	array('label'=>Yii::t('module','Create'), 'url'=>array('create')),
	array('label'=>Yii::t('module','View')),
    array('label'=>Yii::t('module','Update'), 'url'=>array('update', 'id'=>$model->id)),
    array('label'=>Yii::t('module','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))),
	array('label'=>Yii::t('contacts','Contact Lists'), 'url'=>array('/contacts/lists')),
	array('label'=>Yii::t('marketing','Newsletters'), 'url'=>array('weblist/index')),
    array('label'=>Yii::t('marketing','Web Lead Form'), 'url'=>array('webleadForm')),
),$authParams);
?>
<div id="main-column" class="half-width">
<div class="record-title">
<h2><?php echo Yii::t('marketing', 'Campaign'); ?>: <b><?php echo $model->name; ?></b>

<?php if (Yii::app()->user->checkAccess('MarketingUpdate',$authParams)) { ?>
	<a class="x2-button" href="<?php echo $this->createUrl('update/'.$model->id);?>"><?php echo Yii::t('app','Edit');?></a>
<?php } ?>

</h2>
</div>
<?php
foreach(Yii::app()->user->getFlashes() as $key => $message) {
	echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
}
?>

<?php $this->renderPartial('application.components.views._detailView',array('model'=>$model, 'modelName'=>'Campaign')); ?>

<h2><?php echo Yii::t('app','Attachments'); ?></h2>

<div id="campaign-attachments-wrapper" class="x2-layout form-view">
<div class="formSection showSection">
	<div class="formSectionHeader">
		<span class="sectionTitle"><?php echo Yii::t('app','Attachments'); ?></span>
	</div>
	<div id="campaign-attachments" class="tableWrapper" style="min-height: 100px; padding: 5px;">
		<?php $attachments = $model->attachments; ?>
		<?php if($attachments) { ?>
			<?php foreach($attachments as $attachment) { ?>
				<?php $media = $attachment->mediaFile; ?>
				<?php if($media && $media->fileName) { ?>
					<div style="font-weight: bold;">
						<?php echo $media->fileName; ?>
					</div>
				<?php } ?>
			<?php } ?>
		<?php } ?>
	</div>
</div>
</div>

<div style="overflow: auto;">
<?php
if ($model->complete != 1 && $model->type=='Email') {
	if ($model->launchDate == 0) {
		echo CHtml::beginForm(array('launch', 'id'=>$model->id));
		echo CHtml::submitButton(
			Yii::t('marketing','Launch Now'),
			array('class'=>'x2-button highlight left','style'=>'margin-left:0;'));
		echo CHtml::endForm();
		echo CHtml::Button(
			Yii::t('marketing', 'Send Test Email'),
			array(
				'id'=>'test-email-button',
				'class'=>'x2-button left',
				'onclick'=>'toggleEmailForm(); return false;'
			)
		);
	} else if ($model->active == 1) {
		echo CHtml::beginForm(array('toggle', 'id'=>$model->id));
		echo CHtml::submitButton(
			Yii::t('app','Stop'),
			array('class'=>'x2-button left urgent','style'=>'margin-left:0;'));
		echo CHtml::endForm();
		echo CHtml::beginForm(array('complete', 'id'=>$model->id));
		echo CHtml::submitButton(
			Yii::t('marketing','Complete'),
			array('class'=>'x2-button highlight left','style'=>'margin-left:0;'));
		echo CHtml::endForm();
	} else {  //active == 0
		echo CHtml::beginForm(array('toggle', 'id'=>$model->id));
		echo CHtml::submitButton(
			Yii::t('app','Resume'),
			array('class'=>'x2-button highlight left','style'=>'margin-left:0;'));
		echo CHtml::endForm();
		echo CHtml::beginForm(array('complete', 'id'=>$model->id));
		echo CHtml::submitButton(
			Yii::t('marketing','Complete'),
			array('class'=>'x2-button left','style'=>'margin-left:0;'));
		echo CHtml::endForm();
		echo CHtml::Button(
			Yii::t('marketing', 'Send Test Email'),
			array(
				'id'=>'test-email-button',
				'class'=>'x2-button left',
				'onclick'=>'toggleEmailForm(); return false;'
			)
		);
	}

}?>
</div>

<div>
<?php
$this->widget('InlineEmailForm',
	array(
		'attributes'=>array(
			//'to'=>'"'.$model->name.'" <'.$model->email.'>, ',
			'subject'=>$model->subject,
			'message'=>$model->content,
			// 'redirect'=>'contacts/'.$model->id,
			'modelName'=>'Campaign',
			'modelId'=>$model->id,
		),
		'startHidden'=>true,
	)
);
?>
</div>

<?php if ($model->launchDate && $model->active && !$model->complete && $model->type == 'Email') { ?>
<div id="mailer-status" class="wide form" style="max-height: 150px; margin-top:13px;">
</div>
<?php 
	Yii::app()->clientScript->registerScript('mailer-status-update','
	function tryMail() { 
		newEl = $("<div id=\"mailer-status-active\">'. Yii::t('marketing','Attempting to send email') .'...</div>");
		newEl.prependTo($("#mailer-status")).slideDown(232);
		$.ajax("'. $this->createUrl('mail', array('id'=>$model->id)) .'").done(function(data) {
			var dataObj = JSON.parse(data);
			var htmlStr = "";
			for (var i=0; i < dataObj.messages.length; i++) {
				htmlStr += dataObj.messages[i] + "<br/>";
			}
			newEl.html(htmlStr);
			$("#mailer-status-active").removeAttr("id");
			$.fn.yiiGridView.update("contacts-grid");
			window.setTimeout(tryMail, dataObj.wait * 1000);
		});
	}
	tryMail();
	');
} ?>

<div style="margin-top: 23px;">
<?php
if(isset($contactList)) {
	//these columns will be passed to gridview, depending on the campaign type
	$displayColumns = array(
		array(
			'name'=>'name',
			'header'=>Yii::t('contacts','Name'),
			'headerHtmlOptions'=>array('style'=>'width: 15%;'),
			'value'=>'CHtml::link($data["firstName"] . " " . $data["lastName"],array("/contacts/view/".$data["id"]))',
			'type'=>'raw',
		),
		array(
			'name'=>'email',
			'header'=>Yii::t('contacts','Email'),
			'headerHtmlOptions'=>array('style'=>'width: 20%;'),
			//email comes from contacts table, emailAddress from list items table, we could have either one or none
			'value'=>'!empty($data["email"]) ? $data["email"] : (!empty($data["emailAddress"]) ? $data["emailAddress"] : "")',
		),
		array(
			'name'=>'phone',
			'header'=>Yii::t('contacts','Phone'),
			'headerHtmlOptions'=>array('style'=>'width: 10%;'),
		),
		array(
			'name'=>'address',
			'header'=>Yii::t('contacts','Address'),
			'headerHtmlOptions'=>array('style'=>'width: 25%;'),
			'value'=>'$data["address"]." ".$data["address2"]." ".$data["city"]." ".$data["state"]." ".$data["zipcode"]." ".$data["country"]'
		),
	);
	if ($model->type == 'Email' && ($contactList->type == 'campaign')) {
		$displayColumns = array_merge($displayColumns, array(
			array(
				'header'=>Yii::t('marketing','Sent') .': ' . $contactList->statusCount('sent'),
				'class'=>'CCheckBoxColumn',
				'checked'=>'$data["sent"] != 0',
				'selectableRows'=>0,
				'htmlOptions'=>array('style'=>'text-align: center;'),
				'headerHtmlOptions'=>array('style'=>'width: 7%;')
			),
			array(
				'name'=>'opened',
				'value'=>'$data["opened"]',
				'header'=>Yii::t('marketing','Opened') .': ' . $contactList->statusCount('opened'),
				'class'=>'CDataColumn', // this is a raw CDataColumn because CCheckboxColumns are not sortable
				'type'=>'raw',
				'value'=>'CHtml::checkbox("", $data["opened"] != 0, array("onclick"=>"return false;"))',
				'htmlOptions'=>array('style'=>'text-align: center;'),
				'headerHtmlOptions'=>array('style'=>'width: 7%;')
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
				'header'=>Yii::t('marketing','Unsubscribed') .': ' . $contactList->statusCount('unsubscribed'),
				'class'=>'CCheckBoxColumn',
				'checked'=>'$data["unsubscribed"] != 0',
				'selectableRows'=>0,
				'htmlOptions'=>array('style'=>'text-align: center;'),
				'headerHtmlOptions'=>array('style'=>'width: 9%;')
			),
			array(
				'header'=>Yii::t('contacts','Do Not Email'),
				'class'=>'CCheckBoxColumn',
				'checked'=>'$data["doNotEmail"] == 1',
				'selectableRows'=>0,
				'htmlOptions'=>array('style'=>'text-align: center;'),
				'headerHtmlOptions'=>array('style'=>'width: 7%;')
			),
		));
	}
	$this->widget('zii.widgets.grid.CGridView', array(
		'id'=>'contacts-grid',
		'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
		'dataProvider'=>$contactList->campaignDataProvider(20),
		'columns'=>$displayColumns,
	));
}
?>
</div>
<div style="margin-top: 23px;">
<?php
$this->widget('InlineTags', array('model'=>$model, 'modelName'=>'Campaign'));


?>
</div>
</div>
<div class="history half-width">
<?php
$this->widget('Publisher',
	array(
		'associationType'=>'marketing',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'halfWidth'=>true
	)
);

$this->widget('History',array('associationType'=>'marketing','associationId'=>$model->id));
?>
</div>