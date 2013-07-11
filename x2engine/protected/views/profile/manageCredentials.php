<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

$this->actionMenu = array(
	array('label'=>Yii::t('profile','View Profile'), 'url'=>array('view','id'=>$profile->id)),
	array('label'=>Yii::t('profile','Update Profile'),'url'=>array('update','id'=>$profile->id)),
	array('label'=>Yii::t('profile','Change Settings'),'url'=>array('settings','id'=>$profile->id),'visible'=>($profile->id==Yii::app()->user->id)),
	array('label'=>Yii::t('profile','Change Password'),'url'=>array('changePassword','id'=>$profile->id),'visible'=>($profile->id==Yii::app()->user->id)),
	array('label'=>Yii::t('profile','Manage Apps'))
);
?>

<div class="page-title"><h2><?php echo Yii::t('profile','Manage Passwords for Third-Party Applications'); ?></h2></div>
<div style="padding:10px;">
<?php

$dp = new CActiveDataProvider('Credentials',array(
	'criteria' => array(
		'condition'=>'userId=:uid OR userId IS NULL',
		'order' => 'name ASC',
		'params' => array(':uid' => $profile->user->id),
	),
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
echo CHtml::beginForm(array('profile/createUpdateCredentials'),'get');
echo CHtml::submitButton(Yii::t('app','Add New'),array('class'=>'x2-button','style'=>'float:left;margin-top:0'));
echo CHtml::dropDownList('class','EmailAccount',Credentials::model()->authModelLabels);
echo CHtml::endForm();

?>
</div>
