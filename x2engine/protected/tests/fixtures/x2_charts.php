<?php return array(
	'testChart1' => array(
		'reportId' => 1,
		'name' => 'Test Chart 1',
		'type' => 'TimeSeries',
		'settings' => json_encode(array(
			'timeField' => 'createDate',
			'labelField' => 'type',
			'filterType' => 'trailing',
			'filter' => 'week',
			'filterFrom' => null,
			'filterTo' => null 
		))
	),
	'testChart2' => array(
		'reportId' => 2,
		'name' => 'Test Chart 2',
		'type' => 'Bar',
		'settings' => json_encode(array(
			'categories' => 'assignedTo',
			'values' => 'dealValue',
		))
	)
) ?>