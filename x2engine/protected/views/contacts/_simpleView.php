<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

$attributeLabels = ContactChild::attributeLabels();
$showSocialMedia = ProfileChild::getSocialMedia();

Yii::app()->clientScript->registerScript('detailVewFields', "

function hideSocialMedia() {
	$('#social-media').hide();
	$('#social-media-minimize').html('[+]');
}
function toggleSocialMedia() {
	$('#social-media').toggle('blind',{},400);
	var button = $('#social-media-minimize');
	if(button.html() == '[+]')
		button.html('[&ndash;]');
	else
		button.html('[+]');
}
".($showSocialMedia? '' : "$(function(){hideSocialMedia();});"),CClientScript::POS_HEAD);
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

// $template="<a href=".$this->createUrl('search/search?term=%23\\2')."> #\\2</a>";
		// $info=$model->backgroundInfo;
		// $info=mb_ereg_replace('(^|\s)#(\w\w+)',$template,$info);
?>
<div class="record">
	<div class="row">
		<div class="cell">
			<h2 style="margin-bottom:0;"><?php echo Yii::t('contacts','Contact:'); ?> <b><?php echo $model->firstName.' '.$model->lastName; ?></b>
			<?php echo CHtml::link(Yii::t('contacts','Detail View'),array('view','id'=>$model->id,'detail'=>1),array('class'=>'x2-button','style'=>'margin-left:20px;')); ?></h2>
		</div>
	</div>
	<div class="row" style="margin-top:0;">
		<div class="cell span-6">
			<?php
			if(!empty($model->assignedTo) && $model->assignedTo != 'Anyone' && isset($users[$model->assignedTo])) {
				//$assignedUser = $users[$model->assignedTo];
				
				$assignedUser = CActiveRecord::model('UserChild')->findByAttributes(array('username'=>$model->assignedTo));
				$userLink = CHtml::link($assignedUser->name,array('profile/view','id'=>$assignedUser->id));
			} else
				//echo $form->label($model,'assignedTo');
				$userLink = Yii::t('app','anyone');
			
			//$assignedUser 
			echo Yii::t('contacts','Assigned to {name}',array('{name}'=>$userLink));
			?>
		</div>
		<div class="cell">
			<?php
			// if(empty($model->company)) {
			if(!empty($model->accountId)) {
				$accountModel = CActiveRecord::model('AccountChild')->findByPk($model->accountId);
				if($accountModel != null)
					echo $accountModel->name . ' ' . CHtml::link('['.Yii::t('accounts','account').']',array('accounts/view','id'=>$accountModel->id))."<br />\n";
			} else if(!empty($model->company))
				echo $model->company."<br />\n";
			?>
		</div>
	</div>
	<div class="row" style="margin-bottom:5px;">
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
			$str=substr(Yii::app()->request->getServerName(),4);
			if(!empty($model->email)) echo CHtml::mailto($model->email,$model->email."?cc=dropbox@".$str);
			?>
		</div>
		<div class="cell">
			<?php if (!empty($model->website))
				echo CHtml::link(preg_replace('/^(http)s?:\/\//i','',$model->website),cleanupUrl($model->website));?>
		</div>
		<div class="cell" style="float:right;">
			<a href="#" onclick="toggleSocialMedia(); return false;"><?php echo Yii::t('contacts','Social Media'); ?> <span id="social-media-minimize">[&ndash;]</span></a>
		</div>
	</div>
	<div id="social-media" class="social-media" style="margin-top:5px;">
	<hr>
	<div class="row" style="margin-top:5px;">
			<?php 
			$img =  CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/skype.png');
			if(!empty($model->skype))
				echo '<div class="cell span-6">'.CHtml::link($img.' '.$model->skype,'skype:'.$model->skype.'?call')."</div>\n";

			$img =  CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/facebook.png');
			if(!empty($model->facebook))
				echo '<div class="cell span-6">'.CHtml::link($img.' '.humanUrl($model->facebook),cleanupUrl($model->facebook),array('target'=>'_blank'))."</div>\n";

			$img = CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/twitter.png');
			if(!empty($model->twitter))
				echo '<div class="cell span-6">'.CHtml::link($img.' '.$model->twitter,'http://www.twitter.com/'.$model->twitter,array('target'=>'_blank'))."</div>\n";

			$img = CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/googleplus.png');
			if(!empty($model->googleplus))
				echo '<div class="cell span-6">'.CHtml::link($img.' '.humanUrl($model->googleplus),cleanupUrl($model->googleplus),array('target'=>'_blank'))."</div>\n";

			$img =  CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/linkedin.png');
			if(!empty($model->linkedin))
				echo '<div class="cell span-6">'.CHtml::link($img.' '.humanUrl($model->linkedin),cleanupUrl($model->linkedin),array('target'=>'_blank'))."</div>\n";

			$img =  CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/other.png');
			if(!empty($model->otherUrl))
				echo '<div class="cell span-6">'.CHtml::link($img.' '.humanUrl($model->otherUrl),cleanupUrl($model->otherUrl),array('target'=>'_blank'))."</div>\n";
			?>

	</div>
	</div>
	
	<?php if(!empty($model->backgroundInfo)) { ?>
	<hr>
	<div class="row" style="margin-top:5px;">
		<div class="cell">
			<?php echo $this->convertUrls($model->backgroundInfo); ?>
		</div>
	</div>
<?php } ?>
</div>