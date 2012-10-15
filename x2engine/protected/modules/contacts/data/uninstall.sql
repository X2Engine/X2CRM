DROP TABLE `x2_subscribe_contacts`;
/*&*/
DROP TABLE `x2_contacts`;
/*&*/
DELETE FROM `x2_modules` WHERE `name`='contacts';
/*&*/
DELETE FROM `x2_fields` WHERE `modelName`='Contacts';
/*&*/
DELETE FROM `x2_form_layouts` WHERE `model`='Contacts';