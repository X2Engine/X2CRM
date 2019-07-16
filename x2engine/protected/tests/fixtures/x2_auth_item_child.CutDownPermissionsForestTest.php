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




return array(
'0' => array (
  'parent' => 'AccountsAdminAccess',
  'child' => 'AccountsAccountsReport',
),
'1' => array (
  'parent' => 'AccountsUpdateAccess',
  'child' => 'AccountsAddUser',
),
'2' => array (
  'parent' => 'AccountsUpdatePrivate',
  'child' => 'AccountsAddUser',
),
'3' => array (
  'parent' => 'AccountsAdminAccess',
  'child' => 'AccountsAdmin',
),
'4' => array (
  'parent' => 'administrator',
  'child' => 'AccountsAdminAccess',
),
'5' => array (
  'parent' => 'TestRole',
  'child' => 'AccountsAdminAccess',
),
'6' => array (
  'parent' => 'AccountsIndex',
  'child' => 'AccountsAjaxGetModelAutocomplete',
),
'7' => array (
  'parent' => 'AccountsPrivateUpdateAccess',
  'child' => 'AccountsBasicAccess',
),
'8' => array (
  'parent' => 'AccountsUpdateAccess',
  'child' => 'AccountsBasicAccess',
),
'9' => array (
  'parent' => 'AccountsBasicAccess',
  'child' => 'AccountsCreate',
),
'10' => array (
  'parent' => 'AccountsDeletePrivate',
  'child' => 'AccountsDelete',
),
'11' => array (
  'parent' => 'AccountsFullAccess',
  'child' => 'AccountsDelete',
),
'12' => array (
  'parent' => 'AccountsFullAccess',
  'child' => 'AccountsDeleteNote',
),
'13' => array (
  'parent' => 'AccountsPrivateFullAccess',
  'child' => 'AccountsDeleteNote',
),
'14' => array (
  'parent' => 'AccountsPrivateFullAccess',
  'child' => 'AccountsDeletePrivate',
),
'15' => array (
  'parent' => 'AccountsAdminAccess',
  'child' => 'AccountsExportAccountsReport',
),
'16' => array (
  'parent' => 'AccountsAdminAccess',
  'child' => 'AccountsFullAccess',
),
'17' => array (
  'parent' => 'AccountsMinimumRequirements',
  'child' => 'AccountsGetItems',
),
'18' => array (
  'parent' => 'AccountsUpdateAccess',
  'child' => 'AccountsGetX2ModelInput',
),
'19' => array (
  'parent' => 'AccountsMinimumRequirements',
  'child' => 'AccountsIndex',
),
'20' => array (
  'parent' => 'AccountsPrivateReadOnlyAccess',
  'child' => 'AccountsMinimumRequirements',
),
'21' => array (
  'parent' => 'AccountsReadOnlyAccess',
  'child' => 'AccountsMinimumRequirements',
),
'22' => array (
  'parent' => 'AccountsPrivateFullAccess',
  'child' => 'AccountsPrivateUpdateAccess',
),
'23' => array (
  'parent' => 'AccountsMinimumRequirements',
  'child' => 'AccountsQtip',
),
'24' => array (
  'parent' => 'AccountsBasicAccess',
  'child' => 'AccountsReadOnlyAccess',
),
'25' => array (
  'parent' => 'AccountsUpdateAccess',
  'child' => 'AccountsRemoveUser',
),
'26' => array (
  'parent' => 'AccountsUpdatePrivate',
  'child' => 'AccountsRemoveUser',
),
'27' => array (
  'parent' => 'AccountsMinimumRequirements',
  'child' => 'AccountsSearch',
),
'28' => array (
  'parent' => 'AccountsReadOnlyAccess',
  'child' => 'AccountsShareAccount',
),
'29' => array (
  'parent' => 'AccountsViewPrivate',
  'child' => 'AccountsShareAccount',
),
'30' => array (
  'parent' => 'AccountsUpdateAccess',
  'child' => 'AccountsUpdate',
),
'31' => array (
  'parent' => 'AccountsUpdatePrivate',
  'child' => 'AccountsUpdate',
),
'32' => array (
  'parent' => 'AccountsFullAccess',
  'child' => 'AccountsUpdateAccess',
),
'33' => array (
  'parent' => 'DefaultRole',
  'child' => 'AccountsUpdateAccess',
),
'34' => array (
  'parent' => 'AccountsPrivateUpdateAccess',
  'child' => 'AccountsUpdatePrivate',
),
'35' => array (
  'parent' => 'AccountsReadOnlyAccess',
  'child' => 'AccountsView',
),
'36' => array (
  'parent' => 'AccountsViewPrivate',
  'child' => 'AccountsView',
),
'37' => array (
  'parent' => 'AccountsPrivateReadOnlyAccess',
  'child' => 'AccountsViewPrivate',
),
'38' => array (
  'parent' => 'ActionsAdminAccess',
  'child' => 'ActionsAdmin',
),
'39' => array (
  'parent' => 'administrator',
  'child' => 'ActionsAdminAccess',
),
'40' => array (
  'parent' => 'ActionsIndex',
  'child' => 'ActionsAjaxGetModelAutocomplete',
),
'41' => array (
  'parent' => 'ActionsPrivateUpdateAccess',
  'child' => 'ActionsBasicAccess',
),
'42' => array (
  'parent' => 'ActionsUpdateAccess',
  'child' => 'ActionsBasicAccess',
),
'43' => array (
  'parent' => 'TestRole',
  'child' => 'ActionsBasicAccess',
),
'44' => array (
  'parent' => 'ActionsUpdateAccess',
  'child' => 'ActionsComplete',
),
'45' => array (
  'parent' => 'ActionsUpdatePrivate',
  'child' => 'ActionsComplete',
),
'46' => array (
  'parent' => 'ActionsReadOnlyAccess',
  'child' => 'ActionsCompleteSelected',
),
'47' => array (
  'parent' => 'ActionsViewPrivate',
  'child' => 'ActionsCompleteSelected',
),
'48' => array (
  'parent' => 'ActionsBasicAccess',
  'child' => 'ActionsCreate',
),
'49' => array (
  'parent' => 'ActionsDeletePrivate',
  'child' => 'ActionsDelete',
),
'50' => array (
  'parent' => 'ActionsFullAccess',
  'child' => 'ActionsDelete',
),
'51' => array (
  'parent' => 'ActionsFullAccess',
  'child' => 'ActionsDeleteNote',
),
'52' => array (
  'parent' => 'ActionsPrivateFullAccess',
  'child' => 'ActionsDeleteNote',
),
'53' => array (
  'parent' => 'ActionsPrivateFullAccess',
  'child' => 'ActionsDeletePrivate',
),
'54' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'ActionsEmailOpened',
),
'55' => array (
  'parent' => 'ActionsAdminAccess',
  'child' => 'ActionsFullAccess',
),
'56' => array (
  'parent' => 'ActionsIndex',
  'child' => 'ActionsGetItems',
),
'57' => array (
  'parent' => 'ActionsMinimumRequirements',
  'child' => 'ActionsGetTerms',
),
'58' => array (
  'parent' => 'ActionsUpdateAccess',
  'child' => 'ActionsGetX2ModelInput',
),
'59' => array (
  'parent' => 'ActionsMinimumRequirements',
  'child' => 'ActionsIndex',
),
'60' => array (
  'parent' => 'ActionsMinimumRequirements',
  'child' => 'ActionsInvalid',
),
'61' => array (
  'parent' => 'ActionsPrivateReadOnlyAccess',
  'child' => 'ActionsMinimumRequirements',
),
'62' => array (
  'parent' => 'ActionsReadOnlyAccess',
  'child' => 'ActionsMinimumRequirements',
),
'63' => array (
  'parent' => 'ActionsMinimumRequirements',
  'child' => 'ActionsParseType',
),
'64' => array (
  'parent' => 'DefaultRole',
  'child' => 'ActionsPrivateFullAccess',
),
'65' => array (
  'parent' => 'ActionsPrivateFullAccess',
  'child' => 'ActionsPrivateUpdateAccess',
),
'66' => array (
  'parent' => 'ActionsBasicAccess',
  'child' => 'ActionsPublisherCreate',
),
'67' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'ActionsPublisherCreate',
),
'68' => array (
  'parent' => 'ActionsUpdateAccess',
  'child' => 'ActionsQuickUpdate',
),
'69' => array (
  'parent' => 'ActionsUpdatePrivate',
  'child' => 'ActionsQuickUpdate',
),
'70' => array (
  'parent' => 'ActionsBasicAccess',
  'child' => 'ActionsReadOnlyAccess',
),
'71' => array (
  'parent' => 'ActionsMinimumRequirements',
  'child' => 'ActionsSaveShowActions',
),
'72' => array (
  'parent' => 'ActionsMinimumRequirements',
  'child' => 'ActionsSearch',
),
'73' => array (
  'parent' => 'ActionsReadOnlyAccess',
  'child' => 'ActionsSendReminder',
),
'74' => array (
  'parent' => 'ActionsViewPrivate',
  'child' => 'ActionsSendReminder',
),
'75' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'ActionsSendReminder',
),
'76' => array (
  'parent' => 'ActionsReadOnlyAccess',
  'child' => 'ActionsShareAction',
),
'77' => array (
  'parent' => 'ActionsViewPrivate',
  'child' => 'ActionsShareAction',
),
'78' => array (
  'parent' => 'ActionsPublisherCreate',
  'child' => 'ActionsTimerControl',
),
'79' => array (
  'parent' => 'ActionsUpdateAccess',
  'child' => 'ActionsToggleSticky',
),
'80' => array (
  'parent' => 'ActionsUpdatePrivate',
  'child' => 'ActionsToggleSticky',
),
'81' => array (
  'parent' => 'ActionsReadOnlyAccess',
  'child' => 'ActionsTomorrow',
),
'82' => array (
  'parent' => 'ActionsViewPrivate',
  'child' => 'ActionsTomorrow',
),
'83' => array (
  'parent' => 'ActionsReadOnlyAccess',
  'child' => 'ActionsUncomplete',
),
'84' => array (
  'parent' => 'ActionsViewPrivate',
  'child' => 'ActionsUncomplete',
),
'85' => array (
  'parent' => 'ActionsReadOnlyAccess',
  'child' => 'ActionsUncompleteSelected',
),
'86' => array (
  'parent' => 'ActionsViewPrivate',
  'child' => 'ActionsUncompleteSelected',
),
'87' => array (
  'parent' => 'ActionsUpdateAccess',
  'child' => 'ActionsUpdate',
),
'88' => array (
  'parent' => 'ActionsUpdatePrivate',
  'child' => 'ActionsUpdate',
),
'89' => array (
  'parent' => 'ActionsFullAccess',
  'child' => 'ActionsUpdateAccess',
),
'90' => array (
  'parent' => 'ActionsPrivateUpdateAccess',
  'child' => 'ActionsUpdatePrivate',
),
'91' => array (
  'parent' => 'ActionsReadOnlyAccess',
  'child' => 'ActionsView',
),
'92' => array (
  'parent' => 'ActionsViewPrivate',
  'child' => 'ActionsView',
),
'93' => array (
  'parent' => 'ActionsReadOnlyAccess',
  'child' => 'ActionsViewAction',
),
'94' => array (
  'parent' => 'ActionsViewPrivate',
  'child' => 'ActionsViewAction',
),
'95' => array (
  'parent' => 'ActionsMinimumRequirements',
  'child' => 'ActionsViewAll',
),
'96' => array (
  'parent' => 'ActionsReadOnlyAccess',
  'child' => 'ActionsViewEmail',
),
'97' => array (
  'parent' => 'ActionsViewPrivate',
  'child' => 'ActionsViewEmail',
),
'98' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'ActionsViewEmail',
),
'99' => array (
  'parent' => 'ActionsMinimumRequirements',
  'child' => 'ActionsViewGroup',
),
'100' => array (
  'parent' => 'ActionsPrivateReadOnlyAccess',
  'child' => 'ActionsViewPrivate',
),
'101' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminActivitySettings',
),
'102' => array (
  'parent' => 'LeadRoutingTask',
  'child' => 'AdminAddCriteria',
),
'103' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminApi2Settings',
),
'104' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminAppSettings',
),
'105' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminAuthGraph',
),
'106' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminBackup',
),
'107' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminCalculateMissingTranslations',
),
'108' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminCalculateTranslationRedundancy',
),
'109' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminChangeApplicationName',
),
'110' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminCheckDatabaseBackup',
),
'111' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminCleanUp',
),
'112' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminCleanUpImport',
),
'113' => array (
  'parent' => 'AdminImport',
  'child' => 'AdminCleanUpModelImport',
),
'114' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminClearChangelog',
),
'115' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminClearViewHistory',
),
'116' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminConvertCustomModules',
),
'117' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminCreateFormLayout',
),
'118' => array (
  'parent' => 'X2StudioTask',
  'child' => 'AdminCreateModule',
),
'119' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminCreatePage',
),
'120' => array (
  'parent' => 'FieldsTask',
  'child' => 'AdminCreateUpdateField',
),
'121' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminDelete',
),
'122' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminDeleteCriteria',
),
'123' => array (
  'parent' => 'DropDownsTask',
  'child' => 'AdminDeleteDropdown',
),
'124' => array (
  'parent' => 'FieldsTask',
  'child' => 'AdminDeleteField',
),
'125' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminDeleteFormLayout',
),
'126' => array (
  'parent' => 'X2StudioTask',
  'child' => 'AdminDeleteModule',
),
'127' => array (
  'parent' => 'RoleAccessTask',
  'child' => 'AdminDeleteRole',
),
'128' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminDeleteRouting',
),
'129' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminDeleteTag',
),
'130' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'AdminDownloadData',
),
'131' => array (
  'parent' => 'DropDownsTask',
  'child' => 'AdminDropDownEditor',
),
'132' => array (
  'parent' => 'DropDownsTask',
  'child' => 'AdminEditDropdown',
),
'133' => array (
  'parent' => 'X2StudioTask',
  'child' => 'AdminEditor',
),
'134' => array (
  'parent' => 'RoleAccessTask',
  'child' => 'AdminEditRole',
),
'135' => array (
  'parent' => 'RoleAccessTask',
  'child' => 'AdminEditRoleAccess',
),
'136' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminEmailDropboxSettings',
),
'137' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminEmailSetup',
),
'138' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminEndSession',
),
'139' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminExport',
),
'140' => array (
  'parent' => 'AdminExport',
  'child' => 'AdminExportMapping',
),
'141' => array (
  'parent' => 'DefaultRole',
  'child' => 'AdminExportModelRecords',
),
'142' => array (
  'parent' => 'DefaultRole',
  'child' => 'AdminExportModels',
),
'143' => array (
  'parent' => 'X2StudioTask',
  'child' => 'AdminExportModule',
),
'144' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminFindMissingPermissions',
),
'145' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminFlowDesigner',
),
'146' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminGetAttributes',
),
'147' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminGetDropdown',
),
'148' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminGetFieldData',
),
'149' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminGetFieldType',
),
'150' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminGetRole',
),
'151' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'AdminGetRoutingType',
),
'152' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'AdminGetRoutingType',
),
'153' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminGetWorkflowStages',
),
'154' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminGlobalExport',
),
'155' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminGlobalImport',
),
'156' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminGoogleIntegration',
),
'157' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminImport',
),
'158' => array (
  'parent' => 'AdminImport',
  'child' => 'AdminImportModelRecords',
),
'159' => array (
  'parent' => 'AdminImport',
  'child' => 'AdminImportModels',
),
'160' => array (
  'parent' => 'X2StudioTask',
  'child' => 'AdminImportModule',
),
'161' => array (
  'parent' => 'administrator',
  'child' => 'AdminIndex',
),
'162' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminIndex',
),
'163' => array (
  'parent' => 'TranslationsTask',
  'child' => 'AdminIndex',
),
'164' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminInstallUpdate',
),
'165' => array (
  'parent' => 'admin',
  'child' => 'administrator',
),
'166' => array (
  'parent' => 'SuperTestRole',
  'child' => 'administrator',
),
'167' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminLockApp',
),
'168' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminManageActionPublisherTabs',
),
'169' => array (
  'parent' => 'DropDownsTask',
  'child' => 'AdminManageDropDowns',
),
'170' => array (
  'parent' => 'FieldsTask',
  'child' => 'AdminManageFields',
),
'171' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminManageModules',
),
'172' => array (
  'parent' => 'RoleAccessTask',
  'child' => 'AdminManageRoles',
),
'173' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminManageSessions',
),
'174' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminManageTags',
),
'175' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminPrepareExport',
),
'176' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminPrepareImport',
),
'177' => array (
  'parent' => 'AdminImport',
  'child' => 'AdminPrepareModelImport',
),
'178' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminPublicInfo',
),
'179' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminRegisterModules',
),
'180' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminRemoveField',
),
'181' => array (
  'parent' => 'X2StudioTask',
  'child' => 'AdminRenameModules',
),
'182' => array (
  'parent' => 'RoleAccessTask',
  'child' => 'AdminRoleEditor',
),
'183' => array (
  'parent' => 'RoleAccessTask',
  'child' => 'AdminRoleException',
),
'184' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminRollbackImport',
),
'185' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminRollbackStage',
),
'186' => array (
  'parent' => 'LeadRoutingTask',
  'child' => 'AdminRoundRobinRules',
),
'187' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminSetDefaultTheme',
),
'188' => array (
  'parent' => 'LeadRoutingTask',
  'child' => 'AdminSetLeadRouting',
),
'189' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminSetServiceRouting',
),
'190' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminToggleDefaultLogo',
),
'191' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminToggleModule',
),
'192' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminToggleSession',
),
'193' => array (
  'parent' => 'TranslationsTask',
  'child' => 'AdminTranslationManager',
),
'194' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminUpdater',
),
'195' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminUpdaterSettings',
),
'196' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminUpdateStage',
),
'197' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminUploadLogo',
),
'198' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminUserViewLog',
),
'199' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminValidateField',
),
'200' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminViewChangelog',
),
'201' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminViewLog',
),
'202' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminViewLogs',
),
'203' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'AdminViewPage',
),
'204' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminViewSessionHistory',
),
'205' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminViewSessionLog',
),
'206' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminWorkflowSettings',
),
'207' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'AdminX2CronSettings',
),
'208' => array (
  'parent' => 'administrator',
  'child' => 'authenticated',
),
'209' => array (
  'parent' => 'DefaultRole',
  'child' => 'AuthenticatedSiteFunctionsTask',
),
'210' => array (
  'parent' => 'BugReportsAdminAccess',
  'child' => 'BugReportsAdmin',
),
'211' => array (
  'parent' => 'administrator',
  'child' => 'BugReportsAdminAccess',
),
'212' => array (
  'parent' => 'BugReportsIndex',
  'child' => 'BugReportsAjaxGetModelAutocomplete',
),
'213' => array (
  'parent' => 'BugReportsPrivateUpdateAccess',
  'child' => 'BugReportsBasicAccess',
),
'214' => array (
  'parent' => 'BugReportsUpdateAccess',
  'child' => 'BugReportsBasicAccess',
),
'215' => array (
  'parent' => 'BugReportsBasicAccess',
  'child' => 'BugReportsCreate',
),
'216' => array (
  'parent' => 'BugReportsDeletePrivate',
  'child' => 'BugReportsDelete',
),
'217' => array (
  'parent' => 'BugReportsFullAccess',
  'child' => 'BugReportsDelete',
),
'218' => array (
  'parent' => 'BugReportsMinimumRequirements',
  'child' => 'BugReportsDeleteNote',
),
'219' => array (
  'parent' => 'BugReportsPrivateFullAccess',
  'child' => 'BugReportsDeletePrivate',
),
'220' => array (
  'parent' => 'BugReportsAdminAccess',
  'child' => 'BugReportsFullAccess',
),
'221' => array (
  'parent' => 'BugReportsMinimumRequirements',
  'child' => 'BugReportsGetItems',
),
'222' => array (
  'parent' => 'BugReportsUpdateAccess',
  'child' => 'BugReportsGetX2ModelInput',
),
'223' => array (
  'parent' => 'BugReportsMinimumRequirements',
  'child' => 'BugReportsIndex',
),
'224' => array (
  'parent' => 'BugReportsPrivateReadOnlyAccess',
  'child' => 'BugReportsMinimumRequirements',
),
'225' => array (
  'parent' => 'BugReportsReadOnlyAccess',
  'child' => 'BugReportsMinimumRequirements',
),
'226' => array (
  'parent' => 'TestRole',
  'child' => 'BugReportsPrivateReadOnlyAccess',
),
'227' => array (
  'parent' => 'BugReportsPrivateFullAccess',
  'child' => 'BugReportsPrivateUpdateAccess',
),
'228' => array (
  'parent' => 'BugReportsBasicAccess',
  'child' => 'BugReportsReadOnlyAccess',
),
'229' => array (
  'parent' => 'BugReportsMinimumRequirements',
  'child' => 'BugReportsSearch',
),
'230' => array (
  'parent' => 'BugReportsMinimumRequirements',
  'child' => 'BugReportsStatusFilter',
),
'231' => array (
  'parent' => 'BugReportsUpdateAccess',
  'child' => 'BugReportsUpdate',
),
'232' => array (
  'parent' => 'BugReportsUpdatePrivate',
  'child' => 'BugReportsUpdate',
),
'233' => array (
  'parent' => 'BugReportsFullAccess',
  'child' => 'BugReportsUpdateAccess',
),
'234' => array (
  'parent' => 'DefaultRole',
  'child' => 'BugReportsUpdateAccess',
),
'235' => array (
  'parent' => 'BugReportsPrivateUpdateAccess',
  'child' => 'BugReportsUpdatePrivate',
),
'236' => array (
  'parent' => 'BugReportsReadOnlyAccess',
  'child' => 'BugReportsView',
),
'237' => array (
  'parent' => 'BugReportsViewPrivate',
  'child' => 'BugReportsView',
),
'238' => array (
  'parent' => 'BugReportsPrivateReadOnlyAccess',
  'child' => 'BugReportsViewPrivate',
),
'239' => array (
  'parent' => 'CalendarAdminAccess',
  'child' => 'CalendarAdmin',
),
'240' => array (
  'parent' => 'administrator',
  'child' => 'CalendarAdminAccess',
),
'241' => array (
  'parent' => 'CalendarIndex',
  'child' => 'CalendarAjaxGetModelAutocomplete',
),
'242' => array (
  'parent' => 'CalendarUpdateAccess',
  'child' => 'CalendarBasicAccess',
),
'243' => array (
  'parent' => 'CalendarUpdateAccess',
  'child' => 'CalendarCompleteAction',
),
'244' => array (
  'parent' => 'CalendarBasicAccess',
  'child' => 'CalendarCreate',
),
'245' => array (
  'parent' => 'CalendarFullAccess',
  'child' => 'CalendarDelete',
),
'246' => array (
  'parent' => 'CalendarFullAccess',
  'child' => 'CalendarDeleteAction',
),
'247' => array (
  'parent' => 'CalendarFullAccess',
  'child' => 'CalendarDeleteGoogleEvent',
),
'248' => array (
  'parent' => 'CalendarFullAccess',
  'child' => 'CalendarDeleteNote',
),
'249' => array (
  'parent' => 'CalendarUpdateAccess',
  'child' => 'CalendarEditAction',
),
'250' => array (
  'parent' => 'CalendarUpdateAccess',
  'child' => 'CalendarEditGoogleEvent',
),
'251' => array (
  'parent' => 'CalendarAdminAccess',
  'child' => 'CalendarFullAccess',
),
'252' => array (
  'parent' => 'DefaultRole',
  'child' => 'CalendarFullAccess',
),
'253' => array (
  'parent' => 'TestRole',
  'child' => 'CalendarFullAccess',
),
'254' => array (
  'parent' => 'CalendarMinimumRequirements',
  'child' => 'CalendarIndex',
),
'255' => array (
  'parent' => 'CalendarMinimumRequirements',
  'child' => 'CalendarJsonFeed',
),
'256' => array (
  'parent' => 'CalendarMinimumRequirements',
  'child' => 'CalendarJsonFeedGoogle',
),
'257' => array (
  'parent' => 'CalendarMinimumRequirements',
  'child' => 'CalendarJsonFeedGroup',
),
'258' => array (
  'parent' => 'CalendarMinimumRequirements',
  'child' => 'CalendarJsonFeedShared',
),
'259' => array (
  'parent' => 'CalendarMinimumRequirements',
  'child' => 'CalendarList',
),
'260' => array (
  'parent' => 'CalendarReadOnlyAccess',
  'child' => 'CalendarMinimumRequirements',
),
'261' => array (
  'parent' => 'CalendarUpdateAccess',
  'child' => 'CalendarMoveAction',
),
'262' => array (
  'parent' => 'CalendarUpdateAccess',
  'child' => 'CalendarMoveGoogleEvent',
),
'263' => array (
  'parent' => 'CalendarMinimumRequirements',
  'child' => 'CalendarMyCalendarPermissions',
),
'264' => array (
  'parent' => 'CalendarBasicAccess',
  'child' => 'CalendarReadOnlyAccess',
),
'265' => array (
  'parent' => 'CalendarUpdateAccess',
  'child' => 'CalendarResizeAction',
),
'266' => array (
  'parent' => 'CalendarUpdateAccess',
  'child' => 'CalendarResizeGoogleEvent',
),
'267' => array (
  'parent' => 'CalendarMinimumRequirements',
  'child' => 'CalendarSaveCheckedCalendar',
),
'268' => array (
  'parent' => 'CalendarMinimumRequirements',
  'child' => 'CalendarSaveCheckedCalendarFilter',
),
'269' => array (
  'parent' => 'CalendarBasicAccess',
  'child' => 'CalendarSaveGoogleEvent',
),
'270' => array (
  'parent' => 'CalendarMinimumRequirements',
  'child' => 'CalendarSearch',
),
'271' => array (
  'parent' => 'CalendarBasicAccess',
  'child' => 'CalendarSyncActionsToGoogleCalendar',
),
'272' => array (
  'parent' => 'CalendarMinimumRequirements',
  'child' => 'CalendarTogglePortletVisible',
),
'273' => array (
  'parent' => 'CalendarMinimumRequirements',
  'child' => 'CalendarToggleUserCalendarsVisible',
),
'274' => array (
  'parent' => 'CalendarUpdateAccess',
  'child' => 'CalendarUncompleteAction',
),
'275' => array (
  'parent' => 'CalendarUpdateAccess',
  'child' => 'CalendarUpdate',
),
'276' => array (
  'parent' => 'CalendarFullAccess',
  'child' => 'CalendarUpdateAccess',
),
'277' => array (
  'parent' => 'CalendarAdminAccess',
  'child' => 'CalendarUserCalendarPermissions',
),
'278' => array (
  'parent' => 'CalendarReadOnlyAccess',
  'child' => 'CalendarView',
),
'279' => array (
  'parent' => 'CalendarReadOnlyAccess',
  'child' => 'CalendarViewAction',
),
'280' => array (
  'parent' => 'CalendarReadOnlyAccess',
  'child' => 'CalendarViewGoogleEvent',
),
'281' => array (
  'parent' => 'ChartsAdminAccess',
  'child' => 'ChartsAdmin',
),
'282' => array (
  'parent' => 'administrator',
  'child' => 'ChartsAdminAccess',
),
'283' => array (
  'parent' => 'TestRole',
  'child' => 'ChartsAdminAccess',
),
'284' => array (
  'parent' => 'ChartsIndex',
  'child' => 'ChartsAjaxGetModelAutocomplete',
),
'285' => array (
  'parent' => 'ChartsFullAccess',
  'child' => 'ChartsDeleteNote',
),
'286' => array (
  'parent' => 'ChartsAdminAccess',
  'child' => 'ChartsFullAccess',
),
'287' => array (
  'parent' => 'DefaultRole',
  'child' => 'ChartsFullAccess',
),
'288' => array (
  'parent' => 'ChartsMinimumRequirements',
  'child' => 'ChartsGetFieldData',
),
'289' => array (
  'parent' => 'ChartsMinimumRequirements',
  'child' => 'ChartsIndex',
),
'290' => array (
  'parent' => 'ChartsFullAccess',
  'child' => 'ChartsLeadVolume',
),
'291' => array (
  'parent' => 'ChartsFullAccess',
  'child' => 'ChartsMarketing',
),
'292' => array (
  'parent' => 'ChartsFullAccess',
  'child' => 'ChartsMinimumRequirements',
),
'293' => array (
  'parent' => 'ChartsFullAccess',
  'child' => 'ChartsPipeline',
),
'294' => array (
  'parent' => 'ChartsFullAccess',
  'child' => 'ChartsSales',
),
'295' => array (
  'parent' => 'ChartsMinimumRequirements',
  'child' => 'ChartsSearch',
),
'296' => array (
  'parent' => 'ChartsFullAccess',
  'child' => 'ChartsWorkflow',
),
'297' => array (
  'parent' => 'ContactsBasicAccess',
  'child' => 'ContactsAddToList',
),
'298' => array (
  'parent' => 'ContactsAdminAccess',
  'child' => 'ContactsAdmin',
),
'299' => array (
  'parent' => 'administrator',
  'child' => 'ContactsAdminAccess',
),
'300' => array (
  'parent' => 'ContactsIndex',
  'child' => 'ContactsAjaxGetModelAutocomplete',
),
'301' => array (
  'parent' => 'ContactsPrivateUpdateAccess',
  'child' => 'ContactsBasicAccess',
),
'302' => array (
  'parent' => 'ContactsUpdateAccess',
  'child' => 'ContactsBasicAccess',
),
'303' => array (
  'parent' => 'ContactsAdminAccess',
  'child' => 'ContactsCleanFailedLeads',
),
'304' => array (
  'parent' => 'ContactsAdminAccess',
  'child' => 'ContactsCleanUpImport',
),
'305' => array (
  'parent' => 'ContactsBasicAccess',
  'child' => 'ContactsCreate',
),
'306' => array (
  'parent' => 'ContactsBasicAccess',
  'child' => 'ContactsCreateList',
),
'307' => array (
  'parent' => 'ContactsBasicAccess',
  'child' => 'ContactsCreateListFromSelection',
),
'308' => array (
  'parent' => 'ContactsDeletePrivate',
  'child' => 'ContactsDelete',
),
'309' => array (
  'parent' => 'ContactsFullAccess',
  'child' => 'ContactsDelete',
),
'310' => array (
  'parent' => 'ContactsDeletePrivate',
  'child' => 'ContactsDeleteList',
),
'311' => array (
  'parent' => 'ContactsFullAccess',
  'child' => 'ContactsDeleteList',
),
'312' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsDeleteMap',
),
'313' => array (
  'parent' => 'ContactsFullAccess',
  'child' => 'ContactsDeleteNote',
),
'314' => array (
  'parent' => 'ContactsPrivateFullAccess',
  'child' => 'ContactsDeleteNote',
),
'315' => array (
  'parent' => 'ContactsPrivateFullAccess',
  'child' => 'ContactsDeletePrivate',
),
'316' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsDiscardNew',
),
'317' => array (
  'parent' => 'ContactsAdminAccess',
  'child' => 'ContactsFullAccess',
),
'318' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsGetContacts',
),
'319' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsGetItems',
),
'320' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsGetLists',
),
'321' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsGetTerms',
),
'322' => array (
  'parent' => 'ContactsUpdateAccess',
  'child' => 'ContactsGetX2ModelInput',
),
'323' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsGoogleMaps',
),
'324' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsIgnoreDuplicates',
),
'325' => array (
  'parent' => 'ContactsAdminAccess',
  'child' => 'ContactsImportExcel',
),
'326' => array (
  'parent' => 'ContactsAdminAccess',
  'child' => 'ContactsImportRecords',
),
'327' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsIndex',
),
'328' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsList',
),
'329' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsLists',
),
'330' => array (
  'parent' => 'ContactsPrivateReadOnlyAccess',
  'child' => 'ContactsMinimumRequirements',
),
'331' => array (
  'parent' => 'ContactsReadOnlyAccess',
  'child' => 'ContactsMinimumRequirements',
),
'332' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsMyContacts',
),
'333' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsNewContacts',
),
'334' => array (
  'parent' => 'ContactsAdminAccess',
  'child' => 'ContactsPrepareImport',
),
'335' => array (
  'parent' => 'TestRole',
  'child' => 'ContactsPrivateFullAccess',
),
'336' => array (
  'parent' => 'ContactsPrivateFullAccess',
  'child' => 'ContactsPrivateUpdateAccess',
),
'337' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsQtip',
),
'338' => array (
  'parent' => 'ContactsBasicAccess',
  'child' => 'ContactsQuickContact',
),
'339' => array (
  'parent' => 'ContactsBasicAccess',
  'child' => 'ContactsReadOnlyAccess',
),
'340' => array (
  'parent' => 'ContactsUpdateAccess',
  'child' => 'ContactsRemoveFromList',
),
'341' => array (
  'parent' => 'ContactsUpdatePrivate',
  'child' => 'ContactsRemoveFromList',
),
'342' => array (
  'parent' => 'ContactsReadOnlyAccess',
  'child' => 'ContactsRevisions',
),
'343' => array (
  'parent' => 'ContactsViewPrivate',
  'child' => 'ContactsRevisions',
),
'344' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsSavedMaps',
),
'345' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsSaveMap',
),
'346' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsSearch',
),
'347' => array (
  'parent' => 'ContactsReadOnlyAccess',
  'child' => 'ContactsShareContact',
),
'348' => array (
  'parent' => 'ContactsViewPrivate',
  'child' => 'ContactsShareContact',
),
'349' => array (
  'parent' => 'ContactsReadOnlyAccess',
  'child' => 'ContactsSubscribe',
),
'350' => array (
  'parent' => 'ContactsViewPrivate',
  'child' => 'ContactsSubscribe',
),
'351' => array (
  'parent' => 'ContactsUpdateAccess',
  'child' => 'ContactsSyncAccount',
),
'352' => array (
  'parent' => 'ContactsUpdatePrivate',
  'child' => 'ContactsSyncAccount',
),
'353' => array (
  'parent' => 'ContactsAdminAccess',
  'child' => 'ContactsTrigger',
),
'354' => array (
  'parent' => 'ContactsUpdateAccess',
  'child' => 'ContactsUpdate',
),
'355' => array (
  'parent' => 'ContactsUpdatePrivate',
  'child' => 'ContactsUpdate',
),
'356' => array (
  'parent' => 'ContactsFullAccess',
  'child' => 'ContactsUpdateAccess',
),
'357' => array (
  'parent' => 'DefaultRole',
  'child' => 'ContactsUpdateAccess',
),
'358' => array (
  'parent' => 'ContactsPrivateUpdateAccess',
  'child' => 'ContactsUpdateList',
),
'359' => array (
  'parent' => 'ContactsUpdateAccess',
  'child' => 'ContactsUpdateList',
),
'360' => array (
  'parent' => 'ContactsMinimumRequirements',
  'child' => 'ContactsUpdateLocation',
),
'361' => array (
  'parent' => 'ContactsPrivateUpdateAccess',
  'child' => 'ContactsUpdatePrivate',
),
'362' => array (
  'parent' => 'ContactsReadOnlyAccess',
  'child' => 'ContactsView',
),
'363' => array (
  'parent' => 'ContactsViewPrivate',
  'child' => 'ContactsView',
),
'364' => array (
  'parent' => 'ContactsPrivateReadOnlyAccess',
  'child' => 'ContactsViewPrivate',
),
'365' => array (
  'parent' => 'ContactsReadOnlyAccess',
  'child' => 'ContactsViewRelationships',
),
'366' => array (
  'parent' => 'ContactsViewPrivate',
  'child' => 'ContactsViewRelationships',
),
'367' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'ContactsWeblead',
),
'368' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'ContactsWeblead',
),
'369' => array (
  'parent' => 'GeneralAdminSettingsTask',
  'child' => 'CredentialsAdmin',
),
'370' => array (
  'parent' => 'CredentialsCreateUpdateOwn',
  'child' => 'CredentialsCreateUpdate',
),
'371' => array (
  'parent' => 'CredentialsCreateUpdateSystemwide',
  'child' => 'CredentialsCreateUpdate',
),
'372' => array (
  'parent' => 'DefaultRole',
  'child' => 'CredentialsCreateUpdateOwn',
),
'373' => array (
  'parent' => 'CredentialsAdmin',
  'child' => 'CredentialsCreateUpdateSystemwide',
),
'374' => array (
  'parent' => 'CredentialsDeleteOwn',
  'child' => 'CredentialsDelete',
),
'375' => array (
  'parent' => 'CredentialsDeleteSystemwide',
  'child' => 'CredentialsDelete',
),
'376' => array (
  'parent' => 'DefaultRole',
  'child' => 'CredentialsDeleteOwn',
),
'377' => array (
  'parent' => 'CredentialsAdmin',
  'child' => 'CredentialsDeleteSystemwide',
),
'378' => array (
  'parent' => 'CredentialsSelectNonPrivate',
  'child' => 'CredentialsSelect',
),
'379' => array (
  'parent' => 'CredentialsSelectOwn',
  'child' => 'CredentialsSelect',
),
'380' => array (
  'parent' => 'CredentialsSelectSystemwide',
  'child' => 'CredentialsSelect',
),
'381' => array (
  'parent' => 'CredentialsAdmin',
  'child' => 'CredentialsSelectNonPrivate',
),
'382' => array (
  'parent' => 'DefaultRole',
  'child' => 'CredentialsSelectOwn',
),
'383' => array (
  'parent' => 'DefaultRole',
  'child' => 'CredentialsSelectSystemwide',
),
'384' => array (
  'parent' => 'CredentialsSetDefaultOwn',
  'child' => 'CredentialsSetDefault',
),
'385' => array (
  'parent' => 'CredentialsSetDefaultSystemwide',
  'child' => 'CredentialsSetDefault',
),
'386' => array (
  'parent' => 'CredentialsSelect',
  'child' => 'CredentialsSetDefaultOwn',
),
'387' => array (
  'parent' => 'CredentialsAdmin',
  'child' => 'CredentialsSetDefaultSystemwide',
),
'388' => array (
  'parent' => 'authenticated',
  'child' => 'DefaultRole',
),
'389' => array (
  'parent' => 'DocsAdminAccess',
  'child' => 'DocsAdmin',
),
'390' => array (
  'parent' => 'administrator',
  'child' => 'DocsAdminAccess',
),
'391' => array (
  'parent' => 'DocsMinimumRequirements',
  'child' => 'DocsAjaxCheckEditPermission',
),
'392' => array (
  'parent' => 'DocsIndex',
  'child' => 'DocsAjaxGetModelAutocomplete',
),
'393' => array (
  'parent' => 'DocsMinimumRequirements',
  'child' => 'DocsAutosave',
),
'394' => array (
  'parent' => 'DocsPrivateUpdateAccess',
  'child' => 'DocsBasicAccess',
),
'395' => array (
  'parent' => 'DocsUpdateAccess',
  'child' => 'DocsBasicAccess',
),
'396' => array (
  'parent' => 'DocsUpdatePrivate',
  'child' => 'DocsChangePermissions',
),
'397' => array (
  'parent' => 'DocsBasicAccess',
  'child' => 'DocsCreate',
),
'398' => array (
  'parent' => 'DocsBasicAccess',
  'child' => 'DocsCreateEmail',
),
'399' => array (
  'parent' => 'DocsBasicAccess',
  'child' => 'DocsCreateQuote',
),
'400' => array (
  'parent' => 'DocsDeletePrivate',
  'child' => 'DocsDelete',
),
'401' => array (
  'parent' => 'DocsFullAccess',
  'child' => 'DocsDelete',
),
'402' => array (
  'parent' => 'DocsFullAccess',
  'child' => 'DocsDeleteNote',
),
'403' => array (
  'parent' => 'DocsPrivateFullAccess',
  'child' => 'DocsDeleteNote',
),
'404' => array (
  'parent' => 'DocsPrivateFullAccess',
  'child' => 'DocsDeletePrivate',
),
'405' => array (
  'parent' => 'DocsExportToHtml',
  'child' => 'DocsDownloadExport',
),
'406' => array (
  'parent' => 'DocsReadOnlyAccess',
  'child' => 'DocsExportToHtml',
),
'407' => array (
  'parent' => 'DocsViewPrivate',
  'child' => 'DocsExportToHtml',
),
'408' => array (
  'parent' => 'DocsAdminAccess',
  'child' => 'DocsFullAccess',
),
'409' => array (
  'parent' => 'DocsReadOnlyAccess',
  'child' => 'DocsFullView',
),
'410' => array (
  'parent' => 'DocsViewPrivate',
  'child' => 'DocsFullView',
),
'411' => array (
  'parent' => 'DocsMinimumRequirements',
  'child' => 'DocsGetItem',
),
'412' => array (
  'parent' => 'DocsMinimumRequirements',
  'child' => 'DocsGetItems',
),
'413' => array (
  'parent' => 'DocsUpdateAccess',
  'child' => 'DocsGetX2ModelInput',
),
'414' => array (
  'parent' => 'DocsMinimumRequirements',
  'child' => 'DocsIndex',
),
'415' => array (
  'parent' => 'DocsPrivateReadOnlyAccess',
  'child' => 'DocsMinimumRequirements',
),
'416' => array (
  'parent' => 'DocsReadOnlyAccess',
  'child' => 'DocsMinimumRequirements',
),
'417' => array (
  'parent' => 'TestRole',
  'child' => 'DocsPrivateReadOnlyAccess',
),
'418' => array (
  'parent' => 'DocsPrivateFullAccess',
  'child' => 'DocsPrivateUpdateAccess',
),
'419' => array (
  'parent' => 'DocsBasicAccess',
  'child' => 'DocsReadOnlyAccess',
),
'420' => array (
  'parent' => 'DocsMinimumRequirements',
  'child' => 'DocsSearch',
),
'421' => array (
  'parent' => 'DocsUpdateAccess',
  'child' => 'DocsUpdate',
),
'422' => array (
  'parent' => 'DocsUpdatePrivate',
  'child' => 'DocsUpdate',
),
'423' => array (
  'parent' => 'DefaultRole',
  'child' => 'DocsUpdateAccess',
),
'424' => array (
  'parent' => 'DocsFullAccess',
  'child' => 'DocsUpdateAccess',
),
'425' => array (
  'parent' => 'DefaultRole',
  'child' => 'DocsUpdatePrivate',
),
'426' => array (
  'parent' => 'DocsPrivateUpdateAccess',
  'child' => 'DocsUpdatePrivate',
),
'427' => array (
  'parent' => 'DocsReadOnlyAccess',
  'child' => 'DocsView',
),
'428' => array (
  'parent' => 'DocsViewPrivate',
  'child' => 'DocsView',
),
'429' => array (
  'parent' => 'DocsPrivateReadOnlyAccess',
  'child' => 'DocsViewPrivate',
),
'430' => array (
  'parent' => 'X2StudioTask',
  'child' => 'DropDownsTask',
),
'431' => array (
  'parent' => 'X2StudioTask',
  'child' => 'FieldsTask',
),
'432' => array (
  'parent' => 'administrator',
  'child' => 'GeneralAdminSettingsTask',
),
'433' => array (
  'parent' => 'GroupsAdminAccess',
  'child' => 'GroupsAdmin',
),
'434' => array (
  'parent' => 'administrator',
  'child' => 'GroupsAdminAccess',
),
'435' => array (
  'parent' => 'GroupsIndex',
  'child' => 'GroupsAjaxGetModelAutocomplete',
),
'436' => array (
  'parent' => 'GroupsUpdateAccess',
  'child' => 'GroupsBasicAccess',
),
'437' => array (
  'parent' => 'GroupsBasicAccess',
  'child' => 'GroupsCreate',
),
'438' => array (
  'parent' => 'GroupsFullAccess',
  'child' => 'GroupsDelete',
),
'439' => array (
  'parent' => 'GroupsFullAccess',
  'child' => 'GroupsDeleteNote',
),
'440' => array (
  'parent' => 'GroupsAdminAccess',
  'child' => 'GroupsFullAccess',
),
'441' => array (
  'parent' => 'GroupsMinimumRequirements',
  'child' => 'GroupsGetGroups',
),
'442' => array (
  'parent' => 'GroupsIndex',
  'child' => 'GroupsGetItems',
),
'443' => array (
  'parent' => 'GroupsMinimumRequirements',
  'child' => 'GroupsIndex',
),
'444' => array (
  'parent' => 'GroupsReadOnlyAccess',
  'child' => 'GroupsMinimumRequirements',
),
'445' => array (
  'parent' => 'DefaultRole',
  'child' => 'GroupsReadOnlyAccess',
),
'446' => array (
  'parent' => 'GroupsBasicAccess',
  'child' => 'GroupsReadOnlyAccess',
),
'447' => array (
  'parent' => 'GroupsMinimumRequirements',
  'child' => 'GroupsSearch',
),
'448' => array (
  'parent' => 'GroupsUpdateAccess',
  'child' => 'GroupsUpdate',
),
'449' => array (
  'parent' => 'GroupsFullAccess',
  'child' => 'GroupsUpdateAccess',
),
'450' => array (
  'parent' => 'TestRole',
  'child' => 'GroupsUpdateAccess',
),
'451' => array (
  'parent' => 'GroupsReadOnlyAccess',
  'child' => 'GroupsView',
),
'452' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'GuestSiteFunctionsTask',
),
'453' => array (
  'parent' => 'guest',
  'child' => 'GuestSiteFunctionsTask',
),
'454' => array (
  'parent' => 'administrator',
  'child' => 'LeadRoutingTask',
),
'455' => array (
  'parent' => 'MarketingAdminAccess',
  'child' => 'MarketingAdmin',
),
'456' => array (
  'parent' => 'administrator',
  'child' => 'MarketingAdminAccess',
),
'457' => array (
  'parent' => 'TestRole',
  'child' => 'MarketingAdminAccess',
),
'458' => array (
  'parent' => 'MarketingIndex',
  'child' => 'MarketingAjaxGetModelAutocomplete',
),
'459' => array (
  'parent' => 'MarketingAdminAccess',
  'child' => 'MarketingAnonContactDelete',
),
'460' => array (
  'parent' => 'MarketingAdminAccess',
  'child' => 'MarketingAnonContactIndex',
),
'461' => array (
  'parent' => 'MarketingAdminAccess',
  'child' => 'MarketingAnonContactView',
),
'462' => array (
  'parent' => 'MarketingUpdateAccess',
  'child' => 'MarketingBasicAccess',
),
'463' => array (
  'parent' => 'MarketingPrivateBasicAccess',
  'child' => 'MarketingBasicPrivate',
),
'464' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'MarketingClick',
),
'465' => array (
  'parent' => 'MarketingUpdateAccess',
  'child' => 'MarketingComplete',
),
'466' => array (
  'parent' => 'MarketingUpdatePrivate',
  'child' => 'MarketingComplete',
),
'467' => array (
  'parent' => 'MarketingBasicAccess',
  'child' => 'MarketingCreate',
),
'468' => array (
  'parent' => 'MarketingPrivateBasicAccess',
  'child' => 'MarketingCreate',
),
'469' => array (
  'parent' => 'MarketingBasicAccess',
  'child' => 'MarketingCreateFromTag',
),
'470' => array (
  'parent' => 'MarketingPrivateBasicAccess',
  'child' => 'MarketingCreateFromTag',
),
'471' => array (
  'parent' => 'MarketingDeletePrivate',
  'child' => 'MarketingDelete',
),
'472' => array (
  'parent' => 'MarketingFullAccess',
  'child' => 'MarketingDelete',
),
'473' => array (
  'parent' => 'MarketingDeletePrivate',
  'child' => 'MarketingDeleteNote',
),
'474' => array (
  'parent' => 'MarketingFullAccess',
  'child' => 'MarketingDeleteNote',
),
'475' => array (
  'parent' => 'MarketingPrivateFullAccess',
  'child' => 'MarketingDeletePrivate',
),
'476' => array (
  'parent' => 'MarketingAdminAccess',
  'child' => 'MarketingFingerprintIndex',
),
'477' => array (
  'parent' => 'MarketingAdminAccess',
  'child' => 'MarketingFullAccess',
),
'478' => array (
  'parent' => 'MarketingBasicAccess',
  'child' => 'MarketingGetCampaignChartData',
),
'479' => array (
  'parent' => 'MarketingMinimumRequirements',
  'child' => 'MarketingGetItems',
),
'480' => array (
  'parent' => 'MarketingUpdateAccess',
  'child' => 'MarketingGetX2ModelInput',
),
'481' => array (
  'parent' => 'MarketingMinimumRequirements',
  'child' => 'MarketingIndex',
),
'482' => array (
  'parent' => 'MarketingBasicAccess',
  'child' => 'MarketingLaunch',
),
'483' => array (
  'parent' => 'MarketingBasicPrivate',
  'child' => 'MarketingLaunch',
),
'484' => array (
  'parent' => 'MarketingMinimumRequirements',
  'child' => 'MarketingMailIndividual',
),
'485' => array (
  'parent' => 'MarketingPrivateReadOnlyAccess',
  'child' => 'MarketingMinimumRequirements',
),
'486' => array (
  'parent' => 'MarketingReadOnlyAccess',
  'child' => 'MarketingMinimumRequirements',
),
'487' => array (
  'parent' => 'MarketingPrivateUpdateAccess',
  'child' => 'MarketingPrivateBasicAccess',
),
'488' => array (
  'parent' => 'DefaultRole',
  'child' => 'MarketingPrivateUpdateAccess',
),
'489' => array (
  'parent' => 'MarketingPrivateFullAccess',
  'child' => 'MarketingPrivateUpdateAccess',
),
'490' => array (
  'parent' => 'MarketingBasicAccess',
  'child' => 'MarketingReadOnlyAccess',
),
'491' => array (
  'parent' => 'MarketingPrivateBasicAccess',
  'child' => 'MarketingReadOnlyAccess',
),
'492' => array (
  'parent' => 'MarketingFullAccess',
  'child' => 'MarketingRemoveWebLeadFormCustomHtml',
),
'493' => array (
  'parent' => 'MarketingFullAccess',
  'child' => 'MarketingSaveWebLeadFormCustomHtml',
),
'494' => array (
  'parent' => 'MarketingMinimumRequirements',
  'child' => 'MarketingSearch',
),
'495' => array (
  'parent' => 'MarketingBasicAccess',
  'child' => 'MarketingToggle',
),
'496' => array (
  'parent' => 'MarketingBasicPrivate',
  'child' => 'MarketingToggle',
),
'497' => array (
  'parent' => 'MarketingUpdateAccess',
  'child' => 'MarketingUpdate',
),
'498' => array (
  'parent' => 'MarketingUpdatePrivate',
  'child' => 'MarketingUpdate',
),
'499' => array (
  'parent' => 'MarketingFullAccess',
  'child' => 'MarketingUpdateAccess',
),
'500' => array (
  'parent' => 'MarketingPrivateUpdateAccess',
  'child' => 'MarketingUpdatePrivate',
),
'501' => array (
  'parent' => 'MarketingReadOnlyAccess',
  'child' => 'MarketingView',
),
'502' => array (
  'parent' => 'MarketingViewPrivate',
  'child' => 'MarketingView',
),
'503' => array (
  'parent' => 'MarketingReadOnlyAccess',
  'child' => 'MarketingViewContent',
),
'504' => array (
  'parent' => 'MarketingViewPrivate',
  'child' => 'MarketingViewContent',
),
'505' => array (
  'parent' => 'MarketingPrivateReadOnlyAccess',
  'child' => 'MarketingViewPrivate',
),
'506' => array (
  'parent' => 'LeadRoutingTask',
  'child' => 'MarketingWebLeadForm',
),
'507' => array (
  'parent' => 'MarketingAdminAccess',
  'child' => 'MarketingWebleadForm',
),
'508' => array (
  'parent' => 'MarketingAdminAccess',
  'child' => 'MarketingWebTracker',
),
'509' => array (
  'parent' => 'MediaAdminAccess',
  'child' => 'MediaAdmin',
),
'510' => array (
  'parent' => 'administrator',
  'child' => 'MediaAdminAccess',
),
'511' => array (
  'parent' => 'MediaIndex',
  'child' => 'MediaAjaxGetModelAutocomplete',
),
'512' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'MediaAjaxUpload',
),
'513' => array (
  'parent' => 'MediaUpdateAccess',
  'child' => 'MediaBasicAccess',
),
'514' => array (
  'parent' => 'TestRole',
  'child' => 'MediaBasicAccess',
),
'515' => array (
  'parent' => 'MediaBasicAccess',
  'child' => 'MediaCreate',
),
'516' => array (
  'parent' => 'MediaFullAccess',
  'child' => 'MediaDelete',
),
'517' => array (
  'parent' => 'MediaFullAccess',
  'child' => 'MediaDeleteNote',
),
'518' => array (
  'parent' => 'MediaReadOnlyAccess',
  'child' => 'MediaDownload',
),
'519' => array (
  'parent' => 'MediaAdminAccess',
  'child' => 'MediaFullAccess',
),
'520' => array (
  'parent' => 'MediaIndex',
  'child' => 'MediaGetItems',
),
'521' => array (
  'parent' => 'MediaUpdateAccess',
  'child' => 'MediaGetX2ModelInput',
),
'522' => array (
  'parent' => 'MediaMinimumRequirements',
  'child' => 'MediaIndex',
),
'523' => array (
  'parent' => 'MediaReadOnlyAccess',
  'child' => 'MediaMinimumRequirements',
),
'524' => array (
  'parent' => 'MediaReadOnlyAccess',
  'child' => 'MediaQtip',
),
'525' => array (
  'parent' => 'MediaBasicAccess',
  'child' => 'MediaReadOnlyAccess',
),
'526' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'MediaRecursiveDriveFiles',
),
'527' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'MediaRefreshDriveCache',
),
'528' => array (
  'parent' => 'MediaMinimumRequirements',
  'child' => 'MediaSearch',
),
'529' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'MediaToggleUserMediaVisible',
),
'530' => array (
  'parent' => 'MediaUpdateAccess',
  'child' => 'MediaUpdate',
),
'531' => array (
  'parent' => 'DefaultRole',
  'child' => 'MediaUpdateAccess',
),
'532' => array (
  'parent' => 'MediaFullAccess',
  'child' => 'MediaUpdateAccess',
),
'533' => array (
  'parent' => 'MediaBasicAccess',
  'child' => 'MediaUpload',
),
'534' => array (
  'parent' => 'MediaReadOnlyAccess',
  'child' => 'MediaView',
),
'535' => array (
  'parent' => 'OpportunitiesUpdateAccess',
  'child' => 'OpportunitiesAddContact',
),
'536' => array (
  'parent' => 'OpportunitiesUpdatePrivate',
  'child' => 'OpportunitiesAddContact',
),
'537' => array (
  'parent' => 'OpportunitiesUpdateAccess',
  'child' => 'OpportunitiesAddUser',
),
'538' => array (
  'parent' => 'OpportunitiesUpdatePrivate',
  'child' => 'OpportunitiesAddUser',
),
'539' => array (
  'parent' => 'OpportunitiesAdminAccess',
  'child' => 'OpportunitiesAdmin',
),
'540' => array (
  'parent' => 'administrator',
  'child' => 'OpportunitiesAdminAccess',
),
'541' => array (
  'parent' => 'OpportunitiesIndex',
  'child' => 'OpportunitiesAjaxGetModelAutocomplete',
),
'542' => array (
  'parent' => 'OpportunitiesPrivateUpdateAccess',
  'child' => 'OpportunitiesBasicAccess',
),
'543' => array (
  'parent' => 'OpportunitiesUpdateAccess',
  'child' => 'OpportunitiesBasicAccess',
),
'544' => array (
  'parent' => 'OpportunitiesBasicAccess',
  'child' => 'OpportunitiesCreate',
),
'545' => array (
  'parent' => 'OpportunitiesDeletePrivate',
  'child' => 'OpportunitiesDelete',
),
'546' => array (
  'parent' => 'OpportunitiesFullAccess',
  'child' => 'OpportunitiesDelete',
),
'547' => array (
  'parent' => 'OpportunitiesDeletePrivate',
  'child' => 'OpportunitiesDeleteNote',
),
'548' => array (
  'parent' => 'OpportunitiesFullAccess',
  'child' => 'OpportunitiesDeleteNote',
),
'549' => array (
  'parent' => 'OpportunitiesPrivateFullAccess',
  'child' => 'OpportunitiesDeletePrivate',
),
'550' => array (
  'parent' => 'OpportunitiesAdminAccess',
  'child' => 'OpportunitiesFullAccess',
),
'551' => array (
  'parent' => 'TestRole',
  'child' => 'OpportunitiesFullAccess',
),
'552' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'OpportunitiesGetItems',
),
'553' => array (
  'parent' => 'OpportunitiesMinimumRequirements',
  'child' => 'OpportunitiesGetTerms',
),
'554' => array (
  'parent' => 'OpportunitiesUpdateAccess',
  'child' => 'OpportunitiesGetX2ModelInput',
),
'555' => array (
  'parent' => 'OpportunitiesMinimumRequirements',
  'child' => 'OpportunitiesIndex',
),
'556' => array (
  'parent' => 'OpportunitiesPrivateReadOnlyAccess',
  'child' => 'OpportunitiesMinimumRequirements',
),
'557' => array (
  'parent' => 'OpportunitiesReadOnlyAccess',
  'child' => 'OpportunitiesMinimumRequirements',
),
'558' => array (
  'parent' => 'OpportunitiesPrivateFullAccess',
  'child' => 'OpportunitiesPrivateUpdateAccess',
),
'559' => array (
  'parent' => 'OpportunitiesMinimumRequirements',
  'child' => 'OpportunitiesQtip',
),
'560' => array (
  'parent' => 'OpportunitiesBasicAccess',
  'child' => 'OpportunitiesReadOnlyAccess',
),
'561' => array (
  'parent' => 'OpportunitiesUpdateAccess',
  'child' => 'OpportunitiesRemoveContact',
),
'562' => array (
  'parent' => 'OpportunitiesUpdatePrivate',
  'child' => 'OpportunitiesRemoveContact',
),
'563' => array (
  'parent' => 'OpportunitiesUpdateAccess',
  'child' => 'OpportunitiesRemoveUser',
),
'564' => array (
  'parent' => 'OpportunitiesUpdatePrivate',
  'child' => 'OpportunitiesRemoveUser',
),
'565' => array (
  'parent' => 'OpportunitiesMinimumRequirements',
  'child' => 'OpportunitiesSearch',
),
'566' => array (
  'parent' => 'OpportunitiesReadOnlyAccess',
  'child' => 'OpportunitiesShareOpportunity',
),
'567' => array (
  'parent' => 'OpportunitiesViewPrivate',
  'child' => 'OpportunitiesShareOpportunity',
),
'568' => array (
  'parent' => 'OpportunitiesUpdateAccess',
  'child' => 'OpportunitiesUpdate',
),
'569' => array (
  'parent' => 'OpportunitiesUpdatePrivate',
  'child' => 'OpportunitiesUpdate',
),
'570' => array (
  'parent' => 'DefaultRole',
  'child' => 'OpportunitiesUpdateAccess',
),
'571' => array (
  'parent' => 'OpportunitiesFullAccess',
  'child' => 'OpportunitiesUpdateAccess',
),
'572' => array (
  'parent' => 'OpportunitiesPrivateUpdateAccess',
  'child' => 'OpportunitiesUpdatePrivate',
),
'573' => array (
  'parent' => 'OpportunitiesReadOnlyAccess',
  'child' => 'OpportunitiesView',
),
'574' => array (
  'parent' => 'OpportunitiesViewPrivate',
  'child' => 'OpportunitiesView',
),
'575' => array (
  'parent' => 'OpportunitiesPrivateReadOnlyAccess',
  'child' => 'OpportunitiesViewPrivate',
),
'576' => array (
  'parent' => 'ProductsAdminAccess',
  'child' => 'ProductsAdmin',
),
'577' => array (
  'parent' => 'administrator',
  'child' => 'ProductsAdminAccess',
),
'578' => array (
  'parent' => 'ProductsIndex',
  'child' => 'ProductsAjaxGetModelAutocomplete',
),
'579' => array (
  'parent' => 'ProductsPrivateUpdateAccess',
  'child' => 'ProductsBasicAccess',
),
'580' => array (
  'parent' => 'ProductsUpdateAccess',
  'child' => 'ProductsBasicAccess',
),
'581' => array (
  'parent' => 'ProductsBasicAccess',
  'child' => 'ProductsCreate',
),
'582' => array (
  'parent' => 'ProductsDeletePrivate',
  'child' => 'ProductsDelete',
),
'583' => array (
  'parent' => 'ProductsFullAccess',
  'child' => 'ProductsDelete',
),
'584' => array (
  'parent' => 'ProductsDeletePrivate',
  'child' => 'ProductsDeleteNote',
),
'585' => array (
  'parent' => 'ProductsFullAccess',
  'child' => 'ProductsDeleteNote',
),
'586' => array (
  'parent' => 'ProductsPrivateFullAccess',
  'child' => 'ProductsDeletePrivate',
),
'587' => array (
  'parent' => 'ProductsAdminAccess',
  'child' => 'ProductsFullAccess',
),
'588' => array (
  'parent' => 'ProductsMinimumRequirements',
  'child' => 'ProductsGetItems',
),
'589' => array (
  'parent' => 'ProductsUpdateAccess',
  'child' => 'ProductsGetX2ModelInput',
),
'590' => array (
  'parent' => 'ProductsMinimumRequirements',
  'child' => 'ProductsIndex',
),
'591' => array (
  'parent' => 'ProductsPrivateReadOnlyAccess',
  'child' => 'ProductsMinimumRequirements',
),
'592' => array (
  'parent' => 'ProductsReadOnlyAccess',
  'child' => 'ProductsMinimumRequirements',
),
'593' => array (
  'parent' => 'TestRole',
  'child' => 'ProductsPrivateFullAccess',
),
'594' => array (
  'parent' => 'ProductsPrivateFullAccess',
  'child' => 'ProductsPrivateUpdateAccess',
),
'595' => array (
  'parent' => 'ProductsBasicAccess',
  'child' => 'ProductsReadOnlyAccess',
),
'596' => array (
  'parent' => 'ProductsMinimumRequirements',
  'child' => 'ProductsSearch',
),
'597' => array (
  'parent' => 'ProductsUpdateAccess',
  'child' => 'ProductsUpdate',
),
'598' => array (
  'parent' => 'ProductsUpdatePrivate',
  'child' => 'ProductsUpdate',
),
'599' => array (
  'parent' => 'DefaultRole',
  'child' => 'ProductsUpdateAccess',
),
'600' => array (
  'parent' => 'ProductsFullAccess',
  'child' => 'ProductsUpdateAccess',
),
'601' => array (
  'parent' => 'ProductsPrivateUpdateAccess',
  'child' => 'ProductsUpdatePrivate',
),
'602' => array (
  'parent' => 'ProductsReadOnlyAccess',
  'child' => 'ProductsView',
),
'603' => array (
  'parent' => 'ProductsViewPrivate',
  'child' => 'ProductsView',
),
'604' => array (
  'parent' => 'ProductsPrivateReadOnlyAccess',
  'child' => 'ProductsViewPrivate',
),
'605' => array (
  'parent' => 'QuotesAdminAccess',
  'child' => 'QuotesAdmin',
),
'606' => array (
  'parent' => 'administrator',
  'child' => 'QuotesAdminAccess',
),
'607' => array (
  'parent' => 'QuotesIndex',
  'child' => 'QuotesAjaxGetModelAutocomplete',
),
'608' => array (
  'parent' => 'QuotesPrivateUpdateAccess',
  'child' => 'QuotesBasicAccess',
),
'609' => array (
  'parent' => 'QuotesUpdateAccess',
  'child' => 'QuotesBasicAccess',
),
'610' => array (
  'parent' => 'QuotesUpdateAccess',
  'child' => 'QuotesConvertToInvoice',
),
'611' => array (
  'parent' => 'QuotesUpdatePrivate',
  'child' => 'QuotesConvertToInvoice',
),
'612' => array (
  'parent' => 'QuotesBasicAccess',
  'child' => 'QuotesCreate',
),
'613' => array (
  'parent' => 'QuotesDeletePrivate',
  'child' => 'QuotesDelete',
),
'614' => array (
  'parent' => 'QuotesFullAccess',
  'child' => 'QuotesDelete',
),
'615' => array (
  'parent' => 'QuotesDeletePrivate',
  'child' => 'QuotesDeleteNote',
),
'616' => array (
  'parent' => 'QuotesFullAccess',
  'child' => 'QuotesDeleteNote',
),
'617' => array (
  'parent' => 'QuotesPrivateFullAccess',
  'child' => 'QuotesDeletePrivate',
),
'618' => array (
  'parent' => 'QuotesUpdateAccess',
  'child' => 'QuotesDeleteProduct',
),
'619' => array (
  'parent' => 'QuotesUpdatePrivate',
  'child' => 'QuotesDeleteProduct',
),
'620' => array (
  'parent' => 'QuotesAdminAccess',
  'child' => 'QuotesFullAccess',
),
'621' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'QuotesGetItems',
),
'622' => array (
  'parent' => 'QuotesMinimumRequirements',
  'child' => 'QuotesGetTerms',
),
'623' => array (
  'parent' => 'QuotesUpdateAccess',
  'child' => 'QuotesGetX2ModelInput',
),
'624' => array (
  'parent' => 'QuotesMinimumRequirements',
  'child' => 'QuotesIndex',
),
'625' => array (
  'parent' => 'QuotesMinimumRequirements',
  'child' => 'QuotesIndexInvoice',
),
'626' => array (
  'parent' => 'QuotesPrivateReadOnlyAccess',
  'child' => 'QuotesMinimumRequirements',
),
'627' => array (
  'parent' => 'QuotesReadOnlyAccess',
  'child' => 'QuotesMinimumRequirements',
),
'628' => array (
  'parent' => 'QuotesReadOnlyAccess',
  'child' => 'QuotesPrint',
),
'629' => array (
  'parent' => 'QuotesViewPrivate',
  'child' => 'QuotesPrint',
),
'630' => array (
  'parent' => 'TestRole',
  'child' => 'QuotesPrivateReadOnlyAccess',
),
'631' => array (
  'parent' => 'QuotesPrivateFullAccess',
  'child' => 'QuotesPrivateUpdateAccess',
),
'632' => array (
  'parent' => 'QuotesBasicAccess',
  'child' => 'QuotesQuickCreate',
),
'633' => array (
  'parent' => 'QuotesDeletePrivate',
  'child' => 'QuotesQuickDelete',
),
'634' => array (
  'parent' => 'QuotesFullAccess',
  'child' => 'QuotesQuickDelete',
),
'635' => array (
  'parent' => 'QuotesUpdateAccess',
  'child' => 'QuotesQuickUpdate',
),
'636' => array (
  'parent' => 'QuotesUpdatePrivate',
  'child' => 'QuotesQuickUpdate',
),
'637' => array (
  'parent' => 'QuotesBasicAccess',
  'child' => 'QuotesReadOnlyAccess',
),
'638' => array (
  'parent' => 'QuotesUpdateAccess',
  'child' => 'QuotesRemoveUser',
),
'639' => array (
  'parent' => 'QuotesUpdatePrivate',
  'child' => 'QuotesRemoveUser',
),
'640' => array (
  'parent' => 'QuotesMinimumRequirements',
  'child' => 'QuotesSearch',
),
'641' => array (
  'parent' => 'QuotesReadOnlyAccess',
  'child' => 'QuotesShareQuote',
),
'642' => array (
  'parent' => 'QuotesViewPrivate',
  'child' => 'QuotesShareQuote',
),
'643' => array (
  'parent' => 'QuotesUpdateAccess',
  'child' => 'QuotesUpdate',
),
'644' => array (
  'parent' => 'QuotesUpdatePrivate',
  'child' => 'QuotesUpdate',
),
'645' => array (
  'parent' => 'DefaultRole',
  'child' => 'QuotesUpdateAccess',
),
'646' => array (
  'parent' => 'QuotesFullAccess',
  'child' => 'QuotesUpdateAccess',
),
'647' => array (
  'parent' => 'QuotesPrivateUpdateAccess',
  'child' => 'QuotesUpdatePrivate',
),
'648' => array (
  'parent' => 'QuotesReadOnlyAccess',
  'child' => 'QuotesView',
),
'649' => array (
  'parent' => 'QuotesViewPrivate',
  'child' => 'QuotesView',
),
'650' => array (
  'parent' => 'QuotesReadOnlyAccess',
  'child' => 'QuotesViewInline',
),
'651' => array (
  'parent' => 'QuotesViewPrivate',
  'child' => 'QuotesViewInline',
),
'652' => array (
  'parent' => 'QuotesPrivateReadOnlyAccess',
  'child' => 'QuotesViewPrivate',
),
'653' => array (
  'parent' => 'ReportsFullAccess',
  'child' => 'ReportsActivityReport',
),
'654' => array (
  'parent' => 'ReportsAdminAccess',
  'child' => 'ReportsAdmin',
),
'655' => array (
  'parent' => 'administrator',
  'child' => 'ReportsAdminAccess',
),
'656' => array (
  'parent' => 'ReportsIndex',
  'child' => 'ReportsAjaxGetModelAutocomplete',
),
'657' => array (
  'parent' => 'ReportsFullAccess',
  'child' => 'ReportsDealReport',
),
'658' => array (
  'parent' => 'ReportsFullAccess',
  'child' => 'ReportsDelete',
),
'659' => array (
  'parent' => 'ReportsFullAccess',
  'child' => 'ReportsDeleteNote',
),
'660' => array (
  'parent' => 'ReportsAdminAccess',
  'child' => 'ReportsFullAccess',
),
'661' => array (
  'parent' => 'TestRole',
  'child' => 'ReportsFullAccess',
),
'662' => array (
  'parent' => 'ReportsMinimumRequirements',
  'child' => 'ReportsGetOptions',
),
'663' => array (
  'parent' => 'ReportsFullAccess',
  'child' => 'ReportsGridReport',
),
'664' => array (
  'parent' => 'ReportsMinimumRequirements',
  'child' => 'ReportsIndex',
),
'665' => array (
  'parent' => 'ReportsFullAccess',
  'child' => 'ReportsLeadPerformance',
),
'666' => array (
  'parent' => 'ReportsFullAccess',
  'child' => 'ReportsMinimumRequirements',
),
'667' => array (
  'parent' => 'ReportsFullAccess',
  'child' => 'ReportsPrintReport',
),
'668' => array (
  'parent' => 'ReportsFullAccess',
  'child' => 'ReportsSavedReports',
),
'669' => array (
  'parent' => 'ReportsFullAccess',
  'child' => 'ReportsSaveReport',
),
'670' => array (
  'parent' => 'ReportsFullAccess',
  'child' => 'ReportsSaveTempImage',
),
'671' => array (
  'parent' => 'ReportsMinimumRequirements',
  'child' => 'ReportsSearch',
),
'672' => array (
  'parent' => 'ReportsFullAccess',
  'child' => 'ReportsWorkflow',
),
'673' => array (
  'parent' => 'administrator',
  'child' => 'RoleAccessTask',
),
'674' => array (
  'parent' => 'ServicesAdminAccess',
  'child' => 'ServicesAdmin',
),
'675' => array (
  'parent' => 'administrator',
  'child' => 'ServicesAdminAccess',
),
'676' => array (
  'parent' => 'ServicesIndex',
  'child' => 'ServicesAjaxGetModelAutocomplete',
),
'677' => array (
  'parent' => 'ServicesPrivateUpdateAccess',
  'child' => 'ServicesBasicAccess',
),
'678' => array (
  'parent' => 'ServicesUpdateAccess',
  'child' => 'ServicesBasicAccess',
),
'679' => array (
  'parent' => 'ServicesBasicAccess',
  'child' => 'ServicesCreate',
),
'680' => array (
  'parent' => 'ServicesAdminAccess',
  'child' => 'ServicesCreateWebForm',
),
'681' => array (
  'parent' => 'ServicesDeletePrivate',
  'child' => 'ServicesDelete',
),
'682' => array (
  'parent' => 'ServicesFullAccess',
  'child' => 'ServicesDelete',
),
'683' => array (
  'parent' => 'ServicesFullAccess',
  'child' => 'ServicesDeleteNote',
),
'684' => array (
  'parent' => 'ServicesPrivateFullAccess',
  'child' => 'ServicesDeleteNote',
),
'685' => array (
  'parent' => 'ServicesPrivateFullAccess',
  'child' => 'ServicesDeletePrivate',
),
'686' => array (
  'parent' => 'ServicesAdminAccess',
  'child' => 'ServicesExportServiceReport',
),
'687' => array (
  'parent' => 'ServicesAdminAccess',
  'child' => 'ServicesFullAccess',
),
'688' => array (
  'parent' => 'ServicesMinimumRequirements',
  'child' => 'ServicesGetItems',
),
'689' => array (
  'parent' => 'ServicesUpdateAccess',
  'child' => 'ServicesGetX2ModelInput',
),
'690' => array (
  'parent' => 'ServicesMinimumRequirements',
  'child' => 'ServicesIndex',
),
'691' => array (
  'parent' => 'ServicesPrivateReadOnlyAccess',
  'child' => 'ServicesMinimumRequirements',
),
'692' => array (
  'parent' => 'ServicesReadOnlyAccess',
  'child' => 'ServicesMinimumRequirements',
),
'693' => array (
  'parent' => 'ServicesPrivateFullAccess',
  'child' => 'ServicesPrivateUpdateAccess',
),
'694' => array (
  'parent' => 'ServicesBasicAccess',
  'child' => 'ServicesReadOnlyAccess',
),
'695' => array (
  'parent' => 'ServicesMinimumRequirements',
  'child' => 'ServicesSearch',
),
'696' => array (
  'parent' => 'ServicesAdminAccess',
  'child' => 'ServicesServicesReport',
),
'697' => array (
  'parent' => 'ServicesMinimumRequirements',
  'child' => 'ServicesStatusFilter',
),
'698' => array (
  'parent' => 'ServicesUpdateAccess',
  'child' => 'ServicesUpdate',
),
'699' => array (
  'parent' => 'ServicesUpdatePrivate',
  'child' => 'ServicesUpdate',
),
'700' => array (
  'parent' => 'DefaultRole',
  'child' => 'ServicesUpdateAccess',
),
'701' => array (
  'parent' => 'ServicesFullAccess',
  'child' => 'ServicesUpdateAccess',
),
'702' => array (
  'parent' => 'TestRole',
  'child' => 'ServicesUpdateAccess',
),
'703' => array (
  'parent' => 'ServicesPrivateUpdateAccess',
  'child' => 'ServicesUpdatePrivate',
),
'704' => array (
  'parent' => 'ServicesReadOnlyAccess',
  'child' => 'ServicesView',
),
'705' => array (
  'parent' => 'ServicesViewPrivate',
  'child' => 'ServicesView',
),
'706' => array (
  'parent' => 'ServicesPrivateReadOnlyAccess',
  'child' => 'ServicesViewPrivate',
),
'707' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'ServicesWebForm',
),
'708' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'ServicesWebForm',
),
'709' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'SiteIndex',
),
'710' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'SiteLogin',
),
'711' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'SiteLogout',
),
'712' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'SiteToggleVisibility',
),
'713' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'SiteWhatsNew',
),
'714' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioAjaxGetModelAutocomplete',
),
'715' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioDeleteAllTriggerLogs',
),
'716' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioDeleteAllTriggerLogsForAllFlows',
),
'717' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioDeleteFlow',
),
'718' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioDeleteNote',
),
'719' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioDeleteTriggerLog',
),
'720' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioExportFlow',
),
'721' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioFlowDesigner',
),
'722' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioFlowIndex',
),
'723' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioGetFields',
),
'724' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioGetParams',
),
'725' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioImportFlow',
),
'726' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioSearch',
),
'727' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioTest',
),
'728' => array (
  'parent' => 'X2StudioTask',
  'child' => 'StudioTriggerLogs',
),
'729' => array (
  'parent' => 'administrator',
  'child' => 'TranslationsTask',
),
'730' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'UsersAddTopContact',
),
'731' => array (
  'parent' => 'UsersMinimumRequirements',
  'child' => 'UsersAdmin',
),
'732' => array (
  'parent' => 'administrator',
  'child' => 'UsersAdminAccess',
),
'733' => array (
  'parent' => 'UsersIndex',
  'child' => 'UsersAjaxGetModelAutocomplete',
),
'734' => array (
  'parent' => 'UsersUpdateAccess',
  'child' => 'UsersBasicAccess',
),
'735' => array (
  'parent' => 'UsersBasicAccess',
  'child' => 'UsersCreate',
),
'736' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'UsersCreateAccount',
),
'737' => array (
  'parent' => 'UsersFullAccess',
  'child' => 'UsersDelete',
),
'738' => array (
  'parent' => 'UsersFullAccess',
  'child' => 'UsersDeleteNote',
),
'739' => array (
  'parent' => 'UsersAdminAccess',
  'child' => 'UsersDeleteTemporary',
),
'740' => array (
  'parent' => 'UsersAdminAccess',
  'child' => 'UsersFullAccess',
),
'741' => array (
  'parent' => 'UsersMinimumRequirements',
  'child' => 'UsersIndex',
),
'742' => array (
  'parent' => 'UsersAdminAccess',
  'child' => 'UsersInviteUsers',
),
'743' => array (
  'parent' => 'UsersReadOnlyAccess',
  'child' => 'UsersMinimumRequirements',
),
'744' => array (
  'parent' => 'TestRole',
  'child' => 'UsersReadOnlyAccess',
),
'745' => array (
  'parent' => 'UsersBasicAccess',
  'child' => 'UsersReadOnlyAccess',
),
'746' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'UsersRemoveTopContact',
),
'747' => array (
  'parent' => 'UsersMinimumRequirements',
  'child' => 'UsersSearch',
),
'748' => array (
  'parent' => 'UsersUpdateAccess',
  'child' => 'UsersUpdate',
),
'749' => array (
  'parent' => 'UsersFullAccess',
  'child' => 'UsersUpdateAccess',
),
'750' => array (
  'parent' => 'UsersReadOnlyAccess',
  'child' => 'UsersView',
),
'751' => array (
  'parent' => 'WeblistIndex',
  'child' => 'WeblistAjaxGetModelAutocomplete',
),
'752' => array (
  'parent' => 'MarketingFullAccess',
  'child' => 'WeblistCreate',
),
'753' => array (
  'parent' => 'MarketingFullAccess',
  'child' => 'WeblistDelete',
),
'754' => array (
  'parent' => 'MarketingMinimumRequirements',
  'child' => 'WeblistIndex',
),
'755' => array (
  'parent' => 'MarketingFullAccess',
  'child' => 'WeblistUpdate',
),
'756' => array (
  'parent' => 'MarketingPrivateReadOnlyAccess',
  'child' => 'WeblistView',
),
'757' => array (
  'parent' => 'MarketingReadOnlyAccess',
  'child' => 'WeblistView',
),
'758' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'WeblistWeblist',
),
'759' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'WeblistWeblist',
),
'760' => array (
  'parent' => 'WorkflowAdminAccess',
  'child' => 'WorkflowAdmin',
),
'761' => array (
  'parent' => 'administrator',
  'child' => 'WorkflowAdminAccess',
),
'762' => array (
  'parent' => 'WorkflowReadOnlyAccess',
  'child' => 'WorkflowAjaxAddADeal',
),
'763' => array (
  'parent' => 'WorkflowIndex',
  'child' => 'WorkflowAjaxGetModelAutocomplete',
),
'764' => array (
  'parent' => 'WorkflowUpdateAccess',
  'child' => 'WorkflowBasicAccess',
),
'765' => array (
  'parent' => 'WorkflowReadOnlyAccess',
  'child' => 'WorkflowChangeUI',
),
'766' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'WorkflowCompleteStage',
),
'767' => array (
  'parent' => 'WorkflowBasicAccess',
  'child' => 'WorkflowCreate',
),
'768' => array (
  'parent' => 'WorkflowFullAccess',
  'child' => 'WorkflowDelete',
),
'769' => array (
  'parent' => 'WorkflowFullAccess',
  'child' => 'WorkflowDeleteNote',
),
'770' => array (
  'parent' => 'WorkflowAdminAccess',
  'child' => 'WorkflowFullAccess',
),
'771' => array (
  'parent' => 'WorkflowReadOnlyAccess',
  'child' => 'WorkflowGetItems',
),
'772' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'WorkflowGetStageDetails',
),
'773' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'WorkflowGetStageMembers',
),
'774' => array (
  'parent' => 'WorkflowReadOnlyAccess',
  'child' => 'WorkflowGetStageNameItems',
),
'775' => array (
  'parent' => 'WorkflowReadOnlyAccess',
  'child' => 'WorkflowGetStageNames',
),
'776' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'WorkflowGetStages',
),
'777' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'WorkflowGetStageValue',
),
'778' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'WorkflowGetWorkflow',
),
'779' => array (
  'parent' => 'WorkflowMinimumRequirements',
  'child' => 'WorkflowIndex',
),
'780' => array (
  'parent' => 'WorkflowReadOnlyAccess',
  'child' => 'WorkflowMinimumRequirements',
),
'781' => array (
  'parent' => 'WorkflowReadOnlyAccess',
  'child' => 'WorkflowMoveFromStageAToStageB',
),
'782' => array (
  'parent' => 'DefaultRole',
  'child' => 'WorkflowReadOnlyAccess',
),
'783' => array (
  'parent' => 'TestRole',
  'child' => 'WorkflowReadOnlyAccess',
),
'784' => array (
  'parent' => 'WorkflowBasicAccess',
  'child' => 'WorkflowReadOnlyAccess',
),
'785' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'WorkflowRevertStage',
),
'786' => array (
  'parent' => 'WorkflowMinimumRequirements',
  'child' => 'WorkflowSearch',
),
'787' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'WorkflowStartStage',
),
'788' => array (
  'parent' => 'WorkflowUpdateAccess',
  'child' => 'WorkflowUpdate',
),
'789' => array (
  'parent' => 'WorkflowFullAccess',
  'child' => 'WorkflowUpdateAccess',
),
'790' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'WorkflowUpdateStageDetails',
),
'791' => array (
  'parent' => 'WorkflowReadOnlyAccess',
  'child' => 'WorkflowView',
),
'792' => array (
  'parent' => 'AuthenticatedSiteFunctionsTask',
  'child' => 'WorkflowViewStage',
),
'793' => array (
  'parent' => 'X2LeadsAdminAccess',
  'child' => 'X2LeadsAdmin',
),
'794' => array (
  'parent' => 'administrator',
  'child' => 'X2LeadsAdminAccess',
),
'795' => array (
  'parent' => 'X2LeadsIndex',
  'child' => 'X2LeadsAjaxGetModelAutocomplete',
),
'796' => array (
  'parent' => 'X2LeadsPrivateUpdateAccess',
  'child' => 'X2LeadsBasicAccess',
),
'797' => array (
  'parent' => 'X2LeadsUpdateAccess',
  'child' => 'X2LeadsBasicAccess',
),
'798' => array (
  'parent' => 'X2LeadsUpdateAccess',
  'child' => 'X2LeadsConvertLead',
),
'799' => array (
  'parent' => 'X2LeadsBasicAccess',
  'child' => 'X2LeadsCreate',
),
'800' => array (
  'parent' => 'X2LeadsDeletePrivate',
  'child' => 'X2LeadsDelete',
),
'801' => array (
  'parent' => 'X2LeadsFullAccess',
  'child' => 'X2LeadsDelete',
),
'802' => array (
  'parent' => 'X2LeadsDeletePrivate',
  'child' => 'X2LeadsDeleteNote',
),
'803' => array (
  'parent' => 'X2LeadsFullAccess',
  'child' => 'X2LeadsDeleteNote',
),
'804' => array (
  'parent' => 'X2LeadsPrivateFullAccess',
  'child' => 'X2LeadsDeletePrivate',
),
'805' => array (
  'parent' => 'X2LeadsAdminAccess',
  'child' => 'X2LeadsFullAccess',
),
'806' => array (
  'parent' => 'GuestSiteFunctionsTask',
  'child' => 'X2LeadsGetItems',
),
'807' => array (
  'parent' => 'X2LeadsMinimumRequirements',
  'child' => 'X2LeadsGetTerms',
),
'808' => array (
  'parent' => 'X2LeadsUpdateAccess',
  'child' => 'X2LeadsGetX2ModelInput',
),
'809' => array (
  'parent' => 'X2LeadsMinimumRequirements',
  'child' => 'X2LeadsIndex',
),
'810' => array (
  'parent' => 'X2LeadsPrivateReadOnlyAccess',
  'child' => 'X2LeadsMinimumRequirements',
),
'811' => array (
  'parent' => 'X2LeadsReadOnlyAccess',
  'child' => 'X2LeadsMinimumRequirements',
),
'812' => array (
  'parent' => 'TestRole',
  'child' => 'X2LeadsPrivateUpdateAccess',
),
'813' => array (
  'parent' => 'X2LeadsPrivateFullAccess',
  'child' => 'X2LeadsPrivateUpdateAccess',
),
'814' => array (
  'parent' => 'X2LeadsBasicAccess',
  'child' => 'X2LeadsReadOnlyAccess',
),
'815' => array (
  'parent' => 'X2LeadsMinimumRequirements',
  'child' => 'X2LeadsSearch',
),
'816' => array (
  'parent' => 'X2LeadsUpdateAccess',
  'child' => 'X2LeadsUpdate',
),
'817' => array (
  'parent' => 'X2LeadsUpdatePrivate',
  'child' => 'X2LeadsUpdate',
),
'818' => array (
  'parent' => 'DefaultRole',
  'child' => 'X2LeadsUpdateAccess',
),
'819' => array (
  'parent' => 'X2LeadsFullAccess',
  'child' => 'X2LeadsUpdateAccess',
),
'820' => array (
  'parent' => 'X2LeadsPrivateUpdateAccess',
  'child' => 'X2LeadsUpdatePrivate',
),
'821' => array (
  'parent' => 'X2LeadsReadOnlyAccess',
  'child' => 'X2LeadsView',
),
'822' => array (
  'parent' => 'X2LeadsViewPrivate',
  'child' => 'X2LeadsView',
),
'823' => array (
  'parent' => 'X2LeadsPrivateReadOnlyAccess',
  'child' => 'X2LeadsViewPrivate',
),
'824' => array (
  'parent' => 'administrator',
  'child' => 'X2StudioTask',
),
);
?>
