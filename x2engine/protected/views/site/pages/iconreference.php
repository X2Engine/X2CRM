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
 ?>

<?php
$this->layout = '//layouts/column1';
$this->pageTitle=Yii::app()->name . ' - ' . Yii::t('help','Icon Reference');


$cssString = "
    #icon-reference-title {
        width: 1002px;
        margin-left: 50px;
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

";

Yii::app()->clientScript->registerCss('icon-reference-css', $cssString);

?>

<div id="icon-reference-title" class="page-title">
    <h2> <?php echo Yii::t('help', 'Icon Reference'); ?> </h2>
</div>


<div id="icon-reference-section-1" class="icon-reference form p-20">
    <h2 class="section-title"> 
        <?php echo Yii::t ('help', 'X2CRM Modules'); ?>
    </h2>
    <div class="column1 cell">
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/accounts.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('accounts', 'Accounts'), array ('/accounts/accounts/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/feed.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Activity Feed'), array ('/site/whatsNew')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/actions.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Actions'), array ('/actions/actions/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/calendar.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('calendar', 'Calendar'), array ('/calendar/calendar/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/charts.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Charts'), array ('/charts/charts/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/contacts.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('contacts', 'Contacts'), array ('/contacts/contacts/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/docs.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('docs', 'Docs'), array ('/docs/docs/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/groups.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Groups'), array ('/groups/groups/index')); ?> </p>
            </div>
        </div>
    </div>
    <div class="cell">
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/media.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Media'), array ('/media/media/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/marketing.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('app', 'Marketing'), array ('/marketing/marketing/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/opportunities.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('opportunities', 'Opportunities'), array ('/opportunities/opportunities/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/products.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('products', 'Products'), array ('/products/products/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/quote_emailed.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('quotes', 'Quotes'), array ('/quotes/quotes/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/services.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('help', 'Services'), array ('/services/services/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/workflow.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('workflow', 'Workflow'), array ('/workflow/workflow/index')); ?> </p>
            </div>
        </div>
    </div>
</div>


<div id="icon-reference-section-2" class="icon-reference form p-20">
    <h2 class="section-title"> 
        <?php echo Yii::t ('help', 'Events'); ?>
    </h2>
    <div class="column1 cell">
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/action_complete.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('help', 'Action Completed'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/action_reminder.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('help', 'Action Reminder'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/calendar_event.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('help', 'Calendar Event'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/case_escalated.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('help', 'Case Escalated'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/doc_update.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('help', 'Document Updated'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/email_from.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('help', 'Email Received'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/email_opened.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('help', 'Email Opened'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/email_sent.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('help', 'Email Sent'); ?>
                </p>
            </div>
        </div>
    </div>
    <div class="cell">
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/notif.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('help', 'Notification'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/record_create.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('help', 'Record Created'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/record_deleted.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('help', 'Record Deleted'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/web_activity.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('app', 'Web Activity'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/weblead_create.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('help', 'Web Lead Created'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/workflow_revert.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('app', 'Workflow Reverted'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/workflow_start.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('app', 'Workflow Started'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/workflow_complete.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('help', 'Workflow Completed'); ?>
                </p>
            </div>
        </div>
    </div>
</div>



