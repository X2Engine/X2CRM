<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

$listItemColors = Workflow::getPipelineListItemColors ($colors, true);
$listItemColorCss = '';
for ($i = 1; $i <= count ($listItemColors); ++$i) {
    $listItemColorCss .= 
    "#workflow-stage-$i .stage-member-container {
        background-color: ".$listItemColors[$i - 1][0].";
    }
    #workflow-stage-$i .stage-member-container:hover {
        background-color: ".$listItemColors[$i - 1][1].";
    }";
}
Yii::app()->clientScript->registerCss('stageMemberColorCss',$listItemColorCss);



// drag and drop CSS always gets loaded, this prevents layout thrashing when the UI changes 
Yii::app()->clientScript->registerCss('dragAndDropCss',"

/***********************************************************************
* top scroll bar 
***********************************************************************/
#stage-member-list-container-top-scrollbar-outer {
    border-left: 1px solid #C2C2C2;
    border-right: 1px solid #C2C2C2;
}

#stage-member-list-container-top-scrollbar {
    width: 100%;
    margin-top: -1px;
    overflow-x: scroll;
}
#stage-member-list-container-top-scrollbar > div {
    height:1px;
}



#workflow-filters-container.pipeline-view #process-status-container {
    width: auto !important;
    margin: 0 !important;
}

#workflow-filters-container.pipeline-view #data-summary-box {
    display: none !important;
}

.stage-member-info {
    line-height: 9px;
    color: rgb(136, 136, 136);
}

.stage-title-row {
    height: 0;
    /*margin-top: -20px;*/
}

.total-projected-stage-value {
    clear: both;
    /*color: rgb(88, 88, 88);*/
    float: left;
    height: 0;
}

.total-projected-stage-value,
.total-stage-deals {
    font-weight: bold;
    color: rgb(37, 37, 37);
    font-size: 15px;
}

.stage-member-value {
    font-weight: bold;
    color: rgb(37, 37, 37);
}

.total-stage-deals {
    float: right;
    /*color: rgb(88, 88, 88);*/
    margin-right: 40px;
    height: 0;
}

.stage-member-staging-area {
    /*width: 270px;*/
    width: 246px;
    position: fixed;
    top: 30px;
    background-color: white;
    overflow: hidden;
    z-index: 100;
}

.stage-member-staging-area .stage-member-button-container > a {
    display: none !important;
}

#stage-member-lists-container .yiiPager {
    display: none;
}

#add-a-deal-button {
    margin-top: 5px;
}

/***********************************************************************
* filters 
***********************************************************************/

#workflow-filters-container .row {
    float: left;
    clear: none;
}

#workflow-filters-container form {
    width: auto !important;
}
#workflow-filters-container form .x2-button {
    margin: 11px 0px 0 7px !important;
    padding: 2px 15px !important;
}

#workflow-filters-container #process-status-container {
    border-left: 1px solid #C2C2C2;
    border-right: 1px solid #C2C2C2;
    border-radius: 0;
}



#workflow-filters {
    margin: 5px 5px 0 0;
}

.stage-member-button-container {
    width: 91px;
    left: 153px;
    position: relative;
    height: 30px;
    /*width: 223px;*/
    /*left: 20px;*/
}

.stage-icon-container {
    height: 18px;
    width: 18px;
    margin-top: 13px;
    float: left;
    margin-right: 4px;
}

.stage-member-type-icon {
    opacity: 0.4;
    height: 18px;
    margin-right: 5px;
}

.stage-members {
    float: left;
    width: 246px;
}

#stage-member-lists-container {
    overflow-x: auto;
    /*background-color: rgb(247, 247, 247);*/
    background-color: rgb(235, 235, 235);
    min-height: 790px;
}

#content-container-inner {
    background-color: rgb(235, 235, 235);
}

.list-view .items {
    border: none !important;
    border-radius: 0 !important;
}

.list-view .pager {
    height: 40px;
    float: right;
    line-height: 40px;
    margin: 0;
    border: 0;
    border-radius: 0;
    padding: 0;
    background: none;
}

