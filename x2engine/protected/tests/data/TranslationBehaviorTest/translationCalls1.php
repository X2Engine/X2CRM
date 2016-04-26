<?php
// Dummy testing file for the codebase parser.


// Valid:
Yii::t('app','This and that thing\'s thing');
Yii::t ("admin","This and that \"thing\"");
Yii::t ("admin","Messages (with parentheses)");
Yii:: t('app','multiple').' '.Yii::t('app','messages').' '.Yii::t('app','on').' '.Yii::t('app','the').' '. Yii::t('app','same').' '.Yii::t('app','line');
Yii::t('users',"multiline
message");
Yii::t('app','message with params and {stuff}',array('{stuff}'=>'things'));
Yii::t('app','Multi-line translation '.
            'message with splitting text.');
Yii::t('app','Multi-line translation '.
            'message with {param} text.',array('{param}'=>'things'));
Yii::t('app','Special character translation: (){}_.-,+^%@*#|&!?/<>;:');
Yii::t('app','To set up a website domain alias for tracking, you\'ll need to create a' .
            ' CNAME DNS resource record through your domain name registrar. Your CNAME record\'s name should'. 
            ' refer to a subdomain of your website and should point to the domain of your CRM.');
installer_t('installer message');
installer_tr('installer message with {p}',array('{p}'=>'params'));
// "Cheating" in utility classes by using a locally-defined function / looking for exceptions:
$installer_t('Weekdays');
throw new Exception('Exceptions too');

// Miscellaneous things that were found but not parsed, and added here for extending the regex:
Yii::t('profile','Manage Passwords for Third-Party Applications');
Yii::t('admin','Define how the system sends email by default.');
Yii::t('admin','Note that this will not supersede other email settings. Usage of these particular settings is a legacy feature. Unless this web server also serves as your company\'s primary mail server, it is recommended to instead use "{ma}" to set up email accounts for system usage instead.',array('{ma}'=>CHtml::link(Yii::t('app','Manage Apps'),array('profile/manageCredentials'))));
Yii::t('admin','Configure how X2Engine sends email when responding to new service case requests.');

// Not valid/ignored:
Yii::t('users',$notastring);
Yii::t('users',"this $string contains a variable");
?>
