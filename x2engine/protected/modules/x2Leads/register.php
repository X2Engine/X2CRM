<?php

return array(
    'name' => "X2Leads",
    'install' => array(
        implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install.sql')),
        array('INSERT INTO x2_form_layouts (id,model,version,layout,defaultView,defaultForm,createDate,lastUpdated) VALUES 
        (22,"X2Leads","Form",\'{"sections":[{"collapsible":false,"rows":[{"cols":[{"items":[{"height":"22","labelType":"left","name":"formItem_firstName","readOnly":"0","tabindex":"0","width":"187"},{"height":"22","labelType":"left","name":"formItem_salesStage","readOnly":"0","tabindex":"0","width":"187"}],"width":293},{"items":[{"height":"22","labelType":"left","name":"formItem_lastName","readOnly":"0","tabindex":"0","width":"187"},{"height":"22","labelType":"left","name":"formItem_accountName","readOnly":"0","tabindex":"0","width":"187"},{"height":"22","labelType":"left","name":"formItem_leadSource","readOnly":"0","tabindex":"0","width":"187"}],"width":294}]}],"title":"BasicInformation"},{"collapsible":false,"rows":[{"cols":[{"items":[{"height":"22","labelType":"left","name":"formItem_expectedCloseDate","readOnly":"0","tabindex":"0","width":"187"},{"height":"22","labelType":"left","name":"formItem_quoteAmount","readOnly":"0","tabindex":"0","width":"187"},{"height":"22","labelType":"left","name":"formItem_probability","readOnly":"0","tabindex":"0","width":"187"}],"width":293},{"items":[{"height":"24","labelType":"left","name":"formItem_assignedTo","readOnly":"0","tabindex":"0","width":"184"}],"width":294}]}],"title":"OtherInfo"},{"collapsible":true,"rows":[{"cols":[{"items":[{"height":"61","labelType":"left","name":"formItem_description","readOnly":"0","tabindex":"0","width":"482"}],"width":588}]}],"title":"Description"}],"version":"1.0"}\',"0","1","' . time() . '","' . time() . '"),
        (23,"X2Leads","View",\'{"version":"1.0","sections":[{"collapsible":false,"title":"Basic Information","rows":[{"cols":[{"width":293,"items":[{"name":"formItem_createDate","labelType":"left","readOnly":"0","height":"22","width":"187","tabindex":"0"},{"name":"formItem_salesStage","labelType":"left","readOnly":"0","height":"22","width":"187","tabindex":"0"}]},{"width":294,"items":[{"name":"formItem_accountName","labelType":"left","readOnly":"0","height":"22","width":"187","tabindex":"0"},{"name":"formItem_leadSource","labelType":"left","readOnly":"0","height":"22","width":"187","tabindex":"0"}]}]}]},{"collapsible":false,"title":"Other Info","rows":[{"cols":[{"width":293,"items":[{"name":"formItem_expectedCloseDate","labelType":"left","readOnly":"0","height":"22","width":"187","tabindex":"0"},{"name":"formItem_quoteAmount","labelType":"left","readOnly":"0","height":"22","width":"187","tabindex":"0"},{"name":"formItem_probability","labelType":"left","readOnly":"0","height":"22","width":"187","tabindex":"0"}]},{"width":294,"items":[{"name":"formItem_assignedTo","labelType":"left","readOnly":"0","height":"24","width":"184","tabindex":"0"}]}]}]},{"collapsible":true,"title":"Description","rows":[{"cols":[{"width":588,"items":[{"name":"formItem_description","labelType":"left","readOnly":"0","height":"61","width":"482","tabindex":"0"}]}]}]}]}\',"1","0","' . time() . '","' . time() . '")'
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
