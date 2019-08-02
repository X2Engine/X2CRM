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






Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/admin/userHistory.css');

?>

<div class="page-title"><h2><?php echo Yii::t('admin', 'User History'); ?></h2></div>
<div>
<?php
    echo Yii::t ('admin', 'To manage user login settings, including failed logins before '.
                          'CAPTCHA and failed logins before ban, please visit the {link} page.',
                          array(
                              '{link}' => CHtml::link(Yii::t('admin', 'Advanced Security Settings'), array('admin/securitySettings')),
                          ));
?>
<br />
<br />
</div>
<div>
<?php

    // Display a grid of failed login attempts
    $this->widget('X2GridViewGeneric', array(
        'id' => 'failed-logins-grid',
	    'title'=>Yii::t('admin', 'Failed Login Attempts'),
        'dataProvider' => $failedLoginsDataProvider,
	    'baseScriptUrl'=>  
            Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	    'template'=> '<div class="page-title">{title}'
		    .'{buttons}{summary}</div>{items}{pager}',
        'buttons' => array ('autoResize', 'exportFailedLogins'),
        'defaultGvSettings' => array (
            'IP' => 100,
            'attempts' => 120,
            'active' => 20,
            'lastAttempt' => 200,
            'aclControls' => '50',
        ),
        'gvSettingsName' => 'failed-logins-grid',
    	'columns'=>array(
    		array (
                'name' => 'IP',
                'header' => Yii::t('admin','IP Address'),
            ),
    		array (
                'name' => 'attempts',
                'header' => Yii::t('admin','Last Failed Attempts'),
            ),
            array(
                'name' => 'active',
                'header' => Yii::t('admin','Active?'),
                'type' => 'raw',
                'value' => 'X2Html::fa ($data->active ? "check" : "times")',
            ),
            array(
                'name' => 'lastAttempt',
                'header' => Yii::t('admin','Last Failed Login Attempt'),
                'type' => 'raw',
                'value' => 'Formatter::formatCompleteDate($data->lastAttempt)',
            ),
            array(
                'name' => 'aclControls',
                'header' => '',
                'type' => 'raw',
                'value' => 'Admin::renderACLControl ("blacklist", $data["IP"])',
            ),
	    ),
    ));

    echo '<br /><br />';

    // Display a grid of user login history
    $this->widget('X2GridViewGeneric', array(
        'id' => 'login-history-grid',
	    'title'=>Yii::t('admin', 'User Login History'),
        'dataProvider' => $loginHistoryDataProvider,
	    'baseScriptUrl'=>  
            Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	    'template'=> '<div class="page-title">{title}'
		    .'{buttons}{summary}</div>{items}{pager}',
        'buttons' => array ('autoResize', 'exportLogins'),
        'defaultGvSettings' => array (
            'username' => 100,
            'emailAddress' => 100,
            'IP' => 100,
            'timestamp' => 180,
            'aclControls' => 150,
        ),
        'gvSettingsName' => 'login-history-grid',
    	'columns'=>array(
    		array (
                'name' => 'username',
                'header' => Yii::t('admin','User'),
                'type' => 'raw',
                'value' => '$data->userLink',
            ),
    		array (
                'name' => 'emailAddress',
                'header' => Yii::t('admin','Email'),
                'type' => 'raw',
                'value' => '$data->email',
            ),
    		array (
                'name' => 'timestamp',
                'header' => Yii::t('admin','Login Time'),
                'type' => 'raw',
                'value' => 'Formatter::formatCompleteDate($data["timestamp"])',
            ),
    		array (
                'name' => 'IP',
                'header' => Yii::t('admin','IP Address'),
            ),
            array(
                'name' => 'aclControls',
                'header' => '',
                'type' => 'raw',
                'value' => 
                    '"<div class=\"x2-button-group\">".
                        Admin::renderACLControl ("blacklist", $data["IP"]).
                        Admin::renderACLControl ("whitelist", $data["IP"]).
                        Admin::renderACLControl ("disable", $data["username"]).
                    "</div>"',
            ),
	    ),
    ));
    ?>
</div>
