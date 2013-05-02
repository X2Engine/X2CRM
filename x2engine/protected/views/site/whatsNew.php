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
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/modcoder_excolor/jquery.modcoder.excolor.js');
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
$.each($(".comment-count"),function(){
    if($(this).attr("val")>0){
        $(this).parent().click();
    }
});
',CClientScript::POS_READY);
Yii::app()->clientScript->registerScript('activity-feed','

var debug = 0;

function consoleLog(obj) {
    if (console != undefined) {
        if(console.log != undefined && debug) {
            console.log(obj);
        }
    }
}

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
    $(".date-break.first").after("<div class=\"list-view\"><div id=\"new-events\" class=\"items\" style=\"display:none;border-bottom:solid;\"></div></div>");
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
    $("#broadcast-dialog").dialog({
        autoOpen: true,
        buttons: {
            "Broadcast": function() {
                $(this).dialog("close");
                $.ajax({
                    url:"flagPost",
                    data:{
                        id:id,
                        attr:"important",
                        email:$("#emailUsers").attr("checked"),
                        color:$("#broadcastColor").val().replace("#","%23"),
                        fontColor:$("#fontColor").val().replace("#","%23"),
                        linkColor:$("#linkColor").val().replace("#","%23")
                    },
                    success:function(data){
                        if($("#broadcastColor").val()==""){
                            var color="#FFFFC2";
                        }else{
                            var color=$("#broadcastColor").val();
                        }
                        if($("#fontColor").val()!=""){
                            $(link).parents(".view.top-level").css("color",$("#fontColor").val());
                            $(link).parents(".view.top-level div.event-text-box").children(".comment-age").css("color",$("#fontColor").val());
                        }
                        if($("#linkColor").val()!=""){
                            $(link).parents(".view.top-level div.event-text-box").find("a").css("color",$("#linkColor").val());
                        }
                        $(link).parents(".view.top-level").css("background-color",color);
                        $(link).parents(".view.top-level div.event-text-box").children(".comment-age").css("background-color",color);
                        $(link).toggle();
                        $(link).next().toggle();
                        $("#broadcastColor").val("");
                        $("#fontColor").val("");
                        $("#linkColor").val("");
                        $(".modcoder_excolor_clrbox").css("background-image","url("+yii.baseUrl+"/js/modcoder_excolor/transp.gif)");
                    }
                });
            },
            "Nevermind":function(){
                $(this).dialog("close");
            }
        },
        height:"auto",
        width:450,
        resizable:false
    });

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
            $(link).parents(".view.top-level").css("background-color","#fff");
            $(link).parents(".view.top-level").css("color","#222");
            $(link).parents(".view.top-level div.event-text-box").find("a").css("color","#06c");
            $(link).parents(".view.top-level div.event-text-box").children(".comment-age").css("background-color","#fff");
            $(link).parents(".view.top-level div.event-text-box").children(".comment-age").css("color","#666");

        }
    });
    $(link).toggle();
    $(link).prev().toggle();
});

function incrementLikeCount (likeCountElem) {
    likeCount = parseInt ($(likeCountElem).html ().replace (/[() ]/g, ""), 10) + 1;
    $(likeCountElem).html (" (" + likeCount + ")");
}

function decrementLikeCount (likeCountElem) {
    likeCount = parseInt ($(likeCountElem).html ().replace (/[() ]/g, ""), 10) - 1;
    $(likeCountElem).html (" (" + likeCount + ")");
}

$(document).on("click",".like-button",function(e){
    consoleLog ("click like");
    var link=this;
    e.preventDefault();
    var pieces=$(this).attr("id").split("-");
    var id=pieces[0];
    var tmpElem = $("<span>", { "text": ($(link).text ()) });
    $(link).after (tmpElem);
    $(link).toggle();
    $.ajax({
        url:"likePost",
        data:{id:id},
        success:function(data){
            consoleLog ("like-button ajax success " + data);
            $(tmpElem).remove ();
            if (data === "liked post") {
                incrementLikeCount ($(link).next().next());
            }
            $(link).next().toggle();
            reloadLikeHistory (id);
        }
    });
});

$(document).on("click",".unlike-button",function(e){
    consoleLog ("click unlike");
    var link=this;
    e.preventDefault();
    var pieces=$(this).attr("id").split("-");
    var id=pieces[0];
    var tmpElem = $("<span>", { "text": ($(link).text ()) });
    $(link).after (tmpElem);
    $(link).toggle();
    $.ajax({
        url:"likePost",
        data:{id:id},
        success:function(data){
            consoleLog ("unlike-button ajax success " + data);
            $(tmpElem).remove ();
            if (data === "unliked post") {
                decrementLikeCount ($(link).next());
            }
            $(link).prev().toggle();
            reloadLikeHistory (id);
        }
    });
});

