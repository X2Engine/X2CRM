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




Yii::app()->clientScript->registerScriptFile(
    Yii::app()->getBaseUrl().'/js/activityFeed.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->getBaseUrl().'/js/EnlargeableImage.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->getBaseUrl().'/js/jquery-expander/jquery.expander.js', CClientScript::POS_END);

// used for rich editing in new post text field
Yii::app()->clientScript->registerPackage ('emailEditor');


Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/lib/moment-with-locales.min.js');


$groups = Groups::getUserGroups(Yii::app()->user->getId());
$tempUserList = array();
foreach($groups as $groupId){
    $userLinks = GroupToUser::model()->findAllByAttributes(array('groupId'=>$groupId));
    foreach($userLinks as $link){
        $user = User::model()->findByPk($link->userId);
        if(isset($user)){
            $tempUserList[] = $user->username;
        }
    }
}

$userList = array_keys(User::getNames());
$tempUserList = array_diff($userList,$tempUserList);
$usersGroups = implode(",",$tempUserList);

Yii::app()->clientScript->registerScript('setUpActivityFeedManager', "

x2.activityFeed = new x2.ActivityFeed ({
    translations: ".CJSON::encode (array (
        'Unselect All' => Yii::t('app','Unselect All'),
        'Select All' => Yii::t('app','Select All'),
        'Uncheck All' => Yii::t('app','Uncheck All'),
        'Check All' => Yii::t('app','Check All'),
        'Enter text here...' => Yii::t('app','Enter text here...'),
        'Broadcast Event' => Yii::t('app','Broadcast Event'),
        'Make Important' => Yii::t('app','Make Important'),
        'Broadcast' => Yii::t('app','Broadcast'),
        'broadcast error message 1' => Yii::t('app','Select at least one user to broadcast to'),
        'broadcast error message 2' => Yii::t('app','Select at least one broadcast method'),
        'Okay' => Yii::t('app','Okay'),
        'Nevermind' => Yii::t('app','Cancel'),
        'Create' => Yii::t('app','Create'),
        'Cancel' => Yii::t('app','Cancel'),
        'Read more' => Yii::t('app','Read') . '&nbsp;' . Yii::t('app', 'More'),
        'Read less' => Yii::t('app','Read') . '&nbsp;' . Yii::t('app', 'Less'),
    )).",
    usersGroups: '".$usersGroups."',
    minimizeFeed: ".(Yii::app()->params->profile->minimizeFeed==1?'true':'false').",
    commentFlag: false,
    lastEventId: ".(!empty($lastEventId)?$lastEventId:0).",
    lastTimestamp: ".(!empty($lastTimestamp)?$lastTimestamp:0).",
    profileId: ".$profileId.",
    myProfileId: ".Yii::app()->params->profile->id.",
    deletePostUrl: '".$this->createUrl('/profile/deletePost')."'
});

", CClientScript::POS_END);
?>

<div id='activity-feed-container' class='x2-layout-island'>
<div id='page-title-container'>
    <div class="page-title icon rounded-top activity-feed x2Activity">
        <h2 style="padding-left: 5px;"><?php echo Yii::t('app','Activity Feed'); ?></h2>
        <span title='<?php echo Yii::t('app', 'Feed Settings'); ?>'>
        <?php
        echo X2Html::settingsButton (Yii::t('app', 'Feed Settings'), 
            array ('id' => 'activity-feed-settings-button'));
        ?>
        </span>
        <a href='#' id='feed-filters-button' 
         class='filter-button right'>
            <span class='fa fa-filter'></span>
        </a>
        <div id="menu-links" class="title-bar" style='display: none;'>
            <?php
            echo CHtml::link(
                Yii::t('app','Toggle Comments'),'#',
                array('id'=>'toggle-all-comments','class'=>'x2-button x2-minimal-button right'));
            echo CHtml::link(
                Yii::t('app','Restore Posts'),'#',
                array('id'=>'restore-posts','style'=>'display:none;',
                    'class'=>'x2-button x2-minimal-button right'));
            echo CHtml::link(
                Yii::t('app','Minimize Posts'),
                '#',array('id'=>'min-posts','class'=>'x2-button x2-minimal-button right'));
            ?>
        </div>
    </div>
</div>

<?php
$this->renderPartial ('_feedFilters');
?>

