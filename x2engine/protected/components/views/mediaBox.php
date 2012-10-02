<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
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
?>

<?php
$widgetSettings = ProfileChild::getWidgetSettings();
$mediaSettings = $widgetSettings->MediaBox;
$mediaBoxHeight = $mediaSettings->mediaBoxHeight;
$hideUsers = $mediaSettings->hideUsers;
$imageTooltips = '';
$minimizeUserMedia = '';
$username = Yii::app()->params->profile->username;
$fullname = Yii::app()->params->profile->fullName;
?>

<div id="media-library-widget-wrapper">
<div id="media-library-widget-container">

		<?php
		$toggleUserMediaVisibleUrl = $this->controller->createUrl('/media/toggleUserMediaVisible') ."?user=$username";
		$visible = !in_array($username, $hideUsers);
		if(!$visible)
		    $minimizeUserMedia .= "$('$username-media').hide();\n";		
		$minimizeLink = CHtml::ajaxLink($visible? '[&ndash;]' : '[+]', $toggleUserMediaVisibleUrl, array('success'=>"function(response) { toggleUserMedia($(\"#$username-media\"), $('#$username-media-showhide'), response); }", 'type'=>'GET'), array('id'=>"$username-media-showhide", 'class'=>'media-library-showhide')); // javascript function togglePortletVisible defined in js/layout.js
		?>
		<strong><?php echo $fullname; ?></strong>
		<?php echo $minimizeLink; ?><br>

		<?php $myMediaItems = Media::model()->findAllByAttributes(array('uploadedBy'=>$username)); // get current user's media ?>

		<div id="<?php echo $username; ?>-media" class="user-media-list">
			<?php foreach($myMediaItems as $item) {
				$id = "$username-media-id-{$item->id}";
				echo '<span class="media-item">';
				echo CHtml::link($item->fileName, array('/media', 'view'=>$item->id), array('class'=>'x2-link media', 'id'=>$id, 'style'=>'curosr:pointer;'));
				echo '</span>';
				if($item->isImage()) {
					$imageLink = $item->getUrl();
					$image = CHtml::image($imageLink, '', array('class'=>'media-hover-image'));
					if($item->description)
						$imageTooltips .= "$('#$id').qtip({content: '<span style=\"max-width: 200px;\">$image {$item->description}</span>', position: {my: 'top right', at: 'bottom left'}});\n";
					else
						$imageTooltips .= "$('#$id').qtip({content: '$image', position: {my: 'top right', at: 'bottom left'}});\n";
				} else if($item->description) {
    				$imageTooltips .= "$('#$id').qtip({content: '{$item->description}', position: {my: 'top right', at: 'bottom left'}});\n";
    			}
			} ?>
			<br>
			<br>
		</div>

		<?php $users = Profile::model()->findAll(array( // get all media that belongs to some user
		    'select'=>'fullName, username',
		    'condition'=>'username!=:username',
		    'params'=>array(':username'=>Yii::app()->user->name)
		));
		$admin = Yii::app()->params->profile->username == 'admin';
		 ?>

		<?php foreach($users as $user) { ?>
    		<?php $userMediaItems = Media::model()->findAllByAttributes(array('uploadedBy'=>$user->username)); ?>
				<?php if($userMediaItems) { // user has any media items? ?>
    				<?php $toggleUserMediaVisibleUrl = Yii::app()->controller->createUrl('/media/toggleUserMediaVisible') ."?user={$user->username}"; ?>
    				<?php $visible = !in_array($user->username, $hideUsers); ?>
    				<?php if(!$visible) $minimizeUserMedia .= "$('#{$user->username}-media').hide();\n"; ?>
    				<?php $minimizeLink = CHtml::ajaxLink($visible? '[&ndash;]' : '[+]', $toggleUserMediaVisibleUrl, array('success'=>"function(response) { toggleUserMedia($('#{$user->username}-media'), $('#{$user->username}-media-showhide'), response); }", 'type'=>'GET'), array('id'=>"{$user->username}-media-showhide", 'class'=>'media-library-showhide')); // javascript function togglePortletVisible defined in js/layout.js ?>
    				<strong><?php echo $user->fullName; ?></strong>
    				<?php echo $minimizeLink; ?><br>
    				<div id="<?php echo $user->username; ?>-media" class="user-media-list">
    					<?php foreach($userMediaItems as $item) {
    						if(!$item->private || $admin) {
    							$id = "{$user->username}-media-id-{$item->id}";
    							echo '<span class="media-item">';
    							echo CHtml::link($item->fileName, array('/media', 'view'=>$item->id), array('class'=>'x2-link media media-library-item', 'id'=>$id));
    							echo '</span>';
    							if($item->isImage()) {
    								$imageLink = $item->getUrl();
    								$image = CHtml::image($imageLink, '', array('class'=>'media-hover-image'));
    								if($item->description)
    									$imageTooltips .= "$('#$id').qtip({content: '<span style=\"max-width: 200px;\">$image {$item->description}</span>', position: {my: 'top right', at: 'bottom left'}});\n";
    								else
    									$imageTooltips .= "$('#$id').qtip({content: '$image', position: {my: 'top right', at: 'bottom left'}});\n";
    							} else if($item->description) {
    								$imageTooltips .= "$('#$id').qtip({content: '{$item->description}', position: {my: 'top right', at: 'bottom left'}});\n";
    							}
    						}
    					} ?>
    					<br>
    					<br>
    				</div>
			<?php } ?>
		<?php } ?>
</div>
</div>

<?php
$saveWidgetHeight = $this->controller->createUrl('/site/saveWidgetHeight');

Yii::app()->clientScript->registerScript('media-tooltips', "
$(function() {
    ". $imageTooltips ."
    ". $minimizeUserMedia ."
    $('#media-library-widget-wrapper').resizable({
    	handles: 's',
    	minHeight: 100,
    	stop: function(event, ui) {
    		// done resizing, save height to user profile for next time user visits page
    		$.post('$saveWidgetHeight', {Widget: 'MediaBox', Height: {mediaBoxHeight: parseInt($('#media-library-widget-container').css('height'))} });
    	}
    });
});",CClientScript::POS_HEAD);
?>
