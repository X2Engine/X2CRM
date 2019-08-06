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




/*
Parameters:
    gridId - the id property of the X2GridView instance
    gridObj - object - the x2gridview instance
Preconditions:
    - {pager} must be in the grid's template and the pager must have previous and next buttons
*/

Yii::app()->clientScript->registerScriptFile (
    Yii::app()->getBaseUrl().'/js/X2GridView/X2GridViewTopPagerManager.js', CClientScript::POS_END);

Yii::app()->clientScript->registerCss ('topPagerCss', "
.x2-gridview-top-pager {
    display: inline-block;
    margin-right: 2px;
    margin-top: 1px;
    height: 0;
    float: right;
}
.x2-gridview-top-pager a {
    padding: 0 7px;
    margin-right: 0;
}
.x2-gridview-top-pager a.x2-last-child {
    margin-left: -4px !important;
}
");

$gridObj->addToAfterAjaxUpdate ("
    //console.log ('after ajax update top pager');
    if (typeof x2.".$namespacePrefix."TopPagerManager !== 'undefined') 
        x2.".$namespacePrefix."TopPagerManager.reinit (); 
    $('#".$gridId." .x2-gridview-updating-anim').hide ();
");

Yii::app()->clientScript->registerScript($namespacePrefix.'TopPagerInitScript',"
    if (typeof x2.".$namespacePrefix."TopPagerManager === 'undefined') {
        x2.".$namespacePrefix."TopPagerManager = new X2GridViewTopPagerManager ({
            gridId: '".$gridId."',
            gridSelector: '#".$gridId."',
            namespacePrefix: '".$namespacePrefix."'
        });
    }
", CClientScript::POS_READY);

?>
<div id='<?php echo $gridId; ?>-top-pager' class='x2-gridview-top-pager'>
    <div class='x2-button-group'>
        <a class='top-pager-prev-button x2-button' 
         title='<?php echo Yii::t('app', 'Previous page'); ?>'>&lt;</a>
        <a class='top-pager-next-button x2-button x2-last-child'
         title='<?php echo Yii::t('app', 'Next page'); ?>'>&gt;</a>
    </div>
</div>
