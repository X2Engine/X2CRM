<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

/**
 * This is the model class for table "x2_workflows".
 *
 * The followings are the available columns in table 'x2_workflows':
 * @property integer $id
 * @property string $name
 * @property integer $lastUpdated
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

	/**
	 * @return string the route to view this model
	 */
	public function getDefaultRoute() { return '/workflow'; }
	
	/**
	 * @return string the route to this model's AutoComplete data source
	 */
	public function getAutoCompleteSource() { return '/workflow/getItems'; }

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

	public static function getList() {
		$workflows = CActiveRecord::model('Workflow')->findAll();
		$list = array(0=>Yii::t('app','None'));
		foreach ($workflows as $model)
			$list[$model->id] = $model->name;
		return $list;
	}
	
	public static function getWorkflowStatus($workflowId,$modelId = 0,$modelType = '') {
	
		$workflowStatus = array();
		
		$workflowStages = CActiveRecord::model('WorkflowStage')->findAllByAttributes(array('workflowId'=>$workflowId),new CDbCriteria(array('order'=>'id ASC')));
		
		$workflowStatus[] = $workflowId;
		foreach($workflowStages as &$stage) {	// load all WorkflowStage names into workflowStatus
			$workflowStatus[] = array(
				'name'=>$stage->name,
				'requirePrevious'=>$stage->requirePrevious,
				'roles'=>$stage->roles,
				'requireComment'=>$stage->requireComment
			);
		}

		$workflowActions = array();
		
		if(!empty($modelId)) {
			$workflowActions = CActiveRecord::model('Actions')->findAllByAttributes(
				array('associationId'=>$modelId,'associationType'=>$modelType,'type'=>'workflow','workflowId'=>$workflowId),
				new CDbCriteria(array('order'=>'createDate ASC'))
			);
		}

		foreach($workflowActions as &$action) {

			// decode workflowActions into a funnel list
			$workflowStatus[$action->stageNumber]['createDate'] = $action->createDate;		// Note: multiple actions with the same stage will overwrite each other
			$workflowStatus[$action->stageNumber]['completeDate'] = $action->completeDate;
			$workflowStatus[$action->stageNumber]['complete'] = ($action->complete == 'Yes') || (!empty($action->completeDate) && $action->completeDate < time());	// determine whether stage is complete			
			$workflowStatus[$action->stageNumber]['description'] = $action->actionDescription;																		// or the stage is beyond the possible range somehow
			
			/* $actionData = explode(':',$action->actionDescription);
			// decode workflowActions into a funnel list
			if(count($actionData) >= 2 && $actionData[0] == $workflowId && $actionData[1] <= count($workflowStages)) {		// ignore action if it's for a different workflow
				$workflowStatus[$actionData[1]]['createDate'] = $action->createDate;				// or the stage is beyond the possible range somehow
				$workflowStatus[$actionData[1]]['completeDate'] = $action->completeDate;		// Note: multiple actions with the same stage will overwrite each other
				$workflowStatus[$actionData[1]]['complete'] = ($action->complete == 'Yes') || (!empty($action->completeDate) && $action->completeDate < time());	// determine whether stage is complete
			} */
		}
		return $workflowStatus;
	}
	
	public static function getStages($id){
		return Yii::app()->db->createCommand()
			->select('name')
			->from('x2_workflow_stages')
			->where('workflowId=:id',array(':id'=>$id))
			->order('stageNumber ASC')
			->queryColumn();
	}
	
	public static function getWorkflowDetails($workflowId,$stageId,$modelId,$modelType) {
		return CActiveRecord::model('Actions')->findByAttributes(array(
			'associationId'=>$modelId,
			'associationType'=>$modelType,
			'type'=>'workflow',
			'workflowId'=>$workflowId,
			'stageNumber'=>$stageId
		));
	
	}

	
	public static function renderWorkflow(&$workflowStatus) {
	
		$workflowId = &$workflowStatus[0];
	
		$stageCount = count($workflowStatus)-1;
	
		if($stageCount < 1)
			return '';

		$stageCount = count($workflowStatus)-1;
	
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

			if(!empty($workflowStatus[$stage]['roles']))	// if roles are specified, check if user has any of them
				$editPermission = count(array_intersect(Yii::app()->params->roles,$workflowStatus[$stage]['roles'])) > 0;
			else
				$editPermission = true;	// default is full permission for everybody
				
			if(Yii::app()->user->getName() == 'admin')	// admin override
				$editPermission = true;
			
			$color = Workflow::rgb2hex(
				$startingRgb[0] + ($rgbSteps[0]*$stage),
				$startingRgb[1] + ($rgbSteps[1]*$stage),
				$startingRgb[2] + ($rgbSteps[2]*$stage)
			);
			$width = round($startingWidth + $widthStep*$stage);
			
			$funnelStr .= '<div class="workflow-funnel-stage" style="width:'.$width.'px;background:'.$color.';"><b>'.$workflowStatus[$stage]['name'].'</b></div>';;
			$statusStr .= '<div class="row"><div class="workflow-funnel-box" style="width:'.($startingWidth+10).'px">'
				.'<div class="workflow-funnel-stage" style="width:'.$width.'px;background:'.$color.';"><b>'.$workflowStatus[$stage]['name'].'</b></div></div>';

			$previousCheck = true;
			if($workflowStatus[$stage]['requirePrevious'] == 1) {	// check if all stages before this one are complete
				for($i=1; $i<$stage; $i++) {
					if(empty($workflowStatus[$i]['complete'])) {
						$previousCheck = false;
						break;
					}
				}
			} else if($workflowStatus[$stage]['requirePrevious'] < 0) {		// or just check if the specified stage is complete
				if(empty($workflowStatus[ -1*$workflowStatus[$stage]['requirePrevious'] ]['complete']))
					$previousCheck = false;
			}
				
			if(isset($workflowStatus[$stage]['createDate'])) {
				
				// check if this is the last stage to be started or completed
				$latestStage = true;
				if($stage < $stageCount) {
					for($i=$stage+1; $i<=$stageCount; $i++) {
						if(!empty($workflowStatus[$i]['createDate'])) {
							$latestStage = false;
							break;
						}
					}
				}
				$statusStr .= '<div class="workflow-status">';
				if($editPermission)
					$statusStr .= ' <a href="javascript:void(0)" class="right" onclick="workflowStageDetails('.$workflowId.','.$stage.');">['.Yii::t('workflow','Details').']</a> ';
				
				
				if($workflowStatus[$stage]['complete']) {
					$statusStr .= Yii::t('workflow','Completed').' '.date("Y-m-d",$workflowStatus[$stage]['completeDate']);
					// X2Date::dateBox($workflowStatus[$stage]['completeDate']);
					if($editPermission)
						$statusStr .= ' <a href="javascript:void(0)" class="right" onclick="revertWorkflowStage('.$workflowId.','.$stage.');">['.Yii::t('workflow','Undo').']</a>';
				} else {
					// $started = true;
					$statusStr .= '<b>'.Yii::t('workflow','Started').' '.date("Y-m-d",$workflowStatus[$stage]['createDate']).'</b>';
					// if(!$latestStage)
					if($editPermission)
						$statusStr .= '<a href="javascript:void(0)" class="right" onclick="revertWorkflowStage('.$workflowId.','.$stage.');">['.Yii::t('workflow','Undo').']</a>';
					if($previousCheck && $editPermission) {
						if($workflowStatus[$stage]['requireComment'])
							$statusStr .= ' <a href="javascript:void(0)" class="right" onclick="workflowCommentDialog('.$workflowId.','.$stage.');">['.Yii::t('workflow','Complete').']</a> ';
						else
							$statusStr .= ' <a href="javascript:void(0)" class="right" onclick="completeWorkflowStage('.$workflowId.','.$stage.');">['.Yii::t('workflow','Complete').']</a> ';
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
		return $statusStr;
		// $str = '<div class="row">
					// <div class="cell"><div class="workflow-funnel-box" style="width:'.($startingWidth+10).'px">'.$funnelStr.'</div></div>
					// <div class="cell" style="width:250px;">'.$statusStr.'</div>
				// </div>';
		// return $str;
	}
	
	public static function renderWorkflowStats(&$workflowStatus) {
	
		$stageCount = count($workflowStatus)-1;
	
		if($stageCount < 1)
			return '';

		$stageCount = count($workflowStatus)-1;
	
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

			$contacts = CActiveRecord::model('Actions')->countByAttributes(
				array('type'=>'workflow','workflowId'=>$workflowStatus[0],'stageNumber'=>$i),
				new CDbCriteria(array('condition'=>"complete != 'Yes' AND (completeDate IS NULL OR completeDate = 0)"))
			);
			
			// $sales = CActiveRecord::model('Actions')->countByAttributes(
				// array('type'=>'workflow','associationType'=>'sales','actionDescription'=>$workflowStatus[0].':'.$i),
				// new CDbCriteria(array('condition'=>"complete != 'Yes' OR completeDate IS NULL OR completeDate = 0"))
			// );
			
			$funnelStr .= '<div class="workflow-funnel-stage" style="width:'.$width.'px;background:'.$color.';"><span class="name">';
				
			$funnelStr .= CHtml::link($workflowStatus[$i]['name'],array('workflow/view','id'=>$workflowStatus[0],'stage'=>$i),
				array('onclick'=>'getStageMembers('.$i.'); return false;')
			);
			
			$funnelStr .= '</span><span class="contact-icon">'
				.Yii::app()->locale->numberFormatter->formatDecimal($contacts).'</span></div>';
				// <span class="sales-icon">'
				// .Yii::app()->locale->numberFormatter->formatDecimal($sales).'</span>
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