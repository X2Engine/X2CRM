<?php

return array(
	'name' => "Opportunities",
	'install' => array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install.sql')),
		array('INSERT INTO x2_form_layouts (id,model,version,layout,defaultView,defaultForm,createDate,lastUpdated) VALUES
				(3,"Opportunity","Form","{\"version\":\"5.2\",\"sections\":[{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_name\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_salesStage\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_email\",\"labelType\":\"left\",\"readOnly\":0}],\"width\":\"49.82%\"},{\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_contactName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.82%\"}]}],\"collapsible\":false,\"title\":\"Basic Information\"},{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_expectedCloseDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_quoteAmount\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.82%\"},{\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.82%\"}]}],\"collapsible\":false,\"title\":\"Other Info\"},{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"99.82%\"}]}],\"collapsible\":true,\"collapsedByDefault\":false,\"title\":\"Description\"}]}","0","1","' . time() . '","' . time() . '"),
				(4,"Opportunity","View","{\"version\":\"5.2\",\"sections\":[{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_createDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_salesStage\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_email\",\"labelType\":\"left\",\"readOnly\":0}],\"width\":\"49.82%\"},{\"items\":[{\"name\":\"formItem_accountName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_contactName\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_leadSource\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.82%\"}]}],\"collapsible\":false,\"title\":\"Basic Information\"},{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_expectedCloseDate\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_quoteAmount\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"},{\"name\":\"formItem_probability\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.82%\"},{\"items\":[{\"name\":\"formItem_assignedTo\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"49.82%\"}]}],\"collapsible\":false,\"title\":\"Other Info\"},{\"rows\":[{\"cols\":[{\"items\":[{\"name\":\"formItem_description\",\"labelType\":\"left\",\"readOnly\":\"0\",\"tabindex\":\"0\"}],\"width\":\"99.82%\"}]}],\"collapsible\":true,\"collapsedByDefault\":false,\"title\":\"Description\"}]}","1","0","' . time() . '","' . time() . '")'
		),
	),
	'uninstall' => array(
		implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','uninstall.sql')),
	),
	'editable' => true,
	'searchable' => true,
	'adminOnly' => false,
	'custom' => false,
	'toggleable' => false,
	'version' => '2.0'
);
?>
