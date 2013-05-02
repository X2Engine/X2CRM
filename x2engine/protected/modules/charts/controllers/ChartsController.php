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
 * @package X2CRM.modules.charts.controllers 
 */
class ChartsController extends x2base {
    public $modelClass="";

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
					$dateRange['end'] = Formatter::parseDate($_GET['end']);
					if($dateRange['end'] == false)
						$dateRange['end'] = time();
					else
						$dateRange['end'] = strtotime('23:59:59',$dateRange['end']);
				}
				
				$dateRange['start'] = strtotime('1 month ago',$dateRange['end']);
				if(isset($_GET['start'])) {
					$dateRange['start'] = Formatter::parseDate($_GET['start']);
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
					$group = X2Model::model('Groups')->findByPk($data[$i]['id']);
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