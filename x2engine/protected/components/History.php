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

    public static function getCriteria(
    $associationId, $associationType, $relationships, $historyType) {

        // Based on our filter, we need a particular additional criteria
        $historyCriteria = array(
            'all' => '',
            'action' => ' AND type IS NULL',
            'overdueActions' => ' AND type IS NULL AND complete="NO" AND dueDate <= ' . time(),
            'incompleteActions' => ' AND type IS NULL AND complete="NO"',
            'call' => ' AND type="call"',
            'note' => ' AND type="note"',
            'attachments' => ' AND type="attachment"',
            'event' => ' AND type="event"',
            'email' =>
            ' AND type IN ("email","email_staged","emailFrom",' .
            '"email_opened","email_clicked","email_unsubscribed")',
            'marketing' =>
            ' AND type IN ("email","webactivity","weblead","email_staged",' .
            '"email_opened","email_clicked","email_unsubscribed","event")',
             'products' => 'AND type="products"', 
            'quotes' => 'AND type like "quotes%"',
            'time' => ' AND type="time"',
            'webactivity' => 'AND type IN ("weblead","webactivity")',
            'workflow' => ' AND type="workflow"',
        );
        $multiAssociationIds = array($associationId);
        if ($relationships) {
            // Add association conditions for our relationships
            $type = $associationType;
            $model = X2Model::model($type)->findByPk($associationId);
            if (count($model->relatedX2Models) > 0) {
                $associationCondition = "((associationId={$associationId} AND " .
                        "associationType='{$associationType}')";
                // Loop through related models and add an association type OR for each
                foreach ($model->relatedX2Models as $relatedModel) {
                    if ($relatedModel instanceof X2Model) {
                        $multiAssociationIds[] = $relatedModel->id;
                        $associationCondition .=
                                " OR (associationId={$relatedModel->id} AND " .
                                "associationType='{$relatedModel->myModelName}')";
                    }
                }
                $associationCondition.=")";
            } else {
                $associationCondition = 'associationId=' . $associationId . ' AND ' .
                        'associationType="' . $associationType . '"';
            }
        } else {
            $associationCondition = 'associationId=' . $associationId . ' AND ' .
                    'associationType="' . $associationType . '"';
        }
        /* Fudge replacing Opportunity and Quote because they're stored as plural in the actions 
          table */
        $associationCondition = str_replace('Opportunity', 'opportunities', $associationCondition);
        $associationCondition = str_replace('Quote', 'quotes', $associationCondition);
        $visibilityCondition = '';
        $module = isset(Yii::app()->controller->module) ?
                Yii::app()->controller->module->getId() : Yii::app()->controller->getId();
        // Apply history privacy settings so that only allowed actions are viewable.
        if (!Yii::app()->user->checkAccess(ucfirst ($module) . 'Admin')) {
            if (Yii::app()->settings->historyPrivacy == 'user') {
                $visibilityCondition = ' AND (assignedTo="' . Yii::app()->user->getName() . '")';
            } elseif (Yii::app()->settings->historyPrivacy == 'group') {
                $visibilityCondition = ' AND (
                        t.assignedTo IN (
                            SELECT DISTINCT b.username 
                            FROM x2_group_to_user a 
                            INNER JOIN x2_group_to_user b ON a.groupId=b.groupId 
                            WHERE a.username="' . Yii::app()->user->getName() . '") OR 
                            (t.assignedTo="' . Yii::app()->user->getName() . '"))';
            } else {
                $visibilityCondition = ' AND (visibility="1" OR assignedTo="' . Yii::app()->user->getName() . '")';
            }
        }
        $orderStr = 'IF(complete="No", GREATEST(createDate, IFNULL(dueDate,0), ' .
                'IFNULL(lastUpdated,0)), GREATEST(createDate, ' .
                'IFNULL(completeDate,0), IFNULL(lastUpdated,0))) DESC';
        $mainCountCmd = Yii::app()->db->createCommand()
                ->select('COUNT(*)')
                ->from('x2_actions t')
                ->where($associationCondition .
                        $visibilityCondition . $historyCriteria[$historyType]);
        $mainCmd = Yii::app()->db->createCommand()
                ->select('*')
                ->from('x2_actions t')
                ->where($associationCondition .
                        $visibilityCondition . $historyCriteria[$historyType])
                ->order($orderStr);
        
        $multiAssociationIdParams = AuxLib::bindArray($multiAssociationIds);
        $associationCondition = '((' . $associationCondition . ') OR ' .
                'x2_action_to_record.recordId in ' . AuxLib::arrToStrList(
                        array_keys($multiAssociationIdParams)) . ')';

        $associationCondition = 'x2_action_to_record.recordId in ' . AuxLib::arrToStrList(
                        array_keys($multiAssociationIdParams));
        $joinCountCmd = Yii::app()->db->createCommand()
                ->select('COUNT(*)')
                ->from('x2_actions t')
                ->join('x2_action_to_record', 'actionId=t.id')
                ->where($associationCondition . $visibilityCondition . $historyCriteria[$historyType] . ' AND 
                x2_action_to_record.recordType=:recordType');
        $joinCmd = Yii::app()->db->createCommand()
                ->select('t.*')
                ->from('x2_actions t')
                ->join('x2_action_to_record', 'actionId=t.id')
                ->where($associationCondition . $visibilityCondition . $historyCriteria[$historyType] . ' AND 
                x2_action_to_record.recordType=:recordType');
        
        $count = $mainCountCmd->union($joinCountCmd->getText())->queryScalar(array_merge(array(':recordType' => X2Model::getModelName($associationType)), $multiAssociationIdParams));
        return array(
            'cmd' => $mainCmd->union($joinCmd->getText()),
            'count' => $count,
            'params' => array_merge(array(':recordType' => X2Model::getModelName($associationType)), $multiAssociationIdParams));
    }

    /**
     * This renders the list view of the action history for a record.
     */
    public function run() {
        if ($this->filters) {
            // Filter tabs allowed
            $historyTabs = array(
                'all' => Yii::t('app', 'All'),
                'action' => Yii::t('app', '{actions}', array(
                    '{actions}' => Modules::displayName(true, 'Actions'),
                )),
                'overdueActions' => Yii::t('app', 'Overdue {actions}', array(
                    '{actions}' => Modules::displayName(true, 'Actions'),
                )),
                'incompleteActions' => Yii::t('app', 'Incomplete {actions}', array(
                    '{actions}' => Modules::displayName(true, 'Actions'),
                )),
                'attachments' => Yii::t('app', 'Attachments'),
                'call' => Yii::t('app', 'Calls'),
                'note' => Yii::t('app', 'Comments'),
                'email' => Yii::t('app', 'Emails'),
                'event' => Yii::t('app', 'Events'),
                'marketing' => Yii::t('app', 'Marketing'),
                'time' => Yii::t('app', 'Logged Time'),
                 'products' => Yii::t('app', '{products}', array(
                    '{products}' => Modules::displayName(true, 'Products'),
                )), 
                'quotes' => Yii::t('app', 'Quotes'),
                'webactivity' => Yii::t('app', 'Web Activity'),
                'workflow' => Yii::t('app', '{process}', array(
                    '{process}' => Modules::displayName(true, 'Workflow'),
                )),
            );
            $profile = Yii::app()->params->profile;
            if (isset($profile)) { // Load their saved preferences from the profile
                // No way to give truly infinite pagination, fudge it by making it 10,000
                $this->pageSize = $profile->historyShowAll ? 10000 : 10;
                $this->relationships = $profile->historyShowRels;
            }
            // See if we can filter for what they wanted
            if (isset($_GET['history']) && array_key_exists($_GET['history'], $historyTabs)) {
                $this->historyType = $_GET['history'];
            }
            if (isset($_GET['pageSize'])) {
                $this->pageSize = $_GET['pageSize'];
                if (isset($profile)) {
                    // Save profile preferences
                    $profile->historyShowAll = $this->pageSize > 10 ? 1 : 0;
                    $profile->update(array('historyShowAll'));
                }
            }
            if (isset($_GET['relationships'])) {
                $this->relationships = $_GET['relationships'];
                if (isset($profile)) {
                    $profile->historyShowRels = $this->relationships; // Save profile preferences
                    $profile->update(array('historyShowRels'));
                }
            }
        } else {
            $historyTabs = array();
        }
        // Register JS to make the history tabs update the history when selected.
        Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getBaseUrl() . '/js/ActionHistory.js');
        Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getBaseUrl() . '/js/EnlargeableImage.js',
                CClientScript::POS_END);
        Yii::app()->clientScript->registerScript('history-tabs', "
            x2.actionHistory = new x2.ActionHistory ({
                relationshipFlag: {$this->relationships}
            });
        ", CClientScript::POS_END);

        Yii::app()->clientScript->registerCssFile(
            Yii::app()->theme->baseUrl.'/css/actionHistory.css'); 

        $this->widget('application.components.X2ListView', array(
            'pager' => array(
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
            'itemView' => 'application.modules.actions.views.actions._historyView',
            'htmlOptions' => array('class' => 'action list-view'),
            'template' =>
            '<div class="form action-history-controls">' .
            CHtml::dropDownList(
                    'history-selector', $this->historyType, $historyTabs, array(
                'class' => 'x2-select'
                    )
            ) .
            '<span style="margin-top:5px;" class="right">' .
            CHtml::link(
                    Yii::t('app', 'Toggle Text'), '#', array(
                'id' => 'history-collapse', 'class' => 'x2-hint',
                'title' =>
                Yii::t('app', 'Click to toggle showing the full text of History items.')
                    )
            )
            . ' | ' . CHtml::link(
                    Yii::t('app', 'Show All'), '#', array(
                'id' => 'show-history-link', 'class' => 'x2-hint',
                'title' =>
                Yii::t('app', 'Click to increase the number of History items shown.'),
                'style' => $this->pageSize > 10 ? 'display:none;' : ''
                    )
            )
            . CHtml::link(
                    Yii::t('app', 'Show Less'), '#', array(
                'id' => 'hide-history-link', 'class' => 'x2-hint', '
                        title' =>
                Yii::t('app', 'Click to decrease the number of History items shown.'),
                'style' => $this->pageSize > 10 ? '' : 'display:none;'
                    )
            )
            . ((!Yii::app()->user->isGuest) ?
                    ' | ' . CHtml::link(
                            Yii::t('app', 'Relationships'), '#', array(
                        'id' => 'show-relationships-link', 'class' => 'x2-hint',
                        'title' =>
                        Yii::t('app', 'Click to toggle showing actions associated with related records.'))) : '')
            . '</span></div> {sorter}{items}{pager}',
        ));
    }

    /**
     * Function to actually generate the condition and data provider for the
     * History CListView.
     * @return \CActiveDataProvider
     */
    public function getHistory() {
        $historyCmd = self::getCriteria(
                        $this->associationId, $this->associationType, $this->relationships, $this->historyType);
        return new CSqlDataProvider($historyCmd['cmd'], array(
            'totalItemCount' => $historyCmd['count'],
            'params' => $historyCmd['params'],
            'pagination' => array(
                'pageSize' => $this->pageSize,
            ),
        ));
    }

}
