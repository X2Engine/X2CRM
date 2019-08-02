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
 * External action for creating and editing X2Flow automation workflows
 *
 * @package application.components
 */
class FlowDesignerAction extends CAction {
    public function run($pageSize=null) {

        $viewParams = array ();

        if(isset($_GET['id'])){
            $flow = $this->loadModel($_GET['id']);
            User::addRecentItem('f', $flow->id, Yii::app()->user->getId()); 
        } else {
            $flow = new X2Flow;
        }
        if(isset($_POST['X2Flow'])) {
            $flow->attributes = $_POST['X2Flow'];
            
            $flowData = CJSON::decode($flow->flow);
            $flow->name = $_POST['X2Flow']['name'];
            $flow->description = filter_var($_POST['X2Flow']['description'], FILTER_SANITIZE_STRING, array('flags' => FILTER_FLAG_NO_ENCODE_QUOTES));
            $flowData['flowName'] = $flow->name;
            $flowData['flowDesc'] = $flow->description;
            $flow->flow = CJSON::encode ($flowData);
            $flow->active = $_POST['X2Flow']['active'];

			if($flow->save()) {
				$this->getController()->redirect(array('/studio/flowDesigner','id'=>$flow->id));
            }
		}

        if (isset ($flow->name)) {
            $triggerLogsDataProvider = new CActiveDataProvider('TriggerLog', array(
                'criteria' => array(
                    'condition' =>
                        'flowId='.$flow->id,
                    'order' => 'triggeredAt DESC'
                ),
                'pagination'=>array(
                    'pageSize' => !empty($pageSize) ?
                        $pageSize :
                        Profile::getResultsPerPage()
                ),
            ));
            $viewParams['triggerLogsDataProvider'] = $triggerLogsDataProvider;
        }

        if (isset ($_GET['ajax']) && $_GET['ajax'] = 'trigger-log-grid') {
            $this->controller->renderPartial (
                '_triggerLogsGridView', array (
                    'triggerLogsDataProvider' => $triggerLogsDataProvider,
                    'flowId' => $flow->id,
                    'parentView' => 'flowEditor'
                )
            );
            Yii::app()->end ();
        }

        // order action types
        $actionTypes = X2FlowAction::getActionTypes();
        asort ($actionTypes);

        $viewParams['model'] = $flow;
        $viewParams['actionTypes'] = $actionTypes;
        $viewParams['triggerTypes'] = X2FlowTrigger::getTriggerTypes();
        $viewParams['requiresCron'] = array_keys(
            array_merge(X2FlowAction::getActionTypes('requiresCron',true),
                X2FlowTrigger::getTriggerTypes('requiresCron',true)));

        $this->getController()->render('flowEditor', $viewParams);
    }

    /**
     * Saves all the items in a given branch. Recurses when a switch is encountered.
     * @param array &$items the items in the current branch
     * @return integer ID of the first item in this branch
     */
    protected function saveFlowBranch(&$items,$flowId) {
        for($i=count($items)-1;$i>=0;$i--) {        // loop backwards through the flow
            $flowItem = new X2FlowItem;
            $flowItem->flowId = $flowId;
            $flowItem->type = $item[$i]['type'];

            if($item[$i]['type'] == 'switch') {
                $flowItem->config = CJSON::encode($items[$i]['conditions']);    // save the conditions for this switch
                $flowItem->nextIfTrue = $this->saveFlowBranch($items[$i]['trueBranch'],$flowId);
                $flowItem->nextIfFalse = $this->saveFlowBranch($items[$i]['falseBranch'],$flowId);
            } else {
                $flowItem->config = CJSON::encode($items[$i]['fields']);    // save the conditions for this switch
                $flowItem->nextIfTrue = $followingId;
            }
            if($flowItem->save())
                return $flowItem->id;    // return ID for the next call up the stack to set as $item->nextIfTrue
            else
                return false;    // invalid, abort
        }
        return null;    // end of flow branch
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     *
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     */
    public function loadModel($id) {
        if(null === $model = CActiveRecord::model('X2Flow')->findByPk((int)$id))
            throw new CHttpException(404,Yii::t('app','The requested page does not exist.'));
        return $model;
    }
}
?>
