<?php
$install = implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install.sql'));
$installPla = implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','install-pla.sql'));
$uninstall = implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','uninstall.sql'));
$uninstallPla = implode(DIRECTORY_SEPARATOR,array(__DIR__,'data','uninstall-pla.sql'));
$formLayouts = array(
    'INSERT INTO x2_form_layouts
        (id,model,version,layout,defaultView,defaultForm,createDate,lastUpdated) VALUES
        (14,"Campaign","View",\'
{
    "sections": [
        {
            "collapsible": false, 
            "rows": [
                {
                    "cols": [
                        {
                            "items": [
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_name", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "230"
                                }
                            ], 
                            "width": 572
                        }
                    ]
                }
            ], 
            "title": ""
        }, 
        {
            "collapsible": false, 
            "rows": [
                {
                    "cols": [
                        {
                            "items": [
                                {
                                    "height": "39", 
                                    "labelType": "left", 
                                    "name": "formItem_description", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "483"
                                }
                            ], 
                            "width": 572
                        }
                    ]
                }
            ], 
            "title": ""
        }, 
        {
            "collapsible": false, 
            "rows": [
                {
                    "cols": [
                        {
                            "items": [
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_listId", 
                                    "readOnly": "0", 
                                    "tabindex": "NaN", 
                                    "width": "135"
                                }, 
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_type", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "135"
                                }
                            ], 
                            "width": 572
                        }
                    ]
                }
            ], 
            "title": ""
        }, 
        {
            "collapsible": false, 
            "rows": [
                {
                    "cols": [
                        {
                            "items": [
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_enableRedirectLinks", 
                                    "readOnly": "undefined", 
                                    "tabindex": "undefined", 
                                    "width": "154"
                                }
                            ], 
                            "width": 572
                        }
                    ]
                }
            ], 
            "title": ""
        }, 
        {
            "collapsible": true, 
            "rows": [
                {
                    "cols": [
                        {
                            "items": [
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_subject", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "226"
                                }, 
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_template", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "133"
                                }, 
                                {
                                    "height": "259", 
                                    "labelType": "left", 
                                    "name": "formItem_content", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "478"
                                }
                            ], 
                            "width": 572
                        }
                    ]
                }
            ], 
            "title": "Email Template"
        }, 
        {
            "collapsible": false, 
            "rows": [
                {
                    "cols": [
                        {
                            "items": [
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_active", 
                                    "readOnly": "1", 
                                    "tabindex": "0", 
                                    "width": "17"
                                }, 
                                {
                                    "height": "22", 
                                    "labelType": "left", 
                                    "name": "formItem_complete", 
                                    "readOnly": "1", 
                                    "tabindex": "0", 
                                    "width": "17"
                                }
                            ], 
                            "width": 572
                        }
                    ]
                }
            ], 
            "title": ""
        }, 
        {
            "collapsible": false, 
            "rows": [
                {
                    "cols": [
                        {
                            "items": [
                                {
                                    "height": "24", 
                                    "labelType": "left", 
                                    "name": "formItem_assignedTo", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "145"
                                }, 
                                {
                                    "height": "24", 
                                    "labelType": "left", 
                                    "name": "formItem_visibility", 
                                    "readOnly": "0", 
                                    "tabindex": "0", 
                                    "width": "145"
                                }
                            ], 
                            "width": 572
                        }
                    ]
                }
            ], 
            "title": ""
        }
    ], 
    "version": "1.2"
}
\',"1","0","' . time() . '","' . time() . '")'
);

return array(
	'name' => 'Marketing',
	'install' => file_exists($installPla)? array($install, $installPla, $formLayouts) : array($install, $formLayouts),
	'uninstall' => file_exists($uninstallPla)? array($uninstall, $uninstallPla) : array($uninstall),
	'editable' => true,
	'searchable' => true,
	'adminOnly' => false,
	'custom' => false,
	'toggleable' => true,
	'version' => '2.0',
);
?>