<div class="form" id="post-form" style="clear:both">
    <?php $feed=new Events; ?>
    <?php $form = $this->beginWidget('CActiveForm', array(
    'id'=>'feed-form',
    'enableAjaxValidation'=>false,
    'method'=>'post',
    )); ?>
    <div class="float-row" style='overflow:visible;'>
        <?php
        echo $form->textArea($feed,'text',array('style'=>'width:99%;height:25px;color:#aaa;display:block;clear:both;'));
        echo "<div id='post-buttons' style='display:none;'>";
        echo $form->dropDownList($feed,'associationId',$users, 
            array (
                'style' => ($isMyProfile ? '' : 'display:none;'),
                'class' => 'x2-select'
            )
        );
        $feed->visibility=1;
        echo $form->dropDownList($feed,'visibility',
            array(1=>Yii::t('actions','Public'), 0=>Yii::t('actions','Private')),
            array ('class' => 'x2-select')
        );
        function translateOptions($item){
            return Yii::t('app',$item);
        }
        echo $form->dropDownList($feed,'subtype',
            array_map(
                'translateOptions',
                Dropdowns::getSocialSubtypes ()),
            array ('class' => 'x2-select'));
?>
        <div class="x2-button" title="<?php echo Yii::t('profile', 'Insert Link to Record'); ?>" onclick='$("#feed_link_record").slideToggle()'><?php echo X2Html::fa('chain'); ?></div>
        <div class="row" id="feed_link_record" style="display:none">
            <div class="cell"><?php
                // Model association autocomplete
                echo $form->hiddenField($feed, 'recordLinks');
                $modelList = Fields::getDisplayedModelNamesList();
                echo $form->label($feed, 'associationType'); 
                echo $form->dropDownList(
                    $feed, 'associationType', 
                    array_merge(array('none' => Yii::t('app','None')), $modelList), 
                    array(
                        'ajax' => array(
                            'type' => 'POST',
                            'url' => CController::createUrl('/actions/actions/parseType'), 
                            'update' => '#', //selector to update
                            'success' => 'function(data){
                                if(data){
                                    $("#feed_auto_select").autocomplete("option","source",data);
                                    $("#feed_auto_select").val("");
                                    $("#feed_auto_complete").show();
                                }else{
                                    $("#feed_auto_complete").hide();
                                }
                            }'
                        )
                    )
                );
                echo $form->error($feed, 'associationType');
                ?>
            </div>
            <div class="cell" id="feed_auto_complete" 
             style="display:none;">
                <?php
                echo $form->label($feed, 'associationName');
                $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                    'name' => 'feed_auto_select',
                    'value' => '',
                    'source' => '',
                    'options' => array(
                        'minLength' => '2',
                        'select' => 'js:function( event, ui ) {
                            var recordType = $("#Events_associationType").val();
                            $.ajax({
                                url: "'.$this->createUrl('/actions/actions/getAutocompleteAssocLink').'",
                                method: "POST",
                                data: {
                                    type: recordType,
                                    id: ui.item.id,
                                    YII_CSRF_TOKEN: x2.csrfToken
                                },
                                complete: function(data) {
                                    data = JSON.parse(data.responseText);
                                    $("#feed_record_links").show().append("<br />" + data[2]);
                                    var recordLinksVal = $("#Events_recordLinks").val();
                                    var currentLinks = false;
                                    if (recordLinksVal)
                                        currentLinks = JSON.parse(recordLinksVal);
                                    if (!currentLinks) currentLinks = [];
                                    currentLinks.push([data[0], data[1]]);
                                    $("#Events_recordLinks").val(JSON.stringify(currentLinks));
                                    $("#Events_associationType").val("none");
                                    $("#feed_link_record").slideUp();
                                    $("#feed_auto_complete").hide();
                                    $("#feed_auto_select").val("");
                                }
                            });
                            return false;
                        }',
                    ),
                ));
                ?>
            </div>
            <div class="cell" id="feed_record_links" style="display:none;"></div>
        </div>

        <div id='second-row-buttons-container'>
            <?php
            echo CHtml::hiddenField('geoCoords', '');
            echo CHtml::submitButton(
                Yii::t('app','Post'),array('class'=>'x2-button','id'=>'save-button'));

            if ($isMyProfile) {
                echo CHtml::button(
                    Yii::t('app','Attach A File/Photo'),
                    array(
                        'class'=>'x2-button',
                        'onclick'=>"x2.FileUploader.toggle('activity')",
                        'id'=>"toggle-attachment-menu-button"));
            } ?>

            <button id="toggle-location-button" class="x2-button" title="<?php echo Yii::t('app', 'Location Check-In'); ?>" style="display:inline-block; margin-left:10px"><?php
                echo X2Html::fa('crosshairs fa-lg');
            ?></button>
            <button id="toggle-location-comment-button" class="x2-button" title="<?php echo Yii::t('app', 'Add a comment on your location '); ?>" style="display:inline-block"><?php
                echo X2Html::fa('stack', array(),
                    X2Html::fa('comment-o fa-stack-2x').
                    X2Html::fa('crosshairs fa-stack-1x')
                );
            ?></button>
            <?php
            $checkInPlaceholder = Yii::t('app', 'Check-in comment.');
            if (!isset($_SERVER['HTTPS']) ?  : '')
                $checkInPlaceholder .= Yii::t('app', ' Note: for higher accuracy and an embedded static map, visit the site under HTTPS.');
            ?>
            <textarea id="checkInComment" rows=2 placeholder="<?php echo $checkInPlaceholder; ?>"></textarea>
        </div>
        </div>
        <?php
            Yii::app()->clientScript->registerGeolocationScript(true);
            $checkinByDefault = Yii::app()->settings->checkinByDefault;
            Yii::app()->clientScript->registerCheckinScript("#feed-form input[type=\'submit\'", $checkinByDefault);
        ?>
    </div>
    <?php $this->endWidget(); ?>
