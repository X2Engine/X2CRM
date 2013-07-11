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

/**
 * Renders a CListView containing all actions associated with the specified model
 *
 * @package X2CRM.components
 */
class History extends X2Widget {

    public $associationType;  // type of record to associate actions with
    public $associationId = '';  // record to associate actions with
    public $filters = true;
    public $historyType = 'all';
    public $pageSize = 10;
    public $relationships = 0;

    public function run(){
        if($this->filters){
            $historyTabs = array(
                'all' => 'All',
                'actions' => 'Actions',
                'comments' => 'Comments',
                'workflow' => 'Workflow',
                'attachments' => 'Attachments',
                'marketing' => 'Marketing',
                'webactivity'=>'Web Activity',
            );
            $profile=Yii::app()->params->profile;
            $this->pageSize=$profile->historyShowAll?10000:10;
            $this->relationships=$profile->historyShowRels;
            if(isset($_GET['history']) && array_key_exists($_GET['history'], $historyTabs)){
                $this->historyType = $_GET['history'];
            }
            if(isset($_GET['pageSize'])){
                $this->pageSize=$_GET['pageSize'];
                $profile->historyShowAll=$this->pageSize>10?1:0;
                $profile->update(array('historyShowAll'));
            }
            if(isset($_GET['relationships'])){
                $this->relationships=$_GET['relationships'];
                $profile->historyShowRels=$this->relationships;
                $profile->update(array('historyShowRels'));
            }
        }else{
            $historyTabs = array();
        }
        Yii::app()->clientScript->registerScript('history-tabs',"
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
        ");
        $this->widget('zii.widgets.CListView', array(
            'id' => 'history',
            'dataProvider' => $this->getHistory(),
            'viewData'=>array(
                'relationshipFlag'=>$this->relationships,
            ),
            'itemView' => 'application.modules.actions.views.actions._view',
            'htmlOptions' => array('class' => 'action list-view'),
            'template' => '<div class="form">'.CHtml::dropDownList('history-selector',$this->historyType,$historyTabs).
            '<span style="margin-top:5px;" class="right">'.CHtml::link('Toggle Text','#',array('id'=>'history-collapse','class'=>'x2-hint','title'=>'Click to toggle showing the full text of History items.'))
            .' | '.CHtml::link('Show All','#',array('id'=>'show-history-link','class'=>'x2-hint','title'=>'Click to increase the number of History items shown.','style'=>$this->pageSize>10?'display:none;':''))
            .CHtml::link('Show Less','#',array('id'=>'hide-history-link','class'=>'x2-hint','title'=>'Click to decrease the number of History items shown.','style'=>$this->pageSize>10?'':'display:none;'))
            .((!Yii::app()->user->isGuest)?' | '.CHtml::link('Relationships','#',array('id'=>'show-relationships-link','class'=>'x2-hint','title'=>'Click to toggle showing actions associated with related records.')):'')
            .'</span></div> {sorter}{items}{pager}',
        ));
    }

    public function getHistory(){

        $historyCriteria = array(
            'all' => '',
            'actions' => ' AND type IS NULL',
            'workflow' => ' AND type="workflow"',
            'comments' => ' AND type="note"',
            'attachments' => ' AND type="attachment"',
            'marketing' => ' AND type IN ("email","webactivity","weblead","email_staged","email_opened","email_clicked","email_unsubscribed")',
            'webactivity'=>'AND type IN ("weblead","webactivity")'
        );
        if($this->relationships){
            $type=$this->associationType;
            $model=X2Model::model($type)->findByPk($this->associationId);
            if(count($model->relatedX2Models)>0){
                $associationCondition="((associationId={$this->associationId} AND associationType='{$this->associationType}')";
                foreach($model->relatedX2Models as $relatedModel){
                        $associationCondition.=" OR (associationId={$relatedModel->id} AND associationType='{$relatedModel->myModelName}')";
                }
                $associationCondition.=")";
            }else{
                $associationCondition='associationId='.$this->associationId.' AND associationType="'.$this->associationType.'"';
            }
        }else{
            $associationCondition='associationId='.$this->associationId.' AND associationType="'.$this->associationType.'"';
        }
        $associationCondition=str_replace('Opportunity','opportunities',$associationCondition);
        $associationCondition=str_replace('Quote','quotes',$associationCondition);
        return new CActiveDataProvider('Actions', array(
                    'criteria' => array(
                        'order' => 'IF(complete="No", GREATEST(createDate, IFNULL(dueDate,0), IFNULL(lastUpdated,0)), GREATEST(createDate, IFNULL(completeDate,0), IFNULL(lastUpdated,0))) DESC',
                        'condition' => $associationCondition.
					'AND (visibility="1" OR assignedTo="'.Yii::app()->user->getName().'")'.$historyCriteria[$this->historyType]
                    ),
                    'pagination'=>array(
                        'pageSize'=>$this->pageSize,
                    )
                ));
    }

}

