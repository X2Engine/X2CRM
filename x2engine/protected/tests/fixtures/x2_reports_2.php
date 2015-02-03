<?php 
	return array(
		'testReport1' => array(
			'id' => 1,
			'name' => 'Test Report 1',
			'type' => 'rowsAndColumns',
			'settings' => json_encode (array ( 
				"columns" => array(
					"name",
					"impact",
					"status",
					"assignedTo",
					"lastUpdated",
					"updatedBy"
				),
				"orderBy" =>  array(),
				"primaryModelType" => "Services",
				"allFilters" => array(),
				"anyFilters" => array(),
				"export" => false,
				"print" => false,
				"email" => false
			)),
			'dataWidgetLayout' => json_encode (array (
				'DataWidget_546fe2089f793' => array(
		            'hidden' => null,
		            'minimized' => null,
		            'label' => 'Lead Volume',
		            'chartId' => 1,
		            'uid' => null,
		            'containerNumber' => 2,
		            'softDeleted' => null,
		            'displayType' => 'pie',
		            'legend' => array (
	                    'Portland trade show',
	                    'null'
	                ),
		        ),
				'TimeSeriesWidget_546fe2089f793' => array(
		            'hidden' => null,
		            'minimized' => null,
		            'label' => 'Lead Volume',
		            'chartId' => 1,
		            'uid' => null,
		            'containerNumber' => 2,
		            'softDeleted' => null,
		            'displayType' => 'pie',
		            'legend' => array (
	                    'Portland trade show',
	                    'null'
	                ),
		            'subchart' => null,
		            'timeBucket' => 'day',
		            'filter' => 'week',
		            'filterType' => 'trailing',
		            'filterFrom' => null,
		            'filterTo' => null,
		        )		        
			))
		)		
	);
?>