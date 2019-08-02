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




$layoutManager = $this->widget ('RecordViewLayoutManager', array ('staticLayout' => false));
$this->noBackdrop = true;

Yii::app()->clientScript->registerResponsiveCssFile(
    Yii::app()->theme->baseUrl.'/css/responsiveRecordView.css');
Yii::app()->clientScript->registerCssFile ($this->module->assetsUrl.'/css/campaignView.css');



// if the campaign has been launched, hide all collapsables
if($model->launchDate){
    Yii::app()->clientScript->registerScript('hide-all-collapsables', "
	$(function() {
		$('.formSection.collapsible').each(function() {
			if($(this).hasClass('showSection')) {
				$(this).removeClass('showSection');
				$(this).find('.tableWrapper').css('display', 'none');
			}
		});
	});
	");
}

$this->pageTitle = $model->name;
$themeUrl = Yii::app()->theme->getBaseUrl();
$authParams['X2Model'] = $model;

$menuOptions = array(
    'all', 'create', 'view', 'edit', 'delete', 'lists', 'newsletters',
    'weblead', 'x2flow', /* x2entend */ 'A/B-Campaigns', 'Long-Term-Campaigns', /* x2entend */
);
$this->insertMenu($menuOptions, $model, $authParams);

?>

<div class="page-title-placeholder"></div>

<div class="page-title-fixed-outer">
    <div class="page-title-fixed-inner">
        <div class="page-title icon marketing">
            <h2><?php echo CHtml::encode($model->name); ?></h2>
            <?php 
            if(Yii::app()->user->checkAccess('MarketingUpdate', $authParams)) { 
                echo X2Html::editRecordButton($model);
            } 
        ?>
        </div>
    </div>
</div>

<div id="main-column" <?php echo $layoutManager->columnWidthStyleAttr (1); ?>>
    <?php echo X2Html::getFlashes();

    $partialParams = array(
        'model' => $model,
        'disableInlineEditingFor' => array ('content'),
        'modelName' => 'Campaign',
        'specialFields' => array(
            'content' => '<div style="height:350px;"><iframe src="'.$this->createUrl('/marketing/marketing/viewContent',array('id'=>$model->id)).'" id="docIframe" frameBorder="0" style="height:100%;background:#fff;"></iframe></div>'
        ),
    );
  
    if(!isset($partialParams['suppressFields'])){
        $partialParams['suppressFields'] = array();
    }
   if (!Yii::app()->params->isAdmin) {
        $partialParams['suppressFields'] = array('bouncedAccount', 'enableBounceHandling');
    }
    if ($model->type == 'Email') {
        $partialParams['suppressFields'] = array_merge($partialParams['suppressFields'], array('template', 'subject'));

    }
    if($model->type == 'Parent') {
        $this->renderPartial('KidCampaigns', array('model'=>$model));
    } elseif($model->type == 'ParentAB') {
        $this->renderPartial('KidCampaigns', array('model'=>$model));
    } else {
        $this->widget ('DetailView', $partialParams);
        // $this->renderPartial('application.components.views.@DETAILVIEW', $partialParams);
        if ($model->type == 'Email') {
            $this->renderPartial('attachments', array('model'=>$model));
        }

        if(!$model->complete && Yii::app()->user->checkAccess('MarketingLaunch')){
            $this->renderPartial('marketingLaunch', array('model'=>$model));
        }

        $this->renderPartial('inlineEmailForm', array('model'=>$model));

        if($model->type === 'Email'){
            if($model->launchDate && $model->active && !$model->complete){
                if ($model->launchDate > time()) {
                    echo '<div class="campaign-schedule-notice"><p>';
                    echo Yii::t('marketing', 'Campaign is scheduled to launch at ').
                        Formatter::formatDateTime($model->launchDate);
                    echo '</p>';
                    echo CHtml::ajaxButton(Yii::t('marketing', 'Validate'), 'validate', array('data' => array('id' => $model->id), 'complete' => 'function(data) { console.log(data); alert(data.responseJSON.message); }'), array('class' => 'x2-button', 'style' => 'display: inline-block'));
                    echo '</div>';
                } else {
                    $this->widget('EmailProgressControl',array(
                        'campaign' => $model,
                    ));
                }
            }
        } 

        if(isset($model->list) && $model->launchDate){
           $this->renderPartial('campaignGrid', array(
                'model'=>$model, 
            ));
        }
    }
    ?>
</div>

<?php
$this->widget('X2WidgetList', array(
    'layoutManager' => $layoutManager,
    'block' => 'center',
    'model' => $model,
    'modelType' => 'Marketing'
));
