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






INSERT INTO `x2_auth_item_child`
(`parent`,`child`)
VALUES
('GeneralAdminSettingsTask','AdminManageActionPublisherTabs'),
('GeneralAdminSettingsTask','AdminFlowSettings'),
('GeneralAdminSettingsTask','AdminEmailDropboxSettings'),
('GeneralAdminSettingsTask','AdminViewLog'),
('GeneralAdminSettingsTask','AdminLockApp'),
('GeneralAdminSettingsTask','AdminX2CronSettings'),
('GeneralAdminSettingsTask','AdminSecuritySettings'),
('GeneralAdminSettingsTask','AdminPackager'),
('GeneralAdminSettingsTask','AdminDisableUser'),
('GeneralAdminSettingsTask','AdminBanIp'),
('GeneralAdminSettingsTask','AdminWhitelistIp'),
('GeneralAdminSettingsTask','AdminExportLoginHistory'),
('GeneralAdminSettingsTask','AdminImportPackage'),
('GeneralAdminSettingsTask','AdminPreviewPackageImport'),
('GeneralAdminSettingsTask','AdminExportPackage'),
('GeneralAdminSettingsTask','AdminBeginPackageRevert'),
('GeneralAdminSettingsTask','AdminFinishPackageRevert'),
('GeneralAdminSettingsTask','AdminRevertPackage'),
('ReportsReadOnlyAccess','ReportsActivityReport'),
('ReportsReadOnlyAccess', 'ReportsInlineEmail'),
('ReportsReadOnlyAccess','ReportsChartDashboard'),
('ReportsReadOnlyAccess','ReportsMobileChartDashboard'),
('ReportsReadOnlyAccess','ReportsViewChart'),
('ReportsReadOnlyAccess','ReportsCloneChart'),
('ReportsReadOnlyAccess','ReportsAddToDashboard'),
('ReportsReadOnlyAccess','ReportsCreateChart'),
('ReportsReadOnlyAccess','ReportsFetchData'),
('ReportsReadOnlyAccess','ReportsPrintChart'),
('ReportsReadOnlyAccess','ReportsCallChartFunction'),
('ReportsAdminAccess','ReportsAdmin'),
('administrator','ReportsAdminAccess'),
('ReportsReadOnlyAccess','ReportsDealReport'),
('ReportsReadOnlyAccess','ReportsDelete'),
('ReportsReadOnlyAccess','ReportsDeleteNote'),
('ReportsAdminAccess','ReportsMinimumRequirements'),
('ReportsMinimumRequirements','ReportsGetOptions'),
('ReportsReadOnlyAccess','ReportsGridReport'),
('ReportsMinimumRequirements','ReportsIndex'),
('ReportsIndex', 'ReportsX2GridViewMassAction'),
('ReportsReadOnlyAccess','ReportsLeadPerformance'),
('ReportsReadOnlyAccess','ReportsMinimumRequirements'),
('ReportsReadOnlyAccess','ReportsPrintReport'),
('ReportsReadOnlyAccess','ReportsSavedReports'),
('ReportsReadOnlyAccess','ReportsSaveReport'),
('ReportsReadOnlyAccess','ReportsSaveTempImage'),
('ReportsReadOnlyAccess','ReportsView'),
('ReportsReadOnlyAccess','ReportsQuickView'),
('ReportsReadOnlyAccess','ReportsCopy'),
('ReportsReadOnlyAccess','ReportsUpdate'),
('ReportsReadOnlyAccess','ReportsRowsAndColumnsReport'),
('ReportsReadOnlyAccess','ReportsSummationReport'),
('ReportsReadOnlyAccess','ReportsExternalReport'),
('ReportsMinimumRequirements','ReportsSearch'),
('ReportsReadOnlyAccess','ReportsWorkflow'),
('ReportsMinimumRequirements','ReportsGetItems'),
('RoleAccessTask','AdminEditRoleAccess'),
('MarketingFullAccess','WeblistCreate'),
('MarketingFullAccess','WeblistDelete'),
('MarketingMinimumRequirements','WeblistIndex'),
('MarketingFullAccess','WeblistUpdate'),
('MarketingFullAccess','WeblistRemoveFromList'),
('MarketingAdminAccess','MarketingWebTracker'),
('MarketingAdminAccess','MarketingExportWebTracker'),
('MarketingPrivateReadOnlyAccess','WeblistView'),
('MarketingPrivateReadOnlyAccess','WeblistQuickView'),
('MarketingReadOnlyAccess','WeblistView'),
('MarketingReadOnlyAccess','WeblistQuickView'),
('ServicesAdminAccess','ServicesServicesReport'),
('ServicesAdminAccess','ServicesExportServiceReport'),
('AccountsAdminAccess','AccountsAccountsReport'),
('AccountsAdminAccess','AccountsAccountsCampaign'),
('AccountsAdminAccess','AccountsExportAccountsReport'),
('X2StudioTask','StudioImportFlow'),
('X2StudioTask','StudioExportFlow'),
('X2StudioTask','StudioAjaxGetModelAutocomplete'),
('X2StudioTask','StudioFlowIndex'),
('X2StudioTask','StudioFlowDesigner'),
('X2StudioTask','StudioDeleteFlow'),
('X2StudioTask','StudioTest'),
('X2StudioTask','StudioGetParams'),
('X2StudioTask','StudioGetFields'),
('X2StudioTask','StudioDeleteNote'),
('X2StudioTask','StudioTriggerLogs'),
('X2StudioTask','StudioDeleteAllTriggerLogs'),
('X2StudioTask','StudioDeleteAllTriggerLogsForAllFlows'),
('X2StudioTask','StudioDeleteTriggerLog'),
('X2StudioTask','StudioSearch'),
('X2StudioTask','StudioQuickView'),
('administrator', 'EmailInboxesAdminAccess'),
('administrator', 'EmailInboxesReadOnlyAccess'),
('EmailInboxesReadOnlyAccess', 'EmailInboxesMinimumRequirements'),
('EmailInboxesAdminAccess', 'EmailInboxesSharedInboxesIndex'),
('EmailInboxesAdminAccess', 'EmailInboxesCreateSharedInbox'),
('EmailInboxesAdminAccess', 'EmailInboxesUpdateSharedInbox'),
('EmailInboxesAdminAccess', 'EmailInboxesDeleteSharedInbox'),
('EmailInboxesAdminAccess', 'EmailInboxesMinimumRequirements'),
('EmailInboxesAdminAccess', 'EmailInboxesAdmin'),
('EmailInboxesAdminAccess', 'EmailInboxesDeleteNote'),
('EmailInboxesMinimumRequirements', 'EmailInboxesGetItems'),
('EmailInboxesMinimumRequirements', 'EmailInboxesQuickView'),
('EmailInboxesMinimumRequirements', 'EmailInboxesForgetInbox'),
('EmailInboxesMinimumRequirements', 'EmailInboxesInlineEmail'),
('EmailInboxesMinimumRequirements', 'EmailInboxesSearch'),
('EmailInboxesMinimumRequirements', 'EmailInboxesX2GridViewMassAction'),
('EmailInboxesMinimumRequirements', 'EmailInboxesIndex'),
('EmailInboxesMinimumRequirements', 'EmailInboxesSaveTabSettings'),
('EmailInboxesMinimumRequirements', 'EmailInboxesViewMessage'),
('EmailInboxesMinimumRequirements', 'EmailInboxesViewAttachment'),
('EmailInboxesMinimumRequirements', 'EmailInboxesDownloadAttachment'),
('EmailInboxesMinimumRequirements', 'EmailInboxesAssociateAttachment'),
('EmailInboxesMinimumRequirements', 'EmailInboxesMarkMessages'),
('EmailInboxesMinimumRequirements', 'EmailInboxesConfigureMyInbox'),
('EmailInboxesAdminAccess', 'EmailInboxesAjaxGetModelAutocomplete'),
('EmailInboxesAdminAccess', 'EmailInboxesGetX2ModelInput'),
('DefaultRole','EmailInboxesReadOnlyAccess');
