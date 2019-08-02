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




$migratePageTitleColors = function() {
    $sql = "INSERT INTO `x2_docs` 
        (`id`,`name`, `nameId`, `subject`, `type`, `text`, `createdBy`, `createDate`, `updatedBy`, `lastUpdated`, `visibility`) 
        VALUES (56,'An email template for shaving 20 hours off your work week','My plan for the week_56',NULL,'email','<html>\r\n<head>\r\n	<title></title>\r\n</head>\r\n<body>\r\n<p style=\"margin:0px;font-family:Georgia;font-size:17px;\">{firstName},<br />\r\n<br />\r\n&nbsp;</p>\r\n\r\n<p style=\"margin:0px;font-family:Georgia;font-size:17px;\">After reviewing my activities here is my plan for the week in order of priority. Let me know if you think I should re-prioritize:<br />\r\n<br />\r\n&nbsp;</p>\r\n\r\n<p style=\"margin:0px;font-family:Georgia;font-size:17px;\">Planned Major Activities for the week<br />\r\n<br />\r\n&nbsp;</p>\r\n\r\n<p style=\"margin:0px;font-family:Georgia;font-size:17px;\">1) {firstActivity}</p>\r\n\r\n<p style=\"margin:0px;font-family:Georgia;font-size:17px;\">2) <span style=\"font-family:Georgia;font-size:17px;\">{secondActivity</span><span style=\"font-family:Georgia;font-size:17px;\">}</span></p>\r\n\r\n<p style=\"margin:0px;font-family:Georgia;font-size:17px;\">3) <span style=\"font-family:Georgia;font-size:17px;\">{thirdActivity</span><span style=\"font-family:Georgia;font-size:17px;\">}</span><br />\r\n<br />\r\n&nbsp;</p>\r\n\r\n<p style=\"margin:0px;font-family:Georgia;font-size:17px;\">Open items that I will look into, but won&rsquo;t get finished this week<br />\r\n<br />\r\n&nbsp;</p>\r\n\r\n<p style=\"margin:0px;font-family:Georgia;font-size:17px;\">1) <span style=\"font-family:Georgia;font-size:17px;\">{firstActivityUnfinished</span><span style=\"font-family:Georgia;font-size:17px;\">}</span></p>\r\n\r\n<p style=\"margin:0px;font-family:Georgia;font-size:17px;\">2) <span style=\"font-family:Georgia;font-size:17px;\">{secondActivityUnfinished</span><span style=\"font-family:Georgia;font-size:17px;\">}</span><br />\r\n<br />\r\n&nbsp;</p>\r\n\r\n<p style=\"margin:0px;font-family:Georgia;font-size:17px;\">Let me know if you have any comments. Thank you!</p>\r\n\r\n<p style=\"margin:0px;font-family:Georgia;font-size:17px;\">{signature}</p>\r\n</body>\r\n</html>\r\n','Anyone',1498806917,'Anyone',1498806941,1), 
        (57,'When you receive perpetual last-minute requests','When you receive perpetual last-minute requests_57',NULL,'email','{firstName},<br /><br /><span style=\"font-family:Georgia;font-size:17px;\">I would love to help you out, but I already made commitments to {personBusyWith} to complete their projects today. It would not be fair to them to not follow through on what I said I would do. I will be sure to fit this in as soon as possible. Thanks for your understanding.<br /><br />\n{signature}</span><br />\n','Anyone',1498828106,'Anyone',1498828106,1), 
        (58,'When you are given an exceptionally short deadline','When you’re given an exceptionally short deadline_58',NULL,'email','{firstName},<br />\n\n<blockquote style=\"margin-right:63.578125px;font-family:Georgia;\">\n<p style=\"margin:0px;\">I know {project} is a high priority for you, and if it is absolutely necessary for me to turn something in by {dueDate}, I can make it happen. But if I could have {amountOfTmeNeeded}, I could really deliver something of higher quality. Would it be possible for me to have a bit more time?</p>\n</blockquote>\n<br /><br />\n{signature}','Anyone',1367966539,'Anyone',1367966539,1);";
    Yii::app()->db->createCommand($sql)->execute();
};

$migratePageTitleColors();
