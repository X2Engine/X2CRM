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






Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/packager.css');

?>

<div class="page-title"><h2><?php 
    echo CHtml::encode (Yii::t('admin', 'Importing Package: {package}', array(
        '{package}' => $manifest['name']
    ))); 
?></h2></div>

<div id='packager-form' class="form">
    <?php 
    echo Yii::t('admin', 
       'You are about to import the following package. Please review '.
       'the pending changes before proceeding.'
    );
    echo '<h3>'.Yii::t('admin', 'Package Components').'</h3>';
    ?>
    <div class="packageSummary">
    <div class="row">
        <div class="cell">
             <label>Description</label>
        </div>
        <div class="cell">
            <?php echo CHtml::encode ($manifest['description']); ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
             <label>Version</label>
        </div>
        <div class="cell">
            <?php echo CHtml::encode ($manifest['version']); ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
             <label>Edition</label>
        </div>
        <div class="cell">
            <?php echo CHtml::encode ($manifest['edition']); ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
             <label>Modules</label>
        </div>
        <div class="cell">
            <?php echo CHtml::encode(implode (',', $manifest['modules'])); ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
             <label>Roles</label>
        </div>
        <div class="cell">
            <?php echo CHtml::encode(implode (',', $manifest['roles'])); ?>
        </div>
    </div>
    <div class="row">
        <div class="cell">
            <label>Includes Contact Data?</label>
        </div>
        <div class="cell">
            <?php echo $manifest['contacts'] ? "Yes" : "No" ; ?>
        </div>
    </div>
    </div>
    <?php

    echo X2Html::getFlashes();
    echo CHtml::button(Yii::t('admin','Apply Package'), array(
        'class' => 'x2-button',
        'id' => 'import-button'
    ));
    echo CHtml::link(Yii::t('admin','Back'), array('packager'), array(
        'class' => 'x2-button',
    ));
    echo '<div id="status"></div>';

    Yii::app()->clientScript->registerScript ('previewPackage','
        ;(function () {
            $("#import-button").click(function() {
                var throbber = auxlib.pageLoading();
                $("#import-button").hide();

                $.ajax({
                    url: "'.$this->createUrl (
                        'importPackage', array('package' => $manifest['name']
                    )).'",
                    type: "post",
                    success: function(data) {
                        throbber.remove();
                        $("#status").html("'.Yii::t('admin', 'Finished applying package. Redirecting...').'");
                        $("#status").addClass("flash-success");
                        window.location.href = "'.$this->createUrl ('packager').'";
                    },
                    error: function(data, error) {
                        var error = data.responseText;
                        throbber.remove();
                        $("#status").html("'.Yii::t('admin', 'Failed to apply package! ').'" + error);
                        $("#status").addClass("flash-error");
                    }
                });
            });
        }) ();
    ', CClientScript::POS_READY);
?>
</div>
