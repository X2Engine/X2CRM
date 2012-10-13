<?php

return array(
	'name' => 'Marketing',
	'install' => array(
		dirname(__FILE__) . '/data/install.sql',
		array('INSERT INTO x2_form_layouts
						(model,version,layout,defaultView,defaultForm,createDate,lastUpdated)
				VALUES	("Campaign","Form","{\"version\":\"1.1\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Info\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"230\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"39\",\"width\":\"498\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_listId\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"NaN\"},{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Email Template\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_subject\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"311\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_content\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"359\",\"width\":\"578\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"145\",\"tabindex\":\"0\"},{\"name\":\"formItem_visibility\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"145\",\"tabindex\":\"0\"}]}]}]}]}","0","1","' . time() . '","' . time() . '"),
						("Campaign","View","{\"version\":\"1.1\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Info\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"230\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"39\",\"width\":\"498\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_listId\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"NaN\"},{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Email Template\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_subject\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"311\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_content\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"259\",\"width\":\"478\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Status\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_active\",\"labelType\":\"left\",\"readOnly\":\"1\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"},{\"name\":\"formItem_complete\",\"labelType\":\"left\",\"readOnly\":\"1\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"145\",\"tabindex\":\"0\"},{\"name\":\"formItem_visibility\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"145\",\"tabindex\":\"0\"}]}]}]}]}","1","0","' . time() . '","' . time() . '")'
		)
	),
	'uninstall' => array(
		dirname(__FILE__) . '/data/uninstall.sql',
	),
	'editable' => true,
	'searchable' => true,
	'adminOnly' => false,
	'custom' => false,
	'toggleable' => true,
	'version' => '2.0',
);
?>
