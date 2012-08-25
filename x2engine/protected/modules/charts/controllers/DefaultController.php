<?php

class DefaultController extends x2base {

	// public $layout = '//layouts/';

	public function accessRules() {
		return array(
                        array('allow',
                            'actions'=>array('getFieldData'),
                            'users'=>array('*'),
                        ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions' => array('index','sales', 'marketing', 'pipeline', 'leadVolume','leadActivity','leadPerformance','leadSources','workflow'),
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
		$dateRange['strict'] = false;
		if(isset($_GET['strict']) && $_GET['strict'])
			$dateRange['strict'] = true;
			
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
		
		if($dateRange['strict'])
			$attributeConditions .= ' AND createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'];

		$workflow = 1;
		
		if(isset($_GET['workflow'])) {

			if(ctype_digit($_GET['workflow']))
				$workflow = $_GET['workflow'];

			if($workflow != 1 && $workflow != 2)	// only these 2 workflows are allowed
				$workflow = 1;
				
			$stageIds = array(
				1 => array('i'=>3,'e'=>13,'s'=>17),	// "traditional"
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
					->where('assignedTo="'.$assignedTo[$i].($dateRange['strict']? '"':'" AND createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end']).$attributeConditions, $attributeParams)
					// ->where('assignedTo="'.$assignedTo[$i].'" AND createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'].$attributeConditions, $attributeParams)
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
		$model->unsetFilters();
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
		
		if($dateRange['strict'])
			$attributeConditions .= ' AND createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'];
		
		$workflow = 1;
		
		if(isset($_GET['workflow'])) {
		
			if(ctype_digit($_GET['workflow']))
				$workflow = $_GET['workflow'];
			
			
                        $workflowStages=WorkflowStage::model()->findAllByAttributes(array('workflowId'=>$workflow),array('order'=>'stageNumber ASC'));
                        
                        $stageIds=array();
                        foreach($workflowStages as $stage){
                            $stageIds[$stage->name]=$stage->stageNumber;
                        }
				
			
			
			// SELECT COUNT(`a`.*) as `count1`, COUNT(`b`.*) as `count2`, assignedTo, stageNumber FROM `x2_actions` `a`, `x2_actions` `b` WHERE a.type="workflow" AND b.type="workflow" AND a.workflowId=1 AND b.workflowId=1 AND `a`.stageNumber=1 AND `b`.stageNumber=2 GROUP BY a.assignedTo, a.stageNumber, b.stageNumber

			
			$users = UserChild::getNames();

			$assignedTo = array_keys($users);
			
			$data = array();
			$totals = array(
				'id'=>'',
				'name'=>Yii::t('dashboard','Total'),
				'leads'=>0,
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
					->where('assignedTo="'.$assignedTo[$i].($dateRange['strict']? '"':'" AND createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end']).$attributeConditions, $attributeParams)
					->queryScalar();
					
				$totals['leads'] += $data[$i]['leads'];
                                $str="";
                                foreach($stageIds as $name=>$id){
                                    $str.=" SUM(IF(x2_actions.stageNumber='$id',1,0)) AS $name, ";
                                }
				$row = Yii::app()->db->createCommand()
				->select($str.' x2_contacts.assignedTo AS assignedTo')
				->from('x2_contacts')
				->join('x2_actions','x2_actions.associationId=x2_contacts.id AND x2_actions.associationType="contacts"')
				->where('x2_actions.type="workflow" AND x2_actions.workflowId='.$workflow.' AND x2_actions.completeDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'].' AND '.$assignmentCheck
					.' AND (SELECT COUNT(*) FROM x2_contacts WHERE x2_contacts.id=x2_actions.associationId '.$attributeConditions.') > 0',$attributeParams)
				->queryRow();
				foreach($stageIds as $name=>$id){
                                    $data[$i][$name]=isset($row[$name])? $row[$name] : 0;
                                    $totals[$name]=isset($totals[$name])?$totals[$name]+$data[$i][$name]:$data[$i][$name];
                                }
				
				
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
                        $stageIds=array();
		}
		$model->unsetFilters();
		$this->render('leadPerformance', array(
			'model'=>$model,
			'workflow'=>$workflow,
			'dataProvider'=>$dataProvider,
			'dateRange'=>$dateRange,
                        'stageIds'=>$stageIds,
		));
	}

