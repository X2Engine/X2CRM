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
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
 * @package X2CRM.modules.charts.controllers 
 */
class ChartsController extends x2base {


	public function getDateRange() {
	
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

		// if(isset($_GET['test'])) {
		if(isset($_GET['range'])) {
			$data = Yii::app()->db->createCommand()
				->select('x2_users.id as userId, CONCAT(x2_users.firstName, " ",x2_users.lastName) as name, assignedTo as id, COUNT(assignedTo) as count')
				// ->select('assignedTo as id, COUNT(assignedTo) as count')
				->from('x2_contacts')
				->group('assignedTo')
				->leftJoin('x2_users','x2_contacts.assignedTo=x2_users.username')
				->where('createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end'])
				->order('id ASC')
				->queryAll();
				
			$total = 0;
			for($i=0;$i<$size=count($data);$i++) {
				$total += $data[$i]['count'];
				if(is_numeric($data[$i]['id'])) {
					$group = CActiveRecord::model('Groups')->findByPk($data[$i]['id']);
					if(isset($group))
						$data[$i]['name'] = $group->createLink();
					else
						$data[$i]['name'] = $data[$i]['id'];
						
				} elseif(!empty($data[$i]['userId'])) {
					$data[$i]['name'] = CHtml::link($data[$i]['name'],array('/users/'.$data[$i]['userId']));
				} else {
					$data[$i]['name'] = $data[$i]['id'];
				}
				
			}
			$data[] = array('id'=>null,'name'=>'Total','count'=>$total);
			// $data[] = $totals;

			$dataProvider = new CArrayDataProvider($data,array(
				// 'totalItemCount'=>$count,
				'pagination'=>array(
					'pageSize'=>100,//Yii::app()->params->profile->resultsPerPage,
				),
			));

		} else {
			$dataProvider = null;
		}

		$this->render('leadVolume', array(
			'dataProvider'=>$dataProvider,
			'dateRange'=>$dateRange
		));
		
		// } else {
		
		// $this->render('leadVolume', array(
			// 'dateRange'=>$dateRange
		// ));
		// }
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

	public function actionAdmin() {
		$this->redirect($this->createUrl('/charts/index'));
	}

	public function actionIndex() {
		$this->redirect($this->createUrl('/charts/leadVolume'));
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
			return '&ndash;';
		else
			return number_format(100*$a/$b,2).'%';
	}
}