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

$justMeUrl = $this->controller->createUrl('/site/toggleShowTags', array('tags'=>'justMe'));
$allUsersUrl = $this->controller->createUrl('/site/toggleShowTags', array('tags'=>'allUsers'));?><span style="float:left"><?php
echo CHtml::ajaxLink(Yii::t('app','Just Me'), $justMeUrl,array('success'=>'function(response) { $("#myTags").show(); $("#allTags").hide(); } '))." | ".CHtml::ajaxLink(Yii::t('app','All Users'), $allUsersUrl,array('success'=>'function() { $("#allTags").show(); $("#myTags").hide(); }'))."<br />";
?></span><span style="float:right"><span id="tag-hint" style="color:#06c">[?]</span></span> <br><br>
<div id="myTags" <?php echo ($showAllUsers? 'style="display:none;"' : ''); ?>>
<?php
foreach($myTags as &$tag) {
	echo 
        '<span style="position:relative;" class="tag hide" tag-name="'.substr($tag['tag'],1).'">'.
            CHtml::link(
                CHtml::encode ($tag['tag']),
                array(
                    '/search/search','term'=>'#'.ltrim($tag['tag'],'#')
                ),
                array('class'=>'x2-link x2-tag')
            ).
        '</span>';
}
?>
</div>

<div id="allTags"  <?php echo ($showAllUsers? '' : 'style="display:none;"'); ?>>
<?php
foreach($allTags as &$tag) {
	echo 
        '<span style="position:relative;" class="tag hide" tag-name="'.substr($tag['tag'],1).'">'.
            CHtml::link(
                CHtml::encode ($tag['tag']),
                array(
                    '/search/search',
                    'term'=>'#'.ltrim($tag['tag'],'#'),
                ),
                array('class'=>'x2-link x2-tag')
            ).
        '</span>';
}
?>
</div>
<script>
    $('.hide').mouseenter(function(e){
        e.preventDefault();
        var tag=$(this).attr('tag-name');
        var elem=$(this);
        var content='<span style="position:absolute;right:1px;top:1px;;background-color:#F0F0F0;" class="hide-link-span"><a href="#" class="hide-link" style="color:#06C;">[x]</a></span>';
        $(content).hide().delay(1500).appendTo($(this)).fadeIn(500);
        $('.hide-link').click(function(e){
           e.preventDefault();
           $.ajax({
              url:'<?php echo CHtml::normalizeUrl(array('/profile/hideTag')); ?>'+'?tag='+tag,
              success:function(){
                  $(elem).closest('.tag').fadeOut(500);
              }
           });
        });
    }).mouseleave(function(){
        $('.hide-link-span').remove();
    });
    $('#tag-hint').qtip({
       position:{'my':'top right','at':'bottom left'},
       content:'<?php echo Yii::t('app','Pressing the X button on a tag will hide it from this widget. Hidden tags can be restored from your Preferences page.'); ?>'
    });
</script>
