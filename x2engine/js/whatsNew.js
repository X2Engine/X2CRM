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


// Globals
x2.whatsNew.timeout = null; // used to clear timeout when editor resize animation is called
x2.whatsNew.editorManualResize = false; // used to prevent editor resize animation on manual resize
x2.whatsNew.editorIsExpanded = false; // used to prevent text field expansion if already expanded
var debug = 0;

function consoleLog (obj) {
    if (console != undefined) {
        if(console.log != undefined && debug) {
            console.log(obj);
        }
    }
}

function consoleDebug (obj) {
    if (console != undefined) {
        if(console.debug != undefined && debug) {
            console.debug (obj);
        }
    }
}


/*
Send post text to server via Ajax and minimize editor.
*/
function publishPost () {

    var editorText = window.newPostEditor.getData();

    if (!editorText.match (/<br \/>\\n&nbsp;$/)) { // append newline if there isn't one
        editorText += "<br />\n&nbsp;";
    }

    // insert an invisible div over editor to prevent focus
    var editorOverlay = $("<div>", {"id": "editor-overlay"}).css ({
        "width": $("#post-form").css ("width"),
        "height": $("#post-form").css ("height"),
        "position": "absolute"
    });
    $("#post-form").after ($(editorOverlay));
    $(editorOverlay).position ({
        my: "left top",
        at: "left+10 top",
        of: $("#post-form")
    });

    initMinimizeEditor ();

    $.ajax({
        url:"publishPost",
        type:"POST",
        data:{
            //"text":window.newPostEditor.getData(),
            "text":editorText,
            "associationId":$("#Events_associationId").val(),
            "visibility":$("#Events_visibility").val(),
            "subtype":$("#Events_subtype").val()
        },
        success:function(){
            finishMinimizeEditor ();
        },
        failure:function(){
            window.newPostEditor.focusManager.unlock ();
        },
        complete:function(){
            $(editorOverlay).remove ();
        }
    });
    return false;
}

/*
Animate resize of the new post ckeditor window.
*/
function animateEditorVerticalResize (initialHeight, newHeight,
                                      animationTime /* in milliseconds */) {
    if (x2.whatsNew.editorManualResize) { // user is currently resizing text field manually
        return;
    }

    // calculate delta per 50 ms for given animation time
    var heightDifference = Math.abs (newHeight - initialHeight);
    var delay = 50;
    var steps = Math.ceil (animationTime / delay);
    var delta = Math.ceil (heightDifference / steps);

    var lastStepSize = delta;

    // ensure that ckeditor text field is resized exactly to specified height
    if (steps * delta > heightDifference) {
        lastStepSize = heightDifference - (steps - 1) * delta;
    }


    var increaseHeight = newHeight - initialHeight > 0 ? true : false;
    if (!increaseHeight) delta *= -1;
    var currentHeight = initialHeight;

    if (x2.whatsNew.timeout != null) {
        window.clearTimeout (x2.whatsNew.timeout); // clear an existing animation timeout
    }
    x2.whatsNew.timeout = window.setTimeout (function resizeTimeout () {
        if (--steps === 0) {
            delta = lastStepSize;
            if (!increaseHeight) delta *= -1;
        }
        window.newPostEditor.resize ("100%", currentHeight + delta, true);
        currentHeight += delta;
        if (increaseHeight && currentHeight < newHeight) {
            x2.whatsNew.timeout = setTimeout (resizeTimeout, delay);
        } else if (!increaseHeight && currentHeight > newHeight) {
            x2.whatsNew.timeout = setTimeout (resizeTimeout, delay);
        }
    }, delay);
}

/*
Remove cursor from editor by focusing on a temporary dummy input element.
*/
function removeCursorFromEditor () {
    $("#post-form").append ($("<input>", {"id": "dummy-input"}));//, "style":"display:none;"}));
    var x = window.scrollX;
    var y = window.scrollY;
    $("#dummy-input").focus ();
    window.scrollTo (x, y); // prevent scroll from focus event
    $("#dummy-input").remove ();
}

/*
Called after initMinimizeEditor (), minimizes the editor.
*/
function finishMinimizeEditor () {

    if ($("[title='Collapse Toolbar']").length !== 0) {
        window.newPostEditor.execCommand ("toolbarCollapse");
    }
    var editorCurrentHeight = parseInt (
        window.newPostEditor.ui.space (
        "contents").getStyle("height").replace (/px/, ""), 10);
    var editorMinHeight = window.newPostEditor.config.height;
    animateEditorVerticalResize (editorCurrentHeight, editorMinHeight, 300);
    if (window.newPostEditor.getData () !== "") {
        window.newPostEditor.setData ("", function () {
            window.newPostEditor.fire ("blur");
        });
    }
    $("#save-button").removeClass("highlight");
    $("#post-buttons").slideUp (400);
    x2.whatsNew.editorIsExpanded = false;

    // focus on dummy input field to negate forced toolbar collapse refocusing on editor
    removeCursorFromEditor ();

    window.newPostEditor.focusManager.unlock ();

    $("#attachments").hide ();
}

/*
Called before finishMinimizeEditor (), prevents forced toolbar collapse from refocusing
on editor.
*/
function initMinimizeEditor () {
    window.newPostEditor.focusManager.blur (true);
    window.newPostEditor.focusManager.lock ();
}


// this is a hack to temporarily improve behavior of file attachment menu
function attachmentMenuBehavior () {

    $("#submitAttach").hide ();

    function submitAttachment () {
        $("#submitAttach").click ();
        return false;
    }

    $("#toggle-attachment-menu-button").click (function () {
        if ($("#attachments").is (":visible")) {
            $("#save-button").bind ("click", submitAttachment);
        } else {
            $("#save-button").unbind ("click", submitAttachment);
        }
    });
}


