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

/**
 * Renders a CListView containing all actions associated with the specified model
 *
 * @package application.components
 */
class History extends X2Widget {

    public $associationType;  // type of record to associate actions with
    public $associationId = '';  // record to associate actions with
    public $filters = true;  // whether or not action types can be filtered on
    public $historyType = 'all'; // default filter type
    public $pageSize = 10; // how many records to show per page
    public $relationships = 0; // don't show actions on related records by default

    /**
     * This renders the list view of the action history for a record.
     */
    public function run(){
        if($this->filters){
            // Filter tabs allowed
            $historyTabs = array(
                'all' => Yii::t('app', 'All'),
                'actions' => Yii::t('app', '{actions}', array(
                    '{actions}' => Modules::displayName(true, 'Actions'),
                )),
                'overdueActions' => Yii::t('app', 'Overdue {actions}', array(
                    '{actions}' => Modules::displayName(true, 'Actions'),
                )),
                'incompleteActions' => Yii::t('app', 'Incomplete {actions}', array(
                    '{actions}' => Modules::displayName(true, 'Actions'),
                )),
                'attachments' => Yii::t('app', 'Attachments'),
                'calls' => Yii::t('app', 'Calls'),
                'comments' => Yii::t('app', 'Comments'),
                'emails' => Yii::t('app', 'Emails'),
                'event' => Yii::t('app', 'Events'),
                'marketing' => Yii::t('app', 'Marketing'),
                'time' => Yii::t('app', 'Logged Time'),
                
                'quotes' => Yii::t('app', 'Quotes'),
                'webactivity' => Yii::t('app', 'Web Activity'),
                'workflow' => Yii::t('app', '{process}', array(
                    '{process}' => Modules::displayName(true, 'Workflow'),
                )),
            );
            $profile = Yii::app()->params->profile;
            if(isset($profile)){ // Load their saved preferences from the profile
                // No way to give truly infinite pagination, fudge it by making it 10,000
                $this->pageSize = $profile->historyShowAll ? 10000 : 10; 
                $this->relationships = $profile->historyShowRels;
            }
            // See if we can filter for what they wanted
            if(isset($_GET['history']) && array_key_exists($_GET['history'], $historyTabs)){ 
                $this->historyType = $_GET['history'];
            }
            if(isset($_GET['pageSize'])){
                $this->pageSize = $_GET['pageSize'];
                if(isset($profile)){
                    // Save profile preferences
                    $profile->historyShowAll = $this->pageSize > 10 ? 1 : 0; 
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
        // Register JS to make the history tabs update the history when selected.
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

        Yii::app()->clientScript->registerCss('historyWidgetCss',"
            .action-history-controls {
                border-radius: 4px 4px 0 0 !important; 
                -moz-border-radius: 4px 4px 0 0 !important;
                -webkit-border-radius: 4px 4px 0 0 !important;
                -o-border-radius: 4px 4px 0 0 !important;
                margin-bottom: -2px !important;
                border-bottom: none !important;
                border: 1px solid #c5c5c5 !important;
            }
            .history .pager {
                border-radius: 0 0 4px 4px !important; 
                -moz-border-radius: 0 0 4px 4px !important;
                -webkit-border-radius: 0 0 4px 4px !important;
                -o-border-radius: 0 0 4px 4px !important;
                border: 1px solid #c5c5c5 !important;
                margin-top: -3px !important;
            }
        ");

        $this->widget('application.components.X2ListView', array(
            'pager' => array (
                'class' => 'CLinkPager', 
                'header' => '',
                'firstPageCssClass' => '',
                'lastPageCssClass' => '',
                'prevPageLabel' => '<',
                'nextPageLabel' => '>',
                'firstPageLabel' => '<<',
                'lastPageLabel' => '>>',
            ),
            'id' => 'history',
            'dataProvider' => $this->getHistory(),
            'viewData' => array(
                // Pass relationship flag to the views so they can modify their layout slightly
                'relationshipFlag' => $this->relationships, 
            ),
            'itemView' => 'application.modules.actions.views.actions._view',
            'htmlOptions' => array('class' => 'action list-view'),
            'template' => 
            '<div class="form action-history-controls">'.
                CHtml::dropDownList(
                    'history-selector',
                    $this->historyType,
                    $historyTabs,
                    array (
                        'class' => 'x2-select'
                    )
                ).
            '<span style="margin-top:5px;" class="right">'.
                CHtml::link(
                    Yii::t('app', 'Toggle Text'), '#', 
                    array(
                        'id' => 'history-collapse', 'class' => 'x2-hint', 
                        'title' => 
                            Yii::t('app', 'Click to toggle showing the full text of History items.')
                    )
                )
                .' | '.CHtml::link(
                    Yii::t('app', 'Show All'), '#', 
                    array(
                        'id' => 'show-history-link', 'class' => 'x2-hint', 
                        'title' => 
                            Yii::t('app', 'Click to increase the number of History items shown.'), 
                        'style' => $this->pageSize > 10 ? 'display:none;' : ''
                    )
                )
                .CHtml::link(
                    Yii::t('app', 'Show Less'), '#', 
                    array(
                        'id' => 'hide-history-link', 'class' => 'x2-hint', '
                        title' => 
                            Yii::t('app', 'Click to decrease the number of History items shown.'), 
                        'style' => $this->pageSize > 10 ? '' : 'display:none;'
                    )
                )
                .((!Yii::app()->user->isGuest) ? 
                    ' | '.CHtml::link(
                        Yii::t('app', 'Relationships'), '#', 
                        array(
                            'id' => 'show-relationships-link', 'class' => 'x2-hint',
                            'title' => 
                Yii::t('app', 'Click to toggle showing actions associated with related records.'))) 
                    : '')
            .'</span></div> {sorter}{items}{pager}',
        ));
    }

    /**
     * Function to actually generate the condition and data provider for the
     * History CListView.
     * @return \CActiveDataProvider
     */
    public function getHistory(){
        // Based on our filter, we need a particular additional criteria
        $historyCriteria = array(
            'all' => '',
            'actions' => ' AND type IS NULL',
            'overdueActions' => ' AND type IS NULL AND complete="NO" AND dueDate <= '.time (),
            'incompleteActions' => ' AND type IS NULL AND complete="NO"',
            'calls' => ' AND type="call"',
            'comments' => ' AND type="note"',
            'attachments' => ' AND type="attachment"',
            'event' => ' AND type="event"',
            'emails' => 
                ' AND type IN ("email","email_staged",'.
                    '"email_opened","email_clicked","email_unsubscribed")',
            'marketing' => 
                ' AND type IN ("email","webactivity","weblead","email_staged",'.
                    '"email_opened","email_clicked","email_unsubscribed","event")',
             
            'quotes' => 'AND type="quotes"',
            'time' => ' AND type="time"',
            'webactivity' => 'AND type IN ("weblead","webactivity")',
            'workflow' => ' AND type="workflow"',
        );
        $multiAssociationIds = array ($this->associationId);
        if($this->relationships){
            // Add association conditions for our relationships
            $type = $this->associationType;
            $model = X2Model::model($type)->findByPk($this->associationId);
            if(count($model->relatedX2Models) > 0){
                $associationCondition =
					"((associationId={$this->associationId} AND ".
					"associationType='{$this->associationType}')";
                // Loop through related models and add an association type OR for each
                foreach($model->relatedX2Models as $relatedModel){
                    if($relatedModel instanceof X2Model){
                        $multiAssociationIds[] = $relatedModel->id;
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
        /* Fudge replacing Opportunity and Quote because they're stored as plural in the actions 
        table */
        $associationCondition =
			str_replace('Opportunity', 'opportunities', $associationCondition);
        $associationCondition = str_replace('Quote', 'quotes', $associationCondition);
        $visibilityCondition = '';
        $module = isset(Yii::app()->controller->module) ? 
            Yii::app()->controller->module->getId() : Yii::app()->controller->getId();
        // Apply history privacy settings so that only allowed actions are viewable.
        if(!Yii::app()->user->checkAccess($module.'Admin')){
            if(Yii::app()->settings->historyPrivacy == 'user'){
                $visibilityCondition = ' AND (assignedTo="'.Yii::app()->user->getName().'")';
            }elseif(Yii::app()->settings->historyPrivacy == 'group'){
                $visibilityCondition = 
                    ' AND (
                        t.assignedTo IN (
                            SELECT DISTINCT b.username 
                            FROM x2_group_to_user a 
                            INNER JOIN x2_group_to_user b ON a.groupId=b.groupId 
                            WHERE a.username="'.Yii::app()->user->getName().'") OR 
                            (t.assignedTo="'.Yii::app()->user->getName().'"))';
            }else{
                $visibilityCondition = 
                    ' AND (visibility="1" OR assignedTo="'.Yii::app()->user->getName().'")';
            }
        }

        $multiAssociationIdParams = AuxLib::bindArray ($multiAssociationIds);
        $associationCondition = 
            '(('.$associationCondition.') OR '.
            'x2_action_to_record.recordId in '.AuxLib::arrToStrList (
                array_keys ($multiAssociationIdParams)).')';

        return new CActiveDataProvider('Actions', array(
            'criteria' => array(
                'order' => 'IF(complete="No", GREATEST(createDate, IFNULL(dueDate,0), '.
                               'IFNULL(lastUpdated,0)), GREATEST(createDate, '.
                               'IFNULL(completeDate,0), IFNULL(lastUpdated,0))) DESC',
                'condition' => $associationCondition.
                    $visibilityCondition.$historyCriteria[$this->historyType],
                'join' => 'LEFT JOIN x2_action_to_record ON actionId=t.id AND 
                    x2_action_to_record.recordType=:recordType',
                'params' => array_merge (array (
                    ':recordType' => X2Model::getModelName ($this->associationType)
                ), $multiAssociationIdParams),
                'distinct' => true
            ),
            'pagination' => array(
                'pageSize' => $this->pageSize,
            )
        ));
    }

}

