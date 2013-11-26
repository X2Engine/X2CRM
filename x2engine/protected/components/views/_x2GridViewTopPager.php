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
    gridId - the id property of the X2GridView instance
    modelName - the modelName property of the X2GridView instance
    gridObj - object - the x2gridview instance
Preconditions:
    - {pager} must be in the grid's template and the pager must have previous and next buttons
*/

Yii::app()->clientScript->registerCss ('topPagerCss', "
#x2-gridview-top-pager {
    display: inline-block;
    margin-right: 2px;
    margin-top: 2px;
}
#x2-gridview-top-pager a {
    padding: 0 7px;
    margin-right: 0;
}
#x2-gridview-top-pager a.x2-last-child {
    margin-left: -4px;
}
");

$gridObj->addToBeforeAjaxUpdate ("
");

$gridObj->addToAfterAjaxUpdate ("
");

Yii::app()->clientScript->registerScript ('topPagerScript', "

x2.topPager = {};
x2.topPager.DEBUG = false;

// The public method. Holds the result of _condenseExpandTitleBar.
x2.topPager.condenseExpandTitleBar; 

/* 
The private method
Creates a closure to keep track of state information about the title bar.
*/
x2.topPager._condenseExpandTitleBar = function () {
    var hiddenButtons = 0;
    var rightmostPosRightElems;
    var leftMostTopPosLeftElems = $('#x2-gridview-top-pager').position ().top;

    /*
    Checks whether the top bar UI should be expanded or condensed and performs the appropriate
    action.
    Parameters:
        newLeftMostTopPosLeftElems - if set, the top offset of the top bar pagination buttons will 
            be checked. This check has the function of determining whether the pagination buttons
            have been moved down due to a lack of space. Having the optional variable eliminates
            the need for calling position ().top every execution (a costly operation).
    */
    return function (newLeftMostTopPosLeftElems) {
        var newLeftMostTopPosLeftElems = 
            typeof newLeftMostTopPosLeftElems === 'undefined' ? undefined : 
                newLeftMostTopPosLeftElems; 
        var moreButton = $('#mass-action-more-button');
    
        if (typeof rightmostPosRightElems === 'undefined') { // calculate once and cache
            var rightmostPosRightElems = $(moreButton).position ().left + $(moreButton).width ();
        }
        var leftMostPosLeftElems = $('#x2-gridview-top-pager').position ().left;
        var titleBarEmptySpace = leftMostPosLeftElems - rightmostPosRightElems;

        x2.topPager.DEBUG && console.log (titleBarEmptySpace);
        x2.topPager.DEBUG && console.log ('hiddenButtons = ');
        x2.topPager.DEBUG && console.log (hiddenButtons);
    
        if (newLeftMostTopPosLeftElems && hiddenButtons == 0 &&
            newLeftMostTopPosLeftElems > leftMostTopPosLeftElems) {

            if (x2.massActions.moveButtonIntoMoreMenu ()) hiddenButtons++;
            rightmostPosRightElems = $(moreButton).position ().left + $(moreButton).width ();
        } else if (titleBarEmptySpace < 80 && hiddenButtons === 0) {
            if (x2.massActions.moveButtonIntoMoreMenu ()) hiddenButtons++;
            rightmostPosRightElems = $(moreButton).position ().left + $(moreButton).width ();
        } else if (titleBarEmptySpace < 70 && hiddenButtons === 1) {
            if (x2.massActions.moveButtonIntoMoreMenu ()) hiddenButtons++;
            rightmostPosRightElems = $(moreButton).position ().left + $(moreButton).width ();
        } else if (titleBarEmptySpace >= 80 && hiddenButtons == 2) {
            if (x2.massActions.moveMoreButtonMenuItemIntoButtons ()) hiddenButtons--;
            rightmostPosRightElems = $(moreButton).position ().left + $(moreButton).width ();
        } else if (titleBarEmptySpace >= 90 && hiddenButtons > 0) {
            if (x2.massActions.moveMoreButtonMenuItemIntoButtons ()) hiddenButtons--;
            rightmostPosRightElems = $(moreButton).position ().left + $(moreButton).width ();
        } 
    }
}

/*
Sets up behavior which will hide/show mass action buttons when there isn't space for them
*/
x2.topPager._setUpTitleBarResponsiveness = function () {
    if (!x2.massActions) return; 

    x2.topPager.condenseExpandTitleBar = x2.topPager._condenseExpandTitleBar ();

    $(window).unbind ('resize.topPager').bind (
        'resize.topPager', x2.topPager.condenseExpandTitleBar);

    $(document).on ('showWidgets', function () {
        if ($('body').hasClass ('no-widgets')) return;
        x2.topPager.DEBUG && console.log ('showWidgets');
        x2.topPager.condenseExpandTitleBar ($('#x2-gridview-top-pager').position ().top);
    });
};

x2.topPager._checkFirstPage = function () {
    return $('#".$gridId."').find ('.pager').find ('.previous').hasClass ('hidden');
};

x2.topPager._checkLastPage = function () {
    return $('#".$gridId."').find ('.pager').find ('.next').hasClass ('hidden');
};

x2.topPager._checkDisableButton = function (prev) {
    if (prev && x2.topPager._checkFirstPage ()) {
        $('#top-pager-prev-button').addClass ('disabled');
    } else if (!prev && x2.topPager._checkLastPage ()) {
        $('#top-pager-next-button').addClass ('disabled');
    }
}

x2.topPager._setUpButtonBehavior = function () {
    x2.topPager._checkDisableButton (true);
    x2.topPager._checkDisableButton (false);
    $('#top-pager-prev-button').on ('click', function () {
        x2.topPager.DEBUG && console.log ('prev');
        $('#".$gridId."').find ('.pager').find ('.previous').find ('a').click ();
        x2.topPager._checkDisableButton (true);
    });
    $('#top-pager-next-button').on ('click', function () {
        x2.topPager.DEBUG && console.log ('next');
        $('#".$gridId."').find ('.pager').find ('.next').find ('a').click ();
        x2.topPager._checkDisableButton (false);
    });
};

x2.topPager.x2GridViewTopPagerMain = function () {
    if (!$('#".$gridId."').find ('.pager').length) {
        $('#x2-gridview-top-pager').hide ()
        return;
    }
    x2.topPager._setUpTitleBarResponsiveness ();
    x2.topPager._setUpButtonBehavior ();
};

x2.topPager._calledReadyFn = false;
$(document).on ('ready', function () {
    x2.topPager._calledReadyFn = true;
    x2.topPager.x2GridViewTopPagerMain ();
});

", CClientScript::POS_HEAD);

?>
<div class='right' id='x2-gridview-top-pager'>
    <div class='x2-button-group'>
        <a id='top-pager-prev-button' 
         class='x2-button' title='<?php echo Yii::t('app', 'Previous Page'); ?>'>&lt;</a>
        <a id='top-pager-next-button' class='x2-button x2-last-child'
         title='<?php echo Yii::t('app', 'Next Page'); ?>'>&gt;</a>
    </div>
</div>
<!--main function must be called from script tag so it executes when grid refreshes-->
<script> 
    if (x2.topPager._calledReadyFn) x2.topPager.x2GridViewTopPagerMain (); 
</script>
