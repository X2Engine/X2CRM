<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

$users = User::getUserIds();

$menuItems = array(
    array('label' => Yii::t('app', 'Main Menu'), 'url' => array('/mobile/home')),
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
    <fieldset data-role='controlgroup' data-type='horizontal' data-shadow="true">
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
            Dropdowns::getSocialSubtypes ()
        ),
        array ('data-mini'=>'true', 'id'=>'feed-post-subtype')
    );
?>
    </fieldset>
    <button type='submit' class='x2-button' id='feed-post-button' 
     data-inline='true'><?php echo Yii::t('app', 'Submit Post'); ?></button>
     <?php echo X2Html::csrfToken(); ?>
</form>
<div id="attachments" style='display: none;'>
<?php
$this->widget (
    'Attachments',
    array(
        'associationType'=>'feed',
        'associationId'=>Yii::app()->user->getId(),
        'mobile' => true,
    )
);
?>
</div>
<div id="feed-box"></div>
<script type='text/javascript'>

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

            // if a photo is uploaded, submit it (requires page refresh)
            if (x2.attachments.fileIsUploaded ()) {
                $.mobile['ajaxEnabled'] = false; 
                $('#submitAttach').click ();            
                return false;
            }

            $.ajax({
                url:"<?php echo Yii::app()->request->getScriptUrl () . '/profile/publishPost'; ?>",
                type:"POST",
                data:{
                    "text":$("#feed-post-editor").val(),
                    "associationId":$("#feed-post-association-id").val(),
                    "visibility":$("#feed-post-visibility").val(),
                    "subtype":$("#feed-post-subtype").val(),
                    "YII_CSRF_TOKEN": '<?php echo Yii::app()->request->csrfToken ?>'
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
