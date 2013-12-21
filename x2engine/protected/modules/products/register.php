<?php

return array(
	'name' => "Products",
	'install' => array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install.sql')),
		array('INSERT INTO x2_form_layouts (id,model,version,layout,defaultView,defaultForm,createDate,lastUpdated) VALUES 
				(5,"Product","Form","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"1\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"2\"}]}]}]},{\"collapsible\":false,\"title\":\"Product Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_price\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"182\",\"tabindex\":\"3\"},{\"name\":\"formItem_currency\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"188\",\"tabindex\":\"4\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_inventory\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"187\",\"tabindex\":\"5\"},{\"name\":\"formItem_status\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"188\",\"tabindex\":\"6\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"62\",\"width\":\"477\",\"tabindex\":\"7\"}]}]}]}]}","0","1","' . time() . '","' . time() . '"),
				(6,"Product","View","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Basic Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"205\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_type\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"3\"}]}]}]},{\"collapsible\":false,\"title\":\"Product Information\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_price\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"4\"},{\"name\":\"formItem_currency\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"203\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_inventory\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"202\",\"tabindex\":\"6\"},{\"name\":\"formItem_status\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"24\",\"width\":\"203\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":true,\"title\":\"Description\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"62\",\"width\":\"477\",\"tabindex\":\"7\"}]}]}]}]}","1","0","' . time() . '","' . time() . '");'
		)
	),
	'uninstall' => array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','uninstall.sql')),
	),
	'editable' => true,
	'searchable' => true,
	'adminOnly' => false,
	'custom' => false,
	'toggleable' => false,
	'version' => '2.0',
);
?>
