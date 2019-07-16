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



 ?>

<?php
$this->layout = '//layouts/column1';
$this->pageTitle=Yii::app()->settings->appName . ' - ' . Yii::t('help','Icon Reference');


$cssString = "
    #icon-reference-title {
        width: 1002px;
        margin-right: 0;
        padding: 0 0 0 0;
    }

    div.icon-reference {
        width: 1000px;
        margin-left: 50px;
        margin-right: 0;
        padding: 0 0 0 0;
    }

    div.icon-reference .section-title {
        margin-left: 20px;
        margin-top: 15px;
    }

    div.icon-reference .cell {
        margin-left: 20px;
        margin-bottom: 10px;
    }
 
    div.icon-reference .row {
        width: 460px;
        height: 60px;
        line-height: 60px;
        vertical-align: middle;
    }
 
    div.icon-reference img {
        vertical-align: middle;
        display: inline-block;
        /*margin-bottom: 20px;*/
    }

    div.icon-reference .icon-container {
        float: left;
        height: 60px;
        font-size: 30px;
        color: #004baf; // darkBlue in colors.scss
    }
 
    div.icon-reference .icon-description {
        margin-left: 60px;
        height: 60px;
    }

    div.icon-reference .icon-description p {
        vertical-align: middle;
        margin: 0 0 0 0;
        display: inline-block;
        font-size: 12px;
	    font-family: Arial, Helvetica, sans-serif;
        line-height: 14px;
    }

    .img-box .stacked-icon {
        top: 32px;
    }

    .icon-reference .section-title {
        background: none;
    }


";

Yii::app()->clientScript->registerCss ('icon-reference-css', $cssString);


?>

<div id="icon-reference-title" class="page-title">
    <h2> <?php echo Yii::t('help', 'Icon Reference'); ?> </h2>
</div>


<div id="icon-reference-section-1" class="icon-reference form p-20">
    <h2 class="section-title"> 
        <?php echo Yii::t ('help', 'X2Engine Modules'); ?>
    </h2>
    <div class="column1 cell">
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-building'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('accounts', 'Accounts'), array ('/accounts/accounts/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::x2icon('activity') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Activity Feed'), array ('/profile/view', 'id' => Yii::app()->user->getId())); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-play-circle') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Actions'), array ('/actions/actions/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-calendar-o') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('calendar', 'Calendar'), array ('/calendar/calendar/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-bar-chart') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Charts'), array ('/reports/chartDashboard')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-file-o') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Reports'), array ('/reports/savedReports')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::x2icon('contact') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('contacts', 'Contacts'), array ('/contacts/contacts/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-file-o') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('docs', 'Docs'), array ('/docs/docs/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-users'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Groups'), array ('/groups/groups/index')); ?> </p>
            </div>
        </div>
    </div>
    <div class="cell">
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-music'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Media'), array ('/media/media/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-bullhorn') ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Marketing'), array ('/marketing/marketing/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::fa('fa-bullseye'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('opportunities', 'Opportunities'), array ('/opportunities/opportunities/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::x2icon('package'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('products', 'Products'), array ('/products/products/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::x2icon('quotes'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('quotes', 'Quotes'), array ('/quotes/quotes/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::x2icon('service'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('help', 'Services'), array ('/services/services/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo X2Html::x2icon('funnel'); ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('workflow', 'Process'), array ('/workflow/workflow/index')); ?> </p>
            </div>
        </div>
    </div>
</div>

<?php 
Yii::app()->clientScript->registerCssFile (Yii::app()->theme->baseUrl.'/css/activityFeed.css');

/* The event icons are assigned in activityFeed.css, so this way reuses the assignments there */

$arr1 = array (
    'action_complete'    => 'Action Completed',
    'action_reminder'    => 'Action Reminder',
    'generic_calendar_event'     => 'Calendar Event',
    'doc_update'         => 'Document Updated',
    'email_from'         => 'Email Received',
    'email_sent'         => 'Email Sent',
    'record_create'      => 'Record Created',
    'record_deleted'     => 'Record Deleted',
);


$arr2 = array (
    'notif'              => 'Notification',
    'web_activity'       => 'Web Activity',
    'weblead_create'     => 'Web Lead Created',
    'case_escalated'     => 'Case Escalated',
    'email_opened'       => 'Email Opened',
    'workflow_revert'    => 'Workflow Reverted',
    'workflow_complete'  => 'Workflow Completed',
    'workflow_start'     => 'Workflow Started',
);


function echoIcons ($array) {
    foreach($array as $key => $value) {
        echo "<div class='row'>
            <div class='img-box $key'>
                <div class='stacked-icon'></div>
            </div>
            <div class='icon-description'>
                <p> 
                    ".Yii::t ('help', $value)."
                </p>
            </div>
        </div>";
    }
}
?>


<div id="activity-feed-container" class="icon-reference form p-20">
    <h2 class="section-title"> 
        <?php echo Yii::t ('help', 'Events'); ?>
    </h2>
    <div class="column1 cell">
        <?php echoIcons($arr1); ?>
    </div>
    <div class="cell">
        <?php echoIcons($arr2); ?>
    </div>
</div>