/*
Used by unlike-button and like-button click events to update the like history
if it is already open
*/
function reloadLikeHistory (id) {
    var likeHistoryBox = $("#" + id + "-like-history-box");
    if (!likeHistoryBox.is(":visible")) {
        return;
    }
    var likes = $("#" + id + "-likes");
    $.ajax({
        url:"loadLikeHistory",
        data:{id:id},
        success:function(data){
            likes.html ("");
            var likeHistory = JSON.parse (data);
            consoleLog (data);

            // if last like was removed, collapse box
            if (likeHistory.length === 0) {
                likeHistoryBox.slideUp ();
                likes.html ("");
                return;
            }
            for (var name in likeHistory) {
                likes.append (likeHistory[name] + " liked this post. </br>");
            }
        }
    });
}

/*
Display the like history in a drop down underneath the post
*/
$(document).on("click",".like-count",function(e){
    e.preventDefault();
    var pieces=$(this).attr("id").split("-");
    var id=pieces[0];
    var likeHistoryBox = $("#" + id + "-like-history-box");
    var likes = $("#" + id + "-likes");
    if (likeHistoryBox.is(":visible")) {
        likeHistoryBox.slideUp ();
        likes.html ("");
        return;
    }
    $.ajax({
        url:"loadLikeHistory",
        data:{id:id},
        success:function(data){
            var likeHistory = JSON.parse (data);
            consoleLog (likeHistory);
            for (var name in likeHistory) {
                likes.append (likeHistory[name] + " liked this post. </br>");
                likeHistoryBox.slideDown (400);
            }
        }
    });
});

/*
Inserts a stickied activity into the sticky feed
*/
function insertSticky (stickyElement) {
    var id = $(stickyElement).children ().find (".comment-age").attr ("id").split ("-");

    // add sticky header
    if ($("#sticky-feed .empty").length !== 0) {
        $("#sticky-feed .items").append ($("<div>", {
            "class": "view top-level date-break sticky-section-header",
            "text": "- Sticky -"
        }));
        $("#sticky-feed .empty").remove ();
    }
    $("#sticky-feed").show ();
    $("#sticky-feed .items").show ();

    var stickyId = id[0];
    var stickyTimeStamp = id[1];

    // place the stickied post into the sticky feed in the correct location
    var hasInserted = false;
    $("#sticky-feed > .items > div.view.top-level.activity-feed").each (function (index, element) {
        var id = $(element).children ().find (".comment-age").attr ("id").split ("-");
        var eventId = id[0];
        var eventTimeStamp = id[1];
        consoleLog ("evtts = " + eventTimeStamp + ", stickyts = " + stickyTimeStamp);
        if (stickyTimeStamp == eventTimeStamp) {
            consoleLog ("found equal timestamp");
            if (stickyId > eventId) {
                $(stickyElement).insertBefore ($(element));
                hasInserted = true;
                return false;
            }
        } else if (stickyTimeStamp > eventTimeStamp) {
            $(stickyElement).insertBefore ($(element));
            hasInserted = true;
            return false;
        }
    });
    if (!hasInserted) {
        $("#sticky-feed .items").append ($(stickyElement));
    }

}

/*
Removes the activity from the activity feed and determines whether or not the
date header needs to be removed.
Parameter:
    activityElement - the activity element
    timeStamp - the formatted time stamp returned by ajax
Returns:
    the detacheded activity element
*/
function detachActivity (activityElement, timeStamp) {
    var foundMatch = false;
    var eventCount = 0;
    var match = null;
    var re = new RegExp (timeStamp, "g");

    // check if the activity is the only activity on a certain day,
    // if yes, remove the date header
    $("#activity-feed > .items").children ().each (function (index, element) {
        if ($(element).hasClass ("date-break")) { // found date header
            consoleLog ("if" + $(element).text ());
            if ($(element).text ().match (re)) { // date header matches
                consoleLog ("match");
                foundMatch = true;
                match = element;
            } else if (foundMatch) {
                return false;
            }
        } else if ($(element).hasClass ("view top-level activity-feed")) { // found post
            consoleLog ("else");
            if (foundMatch) {
                eventCount++;
            }
        } else if ($(element).hasClass ("list-view")) { // search through new posts
            $(element).find ("div.view.top-level.activity-feed").each (function (index, element) {
                if ($(element).hasClass ("view top-level activity-feed")) {
                    consoleLog ("else");
                    if (foundMatch) {
                        eventCount++;
                    }
                }
            });
        }
    });

    if (eventCount === 1) {
        consoleLog ("removing header");
        $(match).remove ();
    } else {
        consoleLog ("not removing header");
    }

    $(activityElement).children ().find (".sticky-link").mouseleave (); // close tool tip

    // hide extra elements if the activity is the last new post
    if ($(activityElement).parent ("#new-events").length === 1 &&
          $(activityElement).siblings ().length === 0) {
        $("#new-events").toggle ();
    }

    return $(activityElement).detach ();
}

