<?php

return array(
	'name' => "Quotes",
	'install' => array(
		dirname(__FILE__).'/data/install.sql',
		array(
			'INSERT INTO x2_form_layouts (id,model,version,layout,defaultView,defaultForm,createDate,lastUpdated) VALUES 
				(9,"Quote","Form","{\"version\":\"1.2\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Info\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"185\",\"tabindex\":\"0\"},{\"name\":\"formItem_status\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"185\",\"tabindex\":\"NaN\"}]},{\"width\":286,\"items\":[{\"name\":\"formItem_locked\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_expirationDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Sales\",\"rows\":[{\"cols\":[{\"width\":285,\"items\":[{\"name\":\"formItem_associatedContacts\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"185\",\"tabindex\":\"NaN\"}]},{\"width\":286,\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Notes\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"52\",\"width\":\"430\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
				(10,"Quote","View","{\"version\":\"1.2\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Info\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_status\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"165\",\"tabindex\":\"NaN\"},{\"name\":\"formItem_id\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]},{\"width\":278,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_locked\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Sales\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_associatedContacts\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"NaN\"},{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]},{\"width\":278,\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Dates\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_expirationDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_lastUpdated\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]},{\"width\":278,\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"},{\"name\":\"formItem_updatedBy\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"57\",\"width\":\"431\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'");',
		)
	),
	'uninstall' => array(
		dirname(__FILE__).'/data/uninstall.sql',
	),
	'editable' => true,
	'searchable' => true,
	'adminOnly' => false,
	'custom' => false,
	'toggleable' => false,
	'version' => '2.0',
);
?>