// setup ckeditor publisher behavior
function setupEditorBehavior () {

    window.newPostEditor = createCKEditor (
        "Events_text", { height:70, toolbarStartupExpanded: false, placeholder: x2.whatsNew.translations['Enter text here...']}, editorCallback);

    function editorCallback () {

        // expand post buttons if user manually resizes
        CKEDITOR.instances.Events_text.on ("resize", function () {
            if (x2.whatsNew.editorManualResize && !x2.whatsNew.editorIsExpanded) {
                CKEDITOR.instances.Events_text.focus ();
            }
        });

        // prevent editor resize animation when user is manually resizing
        $(".cke_resizer_ltr").mousedown (function () {
            $(document).one ("mouseup", function () {
                x2.whatsNew.editorManualResize = false;
            });
            x2.whatsNew.editorManualResize = true;
        });

    }

    // custom event triggered by ckeditor confighelper plugin
    $(document).on ("myFocus", function () {
        if (!x2.whatsNew.editorIsExpanded) {
            x2.whatsNew.editorIsExpanded = true;
            $("#save-button").addClass ("highlight");
            var editorMinHeight = window.newPostEditor.config.height;
            var newHeight = 140;
            animateEditorVerticalResize (editorMinHeight, newHeight, 300);
            $("#post-buttons").slideDown (400);
        }
    });

    // minimize editor on click outside
    $("html").click (function () {
        var editorText = window.newPostEditor.getData();

        if (x2.whatsNew.editorIsExpanded && editorText === "" &&
            $('#upload').val () === "") {

            initMinimizeEditor ();
            finishMinimizeEditor ();
        }
    });

    // enables detection of a click outside the publisher div
    $("#post-form, #attachment-form").click (function (event) {
        event.stopPropagation ();
    });

}