function getDateHeader (timeStamp, timeStampFormatted) {
    return $("<div>", {
        "class": "view top-level date-break",
        "id": ("date-break-" + timeStamp),
        "text": ("- " + timeStampFormatted + " -")
    });
}

/*
Inserts an activity into the activity feed. Inserts a new date header if necessary.
Parameters:
    timeStamp - the formatted time stamp returned by ajax
*/
function insertActivity (activityElement, timeStampFormatted) {
    var id = $(activityElement).children ().find (".comment-age").attr ("id").split ("-");

    if ($("#sticky-feed div.view.top-level.activity-feed").length === 0) {
        $("#sticky-feed").hide ();
    }

    var stickyId = id[0];
    var stickyTimeStamp = id[1];
    var re = new RegExp (timeStampFormatted, "g");

    var hasInserted = false;
    var foundMyHeader = false;
    var prevElement = null;
    $("#activity-feed > .items").children ().each (function (index, element) {
        if ($(element).hasClass ("date-break")) { // found date header
            consoleLog ("date-break " + $(element).text ());
            if (!$(element).text ().match (re)) { // date header differs
                var eventTimeStamp = $(element).attr ("id").split ("-")[2];
                if (foundMyHeader) { // insert as last element under header
                    if (stickyTimeStamp > eventTimeStamp || timeStampFormatted.match (/Today/)) {
                        $(activityElement).insertBefore ($(element));
                        hasInserted = true;
                        return false;
                    }
                } else { // create new date header
                    if (stickyTimeStamp > eventTimeStamp || timeStampFormatted.match (/Today/)) {
                        var header = getDateHeader (stickyTimeStamp, timeStampFormatted);
                        $(header).insertBefore ($(element));
                        $(activityElement).insertAfter ($(header));
                        if (timeStampFormatted.match (/Today/)) {
                            var newPostContainer = $("#activity-feed > .items > div.list-view").detach ();
                            $(newPostContainer).insertAfter ($(header));
                        }
                        consoleLog ("create header");
                        hasInserted = true;
                        return false;
                    }
                }
                consoleLog ("dont create header");
            } else {
                foundMyHeader = true;
            }
        } else if ($(element).hasClass ("view top-level activity-feed")) { // found post
            consoleLog ("false " + $(element).attr ("class"));
            var id = $(element).children ().find (".comment-age").attr ("id").split ("-");
            var eventId = id[0];
            var eventTimeStamp = id[1];
            if (stickyTimeStamp === eventTimeStamp) {
                if (stickyId > eventId) {
                    consoleLog ("inserting element before next, sort id");
                    $(activityElement).insertBefore ($(element));
                    hasInserted = true;
                    return false;
                }
            } else if (stickyTimeStamp > eventTimeStamp) {
                consoleLog ("inserting element before next");
                $(activityElement).insertBefore ($(element));
                hasInserted = true;
                return false;
            }
            prevElement = element;
        } else if ($(element).hasClass ("list-view")) { // search through new posts
            consoleLog ("false false " + $(element).attr ("class"));
            var brokeLoop = false;
            $(element).find ("div.view.top-level.activity-feed").each (function (index, element) {
                var id = $(element).children ().find (".comment-age").attr ("id").split ("-");
                var eventId = id[0];
                var eventTimeStamp = id[1];
                if (stickyTimeStamp === eventTimeStamp) {
                    if (stickyId > eventId) {
                        consoleLog ("2 inserting element before next, sort id");
                        $(activityElement).insertBefore ($(element));
                        hasInserted = true;
                        brokeLoop = true;
                        return false;
                    }
                } else if (stickyTimeStamp > eventTimeStamp) {
                    consoleLog ("2 inserting element before next");
                    $(activityElement).insertBefore ($(element));
                    hasInserted = true;
                    brokeLoop = true;
                    return false;
                }
                prevElement = element;
            });
            if (brokeLoop) {
                return false;
            }
        }
    });

    if (!hasInserted) {
        if (prevElement) { // insert post at end of activity feed
            if (foundMyHeader) {
                consoleLog ("inserting element at end");
                $(activityElement).insertAfter ($(prevElement));
            } else {
                consoleLog ("inserting element + header at end 1");
                var header = getDateHeader (stickyTimeStamp, timeStampFormatted);
                $(header).insertAfter ($(prevElement));
                $(activityElement).insertAfter ($(header));
            }
        } else { // no posts in activity feed
            consoleLog ("inserting element + header at end");
            var header = getDateHeader (stickyTimeStamp, timeStampFormatted);
            $("#activity-feed .list-view").before ($(header));
            $("#activity-feed > .items").append ($(activityElement));
        }
    }
}

