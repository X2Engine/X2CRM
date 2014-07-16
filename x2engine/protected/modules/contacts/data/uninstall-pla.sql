DELETE FROM `x2_fields` WHERE `modelName`='Contacts' AND `fieldName`='reverseIp';
/*&*/
ALTER TABLE x2_contacts DROP reverseIp;
