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

/*
Parameters:
    massActions - array of strings - list of available mass actions to select from
        ('delete' | 'newList' | 'addToList' | 'tag' | 'updateField')
    gridId - the id property of the X2GridView instance
    modelName - the modelName property of the X2GridView instance
    selectedAction - string - if set, used to select option from mass actions dropdown
    gridObj - object - the x2gridview instance
*/



$massActionLabels = array (
    'newList' => Yii::t ('app', 'New list from selection'),
    'addToList' => Yii::t ('app', 'Add selected to list'),

);

AuxLib::registerTranslationsScript ('massActions', array (
    'addedItems' => 'Added items to list',
    'addToList' => 'Add selected to list',
    'newList' => 'Create new list from selected',
    'add' => 'Add to list',
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

#mass-action-grid-updating-anim {
    position: fixed;
    z-index: 1000;
}

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

#mass-action-grid-updating-anim > .x2-loading-icon {
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    margin: auto;
    position: absolute;
}

#mass-action-grid-updating-anim {
    width: 100%;
    height: 100%;
    margin: auto;
    top: 0;
    left: 0;
    margin-right: 30px;
}
");

$gridObj->beforeGridViewUpdateJSString .= "
    
    
    $('.mass-action-dialog').each (function () {
        x2.DEBUG && console.log ('destroying dialog loop');
        if ($(this).closest ('.ui-dialog').length) {
            x2.DEBUG && console.log ('destroying dialog');
            $(this).dialog ('destroy');
        }
    });

    x2.massActions.previouslySelectedRecords = selectedRecords; // save to preserve checks

    $('#mass-action-grid-updating-anim').show ();
    $('#x2-gridview-mass-action-buttons').find ('.mass-action-button').unbind ('click');
";

$gridObj->afterGridViewUpdateJSString .= "
    gridViewMassActionsMain (); 
    $('#mass-action-grid-updating-anim').hide ();
";

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
function displayKeyFlashes (key, flashes) {
    x2.DEBUG && console.log ('displayKeyFlashes');
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
        x2.DEBUG && console.log ('displayKeyFlashes: i = ' + i);
        $('#x2-gridview-flashes-' + key + '-list').append ($('<li>', {
            text: flashes[i]
        }));
    }

    if (key === 'success') { // other types of flash containers have close buttons
        if (x2.massActions.timeout) window.clearTimeout (x2.massActions.timeout);
        x2.massActions.timeout = 
            setTimeout (function () { $('#x2-gridview-flash-' + key + '-container').fadeOut (3000); },
                2000);
    }
}

/*
Append flash section container div to parent element
*/
function appendFlashSectionContainer (key, parent) {
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
}

/*
Build the flash container, fill it with given flashes
*/
function displayFlashes (flashes) {
    x2.DEBUG && console.log ('displayFlashes: flashes = ');
    x2.DEBUG && console.log (flashes);
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
        appendFlashSectionContainer ('success', $('#x2-gridview-flashes-container'));
        var successFlashes = flashes['success'];
        displayKeyFlashes ('success', successFlashes);
    }
    if (flashes['notice'] && flashes['notice'].length > 0) {
        appendFlashSectionContainer ('notice', $('#x2-gridview-flashes-container'));
        var noticeFlashes = flashes['notice'];
        displayKeyFlashes ('notice', noticeFlashes);
    }
    if (flashes['error'] && flashes['error'].length > 0) {
        appendFlashSectionContainer ('error', $('#x2-gridview-flashes-container'));
        var errorFlashes = flashes['error'];
        displayKeyFlashes ('error', errorFlashes);
    }

    var flashesContainer = $('#x2-gridview-flashes-container');
    $('#content-container').attr (
        'style', 'padding-bottom: ' + $(flashesContainer).height () + 'px;');
    $(flashesContainer).width ($('#content-container').width () - 5);
    $(window).unbind ('resize.contentContainer').bind ('resize.contentContainer', function () {
        $(flashesContainer).width ($('#content-container').width () - 5);
    });

    x2.DEBUG && console.log ('$(flashesContainer).positoin ().top = ');
    x2.DEBUG && console.log ($(flashesContainer).position ().top);

    if (!checkFlashesUnsticky ()) {
        $(window).unbind ('scroll', checkFlashesUnsticky).bind ('scroll', checkFlashesUnsticky);
    }
}

function checkFlashesSticky () {
    var flashesContainer = $('#x2-gridview-flashes-container');

    if ($(flashesContainer).position ().top > 
        $('#content-container').position ().top + $('#content-container').height ()) {
         $(flashesContainer).removeClass ('fixed-flashes-container');
        $(window).unbind ('scroll', checkFlashesUnsticky).bind ('scroll', checkFlashesUnsticky);
    }
}

function checkFlashesUnsticky () {
    var flashesContainer = $('#x2-gridview-flashes-container');

    if ($(flashesContainer).offset ().top - $(window).scrollTop () >
        ($(window).height () - 5) - $(flashesContainer).height ()) {

        $(flashesContainer).addClass ('fixed-flashes-container');
        $(window).unbind ('scroll', checkFlashesSticky).bind ('scroll', checkFlashesSticky);
    } else {
        return false;
    }
}


/*
Removes objects which will get reconstructed after the grid updates and then updates the grid
*/
function updateGrid (selectedRecords) {
    ".$gridObj->beforeGridViewUpdateJSString."
    $('#".$gridId."').yiiGridView ('update', {
        complete: function () {
            x2.DEBUG && console.log ('updateGrid complete');
            ".$gridObj->afterGridViewUpdateJSString."
            /*gridViewMassActionsMain (); 
            $('#mass-action-grid-updating-anim').hide ();*/
        }
    });
}