function setupActivityFeed () {

    function updateComments(id){
        $.ajax({
            url:"loadComments",
            data:{id:id},
            success:function(data){
                $("#"+id+"-comments").html(data);
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
                        $(text).html($(text).html().slice(0,200)).after("<span class='elipsis'>...</span>");
                        oldText="<span class='old-text' style='display:none;'>"+oldText+"</span>";
                        $(text).after(oldText);
                    }
                });
            }else{

            }
        });
    }

    //var minimize = x2.whatsNew.minimizeFeed;
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
    if($(":checkbox:checked").length > ($(":checkbox").length)/2){
        checkedFlag=true;
    } else {
        checkedFlag=false;
        $("#toggle-filters-link").html("Check Filters");
    }

    $(document).on("click","#toggle-filters-link",function(e){
        e.preventDefault();
        checkedFlag=!checkedFlag;
        if(checkedFlag){
            $(this).html(x2.whatsNew.translations['Uncheck Filters']);
            $(".filter-checkbox").attr("checked","checked");
        }else{
            $(this).html(x2.whatsNew.translations['Check Filters']);
            $(".filter-checkbox").attr("checked",null);
        }
    });

    $(document).on("click","#min-posts",function(e){
        e.preventDefault();
        minimizePosts();
        x2.whatsNew.minimizeFeed = true;
        $(this).toggle();
        $(this).prev().show();
    });

    $(document).on("click","#restore-posts",function(e){
        e.preventDefault();
        restorePosts();
        x2.whatsNew.minimizeFeed = false;
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

    if(x2.whatsNew.minimizeFeed == true){
        $("#min-posts").click();
    }
    $(".date-break.first").after("<div class='list-view'><div id='new-events' class='items' style='display:none;border-bottom:solid #BABABA;'></div></div>");

    var username=yii.profile.username;//"'.Yii::app()->user->getName().'";
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

    //var commentFlag=false;
    $(document).on("click","#toggle-all-comments",function(e){
        e.preventDefault();
        x2.whatsNew.commentFlag = !x2.whatsNew.commentFlag;
        if(x2.whatsNew.commentFlag){
            $(".comment-link").click();
        }else{
            $(".comment-hide-link").click();
        }
    });

    $('#submit-button').click (publishPost);

    if ($("#sticky-feed .empty").length !== 0) {
        $("#sticky-feed").hide ();
    }
    
	$('#activity-feed').on('submit','.comment-box form',function() {
		commentSubmit($(this).attr('id').slice(9));
		return false;
	});
        
    // show all comments
    $.each($(".comment-count"),function(){
        if($(this).attr("val")>0){
            $(this).parent().click();
        }
    });

    // expand all like histories
    $.each($(".like-count"),function(){
        var likeCount = parseInt ($(this).text ().replace (/[()]/g, ""), 10);
        if (likeCount > 0) {
            $(this).click();
        }
    });

}


function updateEventList () {

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
                            addCheckerImage ($("#broadcastColor"));
                            addCheckerImage ($("#fontColor"));
                            addCheckerImage ($("#linkColor"));
                        }
                    });
                },
                "Nevermind":function(){
                    $(this).dialog("close");
                }
            },
            height:"auto",
            width:850,
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
            if (stickyTimeStamp == eventTimeStamp) {
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
                if ($(element).text ().match (re)) { // date header matches
                    foundMatch = true;
                    match = element;
                } else if (foundMatch) {
                    return false;
                }
            } else if ($(element).hasClass ("view top-level activity-feed")) { // found post
                if (foundMatch) {
                    eventCount++;
                }
            } else if ($(element).hasClass ("list-view")) { // search through new posts
                $(element).find ("div.view.top-level.activity-feed").each (function (index, element) {
                    if ($(element).hasClass ("view top-level activity-feed")) {
                        if (foundMatch) {
                            eventCount++;
                        }
                    }
                });
            }
        });

        if (eventCount === 1) {
            $(match).remove ();
        } else {
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
                            hasInserted = true;
                            return false;
                        }
                    }
                } else {
                    foundMyHeader = true;
                }
            } else if ($(element).hasClass ("view top-level activity-feed")) { // found post
                var id = $(element).children ().find (".comment-age").attr ("id").split ("-");
                var eventId = id[0];
                var eventTimeStamp = id[1];
                if (stickyTimeStamp === eventTimeStamp) {
                    if (stickyId > eventId) {
                        $(activityElement).insertBefore ($(element));
                        hasInserted = true;
                        return false;
                    }
                } else if (stickyTimeStamp > eventTimeStamp) {
                    $(activityElement).insertBefore ($(element));
                    hasInserted = true;
                    return false;
                }
                prevElement = element;
            } else if ($(element).hasClass ("list-view")) { // search through new posts
                var brokeLoop = false;
                $(element).find ("div.view.top-level.activity-feed").each (function (index, element) {
                    var id = $(element).children ().find (".comment-age").attr ("id").split ("-");
                    var eventId = id[0];
                    var eventTimeStamp = id[1];
                    if (stickyTimeStamp === eventTimeStamp) {
                        if (stickyId > eventId) {
                            $(activityElement).insertBefore ($(element));
                            hasInserted = true;
                            brokeLoop = true;
                            return false;
                        }
                    } else if (stickyTimeStamp > eventTimeStamp) {
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
                    $(activityElement).insertAfter ($(prevElement));
                } else {
                    var header = getDateHeader (stickyTimeStamp, timeStampFormatted);
                    $(header).insertAfter ($(prevElement));
                    $(activityElement).insertAfter ($(header));
                }
            } else { // no posts in activity feed
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
                var elem = $(link).parents ("div.view.top-level.activity-feed").detach ();
                $(tmpElem).remove ();
                $(link).prev().toggle();
                insertActivity (elem, data);
            }
        });
    });

    var lastEventId=x2.whatsNew.lastEventId;
    var lastTimestamp=x2.whatsNew.lastTimestamp;
    function updateFeed(){
        $.ajax({
            url:"getEvents",
            type:"GET",
            data:{'lastEventId':lastEventId, 'lastTimestamp':lastTimestamp},
            success:function(data){
                data=JSON.parse(data);
                lastEventId=data[0];
                if(data[1]){
                    var text=data[1];
                    if($("#activity-feed .items .empty").html()){
                        $("#activity-feed .items").html("<div class='list-view'><div id='new-events' style='display:none;'></div></div>");
                    }
                    if($("#new-events").is(":hidden")){
                        $("#new-events").show();
                    }
                    $.each($(".list-view"), function(){
                            if(typeof $.fn.yiiListView.settings["'"+$(this).attr("id")+"'"]=="undefined")
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
                    if(data[4]>lastTimestamp)
                        lastTimestamp=data[4];
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
            window.location=x2.whatsNew.deletePostUrl + '?id=' + id;
        }else{
            e.preventDefault();
        }
    });

    $(document).on("submit","#attachment-form-form",function(){
        if(window.newPostEditor.getData()!="" && window.newPostEditor.getData()!=x2.whatsNew.translations['Enter text here...']){
            $("#attachmentText").val(window.newPostEditor.getData ());
        }
    });

}

function setupFeedColorPickers () {

    /*
    Convert relevent input fields to color pickers.
    */
    setupSpectrum ($('#broadcastColor'));
    setupSpectrum ($('#fontColor'));
    setupSpectrum ($('#linkColor'));
    setupSpectrum ($('#broadcastColor'));

    $('#broadcast-dialog').hide();

}

function setupChartBehavior () {

    var eventData = null;
    var feedChart = null;
    var msPerHour = 3600 * 1000;
    var msPerDay = 86400 * 1000;
    var msPerWeek = 7 * 86400 * 1000;

    /*
    Ask server for all events between user specified dates of a specified type.
    Replot data on server response.
    Parameters:
        type - String, the event type
        redraw - Boolean, determines whether plotData will clear the plot before drawing
    */
    function getEventsBetweenDates (type, redraw) {
        var tsDict = getStartEndTimestamp ();
        var startTimestamp = tsDict['startTimestamp'];
        var endTimestamp = tsDict['endTimestamp'];

        $.ajax ({
            url: 'getEventsBetween',
            data: {
                'startTimestamp': startTimestamp / 1000,
                'endTimestamp': endTimestamp / 1000,
                'type': 'any'
            },
            success: function (data) {
                eventData = JSON.parse (data);
                plotData ({'redraw': redraw});
            }
        });
    }

    /*
    Returns the string of the specified width padded on the left with zeroes.
    Precondition: width >= str.length
    */
    function padTimeField (str, width) {
        if (str.length === width) return str;


        return (new Array (width - str.length + 1)).join ('0') + str;
    }

    /*
    Returns an array of jqplot entries with y values equal to 0 and x values
    between timestamp1 and timestamp2. x values increase by interval.
    Parameters
        inclusiveBegin - a boolean, whether to include the entry corresponding to
            timestamp1 in the returned array
        inclusiveEnd - a boolean, whether to include the entry corresponding to
            timestamp2 in the returned array
        showMarker - if this is set to true, the returned array will have at most
            2 entries.
    */
    function getZeroEntriesBetween (
        timestamp1, timestamp2, interval, inclusiveBegin , inclusiveEnd , showMarker) {

        if (timestamp2 <= timestamp1) {
            return [];
        }

        var entries = [];

        if (inclusiveBegin)
            entries.push ([timestamp1, 0]);

        switch (interval) {
            case 'hour':
                var msPerHour = 3600 * 1000;

                if (!showMarker) {
                    var intermediateTimestamp1 = timestamp1;
                    var intermediateTimestamp2 = timestamp2;
                    intermediateTimestamp1 += msPerHour;
                    intermediateTimestamp2 -= msPerHour;
                    if (intermediateTimestamp1 < intermediateTimestamp2) {
                        entries.push ([intermediateTimestamp1, 0]);
                        entries.push ([intermediateTimestamp2, 0]);
                    } else if (intermediateTimestamp2 < timestamp2) {
                        entries.push ([intermediateTimestamp1, 0]);
                    }
                    if (inclusiveEnd) {
                        entries.push ([timestamp2, 0]);
                    }
                } else {
                    var intermediateTimestamp = timestamp1;
                    while (true) {
                        intermediateTimestamp += msPerHour;
                        if ((intermediateTimestamp < timestamp2 && !inclusiveEnd) ||
                            (intermediateTimestamp <= timestamp2 && inclusiveEnd)) {
                            entries.push ([intermediateTimestamp, 0]);
                        } else {
                            break;
                        }
                    }
                }
                break;
            case 'day':
                var msPerDay = 86400 * 1000;

                if (!showMarker) {
                    var intermediateTimestamp1 = timestamp1;
                    var intermediateTimestamp2 = timestamp2;
                    intermediateTimestamp1 += msPerDay;
                    intermediateTimestamp2 -= msPerDay;
                    if (intermediateTimestamp1 < intermediateTimestamp2) {
                        entries.push ([intermediateTimestamp1, 0]);
                        entries.push ([intermediateTimestamp2, 0]);
                    } else if (intermediateTimestamp2 < timestamp2) {
                        entries.push ([intermediateTimestamp1, 0]);
                    }
                    if (inclusiveEnd) {
                        entries.push ([timestamp2, 0]);
                    }
                } else {
                    var intermediateTimestamp = timestamp1;
                    while (true) {
                        intermediateTimestamp += msPerDay;
                        if ((intermediateTimestamp < timestamp2 && !inclusiveEnd) ||
                            (intermediateTimestamp <= timestamp2 && inclusiveEnd)) {
                            entries.push ([intermediateTimestamp, 0]);
                        } else {
                            break;
                        }
                    }
                }

                break;
            case 'week':
                var msPerWeek = 7 * 86400 * 1000;

                if (!showMarker) {
                    var intermediateTimestamp1 = timestamp1;
                    var intermediateTimestamp2 = timestamp2;
                    intermediateTimestamp1 += msPerWeek;
                    intermediateTimestamp2 -= msPerWeek;
                    if (intermediateTimestamp1 < intermediateTimestamp2) {
                        entries.push ([intermediateTimestamp1, 0]);
                        entries.push ([intermediateTimestamp2, 0]);
                    } else if (intermediateTimestamp2 < timestamp2) {
                        entries.push ([intermediateTimestamp1, 0]);
                    }
                } else {
                    var intermediateTimestamp = timestamp1;
                    while (true) {
                        intermediateTimestamp += msPerWeek;
                        if ((intermediateTimestamp < timestamp2 && !inclusiveEnd) ||
                            (intermediateTimestamp <= timestamp2 && inclusiveEnd)) {
                            entries.push ([intermediateTimestamp, 0]);
                        } else {
                            break;
                        }
                    }
                }

                break;
            case 'month':
                var date1 = new Date (timestamp1);
                var date2 = new Date (timestamp2);
                var M1 = date1.getMonth () + 1;
                var D1 = date1.getDate ();
                var Y1 = date1.getFullYear ();
                var M2 = date2.getMonth () + 1;
                var D2 = date2.getDate ();
                var Y2 = date2.getFullYear ();
                var endMonth = date2.getMonth ();
                var endYear = date2.getYear ();
                var beginString = (M1 + '-' + 1 + '-' + Y1);
                var endString = (M2 + '-' + 1 + '-' + Y2);
                var isFirst = true;


                var dateString, timestamp;
                while (true) {

                    M1++;
                    if (M1 === 13) {
                        Y1++;
                        M1 = 1;
                    }

                    beginString = M1 + '-' + 1 + '-' + Y1;
                    nextMonth = M1 + 1;
                    nextYear = Y1;
                    if (nextMonth === 13) {
                        nextYear++;
                        nextMonth = 1;
                    }
                    nextString = nextMonth + '-' + 1 + '-' + nextYear;

                    if ((inclusiveEnd) ||
                        (!inclusiveEnd && beginString !== endString)) {
                        timestamp = (new Date (Y1, M1 - 1, 1, 0, 0, 0, 0)).getTime ();
                        if (isFirst) {
                            entries.push ([timestamp, 0]);
                            isFirst = false;
                        } else if (showMarker ||
                            nextString === endString) {
                            entries.push ([timestamp, 0]);
                        } else {

                        }
                    }
                    if (beginString === endString) {
                        break;
                    }
                }

                break;
        }
        return entries;
    }

    /*
    Returns an array which can be passed to jqplot. Each entry in the array corresponds
    to the number of events of a given type and at a certain time (hour, day, week, or
    month depending on the bin size)
    Parameters:
        eventData - an array set by getEventsBetween
        binSize - a string
        type - a string. The type of event that will get plotted.
    */
    function groupChartData (eventData, binSize, type, showMarker) {
        var chartData = [];

        // group chart data into bins and keep count of the number of entries in each bin
        switch (binSize) {
            case 'hour-bin-size':
                var hour, day, month, year, evt, dateString, timestamp;
                for (var i in eventData) {
                    evt = eventData[i];
                    if (type !== 'any' && evt['type'] !== type) continue;
                    if (evt['year'] === year &&
                        evt['month'] === month &&
                        evt['day'] === day &&
                        evt['hour'] === hour) {
                        chartData[chartData.length - 1][1]++;
                    } else {
                        year = evt['year'];
                        month = evt['month'];
                        day = evt['day'];
                        hour = evt['hour'];

                        timestamp = (new Date (
                            year, month - 1, day, hour, 0, 0, 0)).getTime ();
                        chartData.push ([timestamp, 1]);
                    }

                }
                break;
            case 'day-bin-size':
                var day, month, year, evt, dateString, timestamp;
                for (var i in eventData) {
                    evt = eventData[i];
                    if (type !== 'any' && evt['type'] !== type) continue;
                    if (evt['year'] === year &&
                        evt['month'] === month &&
                        evt['day'] === day) {
                        chartData[chartData.length - 1][1]++;
                    } else {
                        year = evt['year'];
                        month = evt['month'];
                        day = evt['day'];

                        timestamp = (new Date (
                            year, month - 1, day, 0, 0, 0, 0)).getTime ();
                        chartData.push ([timestamp, 1]);
                    }
                }
                break;
            case 'week-bin-size':
                var week, year, evt, dateString, timestamp, date, day, msPerWeek;
                for (var i in eventData) {
                    evt = eventData[i];
                    if (type !== 'any' && evt['type'] !== type) continue;
                    if (evt['year'] === year &&
                        evt['week'] === week) {
                        chartData[chartData.length - 1][1]++;
                    } else {
                        year = evt['year'];
                        week = evt['week'];
                        timestamp = (new Date (
                            year, evt['month'] - 1, evt['day'], 0, 0, 0, 0)).getTime ();
                        date = new Date (timestamp);
                        day = date.getDay ();
                        msPerWeek = 86400 * 1000;
                        timestamp -= day * msPerWeek;

                        chartData.push ([(timestamp), 0]);
                    }
                }
                break;
            case 'month-bin-size':
                var month, year, evt, dateString, timestamp;
                for (var i in eventData) {
                    evt = eventData[i];
                    if (type !== 'any' && evt['type'] !== type) continue;
                    if (evt['year'] === year &&
                        evt['month'] === month) {
                        chartData[chartData.length - 1][1]++;
                    } else {
                        year = evt['year'];
                        month = evt['month'];

                        timestamp = (new Date (
                            year, month - 1, 1, 0, 0, 0, 0)).getTime ();
                        chartData.push ([timestamp, 1]);
                    }
                }
                break;
        }

        // insert entries with y value equal to 0 into chartData at the specified interval
        chartData.reverse ();
        var chartDataIndex = 0;
        var timestamp1, timestamp2, arr1, arr2, intermArr;
        while (chartData.length !== 0 && chartDataIndex < chartData.length - 1) {

            timestamp1 = chartData[chartDataIndex][0];
            timestamp2 = chartData[chartDataIndex + 1][0];

            switch (binSize) {
                case 'hour-bin-size':
                    arr1 = chartData.slice (0, chartDataIndex + 1);
                    arr2 = chartData.slice (chartDataIndex + 1, chartData.length);
                    intermArr = getZeroEntriesBetween (
                        timestamp1, timestamp2, 'hour', false, false, showMarker);
                    if (intermArr.length !== 0)
                        chartData = arr1.concat (intermArr, arr2);
                    chartDataIndex += intermArr.length + 1;
                    break;
                case 'day-bin-size':
                    arr1 = chartData.slice (0, chartDataIndex + 1);
                    arr2 = chartData.slice (chartDataIndex + 1, chartData.length);
                    intermArr = getZeroEntriesBetween (
                        timestamp1, timestamp2, 'day', false, false, showMarker);
                    if (intermArr.length !== 0)
                        chartData = arr1.concat (intermArr, arr2);
                    chartDataIndex += intermArr.length + 1;
                    break;
                case 'week-bin-size':
                    arr1 = chartData.slice (0, chartDataIndex + 1);
                    arr2 = chartData.slice (chartDataIndex + 1, chartData.length);
                    intermArr = getZeroEntriesBetween (
                        timestamp1, timestamp2, 'week', false, false, showMarker);
                    if (intermArr.length !== 0)
                        chartData = arr1.concat (intermArr, arr2);
                    chartDataIndex += intermArr.length + 1;
                    break;
                case 'month-bin-size':
                    arr1 = chartData.slice (0, chartDataIndex + 1);
                    arr2 = chartData.slice (chartDataIndex + 1, chartData.length);
                    intermArr = getZeroEntriesBetween (
                        timestamp1, timestamp2, 'month', false, false, showMarker);
                    if (intermArr.length !== 0)
                        chartData = arr1.concat (intermArr, arr2);
                    chartDataIndex += intermArr.length + 1;
                    break;
            }

        }


        return {
            chartData: chartData
        };
    }

    /*
    Helper function for jqplot used to widen the date range if the user selected
    begin and end dates are the same.
    */
    function shiftTimeStampOneInterval (timestamp, binSize, forward) {
        var newTimestamp = timestamp;
        switch (binSize) {
            case 'hour-bin-size':
            case 'day-bin-size':
                var msPerDay = 86400 * 1000;
                if (forward)
                    newTimestamp += msPerDay;
                else
                    newTimestamp -= msPerDay;
                break;
            case 'week-bin-size':
                var msPerWeek = 7 * 86400 * 1000;
                if (forward)
                    newTimestamp += msPerWeek;
                else
                    newTimestamp -= msPerWeek;
                break;
            case 'month-bin-size':
                var date = new Date (timestamp);
                var M = date.getMonth () + 1;
                var Y = date.getFullYear ();
                if (forward) {
                    M++;
                    if (M === 13) {
                        M = 0;
                        Y++;
                    }
                } else {
                    M--;
                    if (M === 0) {
                        M = 12;
                        Y--;
                    }
                }
                newTimestamp = (new Date (Y, M - 1, 1, 0, 0, 0, 0)).getTime ();
                break;
        }
        return newTimestamp;
    }

    /*
    Calls getZeroEntriesBetween () to pad the left and right side of the chart data
    with entries having y values equal to 0 and x values increasing by the bin size.
    Parameter:
        binSize - user selected, determines x value spacing
        showMarker - if false, a maximum of two entries will be added to the left
            and right side of the chart data.
    */
    function fillZeroEntries (
        startTimestamp, endTimestamp, binSize, chartData, showMarker) {

        var binType = binSize.match (/^[^-]+/)[0];

        if (chartData[0] === null) {
            chartData = getZeroEntriesBetween (
                startTimestamp, endTimestamp, binType, true, true, showMarker);
            return chartData;
        }

        var chartStartTimestamp = chartData[0][0];
        var chartEndTimestamp = chartData[chartData.length - 1][0];
        if (startTimestamp < chartStartTimestamp) {
            var arr = getZeroEntriesBetween (
                startTimestamp, chartStartTimestamp, binType, true, false, showMarker);
            if (arr.length !== 0) {
                chartData = arr.concat (chartData);
            }
        }
        if (endTimestamp > chartEndTimestamp) {
            var arr = getZeroEntriesBetween (
                chartEndTimestamp, endTimestamp, binType, false, true, showMarker);
            if (arr.length !== 0)
                chartData = chartData.concat (arr);
        }

        return chartData;
    }


    /*
    Returns a dictionary containing the number of hours, days, months, and years between
    the start and end timestamps.
    */
    function countHoursDaysMonthsYears (startTimestamp, endTimestamp) {

        var dateRange =
            endTimestamp - startTimestamp;


        // get starting and ending months and years
        var startDate = new Date (startTimestamp);
        var startMonth = startDate.getMonth () + 1;
        var startYear = startDate.getFullYear ();
        var endDate = new Date (endTimestamp);
        var endMonth = endDate.getMonth () + 1;
        var endYear = endDate.getFullYear ();


        // count hours, days, weeks, months
        var hours = dateRange / 1000 / 60 / 60;
        var days = hours / 24;
        var weeks = days / 7;
        var months;
        var yearCount = endYear - startYear;
        if (yearCount === 0) {
            months = endMonth - startMonth + 1;
        } else if (yearCount === 1) {
            months = endMonth + ((12 - startMonth) + 1) + 1;
        } else { // yearCount > 1
            months = (endMonth + ((12 - startMonth) + 1)) + (12 * (yearCount - 2)) + 1;
        }

        if (hours === 0) hours = 24;
        if (days === 0) hours = 1;
        if (weeks === 0) weeks = 1;
        if (months === 0) months = 1;

        return {
            'hours': hours,
            'days': days,
            'months': months,
            'years': yearCount + 1
        };
    }

    /*
    Retrieves the user selected start and end timestamps from the DOM.
    Parameter:
        binSize - if set, the start and end timestamps will be rounded down to the
            nearest hour, day, week, or month, respectively
    */
    function getStartEndTimestamp (binSize /* optional */) {
        var startTimestamp =
            ($('#chart-datepicker-from').datepicker ('getDate').valueOf ());
        var endTimestamp =
            ($('#chart-datepicker-to').datepicker ('getDate').valueOf ());
        if (endTimestamp < startTimestamp)
            endTimestamp = startTimestamp;

        // returns timestamp of nearest previous day at 12am
        function getPreviousDayTs (timestamp) {
            var date = new Date (timestamp);
            var M = date.getMonth () + 1;
            var Y = date.getFullYear ();
            var D = date.getDate ();
            return (new Date (Y, M - 1, D, 0, 0, 0, 0)).getTime ();
        }

        // returns timestamp of nearest previous Sunday at 12am
        function getPreviousWeekTs (timestamp) {
            var date = new Date (timestamp);
            var M = date.getMonth () + 1;
            var D = date.getDate ();
            var Y = date.getFullYear ();
            var newTimestamp = (new Date (Y, M - 1, D, 0, 0, 0, 0)).getTime ();
            var date = new Date (newTimestamp);
            var day = date.getDay ();
            var msPerWeek = 86400 * 1000;
            newTimestamp -= day * msPerWeek;
            return newTimestamp;
        }

        // returns timestamp of nearest previous 1st of month at 12am
        function getPreviousMonthTs (timestamp) {
            var date = new Date (timestamp);
            var M = date.getMonth () + 1;
            var Y = date.getFullYear ();
            return (new Date (Y, M - 1, 1, 0, 0, 0, 0)).getTime ();
        }

        // round dates to nearest interval boundary
        if (typeof binSize !== 'undefined') {
            switch (binSize) {
                case 'hour-bin-size':
                    break;
                case 'day-bin-size':
                    startTimestamp = getPreviousDayTs (startTimestamp);
                    endTimestamp = getPreviousDayTs (endTimestamp);
                    break;
                case 'week-bin-size':
                    startTimestamp = getPreviousWeekTs (startTimestamp);
                    endTimestamp = getPreviousWeekTs (endTimestamp);
                    break;
                case 'month-bin-size':
                    startTimestamp = getPreviousMonthTs (startTimestamp);
                    endTimestamp = getPreviousMonthTs (endTimestamp);
                    break;
            }
        }

        return {
            'startTimestamp': startTimestamp,
            'endTimestamp': endTimestamp
        }

    }

    /*
    Helper function for plotData. Determines the resolution of the graph.
    Returns false if the date range must be sliced into more than the set number of
    intervals, true otherwise.
    If this function returns false, markers should not be displayed.
    */
    function getShowMarkerSetting (binSize, countDict) {
        var hours = countDict['hours'];
        var days = countDict['days'];
        var months = countDict['months'];
        var weeks = Math.floor (days / 7);
        var years = countDict['years'];

        var showMarker = true;
        switch (binSize) {
            case 'hour-bin-size':
                if (hours > 110)
                    showMarker = false;
                break;
            case 'day-bin-size':
                if (days > 110)
                    showMarker = false;
                break;
            case 'week-bin-size':
                if (weeks > 110)
                    showMarker = false;
                break;
            case 'month-bin-size':
                if (months > 110)
                    showMarker = false;
                break;
        }
        return showMarker;
    }

    function getLongMonthName (monthNum) {
        monthNum = + monthNum % 12;
        var monthName = "";
        switch (monthNum) {
            case 1:
                monthName = 'January';
                break;
            case 2:
                monthName = 'February';
                break;
            case 3:
                monthName = 'March';
                break;
            case 4:
                monthName = 'April';
                break;
            case 5:
                monthName = 'May';
                break;
            case 6:
                monthName = 'June';
                break;
            case 7:
                monthName = 'July';
                break;
            case 8:
                monthName = 'August';
                break;
            case 9:
                monthName = 'September';
                break;
            case 10:
                monthName = 'October';
                break;
            case 11:
                monthName = 'November';
                break;
            case 12:
                monthName = 'December';
                break;
        }
        return monthName;
    }

    function getShortMonthName (monthNum) {
        monthNum = + monthNum;
        var monthName = "";
        switch (monthNum) {
            case 1:
                monthName = 'Jan';
                break;
            case 2:
                monthName = 'Feb';
                break;
            case 3:
                monthName = 'Mar';
                break;
            case 4:
                monthName = 'Apr';
                break;
            case 5:
                monthName = 'May';
                break;
            case 6:
                monthName = 'Jun';
                break;
            case 7:
                monthName = 'Jul';
                break;
            case 8:
                monthName = 'Aug';
                break;
            case 9:
                monthName = 'Sep';
                break;
            case 10:
                monthName = 'Oct';
                break;
            case 11:
                monthName = 'Nov';
                break;
            case 12:
                monthName = 'Dec';
                break;
        }
        return monthName;
    }


    /*
    Returns an array of ticks acceptable as input to jqplot. The number of ticks
    and the ticks' labels depend on the user selected bin size and date range.
    */
    function getTicks (startTimestamp, endTimestamp, binSize, countDict) {
        var hours = countDict['hours'];
        var days = countDict['days'];
        var months = countDict['months'];
        var weeks = Math.floor (days / 7);
        var years = countDict['years'];


        /*
        Returns an array of tick entries which is acceptable to jqplot. Ticks will
        be labelled with the month and day from their corresponding timestamp.
        Tick entries will be between specified timestamps increasing at the
        specified interval.
        Parameters:
            interval - the number of days between each tick
        */
        function getDayTicksBetween (startTimestamp, endTimestamp, interval) {
            var date = new Date (startTimestamp);
            var D = date.getDate ();
            var M = date.getMonth () + 1;
            var monthStr = getShortMonthName (M);
            ticks.push ([startTimestamp, monthStr + ' ' + D]);
            var timestamp = startTimestamp;
            timestamp += interval;
            while (timestamp <= endTimestamp) {
                date = new Date (timestamp);
                D = date.getDate ();
                M = date.getMonth () + 1;
                monthStr = getShortMonthName (M);
                ticks.push ([timestamp, monthStr + ' ' + D]);

                timestamp += interval;

                if (timestamp > endTimestamp)
                    ticks.push ([endTimestamp, '']);
            }
            return ticks;
        }

        /*
        Returns an array of tick entries which is acceptable to jqplot. Ticks will
        be labelled with the month from their corresponding timestamp.
        Tick entries will be between specified timestamps increasing at the
        specified interval. If suppressYear is true, the tick's label will not
        include the year.
        Parameters:
            interval - the number of months between each tick
            suppressYear - a boolean
        Precondition: interval <= 12
        */
        function getMonthTicksBetween (
            startTimestamp, endTimestamp, interval, suppressYear) {

            var date1 = new Date (startTimestamp);
            var date2 = new Date (endTimestamp);
            var M1 = date1.getMonth () + 1;
            var D1 = date1.getDate ();
            var Y1 = date1.getFullYear ();
            var M2 = date2.getMonth () + 1;
            var D2 = date2.getDate ();
            var Y2 = date2.getFullYear ();
            var endMonth = date2.getMonth ();
            var endYear = date2.getYear ();
            var beginString = (M1 + '-' + 1 + '-' + Y1);
            var endString = (M2 + '-' + 1 + '-' + Y2);
            var monthStr = getShortMonthName (M1);
            if (!suppressYear) monthStr += ' ' + Y1;
            var timestamp = (new Date (Y1, M1 - 1, 1, 0, 0, 0, 0)).getTime ();
            if (timestamp === startTimestamp)
                ticks.push ([startTimestamp, monthStr]);
            else
                ticks.push ([startTimestamp, '']);

            while (true) {
                M1 += interval;
                if (M1 > 12) {
                    Y1++;
                    M1 = M1 - 12;
                }

                beginString = M1 + '-' + 1 + '-' + Y1;

                timestamp = (new Date (Y1, M1 - 1, 1, 0, 0, 0, 0)).getTime ();
                monthStr = getShortMonthName (M1);
                if (!suppressYear) monthStr += ' ' + Y1;

                if (beginString === endString || Y1 > Y2 || (Y1 === Y2 && M1 > M2)) {
                    if (beginString !== endString)
                        ticks.push ([endTimestamp, ""]);
                    else
                        ticks.push ([timestamp, monthStr]);
                    break;
                } else {
                    ticks.push ([timestamp, monthStr]);
                }
            }
            return ticks;
        }

        var ticks = [];
        switch (binSize) {
            case 'hour-bin-size':
                if (hours < 72) {
                    var date = new Date (startTimestamp);
                    ticks.push ([startTimestamp, '12:00 AM']);
                    var timestamp = startTimestamp;
                    var interval = msPerDay / 2;
                    var period = 'PM';
                    timestamp += interval;
                    while (timestamp <= endTimestamp) {
                        ticks.push ([timestamp, '12:00 ' + period]);
                        if (period === 'PM')
                            period = 'AM';
                        else
                            period = 'PM';
                        timestamp += interval;
                    }
                } else if (days <= 7) {
                    ticks =
                        getDayTicksBetween (startTimestamp, endTimestamp, msPerDay);
                } else if (days <= 62) {
                    ticks = getDayTicksBetween (
                        startTimestamp, endTimestamp, Math.ceil (days / 7) * msPerDay);
                } else if (days <= 182) {
                    ticks = getDayTicksBetween (
                        startTimestamp, endTimestamp, Math.ceil (weeks / 7) *  7 * msPerDay);
                } else if (days < 365) {
                    ticks =
                        getMonthTicksBetween (startTimestamp, endTimestamp, 1, true);
                } else {
                    ticks = getMonthTicksBetween (
                        startTimestamp, endTimestamp, Math.ceil (months / 7), false);
                }
                break;
            case 'day-bin-size':
                if (days <= 7) {
                    ticks =
                        getDayTicksBetween (startTimestamp, endTimestamp, msPerDay);
                } else if (days <= 62) {
                    ticks = getDayTicksBetween (
                        startTimestamp, endTimestamp, Math.ceil (days / 7) * msPerDay);
                } else if (days <= 182) {
                    ticks = getDayTicksBetween (
                        startTimestamp, endTimestamp, Math.ceil (weeks / 7) *  7 * msPerDay);
                } else if (days < 365) {
                    ticks =
                        getMonthTicksBetween (startTimestamp, endTimestamp, 1, true);
                } else {
                    ticks = getMonthTicksBetween (
                        startTimestamp, endTimestamp, Math.ceil (months / 7), false);
                }
                break;
            case 'week-bin-size':
                if (days <= 45) {
                    ticks =
                        getDayTicksBetween (startTimestamp, endTimestamp, 7 * msPerDay);
                } else if (days <= 62) {
                    ticks = getDayTicksBetween (
                        startTimestamp, endTimestamp, Math.ceil (days / 7) * msPerDay);
                } else if (days <= 182) {
                    ticks = getDayTicksBetween (
                        startTimestamp, endTimestamp, Math.ceil (weeks / 7) * 7 * msPerDay);
                } else if (days < 365) {
                    ticks =
                        getMonthTicksBetween (startTimestamp, endTimestamp, 1, true);
                } else {
                    ticks = getMonthTicksBetween (
                        startTimestamp, endTimestamp, Math.ceil (months / 7), false);
                }
                break;
            case 'month-bin-size':
                if (days < 365) {
                    ticks = getMonthTicksBetween (
                        startTimestamp, endTimestamp, Math.ceil (months / 7), true);
                } else {
                    ticks = getMonthTicksBetween (
                        startTimestamp, endTimestamp, Math.ceil (months / 7), false);
                }
                break;
        }

        return ticks;

    }


    /*
    Plots event data retrieved by getEventsBetweenDates ().
    If two metrics are selected by the user, plotData will plot two lines.
    Parameter:
        args - a dictionary containing optional parameters.
            redraw - an optional parameter which can be contained in args. If set to
                true, the chart will be cleared before the plotting.
    */
    function plotData (args /* optional */) {
        if (typeof args !== 'undefined') {
            redraw = typeof args['redraw'] === 'undefined' ?
                false : args['redraw'];
        } else { // defaults
            redraw = false;
        }

        // retrieve user selected values
        var binSize = $('#bin-size-button-set a.disabled-link').attr ('id');
        var tsDict = getStartEndTimestamp (binSize);
        var startTimestamp = tsDict['startTimestamp'];
        var endTimestamp = tsDict['endTimestamp'];
        var type1 = $('#first-metric').val ();
        var type2 = $('#second-metric').val ();


        // default settings
        var labelFormat = "%b %Y";
        var yTickCount = 3;
        var showXTicks = true;
        var showMarker = false;
        var tickInterval = null;

        // graph at least 1 interval
        if (startTimestamp === endTimestamp)
            endTimestamp = shiftTimeStampOneInterval (endTimestamp, binSize, true);

        var min = startTimestamp;
        var max = endTimestamp;

        // determine label format and number of ticks based on data
        var countDict = countHoursDaysMonthsYears (min, max);
        var ticks = getTicks (min, max, binSize, countDict);
        showMarker = getShowMarkerSetting (binSize, countDict);

        if (ticks[0][0] < min)
            min = ticks[0][0]
        if (ticks[ticks.length - 1][0] > max)
            max = ticks[ticks.length - 1][0];

        // get chartData for each user specified type
        var color = ['#7EB2E6']; // color of line 1
        var dataDict = groupChartData (eventData, binSize, type1, showMarker);
        var chartData = [dataDict['chartData']];
        if (type2 !== '') {
            color.push ('#C2597C'); // color of line 2
            dataDict = groupChartData (eventData, binSize, type2, showMarker);
            chartData.push (dataDict['chartData']);
        }


        // if no chartData exists of specified type, don't plot it
        var noChartData = true;
        for (var i in chartData) {
            if (chartData[i].length === 0) {
                chartData[i] = [null];
            } else {
                noChartData = false;
            }
        }

        // pad left and right side of data with entries having y value equal to 0
        chartData[0] = fillZeroEntries (
            min, max, binSize, chartData[0], showMarker);
        if (chartData.length === 2)
            chartData[1] = fillZeroEntries (
                min, max, binSize, chartData[1], showMarker);



        jqplotConfig =  {
            seriesDefaults: {
                showMarker: showMarker,
                shadow: false,
                shadowAngle: 0,
                shadowOffset: 0,
                shadowDepth: 0,
                shadowAlpha: 0,
                markerOptions: {
                    shadow: false,
                    shadowAngle: 0,
                    shadowOffset: 0,
                    shadowDepth: 0,
                    shadowAlpha: 0
                }
            },
            axesDefaults: {
                x2axis: {
                    show: false
                }
            },
            seriesColors: color,
            series:[{
                label: 'Events',
                }
            ],
            legend: {
                show: false
            },
            grid: {
                drawGridLines: false,
                gridLineColor: '#ffffff',
                borderColor: '#999',
                borderWidth: 1,
                background: '#ffffff',
                shadow: false
            },
            axes: {
                xaxis: {
                    renderer: $.jqplot.DateAxisRenderer,
                    tickOptions: {
                        angle: -90
                    },
                    showTicks: showXTicks,
                    ticks: ticks,
                    min: min,
                    max: max,
                    padMin: 150,
                    padMax: 150
                },
                yaxis: {
                    pad: 1.05,
                    numberTicks: yTickCount,
                    tickOptions: {formatString: '%d'},
                    min: 0
                }
            }
        }

        if (noChartData) {
            jqplotConfig.axes.yaxis['max'] = 1;
            jqplotConfig.axes.yaxis['numberTicks'] = 2;
        }

        // plot chartData
        feedChart = $.jqplot ('chart', chartData, jqplotConfig);

        if (redraw) {
            feedChart.replot (); // clear previous plot and plot again
        }
    }


    /*
    Extracts saved settings from cookie and sets chart settings to them.
    */
    function setSettingsFromCookie () {
        var startDate = $.cookie ('startDate');
        var endDate = $.cookie ('endDate');
        var binSize = $.cookie ('binSize');
        var firstMetric = $.cookie ('firstMetric');
        var secondMetric = $.cookie ('secondMetric');

        if (startDate !== null) {
            $('#chart-datepicker-from').datepicker ('setDate', startDate);
        }
        if (endDate !== null) {
            $('#chart-datepicker-to').datepicker ('setDate', endDate);
        }
        if (binSize !== null) {
            $('#chart-container a.disabled-link').removeClass ('disabled-link');
            $('#chart-container #' + binSize).addClass ('disabled-link');
        }
        if (firstMetric !== null) {
            $('#first-metric').find ('option:selected').removeAttr ('selected');
            $('#first-metric').children ().each (function () {
                if ($(this).val () === firstMetric) {
                    $(this).attr ('selected', 'selected');
                    return false;
                }
            });
        }
        if (secondMetric !== null) {
            $('#second-metric').find ('option:selected').removeAttr ('selected');
            if (secondMetric === '') {
                $('#second-metric').first ().attr ('selected', 'selected');
            } else {
                $('#second-metric').children ().each (function () {
                    if ($(this).val () === secondMetric) {
                        $(this).attr ('selected', 'selected');
                        return false;
                    }
                });
            }
        }
    }

    $('#show-chart').click (function (evt) {
        evt.preventDefault();
        $('#chart-container').slideDown (450);
        feedChart.replot ({ resetAxes: false });
        $(this).hide ();
        $('#hide-chart').show ();
    });

    $('#hide-chart').click (function (evt) {
        evt.preventDefault();
        $('#chart-container').slideUp (450);
        $(this).hide ();
        $('#show-chart').show ();
    });

    // bin size button set behavior
    $('#chart-container a.x2-button').click (function (evt) {
        evt.preventDefault();
        if (!$(this).hasClass ('disabled-link')) {
            $('#chart-container a.disabled-link').removeClass ('disabled-link');
            $(this).addClass ('disabled-link');
            if (eventData !== null) {
                plotData ({redraw: true});
            }
            var binSize = $('#bin-size-button-set a.disabled-link').attr ('id');
            $.cookie ('binSize', binSize);
        }
    });

    // clear second metric and redraw graph using only first metric
    $('#clear-metric-button').click (function (evt) {
        evt.preventDefault();
        $('#second-metric-default').attr ('selected', 'selected');
        plotData ({redraw: true});
        $.cookie ('secondMetric', '');
    });

    // setup metric selectors behavior
    $('#first-metric').change (function () {
        plotData ({redraw: true});
        $.cookie ('firstMetric', $(this).val ());
    });
    $('#second-metric').change (function () {
        plotData ({redraw: true});
        $.cookie ('secondMetric', $(this).val ());
    });

    // setup datepickers and initialize range to previous week
    $('#chart-datepicker-from').datepicker({
				constrainInput: false,
				showOtherMonths: true,
				selectOtherMonths: true,
				dateFormat: yii.datePickerFormat
    });
    $('#chart-datepicker-from').datepicker('setDate', new Date ());
    $('#chart-datepicker-from').datepicker('setDate', '-7d'); // default start date
    $('#chart-datepicker-to').datepicker({
				constrainInput: false,
				showOtherMonths: true,
				selectOtherMonths: true,
				dateFormat: yii.datePickerFormat
    });
    $('#chart-datepicker-to').datepicker('setDate', new Date ()); // default end date

    /*
    Save setting in cookie and replot
    */
    $('#chart-datepicker-from').datepicker ('option', 'onSelect', function () {
        getEventsBetweenDates ($('#first-metric').val (), true);
        var startDate = $('#chart-datepicker-from').datepicker (
            { dateFormat: yii.datePickerFormat }).val ();
        $.cookie ('startDate', startDate);
    });

    /*
    Save setting in cookie and replot
    */
    $('#chart-datepicker-to').datepicker ('option', 'onSelect', function () {
        getEventsBetweenDates ($('#first-metric').val (), true);
        var endDate = $('#chart-datepicker-to').datepicker (
            { dateFormat: yii.datePickerFormat }).val ();
        $.cookie ('endDate', endDate);
    });


    // redraw graph on window resize
    $(window).on ('resize', function () {
        if ($('#chart-container').is (':visible'))
            feedChart.replot ({ resetAxes: false });
    });

    setSettingsFromCookie (); // fill settings with saved settings

    getEventsBetweenDates ('any', false); // populate default graph


}


$(document).on ('ready', function whatsNewMain () {
    setupEditorBehavior ();
    setupActivityFeed ();
    updateEventList ();
    setupFeedColorPickers ();
    setupChartBehavior ();
    attachmentMenuBehavior ();
});



