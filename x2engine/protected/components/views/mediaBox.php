<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/



?>

<?php
$widgetSettings = Profile::getWidgetSettings();
$mediaSettings = $widgetSettings->MediaBox;
$mediaBoxHeight = $mediaSettings->mediaBoxHeight;
$hideUsers = $mediaSettings->hideUsers;
$imageTooltips = '';
$minimizeUserMedia = '';
$username = Yii::app()->params->profile->username;
$fullname = Yii::app()->params->profile->fullName;
$escapedName = preg_replace('/[@\.]/', '', $username);
?>

<div id="media-library-widget-wrapper" style="width:99%">
    <div id="media-library-widget-container">
        <?php
        $this->widget('FileUploader', array(
            'id' => 'docViewer',
            'viewParams' => array(
                'noPadding' => true
            ),
            'events' => array(
                'success' => '$("#drive-refresh").click()'
            )
        ));
        ?>
        <?php
        echo "<div id='x2-media-list' style='" . ($this->drive ? 'display:none;' : '') . "'>";
        $toggleUserMediaVisibleUrl = $this->controller->createUrl('/media/media/toggleUserMediaVisible', array('user' => $username));
        $visible = !in_array($username, $hideUsers);
        if (!$visible)
            $minimizeUserMedia .= "$('#" . $escapedName . "-media').hide();\n";
        $minimizeLink = CHtml::ajaxLink(
                        X2Html::fa($visible ? 'fa-caret-down' : 'fa-caret-left'), $toggleUserMediaVisibleUrl, array(
                    'success' => "function(response) { 
                    toggleUserMedia(
                        $('#$escapedName-media'), 
                        $('#$escapedName-media-showhide'), response); 
                }",
                    'type' => 'GET'
                        ), array(
                    'id' => $escapedName . "-media-showhide",
                    'class' => 'media-library-showhide gray-link'
        )); // javascript function toggleUserMedia defined in js/media.js
        ?>
        <strong><?php echo $fullname; ?></strong>
        <?php echo $minimizeLink; ?><br>
        <?php
        $myMediaItems = Yii::app()->db->createCommand()
                ->select('id, uploadedBy, fileName, description, drive, name')
                ->where(
                        'uploadedBy=:username AND drive=:drive AND associationType IN("none","docs")', array(':username' => $username, ':drive' => $this->drive))
                ->from('x2_media')
                ->queryAll();
        ?>
        <?php //$myMediaItems = Media::model()->findAllByAttributes(array('uploadedBy'=>$username)); // get current user's media  ?>
        <div id="<?php echo $escapedName; ?>-media" class="user-media-list">
            <?php
            foreach ($myMediaItems as $item) {
                $baseId = str_replace('@', '', $username) . "-media-id-{$item['id']}";
                $jsSelectorId = CJSON::encode("#$baseId");
                $propertyId = addslashes($baseId);
                $desc = CHtml::encode($item['description']);
                echo '<span class="x2-pillbox media-item">';
                $path = Media::getFilePath($item['uploadedBy'], $item['fileName']);
                $filename = $item['drive'] ? $item['name'] : $item['fileName'];
                if (mb_strlen($filename, 'UTF-8') > 35) {
                    $filename = mb_substr($filename, 0, 32, 'UTF-8') . '…';
                }
                echo CHtml::link(
                        $filename, array('/media/media/view', 'id' => $item['id']), array(
                    'class' => 'x2-link media' . (Media::isImageExt($item['fileName']) ?
                            ' image-file' : ''),
                    'id' => $baseId,
                    'style' => 'cursor:pointer;',
                    'data-url' => Media::model()->findByPk($item['id'])->getPublicUrl(),
                ));
                echo '</span>';

                if (Media::isImageExt($item['fileName'])) {
                    $imageLink = Yii::app()->controller->createUrl('/media/media/getFile', array('id' => $item['id']));
                    $image = CHtml::image($imageLink, '', array('class' => 'media-hover-image'));
                    $imageStr = CJSON::encode($image);

                    if ($item['description']) {
                        $content = CJSON::encode("<span style=\"max-width: 200px;\">$image $desc</span>");
                        $imageTooltips .= "$($jsSelectorId).qtip({content: $content, position: {my: 'top right', at: 'bottom left'}});\n";
                    } else
                        $imageTooltips .= "$($jsSelectorId).qtip({content: $imageStr, position: {my: 'top right', at: 'bottom left'}});\n";
                } else if ($item['description']) {
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

        foreach ($users as $user) {
            //$userMediaItems = X2Model::model('Media')->findAllByAttributes(array('uploadedBy'=>$user->username)); 
            $userMediaItems = Yii::app()->db->createCommand()
                    ->select('id, uploadedBy, fileName, description, private, drive, name')
                    ->where('uploadedBy=:username AND associationType="none"', array(':username' => $user['username']))
                    ->from('x2_media')
                    ->queryAll();
            if ($userMediaItems) { // user has any media items? 
                $toggleUserMediaVisibleUrl = Yii::app()->controller->createUrl('/media/media/toggleUserMediaVisible') . "?user={$user['username']}";
                $visible = !in_array($user['username'], $hideUsers);
                if (!$visible)
                    $minimizeUserMedia .= "$('#{$user['username']}-media').hide();\n";
                $minimizeLink = CHtml::ajaxLink(
                                X2Html::fa($visible ? 'fa-caret-down' : 'fa-caret-left'), $toggleUserMediaVisibleUrl, array(
                            'success' => "function(response) { 
                            toggleUserMedia(
                                $('#{$user['username']}-media'), 
                                $('#{$user['username']}-media-showhide'), 
                                response); 
                        }",
                            'type' => 'GET'
                                ), array(
                            'id' => "{$user['username']}-media-showhide",
                            'class' => 'media-library-showhide gray-link'
                )); // javascript function toggleUserMedia defined in js/media.js  
                ?>
                <strong><?php echo $user['fullName']; ?></strong>
                <?php echo $minimizeLink; ?><br>
                <div id="<?php echo $user['username']; ?>-media" class="user-media-list">
                    <?php
                    foreach ($userMediaItems as $item) {
                        if (!$item['private'] || $admin) {
                            $baseId = "{$user['username']}-media-id-{$item['id']}";
                            $jsSelectorId = CJSON::encode("#$baseId");
                            $propertyId = addslashes($baseId);
                            $desc = CHtml::encode($item['description']);
                            echo '<span class="x2-pillbox media-item">';
                            $path = Media::getFilePath($item['uploadedBy'], $item['fileName']);
                            $filename = $item['drive'] ? $item['name'] : $item['fileName'];
                            if (mb_strlen($filename, 'UTF-8') > 35) {
                                $filename = mb_substr($filename, 0, 32, 'UTF-8') . '…';
                            }
                            echo CHtml::link($filename, array('/media', 'view' => $item['id']), array(
                                'class' => 'x2-link media media-library-item' . (Media::isImageExt($item['fileName']) ? ' image-file' : ''),
                                'id' => $baseId,
                                'data-url' => Media::model()->findByPk($item['id'])->getPublicUrl(),
                            ));
                            echo '</span>';
                            if (Media::isImageExt($item['fileName'])) {
                                $imageLink = Media::getFileUrl($path);
                                $image = CHtml::image($imageLink, '', array('class' => 'media-hover-image'));
                                $imageStr = CJSON::encode($image);

                                if ($item['description']) {
                                    $content = CJSON::encode("<span style=\"max-width: 200px;\">$image $desc</span>");
                                    $imageTooltips .= "$($jsSelectorId).qtip({content: $content, position: {my: 'top right', at: 'bottom left'}});\n";
                                } else
                                    $imageTooltips .= "$($jsSelectorId).qtip({content: $imageStr, position: {my: 'top right', at: 'bottom left'}});\n";
                            } else if ($item['description']) {
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

        <?php echo "<div id='drive-table' style='" . (!$this->drive ? 'display:none;' : '') . "'>"; ?>
        <?php echo isset($_SESSION['driveFiles']) ? $_SESSION['driveFiles'] : ''; ?>
        <?php echo "</div>"; ?>

        <script>
            $(document).on('click', '.toggle-file-system', function (e) {
                e.preventDefault();
                var id = $(this).attr('data-id');
                if ($('#' + id).is(':hidden')) {
                    $.ajax({
                        'url': '<?php echo Yii::app()->controller->createUrl('/media/media/recursiveDriveFiles') ?>',
                        'data': {'folderId': id},
                        'success': function (data) {
                            $('#' + id).html(data);
                            $('#' + id).show();
                            $('a.drive-link').draggable({
                                revert: 'invalid',
                                helper: 'clone',
                                revertDuration: 200,
                                appendTo: 'body',
                                iframeFix: true
                            });
                        }
                    });
                } else {
                    $('#' + id).html('').hide();
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
    " . $imageTooltips . "
    " . $minimizeUserMedia . "
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
