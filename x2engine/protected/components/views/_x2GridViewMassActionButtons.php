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

/*
Parameters:
    massActions - array of strings - list of available mass actions to select from
        ('delete' | 'newList' | 'addToList' | 'tag' | 'updateField')
    gridId - the id property of the X2GridView instance
    modelName - the modelName property of the X2GridView instance
    selectedAction - string - if set, used to select option from mass actions dropdown
    gridObj - object - the x2gridview instance
*/

/* x2prostart */
if (in_array ('tag', $massActions)) {
    Yii::app()->clientScript->registerCssFile (
        Yii::app()->getTheme()->getBaseUrl().'/css/x2tags.css');
    Yii::app()->clientScript->registerScriptFile (
        Yii::app()->getBaseUrl().'/js/X2Tags/TagContainer.js', CClientScript::POS_END);
    Yii::app()->clientScript->registerScriptFile (
        Yii::app()->getBaseUrl().'/js/X2Tags/TagCreationContainer.js', CClientScript::POS_END);
    Yii::app()->clientScript->registerScriptFile (
        Yii::app()->getBaseUrl().'/js/X2Tags/MassActionTagsContainer.js', CClientScript::POS_END);
}

if (in_array ('updateField', $massActions)) {
    // script needed by Yii timepicker widget
    Yii::app()->clientScript->registerScriptFile (
        Yii::app()->getBaseUrl().'/js/jquery-ui-timepicker-addon.js');

    // needed by c star widget
    Yii::app()->clientScript->registerCssFile (
        Yii::app()->theme->getBaseUrl().'/css/rating/jquery.rating.css');
    Yii::app()->clientScript->registerCoreScript('rating');

}
/* x2proend */

$massActionLabels = array (
    'newList' => Yii::t ('app', 'New list from selection'),
    'addToList' => Yii::t ('app', 'Add selected to list'),
    'removeFromList' => Yii::t ('app', 'Remove selected from list'),
/* x2prostart */
    'tag' => Yii::t ('app', 'Tag selected'),
    'updateField' => Yii::t ('app', 'Update fields of selected'),
    'delete' => Yii::t ('app', 'Delete selected'),
/* x2proend */
);

AuxLib::registerTranslationsScript ('massActions', array (
    'addedItems' => 'Added items to list',
    'addToList' => 'Add selected to list',
    'removeFromList' => 'Remove selected from list',
    'newList' => 'Create new list from selected',
    'add' => 'Add to list',
    'remove' => 'Remove from list',
    'noticeFlashList' => 'Mass action exectuted with',
    'errorFlashList' => 'Mass action exectuted with',
    'noticeItemName' => 'warnings',
    'errorItemName' => 'errors',
    'successItemName' => 'Close',
    'blankListNameError' => 'Cannot be left blank',
    'close' => 'Close',
    'cancel' => 'Cancel',
    'create' => 'Create',
    'tag' => 'Tag',
    'update' => 'Update',
    'tagSelected' => 'Tag selected',
    'deleteSelected' => 'Delete selected',
    'delete' => 'Delete',
    'updateField' => 'Update fields of selected',
    'emptyTagError' => 'At least one tag must be included',
));

Yii::app()->clientScript->registerCss ('massActionsCss', "


/*
Flashes container
*/

#x2-gridview-flashes-container.fixed-flashes-container {
    position: fixed;
    opacity: 0.9;
    bottom: 5px;
}

#x2-gridview-flashes-container {
    margin-top: 5px;
    margin-right: 5px;
}

#x2-gridview-flashes-container > div {
    margin-top: 5px;
}

#x2-gridview-flashes-container .flash-list-header {
    margin-bottom: 4px;
}

#x2-gridview-flashes-container .x2-gridview-flashes-list {
    clear: both;
    margin-bottom: 5px;
}

#x2-gridview-flashes-container .flash-list-left-arrow,
#x2-gridview-flashes-container .flash-list-down-arrow {
    margin-left: 6px;
    margin-top: 3px;
}

/* x2prostart */
/*
update fields dialog
*/

#x2-gridview-update-field-dialog select {
    margin-right: 4px;
    margin-top: 2px;
    margin-bottom: 2px;
}

#update-fields-inputs-container {
    margin-top: 4px;
}

#update-fields-inputs-container .update-fields-field-input-container {
    vertical-align: middle;
    margin-top: 0px;
}

#update-fields-inputs-container .updating-field-input-anim {
    margin-left: 40px;
}
/* x2proend */

/*
buttons 
*/

#mass-action-more-button-container .x2-down-arrow {
    margin-left: 30px;
    margin-top: 11px;
}

#mass-action-more-button-container .more-button-arrow {
    height: 5px;
}

#mass-action-more-button-container .more-button-label {
    display: inline !important;
    float: left;
    margin-right:5px;
}

#mass-action-more-button-container {
    margin: 0 5px 0 0;
    display: inline-block;
}

#mass-action-more-button-container button {
    border: 1px solid #888;
    border-bottom-color: #555;
    display: inline;
    height: 26px;
}