</div>
<?php
if ($isMyProfile) {
    $this->widget ('FileUploader',array(
        'id' => 'activity',
        'url' => '/site/upload',
        'mediaParams' => array(
            'profileId' => $profileId, 
            'associationType' => 'feed',
            'associationId' => Yii::app()->user->getId(),
        ),
        'viewParams' => array (
            'showButton' => false
        )
    ));
}
$this->widget('zii.widgets.CListView', array(
    'dataProvider'=>$stickyDataProvider,
    'itemView'=>'_viewEvent',
    'viewData' => array (
        'profileId' => $profileId
    ),
    'id'=>'sticky-feed',
    'htmlOptions' => array (
        'style' => $stickyDataProvider->itemCount === 0 ? 'display: none;' : '',
    ),
    'pager' => array(
        'class' => 'ext.infiniteScroll.IasPager',
        'rowSelector'=>'.view.top-level',
        'listViewId' => 'sticky-feed',
        'header' => '',
        'options'=>array(
            'onRenderComplete'=>'js:function(){
                x2.activityFeed.makePostsExpandable ();
                if(x2.activityFeed.minimizeFeed){
                    x2.activityFeed.minimizePosts();
                }
                if(x2.activityFeed.commentFlag){
                    $(".comment-link").click();
                }
            }'
        ),
    ),
    'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/listview',
    'template'=>'{pager} {items}'
));
$this->widget('zii.widgets.CListView', array(
    'dataProvider'=>$dataProvider,
    'itemView'=>'_viewEvent',
    'viewData' => array (
        'profileId' => $profileId
    ),
    'id'=>'activity-feed',
    'pager' => array(
        'class' => 'ext.infiniteScroll.IasPager',
        'rowSelector'=>'.view.top-level',
        'listViewId' => 'activity-feed',
        'header' => '',
        'options'=>array(
            'onRenderComplete'=>'js:function(){
                x2.activityFeed.makePostsExpandable ();
                if(x2.activityFeed.minimizeFeed){
                    x2.activityFeed.minimizePosts();
                }
                if(x2.activityFeed.commentFlag){
                    $(".comment-link").click();
                }
                $.each($(".comment-count"),function(){
                    if($(this).attr("val")>0){
                        $(this).parent().click();
                    }
                });
            }'
        ),
    ),
    'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/listview',
    'template'=>'{pager} {items}',
));

?>
<div id="make-important-dialog" style="display: none;">
    <div class='dialog-explanation'>
        <?php echo Yii::t('app','Leave colors blank for defaults.');?>
    </div>
    <div>
        <?php
            echo CHtml::label(Yii::t('app','What color should the event be?'),'broadcastColor');
        ?>
        <div class='row'>
            <?php echo CHtml::textField('broadcastColor',''); ?>
        </div>
    </div>
    <div>
        <?php echo CHtml::label(Yii::t('app','What color should the font be?'),'fontColor'); ?>
            <div class='row'>
        <?php echo CHtml::textField('fontColor',''); ?>
        </div>
    </div>
    <div>
        <?php echo CHtml::label(Yii::t('app','What color should the links be?'),'linkColor'); ?>
        <div class='row'>
            <?php echo CHtml::textField('linkColor',''); ?>
        </div>
    </div>
</div>
<div id="broadcast-dialog" style='display: none;'>
    <div class='dialog-explanation'>
        <?php echo Yii::t('app', 'Select a group of users to send this event to via email or notification.'); ?>
    </div>
    <select id='broadcast-dialog-user-select' class='multiselect' multiple='multiple' size='6'>
        <?php foreach ($userModels as $user) { ?>
        <option value="<?php echo $user->id; ?>"> <?php echo $user->firstName . ' ' . $user->lastName; ?> </option>
        <?php } ?>
    </select>
    <div>
        <?php echo CHtml::label(Yii::t('app','Do you want to email selected users?'),'email-users'); ?>
        <?php echo CHtml::checkBox('email-users'); ?>
    </div>
    <div id='notify-users-checkbox-container'>
        <?php echo CHtml::label(Yii::t('app','Do you want to notify selected users?'),'notify-users'); ?>
        <?php echo CHtml::checkBox('notify-users'); ?>
    </div>
</div>
</div>
