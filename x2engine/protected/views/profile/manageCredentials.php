<?php

/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

$this->actionMenu = array(
	array('label'=>Yii::t('profile','View Profile'), 'url'=>array('view','id'=>$profile->id)),
	array('label'=>Yii::t('profile','Edit Profile'),'url'=>array('update','id'=>$profile->id)),
	array('label'=>Yii::t('profile','Change Settings'),'url'=>array('settings','id'=>$profile->id),'visible'=>($profile->id==Yii::app()->user->id)),
	array('label'=>Yii::t('profile','Change Password'),'url'=>array('changePassword','id'=>$profile->id),'visible'=>($profile->id==Yii::app()->user->id)),
	array('label'=>Yii::t('profile','Manage Apps'))
);

Yii::app()->clientScript->registerScript ('manageCredentialsScript', "

    function validate () {
        auxlib.destroyErrorFeedbackBox ($('#class'));
        if ($('#class').val () === '') {
            auxlib.createErrorFeedbackBox ({
                'prevElem': $('#class'),
                'message': '".Yii::t ('app', 'Account type required')."'
            });
            return false;
        }
        return true;
    }

", CClientScript::POS_HEAD);

?>

<div class="page-title icon profile">
    <h2><?php echo Yii::t('profile','Manage Passwords for Third-Party Applications'); ?></h2>
</div>
<div class="credentials-storage">
<?php
$crit = new CDbCriteria(array(
		'condition'=>'userId=:uid OR userId=-1',
		'order' => 'name ASC',
		'params' => array(':uid' => $profile->user->id),
	)
);
$staticModel = Credentials::model();
$staticModel->private = 0;
if(Yii::app()->user->checkAccess('CredentialsSelectNonPrivate',array('model'=>$staticModel)))
	$crit->addCondition('private=0','OR');

$dp = new CActiveDataProvider('Credentials',array(
	'criteria' => $crit,
));
$this->widget('zii.widgets.CListView', array(
	'dataProvider' => $dp,
	'itemView' => '_credentialsView',
	'itemsCssClass' => 'credentials-list',
	'summaryText' => '',
	'emptyText' => ''
));
?>

<?php
echo CHtml::beginForm(
    array('/profile/createUpdateCredentials'),
    'get',
    array (
        'onSubmit' => 'return validate ();'
    )
);
echo CHtml::submitButton(
    Yii::t('app','Add New'),array('class'=>'x2-button','style'=>'float:left;margin-top:0'));
$modelLabels = Credentials::model()->authModelLabels;
$types = array_merge(array(null=>'- '.Yii::t('app','select a type').' -'),$modelLabels);
echo CHtml::dropDownList(
    'class',
    'EmailAccount',
    $types,
    array(
        'options'=>array_merge(
            array(null=>array('selected'=>'selected')),
            array_fill_keys(array_keys($modelLabels),array('selected'=>false))),
        'class' => 'left'
    )
);
echo CHtml::endForm();

?>
</div>
