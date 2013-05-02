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
 ?>

<?php
$this->layout = '//layouts/column1';
$this->pageTitle=Yii::app()->name . ' - ' . Yii::t('app','Icon Reference');


$cssString = "
    #icon-reference-title {
        width: 1000px;
        margin-left: 50px;
        padding: 0 0 0 0;
    }

    div.icon-reference {
        width: 1000px;
        margin-left: 50px;
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
                <p> <?php echo CHtml::link (Yii::t('accounts', 'Accounts'), array ('/accounts/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/feed.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('help', 'Activity Feed'), array ('/site/whatsNew')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/actions.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('actions', 'Actions'), array ('/actions/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/calendar.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('calendar', 'Calendar'), array ('/calendar/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/charts.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('charts', 'Charts'), array ('/charts/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/contacts.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('contacts', 'Contacts'), array ('/contacts/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/docs.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('docs', 'Docs'), array ('/docs/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/groups.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('groups', 'Groups'), array ('/groups/index')); ?> </p>
            </div>
        </div>
    </div>
    <div class="cell">
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/media.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('media', 'Media'), array ('/media/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/marketing.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('marketing', 'Marketing'), array ('/marketing/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/opportunities.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('opportunities', 'Opportunities'), array ('/opportunities/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/products.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('products', 'Products'), array ('/products/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/quote_emailed.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('quotes', 'Quotes'), array ('/quotes/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/services.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('services', 'Services'), array ('/services/index')); ?> </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/workflow.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> <?php echo CHtml::link (Yii::t('workflow', 'Workflow'), array ('/workflow/index')); ?> </p>
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
                    <?php echo Yii::t ('help', 'Web Activity'); ?>
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
                    <?php echo Yii::t ('help', 'Workflow Reverted'); ?>
                </p>
            </div>
        </div>
        <div class="row">
            <div class="icon-container">
                <?php echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/images/eventIcons/workflow_start.png' . "'/>"; ?>
            </div>
            <div class="icon-description">
                <p> 
                    <?php echo Yii::t ('help', 'Workflow Started'); ?>
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



