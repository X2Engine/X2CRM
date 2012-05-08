<?php

class DefaultController extends x2base {

	public $layout = '//layouts/main3';

	public function accessRules() {
		return array(
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions' => array('index','sales', 'marketing', 'pipeline', 'leadVolume','leadActivity','leadPerformance'),
				'users' => array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions' => array('admin'),
				'users' => array('admin'),
			),
			array('deny', // deny all users
				'users' => array('*'),
			),
		);
	}


	/**
	 * @return array action filters
	 */
	public function filters() {
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	private function getDateRange() {
	
		$dateRange = array();

		$dateRange['range'] = 'custom';
		if(isset($_GET['range']))
			$dateRange['range'] = $_GET['range'];

		switch($dateRange['range']) {

			case 'thisWeek':
				$dateRange['start'] = strtotime('mon this week');	// first of this month
				$dateRange['end'] = time();	// now
				break;
			case 'thisMonth':
				$dateRange['start'] = mktime(0,0,0,date('n'),1);	// first of this month
				$dateRange['end'] = time();	// now
				break;
			case 'lastWeek':
				$dateRange['start'] = strtotime('mon last week');	// first of last month
				$dateRange['end'] = strtotime('mon this week')-1;		// first of this month
				break;
			case 'lastMonth':
				$dateRange['start'] = mktime(0,0,0,date('n')-1,1);	// first of last month
				$dateRange['end'] = mktime(0,0,0,date('n'),1)-1;		// first of this month
				break;
			case 'thisYear':
				$dateRange['start'] = mktime(0,0,0,1,1);		// first of the year
				$dateRange['end'] = time();	// now
				break;
			case 'lastYear':
				$dateRange['start'] = mktime(0,0,0,1,1,date('Y')-1);		// first of last year
				$dateRange['end'] = mktime(0,0,0,1,1,date('Y'))-1;			// first of this year
				break;
				
			case 'custom':
			default:
				$dateRange['end'] = time();
				if(isset($_GET['end'])) {
					$dateRange['end'] = $this->parseDate($_GET['end']);
					if($dateRange['end'] == false)
						$dateRange['end'] = time();
					else
						$dateRange['end'] = strtotime('23:59:59',$dateRange['end']);
				}
				
				$dateRange['start'] = strtotime('1 month ago',$dateRange['end']);
				if(isset($_GET['start'])) {
					$dateRange['start'] = $this->parseDate($_GET['start']);
					if($dateRange['start'] == false)
						$dateRange['start'] = strtotime('-30 days 0:00',$dateRange['end']);
					else
						$dateRange['start'] = strtotime('0:00',$dateRange['start']);
				}
		}
		return $dateRange;
	}
	
	
	public function actionLeadVolume() {
		
		$dateRange = $this->getDateRange();

		$this->render('leadVolume', array(
			'dateRange'=>$dateRange
		));
	}

	public function actionLeadActivity() {

		$dateRange = $this->getDateRange();
		$model = new Contacts('search');
		if(isset($_GET['Contacts']))
			$model->attributes = $_GET['Contacts'];

		if(isset($_GET['Contacts']['company_id'],$_GET['Contacts']['company']) && !empty($_GET['Contacts']['company'])) {	// check the ID, if provided
			$linkId = $_GET['Contacts']['company_id'];
			if(!empty($linkId) && CActiveRecord::model('Accounts')->countByAttributes(array('id'=>$linkId)))	// if the link model actually exists,
				$model->company = $linkId;																	// then use the ID as the field value
		}
		if(!empty($_GET['Contacts']['company']) && !ctype_digit($_GET['Contacts']['company'])) {	// if the field is sitll text, try to find the ID based on the name
			$linkModel = CActiveRecord::model('Accounts')->findByAttributes(array('name'=>$_GET['Contacts']['company']));
			if(isset($linkModel))
				$model->company = $linkModel->id;
		}
		
		
		$attributeConditions = '';
		
		$attributeParams = array();
		// $attributeConditions = array();
		//$model->attributes
		foreach($model->attributes as $key=>$value) {
			if(!empty($value)) {
				$attributeConditions .= ' AND '.$key.'=:'.$key;
				$attributeParams[':'.$key] = $value;
				
			}
			
		}
		$workflow = 1;
		
		if(isset($_GET['workflow'])) {

			if(ctype_digit($_GET['workflow']))
				$workflow = $_GET['workflow'];

			if($workflow != 1 && $workflow != 2)	// only these 2 workflows are allowed
				$workflow = 1;
				
			$stageIds = array(
				1 => array('i'=>3,'e'=>12,'s'=>16),	// "traditional"
				2 => array('i'=>3,'e'=>7,'s'=>10),	// "career"
			);

			$users = UserChild::getNames();
			// $groups = Groups::getNames();
			
			// $assignedTo = array_keys($groups) + array_keys($users);
			$assignedTo = array_keys($users);
			
			$data = array();
			$totals = array(
				'id'=>'',
				'name'=>Yii::t('dashboard','Total'),
				'leads'=>0,
				'interviewed'=>0,
				'enrolled'=>0,
				// 'started'=>0
			);
			
			for($i=0, $size=sizeof($assignedTo); $i<$size; $i++) {
			
				$data[$i]['id'] = $assignedTo[$i];
				$data[$i]['name'] = $users[$assignedTo[$i]];
				
				
				if($data[$i]['id']=='Anyone') {
					$assignmentCheck = '(x2_contacts.assignedTo IS NULL OR x2_contacts.assignedTo="" OR x2_contacts.assignedTo="Anyone")';
					$data[$i]['id'] = '';
				} else
					$assignmentCheck = 'x2_contacts.assignedTo="'.$data[$i]['id'].'"';


				$data[$i]['leads'] = Yii::app()->db->createCommand()
					->select('COUNT(*)')->from('x2_contacts')
					->where('assignedTo="'.$assignedTo[$i].'" AND createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'].$attributeConditions, $attributeParams)
					->queryScalar();
					
				$totals['leads'] += $data[$i]['leads'];

				$row = Yii::app()->db->createCommand()
				->select('SUM(IF(x2_actions.stageNumber='.$stageIds[$workflow]['i'].',1,0)) AS interviewed, 
					SUM(IF(x2_actions.stageNumber='.$stageIds[$workflow]['e'].',1,0)) AS enrolled,'
					// SUM(IF(x2_actions.stageNumber='.$stageIds[$workflow]['s'].',1,0)) AS started,
					.'x2_contacts.assignedTo AS assignedTo')
				->from('x2_contacts')
				->join('x2_actions','x2_actions.associationId=x2_contacts.id AND x2_actions.associationType="contacts"')
				->where('x2_actions.type="workflow" AND x2_actions.workflowId='.$workflow.' AND x2_actions.completeDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'].' AND '.$assignmentCheck
					.' AND (SELECT COUNT(*) FROM x2_contacts WHERE x2_contacts.id=x2_actions.associationId '.$attributeConditions.') > 0',$attributeParams)
				->queryRow();
				
				$data[$i]['interviewed'] = isset($row['interviewed'])? $row['interviewed'] : 0;
				$data[$i]['enrolled'] = isset($row['enrolled'])? $row['enrolled'] : 0;
				// $data[$i]['started'] = isset($row['started'])? $row['started'] : 0;
				
				$totals['interviewed'] += $data[$i]['interviewed'];
				$totals['enrolled'] += $data[$i]['enrolled'];
				// $totals['started'] += $data[$i]['started'];
				
				if(array_sum($data[$i]) == 0)
					unset($data[$i]);
			}

			$data[] = $totals;

			$dataProvider = new CArrayDataProvider($data,array(
				// 'totalItemCount'=>$count,
				// 'sort'=>'assignedTo ASC',
				'sort'=>array(
					// 'defaultOrder'=>'completedBy ASC',
					// 'attributes'=>array(
						 // 'id', 'username', 'email',
					// ),
				),
				'pagination'=>array(
					'pageSize'=>Yii::app()->params->profile->resultsPerPage,
				),
			));

		} else {
			$dataProvider = null;
		}
		$this->render('leadActivity', array(
			'model'=>$model,
			'workflow'=>$workflow,
			'dataProvider'=>$dataProvider,
			'dateRange'=>$dateRange
		));
	}
	
	public function actionLeadPerformance() {

		$dateRange = $this->getDateRange();
		$model = new Contacts('search');
		if(isset($_GET['Contacts']))
			$model->attributes = $_GET['Contacts'];

		if(isset($_GET['Contacts']['company_id'],$_GET['Contacts']['company']) && !empty($_GET['Contacts']['company'])) {	// check the ID, if provided
			$linkId = $_GET['Contacts']['company_id'];
			if(!empty($linkId) && CActiveRecord::model('Accounts')->countByAttributes(array('id'=>$linkId)))	// if the link model actually exists,
				$model->company = $linkId;																	// then use the ID as the field value
		}
		if(!empty($_GET['Contacts']['company']) && !ctype_digit($_GET['Contacts']['company'])) {	// if the field is sitll text, try to find the ID based on the name
			$linkModel = CActiveRecord::model('Accounts')->findByAttributes(array('name'=>$_GET['Contacts']['company']));
			if(isset($linkModel))
				$model->company = $linkModel->id;
		}
		
		
		$attributeConditions = '';
		
		$attributeParams = array();
		// $attributeConditions = array();
		//$model->attributes
		foreach($model->attributes as $key=>$value) {
			if(!empty($value)) {
				$attributeConditions .= ' AND x2_contacts.'.$key.'=:'.$key;
				$attributeParams[':'.$key] = $value;
				
			}
			
		}
		$workflow = 1;
		
		if(isset($_GET['workflow'])) {
		
			if(ctype_digit($_GET['workflow']))
				$workflow = $_GET['workflow'];
			
			if($workflow != 1 && $workflow != 2)	// only these 2 workflows are allowed
				$workflow = 1;
				
			$stageIds = array(
				1 => array('i'=>3,'e'=>12,'s'=>16),	// "traditional"
				2 => array('i'=>3,'e'=>7,'s'=>10),	// "career"
			);
			
			
			// SELECT COUNT(`a`.*) as `count1`, COUNT(`b`.*) as `count2`, assignedTo, stageNumber FROM `x2_actions` `a`, `x2_actions` `b` WHERE a.type="workflow" AND b.type="workflow" AND a.workflowId=1 AND b.workflowId=1 AND `a`.stageNumber=1 AND `b`.stageNumber=2 GROUP BY a.assignedTo, a.stageNumber, b.stageNumber

			
			$users = UserChild::getNames();
			// $groups = Groups::getNames();
			
			// $assignedTo = array_keys($groups) + array_keys($users);
			$assignedTo = array_keys($users);
			
			$data = array();
			$totals = array(
				'id'=>'',
				'name'=>Yii::t('dashboard','Total'),
				'leads'=>0,
				'interviewed'=>0,
				'enrolled'=>0,
				'started'=>0
			);
			
			for($i=0, $size=sizeof($assignedTo); $i<$size; $i++) {
				
				$data[$i]['id'] = $assignedTo[$i];
				$data[$i]['name'] = $users[$assignedTo[$i]];
				
				
				if($data[$i]['id']=='Anyone') {
					$assignmentCheck = '(x2_contacts.assignedTo IS NULL OR x2_contacts.assignedTo="" OR x2_contacts.assignedTo="Anyone")';
					$data[$i]['id'] = '';
				} else
					$assignmentCheck = 'x2_contacts.assignedTo="'.$data[$i]['id'].'"';


				$data[$i]['leads'] = Yii::app()->db->createCommand()
					->select('COUNT(*)')->from('x2_contacts')
					->where('assignedTo="'.$assignedTo[$i].'" AND createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'].$attributeConditions, $attributeParams)
					->queryScalar();
					
				$totals['leads'] += $data[$i]['leads'];

				$row = Yii::app()->db->createCommand()
				->select('SUM(IF(x2_actions.stageNumber='.$stageIds[$workflow]['i'].',1,0)) AS interviewed, 
					SUM(IF(x2_actions.stageNumber='.$stageIds[$workflow]['e'].',1,0)) AS enrolled,
					SUM(IF(x2_actions.stageNumber='.$stageIds[$workflow]['s'].',1,0)) AS started,
					x2_contacts.assignedTo AS assignedTo')
				->from('x2_contacts')
				->join('x2_actions','x2_actions.associationId=x2_contacts.id AND x2_actions.associationType="contacts"')
				->where('x2_actions.type="workflow" AND x2_actions.workflowId='.$workflow.' AND x2_actions.completeDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'].' AND '.$assignmentCheck
					.' AND (SELECT COUNT(*) FROM x2_contacts WHERE x2_contacts.id=x2_actions.associationId '.$attributeConditions.') > 0',$attributeParams)
				->queryRow();
				
				$data[$i]['interviewed'] = isset($row['interviewed'])? $row['interviewed'] : 0;
				$data[$i]['enrolled'] = isset($row['enrolled'])? $row['enrolled'] : 0;
				$data[$i]['started'] = isset($row['started'])? $row['started'] : 0;
				
				$totals['interviewed'] += $data[$i]['interviewed'];
				$totals['enrolled'] += $data[$i]['enrolled'];
				$totals['started'] += $data[$i]['started'];
				
				if(array_sum($data[$i]) == 0)
					unset($data[$i]);
			}

			$data[] = $totals;

			// die(var_dump($data));

			// $sql = 'SELECT COUNT(*) as `count`,assignedTo, stageNumber FROM `x2_actions` WHERE type="workflow" AND workflowId=1 GROUP BY assignedTo, stageNumber';
			$dataProvider = new CArrayDataProvider($data,array(
				// 'totalItemCount'=>$count,
				// 'sort'=>'assignedTo ASC',
				'sort'=>array(
					// 'defaultOrder'=>'name ASC',
					// 'attributes'=>array(
						 // 'id', 'username', 'email',
					// ),
				),
				'pagination'=>array(
					'pageSize'=>Yii::app()->params->profile->resultsPerPage,
				),
			));
		} else {
			$dataProvider = null;
		}

		$this->render('leadPerformance', array(
			'model'=>$model,
			'workflow'=>$workflow,
			'dataProvider'=>$dataProvider,
			'dateRange'=>$dateRange
		));
	}
	
	public function actionAdmin() {
		$this->redirect($this->createUrl('default/index'));
	}

	public function actionIndex() {
		$this->redirect($this->createUrl('default/leadVolume'));
	}

	public function actionMarketing() {
		$model = new X2MarketingChartModel();
		if (isset($_POST['X2MarketingChartModel']))
			$model->attributes = $_POST['X2MarketingChartModel'];

		$this->render('marketing', array('model' => $model));
	}

	public function actionSales() {
		$model = new X2SalesChartModel();
		if (isset($_POST['X2SalesChartModel']))
			$model->attributes = $_POST['X2SalesChartModel'];

		$this->render('sales', array('model' => $model));
	}

	public function actionPipeline() {
		$model = new X2PipelineChartModel();
		if (isset($_POST['X2PipelineChartModel']))
			$model->attributes = $_POST['X2PipelineChartModel'];

		$this->render('pipeline', array('model' => $model));
	}
	
	public function formatLeadRatio($a,$b) {
		if($b==0)
			return '0%';
		else
			return round(100*$a/$b).'%';
	}
}