.stage-member-container:hover, 
.stage-member-container.stage-highlight {
    background-color: rgb(243, 243, 243);
}

#stage-member-lists-container .record-stage-change-pending {
    background-color: rgb(230, 230, 230);
}
#stage-member-lists-container .record-stage-change-pending:hover {
    background-color: rgb(223, 223, 223);
}

.list-view .items .empty {
    margin: 0 !important;
}

.stage-member-container,
.list-view .items .empty {
    height: 45px;
    line-height: 36px;
    border-bottom: 1px solid rgb(226, 226, 226);
    padding-left: 3px;
}

#stage-member-lists-container-inner {
    width: 999999px;
}

.stage-list-title {
    border-top: 1px solid rgb(194, 194, 194);
    border-bottom: 1px solid rgb(218, 218, 218);
    background-image: url('".Yii::app()->theme->getBaseUrl ()."/images/workflowStageArrow.png');
    padding-left: 8px;
    height: 68px;
    line-height: 40px;
}

#content h2 {
    float: left;
}
#content .responsive-menu-items {
    float: right;
}

.stage-list-title h2 {
    line-height: 47px;
    margin: 0;
    width: 200px;
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
    height: 41px;
    display: block;
}

.total-stage-deals, .total-projected-stage-value {
    margin-top: -15px;
}

.workflow-stage-arrow {
    pointer-events: none;
    position: relative;
    top:-41px;
    margin-bottom:-31px;
    /*right: 8px;*/
    /*margin-right: -8px;*/
    /*right: 32px;*/
    right: 8px;
    margin-right: -8px;
    /*margin-right: -32px;*/
}

/***********************************************************************
* stage member container 
***********************************************************************/

#stage-member-lists-container .stage-member-name {
    white-space: nowrap;
    text-overflow: ellipsis;
    width: 135px;
    overflow: hidden;
    height: 30px;
    float: left;
}

.stage-member-button {
    position: absolute;
}

#stage-member-lists-container .edit-details-button {
    right: 59px;
}

#stage-member-lists-container .undo-stage-button {
    right: 29px;
}

#stage-member-lists-container .complete-stage-button {
    right: 0px;
}

#stage-member-lists-container .complete-stage-button,
#stage-member-lists-container .undo-stage-button {
	margin:11px 5px 0 0;
}

#stage-member-lists-container .edit-details-button {
	margin:11px -1px 0 0;
	vertical-align:middle;
	padding:0 5px;
}
#stage-member-lists-container .edit-details-button span {
    cursor: pointer;
    opacity: 0.3;
    display: block;
    height: 24px;
    width: 24px;
}

#stage-member-lists-container .edit-details-button span:hover {
    opacity: 0.4;
}

#stage-member-lists-container .complete-stage-button, 
#stage-member-lists-container .undo-stage-button {
    cursor: pointer;
    color: rgb(180,180,180);
    border-radius: 15px;
    width: 8px;
}

");

Yii::app()->clientScript->registerCss('viewWorkflow',"

.date-range-title {
}

#workflow-model-type-filter + button {
    margin-top: 2px;
}

#stage-data-summary > h3,
#stage-data-summary > span {
    display: inline-block;
}

#stage-data-summary > h3 {
    margin-right: 8px;
}
#stage-data-summary > span {
    margin-right: 6px;
}

#data-summary-box {
    width: 100%;
    display: block;
    clear: both;
    padding-left: 5px;
    padding-bottom: 5px;
}

.row-no-title {
    margin-top: 15px;
    margin-bottom: 4px;
}

/***********************************************************************
* funnel 
***********************************************************************/

.funnel-stage-count {
    cursor: pointer;
}

.stage-name-link, .funnel-stage-value {
    font-weight: bold;
    font-size: 16px;
}

.funnel-total-records, .funnel-total-value {
    font-size: 18px;
}

#funnel-container {
    position: relative;
    width: 248px;
    float: left;
    margin-left: 140px;
    margin-right: 208px;
    margin-top: 17px;
    margin-bottom: 26px;
}

