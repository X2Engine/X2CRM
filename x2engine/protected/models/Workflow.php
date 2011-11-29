<?php

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
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'x2_workflows';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
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
		);
	}

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
		foreach($workflowStages as &$stage)
			$workflowStatus[] = array('name'=>$stage->name);	// load all WorkflowStage names into workflowStatus

		if(empty($modelId) || empty($modelId)) {
			$workflowActions = array();
		} else {
			$workflowActions = CActiveRecord::model('ActionChild')->findAllByAttributes(
				array('associationId'=>$modelId,'associationType'=>$modelType,'type'=>'workflow'),
				new CDbCriteria(array('order'=>'createDate ASC'))
			);
		}

		foreach($workflowActions as &$action) {
		
			$actionData = explode(':',$action->actionDescription);
			
			// decode workflowActions into a funnel list
			if(count($actionData) == 2 && $actionData[0] == $workflowId && $actionData[1] <= count($workflowStages)) {		// ignore action if it's for a different workflow
				$workflowStatus[$actionData[1]]['createDate'] = $action->createDate;				// or the stage is beyond the possible range somehow
				$workflowStatus[$actionData[1]]['completeDate'] = $action->completeDate;		// Note: multiple actions with the same stage will overwrite each other
				$workflowStatus[$actionData[1]]['complete'] = ($action->complete == 'Yes') || (!empty($action->completeDate) && $action->completeDate < time());	// determine whether stage is complete
			}
		}
		return $workflowStatus;
	}
	
	public static function renderWorkflow(&$workflowStatus) {
	
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
		
		$started = false;
		for($i=1; $i<=$stageCount;$i++) {
		
			$color = Workflow::rgb2hex(
				$startingRgb[0] + ($rgbSteps[0]*$i),
				$startingRgb[1] + ($rgbSteps[1]*$i),
				$startingRgb[2] + ($rgbSteps[2]*$i)
			);
			$width = round($startingWidth + $widthStep*$i);
			
			$funnelStr .= '<div class="workflow-funnel-stage" style="width:'.$width.'px;background:'.$color.';"><b>'.$workflowStatus[$i]['name'].'</b></div>';;
			if(isset($workflowStatus[$i]['createDate'])) {
				// $started = true;
				$statusStr .= '<div class="workflow-status">';
				if($workflowStatus[$i]['complete']) {
					$statusStr .= Yii::t('workflow','Completed').' '.date("Y-m-d",$workflowStatus[$i]['completeDate']);
					$statusStr .= ' <a href="javascript:void(0)" onclick="revertWorkflowStage('.$workflowStatus[0].','.$i.');">['.Yii::t('workflow','Undo').']</a>';
				} else {
					$started = true;
					$statusStr .= '<b>'.Yii::t('workflow','Started').' '.date("Y-m-d",$workflowStatus[$i]['createDate']).'</b>';
					$statusStr .= ' <a href="javascript:void(0)" onclick="completeWorkflowStage('.$workflowStatus[0].','.$i.');">['.Yii::t('workflow','Complete').']</a> ';
					$statusStr .= '<a href="javascript:void(0)" onclick="revertWorkflowStage('.$workflowStatus[0].','.$i.');">['.Yii::t('workflow','Undo').']</a>';
				}
				$statusStr .= '</div>';
			} else {
				if(!$started) {
					$started = true;
					$statusStr .= '<div class="workflow-status"><a href="javascript:void(0)" onclick="startWorkflowStage('.$workflowStatus[0].','.$i.');">['.Yii::t('workflow','Start').']</a></div>';
				}
			}
			
		}
		$str = '<div class="row">
					<div class="cell"><div class="workflow-funnel-box" style="width:'.($startingWidth+10).'px">'.$funnelStr.'</div></div>
					<div class="cell">'.$statusStr.'</div>
				</div>';
		return $str;
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
				array('type'=>'workflow','actionDescription'=>$workflowStatus[0].':'.$i),
				new CDbCriteria(array('condition'=>"complete != 'Yes' OR completeDate IS NULL OR completeDate = 0"))
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