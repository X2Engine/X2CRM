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

<div id="media-library-widget-wrapper" style="width:99%">
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

		<?php
			$myMediaItems = Yii::app()->db->createCommand()
				->select('id, uploadedBy, fileName, description')
				->where('uploadedBy=:username', array(':username'=>$username))
				->from('x2_media')
				->queryAll();
		?>
		<?php //$myMediaItems = Media::model()->findAllByAttributes(array('uploadedBy'=>$username)); // get current user's media ?>


		<div id="<?php echo $username; ?>-media" class="user-media-list">
			<?php foreach($myMediaItems as $item) {
				$id = "$username-media-id-{$item['id']}";
				echo '<span class="media-item">';
				$path = Media::getFilePath($item['uploadedBy'], $item['fileName']);
				$filename = $item['fileName'];
				if(mb_strlen($filename,'UTF-8') > 35) {
					$filename = mb_substr($filename, 0, 32,'UTF-8') . '…';
				}
				echo CHtml::link($filename, array('/media', 'view'=>$item['id']),array(
					'class'=>'x2-link media'.(Media::isImageExt($item['fileName'])? ' image-file' : ''),
					'id'=>$id,
					'style'=>'curosr:pointer;',
					'data-url'=>Media::getFullFileUrl($path),
				));
				echo '</span>';

				if(Media::isImageExt($item['fileName'])) {
					$imageLink = Media::getFileUrl($path);
					$image = CHtml::image($imageLink, '', array('class'=>'media-hover-image'));
					if($item['description'])
						$imageTooltips .= "$('#$id').qtip({content: '<span style=\"max-width: 200px;\">$image {$item['description']}</span>', position: {my: 'top right', at: 'bottom left'}});\n";
					else
						$imageTooltips .= "$('#$id').qtip({content: '$image', position: {my: 'top right', at: 'bottom left'}});\n";
				} else if($item['description']) {
    				$imageTooltips .= "$('#$id').qtip({content: '{$item['description']}', position: {my: 'top right', at: 'bottom left'}});\n";
    			}
			} ?>
			<br>
			<br>
		</div>

		<?php $users = Yii::app()->db->createCommand()
				->select('fullName, username')
				->where('username!=:username', array(':username'=>Yii::app()->user->name))
				->from('x2_profile')
				->queryAll();

		$admin = Yii::app()->params->isAdmin;
		 ?>

		<?php foreach($users as $user) { ?>
    		<?php //$userMediaItems = X2Model::model('Media')->findAllByAttributes(array('uploadedBy'=>$user->username)); ?>
    		<?php $userMediaItems = Yii::app()->db->createCommand()
				->select('id, uploadedBy, fileName, description, private')
				->where('uploadedBy=:username', array(':username'=>$user['username']))
				->from('x2_media')
				->queryAll();
			?>
				<?php if($userMediaItems) { // user has any media items? ?>
    				<?php $toggleUserMediaVisibleUrl = Yii::app()->controller->createUrl('/media/toggleUserMediaVisible') ."?user={$user['username']}"; ?>
    				<?php $visible = !in_array($user['username'], $hideUsers); ?>
    				<?php if(!$visible) $minimizeUserMedia .= "$('#{$user['username']}-media').hide();\n"; ?>
    				<?php $minimizeLink = CHtml::ajaxLink($visible? '[&ndash;]' : '[+]', $toggleUserMediaVisibleUrl, array('success'=>"function(response) { toggleUserMedia($('#{$user['username']}-media'), $('#{$user['username']}-media-showhide'), response); }", 'type'=>'GET'), array('id'=>"{$user['username']}-media-showhide", 'class'=>'media-library-showhide')); // javascript function togglePortletVisible defined in js/layout.js ?>
    				<strong><?php echo $user['fullName']; ?></strong>
    				<?php echo $minimizeLink; ?><br>
    				<div id="<?php echo $user['username']; ?>-media" class="user-media-list">
    					<?php foreach($userMediaItems as $item) {
    						if(!$item['private'] || $admin) {
    							$id = "{$user['username']}-media-id-{$item['id']}";
    							echo '<span class="media-item">';
    							$path = Media::getFilePath($item['uploadedBy'], $item['fileName']);
   								$filename = $item['fileName'];
								if(mb_strlen($filename,'UTF-8') > 35) {
									$filename = mb_substr($filename, 0, 32,'UTF-8') . '…';
								}
    							echo CHtml::link($filename, array('/media', 'view'=>$item['id']), array(
									'class'=>'x2-link media media-library-item'.(Media::isImageExt($item['fileName'])? ' image-file' : ''),
									'id'=>$id,
									'data-url'=>Media::getFullFileUrl($path),
								));
    							echo '</span>';
    							if(Media::isImageExt($item['fileName'])) {
    								$imageLink = Media::getFileUrl($path);
    								$image = CHtml::image($imageLink, '', array('class'=>'media-hover-image'));
    								if($item['description'])
    									$imageTooltips .= "$('#$id').qtip({content: '<span style=\"max-width: 200px;\">$image {$item['description']}</span>', position: {my: 'top right', at: 'bottom left'}});\n";
    								else
    									$imageTooltips .= "$('#$id').qtip({content: '$image', position: {my: 'top right', at: 'bottom left'}});\n";
    							} else if($item['description']) {
    								$imageTooltips .= "$('#$id').qtip({content: '{$item['description']}', position: {my: 'top right', at: 'bottom left'}});\n";
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
