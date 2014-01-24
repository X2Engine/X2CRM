<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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


$this->widget('X2Chart', array (
    'getChartDataActionName' => 'getEventsBetween',
    'suppressChartSettings' => false,
    'actionParams' => array (),
    'metricTypes' => array (
        'any'=>Yii::t('app', 'All Events'),
        'notif'=>Yii::t('app', 'Notifications'),
        'feed'=>Yii::t('app', 'Feed Events'),
        'comment'=>Yii::t('app', 'Comments'),
        'record_create'=>Yii::t('app', 'Records Created'),
        'record_deleted'=>Yii::t('app', 'Records Deleted'),
        'weblead_create'=>Yii::t('app', 'Webleads Created'),
        'workflow_start'=>Yii::t('app', 'Process Started'),
        'workflow_complete'=>Yii::t('app', 'Process Complete'),
        'workflow_revert'=>Yii::t('app', 'Process Reverted'),
        'email_sent'=>Yii::t('app', 'Emails Sent'),
        'email_opened'=>Yii::t('app', 'Emails Opened'),
        'web_activity'=>Yii::t('app', 'Web Activity'),
        'case_escalated'=>Yii::t('app', 'Cases Escalated'),
        'calendar_event'=>Yii::t('app', 'Calendar Events'),
        'action_reminder'=>Yii::t('app', 'Action Reminders'),
        'action_complete'=>Yii::t('app', 'Actions Completed'),
        'doc_update'=>Yii::t('app', 'Doc Updates'),
        'email_from'=>Yii::t('app', 'Email Received'),
        'voip_calls'=>Yii::t('app', 'VOIP Calls'),
        'media'=>Yii::t('app', 'Media')
    ),
    'chartType' => 'eventsChart',
    'getDataOnPageLoad' => true,
    'hideByDefault' => false,
    'isAjaxRequest' => $isAjaxRequest,
    'chartSubtype' => $chartSubtype
));