/***********************************************************************
* Execute mass actions functions 
***********************************************************************/

/*
Execute add to list mass action
*/
function executeAddToList (selectedRecords, dialog) {
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
            x2.DEBUG && console.log ('executeDeleteSelected: ajax ret: ' + data);
            var response = JSON.parse (data);
            $(dialog).dialog ('close');
            displayFlashes (response);
        }
    });
}

/*
Execute create new list mass action
*/
function executeCreateNewList (selectedRecords, dialog) {
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
                x2.DEBUG && console.log ('executeDeleteSelected: ajax ret: ' + data);
                var response = JSON.parse (data);
                $(dialog).dialog ('close');
                displayFlashes (response);
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
}




/*
Open dialog for mass action form
*/
function massActionDialog (argsList) {
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

}

/*
Call function which opens dialog for specified mass action
*/
function executeMassAction (massAction) {
    x2.DEBUG && console.log ('executeMassAction: massAction = ' + massAction);
    var selectedRecords = $.fn.yiiGridView.getChecked('".$gridId."', 'C_gvCheckbox');
    if(selectedRecords.length === 0) {
        return;
    }

    switch (massAction) {
        case 'newList':
            massActionDialog ({
                dialogElem: $('#x2-gridview-new-list-dialog'),
                title: x2.massActions.translations['newList'],
                goButtonLabel: x2.massActions.translations['create'],
                goFunction: executeCreateNewList,
                selectedRecords: selectedRecords,
            });
            break;
        case 'addToList':
            massActionDialog ({
                dialogElem: $('#x2-gridview-add-to-list-dialog'),
                title: x2.massActions.translations['addToList'],
                goButtonLabel: x2.massActions.translations['add'],
                goFunction: executeAddToList,
                selectedRecords: selectedRecords,
            });
            break;

        default:
            auxlib.error ('executeMassAction: default on switch');
            break;
    }
}



/*
Recheck records whose checkboxes were cleared by ajax update
*/
function checkX2GridViewRows () {
    var idsOfchecked = x2.massActions.previouslySelectedRecords;

    // create a dictionary for O(1) access
    var dictOfIdsOfChecked = {};
    for (var i in idsOfchecked) dictOfIdsOfChecked[idsOfchecked[i]] = true;
    x2.DEBUG && console.log ('checkX2GridViewRows:  dictOfIdsOfChecked = ');
    x2.DEBUG && console.log (dictOfIdsOfChecked);

    $('#".$gridId."').find ('[type=\"checkbox\"]').each (function () {
        if (dictOfIdsOfChecked[$(this).val ().toString ()]) {
            $(this).attr ('checked', 'checked');
        }
    });

    x2.massActions.previouslySelectedRecords = undefined;
}

/*
Sets up open/close behavior of more actions list
*/
function setUpMoreButtonBehavior () {

    // action more button behavior
    function massActionMoreButtonBehavior () {
        if ($('#more-drop-down-list').is (':visible')) {
            $('#more-drop-down-list').hide ();
            return false;
        } 

        if (x2.gridviewStickyHeader && 
            !$(x2.gridviewStickyHeader.titleContainer).is (':visible')) return false;

        $('#more-drop-down-list').show ();
        x2.DEBUG && console.log ('massActionMoreButtonBehavior');
        $('#more-drop-down-list').attr ('style', 'left: ' + $(this).position ().left + 'px;');
            /*my: 'left',
            at: 'left',
            of: $(this)
        });*/
        return false;
    }

    $(document).on ('click.moreDropDownList', function () { $('#more-drop-down-list').hide (); });

    $('#mass-action-more-button').unbind ('click').click (massActionMoreButtonBehavior);
}


/*
Set up mass action button behavior and initialize content within dialogs
*/
function setUpMassActions () {



    if ($('#mass-action-more-button').length) {
        $('#more-drop-down-list').find ('li').on ('click', function () {
            $('#more-drop-down-list').hide ();
            var massAction = $(this).attr ('id').match (/[^-]+$/)[0];
            executeMassAction (massAction);
            return false;
        });
    }
}


function checkUIShow (justChanged) {
    x2.DEBUG && console.log ('checkUIShow');
    justChanged = justChanged === undefined ? true : justChanged;
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
            x2.DEBUG && console.log ('found checked');
            foundChecked = true;
            return;
        }
    });
    if (foundChecked) {
        $('#x2-gridview-mass-action-buttons').show ();
    } else  {
        $('#x2-gridview-mass-action-buttons').hide ();
    }
}

function setUpUIHideShowBehavior () {
    x2.DEBUG && console.log ('setUpUIHideShowBehavior');
    $('#".$gridId."').on ('change', '[type=\"checkbox\"]', checkUIShow);
}


/*
set up mass action ui behavior, this gets run on every grid update
*/
function gridViewMassActionsMain () {
    x2.DEBUG && console.log ('main');

    if (x2.massActions.previouslySelectedRecords) checkX2GridViewRows ();

    if (!x2.massActions) x2.massActions = {};
    x2.massActions.massActions = ".CJSON::encode ($massActions).";
    
    checkUIShow (false);
    setUpMoreButtonBehavior ();
    setUpMassActions ();
    setUpUIHideShowBehavior ();
}

", CClientScript::POS_HEAD);

?>

<div id='x2-gridview-mass-action-buttons'>
     
    <?php
    
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
        foreach ($moreActions as $action) {
        ?>
            <li class='mass-action-button' id='mass-action-<?php echo $action; ?>'>
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
    
    ?>
    <div id='mass-action-grid-updating-anim' style='display: none;'>
        <div class='x2-loading-icon'></div>
    </div>
</div>

<!--main function must be called from script tag so it executes when grid refreshes-->
<script> gridViewMassActionsMain (); </script>
