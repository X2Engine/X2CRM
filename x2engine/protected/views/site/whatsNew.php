<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
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

$groups=Groups::getUserGroups(Yii::app()->user->getId());
$tempUserList=array();
foreach($groups as $groupId){
    $userLinks=GroupToUser::model()->findAllByAttributes(array('groupId'=>$groupId));
    foreach($userLinks as $link){
        $user=User::model()->findByPk($link->userId);
        if(isset($user)){
            $tempUserList[]=$user->username;
        }
    }
}
$userList=array_keys(User::getNames());
$tempUserList=array_diff($userList,$tempUserList);
$usersGroups=implode(",",$tempUserList);
Yii::app()->clientScript->registerScript('highlightButton','
$("#feed-form textarea").bind("focus blur",function(){ toggleText(this); })
	.change(function(){
		if($(this).val()=="")
			$("#save-button").removeClass("highlight");
		else
			$("#save-button").addClass("highlight");
	});
',CClientScript::POS_READY);
Yii::app()->clientScript->registerScript('activity-feed','
function updateComments(id){
    $.ajax({
        url:"loadComments",
        data:{id:id},
        success:function(data){
            $("#"+id+"-comments").html(data);
        }
    });
}
var firstEventId='.$firstEventId.';
function publishPost(){
    $.ajax({
        url:"publishPost",
        type:"POST",
        data:{
            "text":$("#Events_text").val(),
            "associationId":$("#Events_associationId").val(),
            "visibility":$("#Events_visibility").val(),
            "subtype":$("#Events_subtype").val()
        },
        success:function(){
            $("#save-button").removeClass("highlight");
            $("#Events_text").val("");
            var textarea=document.getElementById("Events_text");
            toggleText(textarea);
            $(textarea).css("height","25px");
            $(textarea).next().slideUp(400);
        }
    });
}
function commentSubmit(id){
        var text=$("#"+id+"-comment").val();
        $("#"+id+"-comment").val("");
        $.ajax({
            url:"addComment",
            type:"POST",
            data:{text:text,id:id},
            success:function(data){
                var commentCount=data;
                $("#"+id+"-comment-count").html("<b>"+commentCount+"</b>");
                updateComments(id);
            }
        });
}
function minimizePosts(){
    $.each($(".event-text"),function(){
        if($(this).html().length>200){
            var text=this;
            var oldText=$(this).html();
            $.ajax({
                url:"minimizePosts",
                type:"GET",
                data:{"minimize":"minimize"},
                success:function(){
                    $(text).html($(text).html().slice(0,200)).after("<span class=\'elipsis\'>...</span>");
                    oldText="<span class=\'old-text\' style=\'display:none;\'>"+oldText+"</span>";
                    $(text).after(oldText);
                }
            });
        }else{
        
        }
    });
}
var minimize='.(Yii::app()->params->profile->minimizeFeed==1?'true':'false').';
function restorePosts(){
    $.ajax({
        url:"minimizePosts",
        type:"GET",
        data:{"minimize":"restore"},
        success:function(){
            $(".elipsis").remove();
            $.each($(".old-text"),function(){
                var event=$(this).prev(".event-text");
                $(event).html($(this).html());
            });
        }
    });
}
$(document).on("click","#min-posts",function(e){
    e.preventDefault();
    minimizePosts();
    minimize=true;
    $(this).toggle();
    $(this).next().show();
});

$(document).on("click","#restore-posts",function(e){
    e.preventDefault();
    restorePosts();
    minimize=false;
    $(this).toggle();
    $(this).prev().show();
});
$(document).on("click","#clear-filters-link",function(e){
    e.preventDefault();
    var str=window.location+"";
    pieces=str.split("?");
    var str2=pieces[0];
    pieces2=str2.split("#");
    window.location=pieces2[0]+"?filters=true&visibility=&users=&types=&subtypes=&default=false";
});
$(document).ready(function(){
    if(minimize==true){
        $("#min-posts").click();
    }
});
var username="'.Yii::app()->user->getName().'";
var usergroups="'.$usersGroups.'";
$(document).on("click","#just-me-filter",function(e){
        e.preventDefault();
        var users=new Array();
        $.each($(".users.filter-checkbox"),function(){
            if($(this).attr("name")!=username){
                users.push($(this).attr("name"));
            }
        });
        
        var str=window.location+"";
        pieces=str.split("?");
        var str2=pieces[0];
        pieces2=str2.split("#");
        window.location=pieces2[0]+"?filters=true&visibility=&users="+users+"&types=&subtypes=&default=false";
});
$(document).on("click","#my-groups-filter",function(e){
        e.preventDefault();
        var str=window.location+"";
        pieces=str.split("?");
        var str2=pieces[0];
        pieces2=str2.split("#");
        window.location=pieces2[0]+"?filters=true&visibility=&users="+usergroups+"&types=&subtypes=&default=false";
});
',CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScript('update-event-list','

$(document).on("click",".comment-link",function(e){
    var link=this;
    e.preventDefault();
    var pieces=$(this).attr("id").split("-");
    var id=pieces[0];
    $.ajax({
        url:"loadComments",
        data:{id:id},
        success:function(data){
            $("#"+id+"-comments").html(data);
            $(".empty").parent().hide();
            $("#"+id+"-comment-box").slideDown(400);
            $(link).toggle();
            $(link).next().toggle();
        }
    });
});

$(document).on("click",".comment-hide-link",function(e){
    e.preventDefault();
    $(this).toggle();
    $(this).prev().toggle();
    var pieces=$(this).prev().attr("id").split("-");
    var id=pieces[0];
    $("#"+id+"-comment-box").slideUp(400);
});
$(document).on("click",".important-link",function(e){
    var link=this;
    e.preventDefault();
    var pieces=$(this).attr("id").split("-");
    var id=pieces[0];
    $.ajax({
        url:"flagPost",
        data:{id:id,attr:"important"},
        success:function(data){
            $(link).parents(".view.top-level").addClass("important");
            
        }
    });
    $(link).toggle();
    $(link).next().toggle();
});

$(document).on("click",".unimportant-link",function(e){
    var link=this;
    e.preventDefault();
    var pieces=$(this).attr("id").split("-");
    var id=pieces[0];
    $.ajax({
        url:"flagPost",
        data:{id:id,attr:"unimportant"},
        success:function(data){
            $(link).parents(".view.top-level").removeClass("important");
            
        }
    });
    $(link).toggle();
    $(link).prev().toggle();
});

    var lastEventId='.$lastEventId.';
    function updateFeed(){
        $.ajax({
            url:"getEvents",
            type:"GET",
            data:{lastEventId:lastEventId},
            success:function(data){
                data=JSON.parse(data);
                lastEventId=data[0];
                if(data[1]){
                    var text=data[1];
                    if($("#new-events").is(":hidden")){
                        $("#new-events").show();
                    }
                    $.each($(".list-view"), function(){
                            if(typeof $.fn.yiiListView.settings["\'"+$(this).attr("id")+"\'"]=="undefined")
                                $(this).yiiListView();
                        });
                    $(text).hide().prependTo("#new-events").fadeIn(1000);
                    
                }
                if(data[2]){
                    var comments=data[2];
                    $.each(comments,function(key,value){
                        $("#"+key+"-comment-count").html("<b>"+value+"</b>");
                    });
                    if(data[3]>lastEventId)
                        lastEventId=data[3];
                }
                var t=setTimeout(function(){updateFeed();},5000);
            }
        });
    }
    updateFeed();
$(document).on("click",".delete-link",function(e){
    var link=this;
    pieces=$(link).attr("id").split("-");
    id=pieces[0];
    if(confirm("Are you sure you want to delete this post?")){
        window.location="'.$this->createUrl('profile/deletePost').'?id="+id;
    }else{
        e.preventDefault();
    }
});
',CClientScript::POS_HEAD);

?>

<div style="float:left;"><h2><?php echo Yii::t('app','Activity Feed'); ?></h2></div>
<?php 
echo "<div id='menu-links'>";
echo CHtml::link(Yii::t('app','Minimize Posts'),'#',array('id'=>'min-posts','class'=>'x2-button'));
echo CHtml::link(Yii::t('app','Restore Posts'),'#',array('id'=>'restore-posts','style'=>'display:none;','class'=>'x2-button'));
echo " ".CHtml::link(Yii::t('app','Clear Filters'),'#',array('id'=>'clear-filters-link','class'=>'x2-button'));
echo " ".CHtml::link(Yii::t('app','Just Me'),'#',array('id'=>'just-me-filter','class'=>'x2-button'));
echo " ".CHtml::link(Yii::t('app','My Groups'),'#',array('id'=>'my-groups-filter','class'=>'x2-button'));

echo "</div>";
?>
<div class="form" id="post-form" style="clear:both">
	<?php $feed=new Events; ?>
	<?php $form = $this->beginWidget('CActiveForm', array(
	'id'=>'feed-form',
	'enableAjaxValidation'=>false,
	'method'=>'post',
    'htmlOptions'=>array(
        'onsubmit'=>'publishPost();return false;'
    ),
	
	)); ?>	
	<div class="float-row" style='overflow:visible;'>
		<?php
		$feed->text = Yii::t('app','Enter text here...');
		echo $form->textArea($feed,'text',array('style'=>'width:99%;height:25px;color:#aaa;display:block;clear:both;'));
		echo "<div id='post-buttons' style='display:none;'>";
        echo $form->dropDownList($feed,'associationId',$users);
        $feed->visibility=1;
		echo $form->dropDownList($feed,'visibility',array(1=>Yii::t('actions','Public'),0=>Yii::t('actions','Private')));
        function translateOptions($item){
            return Yii::t('app',$item);
        }
        echo $form->dropDownList($feed,'subtype',array_map('translateOptions',json_decode(Dropdowns::model()->findByPk(14)->options,true)));
		echo CHtml::submitButton(Yii::t('app','Post'),array('class'=>'x2-button','id'=>'save-button'));
		echo CHtml::button(Yii::t('app','Attach A File/Photo'),array('class'=>'x2-button','onclick'=>"$('#attachments').toggle();"));
		echo "</div>";
        ?>
	</div>
	<?php $this->endWidget(); ?>
</div>


<div id="attachments" style="display:none;">
<?php $this->widget('Attachments',array('associationType'=>'feed','associationId'=>Yii::app()->user->getId())); ?>
</div>
<?php 

echo '<div class="list-view"><div id="new-events" class="items" style="display:none;"></div></div>';
$this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_viewEvent', 
    'id'=>'activity-feed',
    'pager' => array(
                    'class' => 'ext.infiniteScroll.IasPager', 
                    'rowSelector'=>'.view.top-level', 
                    'listViewId' => 'activity-feed', 
                    'header' => '',
                    'options'=>array(
                        'onRenderComplete'=>'js:function(){
                            if(minimize){
                                minimizePosts();
                            }
                        }'
                    ),
                    
                  ),
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/listview',
	'template'=>'{pager} {items}',
)); 


?>
<style>
    .social-tabs a{
        text-decoration:none;
    }
    .comment-textbox{
        -webkit-appearance: none;
        height: 22px;
        width: 100% !important;
        box-sizing: border-box;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        border: 1px solid #AAA;
        font-size: 12px;
        font-family: Arial, Helvetica, sans-serif;
        -moz-border-radius: 3px;
        -o-border-radius: 3px;
        -webkit-border-radius: 3px;
        border-radius: 3px;
        background: white;
    }
    .comment-box .items{
        margin-left:15px;
    }
    .important{
        background-color:#FFFFC2;
    }
</style>
<script>
    $(document).on('focus','#feed-form textarea',function(){
        formFieldFocus(this);
    });
    function formFieldFocus(elem) {
        $(elem).css("height","50px");
        $(elem).next().slideDown(400);
    }
</script>