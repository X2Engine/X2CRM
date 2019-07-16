<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/





$this->insertActionMenu();
// $this->actionMenu = array(
//     array('label' => Yii::t('profile', 'View Profile'), 'url' => array('view', 'id' => Yii::app()->user->getId())),
//     array('label' => Yii::t('profile', 'Edit Profile'), 'url' => array('update', 'id' => Yii::app()->user->getId())),
//     array('label' => Yii::t('profile', 'Change Settings'), 'url' => array('settings'),),
//     array('label' => Yii::t('profile', 'Change Password'), 'url' => array('changePassword', 'id' => Yii::app()->user->getId())),
//     array('label' => Yii::t('profile', 'Manage Apps'), 'url' => array('manageCredentials')),
//     array('label' => Yii::t('profile', 'Manage Email Reports')),
// );

$this->widget('X2GridViewGeneric', array(
    'id' => 'email-reports-grid',
    'baseScriptUrl' => Yii::app()->request->baseUrl . '/themes/' . Yii::app()->theme->name . '/css/gridview',
    'template' => '<div class="page-title icon profile"><h2>' . CHtml::encode(Yii::t('profile', 'Manage Email Reports')) . '</h2>'
    . '{summary}</div>{items}{pager}',
    'dataProvider' => $dataProvider,
    'columns' => array(
        'name' => array(
            'name'=>'name',
            'header' => Yii::t('profile','Name'),
        ),
        'schedule' => array(
            'name'=>'schedule',
            'header'=>Yii::t('profile','Schedule'),
        ),
        'active' => array(
            'name' => 'active',
            'header' => Yii::t('profile','Active'),
            'type' => 'raw',
            'value' => 'CHtml::tag("span",array("id"=>$data->id."-status"),$data->cronEvent->recurring?Yii::t("profile","Yes"):Yii::t("profile","No"))'
        ),
        'lastSent' => array(
            'name' => 'lastSent',
            'header' => Yii::t('profile','Last Sent'),
            'type' => 'raw',
            'value' => 'isset($data->cronEvent->lastExecution)?Formatter::formatLongDateTime($data->cronEvent->lastExecution):Yii::t("profile","Never")',
        ),
        'controls' => array(
            'name' => 'controls',
            'header' => Yii::t('profile','Controls'),
            'type' => 'raw',
            'value' => 'CHtml::ajaxButton($data->cronEvent->recurring?Yii::t("profile","Stop"):Yii::t("profile","Start"), "toggleEmailReport", array("success"=>"function(html){jQuery(\"#".$data->id."-status\").html(html==0?\"Yes\":\"No\");jQuery(\"#".$data->id."-toggle-button\").val(html==0?\"Stop\":\"Start\")}","data"=>array("id"=>$data->id)), array("id"=>$data->id."-toggle-button","class"=>"x2-button","style"=>"float:left"))' .
            '.CHtml::ajaxButton(Yii::t("profile","Delete"),"deleteEmailReport",array("complete"=>"$.fn.yiiGridView.update(\'email-reports-grid\')","data"=>array("id"=>$data->id)),array("class"=>"x2-button","style"=>"float:right"))',
        ),
    ),
));
?>