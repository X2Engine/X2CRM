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

$attributeLabels = X2Model::model('Contacts')->attributeLabels();
$showSocialMedia = Yii::app()->params->profile->showSocialMedia;

Yii::app()->clientScript->registerScript('detailVewFields', "

var socialMediaOpen = true;
var socialMediaHeight = 0;
function hideSocialMedia() {
	socialMediaHeight = $('#social-media').height();
	//$('#social-media').hide();
	$('#social-media').css('height',0);
	$('#social-media').css('padding-bottom',0);
	$('#social-media').css('border-bottom-width',0);
	$('#social-media-minimize').html('[+]');
	//$('#social-media-toggle').css('z-index','0');
	//$('#social-media-toggle').css('border-bottom','1px solid #ddd');
	socialMediaOpen = false;
}
function toggleSocialMedia() {
	var button = $('#social-media-minimize');

	if(socialMediaOpen) {
		$('#social-media').stop();
		$('#social-media').animate({height:0,paddingBottom:0},400,'swing', function() {
			$('#social-media').hide();
			$('#social-media-toggle').css('border-bottom-width','1px');
		});

		button.html('[+]');
	} else {
		$('#social-media').show();
		$('#social-media').stop();
		$('#social-media').animate({height:socialMediaHeight,paddingBottom:5},400,'swing');
		$('#social-media-toggle').css('border-bottom-width','0');

		button.html('[&ndash;]');
	}

	socialMediaOpen = !socialMediaOpen;
}
".($showSocialMedia? "$(function() {socialMediaHeight = $('#social-media').css('height'); });" : "$(function(){hideSocialMedia();});"),CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScript('stopEdit','
	$(document).ready(function(){
		$("td#background a").click(function(e){
			e.stopPropagation();
		});
	});
');

function cleanupUrl($url) {
	if (!preg_match('/(http)s?:\/\//i',$url))
		$url = 'http://'.$url;
	return $url;
}
function humanUrl($url) {
	$url = preg_replace('/\/$/i','',$url);		//remove trailing slash
	$url = preg_replace('/^(http)s?:\/\/(www\.)?/i','',$url);		//remove protocol (http://, etc)
	return $url;
}
?>
<div class="record no-border">
<table class="details">
<tr>
	<td colspan="6" style="background:#eee;padding:5px 0 0 0;">
		<div class="row">
			<div class="cell span-6">
				<?php
				if(!empty($model->title))
					echo '<b>'.$model->title.'</b>';
				if(!empty($model->title) && !empty($model->company))
					echo ', ';

				if(!empty($model->accountId) && $model->accountId!=0) {
					$accountModel = X2Model::model('Accounts')->findByPk($model->accountId);
					if($accountModel != null)
						echo CHtml::link($accountModel->name,array('/accounts/accounts/view','id'=>$accountModel->id))."<br />\n";
				} else if(!empty($model->company))
					echo $model->company."<br />\n";
				?>
			</div>
		</div>
		<div class="row">
			<div class="cell span-6">
				<?php
				if(!empty($model->phone))
					echo '<b>'.Yii::t('contacts','Work').'</b> '.$model->phone."</b><br />\n";
				if(!empty($model->phone2))
					echo '<b>'.Yii::t('contacts','Cell').' </b>'.$model->phone2."</b><br />\n";
				?>
			</div>
			<div class="cell">
				<?php if(!empty($model->address)) echo $model->address . '<br />'; ?>
				<?php echo $model->city; if(!empty($model->city) && !empty($model->state)) echo ', ';?>
				<?php echo $model->state; ?>
				<?php echo $model->zipcode; ?>
				<?php if(!empty($model->country)) echo ' ' . $model->country; ?><br />
			</div>
		</div>
		<div class="row">
			<div class="cell span-6">
				<?php
				if(!empty($model->email)) echo CHtml::mailto($model->email,$model->email);
				?>
			</div>
			<div class="cell">
				<?php if (!empty($model->website))
					echo CHtml::link(preg_replace('/^(http)s?:\/\//i','',$model->website),cleanupUrl($model->website));?>
			</div>
		</div>
		<div class="row" style="margin-bottom:-1px;">
			<div class="cell">
				<?php
				$this->widget('CStarRating',array(
					'model'=>$model,
					'attribute'=>'rating',
					'readOnly'=>true,
					'minRating'=>1, //minimal valuez
					'maxRating'=>5,//max value
					'starCount'=>5, //number of stars
					'cssFile'=>Yii::app()->theme->getBaseUrl().'/css/rating/jquery.rating.css',
				)); ?>
			</div>
			<div class="cell" id="social-media-toggle" style="margin:0">
				<a href="#" onclick="toggleSocialMedia(); return false;"><?php echo Yii::t('contacts','Social Networks'); ?> <span id="social-media-minimize">[&ndash;]</span></a>
			</div>
		</div>
		<div class="row social-media" id="social-media">
		<?php
		$img =  CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/skype.png');
		if(!empty($model->skype))
			echo '<div class="span-6">'.CHtml::link($img.' '.$model->skype,'skype:'.$model->skype.'?call')."</div>\n";

		$img =  CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/facebook.png');
		if(!empty($model->facebook))
			echo '<div class="span-6">'.CHtml::link($img.' '.humanUrl($model->facebook),cleanupUrl($model->facebook),array('target'=>'_blank'))."</div>\n";

		$img = CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/twitter.png');
		if(!empty($model->twitter))
			echo '<div class="span-6">'.CHtml::link($img.' '.$model->twitter,'http://www.twitter.com/'.$model->twitter,array('target'=>'_blank'))."</div>\n";

		$img = CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/googleplus.png');
		if(!empty($model->googleplus))
			echo '<div class="span-6">'.CHtml::link($img.' '.humanUrl($model->googleplus),cleanupUrl($model->googleplus),array('target'=>'_blank'))."</div>\n";

		$img =  CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/linkedin.png');
		if(!empty($model->linkedin))
			echo '<div class="span-6">'.CHtml::link($img.' '.humanUrl($model->linkedin),cleanupUrl($model->linkedin),array('target'=>'_blank'))."</div>\n";

		$img =  CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/other.png');
		if(!empty($model->otherUrl))
			echo '<div class="span-6">'.CHtml::link($img.' '.humanUrl($model->otherUrl),cleanupUrl($model->otherUrl),array('target'=>'_blank'))."</div>\n";
		?>
		</div>
	</td>
</tr>
<tr>
	<td colspan="6" style="padding:10px;">
		<?php echo $this->convertUrls($model->backgroundInfo); ?>
	</td>
</tr>
<tr>
	<td class="label" width="80">Assigned to</td>
	<td>
		<?php
		if(!empty($model->assignedTo) && $model->assignedTo != 'Anyone' && isset($users[$model->assignedTo])) {
			//$assignedUser = $users[$model->assignedTo];

			$assignedUser = X2Model::model('User')->findByAttributes(array('username'=>$model->assignedTo));
			$userLink = CHtml::link($assignedUser->name,array('/profile/view','id'=>$assignedUser->id));
		} else
			//echo $form->label($model,'assignedTo');
			$userLink = Yii::t('app','anyone');

		//$assignedUser
		echo $userLink;
		?>
	</td>
	<td class="label"><b><?php echo $attributeLabels['priority']; ?></b></td>
	<td>
		<?php
		if(empty($model->priority))
			$model->priority = 'Medium';
		echo CHtml::dropDownList('priority',$model->priority,array(
			'Low'=>Yii::t('contacts','Low'),
			'Medium'=>Yii::t('contacts','Medium'),
			'High'=>Yii::t('contacts','High')
		),array('disabled'=>true)); ?>
	</td>
	<td class="label"><b><?php echo $attributeLabels['visibility']; ?></b></td>
	<td>
		<?php
		echo CHtml::dropDownList('visibility',$model->visibility,array(
			1=>Yii::t('contacts','Public'),
			0=>Yii::t('contacts','Private')
		),array('disabled'=>true));
		// $model->createDate = time();
		// echo date("Y-m-d",$model->createDate);
		?>
	</td>
</tr>
</table>
</div>

