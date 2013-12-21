<?php

return array(
	'name' => 'Marketing',
	'install' => array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install.sql')),
		array('INSERT INTO x2_form_layouts
						(id,model,version,layout,defaultView,defaultForm,createDate,lastUpdated) VALUES
						(14,"Campaign","View","{\"version\":\"1.2\",\"sections\":[{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"230\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"39\",\"width\":\"483\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_listId\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"NaN\"},{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Email Template\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_subject\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"226\",\"tabindex\":\"0\"},{\"name\":\"formItem_template\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"133\",\"tabindex\":\"0\"},{\"name\":\"formItem_content\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"259\",\"width\":\"478\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_active\",\"labelType\":\"left\",\"readOnly\":\"1\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"},{\"name\":\"formItem_complete\",\"labelType\":\"left\",\"readOnly\":\"1\",\"height\":\"22\",\"width\":\"17\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"\",\"rows\":[{\"cols\":[{\"width\":572,\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"145\",\"tabindex\":\"0\"},{\"name\":\"formItem_visibility\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"145\",\"tabindex\":\"0\"}]}]}]}]}","1","0","' . time() . '","' . time() . '")'
		)
	),
	'uninstall' => array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','uninstall.sql')),
	),
	'editable' => true,
	'searchable' => true,
	'adminOnly' => false,
	'custom' => false,
	'toggleable' => true,
	'version' => '2.0',
);
?>
