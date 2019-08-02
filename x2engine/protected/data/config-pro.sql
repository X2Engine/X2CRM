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





INSERT INTO `x2_forwarded_email_patterns` (`id`, `custom`, `groupName`, `pattern`, `bodyFrom`, `description`) 
VALUES 
(1,0,'GMail1','^\\-+{s}*Forwarded{s}*message{s}*\\-+{s}*\\*?From:{s}*\\*?(?<GMail1_name>{formattedName})?{s}*<?(?<GMail1_address>{emailAddr})>?{s}*{junkFields}*',NULL,NULL),
(2,0,'Outlook1','^From:\\s*(?<Outlook1_name>{formattedName})\\s*\\[mailto:(?<Outlook1_address>{emailAddr})\\]{s}*{junkFields}*',NULL,NULL),
(3,0,'Unknown1','^\\-+{s}*Original Message{s}*\\-+{s}+{junkFields}*\\nFrom:{s}*(?<Unknown1_name>{formattedName}){s}*\\<(?<Unknown1_address>{emailAddr})>{s}*{junkFields}*',NULL,NULL),
(4,0,'AppleMail1','^Begin{s}*forwarded{s}*message:{s}*>?\\s*From:\\s*(?<AppleMail1_name>{formattedName}){s}*<?(?<AppleMail1_address>{emailAddr})>?{s}*{junkFields}*',NULL,NULL),
(5,0,'Zimbra1','^\\-{5} Original Message \\-{5}{s}*From: \"(?<Zimbra1_name>{formattedName})\" <(?<Zimbra1_address>{emailAddr})>{s}*{junkFields}*',NULL,NULL),
(6,0,'Zimbra2','^\\-{5} Forwarded Message \\-{5}{s}*From: \"(?<Zimbra2_name>{formattedName})\" <(?<Zimbra2_address>{emailAddr})>{s}*{junkFields}*',NULL,NULL),
(7,0,'Fastmail','\\-{5} Original message \\-{5}\\n\\nFrom: (?<Fastmail_name>{formattedName}) <\\[\\d*\\](?<Fastmail_address>{emailAddr})>{s}*(?:To: \"\\[\\d*\\]{emailAddr}\"{s}*<\\[\\d*\\]{emailAddr}>)?{s}+{junkFields}*',NULL,NULL);
/*&*/
INSERT INTO `x2_reports_2` (`id`, `createdBy`, `lastUpdated`, `createDate`, `name`, `settings`, `version`, `type`) VALUES 
(1, 'admin', 1414093271, 1414093271,'Services Report',
	'{\"columns\":[\"name\",\"impact\",\"status\",\"assignedTo\",\"lastUpdated\",\"updatedBy\"],\"orderBy\":[],\"primaryModelType\":\"Services\",\"allFilters\":[],\"anyFilters\":[],\"export\":false,\"print\":false,\"email\":false}','4.3','rowsAndColumns'),
(2, 'admin',1414093762, 1414093762,'Deal Report',
	'{\"columns\":[\"name\",\"assignedTo\",\"company\",\"leadscore\",\"closedate\",\"dealvalue\",\"dealstatus\",\"rating\",\"lastUpdated\"],\"orderBy\":[],\"primaryModelType\":\"Contacts\",\"allFilters\":[],\"anyFilters\":[],\"export\":false,\"print\":false,\"email\":false}','5.0','rowsAndColumns');
