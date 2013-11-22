<?php
/*********************************************************************************
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
 ********************************************************************************/

$users = User::getUserIds();

$menuItems = array(
    array('label' => Yii::t('app', 'Main Menu'), 'url' => array('/mobile/site/home')),
);

$this->widget('MenuList', array(
    'id' => 'main-menu',
    'items' => $menuItems
));

?>
<form id='feed-post-publisher'>
    <!--<input type='text' name='name' id='feed-post-editor' 
     placeholder='<?php echo Yii::t('app', 'Enter text here...'); ?>' value='' />-->
    <textarea type='text' name='name' id='feed-post-editor' 
     placeholder='<?php echo Yii::t('app', 'Enter text here...'); ?>'></textarea>
    <fieldset data-role='controlgroup' data-type='horizontal'>
<?php
    $userIds = array_keys ($users);
    $firstUser = $userIds[0];
    echo CHtml::dropDownList('associationId',$firstUser,$users, 
        array ('data-mini'=>'true', 'id'=>'feed-post-association-id'));
    echo CHtml::dropDownList(
        'visibility',1,array(1=>Yii::t('actions','Public'),0=>Yii::t('actions','Private')),
        array ('data-mini'=>'true', 'id'=>'feed-post-visibility'));
    echo CHtml::dropDownList(
        'subtype',1,
        array_map(
            function ($item) { return Yii::t('app', $item); },
            json_decode(Dropdowns::model()->findByPk(113)->options,true)
        ),
        array ('data-mini'=>'true', 'id'=>'feed-post-subtype')
    );
?>
    </fieldset>
    <button type='submit' class='x2-button' id='feed-post-button' 
     data-inline='true'><?php echo Yii::t('app', 'Submit Post'); ?></button>
</form>
<div id="feed-box"></div>
<script>

    /*
    Initializes notifications or, if it has already been initialized, retrieves previously received
    notifications
    */
    function setUpNotifsOrPopulateFeed () {
        if (!x2.notifications) { // init notifs
            x2.notifications = new x2.Notifs ({ 
                isMobile: true,
                translations: {
                    clearAll: '<?php echo addslashes (
                        Yii::t('app', 'Permanently delete all notifications?')); ?>'
                }
            });
        } else { // get old notifs
            $('#feed-box').children ().remove ();
    
            // initialize feed list with old feed messages
            x2.notifications.addFeedMessages (x2.notifications.getCachedFeedMessages (), true);
        }
    }

    /*
    Set up behaviour related to the publisher.
    */
    function setUpPublisherBehaviour () {

        // post text to activity feed
        $('#feed-post-button').on ('click', function () {

            $.ajax({
                url:"<?php echo Yii::app()->request->getScriptUrl () . '/site/publishPost'; ?>",
                type:"POST",
                data:{
                    "text":$("#feed-post-editor").val(),
                    "associationId":$("#feed-post-association-id").val(),
                    "visibility":$("#feed-post-visibility").val(),
                    "subtype":$("#feed-post-subtype").val()
                },
                success:function(){
                    $('#feed-post-editor').val ('');
                    $('#feed-post-editor').blur ();

                    // remove focus styling from submit button
                    $('#feed-post-button').parent ().removeClass ('ui-btn-active')
                    $('#feed-post-button').parent ().removeClass ('ui-focus')
                }
            });

            return false;
        });

    }

    (function activityMain () {
        setUpNotifsOrPopulateFeed ();
        setUpPublisherBehaviour ();

        // links disabled until mobile versions of linked pages are added 
        $(document).on ('click', '#feed-box a', 
            function (evt) { evt.preventDefault (); return false });
    }) ();

</script>
