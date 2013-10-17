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

$this->actionMenu = $this->formatMenu(array(
    array('label' => Yii::t('profile', 'Social Feed'), 'url' => array('/profile')),
    array('label' => Yii::t('users', 'Manage Users')),
    array('label' => Yii::t('users', 'Create User'), 'url' => array('create')),
    array('label' => Yii::t('users', 'Invite Users'), 'url' => array('inviteUsers')),
        ));

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('users-grid', {
		data: $(this).serialize()
	});
	return false;
});
");

if(isset($_GET['offset']))
    $offset = $_GET['offset'];
else
    $offset = 'first day of this week';
?>


<div class="search-form" style="display:none">
    <?php
    $this->renderPartial('_search', array(
        'model' => $model,
    ));
    ?>
</div><!-- search-form -->
<div class='flush-grid-view'>
<?php
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'users-grid',
    'baseScriptUrl' => Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview/',
    'template' => '<div class="page-title icon users"><h2>'.Yii::t('users', 'Manage Users').'</h2><div class="x2-button-group">'
    .CHtml::link('<span></span>', '#', array('title' => Yii::t('app', 'Advanced Search'), 'class' => 'x2-button search-button'))
    .CHtml::link('<span></span>', array(Yii::app()->controller->action->id, 'clearFilters' => 1), array('title' => Yii::t('app', 'Clear Filters'), 'class' => 'x2-button filter-button')).'</div>'
    .CHtml::link(Yii::t('app', 'Today'), array('admin', 'offset' => '0:00'), array('class' => 'x2-button'))
    .CHtml::link(Yii::t('app', 'This Week'), array('admin', 'offset' => 'first day of this week'), array('class' => 'x2-button'))
    .CHtml::link(Yii::t('app', 'This Month'), array('admin', 'offset' => 'first day of this month'), array('class' => 'x2-button x2-last-child'))
    .X2GridView::getFilterHint()
    .'{summary}</div>{items}{pager}',
    'summaryText' => Yii::t('app', '<b>{start}&ndash;{end}</b> of <b>{count}</b>')
    .'<div class="form no-border" style="display:inline;"> '
    .CHtml::dropDownList('resultsPerPage', Profile::getResultsPerPage(), Profile::getPossibleResultsPerPage(), array(
        'ajax' => array(
            'url' => $this->createUrl('/profile/setResultsPerPage'),
            'data' => 'js:{results:$(this).val()}',
            'complete' => 'function(response) { $.fn.yiiGridView.update("users-grid"); }',
        ),
        'style' => 'margin: 0;',
    ))
    .' </div>',
    'dataProvider' => $model->search(),
    'filter' => $model,
    'columns' => array(
        array(
            'name' => 'username',
            'value' => 'CHtml::link($data->username,$data->id)',
            'type' => 'raw',
        ),
        'firstName',
        'lastName',
        array(
            'name' => 'login',
            'header' => Yii::t('users', 'Last Login'),
            'value' => '$data->login?date("Y-m-d",$data->login):"n/a"',
            'type' => 'raw',
        ),
        array(
            'header' => '<b>'.Yii::t('users', 'Records Updated').'</b>',
            'value' => '(Changelog::model()->countByAttributes(array(),"changedBy=\"$data->username\" AND timestamp > '.strtotime("$offset").'"))',
            'type' => 'raw',
        ),
        array(
            'header' => Yii::t('app', 'Active'),
            'value' => '$data->status? Yii::t("app","Yes") : Yii::t("app","No")',
            'type' => 'raw',
            'headerHtmlOptions' => array('style' => 'width:60px;')
        ),
        'emailAddress',
    //'cellPhone',
    //'homePhone',
    //'address',
    //'officePhone',
    //'emailAddress',
    //'status',
    ),
));
?>
</div>
<?php if($count > 0){ ?>
    <br />
    <h2><?php echo Yii::t('users', "Invited Users"); ?></h2>
    <div class="form">
        <b><?php echo Yii::t('users', "{n} user(s) have been invited but have not yet completed registration.", array('{n}' => $count)); ?></b>
        <br /><br />
        <?php echo Yii::t('users', "To delete all users who have not completed their invite, click the button below."); ?>
        <br /><br />
        <?php echo CHtml::link(Yii::t('users', 'Delete Unregistered'), '#', array('class' => 'x2-button', 'submit' => 'deleteTemporary', 'confirm' => Yii::t('users', 'Are you sure you want to delete these users?'))); ?>
    </div>
<?php } ?>