/* x2prostart */
#mass-action-button-delete span {
    background: url('".Yii::app()->getTheme()->getBaseUrl().'/images/icons/mass-action-delete.png'.
        "') 4px center no-repeat;
    background-size: 18px 17px;
    height: 24px;
    width: 24px;
}

#mass-action-button-tag {
    margin-left: -4px;
}

#mass-action-button-tag span {
    background: url('".Yii::app()->getTheme()->getBaseUrl().'/images/icons/mass-action-tag.png'.
        "') 3px center no-repeat;
    height: 24px;
    width: 24px;
}
/* x2proend */


/*
more drop down list
*/

#more-drop-down-list.stuck {
    position: absolute !important;
    top: 74px !important;
}

#more-drop-down-list {
    position: fixed;
    top: 67px;
    z-index: 99;
    list-style-type: none;
    background: #fff;
    border: 1px solid #999;
    -moz-box-shadow: 0 0 15px 0 rgba(0,0,0,0.5);
    -webkit-box-shadow: 0 0 15px 0 rgba(0,0,0,0.5);
    box-shadow: 0 0 15px 0 rgba(0,0,0,0.5);
    padding: 5px 0px 5px 0px;
    clip: rect(0px,1000px,1000px,-10px);
}

#more-drop-down-list li {
    line-height: 17px;
    padding: 0 10px 0 10px;
    cursor: default;
}
#more-drop-down-list li:hover {
    background: #eee;
}

/*
general mass actions styling
*/

#mass-action-dialog-loading-anim {
    margin-right: 30px;
}

#x2-gridview-mass-action-buttons .dialog-help-text {
    margin-bottom: 5px;
}

#x2-gridview-mass-action-buttons {
    margin: 0 5px 0 0;
    display: inline-block;
}
");

