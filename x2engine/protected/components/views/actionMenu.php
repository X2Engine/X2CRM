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



Yii::app()->clientScript->registerCss('actionMenu',"

#action-menu-right-widget a {
    text-decoration: none;
    color: black;
}

");

$Action = Modules::displayName(false, 'Actions');
$Actions = Modules::displayName(true, 'Actions');

Yii::app()->clientScript->registerScript('setShowActions', '
    if (typeof x2 == "undefined")
        x2 = {};
    x2.setShowActions = function(type) {
        var saveShowActionsUrl = '.json_encode(Yii::app()->controller->createUrl('/actions/actions/saveShowActions')).';
        var viewUrl = "'.Yii::app()->controller->createUrl('/actions/actions/viewAll').'";
        $.post(
            saveShowActionsUrl,
            { ShowActions: type }
        );
    };
');

?>
<ul id='action-menu-right-widget'>
	<li>
        <strong>
            <a href='<?php echo Yii::app()->controller->createUrl ('actions/viewAll'); ?>'
            onclick="x2.setShowActions('all')">
            <?php echo $total; ?></a>
        </strong><?php 
        echo Yii::t('app','Total {Action}|Total {Actions}', array(
            $total,
            '{Action}' => $Action,
            '{Actions}' => $Actions,
        ));
    ?></li>
	<li>
        <strong>
            <a href='<?php echo Yii::app()->controller->createUrl ('actions/viewAll'); ?>'
                onclick="x2.setShowActions('uncomplete')">
            <?php echo $unfinished; ?></a>
        </strong><?php 
        echo Yii::t('app','Incomplete {Action}|Incomplete {Actions}', array(
            $unfinished,
            '{Action}' => $Action,
            '{Actions}' => $Actions,
        ));
    ?></li>
	<li>
        <strong>
            <a href='<?php echo Yii::app()->controller->createUrl ('actions/viewAll'); ?>'
                onclick="x2.setShowActions('overdue')">
            <?php echo $overdue; ?></a>
        </strong><?php 
        echo Yii::t('app','Overdue {Action}|Overdue {Actions}', array(
            $overdue,
            '{Action}' => $Action,
            '{Actions}' => $Actions,
        ));
    ?></li>
	<li>
        <strong>
            <a href='<?php echo Yii::app()->controller->createUrl ('actions/viewAll'); ?>'
                onclick="x2.setShowActions('complete')">
            <?php echo $complete; ?></a>
        </strong><?php 
        echo Yii::t('app','Completed {Action}|Completed {Actions}', array(
            $complete,
            '{Action}' => $Action,
            '{Actions}' => $Actions,
        ));
    ?></li>
</ul>

