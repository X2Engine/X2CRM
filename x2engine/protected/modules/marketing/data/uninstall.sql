DROP TABLE `x2_campaigns`,`x2_campaigns_attachments`,`x2_web_forms`;
/*&*/
DELETE FROM `x2_fields` WHERE modelName='Campaign';
/*&*/
DELETE FROM `x2_modules` WHERE modelName='marketing';
/*&*/
DELETE FROM `x2_form_layouts` WHERE `model`='Campaign';