/***********************************************************************
* ui switch buttons 
***********************************************************************/

#per-stage-view-button > div {
    width: 19px;
    height: 2px;
    border-radius: 2px;
    background-color: rgb(126, 123, 123);
    margin: -2px 0 5px 0;
    position:relative;
    top:9px;
}

#per-stage-view-button,
#drag-and-drop-view-button {
    height: 26px;
    box-sizing: border-box;
}

#drag-and-drop-view-button > div {
    display: inline-block;
    width: 2px;
    height: 16px;
    border-radius: 2px;
    background-color: rgb(126, 123, 123);
    margin: 4px 0 5px 0;
}


#content {
    background: none !important;
    border: none !important;
}
#process-status-container {
    width: 440px;
    margin-left: 6px;
    overflow-x: hidden;
    border: none;
}

#drag-and-drop-view-button {
    margin-left: -4px;
}

.page-title .x2-button-group {
}

.grid-view table.items {
    margin-top: 0 !important;
}

#process-status-container {
    margin-bottom: 0 !important;
}

.grid-view .x2-layout-island {
    margin-top: 4px
}
.grid-view .x2-layout-island > h2 {
    margin-top: 6px;
    margin-left: 6px
}

.grid-container {
    margin-top: 5px;
}
");

Yii::app()->clientScript->registerResponsiveCss('workflowDetailResponsiveCss',"

/* responsive title bar behaves differently on this page from how it does on others. these 
lines compensate for that. */
@media (max-width: 657px) {
    .responsive-menu-items {
        margin-top: 29px;
        margin-right: -19px;
    }
    .responsive-page-title > .mobile-dropdown-button {
        margin-top: 9px;
    }
}

/*@media (max-width: 526px) {
    #process-status {
        clear:both !important;
    }
}

@media (max-width: 1036px) {
    #process-status-container { 
        clear: both !important;
    }
    #process-status-container form { 
        width: auto !important;
    }
    #process-status-container form > .row { 
        float: left !important;
        clear: none !important;
    }
}*/

");


Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/workflowFunnel.css');

$getQueryStr = function () use (
    $model, $dateRange, $expectedCloseDateDateRange, $users, $modelType) {

    return 
        'workflowAjax=true&id='.$model->id.
        '&start='.Formatter::formatDate($dateRange['start']).
        '&end='.Formatter::formatDate($dateRange['end']).
        '&range='.$dateRange['range'].
        '&expectedCloseDateStart='.Formatter::formatDate($expectedCloseDateDateRange['start']).
        '&expectedCloseDateEnd='.Formatter::formatDate($expectedCloseDateDateRange['end']).
        '&expectedCloseDateRange='.$expectedCloseDateDateRange['range'].
        '&users='.$users.
        '&modelType='.urlencode (CJSON::encode ($modelType));
};

Yii::app()->clientScript->registerScript('getWorkflowStage',"

x2.WorkflowViewManager = (function () {

function WorkflowViewManager (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        perStageWorkflowView: true
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

    this._init ();
}

/**
 * Requests stage member grids via AJAX 
 */
WorkflowViewManager.getStageMembers = function (stage) {
	$.ajax({
		url: '" . CHtml::normalizeUrl(array('/workflow/workflow/getStageMembers')) . "',
		type: 'GET',
		data: 
            '".$getQueryStr ()."&stage=' + stage,
		success: function(response) {
			if(response === '') return;
            $('#workflow-gridview').html(response);
            $('#workflow-gridview').removeClass ('x2-layout-island');
            $('#workflow-gridview').removeClass ('x2-layout-island-merge-bottom');
            $('#content .x2-layout-island-merge-top-bottom').
                removeClass ('x2-layout-island-merge-top-bottom').
                addClass ('x2-layout-island-merge-top');
            $.ajax({
                url: '" . CHtml::normalizeUrl(array('/workflow/workflow/getStageValue')) . "',
                data: 
                    '".$getQueryStr ()."&stage=' + stage,
                success: function(response) {
                    $('#data-summary-box').html(response);
                }
            });
		}
	});
};

/**
 * Depresses button in interface selection button group corresponding to currently selected
 * interface
 * @param bool perStageWorkflowView
 */
WorkflowViewManager.prototype._updateChangeUIButtons = function (perStageWorkflowView) {
    $('#interface-selection').children ().removeClass ('disabled-link');
    if (perStageWorkflowView) {
        $('#per-stage-view-button').addClass ('disabled-link');
        $('#workflow-filters').hide ();
        $('#add-a-deal-button').hide ();
    } else {
        $('#drag-and-drop-view-button').addClass ('disabled-link');
        $('#workflow-filters').show ();
        $('#add-a-deal-button').show ();
    }
};

/**
 * @param bool perStageWorkflowView 
 */
WorkflowViewManager.prototype._changeUI = function (perStageWorkflowView) {
    var that = this;
    $.ajax ({
        url: '".CHtml::normalizeUrl (array ('/workflow/workflow/changeUI'))."',                 
		type: 'GET',
		data: 
            '".$getQueryStr ().
            "&perStageWorkflowView=' + perStageWorkflowView,
        success: function (data) {
            if (data !== '') {
                $('.page-title').siblings ().remove ();
                $('.page-title').after ($('<div>'));
                $('.page-title').next ().replaceWith (data);
                that._updateChangeUIButtons (perStageWorkflowView);
                that.perStageWorkflowView = perStageWorkflowView;
                x2.forms.initializeMultiselectDropdowns ();
            }
        }
    });
};

/**
 * Unselect pipeline/funnel menu item and selects funnel/pipeline menu item, respectively.
 * @param object menuItem the currently selected menu item <li> element
 * @param bool pipeline
 */
WorkflowViewManager.prototype._swapViewMenuItems = function (menuItem, pipeline) {
    var pipeline = typeof pipeline === 'undefined' ? false : pipeline; 

    $(menuItem).children ().first ().replaceWith ($('<span>', {
        html: $(menuItem).children ().first ().html (),
        id: $(menuItem).children ().first ().attr ('id')
    }));
    if (pipeline) {
        $(menuItem).next ().children ().first ().replaceWith ($('<a>', {
            html: $(menuItem).next ().children ().first ().html (),
            id: $(menuItem).next ().children ().first ().attr ('id'),
            href: '#',
        }));
    } else {
        $(menuItem).prev ().children ().first ().replaceWith ($('<a>', {
            html: $(menuItem).prev ().children ().first ().html (),
            id: $(menuItem).prev ().children ().first ().attr ('id'),
            href: '#'
        }));
    }
}

/**
 * Set up behavior of interface selection buttons 
 */
WorkflowViewManager.prototype._setUpUIChangeBehavior = function () {
    var that = this;
    $('#interface-selection a').click (function (evt) {
        evt.preventDefault ();
        var id = $(this).attr ('id');                                                

        if (id === 'per-stage-view-button') {
            if (!that.perStageWorkflowView) {
                that._swapViewMenuItems ($('#funnel-view-menu-item').closest ('li'), true);
                that._changeUI (true);
            }
        } else { // id === 'drag-and-drop-view-button
            if (that.perStageWorkflowView) {
                that._swapViewMenuItems ($('#pipeline-view-menu-item').closest ('li'), false);
                that._changeUI (false);
            }
        }

        return false;
    });
    $('#funnel-view-menu-item').closest ('li').click (function (evt) {
        if (!that.perStageWorkflowView) {
            that._swapViewMenuItems (this, true);
            that._changeUI (true);
        }
        return false;
    });
    $('#pipeline-view-menu-item').closest ('li').click (function (evt) {
        if (that.perStageWorkflowView) {
            that._swapViewMenuItems (this, false);
            that._changeUI (false);
        }
        return false;
    });
};

WorkflowViewManager.prototype._init = function () {
    this._setUpUIChangeBehavior ();
};

return WorkflowViewManager;

}) ();

$(function () { 
    x2.workflowViewManager = new x2.WorkflowViewManager ({
        perStageWorkflowView: ".($perStageWorkflowView ? 'true' : 'false')."
    }); 
});


",CClientScript::POS_HEAD);

$this->setPageTitle(Yii::t('workflow', 'View Process'));

$isAdmin = (Yii::app()->params->isAdmin);


$workflowViewMenuItems = array (
	array(
        'label'=>Yii::t('app','Funnel View'),
        'linkOptions' => array ('id' => 'funnel-view-menu-item'),
    ),
	array(
        'label'=>Yii::t('app','Pipeline View'),
        'linkOptions' => array ('id' => 'pipeline-view-menu-item'),
    ),
);

if ($perStageWorkflowView) {
    $workflowViewMenuItems[1]['url'] = '#';
} else {
    $workflowViewMenuItems[0]['url'] = '#'; 
}

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('workflow','All Processes'), 'url'=>array('index')),
	array('label'=>Yii::t('app','Create'), 'url'=>array('create'), 'visible'=>$isAdmin),
	array(
        'label'=>Yii::t('workflow','Edit Process'), 
        'url'=>array('update', 'id'=>$model->id), 
        'visible'=>$isAdmin),
    $workflowViewMenuItems[0],
    $workflowViewMenuItems[1],
	array(
        'label'=>Yii::t('workflow','Delete Process'), 
        'url'=>'#', 
        'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),
        'confirm'=>Yii::t('app','Are you sure you want to delete this item?')), 
        'visible'=>$isAdmin
    ),
));

