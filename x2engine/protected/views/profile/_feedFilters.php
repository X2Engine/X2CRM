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




Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/views/profile/feedFilters.css');
?>
<div id='feed-filters' style='display: none;'>
<?php

if(isset($_SESSION['filters'])){
    $filters=$_SESSION['filters'];
}else{
    $filters=array(
        'visibility'=>array(),
        'users'=>array(),
        'types'=>array(),
        'subtypes'=>array(),
    );
}

$visibility=array(
    '1'=>'Public',
    '0'=>'Private',
);
$socialSubtypes = Dropdowns::getSocialSubtypes ();
$users=User::getNames();
$eventTypeList=Yii::app()->db->createCommand()
        ->select('type')
        ->from('x2_events')
        ->group('type')
        ->queryAll();
$eventTypes=array();
/*$eventTypesExpansion=array();
foreach($eventTypeList as $key=>$value){
    if($value['type']!='comment') {
        $eventTypes[$value['type']]=Events::parseType($value['type']);
        $eventTypesExpansion[$value['type']]=Events::parseType($value['type']);
    }
}*/
$profile=Yii::app()->params->profile;


echo '<div class="x2-button-group">';
echo '<a href="#" class="simple-filters x2-button'.
    ($profile->fullFeedControls?"":" disabled-link").'" style="width:42px">'.
    Yii::t('app','Simple').'</a>';
echo '<a href="#" class="full-filters x2-button x2-last-child'.
    ($profile->fullFeedControls?" disabled-link":"").'" style="width:42px">'.
    Yii::t('app','Full').'</a>';
echo "</div>\n";

echo "<div id='full-controls'".($profile->fullFeedControls?"":"style='display:none;'").">";

echo CHtml::dropDownList (
    'visibilityFilters', 
    // remove unselected filters and then map 1/0 to 'Public'/'Private'
    array_map (
        function ($a) use ($visibility) { return $visibility[$a]; }, 
        array_diff (array_keys ($visibility), $filters['visibility'])), 
    array_combine (array_values ($visibility), $visibility), 
    array (
        'multiple' => 'multiple',
        'data-selected-text' => Yii::t('app', 'visibility setting(s)'),
        'class' => 'x2-multiselect-dropdown'
    )
);


echo CHtml::dropDownList (
    'relevantUsers', 
    array_diff (array_keys ($users), $filters['users']), 
    $users, 
    array (
        'multiple' => 'multiple',
        'data-selected-text' => Yii::t('app', 'user(s)'),
        'class' => 'x2-multiselect-dropdown'
    )
);


echo CHtml::dropDownList (
    'eventTypes', 
    array_diff (array_keys ($eventTypes), $filters['types']), 
    $eventTypes, 
    array (
        'multiple' => 'multiple',
        'data-selected-text' => Yii::t('app', 'event type(s)'),
        'class' => 'x2-multiselect-dropdown'
    )
);

$subFilters=$filters['subtypes'];
echo CHtml::dropDownList (
    'socialSubtypes', 
    array_diff (array_keys ($socialSubtypes), $subFilters), 
    $socialSubtypes, 
    array (
        'multiple' => 'multiple',
        'data-selected-text' => Yii::t('app', 'social subtype(s)'),
        'class' => 'x2-multiselect-dropdown'
    )
);


echo "<br />";

echo "<div id='full-controls-button-container'>";
echo CHtml::link(
    Yii::t('app','Unselect All'),'#',
    array('class'=>'toggle-filters-link x2-button'));
echo CHtml::link(
    Yii::t('app','Apply Filters'),'#',
    array('class'=>'x2-button', 'id'=>'apply-feed-filters'));
echo CHtml::checkBox('setDefault', false,
    array(
        'title'=>'',
        'class'=>'default-filter-checkbox',
        'id'=>'filter-default'
    )
);
echo "<label for='filter-default'>".Yii::t('app','Set Default')."</label>";

echo CHtml::link(
        Yii::t('app','Create Report'),'#',
        array('class'=>'x2-button x2-hint','style'=>'color:#000;margin-left:5px;','id'=>'create-activity-report',
            'title'=>Yii::t('app','Create an email report using the selected filters which will be mailed to you periodically.')));

echo "</div>";
echo "</div>";

echo "<div id='simple-controls'".
    ($profile->fullFeedControls?"style='display:none;'":"").">";

?>
<span><?php echo addslashes (Yii::t('app', 'Show me ')); ?></span>
<?php

echo CHtml::dropDownList (
    'simpleEventTypes', 
    '',
    array ('' => Yii::t('app', 'All')) + $eventTypes,
    array (
        'class' => 'x2-select'
    )
);
/*
?>
<span><?php echo addslashes (Yii::t('app', 'Expand ')); ?></span>
<?php

echo CHtml::dropDownList (
    'simpleEventTypesExpansion', 
    '',
    array ('' => Yii::t('app', 'All')) + $eventTypesExpansion,
    array (
        'class' => 'x2-select'
    )
);
*/
?>
<span><?php echo addslashes (Yii::t('app', ' Events associated with ')); ?></span>
<?php

echo CHtml::dropDownList (
    'simpleUserFilter', 
    '',
    array (
        '' => Yii::t('app', 'anyone'),
        'myGroups' => Yii::t('app', 'my groups'),
        'justMe' => Yii::t('app', 'just me'),
    ),
    array (
        'class' => 'x2-select'
    )
);

?>
<a id='execute-feed-filters-button' href='#' class='x2-button highlight'><?php echo Yii::t('app', 'Go'); ?></a>
<?php

echo "</div>";
?>
</div>
<?php