$gridObj->addToBeforeAjaxUpdate ("
    /* x2prostart */ 
    if ($.inArray ('tag', x2.massActions._massActions)) x2.massActions.tagContainer.destructor ();
    /* x2proend */
    
    $('.mass-action-dialog').each (function () {
        x2.massActions.DEBUG && console.log ('destroying dialog loop');
        if ($(this).closest ('.ui-dialog').length) {
            x2.massActions.DEBUG && console.log ('destroying dialog');
            $(this).dialog ('destroy');
        }
    });

    x2.massActions._previouslySelectedRecords = selectedRecords; // save to preserve checks

    $('#x2-gridview-updating-anim').show ();
    $('#x2-gridview-mass-action-buttons').find ('.mass-action-button').unbind ('click');
");

$gridObj->addToAfterAjaxUpdate ("
    gridViewMassActionsMain (); 
    $('#x2-gridview-updating-anim').hide ();
");

Yii::app()->clientScript->registerScript ('massActionsScript', "


/***********************************************************************
* Flashes setup functions
***********************************************************************/

/*
Display flashes of a given type
Parameters:
    key - string - the type of flash ('notice' | 'error' | 'success')
    flashes - array of strings - flash messages which will be displayed
*/
x2.massActions._displayKeyFlashes = function (key, flashes) {
    x2.massActions.DEBUG && console.log ('x2.massActions._displayKeyFlashes');
    var flashNum = flashes.length;
    var hideList = false;


    if (flashNum > 3) { // show header and make flash list expandable

        // add list header
        $('#x2-gridview-flash-' + key + '-container').append (
            $('<p>', {
                'class': 'flash-list-header left',
                text: x2.massActions.translations[key + 'FlashList'] + ' ' + flashNum + ' ' +
                    x2.massActions.translations[key + 'ItemName']
            }),
            $('<img>', {
                'class': 'flash-list-left-arrow',
                'src': '".Yii::app()->getTheme()->getBaseUrl().'/images/icons/Expand_Widget.png'."',
                'alt': '<'
            }),
            $('<img>', {
                'class': 'flash-list-down-arrow',
                'style': 'display: none;',
                'src': '".Yii::app()->getTheme()->getBaseUrl().'/images/icons/Collapse_Widget.png'.
                    "',
                'alt': 'v'
            })
        );

        // set up flashes list expand and collapse behavior
        $('#x2-gridview-flash-' + key + '-container').find ('.flash-list-left-arrow').
            click (function () {

            $(this).hide ();
            $(this).next ().show ();
            $('#x2-gridview-flashes-' + key + '-list').show ();
        });
        $('#x2-gridview-flash-' + key + '-container').find ('.flash-list-down-arrow').
            click (function () {

            $(this).hide ();
            $(this).prev ().show ();
            $('#x2-gridview-flashes-' + key + '-list').hide ();
        });

        hideList = true;
    }

    // build flashes list
    $('#x2-gridview-flash-' + key + '-container').append ($('<ul>', {
        id: 'x2-gridview-flashes-' + key + '-list',
        'class': 'x2-gridview-flashes-list',
        style: (hideList ? 'display: none;' : '')
    }));
    for (var i in flashes) {
        x2.massActions.DEBUG && console.log ('x2.massActions._displayKeyFlashes: i = ' + i);
        $('#x2-gridview-flashes-' + key + '-list').append ($('<li>', {
            text: flashes[i]
        }));
    }

    if (key === 'success') { // other types of flash containers have close buttons
        if (x2.massActions.timeout) window.clearTimeout (x2.massActions.timeout);
        x2.massActions.timeout = setTimeout (
            function () { $('#x2-gridview-flash-' + key + '-container').fadeOut (3000); }, 2000);
    }
}

/*
Append flash section container div to parent element
*/
x2.massActions._appendFlashSectionContainer = function (key, parent) {
    $(parent).append (
        $('<div>', {
            id: 'x2-gridview-flash-' + key + '-container',
            'class': 'flash-' + key 
        })
    )

    // add close button, not needed for success flash container since it fades out
    if (key === 'notice' || key === 'error') {
        $('#x2-gridview-flash-' + key + '-container').append (
            $('<img>', {
                id: key + '-container-close-button',
                'class': 'right',
                title: x2.massActions.translations['close'],
                'src': '".Yii::app()->getTheme()->getBaseUrl().'/images/icons/Close_Widget.png'."',
                alt: '[x]'
            })
        );
    
        // set up close button behavior
        $('#' + key + '-container-close-button').click (function () {
            $('#x2-gridview-flash-' + key + '-container').fadeOut ();
        });
    }
};

/*
Build the flash container, fill it with given flashes
*/
x2.massActions._displayFlashes = function (flashes) {
    x2.massActions.DEBUG && console.log ('x2.massActions._displayFlashes: flashes = ');
    x2.massActions.DEBUG && console.log (flashes);
    if (!flashes['success'] && !flashes['notice'] && !flashes['error']) return;

    // remove previous flashes container
    if ($('#x2-gridview-flashes-container').length) {
        $('#x2-gridview-flashes-container').remove ();
    }

    // build new flashes container
    $('#content-container').append (
        $('<div>', {
            id: 'x2-gridview-flashes-container'
        })
    ); 
    
    // fill container with flashes
    if (flashes['success'] && flashes['success'].length > 0) {
        x2.massActions._appendFlashSectionContainer (
            'success', $('#x2-gridview-flashes-container'));
        var successFlashes = flashes['success'];
        x2.massActions._displayKeyFlashes ('success', successFlashes);
    }
    if (flashes['notice'] && flashes['notice'].length > 0) {
        x2.massActions._appendFlashSectionContainer (
            'notice', $('#x2-gridview-flashes-container'));
        var noticeFlashes = flashes['notice'];
        x2.massActions._displayKeyFlashes ('notice', noticeFlashes);
    }
    if (flashes['error'] && flashes['error'].length > 0) {
        x2.massActions._appendFlashSectionContainer ('error', $('#x2-gridview-flashes-container'));
        var errorFlashes = flashes['error'];
        x2.massActions._displayKeyFlashes ('error', errorFlashes);
    }

    var flashesContainer = $('#x2-gridview-flashes-container');
    $('#content-container').attr (
        'style', 'padding-bottom: ' + $(flashesContainer).height () + 'px;');
    $(flashesContainer).width ($('#content-container').width () - 5);
    $(window).unbind ('resize.contentContainer').bind ('resize.contentContainer', function () {
        $(flashesContainer).width ($('#content-container').width () - 5);
    });

    x2.massActions.DEBUG && console.log ('$(flashesContainer).positoin ().top = ');
    x2.massActions.DEBUG && console.log ($(flashesContainer).position ().top);

    if (!x2.massActions._checkFlashesUnsticky ()) {
        $(window).unbind ('scroll', x2.massActions._checkFlashesUnsticky).
            bind ('scroll', x2.massActions._checkFlashesUnsticky);
    }
};

x2.massActions._checkFlashesSticky = function () {
    var flashesContainer = $('#x2-gridview-flashes-container');

    if ($(flashesContainer).position ().top > 
        $('#content-container').position ().top + $('#content-container').height ()) {
         $(flashesContainer).removeClass ('fixed-flashes-container');
        $(window).unbind ('scroll', x2.massActions._checkFlashesUnsticky).
            bind ('scroll', x2.massActions._checkFlashesUnsticky);
    }
};

x2.massActions._checkFlashesUnsticky = function () {
    var flashesContainer = $('#x2-gridview-flashes-container');

    if ($(flashesContainer).offset ().top - $(window).scrollTop () >
        ($(window).height () - 5) - $(flashesContainer).height ()) {

        $(flashesContainer).addClass ('fixed-flashes-container');
        $(window).unbind ('scroll', x2.massActions._checkFlashesSticky).
            bind ('scroll', x2.massActions._checkFlashesSticky);
    } else {
        return false;
    }
};


/*
Removes objects which will get reconstructed after the grid updates and then updates the grid
*/
x2.massActions._updateGrid = function (selectedRecords) {
    ".$gridObj->getBeforeAjaxUpdateStr ()."
    $('#".$gridId."').yiiGridView ('update', {
        complete: function () {
            x2.massActions.DEBUG && console.log ('x2.massActions._updateGrid complete');
            ".$gridObj->getAfterAjaxUpdateStr ()."
            /*gridViewMassActionsMain (); 
            $('#x2-gridview-updating-anim').hide ();*/
        }
    });
};

/***********************************************************************
* Execute mass actions functions 
***********************************************************************/

/* x2prostart */
/*
Execute tag selected mass action
*/
x2.massActions._executeTagSelected = function (selectedRecords, dialog) {
    auxlib.destroyErrorBox ($(dialog));
    var tags = x2.massActions.tagContainer.getTags ();
    x2.massActions.DEBUG && console.log ('tags.length = ' + tags.length);
    if (tags.length === 0) {
        x2.massActions.DEBUG && console.log ('executeTagSelected validation error');
        $(dialog).append (
            auxlib.createErrorBox ('', [x2.massActions.translations['emptyTagError']]));
        $('#mass-action-dialog-loading-anim').remove ();
        $(dialog).dialog ('widget').find ('.x2-dialog-go-button').show ();
        return;
    } 

    $.ajax({
        url:'".$this->createUrl ('x2GridViewMassAction')."',
        type:'post',
        data:{
            modelType: '".$modelName."',
            tags: tags,
            massAction: 'tag',
            gvSelection: selectedRecords
        },
        success: function (data) { 
            x2.massActions.DEBUG && console.log ('executeTagSelected: ajax ret: ' + data);
            var response = JSON.parse (data);
            var returnStatus = response[0];
            $(dialog).dialog ('close');
            if (response['success']) {
                x2.massActions._updateGrid (selectedRecords);
            } 
            x2.massActions._displayFlashes (response);
        }
    });
};

/*
Execute update fields of selected mass action
*/
x2.massActions._executeUpdateField = function (selectedRecords, dialog) {
    var fieldFieldSelector = $('#update-field-field-selector');
    var fieldName = $(fieldFieldSelector).val ();
    var fieldVal;
    if ($(fieldFieldSelector).next ().find ('.star-rating-control').length) { // CStarInput Widget

        // count stars
        fieldVal = $(fieldFieldSelector).next ().find ('.star-rating-control').
			find ('.star-rating-on').length;
    } else {
        var inputField = $(fieldFieldSelector).next ().children ().first ();
        if ($(inputField).attr ('type') === 'hidden') {
            inputField = $(inputField).next ();
        }
        if ($(inputField).length) fieldVal = $(inputField).val ();
    }

    $.ajax({
        url:'".$this->createUrl ('x2GridViewMassAction')."',
        type:'post',
        data:{
            massAction: 'updateFields',
            fieldName: fieldName,
            fieldVal: fieldVal,
            gvSelection: selectedRecords
        },
        success: function (data) { 
            x2.massActions.DEBUG && console.log ('executeUpdateSelected: ajax ret: ' + data);
            var response = JSON.parse (data);
            //var returnStatus = response[0];
            $(dialog).dialog ('close');
            if (response['success']) {
                x2.massActions._updateGrid (selectedRecords);
            }
            x2.massActions._displayFlashes (response);
        }
    });
};

/*
Execute delete selected mass action
*/
x2.massActions._executeDeleteSelected = function (selectedRecords, dialog) {
    $.ajax({
        url:'".$this->createUrl ('x2GridViewMassAction')."',
        type:'post',
        data:{
            massAction: 'delete',
            gvSelection: selectedRecords
        },
        success: function (data) { 
            x2.massActions.DEBUG && console.log ('executeDeleteSelected: ajax ret: ' + data);
            var response = JSON.parse (data);
            $(dialog).dialog ('close');
            x2.massActions._displayFlashes (response);
            if (response['success']) {
                x2.massActions._updateGrid (selectedRecords);
            }
        }
    });
};
/* x2proend */

/*
Execute add to list mass action
*/
x2.massActions._executeRemoveFromList = function (selectedRecords, dialog) {
    var listId = window.location.search.replace (/(?:^[?]id=([^&]+))/, '$1');
    $.ajax({
        url:'".$this->createUrl ('x2GridViewMassAction')."',
        type:'post',
        data:{
            massAction: 'removeFromList',
            listId: listId,
            gvSelection: selectedRecords
        },
        success: function (data) { 
            x2.massActions.DEBUG && console.log ('_executeRemoveFromList: ajax ret: ' + data);
            var response = JSON.parse (data);
            $(dialog).dialog ('close');
            x2.massActions._displayFlashes (response);
            if (response['success']) {
                x2.massActions._updateGrid (selectedRecords);
            }
        }
    });
};

/*
Execute add to list mass action
*/
x2.massActions._executeAddToList = function (selectedRecords, dialog) {
	var targetList = $('#addToListTarget').val();
    $.ajax({
        url:'".$this->createUrl ('x2GridViewMassAction')."',
        type:'post',
        data:{
            massAction: 'addToList',
            listId: targetList,
            gvSelection: selectedRecords
        },
        success: function (data) { 
            x2.massActions.DEBUG && console.log ('executeDeleteSelected: ajax ret: ' + data);
            var response = JSON.parse (data);
            $(dialog).dialog ('close');
            x2.massActions._displayFlashes (response);
        }
    });
};

/*
Execute create new list mass action
*/
x2.massActions._executeCreateNewList = function (selectedRecords, dialog) {
    auxlib.destroyErrorFeedbackBox ($('#x2-gridview-mass-action-list-name'));
    var listName = $('#x2-gridview-mass-action-list-name').val ();
    if(listName !== '' && listName !== null) {
        $.ajax({
            url:'".$this->createUrl ('x2GridViewMassAction')."',
            type:'post',
            data: {
                massAction: 'createList',
                listName: listName,
                gvSelection: selectedRecords
            },
            success: function (data) { 
                x2.massActions.DEBUG && console.log ('executeDeleteSelected: ajax ret: ' + data);
                var response = JSON.parse (data);
                $(dialog).dialog ('close');
                x2.massActions._displayFlashes (response);
            }
        });
    } else {
        auxlib.createErrorFeedbackBox ({
            prevElem: $('#x2-gridview-mass-action-list-name'),
            message: x2.massActions.translations['blankListNameError']
        });
        $('#mass-action-dialog-loading-anim').remove ();
        $(dialog).dialog ('widget').find ('.x2-dialog-go-button').show ();
    }
};




/*
Open dialog for mass action form
*/
x2.massActions._massActionDialog = function (argsList) {
    var dialog = argsList['dialogElem'];
    $('#x2-gridview-mass-action-buttons .mass-action-button').attr ('disabled', 'disabled');

    $(dialog).show ();
    if ($(dialog).closest ('.ui-dialog').length) {
        $(dialog).dialog ('open');
        return;
    }

    var title = argsList['title'];
    var goButtonLabel = argsList['goButtonLabel'];
    var goFunction = argsList['goFunction'];
    var selectedRecords = argsList['selectedRecords'];

    $(dialog).dialog ({
        title: title,
        autoOpen: true,
        width: 500,
        buttons: [
            {
                text: goButtonLabel,
                'class': 'x2-dialog-go-button',
                click: function () { 
                    $(dialog).dialog ('widget').find ('.x2-dialog-go-button').hide ();
                    $(dialog).dialog('widget').find ('.x2-dialog-go-button').before ($('<div>', {
                        'class': 'x2-loading-icon left', 
                        id: 'mass-action-dialog-loading-anim'
                    }));
                    goFunction (selectedRecords, dialog);
                }
            },
            {
                text: x2.massActions.translations['cancel'],
                click: function () { $(dialog).dialog ('close'); }
            }
        ],
        close: function () {
            $(dialog).hide ();
            $('#mass-action-dialog-loading-anim').remove ();
            $(dialog).dialog ('widget').find ('.x2-dialog-go-button').show ();
            $('#x2-gridview-mass-action-buttons .mass-action-button').
                removeAttr ('disabled', 'disabled');
        }
    });

};

/*
Call function which opens dialog for specified mass action
*/
x2.massActions._executeMassAction = function (massAction) {
    x2.massActions.DEBUG && console.log ('executeMassAction: massAction = ' + massAction);
    var selectedRecords = $.fn.yiiGridView.getChecked('".$gridId."', 'C_gvCheckbox');
    if(selectedRecords.length === 0) {
        return;
    }

    switch (massAction) {
        case 'newList':
            x2.massActions._massActionDialog ({
                dialogElem: $('#x2-gridview-new-list-dialog'),
                title: x2.massActions.translations['newList'],
                goButtonLabel: x2.massActions.translations['create'],
                goFunction: x2.massActions._executeCreateNewList,
                selectedRecords: selectedRecords,
            });
            break;
        case 'addToList':
            x2.massActions._massActionDialog ({
                dialogElem: $('#x2-gridview-add-to-list-dialog'),
                title: x2.massActions.translations['addToList'],
                goButtonLabel: x2.massActions.translations['add'],
                goFunction: x2.massActions._executeAddToList,
                selectedRecords: selectedRecords,
            });
            break;
        case 'removeFromList':
            x2.massActions._massActionDialog ({
                dialogElem: $('#x2-gridview-remove-from-list-dialog'),
                title: x2.massActions.translations['removeFromList'],
                goButtonLabel: x2.massActions.translations['remove'],
                goFunction: x2.massActions._executeRemoveFromList,
                selectedRecords: selectedRecords,
            });
            break;
/* x2prostart */
        case 'delete':
            x2.massActions.DEBUG && console.log ('delete');
            x2.massActions._massActionDialog ({
                dialogElem: $('#x2-gridview-delete-dialog'),
                title: x2.massActions.translations['deleteSelected'],
                goButtonLabel: x2.massActions.translations['delete'],
                goFunction: x2.massActions._executeDeleteSelected,
                selectedRecords: selectedRecords,
            });
            break;
        case 'updateField':
            x2.massActions._massActionDialog ({
                dialogElem: $('#x2-gridview-update-field-dialog'),
                title: x2.massActions.translations['updateField'],
                goButtonLabel: x2.massActions.translations['update'],
                goFunction: x2.massActions._executeUpdateField,
                selectedRecords: selectedRecords,
            });
            break;
        case 'tag':
            x2.massActions._massActionDialog ({
                dialogElem: $('#x2-gridview-tag-selected-dialog'),
                title: x2.massActions.translations['tagSelected'],
                goButtonLabel: x2.massActions.translations['tag'],
                goFunction: x2.massActions._executeTagSelected,
                selectedRecords: selectedRecords,
            });
            break;
/* x2proend */
        default:
            auxlib.error ('executeMassAction: default on switch');
            break;
    }
};

/* x2prostart */
/*
Used by update field mass action to dynamically construct field form
Parameters:
    inputName - the name of the X2Fields field
*/
x2.massActions._getUpdateFieldInput = function (inputName) {
    x2.massActions.DEBUG && console.log ('removing old input');
    $('#update-fields-inputs-container').
        find ('.update-fields-field-input-container').children ().remove ();
    $('#update-fields-inputs-container').
        find ('.update-fields-field-input-container').append ($('<div>', {
            'class': 'x2-loading-icon updating-field-input-anim'
        }));
    $.ajax({
        url:'".$this->createUrl ('getX2ModelInput')."',
        dataType: 'html',
        type:'get',
        data:{
            modelName: '".$modelName."',
            inputName: inputName,
        },
        success: function (response) { 
            x2.massActions.DEBUG && console.log ('getUpdateFieldInput: ajax ret: ' + response);
            if (response !== '') { // success
                $('#update-fields-inputs-container').
                    find ('.update-fields-field-input-container').children ().remove ();
                x2.massActions.DEBUG && console.log ('replacing old input');
                $('#update-fields-inputs-container').
                    find ('.update-fields-field-input-container').html (response);
            }
        }
    });
};
/* x2proend */

/*
Recheck records whose checkboxes were cleared by ajax update
*/
x2.massActions._checkX2GridViewRows = function () {
    var idsOfchecked = x2.massActions._previouslySelectedRecords;

    // create a dictionary for O(1) access
    var dictOfIdsOfChecked = {};
    for (var i in idsOfchecked) dictOfIdsOfChecked[idsOfchecked[i]] = true;
    x2.massActions.DEBUG && console.log ('checkX2GridViewRows:  dictOfIdsOfChecked = ');
    x2.massActions.DEBUG && console.log (dictOfIdsOfChecked);

    $('#".$gridId."').find ('[type=\"checkbox\"]').each (function () {
        if (dictOfIdsOfChecked[$(this).val ().toString ()]) {
            $(this).attr ('checked', 'checked');
        }
    });

    x2.massActions._previouslySelectedRecords = undefined;
};

/*
Sets up open/close behavior of more actions list
*/
x2.massActions._setUpMoreButtonBehavior = function () {

    // action more button behavior
    function massActionMoreButtonBehavior () {
        if ($('#more-drop-down-list').is (':visible')) {
            $('#more-drop-down-list').hide ();
            return false;
        } 

        if (x2.gridviewStickyHeader && 
            !$(x2.gridviewStickyHeader.titleContainer).is (':visible')) return false;

        $('#more-drop-down-list').show ();
        x2.massActions.DEBUG && console.log ('massActionMoreButtonBehavior');
        $('#more-drop-down-list').attr ('style', 'left: ' + $(this).position ().left + 'px;');
            /*my: 'left',
            at: 'left',
            of: $(this)
        });*/
        return false;
    }

    $(document).on ('click.moreDropDownList', function () { $('#more-drop-down-list').hide (); });

    $('#mass-action-more-button').unbind ('click').click (massActionMoreButtonBehavior);
};


/*
Set up mass action button behavior and initialize content within dialogs
*/
x2.massActions._setUpMassActions = function () {

/* x2prostart */
    // set up tag dialog tag container
    if ($.inArray ('tag', x2.massActions._massActions)) {
        x2.massActions.DEBUG && console.log ('setting up tag container')
        x2.massActions.tagContainer = new MassActionTagsContainer ({
            containerSelector: '#x2-tag-list',
        });
    }

    // set up update field dialog selector behavior
    if ($.inArray ('updateField', x2.massActions._massActions)) {
        x2.massActions.DEBUG && console.log ('setting up update field behavior')
        $('#update-field-field-selector').unbind ('change').change (function () {
            x2.massActions.DEBUG && console.log ('update-field-field-selector: change');
            var inputName = $(this).val ();
            x2.massActions._getUpdateFieldInput (inputName);
        });
    }

    if ($('#mass-action-button-set').length) {
        $('#mass-action-button-set').find ('a').on ('click', function () {
            var massAction = $(this).attr ('id').match (/[^-]+$/)[0];
            x2.massActions.DEBUG && console.log ('massAction = ' + massAction);
            x2.massActions.DEBUG && console.log ('massAction = ' + ('delete' === massAction));
            x2.massActions._executeMassAction (massAction);
            return false;
        });
    }
/* x2proend */

    if ($('#mass-action-more-button').length) {
        $('#more-drop-down-list').find ('li').on ('click', function () {
            $('#more-drop-down-list').hide ();
            var massAction = $(this).attr ('id').match (/[^-]+$/)[0];
            x2.massActions._executeMassAction (massAction);
            return false;
        });
    }
};


x2.massActions._checkUIShow = function (justChanged) {
    x2.DEBUG && console.log ('checkUIShow');
    justChanged = typeof justChanged === 'undefined' ? true : justChanged;
    if (justChanged) { 

        // do nothing if additional checkbox is checked/unchecked
        if ($(this).is (':checked') && $('#x2-gridview-mass-action-buttons').is (':visible') ||
            !$(this).is (':checked') && !$('#x2-gridview-mass-action-buttons').is (':visible')) {
            return;
        }

        // hide ui when uncheck all box is unchecked
        if ($(this).parents ('.x2grid-header-container').length &&
            !$(this).is (':checked') &&
            $('#x2-gridview-mass-action-buttons').is (':visible')) {

            $('#x2-gridview-mass-action-buttons').hide ();
            return;
        }
    }

    var foundChecked = false; 
    $('#".$gridId."').find ('[type=\"checkbox\"]').each (function () {
        if ($(this).is (':checked')) {
            x2.massActions.DEBUG && console.log ('found checked');
            foundChecked = true;
            return;
        }
    });
    if (foundChecked) {
        $('#x2-gridview-mass-action-buttons').show ();
        if (x2.topPager && x2.topPager.condenseExpandTitleBar) {
            x2.topPager.condenseExpandTitleBar ($('#x2-gridview-top-pager').position ().top);
        }
    } else  {
        $('#x2-gridview-mass-action-buttons').hide ();
    }
};

x2.massActions._setUpUIHideShowBehavior = function () {
    x2.massActions.DEBUG && console.log ('setUpUIHideShowBehavior');
    $('#".$gridId."').on ('change', '[type=\"checkbox\"]', x2.massActions._checkUIShow);
};

/*
Public function for condensing interface
*/
x2.massActions.moveButtonIntoMoreMenu = function () {
    var moreButton = $('#mass-action-more-button');
    var buttons = $('#mass-action-button-set').children ();
    var visibleCount = 0;

    // get last visible button
    $(buttons).each (function () {
        x2.massActions.DEBUG && console.log ($(this));
        if ($(this).attr ('style') !== 'display: none;') {
            lastButton = $(this); 
            visibleCount++;
        }
    });
    if (typeof lastButton === 'undefined') return false;

    $(lastButton).hide (); // hide button in button group

    // give a solitary button proper styling
    if (visibleCount === 2) $(buttons).first ().addClass ('pseudo-only-child');

    // show button in list
    var lastButtonAction = $(lastButton).attr ('id').match (/[^-]+$/)[0];
    $('#mass-action-' + lastButtonAction).show ();

    return true;
};

/*
Public function for expanding interface
*/
x2.massActions.moveMoreButtonMenuItemIntoButtons = function () {
    var buttons = $('#mass-action-button-set').children ();
    var moreButton = $('#mass-action-more-button');
    var listItems = $('#more-drop-down-list').children ();
    var firstItem;

    // get first non hidden element in button list 
    $(listItems).each (function () {
        x2.massActions.DEBUG && console.log ($(this));
        if ($(this).attr ('style') !== 'display: none;') {
            firstItem = $(this); 
            return false;
        }
    });
    if (typeof firstItem === 'undefined') return false;

    // hiden button list item and show button set button
    $(firstItem).hide ();
    var lastButtonAction = $(firstItem).attr ('id').match (/[^-]+$/)[0];
    $('#mass-action-button-' + lastButtonAction).show ();

    if ($(buttons).length - 
        $('#mass-action-button-set').children ('[style=\"display: none;\"]').length !== 1) {

        $(buttons).first ('.pseudo-only-child').removeClass ('pseudo-only-child');
    } else {
        $('#mass-action-button-' + lastButtonAction).addClass ('pseudo-only-child');
    }

    return true;
};

/*
set up mass action ui behavior, this gets run on every grid update
*/
function gridViewMassActionsMain () {
    if (!x2.massActions) {
        x2.massActions = {};
    }
    x2.massActions.DEBUG = false;
    x2.massActions.DEBUG && console.log ('main');

    if (x2.massActions._previouslySelectedRecords) x2.massActions._checkX2GridViewRows ();

    x2.massActions._massActions = ".CJSON::encode ($massActions).";
    
    x2.massActions._checkUIShow (false);
    x2.massActions._setUpMoreButtonBehavior ();
    x2.massActions._setUpMassActions ();
    x2.massActions._setUpUIHideShowBehavior ();
}

", CClientScript::POS_HEAD);

?>

<div id='x2-gridview-mass-action-buttons'>
     
    <?php
    /* x2prostart */
    if (in_array ('delete', $massActions) || in_array ('tag', $massActions)) {
    ?>
    <div id='mass-action-button-set' class='x2-button-group'>
        <?php
        if (in_array ('delete', $massActions)) {
        ?>
        <a id='mass-action-button-delete'
         title='<?php echo Yii::t('app', 'Delete Selected Records'); ?>' 
         class='mass-action-button x2-button<?php in_array ('tag', $massActions) ? 
            '' : 'x2-last-child'; ?>' href='#'> 
            <span></span>
        </a>
        <?php
        }
        if (in_array ('tag', $massActions)) {
        ?>
        <a id='mass-action-button-tag'
         title='<?php echo Yii::t('app', 'Tag Selected Records'); ?>' 
         class='mass-action-button x2-button x2-last-child' href='#'> 
            <span></span>
        </a>
        <?php
        }
        ?>
    </div>
    <?php
    }
    /* x2proend */
    ?>

    <?php
    $moreActions = array_diff ($massActions, array ('tag', 'delete'));
    if ($moreActions) {
        ?>
        <div id='mass-action-more-button-container'>
            <button id='mass-action-more-button' 
             title='<?php echo Yii::t('app', 'More Mass Actions'); ?>'class='x2-button'>
                <span class='more-button-label'>
                    <?php echo Yii::t('app', 'More'); ?>
                </span>
                <img class='more-button-arrow' 
                 src='<?php echo Yii::app()->getTheme()->getBaseUrl().
                    '/images/icons/Collapse_Widget.png'; ?>' />
            </button>
        </div>
        <ul id='more-drop-down-list' style='display: none;'> 
        <?php
        foreach ($massActions as $action) {
        ?>
            <li class='mass-action-button' 
             <?php echo ($action === 'tag' || $action === 'delete' ? 
              'style="display: none;"' : ''); ?> 
             id='mass-action-<?php echo $action; ?>'>
              <?php echo $massActionLabels[$action]; ?>
            </li>
        <?php
        }
        ?>
        </ul>
    <?php
    }
    ?>

    <!--used to position feedback message-->    
    <span id='mass-action-dummy-elem'></span>

    <!--mass action dialog contents-->    
    <?php
    if (in_array ('newList', $massActions)) {
    ?>
    <div class='mass-action-dialog' id="x2-gridview-new-list-dialog" style="display: none;">
        <span>
            <?php echo Yii::t('app', 'What should the list be named?'); ?>
        </span>
        <br/>
        <input class='left' id='x2-gridview-mass-action-list-name'></input>
    </div>
    <?php
    }
    if (in_array ('addToList', $massActions)) {
    ?>
    <div class='mass-action-dialog' id="x2-gridview-add-to-list-dialog" style="display: none;">
        <span>
            <?php echo Yii::t('app', 'Select a list to which the selected records will be added'); 
            ?>
        </span>
        <?php echo CHtml::dropDownList (
            'addToListTarget', null, X2List::getAllStaticListNames ($this)); ?>
    </div>
    <?php
    }
    if (in_array ('removeFromList', $massActions)) {
    ?>
    <div class='mass-action-dialog' id="x2-gridview-remove-from-list-dialog" 
     style="display: none;">
        <span>
            <?php echo Yii::t('app', 'Remove all selected records from this list?'); ?> 
        </span>
    </div>
    <?php
    }
    /* x2prostart */
    if (in_array ('updateField', $massActions)) {
    ?>
    <div class='mass-action-dialog' id="x2-gridview-update-field-dialog" style="display: none;">
        <span class='dialog-help-text'>
            <?php echo Yii::t('app', 'Select a field and enter a field value'); 
            ?>
        </span><br/>
        <div id='update-fields-inputs-container'>
    <?php
        //$editableFieldsFieldInfo = X2Model::model ($modelName)->getEditableFieldNames (false);
        $editableFieldsFieldInfo = FormLayout::model ()->getEditableFieldsInLayout ($modelName);
        asort ($editableFieldsFieldInfo, SORT_STRING);
        if (sizeof ($editableFieldsFieldInfo) !== 0) {
            ?><select id='update-field-field-selector' class='left'><?php
            foreach ($editableFieldsFieldInfo as $fieldName=>$attrLabel) {
            ?>
                <option value=<?php echo $fieldName; ?>>
                  <?php echo $attrLabel; ?></option>
            <?php
            } ?>
            </select>
            <span class='update-fields-field-input-container'>
            <?php
            $fieldNames = array_keys ($editableFieldsFieldInfo);
            echo X2Model::model ($modelName)->renderInput ($fieldNames[0]);
            ?>
            <br/><br/>
            </span>
            <?php
        }
    ?>
        </div>
    </div>
    <?php
    }
    if (in_array ('tag', $massActions)) {
    ?>
    <div class='mass-action-dialog' id="x2-gridview-tag-selected-dialog" style="display: none;">
        <div class='form'>
            <div id="x2-tag-list">
                <span class='tag-container-placeholder'>
                    <?php echo Yii::t('app', 'Drag tags here from the tag cloud widget or click'.
                        ' or click to create a custom tag.'); ?>
                </span>
            </div>
        </div>
    </div>
    <?php
    }
    if (in_array ('delete', $massActions)) {
    ?>
    <div class='mass-action-dialog' id="x2-gridview-delete-dialog" style="display: none;">
        <span>
            <?php echo Yii::t('app', 'Are you sure you want to delete all selected records?'); ?> 
            <br/>
            <?php echo Yii::t('app', 'This action cannot be undone.'); ?>
        </span>
    </div>
    <?php
    }
    /* x2proend */
    ?>
</div>

<!--main function must be called from script tag so it executes when grid refreshes-->
<script> gridViewMassActionsMain (); </script>
