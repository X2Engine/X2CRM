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

// Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_workflows".
 * @package X2CRM.modules.workflow.models
 */
class Workflow extends CActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @return Workflow the static model class
	 */
	public static function model($className=__CLASS__) { return parent::model($className); }

	/**
	 * @return string the associated database table name
	 */
	public function tableName() { return 'x2_workflows'; }

	public function behaviors() {
		return array_merge(parent::behaviors(),array(
			'X2LinkableBehavior'=>array(
				'class'=>'X2LinkableBehavior',
				'module'=>'workflow'
			)
		));
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('lastUpdated', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>250),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, lastUpdated', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'stages'=>array(self::HAS_MANY, 'WorkflowStage', 'workflowId', 'order'=>'stageNumber ASC'),
		);
	}
	
	/**
	 * @return array behaviors.
	 */
	// public function behaviors(){
		// return array('CSaveRelationsBehavior' => array('class' => 'application.components.CSaveRelationsBehavior'));
	// }
	
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'name' => Yii::t('workflow','Workflow Name'),
			'lastUpdated' => Yii::t('workflow','Last Updated'),
		);
	}

	public static function getList($enableNone=true) {
		$workflows = X2Model::model('Workflow')->findAll();
		$list = array();
		if($enableNone)
			$list[0] = Yii::t('app','None');
		foreach ($workflows as $model)
			$list[$model->id] = $model->name;
		return $list;
	}

	public static function getWorkflowStatus($workflowId,$modelId = 0,$modelType = '') {
	
		$workflowStatus = array(
			'id'=>$workflowId,
			'stages'=>array(),
			'started'=>false,
			'completed'=>false
		);
		
		$workflowStages = X2Model::model('WorkflowStage')->findAllByAttributes(array('workflowId'=>$workflowId),new CDbCriteria(array('order'=>'id ASC')));
		
		// $workflowStatus[] = $workflowId;
		foreach($workflowStages as &$stage) {	// load all WorkflowStage names into workflowStatus
			$workflowStatus['stages'][$stage->stageNumber] = array(
				'name'=>$stage->name,
				'requirePrevious'=>$stage->requirePrevious,
				'roles'=>$stage->roles,
				'requireComment'=>$stage->requireComment
			);
		}
		unset($stage);

		$workflowActions = array();
		
		if(!empty($modelId)) {
			$workflowActions = X2Model::model('Actions')->findAllByAttributes(
				array('associationId'=>$modelId,'associationType'=>$modelType,'type'=>'workflow','workflowId'=>$workflowId),
				new CDbCriteria(array('order'=>'createDate ASC'))
			);
		}
		
		foreach($workflowActions as &$action) {
			
			if($action->stageNumber < 1 || $action->stageNumber > count($workflowStages)) {
				$action->delete();
				continue;
			}
			
			$workflowStatus['started'] = true;	// clearly there's at least one stage up in here
		
			$stage = $action->stageNumber;
			// if(!is_array($workflowStatus[$action->stageNumber]))
				// $workflowStatus[$action->stageNumber] = array($workflowStatus[$action->stageNumber]);
			
			// decode workflowActions into a funnel list
			$workflowStatus['stages'][$stage]['createDate'] = $action->createDate;		// Note: multiple actions with the same stage will overwrite each other
			$workflowStatus['stages'][$stage]['completeDate'] = $action->completeDate;
			$workflowStatus['stages'][$stage]['complete'] = ($action->complete == 'Yes') || (!empty($action->completeDate) && $action->completeDate < time());	// determine whether stage is complete			
			$workflowStatus['stages'][$stage]['description'] = $action->actionDescription;																		// or the stage is beyond the possible range somehow
			
			/* $actionData = explode(':',$action->actionDescription);
			// decode workflowActions into a funnel list
			if(count($actionData) >= 2 && $actionData[0] == $workflowId && $actionData[1] <= count($workflowStages)) {		// ignore action if it's for a different workflow
				$workflowStatus[$actionData[1]]['createDate'] = $action->createDate;				// or the stage is beyond the possible range somehow
				$workflowStatus[$actionData[1]]['completeDate'] = $action->completeDate;		// Note: multiple actions with the same stage will overwrite each other
				$workflowStatus[$actionData[1]]['complete'] = ($action->complete == 'Yes') || (!empty($action->completeDate) && $action->completeDate < time());	// determine whether stage is complete
			} */
		}
		
		$workflowStatus['completed'] = true;
		foreach($workflowStatus['stages'] as &$stage) {		// now scan through and see if there are any incomplete stages
			if(!isset($stage['completeDate'])) {
				$workflowStatus['completed'] = false;
				break;
			}
		}
		
		return $workflowStatus;
	}
	
	public static function getStages($id) {
		return Yii::app()->db->createCommand()
			->select('name')
			->from('x2_workflow_stages')
			->where('workflowId=:id',array(':id'=>$id))
			->order('stageNumber ASC')
			->queryColumn();
	}
	
	public static function renderWorkflow(&$workflowStatus) {
		
		$workflowId = $workflowStatus['id'];
		
		$stageCount = count($workflowStatus['stages']);
		
		if($stageCount < 1)
			return '';
		
		// generate a gradient between green and orange in $stageCount steps
		$startingRgb = Workflow::hex2rgb('c4f455');
		$endingRgb = Workflow::hex2rgb('f18c1c');

		$rgbDifference = array(
			$endingRgb[0] - $startingRgb[0],
			$endingRgb[1] - $startingRgb[1],
			$endingRgb[2] - $startingRgb[2],
		);
		
		$rgbSteps = array(
			$rgbDifference[0] / $stageCount,
			$rgbDifference[1] / $stageCount,
			$rgbDifference[2] / $stageCount,
		);
		
		$startingWidth = 160;
		$endingWidth = 100;
		
		$widthDifference = $endingWidth - $startingWidth;
		$widthStep = $widthDifference / $stageCount;
		

		$funnelStr = '';
		$statusStr = '';
		// die(var_dump($workflowStatus));
		
		// $started = false;
		for($stage=1; $stage<=$stageCount;$stage++) {

			if(!empty($workflowStatus['stages'][$stage]['roles']))	// if roles are specified, check if user has any of them
				$editPermission = count(array_intersect(Yii::app()->params->roles,$workflowStatus['stages'][$stage]['roles'])) > 0;
			else
				$editPermission = true;	// default is full permission for everybody
				
			if(Yii::app()->user->checkAccess('AdminIndex'))	// admin override
				$editPermission = true;
			
			$color = Workflow::rgb2hex(
				$startingRgb[0] + ($rgbSteps[0]*$stage),
				$startingRgb[1] + ($rgbSteps[1]*$stage),
				$startingRgb[2] + ($rgbSteps[2]*$stage)
			);
			$width = round($startingWidth + $widthStep*$stage);
			
			$funnelStr .= '<div class="workflow-funnel-stage" style="width:'.$width.'px;background:'.$color.';"><b>'.$workflowStatus['stages'][$stage]['name'].'</b></div>';;
			$statusStr .= '<div class="row"><div class="workflow-funnel-box" style="width:'.($startingWidth+10).'px">'
				.'<div class="workflow-funnel-stage" style="width:'.$width.'px;background:'.$color.';"><b>'.$workflowStatus['stages'][$stage]['name'].'</b></div></div>';

			$previousCheck = true;
			if($workflowStatus['stages'][$stage]['requirePrevious'] == 1) {	// check if all stages before this one are complete
				for($i=1; $i<$stage; $i++) {
					if(empty($workflowStatus['stages'][$i]['complete'])) {
						$previousCheck = false;
						break;
					}
				}
			} else if($workflowStatus['stages'][$stage]['requirePrevious'] < 0) {		// or just check if the specified stage is complete
				if(empty($workflowStatus['stages'][ -1*$workflowStatus['stages'][$stage]['requirePrevious'] ]['complete']))
					$previousCheck = false;
			}
				
			if(isset($workflowStatus['stages'][$stage]['createDate'])) {
				
				// check if this is the last stage to be started or completed
				$latestStage = true;
				if($stage < $stageCount) {
					for($i=$stage+1; $i<=$stageCount; $i++) {
						if(!empty($workflowStatus['stages'][$i]['createDate'])) {
							$latestStage = false;
							break;
						}
					}
				}
				$statusStr .= '<div class="workflow-status">';
				// if($editPermission)
				$statusStr .= ' <a href="javascript:void(0)" class="right" onclick="workflowStageDetails('.$workflowId.','.$stage.');">['.Yii::t('workflow','Details').']</a> ';
				
				
				if($workflowStatus['stages'][$stage]['complete']) {
					$statusStr .= Yii::t('workflow','Completed').' '.date("Y-m-d",$workflowStatus['stages'][$stage]['completeDate']);
					// X2Date::dateBox($workflowStatus['stages'][$stage]['completeDate']);

					// can only undo if there is no restriction on backdating, or we're still within the edit time window
					$allowUndo = Yii::app()->params->admin->workflowBackdateWindow < 0 || (time() - $workflowStatus['stages'][$stage]['completeDate']) < Yii::app()->params->admin->workflowBackdateWindow;
					
					if($editPermission && ($allowUndo || Yii::app()->user->checkAccess('AdminIndex')))
						$statusStr .= ' <a href="javascript:void(0)" class="right" onclick="revertWorkflowStage('.$workflowId.','.$stage.');">['.Yii::t('workflow','Undo').']</a>';
				} else {
					// $started = true;
					$statusStr .= '<b>'.Yii::t('workflow','Started').' '.date("Y-m-d",$workflowStatus['stages'][$stage]['createDate']).'</b>';
					// if(!$latestStage)
					
					if($editPermission){
						$statusStr .= '<a href="javascript:void(0)" class="right" onclick="revertWorkflowStage('.$workflowId.','.$stage.');">['.Yii::t('workflow','Undo').']</a>';
                    }else{
                        $statusStr.='<span class="right workflow-hint" style="color:gray;" title="You do not have permission to revert this stage.">['.Yii::t('workflow','Undo').']</span>';
                    }
                    if($previousCheck && $editPermission) {
						if($workflowStatus['stages'][$stage]['requireComment'])
							$statusStr .= ' <a href="javascript:void(0)" class="right" onclick="workflowCommentDialog('.$workflowId.','.$stage.');">['.Yii::t('workflow','Complete').']</a> ';
						else
							$statusStr .= ' <a href="javascript:void(0)" class="right" onclick="completeWorkflowStage('.$workflowId.','.$stage.');">['.Yii::t('workflow','Complete').']</a> ';
					}elseif($previousCheck && !$editPermission){
                        $statusStr.='<span class="right workflow-hint" style="color:gray;" title="You do not have permission to complete this stage.">['.Yii::t('workflow','Complete').']</span>';
                    }
				}
				$statusStr .= '</div>';
			} else {
				// if(!$started) {
					// $started = true;
					if($editPermission && $previousCheck)
						$statusStr .= '<div class="workflow-status"><a href="javascript:void(0)" class="right" onclick="startWorkflowStage('.$workflowId.','.$stage.');">['.Yii::t('workflow','Start').']</a></div>';
				// }
			}
			$statusStr .= "</div>\n";
		}
		return $statusStr."<script>$('.workflow-hint').qtip();</script>";
		// $str = '<div class="row">
					// <div class="cell"><div class="workflow-funnel-box" style="width:'.($startingWidth+10).'px">'.$funnelStr.'</div></div>
					// <div class="cell" style="width:250px;">'.$statusStr.'</div>
				// </div>';
		// return $str;
	}
	
	public static function renderWorkflowStats(&$workflowStatus) {
		$dateRange=Yii::app()->controller->getDateRange();
		$user=isset($_GET['users'])?$_GET['users']:''; 
		if(!empty($user)){
			$userString=" AND assignedTo='$user' ";
		}else{
			$userString="";
		}
		$stageCount = count($workflowStatus['stages']);
		
		if($stageCount < 1)
			return '';
		
		
		$startingRgb = Workflow::hex2rgb('c4f455');
		$endingRgb = Workflow::hex2rgb('f18c1c');

		$rgbDifference = array(
			$endingRgb[0] - $startingRgb[0],
			$endingRgb[1] - $startingRgb[1],
			$endingRgb[2] - $startingRgb[2],
		);
		
		$rgbSteps = array(
			$rgbDifference[0] / $stageCount,
			$rgbDifference[1] / $stageCount,
			$rgbDifference[2] / $stageCount,
		);
		
		$startingWidth = 260;
		$endingWidth = 150;
		
		$widthDifference = $endingWidth - $startingWidth;
		$widthStep = $widthDifference / $stageCount;
		

		$funnelStr = '';
		$statusStr = '';

		for($i=1; $i<=$stageCount;$i++) {
		
			$color = Workflow::rgb2hex(
				$startingRgb[0] + ($rgbSteps[0]*$i),
				$startingRgb[1] + ($rgbSteps[1]*$i),
				$startingRgb[2] + ($rgbSteps[2]*$i)
			);
			$width = round($startingWidth + $widthStep*$i);

			$contacts = X2Model::model('Actions')->countByAttributes(
				array('type'=>'workflow','workflowId'=>$workflowStatus['id'],'stageNumber'=>$i),
				new CDbCriteria(array('condition'=>"complete != 'Yes' $userString AND (completeDate IS NULL OR completeDate = 0) AND createDate BETWEEN ".$dateRange['start']." AND ".$dateRange['end']))
			);
			
			// $opportunities = X2Model::model('Actions')->countByAttributes(
				// array('type'=>'workflow','associationType'=>'opportunities','actionDescription'=>$workflowStatus['id'].':'.$i),
				// new CDbCriteria(array('condition'=>"complete != 'Yes' OR completeDate IS NULL OR completeDate = 0"))
			// );
			
			$funnelStr .= '<div class="workflow-funnel-stage" style="width:'.$width.'px;background:'.$color.';"><span class="name">';
				
			$funnelStr .= CHtml::link($workflowStatus['stages'][$i]['name'],array('workflow/view','id'=>$workflowStatus['id'],'stage'=>$i,'start'=>Formatter::formatDate($dateRange['start']),'end'=>Formatter::formatDate($dateRange['end']),'range'=>$dateRange['range'],$user),
				array('onclick'=>'getStageMembers('.$i.'); return false;')
			);
			
			$funnelStr .= '</span><span class="contact-icon">'
				.Yii::app()->locale->numberFormatter->formatDecimal($contacts).'</span></div>';
				// <span class="sales-icon">'
				// .Yii::app()->locale->numberFormatter->formatDecimal($opportunities).'</span>
		}
		$str = '<div class="row">
					<div class="cell"><div class="workflow-funnel-box" style="width:'.($startingWidth+10).'px">'.$funnelStr.'</div></div>
					<div class="cell">'.$statusStr.'</div>
				</div>';
		return $str;
	}
	
	
	
	// convert HEX color to RGB values
	private static function hex2rgb($color) {
		if ($color[0] == '#')
			$color = substr($color, 1);

		if (strlen($color) == 6)
			list($r, $g, $b) = array($color[0].$color[1],
									 $color[2].$color[3],
									 $color[4].$color[5]);
		else if (strlen($color) == 3)
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		else
			return false;

		$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

		return array($r, $g, $b);
	}
	
	private static function rgb2hex($r, $g=-1, $b=-1) {
		if (is_array($r) && sizeof($r) == 3)
			list($r, $g, $b) = $r;

		$r = intval($r); $g = intval($g);
		$b = intval($b);

		$r = dechex($r<0?0:($r>255?255:$r));
		$g = dechex($g<0?0:($g>255?255:$g));
		$b = dechex($b<0?0:($b>255?255:$b));

		$color = (strlen($r) < 2?'0':'').$r;
		$color .= (strlen($g) < 2?'0':'').$g;
		$color .= (strlen($b) < 2?'0':'').$b;
		return '#'.$color;
	}
	
	
	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('lastUpdated',$this->lastUpdated);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}
