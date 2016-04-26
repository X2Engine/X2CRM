<?php
// DO NOT modify this file to enable/configure branding; it will be overwritten
// by updates to X2Engine. Instead, modify the file "branding_constants-custom.php"

if(file_exists($customConstants = __DIR__.DIRECTORY_SEPARATOR.'branding_constants-custom.php')) {
    require_once $customConstants;
}

defined('X2_PARTNER_DISPLAY_BRANDING') or define('X2_PARTNER_DISPLAY_BRANDING',false);
defined('X2_PARTNER_PRODUCT_NAME') or define('X2_PARTNER_PRODUCT_NAME','');
defined('X2_PARTNER_HELP_LINK_URL') or define('X2_PARTNER_HELP_LINK_URL','http://www.x2crm.com/reference_guide');
defined('X2_PARTNER_RENEWAL_LINK_URL') or define('X2_PARTNER_RENEWAL_LINK_URL','http://www.x2crm.com/');
?>