?>
<div id='content-container-inner'>
<div class="responsive-page-title page-title icon workflow x2-layout-island x2-layout-island-merge-bottom">
    <h2><span class="no-bold"><?php echo Yii::t('workflow','Process:'); ?></span> 
        <?php echo $model->name; ?>
    </h2>
    <?php 
    echo ResponsiveHtml::gripButton ();
    ?>
    <div class='responsive-menu-items'>
    <div id='interface-selection' class='x2-button-group right'>
        <a href='#' id='per-stage-view-button' 
         title='<?php echo Yii::t('workflow', 'Funnel View'); ?>'
         class='x2-button<?php echo ($perStageWorkflowView ? ' disabled-link' : ''); ?>'>
         <div></div>
         <div></div>
         <div></div>
        </a>
        <a href='#' id='drag-and-drop-view-button' 
         title='<?php echo Yii::t('workflow', 'Pipeline View'); ?>'
         class='x2-button<?php echo ($perStageWorkflowView ? '': ' disabled-link'); ?>'>
         <div></div>
         <div></div>
         <div></div>
        </a>
    </div>

    <a href='#' id='workflow-filters' class='filter-button right x2-button'
     title='<?php echo Yii::t('workflow', 'Filters'); ?>'
     <?php echo ($perStageWorkflowView ? 'style="display: none;"' : ''); ?>><span></span>
    </a>
    <a href='#' id='add-a-deal-button' class='right x2-button'
     <?php echo ($perStageWorkflowView ? 'style="display: none;"' : ''); ?>>
     <?php echo Yii::t('app', 'Add a Deal'); ?>   
    </a>

    </div>
</div>
<?php
if ($perStageWorkflowView) {
    $this->renderPartial ('_perStageView',
        array (
            'model'=>$model,
            'modelType'=>$modelType,
            'viewStage'=>$viewStage,
            'dateRange'=>$dateRange,
            'expectedCloseDateDateRange'=>$expectedCloseDateDateRange,
            'users'=>$users,
        )
    );
} else {
    $this->renderPartial ('_dragAndDropView',
        array (
            'model'=>$model,
            'modelType'=>$modelType,
            'dateRange'=>$dateRange,
            'expectedCloseDateDateRange'=>$expectedCloseDateDateRange,
            'colors'=>$colors,
            'memberListContainerSelectors'=>$memberListContainerSelectors,
            'stagePermissions'=>$stagePermissions,
            'stagesWhichRequireComments'=>$stagesWhichRequireComments,
            'stageNames'=>$stageNames,
            'stageValues' => $stageValues,
            'users'=>$users,
            'listItemColors' => $listItemColors,
        )
    );
}
?>
</div>