$(document).on("click",".sticky-link",function(e){
    var link=this;
    e.preventDefault();
    var pieces=$(this).attr("id").split("-");
    var id=pieces[0];
    var tmpElem = $("<span>", { "text": ($(link).text ()) });
    $(link).after (tmpElem);
    $(link).toggle();
    $.ajax({
        url:"stickyPost",
        data:{id:id},
        success:function (data) {
            consoleLog ("sticky ajax " + data);
            var elem = detachActivity (
                $(link).parents ("div.view.top-level.activity-feed"), data);
            $(tmpElem).remove ();
            $(link).next().toggle();
            insertSticky (elem);
        }
    });
});

$(document).on("click",".unsticky-link",function(e){
    var link=this;
    e.preventDefault();
    var pieces=$(this).attr("id").split("-");
    var id=pieces[0];
    var tmpElem = $("<span>", { "text": ($(link).text ()) });
    $(link).after (tmpElem);
    $(link).toggle();
    $.ajax({
        url:"stickyPost",
        data:{id:id},
        success:function (data) {
            consoleLog ("unsticky ajax " + data);
            var elem = $(link).parents ("div.view.top-level.activity-feed").detach ();
            $(tmpElem).remove ();
            $(link).prev().toggle();
            insertActivity (elem, data);
        }
    });
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
                    if($("#activity-feed .items .empty").html()){
                        $("#activity-feed .items").html("<div class=\"list-view\"><div id=\"new-events\" style=\"display:none;\"></div></div>");
                    }
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
$(document).on("submit","#attachment-form-form",function(){
    if($("#Events_text").val()!="" && $("#Events_text").val()!="'.Yii::t('app','Enter text here...').'"){
        $("#attachmentText").val($("#Events_text").val());
    }
});
',CClientScript::POS_HEAD);

?>

<div class="page-title icon" style="background-image:url(<?php echo Yii::app()->theme->baseUrl; ?>/images/Activity.png);"><h2><?php echo Yii::t('app','Activity Feed'); ?></h2>
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
$this->widget('zii.widgets.CListView', array(
    'dataProvider'=>$stickyDataProvider,
    'itemView'=>'_viewEvent',
    'id'=>'sticky-feed',
    'pager' => array(
                    'class' => 'ext.infiniteScroll.IasPager',
                    'rowSelector'=>'.view.top-level',
                    'listViewId' => 'sticky-feed',
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
    'template'=>'{pager} {items}'
));
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
<div id="broadcast-dialog">
    <div>
        <?php echo CHtml::label('Do you want to email all users?','emailUsers'); ?>
        <?php echo CHtml::checkBox('emailUsers'); ?>
    </div>
    <div>
        <br><?php echo Yii::t('app','Leave colors blank for defaults.');?>
    </div>
    <div>
        <br>
        <?php echo CHtml::label('What color should the broadcast be?','broadcastColor'); ?>
        <?php echo CHtml::textField('broadcastColor',''); ?>
    </div>
    <div>
        <?php echo CHtml::label('What color should the font be?','fontColor'); ?>
        <?php echo CHtml::textField('fontColor',''); ?>
    </div>
    <div>
        <?php echo CHtml::label('What color should the links be?','linkColor'); ?>
        <?php echo CHtml::textField('linkColor',''); ?>
    </div>
</div>
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
    #modcoder_colorpicker{
        z-index:9998;
        height:17px;
    }
    #modcoder_picker{
        z-index:9999;
    }
    .pager{
        visibility:hidden;
    }
    .yiiPager{
        display:none;
    }
</style>
<script>
    $(document).on('focus','#feed-form textarea',function(){
        $(this).animate({"height":"50px"});
        $(this).next().slideDown(400);
    });

    if ($("#sticky-feed .empty").length !== 0) {
        $("#sticky-feed").hide ();
    }

    $(document).on('ready',function(){
        $("#broadcastColor").modcoder_excolor({
            hue_bar : 3,
            hue_slider : 5,
            border_color : "#aaa",
            sb_border_color : "#d6d6d6",
            round_corners : true,
            shadow_color : "#000000",
            background_color : "#f0f0f0",
            backlight : true
        });
        $("#fontColor").modcoder_excolor({
            hue_bar : 3,
            hue_slider : 5,
            border_color : "#aaa",
            sb_border_color : "#d6d6d6",
            round_corners : true,
            shadow_color : "#000000",
            background_color : "#f0f0f0",
            backlight : true
        });
        $("#linkColor").modcoder_excolor({
            hue_bar : 3,
            hue_slider : 5,
            border_color : "#aaa",
            sb_border_color : "#d6d6d6",
            round_corners : true,
            shadow_color : "#000000",
            background_color : "#f0f0f0",
            backlight : true
        });
        $('#broadcast-dialog').hide();
    });
</script>

