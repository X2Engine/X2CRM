<?php

return array(
	'name' => "Accounts",
	'install' => array(
		dirname(__FILE__) . '/data/install.sql',
		array(
			'INSERT INTO x2_form_layouts 
						(model,version,layout,defaultView,defaultForm,createDate,lastUpdated) 
				VALUES	("Accounts","Form","{\"version\":\"1.2\",\"sections\":[{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"193\",\"tabindex\":\"0\"},{\"name\":\"formItem_tickerSymbol\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":286,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_employees\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_annualRevenue\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":286,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"189\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"61\",\"width\":\"487\",\"tabindex\":\"0\"}]}]}]}]}","0","1","' . time() . '","' . time() . '"),
						("Accounts","View","{\"version\":\"1.2\",\"sections\":[{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_tickerSymbol\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":286,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_website\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_employees\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_phone\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"},{\"name\":\"formItem_annualRevenue\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"192\",\"tabindex\":\"0\"}]},{\"width\":286,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"189\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"61\",\"width\":\"487\",\"tabindex\":\"0\"}]}]}]}]}","1","0","' . time() . '","' . time() . '")'
			),
	),
	'uninstall' => array(
		dirname(__FILE__) . '/data/uninstall.sql'
	),
	'editable' => true,
	'searchable' => true,
	'adminOnly' => false,
	'custom' => false,
	'toggleable' => false,
	'version' => '2.0',
);
?>
