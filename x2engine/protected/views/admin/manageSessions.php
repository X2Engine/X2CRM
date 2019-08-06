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



?>
<div class='flush-grid-view'>
<?php
$this->widget('X2GridViewGeneric', array(
	'id'=>'sessions-grid',
	'buttons'=>array('autoResize'),
	'baseScriptUrl'=>  
        Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'template'=> '<div class="page-title"><h2>'.Yii::t('admin','Active Sessions').'</h2>'
		.'{buttons}{summary}</div>{items}{pager}',
	'summaryText'=>Yii::t('app','<b>{start}&ndash;{end}</b> of <b>{count}</b>'),
	'dataProvider'=>$dataProvider,
    'defaultGvSettings' => array (
        'user' => 100,
        'IP' => 100,
        'lastUpdated' => 100,
        'status' => 100,
    ),
    'gvSettingsName' => 'manage-sessions-grid',
    'viewName' => 'manageSessions',
	'columns'=>array(
		array (
            'name' => 'user',
        ),
		array (
            'name' => 'IP',
        ),
        array(
            'name'=>'lastUpdated',
            'header'=>Yii::t('admin','Last Activity'),
            'type'=>'raw',
            'value'=>'Formatter::formatCompleteDate($data->lastUpdated)',
        ),
        array(
            'name'=>'status',
            'header'=>Yii::t('admin','Status'),
            'type'=>'raw',
            'value'=>'$data->status==1?"Active":"Invisible"',
        ),
        array(
            'header'=>Yii::t('admin','Toggle Invisible'),
            'type'=>'raw',
            'value'=>"CHtml::link(Yii::t('admin','Toggle'),'#',array('class'=>'x2-button toggle-session', 'id'=>\$data->id))"
        ),
        array(
            'header'=>Yii::t('admin','End Session'),
            'type'=>'raw',
            'value'=>"CHtml::link(Yii::t('admin','End'),'#',array('class'=>'x2-button end-session', 'title'=>\$data->id))"
        ),
	),
));
?>
</div>
<?php
Yii::app()->clientScript->registerScript('session-controls','
$(document).on("click",".toggle-session",function(e){
    e.preventDefault();
    var link=this;
    if(confirm("'.Yii::t('admin',"Are you sure you want to toggle this session?").'")){
        $.ajax({
            url:"toggleSession?id="+$(this).attr("id"),
            success:function(data){
                if(data==1){
                    $(link).parent().prev().html("Active");
                }else if(data==0){
                    $(link).parent().prev().html("Inactive");
                }
            }
        });
    }
});

$(document).on("click",".end-session",function(e){
    e.preventDefault();
    var link=this;
    if(confirm("'.Yii::t('admin',"Are you sure you want to end this session?").'")){
        $.ajax({
            url:"endSession?id="+$(this).attr("title"),
            success:function(){
                $.fn.yiiGridView.update("sessions-grid");
            }
        });
    }
});
');
?>