	public function actionGetFieldData(){
		if(isset($_GET['field'])){
			$field=$_GET['field'];
			$options = Yii::app()->db->createCommand()
					->select($field)
					->from('x2_contacts')
					->group($field)
					->queryAll();
			$data=array();
			foreach($options as $row){
				if(!empty($row[$field]))
					$data[$row[$field]]=$row[$field];
			}
			print_r($data);
		}else{
		   
		}
	}

	public function actionLeadSources() {

		$dateRange = $this->getDateRange();
		$model = new Contacts('search');
		// $model->detachBehavior('ERememberFiltersBehavior');
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
		
		// die(var_dump($model->attributes));
		// die(var_dump($_GET));
		if($model->assignedTo=='Anyone')
			$model->assignedTo = '';
			
		$users = UserChild::getNames();
		
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
		if($dateRange['strict'])
			$attributeConditions .= ' AND createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'];
		
		$workflow = 1;
		
		if(isset($_GET['workflow'])) {
		
			if(ctype_digit($_GET['workflow']))
				$workflow = $_GET['workflow'];
			
			if($workflow != 1 && $workflow != 2)	// only these 2 workflows are allowed
				$workflow = 1;
				
			$stageIds = array(
				1 => array('i'=>3,'e'=>13,'s'=>17),	// "traditional"
				2 => array('i'=>3,'e'=>7,'s'=>10),	// "career"
			);

			$leadSourceList = Dropdowns::getItems(4);	// get lead source dropdown list
			$leadSources = array_keys($leadSourceList);
			
			$data = array();
			$totals = array(
				'id'=>0,
				'leadSource'=>Yii::t('dashboard','Total'),
				'leads'=>0,
				'interviewed'=>0,
				'enrolled'=>0,
				'started'=>0
			);
			
			$allRows = Yii::app()->db->createCommand()
				->select('COUNT(x2_contacts.leadSource) as count, x2_contacts.leadSource as leadSource, 
					SUM(IF(x2_actions.stageNumber='.$stageIds[$workflow]['i'].',1,0)) AS interviewed, 
					SUM(IF(x2_actions.stageNumber='.$stageIds[$workflow]['e'].',1,0)) AS enrolled,
					SUM(IF(x2_actions.stageNumber='.$stageIds[$workflow]['s'].',1,0)) AS started,
					x2_contacts.assignedTo AS assignedTo')
				->from('x2_contacts')
				->join('x2_actions','x2_actions.associationId=x2_contacts.id AND x2_actions.associationType="contacts"')
				->where('x2_actions.type="workflow" AND x2_actions.workflowId='.$workflow.' AND x2_actions.completeDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end']
					.' AND (SELECT COUNT(*) FROM x2_contacts WHERE x2_contacts.id=x2_actions.associationId AND x2_contacts.leadSource!="" '.$attributeConditions.') > 0',$attributeParams)
				->group('x2_contacts.leadSource')
				->queryAll();
			
			
			// loop through all lead sources
			for($i=0, $size=sizeof($leadSources); $i<$size; $i++) {
			// for($i=0, $size=sizeof($allRows); $i<$size; $i++) {
				// $row = $allRows[$i];
				// $data[$i]['leadSource'] = $leadSources[$i];
				
				// default if lead source has no entries
				$row = array(
					'leadSource'=>$leadSources[$i],
					'count'=>0
				);
				
				// try to find this lead source in the workflow data
				for($j=0, $size2=sizeof($allRows); $j<$size2; $j++) {
					if($allRows[$j]['leadSource'] == $leadSources[$i]) {
						$row = $allRows[$j];
						break;
					}
				}
				
				
				$data[$i]['leadSource'] = $row['leadSource'];
				$data[$i]['id'] = $i+1;

				$data[$i]['leads'] = $row['count'];
				// $data[$i]['leads'] = Yii::app()->db->createCommand()
					// ->select('COUNT(*)')->from('x2_contacts')
					// ->where('leadSource=:leadSource '.($dateRange['strict']? '':' AND createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end']).$attributeConditions, array_merge(array(':leadSource'=>$leadSources[$i]),$attributeParams))
					// ->queryScalar();
					
				$totals['leads'] += $data[$i]['leads'];

				
				// $row = Yii::app()->db->createCommand()
				// ->select('SUM(IF(x2_actions.stageNumber='.$stageIds[$workflow]['i'].',1,0)) AS interviewed, 
					// SUM(IF(x2_actions.stageNumber='.$stageIds[$workflow]['e'].',1,0)) AS enrolled,
					// SUM(IF(x2_actions.stageNumber='.$stageIds[$workflow]['s'].',1,0)) AS started,
					// x2_contacts.assignedTo AS assignedTo')
				// ->from('x2_contacts')
				// ->join('x2_actions','x2_actions.associationId=x2_contacts.id AND x2_actions.associationType="contacts"')
				// ->where('x2_actions.type="workflow" AND x2_actions.workflowId='.$workflow.' AND x2_actions.completeDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end']
					// .' AND (SELECT COUNT(*) FROM x2_contacts WHERE x2_contacts.id=x2_actions.associationId AND x2_contacts.leadSource=:leadSource '.$attributeConditions.') > 0',array_merge($attributeParams,array(':leadSource'=>$leadSources[$i])))
				// ->queryRow();
				
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
		$model->unsetFilters();
		$this->render('leadSources', array(
			'model'=>$model,
			'users'=>$users,
			'workflow'=>$workflow,
			'dataProvider'=>$dataProvider,
			'dateRange'=>$dateRange
		));
	}
	
/* 	public function actionWorkflow() {

		$dateRange = $this->getDateRange();
		$model = new Contacts('search');
		if(isset($_GET['Contacts']))
			$model->setX2Fields($_GET['Contacts']);

		$attributeConditions = '';
		$attributeParams = array();

		foreach($model->attributes as $key=>$value) {
			if(!empty($value)) {
				$attributeConditions .= ' AND '.$key.'=:'.$key;
				$attributeParams[':'.$key] = $value;
			}
		}
		
		if($dateRange['strict'])
			$attributeConditions .= ' AND createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'];

			
			
		$workflow = 1;
		
		if(isset($_GET['workflow'])) {

			if(ctype_digit($_GET['workflow']))
				$workflow = $_GET['workflow'];

			if($workflow != 1 && $workflow != 2)	// only these 2 workflows are allowed
				$workflow = 1;
				
			$stageIds = array(
				1 => array('i'=>3,'e'=>13,'s'=>17),	// "traditional"
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
					->where('assignedTo="'.$assignedTo[$i].($dateRange['strict']? '"':'" AND createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end']).$attributeConditions, $attributeParams)
					// ->where('assignedTo="'.$assignedTo[$i].'" AND createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'].$attributeConditions, $attributeParams)
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
		$model->unsetFilters();
		$this->render('workflow', array(
			'model'=>$model,
			'workflow'=>$workflow,
			'dataProvider'=>$dataProvider,
			'dateRange'=>$dateRange
		));
	} */
	
	public function actionWorkflow() {
		//print_r($_GET);exit;
		// $fieldVar=isset($_GET['field'])?$_GET['field']:"leadSource";
		
		$dataProvider = null;

		
		$dateRange = $this->getDateRange();
		$model = new Contacts('search');
		if(isset($_GET['Contacts']))
			$model->attributes = $_GET['Contacts'];
		// $input=$model->$fieldVar;

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
		
		$attributeParams = array(
			':date1'=> $dateRange['start'],
			':date2'=> $dateRange['end'],
			':user'=>Yii::app()->user->name,
		);

		foreach($model->attributes as $key=>$value) {
			if(!empty($value)) {
				$attributeConditions .= ' AND x2_contacts.'.$key.'=:'.$key;
				$attributeParams[':'.$key] = $value;
			}
		}
		
		if($dateRange['strict'])
			$attributeConditions .= ' AND createDate BETWEEN :date1 AND :date2';

		$workflow = null;
		$stage = '';
		
		
		$stageOptions = array();
		$workflowOptions = array();
		
		
		
		
		
		
		if(isset($_GET['workflow'])) {
		
			if(ctype_digit($_GET['workflow']))
				$workflow = $_GET['workflow'];
			
				
			if(isset($_GET['stage']) && ctype_digit($_GET['stage']))
				$stage = $_GET['stage'];
				
			$attributeParams[':workflowId'] = $workflow;
			// $attributeParams[':stage'] = $stage;
			
				
				
			// $workflowStages=WorkflowStage::model()->findAllByAttributes(array('workflowId'=>$workflow),array('order'=>'stageNumber ASC'));
			
			// $stageIds=array();
			// foreach($workflowStages as $workflowStage){
				// $stageIds[$workflowStage->name]=$workflowStage->stageNumber;
			// }

			// SELECT COUNT(`a`.*) as `count1`, COUNT(`b`.*) as `count2`, assignedTo, stageNumber FROM `x2_actions` `a`, `x2_actions` `b` WHERE a.type="workflow" AND b.type="workflow" AND a.workflowId=1 AND b.workflowId=1 AND `a`.stageNumber=1 AND `b`.stageNumber=2 GROUP BY a.assignedTo, a.stageNumber, b.stageNumber

			
			/* $users = UserChild::getNames();

			$assignedTo = array_keys($users);
			
			$data = array();
			$totals = array(
				'id'=>'',
				'name'=>Yii::t('dashboard','Total'),
				'leads'=>0,
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
					->where('assignedTo="'.$assignedTo[$i].($dateRange['strict']? '"':'" AND createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end']).$attributeConditions, $attributeParams)
					->queryScalar();
					
				$totals['leads'] += $data[$i]['leads'];
				$str="";
				foreach($stageIds as $name=>$id){
					$str.=" SUM(IF(x2_actions.stageNumber='$id',1,0)) AS $name, ";
				}
				$row = Yii::app()->db->createCommand()
				->select($str.' x2_contacts.assignedTo AS assignedTo')
				->from('x2_contacts')
				->join('x2_actions','x2_actions.associationId=x2_contacts.id AND x2_actions.associationType="contacts"')
				->where('x2_actions.type="workflow" AND x2_actions.workflowId='.$workflow.' AND x2_actions.completeDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'].' AND '.$assignmentCheck
					.' AND (SELECT COUNT(*) FROM x2_contacts WHERE x2_contacts.id=x2_actions.associationId '.$attributeConditions.') > 0',$attributeParams)
				->queryRow();
				foreach($stageIds as $name=>$id) {
					$data[$i][$name]=isset($row[$name])? $row[$name] : 0;
					$totals[$name]=isset($totals[$name])?$totals[$name]+$data[$i][$name]:$data[$i][$name];
				}

				if(array_sum($data[$i]) == 0)
					unset($data[$i]);
			}

			$data[] = $totals; */

			// die(var_dump($data));

			// $sql = 'SELECT COUNT(*) as `count`,assignedTo, stageNumber FROM `x2_actions` WHERE type="workflow" AND workflowId=1 GROUP BY assignedTo, stageNumber';

			
			$attributeConditions = 'x2_checkViewPermission(t.visibility,t.assignedTo,:user) > 0'.$attributeConditions;
			// die(var_dump($attributeParams));
			
			$criteria = new CDbCriteria(array(
				'condition'=>$attributeConditions,
				'join'=>'JOIN x2_actions ON x2_actions.associationId=t.id 
						AND x2_actions.associationType="contacts" 
						AND x2_actions.type="workflow" 
						AND x2_actions.workflowId=:workflowId
						AND x2_actions.completeDate BETWEEN :date1 AND :date2',
				'params'=>$attributeParams,
				'distinct'=>true,
			));
			
			
			// $dataProvider = new CArrayDataProvider($data,array(
			$dataProvider = new CActiveDataProvider('Contacts',array(
				'criteria'=>$criteria,
			
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
			// $dataProvider = null;
			// $stageIds=array();
		}
		$model->unsetFilters();
		$this->render('workflow', array(
			'model'=>$model,
			'workflow'=>$workflow,
			'stage'=>$stage,
			'stageOptions'=>$stageOptions,
			'workflowOptions'=>$workflowOptions,
			'dataProvider'=>$dataProvider,
			'dateRange'=>$dateRange,
			// 'stageIds'=>$stageIds,
			// 'fieldName'=>$fieldVar,
			// 'input'=>$input,
		));
	}
	
	
	public function actionViewLeads() {
	
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
		$stage = isset($_GET['stage'])? $_GET['stage'] : 0;
		
		
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
		$attributeParams[':stage'] = $stageNumber;
		
		
		if($dateRange['strict'])
			$attributeConditions .= ' AND createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'];
	
	
	
		$allRows = Yii::app()->db->createCommand()
				->select('x2_contacts.*')
				->from('x2_contacts')
				->join('x2_actions','x2_actions.associationId=x2_contacts.id')
				->where('x2_actions.associationType="contacts" AND x2_actions.type="workflow" AND x2_actions.workflowId='.$workflow.' AND x2_actions.stageNumber=:stage 
					AND x2_actions.completeDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'],$attributeParams)
				->queryAll();
				
				
		// $dataProvider = 
	
	
	
		$model->unsetFilters();
		$this->render('viewLeads', array(
			'model'=>$model,
			'users'=>$users,
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