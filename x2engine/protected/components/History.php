<?php

/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * Renders a CListView containing all actions associated with the specified model
 *
 * @package X2CRM.components
 */
class History extends X2Widget {

    public $associationType;  // type of record to associate actions with
    public $associationId = '';  // record to associate actions with
    public $filters = true;  // whether or not action types can be filtered on
    public $historyType = 'all'; // default filter type
    public $pageSize = 10; // how many records to show per page
    public $relationships = 0; // don't show actions on related records by default

    /**
     *
     */
    public function run(){
        if($this->filters){
            $historyTabs = array(
                'all' => Yii::t('app', 'All'),
                'actions' => Yii::t('app', 'Actions'),
                'comments' => Yii::t('app', 'Comments'),
                'workflow' => Yii::t('app', 'Workflow'),
                'attachments' => Yii::t('app', 'Attachments'),
                'marketing' => Yii::t('app', 'Marketing'),
                'webactivity' => Yii::t('app', 'Web Activity'),
            );
            $profile = Yii::app()->params->profile;
            if(isset($profile)){ // Load their saved preferences from the profile
                $this->pageSize = $profile->historyShowAll ? 10000 : 10; // No way to give truly infinite pagination, fudge it by making it 10,000
                $this->relationships = $profile->historyShowRels;
            }
            if(isset($_GET['history']) && array_key_exists($_GET['history'], $historyTabs)){ // See if we can filter for what they wanted
                $this->historyType = $_GET['history'];
            }
            if(isset($_GET['pageSize'])){
                $this->pageSize = $_GET['pageSize'];
                if(isset($profile)){
                    $profile->historyShowAll = $this->pageSize > 10 ? 1 : 0; // Save profile preferences
                    $profile->update(array('historyShowAll'));
                }
            }
            if(isset($_GET['relationships'])){
                $this->relationships = $_GET['relationships'];
                if(isset($profile)){
                    $profile->historyShowRels = $this->relationships; // Save profile preferences
                    $profile->update(array('historyShowRels'));
                }
            }
        }else{
            $historyTabs = array();
        }
        Yii::app()->clientScript->registerScript('history-tabs', "
            var relationshipFlag={$this->relationships};
            var currentHistory='".$this->historyType."';
            $(document).on('change','#history-selector',function(){
                $.fn.yiiListView.update('history',{ data:{ history: $(this).val() }});
            });
            $(document).on('click','#history-collapse',function(e){
                e.preventDefault();
                $('#history .description').toggle();
            });
            $(document).on('click','#show-history-link',function(e){
                e.preventDefault();
                $.fn.yiiListView.update('history',{ data:{ pageSize: 10000 }});
            });
            $(document).on('click','#hide-history-link',function(e){
                e.preventDefault();
                $.fn.yiiListView.update('history',{ data:{ pageSize: 10 }});
            });
            $(document).on('click','#show-relationships-link',function(e){
                e.preventDefault();
                if(relationshipFlag){
                    relationshipFlag=0;
                }else{
                    relationshipFlag=1;
                }
                $.fn.yiiListView.update('history',{ data:{ relationships: relationshipFlag }});
            });
        "); // Script to make all the buttons on the history widget function via AJAX.
        $this->widget('application.components.X2ListView', array(
            'id' => 'history',
            'dataProvider' => $this->getHistory(),
            'viewData' => array(
                'relationshipFlag' => $this->relationships, // Pass relationship flag to the views so they can modify their layout slightly
            ),
            'itemView' => 'application.modules.actions.views.actions._view',
            'htmlOptions' => array('class' => 'action list-view'),
            'template' => '<div class="form">'.CHtml::dropDownList('history-selector', $this->historyType, $historyTabs).
            '<span style="margin-top:5px;" class="right">'.CHtml::link(Yii::t('app', 'Toggle Text'), '#', array('id' => 'history-collapse', 'class' => 'x2-hint', 'title' => Yii::t('app', 'Click to toggle showing the full text of History items.')))
            .' | '.CHtml::link(Yii::t('app', 'Show All'), '#', array('id' => 'show-history-link', 'class' => 'x2-hint', 'title' => Yii::t('app', 'Click to increase the number of History items shown.'), 'style' => $this->pageSize > 10 ? 'display:none;' : ''))
            .CHtml::link(Yii::t('app', 'Show Less'), '#', array('id' => 'hide-history-link', 'class' => 'x2-hint', 'title' => Yii::t('app', 'Click to decrease the number of History items shown.'), 'style' => $this->pageSize > 10 ? '' : 'display:none;'))
            .((!Yii::app()->user->isGuest) ? ' | '.CHtml::link(Yii::t('app', 'Relationships'), '#', array('id' => 'show-relationships-link', 'class' => 'x2-hint', 'title' => Yii::t('app', 'Click to toggle showing actions associated with related records.'))) : '')
            .'</span></div> {sorter}{items}{pager}',
        ));
    }

    /**
     *
     * @return \CActiveDataProvider
     */
    public function getHistory(){

        $historyCriteria = array(
            'all' => '',
            'actions' => ' AND type IS NULL',
            'workflow' => ' AND type="workflow"',
            'comments' => ' AND type="note"',
            'attachments' => ' AND type="attachment"',
            'marketing' => ' AND type IN ("email","webactivity","weblead","email_staged",'.
			                             '"email_opened","email_clicked","email_unsubscribed")',
            'webactivity' => 'AND type IN ("weblead","webactivity")'
        );
        if($this->relationships){
            $type = $this->associationType;
            $model = X2Model::model($type)->findByPk($this->associationId);
            if(count($model->relatedX2Models) > 0){
                $associationCondition =
					"((associationId={$this->associationId} AND ".
					"associationType='{$this->associationType}')";
                foreach($model->relatedX2Models as $relatedModel){
                    if($relatedModel instanceof X2Model){
                        $associationCondition .=
							" OR (associationId={$relatedModel->id} AND ".
							"associationType='{$relatedModel->myModelName}')";
                    }
                }
                $associationCondition.=")";
            }else{
                $associationCondition =
					'associationId='.$this->associationId.' AND '.
					'associationType="'.$this->associationType.'"';
            }
        }else{
            $associationCondition =
				'associationId='.$this->associationId.' AND '.
				'associationType="'.$this->associationType.'"';
        }
        $associationCondition =
			str_replace('Opportunity', 'opportunities', $associationCondition);
        $associationCondition = str_replace('Quote', 'quotes', $associationCondition);
        $visibilityCondition = '';
        $module = isset(Yii::app()->controller->module) ? Yii::app()->controller->module->getId() : Yii::app()->controller->getId();
        if(!Yii::app()->user->checkAccess($module.'Admin')){
            if(Yii::app()->params->admin->historyPrivacy == 'user'){
                $visibilityCondition = ' AND (assignedTo="'.Yii::app()->user->getName().'")';
            }elseif(Yii::app()->params->admin->historyPrivacy == 'group'){
                $visibilityCondition = ' AND (t.assignedTo IN (SELECT DISTINCT b.username FROM x2_group_to_user a INNER JOIN x2_group_to_user b ON a.groupId=b.groupId WHERE a.username="'.Yii::app()->user->getName().'") OR (t.assignedTo="'.Yii::app()->user->getName().'"))';
            }else{
                $visibilityCondition = ' AND (visibility="1" OR assignedTo="'.Yii::app()->user->getName().'")';
            }
        }
        return new CActiveDataProvider('Actions', array(
                    'criteria' => array(
                        'order' => 'IF(complete="No", GREATEST(createDate, IFNULL(dueDate,0), '.
						               'IFNULL(lastUpdated,0)), GREATEST(createDate, '.
						 			   'IFNULL(completeDate,0), IFNULL(lastUpdated,0))) DESC',
                        'condition' => $associationCondition.
                        $visibilityCondition.$historyCriteria[$this->historyType]
                    ),
                    'pagination' => array(
                        'pageSize' => $this->pageSize,
                    )
                ));
    }

}

