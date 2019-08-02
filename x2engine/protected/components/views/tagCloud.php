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




$justMeUrl = $this->controller->createUrl('/site/toggleShowTags', array('tags'=>'justMe'));
$allUsersUrl = $this->controller->createUrl('/site/toggleShowTags', array('tags'=>'allUsers'));?><span style="float:left"><?php
echo CHtml::ajaxLink(
    Yii::t('app','Just Me'), 
    $justMeUrl,
    array(
        'success'=>'function(response) { 
            $("#myTags").show(); 
            $("#allTags").hide(); 
        } '
    ))." | ".CHtml::ajaxLink(Yii::t('app','All Users'), 
    $allUsersUrl,
    array(
        'success'=>'function() { 
            $("#allTags").show(); 
            $("#myTags").hide(); 
        }'))."<br />";
?></span>
<span style="float:right">
    <?php
    echo X2Html::hint (
        Yii::t(
            'app','Pressing the X button on a tag will hide it from this widget. Hidden tags can '.
            'be restored from your Preferences page.')); 
    ?>
</span> <br><br>
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
        '<span style="position:relative;" class="tag hide" tag-name="'.
            substr(CHtml::encode($tag['tag']),1).
        '">'.
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
</script>
