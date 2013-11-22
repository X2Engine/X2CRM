<?php
/* * *******************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 * ****************************************************************************** */
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
$escapedName = preg_replace('/[@\.]/','',$username);
?>

<div id="media-library-widget-wrapper" style="width:99%">
    <div id="media-library-widget-container">
        <?php
        echo "<div id='x2-media-list' style='".($this->drive ? 'display:none;' : '')."'>";
        $toggleUserMediaVisibleUrl = $this->controller->createUrl('/media/media/toggleUserMediaVisible',array('user'=>$username));
        $visible = !in_array($username, $hideUsers);
        if(!$visible)
            $minimizeUserMedia .= "$('#".$escapedName."-media').hide();\n";
        $minimizeLink = CHtml::ajaxLink($visible ? '[&ndash;]' : '[+]', $toggleUserMediaVisibleUrl, array('success' => "function(response) { toggleUserMedia($(\"#".$escapedName."-media\"), $('#".$escapedName."-media-showhide'), response); }", 'type' => 'GET'), array('id' => $escapedName."-media-showhide", 'class' => 'media-library-showhide')); // javascript function toggleUserMedia defined in js/media.js
        ?>
        <strong><?php echo $fullname; ?></strong>
        <?php echo $minimizeLink; ?><br>

        <?php
        $myMediaItems = Yii::app()->db->createCommand()
                ->select('id, uploadedBy, fileName, description, drive, title')
                ->where('uploadedBy=:username AND drive=:drive AND associationType="none"', array(':username' => $username, ':drive' => $this->drive))
                ->from('x2_media')
                ->queryAll();
        ?>
        <?php //$myMediaItems = Media::model()->findAllByAttributes(array('uploadedBy'=>$username)); // get current user's media  ?>


        <div id="<?php echo $escapedName; ?>-media" class="user-media-list">
            <?php
            foreach($myMediaItems as $item){
				$baseId = str_replace('@','',$username)."-media-id-{$item['id']}";
                $jsSelectorId = CJSON::encode("#$baseId");
				$propertyId = addslashes($baseId);
				$desc = CHtml::encode($item['description']);
                echo '<span class="media-item">';
                $path = Media::getFilePath($item['uploadedBy'], $item['fileName']);
                $filename = $item['drive']?$item['title']:$item['fileName'];
                if(mb_strlen($filename, 'UTF-8') > 35){
                    $filename = mb_substr($filename, 0, 32, 'UTF-8').'…';
                }
                echo CHtml::link($filename, array('/media/media/view', 'id'=>$item['id']), array(
                    'class' => 'x2-link media'.(Media::isImageExt($item['fileName']) ? ' image-file' : ''),
                    'id' => $baseId,
                    'style' => 'curosr:pointer;',
                    'data-url' => Media::getFullFileUrl($path),
                ));
                echo '</span>';

                if(Media::isImageExt($item['fileName'])){
                    $imageLink = Media::getFileUrl($path);
                    $image = CHtml::image($imageLink, '', array('class' => 'media-hover-image'));
					$imageStr = CJSON::encode($image);

                    if($item['description']) {
						$content = CJSON::encode("<span style=\"max-width: 200px;\">$image $desc</span>");
                        $imageTooltips .= "$($jsSelectorId).qtip({content: $content, position: {my: 'top right', at: 'bottom left'}});\n";
					}else
                        $imageTooltips .= "$($jsSelectorId).qtip({content: $imageStr, position: {my: 'top right', at: 'bottom left'}});\n";
                } else if($item['description']){
					$content = CJSON::encode($item['description']);
                    $imageTooltips .= "$($jsSelectorId).qtip({content: $content, position: {my: 'top right', at: 'bottom left'}});\n";
                }
            }
            ?>
            <br>
            <br>
        </div>

        <?php
        $users = Yii::app()->db->createCommand()
                ->select('fullName, username')
                ->where('username!=:username', array(':username' => Yii::app()->user->name))
                ->from('x2_profile')
                ->queryAll();

        $admin = Yii::app()->params->isAdmin;
        ?>

        <?php foreach($users as $user){ ?>
            <?php //$userMediaItems = X2Model::model('Media')->findAllByAttributes(array('uploadedBy'=>$user->username)); ?>
            <?php
            $userMediaItems = Yii::app()->db->createCommand()
                    ->select('id, uploadedBy, fileName, description, private, drive, title')
                    ->where('uploadedBy=:username AND associationType="none"', array(':username' => $user['username']))
                    ->from('x2_media')
                    ->queryAll();
            ?>
            <?php if($userMediaItems){ // user has any media items? ?>
                <?php $toggleUserMediaVisibleUrl = Yii::app()->controller->createUrl('/media/media/toggleUserMediaVisible')."?user={$user['username']}"; ?>
                <?php $visible = !in_array($user['username'], $hideUsers); ?>
                <?php if(!$visible) $minimizeUserMedia .= "$('#{$user['username']}-media').hide();\n"; ?>
                <?php $minimizeLink = CHtml::ajaxLink($visible ? '[&ndash;]' : '[+]', $toggleUserMediaVisibleUrl, array('success' => "function(response) { toggleUserMedia($('#{$user['username']}-media'), $('#{$user['username']}-media-showhide'), response); }", 'type' => 'GET'), array('id' => "{$user['username']}-media-showhide", 'class' => 'media-library-showhide')); // javascript function toggleUserMedia defined in js/media.js  ?>
                <strong><?php echo $user['fullName']; ?></strong>
                <?php echo $minimizeLink; ?><br>
                <div id="<?php echo $user['username']; ?>-media" class="user-media-list">
                    <?php
                    foreach($userMediaItems as $item){
                        if(!$item['private'] || $admin){
							$baseId = "{$user['username']}-media-id-{$item['id']}";
				            $jsSelectorId = CJSON::encode("#$baseId");
							$propertyId = addslashes($baseId);
							$desc = CHtml::encode($item['description']);
                            echo '<span class="media-item">';
                            $path = Media::getFilePath($item['uploadedBy'], $item['fileName']);
                            $filename = $item['drive']?$item['title']:$item['fileName'];
                            if(mb_strlen($filename, 'UTF-8') > 35){
                                $filename = mb_substr($filename, 0, 32, 'UTF-8').'…';
                            }
                            echo CHtml::link($filename, array('/media', 'view' => $item['id']), array(
                                'class' => 'x2-link media media-library-item'.(Media::isImageExt($item['fileName']) ? ' image-file' : ''),
                                'id' => $baseId,
                                'data-url' => Media::getFullFileUrl($path),
                            ));
                            echo '</span>';
                            if(Media::isImageExt($item['fileName'])){
                                $imageLink = Media::getFileUrl($path);
                                $image = CHtml::image($imageLink, '', array('class' => 'media-hover-image'));
								$imageStr = CJSON::encode($image);

                                if($item['description']) {
									$content = CJSON::encode("<span style=\"max-width: 200px;\">$image $desc</span>");
                                    $imageTooltips .= "$($jsSelectorId).qtip({content: $content, position: {my: 'top right', at: 'bottom left'}});\n";
								}else
                                    $imageTooltips .= "$($jsSelectorId).qtip({content: $imageStr, position: {my: 'top right', at: 'bottom left'}});\n";
                            } else if($item['description']){
								$content = CJSON::encode($desc);
                                $imageTooltips .= "$($jsSelectorId).qtip({content: $content, position: {my: 'top right', at: 'bottom left'}});\n";
                            }
                        }
                    }
                    ?>
                    <br>
                    <br>
                </div>
            <?php } ?>
        <?php } ?>
        <?php echo "</div>"; ?>

        <?php echo "<div id='drive-table' style='".(!$this->drive ? 'display:none;' : '')."'>"; ?>
        <?php echo isset($_SESSION['driveFiles'])?$_SESSION['driveFiles']:''; ?>
        <?php echo "</div>"; ?>

        <script>
                $(document).on('click','.toggle-file-system',function(e){
                    e.preventDefault();
                    var id=$(this).attr('data-id');
                    if($('#'+id).is(':hidden')){
                        $.ajax({
                            'url':'<?php echo Yii::app()->controller->createUrl('/media/media/recursiveDriveFiles') ?>',
                            'data':{'folderId':id},
                            'success':function(data){
                                $('#'+id).html(data);
                                $('#'+id).show();
                                $('a.drive-link').draggable({revert: 'invalid', helper:'clone', revertDuration:200, appendTo:'body',iframeFix:true});
                            }
                        });
                    }else{
                        $('#'+id).html('').hide();
                    }
                });

        </script>
        <style>
            .drive-link{
                text-decoration:none;
                color:#222;
                font-weight:bold;
                display:block;
                margin-left:25px;
            }
            .drive-item{
                vertical-align:middle;
                display:block;
            }
            .drive-wrapper{
                border-bottom-style:solid;
                border-width:1px;
                border-color:#ccc;
                padding:5px;
                margin-left:-500px;
                padding-left:510px;
                vertical-align:middle;
            }
            .drive {
                padding-left:20px;
            }
            .drive-table{

            }
            .drive-icon{
                float:left;
            }
        </style>
    </div>
</div>

<?php
$saveWidgetHeight = $this->controller->createUrl('/site/saveWidgetHeight');

Yii::app()->clientScript->registerScript('media-tooltips', "
$(function() {
    ".$imageTooltips."
    ".$minimizeUserMedia."
    $('#media-library-widget-wrapper').resizable({
    	handles: 's',
    	minHeight: 100,
    	stop: function(event, ui) {
    		// done resizing, save height to user profile for next time user visits page
    		$.post('$saveWidgetHeight', {Widget: 'MediaBox', Height: {mediaBoxHeight: parseInt($('#media-library-widget-container').css('height'))} });
    	}
    });
        $('.drive-link').draggable({revert: 'invalid', helper:'clone', revertDuration:200,iframeFix:true});
});", CClientScript::POS_HEAD);


