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

if($(":checkbox:checked").length > ($(":checkbox").length)/2){
    checkedFlag=true;
}else{
    checkedFlag=false;
    $("#toggle-filters-link").html("Check Filters");
}
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
var checkedFlag;
$(document).on("click","#toggle-filters-link",function(e){
    e.preventDefault();
    checkedFlag=!checkedFlag;
    if(checkedFlag){
        $(this).html("'.Yii::t('app',"Uncheck Filters").'");
        $(".filter-checkbox").attr("checked","checked");
    }else{
        $(this).html("'.Yii::t('app',"Check Filters").'");
        $(".filter-checkbox").attr("checked",null);
    }
});
$(document).on("click","#min-posts",function(e){
    e.preventDefault();
    minimizePosts();
    minimize=true;
    $(this).toggle();
    $(this).prev().show();
});

$(document).on("click","#restore-posts",function(e){
    e.preventDefault();
    restorePosts();
    minimize=false;
    $(this).toggle();
    $(this).next().show();
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
    $(".first").after("<div class=\"list-view\"><div id=\"new-events\" class=\"items\" style=\"display:none;border-bottom:solid;\"></div></div>");
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
var commentFlag=false;
$(document).on("click","#toggle-all-comments",function(e){
    e.preventDefault();
    commentFlag=!commentFlag;
    if(commentFlag){
        $(".comment-link").click();
    }else{
        $(".comment-hide-link").click();
    }
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
            $(link).hide();
            $(link).next().show();
        }
    });
});

$(document).on("click",".comment-hide-link",function(e){
    e.preventDefault();
    $(this).hide();
    $(this).prev().show();
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

$(document).on("click",".sticky-link",function(e){
    var link=this;
    e.preventDefault();
    var pieces=$(this).attr("id").split("-");
    var id=pieces[0];
    $.ajax({
        url:"stickyPost",
        data:{id:id}
    });
    $(link).toggle();
    $(link).next().toggle();
});

$(document).on("click",".unsticky-link",function(e){
    var link=this;
    e.preventDefault();
    var pieces=$(this).attr("id").split("-");
    var id=pieces[0];
    $.ajax({
        url:"stickyPost",
        data:{id:id}
    });
    $(link).toggle();
    $(link).prev().toggle();
});

    var lastEventId='.(!empty($lastEventId)?$lastEventId:0).';
    var lastTimestamp='.(!empty($lastTimestamp)?$lastTimestamp:0).';
    function updateFeed(){
        $.ajax({
            url:"getEvents",
            type:"GET",
            data:{lastEventId:lastEventId, lastTimestamp:lastTimestamp},
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

<div class="page-title icon" style="background-image:url(<?php echo Yii::app()->theme->baseUrl; ?>/images/Activity_Feed.png);"><h2><?php echo Yii::t('app','Activity Feed'); ?></h2>
	<div id="menu-links" class="title-bar">
		<?php 
        echo CHtml::link(Yii::t('app','Toggle Comments'),'#',array('id'=>'toggle-all-comments','class'=>'x2-button right'));
        echo CHtml::link(Yii::t('app','My Groups'),'#',array('id'=>'my-groups-filter','class'=>'x2-button right'));
		echo CHtml::link(Yii::t('app','Just Me'),'#',array('id'=>'just-me-filter','class'=>'x2-button right'));
        echo CHtml::link(Yii::t('app','Uncheck Filters'),'#',array('id'=>'toggle-filters-link','class'=>'x2-button right'));
        echo CHtml::link(Yii::t('app','Restore Posts'),'#',array('id'=>'restore-posts','style'=>'display:none;','class'=>'x2-button right'));
        echo CHtml::link(Yii::t('app','Minimize Posts'),'#',array('id'=>'min-posts','class'=>'x2-button right'));
        
		?>
	</div>
</div>
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
        echo $form->dropDownList($feed,'subtype',array_map('translateOptions',json_decode(Dropdowns::model()->findByPk(113)->options,true)));
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
if(count($stickyDataProvider->getData())>0){
    $this->widget('zii.widgets.CListView', array(
        'dataProvider'=>$stickyDataProvider,
        'itemView'=>'_viewEventSticky', 
        'id'=>'sticky-feed',
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
                                if(commentFlag){
                                    $(".comment-link").click();
                                }
                            }'
                        ),

                    ),
        'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/listview',
        'template'=>'{pager} {items}',
    )); 
}
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
                            if(commentFlag){
                                $(".comment-link").click();
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