/*&*/
INSERT INTO `x2_reports_2` (`id`, `createdBy`, `lastUpdated`, `createDate`, `name`, `settings`, `version`, `type`, `dataWidgetLayout`) VALUES 
(3, 'admin', 1416614514, 1416614514, 'Lead Volume',
	'{\"columns\":[\"createDate\",\"leadSource\"],\"orderBy\":[],\"primaryModelType\":\"Contacts\",\"allFilters\":[],\"anyFilters\":[],\"export\":false,\"print\":false,\"email\":false,\"includeTotalsRow\":\"0\"}','5.0','rowsAndColumns','{\"BarWidget\":{\"label\":\"Bar Chart\",\"uid\":\"\",\"hidden\":false,\"minimized\":false,\"containerNumber\":1,\"softDeleted\":false,\"chartId\":null,\"displayType\":\"bar\",\"legend\":null},\"DataWidget\":{\"label\":\"Data Widget\",\"uid\":\"\",\"hidden\":false,\"minimized\":false,\"containerNumber\":1,\"softDeleted\":false,\"chartId\":null,\"displayType\":null,\"legend\":null},\"TimeSeriesWidget\":{\"label\":\"Activity Chart\",\"uid\":\"\",\"hidden\":false,\"minimized\":false,\"containerNumber\":1,\"softDeleted\":false,\"chartId\":null,\"displayType\":\"line\",\"legend\":null,\"subchart\":false,\"timeBucket\":\"day\",\"filter\":\"month\",\"filterType\":\"trailing\",\"filterFrom\":null,\"filterTo\":null},\"TimeSeriesWidget_546fd27d8e2a2\":{\"hidden\":false,\"minimized\":false,\"label\":\"Lead Volume\",\"chartId\":\"1\",\"uid\":\"\",\"containerNumber\":1,\"softDeleted\":false,\"displayType\":\"line\",\"legend\":[\"Portland trade show\",\"null\"],\"subchart\":false,\"timeBucket\":\"day\",\"filter\":\"month\",\"filterType\":\"trailing\",\"filterFrom\":null,\"filterTo\":null},\"TimeSeriesWidget_546fe2089f793\":{\"hidden\":false,\"minimized\":false,\"label\":\"Lead Volume\",\"chartId\":\"1\",\"uid\":\"\",\"containerNumber\":2,\"softDeleted\":false,\"displayType\":\"pie\",\"legend\":[\"Portland trade show\",\"null\"],\"subchart\":false,\"timeBucket\":\"day\",\"filter\":\"week\",\"filterType\":\"trailing\",\"filterFrom\":null,\"filterTo\":null}}'), 
(4, 'admin', 1416613128, 1416613128, 'Web Activity',
	'{\"columns\":[\"createDate\",\"type\"],\"orderBy\":[],\"primaryModelType\":\"Actions\",\"allFilters\":[],\"anyFilters\":[],\"export\":false,\"print\":false,\"email\":false,\"includeTotalsRow\":\"0\"}','5.0','rowsAndColumns','{\"BarWidget\":{\"label\":\"Bar Chart\",\"uid\":\"\",\"hidden\":false,\"minimized\":false,\"containerNumber\":1,\"softDeleted\":false,\"chartId\":null,\"displayType\":\"bar\",\"legend\":null},\"DataWidget\":{\"label\":\"Data Widget\",\"uid\":\"\",\"hidden\":false,\"minimized\":false,\"containerNumber\":1,\"softDeleted\":false,\"chartId\":null,\"displayType\":null,\"legend\":null},\"TimeSeriesWidget\":{\"label\":\"Activity Chart\",\"uid\":\"\",\"hidden\":false,\"minimized\":false,\"containerNumber\":1,\"softDeleted\":false,\"chartId\":null,\"displayType\":\"line\",\"legend\":null,\"subchart\":false,\"timeBucket\":\"day\",\"filter\":\"month\",\"filterType\":\"trailing\",\"filterFrom\":null,\"filterTo\":null},\"TimeSeriesWidget_546fcd14a50ab\":{\"hidden\":false,\"minimized\":false,\"label\":\"Web Activity\",\"chartId\":\"2\",\"uid\":\"\",\"containerNumber\":1,\"softDeleted\":false,\"displayType\":\"area\",\"legend\":[\"event\",\"note\",\"null\",\"email\",\"quotes\",\"attachment\",\"time\",\"workflow\"],\"subchart\":false,\"timeBucket\":\"day\",\"filter\":\"week\",\"filterType\":\"trailing\",\"filterFrom\":null,\"filterTo\":null},\"TimeSeriesWidget_546fe2966e30e\":{\"hidden\":false,\"minimized\":false,\"label\":\"Web Activity\",\"chartId\":\"2\",\"uid\":\"\",\"containerNumber\":2,\"softDeleted\":false,\"displayType\":\"gauge\",\"legend\":[\"event\",\"note\",\"null\",\"email\",\"quotes\",\"attachment\",\"time\",\"workflow\"],\"subchart\":false,\"timeBucket\":\"day\",\"filter\":\"week\",\"filterType\":\"trailing\",\"filterFrom\":null,\"filterTo\":null}}');
/*&*/
INSERT INTO `x2_charts` (`id`, `createDate`, `createdBy`, `reportId`, `lastUpdated`, `name`, `settings`, `version`, `type`) VALUES (1,1416614525,'admin',3,1416614525,'Lead Volume','{\"timeField\":\"createDate\",\"labelField\":\"leadSource\",\"filterType\":\"trailing\",\"filter\":\"week\",\"filterFrom\":null,\"filterTo\":null}','5.0','TimeSeries'), (2,1416613140,'admin',4,1416613140,'Web Activity','{\"timeField\":\"createDate\",\"labelField\":\"type\",\"filterType\":\"trailing\",\"filter\":\"week\",\"filterFrom\":null,\"filterTo\":null}','5.0','TimeSeries');
