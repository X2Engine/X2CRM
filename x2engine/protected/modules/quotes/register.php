<?php

return array(
	'name' => "Quotes",
	'install' => array(
		dirname(__FILE__) . '/data/install.sql',
		array(
			'INSERT INTO x2_form_layouts (model,version,layout,defaultView,defaultForm,createDate,lastUpdated) VALUES 
				("Quote","Form","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_status\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"0\",\"width\":\"0\",\"tabindex\":\"NaN\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_locked\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_expirationDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Sales\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_associatedContacts\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"undefined\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Notes\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"52\",\"width\":\"430\",\"tabindex\":\"0\"}]}]}]}]}","0","1","' . time() . '","' . time() . '"),
				("Quote","View","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":true,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_id\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_status\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"0\",\"width\":\"0\",\"tabindex\":\"NaN\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_locked\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Sales\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_associatedContacts\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"undefined\"},{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Dates\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_expirationDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_lastUpdated\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_updatedBy\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Notes\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"57\",\"width\":\"431\",\"tabindex\":\"0\"}]}]}]}]}","1","0","' . time() . '","' . time() . '");',
		)
	),
	'uninstall' => array(
		dirname(__FILE__) . '/data/uninstall.sql',
	),
	'editable' => true,
	'searchable' => true,
	'adminOnly' => false,
	'custom' => false,
	'toggleable' => false,
	'version' => '2.0',
);
?>
