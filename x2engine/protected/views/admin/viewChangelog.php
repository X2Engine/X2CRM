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




Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/viewChangelog.css');

$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'changelog-grid',
    'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
    'template' => '<div class="page-title"><h2>'.Yii::t('admin','Changelog').'</h2><div class="title-bar">'
    .CHtml::link(Yii::t('app', 'Clear Filters'), array('viewChangelog', 'clearFilters' => 1))
    .'{summary}</div></div>{items}{pager}',
    'summaryText' => Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
    'dataProvider' => $model->search(),
    'filter' => $model,
    'afterAjaxUpdate' => 'refreshQtipHistory',
    'columns' => array(
        array(
            'header' => Yii::t('admin', 'History'),
            'value' => '$data->type=="Contacts"?CHtml::link(Yii::t("app","View"),array("/contacts/contacts/revisions","id"=>$data->itemId,"timestamp"=>$data->timestamp),array("class"=>"x2-hint","title"=>Yii::t("admin","Click to view the record at this point in its history."))):""',
            'type' => 'raw',
        ),
        array(
            'name' => 'recordName',
            'header' => Yii::t('admin', 'Record'),
            'value' => '($data->changed !== "delete") ? CHtml::link(CHtml::encode($data->recordName),Yii::app()->controller->createUrl(lcfirst($data->type)."/".$data->itemId)) : CHtml::encode($data->recordName)',
            'type' => 'raw',
        ),
        'changed',
        'fieldName',
        'oldValue',
        'newValue',
        'changedBy',
        array(
            'name' => 'timestamp',
            'header' => Yii::t('admin', 'Timestamp'),
            'value' => 'Formatter::formatLongDateTime($data->timestamp)',
            'type' => 'raw',
            'htmlOptions' => array('width' => '20%'),
        ),
    ),
));
echo "<br>";
echo CHtml::link(
    Yii::t('admin','Clear Changelog'), '#', array(
        'class' => 'x2-button',
        'submit' => 'clearChangelog',
        'csrf' => true,
        'confirm' => 'Are you sure you want to clear the changelog?',
));
echo CHtml::link(
    Yii::t('admin',X2Html::fa('fa-share fa-lg').'Export Changelog'), '#', array(
        'class' => 'x2-button export-changelog',
        'id' => 'export-changelog',
));

Yii::app()->clientScript->registerScript ('changelog-js', '
    function refreshQtipHistory(){
        $(".x2-hint").qtip();
    }

    $("#export-changelog").click(function(evt) {
        evt.preventDefault();
        $.ajax ({
            url: "'.$this->createUrl ('exportChangelog').'",
            success: function() {
                window.location.href = "'.
                    $this->createUrl('/admin/downloadData', array(
                        'file' => 'changelog.csv',
                    )).
                '";
            }
        });
    });
', CClientScript::POS_END);
