<?php

return array(
    'name'=>"Calendar",
    'install'=>array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install.sql')),
		array(
			'INSERT INTO x2_form_layouts 
						(id,model,version,layout,defaultView,defaultForm,createDate,lastUpdated) 
				VALUES	(11,"Calendar","Form","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Calendar\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Permissions\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_viewPermission\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"65\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_editPermission\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"65\",\"tabindex\":\"0\"}]}]}]}]}","0","1","'.time().'","'.time().'"),
						(12,"Calendar","View","{\"version\":\"1.0\",\"sections\":[{\"collapsible\":false,\"title\":\"Calendar\",\"rows\":[{\"cols\":[{\"width\":588,\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"135\",\"tabindex\":\"0\"}]}]}]},{\"collapsible\":false,\"title\":\"Permissions\",\"rows\":[{\"cols\":[{\"width\":293,\"items\":[{\"name\":\"formItem_viewPermission\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"65\",\"tabindex\":\"0\"}]},{\"width\":294,\"items\":[{\"name\":\"formItem_editPermission\",\"labelType\":\"left\",\"readOnly\":\"0\",\"height\":\"22\",\"width\":\"65\",\"tabindex\":\"0\"}]}]}]}]}","1","0","'.time().'","'.time().'")',
			),
	),
    'uninstall'=>array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','uninstall.sql')),
    ),
    'editable'=>false,
    'searchable'=>false,
    'adminOnly'=>false,
    'custom'=>false,
    'toggleable'=>false,
	'version' => '2.0',
    
);
?>
