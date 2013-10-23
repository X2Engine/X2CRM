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

/**
 * @file protected/components/views/requirements.php
 * @author Demitri Morgan <demitri@x2engine.com>
 *
 * Multi-role requirements-check script. Can be included as part of another page,
 * run as its own standalone script, or used to return requirements check data
 * as an arrya.
 */

/////////////////
// SET GLOBALS //
/////////////////
$document = '<html><header><title>X2CRM System Requirements Check</title>{headerContent}</head><body><div style="width: 680px; border:1px solid #DDD; margin: 25px auto 25px auto; padding: 20px;font-family:sans-serif;">{bodyContent}</div></body></html>';
$totalFailure = array(
	"<h1>This server definitely, most certainly cannot run X2CRM.</h1><p>Not even the system requirements checker script itself could run properly on this server. It encountered the following {scenario}:</p>\n<pre style=\"overflow-x:auto;margin:5px;padding:5px;border:1px red dashed;\">\n",
	"\n</pre>"
);
$mode = php_sapi_name() == 'cli' ? 'cli' : 'web';
$responding = false;
if(!isset($thisFile))
	$thisFile = __FILE__;
if(!isset($standalone))
	$standalone = realpath($thisFile) === realpath(__FILE__);
$returnArray = (isset($returnArray)?$returnArray:false) || $mode == 'cli';
if(!$standalone){
	// Check being called/included inside another script
	$document = '{bodyContent}';
}


///////////////////////////////////////////////
// LAST-DITCH EFFORT COMPATIBILITY FUNCTIONS //
///////////////////////////////////////////////
// If any errors are encountered in the actual requirements check script itself
// due to missing/disabled functions on the server itself, these functions will
// print an appropriate message for the occasion.

function RIP(){
	global $standalone;
	if($standalone){
        die();
	}
}

// Error handler.
function handleReqError($no, $st, $fi = Null, $ln = Null){
	global $document, $totalFailure, $responding, $standalone;
    $fatal = $no === E_ERROR;
    if($no === E_ERROR){ // Ignore warnings...
        $responding = true;
        echo strtr($document, array(
            '{headerContent}' => '',
            '{bodyContent}' => str_replace('{scenario}', 'error', $totalFailure[0]."Error [$no]: $st $fi L$ln".$totalFailure[1])
        ));
        RIP();
    }
}

// Exception handler.
function handleReqException($e){
    global $document, $totalFailure, $responding, $standalone;
    $responding = true;
    $message = 'Exception: "'.$e->getMessage().'" in '.$e->getFile().' L'.$e->getLine()."\n";

    foreach($e->getTrace() as $stackLevel){
        $message .= $stackLevel['file'].' L'.$stackLevel['line'].' ';
        if($stackLevel['class'] != ''){
            $message .= $stackLevel['class'];
            $message .= '->';
        }
        $message .= $stackLevel['function'];
        $message .= "();\n";
    }
    $message = str_replace('{scenario}', 'uncaught exception', $totalFailure[0].$message.$totalFailure[1]);
    echo strtr($document, array(
        '{headerContent}' => '',
        '{bodyContent}' => $message
    ));

    RIP();
}

// Shutdown function (for fatal errors, i.e. call to undefined function)
function reqShutdown(){
    global $document, $totalFailure, $responding, $standalone;
    $error = error_get_last();
    if($error != null && !$responding){
        $errno = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr = $error["message"];
        $errtype = ($errno == E_PARSE ? 'parse' : 'fatal').' error';
        $message = "PHP $errtype [$errno]: $errstr in $errfile L$errline";
        $message = str_replace('{scenario}', $errtype, $totalFailure[0].$message.$totalFailure[1]);
        echo strtr($document, array(
            '{headerContent}' => '',
            '{bodyContent}' => $message
        ));
    }
}

// Throws an exception when encountering an error for easier handling.
function exceptionForError($no, $st, $fi = Null, $ln = Null){
	throw new Exception("Error [$no]: $st $fi L$ln");
}

if(!$returnArray){
    set_error_handler('handleReqError');
    set_exception_handler('handleReqException');
    register_shutdown_function('reqShutdown');
}
//////////////////////////////
// X2CRM Requirements Check //
//////////////////////////////
// "Cannot {scenario} X2CRM"
if(!isset($scenario))
	$scenario = 'install';

// Get PHP info
ob_start();
phpinfo();
$pi = ob_get_contents();
preg_match('%(<style[^>]*>.*</style>)%ms',$pi,$phpInfoStyle);
preg_match('%<body>(.*)</body>%ms',$pi,$phpInfoContent);
ob_end_clean();
if(count($phpInfoStyle))
	$phpInfoStyle = $phpInfoStyle[1];
else
	$phpInfoStyle = '';
if(count($phpInfoContent))
	$phpInfoContent = $phpInfoContent[1];
else
	$phpInfoStyle = '';

// Declare the function since the script is not being used from within the installer
if(!function_exists('installer_t')){
    function installer_t($msg){
		return $msg;
	}

}
if(!function_exists('installer_tr')) {
    function installer_tr($msg,$params = array()){
        return strtr($msg,$params);
    }
}

/**
 * Test the consistency of the $_SERVER global.
 *
 * This function, based on the similarly-named function of the Yii requirements
 * check, validates several essential elements of $_SERVER
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @parameter string $thisFile
 * @return string
 */
function checkServerVar($thisFile = null){
	$vars = array('HTTP_HOST', 'SERVER_NAME', 'SERVER_PORT', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'PHP_SELF', 'HTTP_ACCEPT', 'HTTP_USER_AGENT');
	$missing = array();
	foreach($vars as $var){
		if(!isset($_SERVER[$var]))
			$missing[] = $var;
	}
	if(!empty($missing))
		return installer_tr('$_SERVER does not have {vars}.', array('{vars}' => implode(', ', $missing)));
	if(empty($thisFile))
		$thisFile = __FILE__;

	if(realpath($_SERVER["SCRIPT_FILENAME"]) !== realpath($thisFile))
		return installer_t('$_SERVER["SCRIPT_FILENAME"] must be the same as the entry script file path.');

	if(!isset($_SERVER["REQUEST_URI"]) && isset($_SERVER["QUERY_STRING"]))
		return installer_t('Either $_SERVER["REQUEST_URI"] or $_SERVER["QUERY_STRING"] must exist.');

	if(!isset($_SERVER["PATH_INFO"]) && strpos($_SERVER["PHP_SELF"], $_SERVER["SCRIPT_NAME"]) !== 0)
		return installer_t('Unable to determine URL path info. Please make sure $_SERVER["PATH_INFO"] (or $_SERVER["PHP_SELF"] and $_SERVER["SCRIPT_NAME"]) contains proper value.');

	return '';
}

$canInstall = True;
$curl = true; //
$tryAccess = true; // Attempt to access the internet from the web server.
$reqMessages = array_fill_keys(array(1, 2, 3), array()); // Severity levels
$requirements = array_fill_keys(array('functions','classes','classConflict','extensions','environment'),array());
$rbm = installer_t("required but missing");

//////////////////////////////////////////////
// TOP PRIORITY: BIG IMPORTANT REQUIREMENTS //
//////////////////////////////////////////////
// Check for a mismatch in directory ownership. Skip this step on Windows
// and systems where posix functions are unavailable; in such cases there's no
// reliable way to get the UID of the actual running process.
$requirements['environment']['filesystem_ownership'] = 1;
$uid = array_fill_keys(array('{id_own}', '{id_run}'), null);
$uid['{id_own}'] = fileowner(realpath(dirname(__FILE__)));
if(function_exists('posix_geteuid')){
	$uid['{id_run}'] = posix_geteuid();
	if($uid['{id_own}'] !== $uid['{id_run}']){
		$reqMessages[3][] = strtr(installer_t("PHP is running with user ID={id_run}, but this directory is owned by the system user with ID={id_own}."), $uid);
        $requirements['environment']['filesystem_ownership'] = 0;
	}
}

$requirements['environment']['filesystem_permissions'] = 1;
// Check that the directory is writable. Print an error message one way or another.
if(!is_writable(dirname(__FILE__))){
	$reqMessages[3][] = installer_t("This directory is not writable by PHP processes run by the webserver.");
    $requirements['environment']['filesystem_permissions'] = 0;
}
if(!is_writable(__FILE__)) {
	$reqMessages[3][] = installer_t("Permissions and/or ownership of uploaded files do not permit PHP processes run by the webserver to write files.");
    $requirements['environment']['filesystem_permissions'] = 0;
}


// Check that the directive open_basedir is not arbitrarily set to some restricted 
// jail directory off in god knows where
$requirements['environment']['open_basedir'] = 1;
$basedir = trim(ini_get('open_basedir'));
$cwd = dirname(__FILE__);
if(!empty($basedir)){
    $allowCwd = 0;
    $basedirs = explode(PATH_SEPARATOR,$basedir);
    foreach($basedirs as $dir){
        if(strpos($cwd,$dir) !== false){
            $allowCwd = 1;
            break;
        }
    }
    if(!$allowCwd) {
    	$reqMessages[3][] = installer_t('The base directory configuration directive is set, and it does not include the current working directory.');
        $requirements['environment']['open_basedir'] = 0;
    }
}

// Check PHP version
$requirements['environment']['php_version'] = 1;
if(!version_compare(PHP_VERSION, "5.3.0", ">=")){
	$reqMessages[3][] = installer_t("Your server's PHP version").': '.PHP_VERSION.'; '.installer_t("version 5.3 or later is required");
    $requirements['environment']['php_version'] = 0;
}
// Check $_SERVER variable meets requirements of Yii
$requirements['environment']['php_server_superglobal'] = 0;
if($mode == 'web'){
    if(($message = checkServerVar($thisFile)) !== ''){
        $reqMessages[3][] = installer_t($message);
    }else{
        $requirements['environment']['php_server_superglobal'] = 1;
    }
}

// Check for existence of Reflection class
$requirements['extensions']['pcre'] = 0;
$requirements['environment']['pcre_version'] = 0;
if(!($requirements['classes']['Reflection']=class_exists('Reflection', false))){
	$reqMessages[3][] = '<a href="http://php.net/manual/class.reflectionclass.php">PHP reflection class</a>: '.$rbm;
}else if($requirements['extensions']['pcre']=extension_loaded("pcre")){
	// Check PCRE library version
	$pcreReflector = new ReflectionExtension("pcre");
	ob_start();
	$pcreReflector->info();
	$pcreInfo = ob_get_clean();
	$matches = array();
	preg_match("/([\d\.]+) \d{4,}-\d{1,2}-\d{1,2}/", $pcreInfo, $matches);
	$thisVer = $matches[1];
	$reqVer = '7.4';
	if(!($requirements['environment']['pcre_version'] = version_compare($thisVer, $reqVer) >= 0)){
		$reqMessages[3][] = strtr(installer_t("The version of the PCRE library included in this build of PHP is {thisVer}, but {reqVer} or later is required."), array('{thisVer}' => $thisVer, '{reqVer}' => $reqVer));
	}
}else{
	$reqMessages[3][] = '<a href="http://www.php.net/manual/book.pcre.php">PCRE extension</a>: '.$rbm;
}
// Check for SPL extension
if(!($requirements['extensions']['SPL']=extension_loaded("SPL"))){

	$reqMessages[3][] = '<a href="http://www.php.net/manual/book.spl.php">SPL</a>: '.$rbm;
}
// Check for MySQL connecter
if(!($requirements['extensions']['pdo_mysql']=extension_loaded('pdo_mysql'))){
	$reqMessages[3][] = '<a href="http://www.php.net/manual/ref.pdo-mysql.php">PDO MySQL extension</a>: '.$rbm;
}
// Check for CType extension
if(!($requirements['extensions']['ctype']=extension_loaded('ctype'))){
	$reqMessages[3][] = '<a href="http://www.php.net/manual/book.ctype.php">CType extension</a>: '.$rbm;
}
// Check for multibyte-string extension
if(!($requirements['extensions']['mbstring']=extension_loaded('mbstring'))){
	$reqMessages[3][] = '<a href="http://www.php.net/manual/book.mbstring.php">Multibyte string extension</a>: '.$rbm;
}
// Check for JSON extension:
if(!($requirements['extensions']['json']=extension_loaded('json'))){
	$reqMessages[3][] = '<a href="http://www.php.net/manual/function.json-decode.php">json extension</a>: '.$rbm;
}
// Check for hash:
if(!($requirements['extensions']['hash']=extension_loaded('hash'))){
	$reqMessages[3][] = '<a href="http://www.php.net/manual/book.hash.php">HASH Message Digest Framework</a>: '.$rbm;
} else {
	$algosRequired = array('sha512');
	$algosAvail = hash_algos();
	$algosNotAvail = array_diff($algosRequired,$algosAvail);
	if(!empty($algosNotAvail))
		$reqMessages[3][] = installer_t('Some hashing algorithms required for software updates are missing on this server:').' '.implode(', ',$algosNotAvail);
}


// Miscellaneous functions:
$requiredFunctions = array(
	'mb_regex_encoding',
	'getcwd',
	'chmod',
);
$missingFunctions = array();
foreach($requiredFunctions as $function)
	if(!($requirements['functions'][$function]=function_exists($function)))
		$missingFunctions[] = $function;
if(count($missingFunctions))
	$reqMessages[3][] = installer_t('The following required PHP function(s) is/are missing or disabled: ').implode(', ',$missingFunctions);
// Check for the permissions to run chmod on files owned by the web server:
if(!in_array('chmod', $missingFunctions)) {
	set_error_handler('exceptionForError');
	try{
		// Attempt to change the permissions of the current file and then change them back again
		$fp = fileperms(__FILE__); // original permissions
		chmod(__FILE__,octdec(100700));
		chmod(__FILE__,$fp);
        $requirements['environment']['chmod'] = 1;
	}catch (Exception $e){
		$reqMessages[3][] = installer_t('PHP scripts are not permitted to run the function "chmod".');
        $requirements['environment']['chmod'] = 0;
	}
	restore_error_handler();
}

// Check for class conflicts:
if($standalone) {
    $classes = array("","AccountCampaignAction","Accounts","AccountsController","AccountsModule","AccountsReportAction","ActionCompleteTrigger","ActionCreateTrigger","ActionHistoryChart","ActionMenu","ActionOverdueTrigger","Actions","ActionsController","ActionsModule","ActionText","ActionUncompleteTrigger","Admin","AdminController","ApiController","ApiControllerSecurityTest","ApiControllerTest","APIModel","APIModelTest","ApiVoipTest","ApplicationConfigBehavior","ArrayUtil","ArrayUtilTest","Attachments","BugReports","BugReportsController","BugReportsModule","CAccessControlFilter","CAccessRule","CActiveDataProvider","CActiveFinder","CActiveForm","CActiveMock","CActiveRecordBehavior","CActiveRecordBehaviorTestCase","CActiveRecordMetaData","CActiveRelation","CalendarController","CalendarEvent","CalendarModule","Campaign","CampaignAttachment","CampaignEmailClickTrigger","CampaignEmailOpenTrigger","CampaignUnsubscribeTrigger","CampaignWebActivityTrigger","CApcCache","CArrayDataProvider","CAssetManager","CAttributeCollection","CAuthAssignment","CAuthItem","CAutoComplete","CBaseActiveRelation","CBehavior","CBelongsToRelation","CBooleanValidator","CBreadcrumbs","CButtonColumn","CCacheDependency","CCacheHttpSession","CCaptcha","CCaptchaAction","CCaptchaValidator","CChainedCacheDependency","CChainedLogFilter","CCheckBoxColumn","CChoiceFormat","CClientScript","CClipWidget","CCodeFile","CCodeForm","CCodeGenerator","CCompareValidator","CComponent","CConfiguration","CConsoleApplication","CConsoleCommandBehavior","CConsoleCommandEvent","CConsoleCommandRunner","CContentDecorator","CController","CCookieCollection","CDataColumn","CDataProviderIterator","CDateFormatter","CDateTimeParser","CDateValidator","CDbAuthManager","CDbCache","CDbCacheDependency","CDbColumnSchema","CDbCommand","CDbCommandBuilder","CDbConnection","CDbCriteria","CDbDataReader","CDbException","CDbExpression","CDbFixtureManager","CDbHttpSession","CDbLogRoute","CDbMessageSource","CDbTableSchema","CDbTransaction","CDefaultValueValidator","CDetailView","CDirectoryCacheDependency","CDummyCache","CEAcceleratorCache","CEmailLogRoute","CEmailValidator","CEnumerable","CErrorEvent","CErrorHandler","CEvent","CException","CExceptionEvent","CExistValidator","CExpressionDependency","CExtController","CFile","CFileCache","CFileCacheDependency","CFileHelper","CFileLogRoute","CFileValidator","CFilter","CFilterChain","CFilterValidator","CFilterWidget","CFlexWidget","CForm","CFormatter","CFormButtonElement","CFormElementCollection","CFormInputElement","CFormModel","CFormStringElement","CGettextMessageSource","CGettextMoFile","CGettextPoFile","CGlobalStateCacheDependency","CGoogleApi","CGridView","Changelog","ChartsController","ChartSetting","ChartsModule","CHasManyRelation","CHasOneRelation","ChatBox","CHelpCommand","CHtml","CHtmlPurifier","CHttpCacheFilter","CHttpCookie","CHttpException","CHttpRequest","CHttpSession","CHttpSessionIterator","CImageComponent","CInlineAction","CInlineFilter","CInlineValidator","CJavaScript","CJavaScriptExpression","CJoinElement","CJoinQuery","CJSON","CJuiAccordion","CJuiAutoComplete","CJuiButton","CJuiDatePicker","CJuiDateTimePicker","CJuiDialog","CJuiDraggable","CJuiDroppable","CJuiProgressBar","CJuiResizable","CJuiSelectable","CJuiSlider","CJuiSliderInput","CJuiSortable","CJuiTabs","class","CLinkColumn","CLinkPager","CList","CListIterator","CListPager","CListView","CLocale","CLogFilter","CLogger","CLogRouter","CManyManyRelation","CMap","CMapIterator","CMarkdown","CMarkdownParser","CMaskedTextField","CMemCache","CMemCacheServerConfiguration","CMenu","CMissingTranslationEvent","CModelBehavior","CModelEvent","CMssqlColumnSchema","CMssqlCommandBuilder","CMssqlPdoAdapter","CMssqlSchema","CMssqlSqlsrvPdoAdapter","CMssqlTableSchema","CMultiFileUpload","CMysqlColumnSchema","CMysqlCommandBuilder","CMysqlSchema","CMysqlTableSchema","CNumberFormatter","CNumberValidator","COciColumnSchema","COciCommandBuilder","COciSchema","COciTableSchema","CodeExchangeException","CommandUtil","CommandUtilTest","CommonControllerBehavior","ContactForm","ContactList","Contacts","ContactsController","ContactsModule","Controller","ControllerCode","ControllerCommand","ControllerGenerator","COutputCache","COutputEvent","COutputProcessor","CPagination","CPgsqlColumnSchema","CPgsqlSchema","CPgsqlTableSchema","CPhpAuthManager","CPhpMessageSource","CPortlet","CPradoViewRenderer","CProfileLogRoute","CPropertyValue","CQueue","CQueueIterator","CRangeValidator","Credentials","CredentialsTest","CRegularExpressionValidator","CRequiredValidator","Criteria","CronEvent","CrontabUtil","CrontabUtilTest","CrudCode","CrudCommand","CrudGenerator","CryptSetupCommand","CSafeValidator","CSaveRelationsBehavior","CSecurityManager","CSoapObjectWrapper","CSort","CSqlDataProvider","CSqliteColumnSchema","CSqliteCommandBuilder","CSqliteSchema","CStack","CStackIterator","CStarRating","CStatElement","CStatePersister","CStatRelation","CStringValidator","CTabView","CTextHighlighter","CTheme","CThemeManager","CTimestamp","CTimestampBehavior","CTreeView","CTypedList","CTypedMap","CTypeValidator","CUniqueValidator","CUnsafeValidator","CUploadedFile","CUrlManager","CUrlRule","CUrlValidator","CUserIdentity","CVarDumper","CViewAction","CWebApplication","CWebLogRoute","CWebModule","CWebService","CWebServiceAction","CWebUser","CWidget","CWidgetFactory","CWinCache","CWsdlGenerator","CXCache","CZendDataCache","Dashboard","DashboardController","DashboardModule","DashboardSettings","DatabaseBackupAction","DbProfileLogRoute","DefaultController","DocChild","Docs","DocsController","DocsModule","DocsTest","DocViewer","DownloadDatabaseBackupAction","Dropdowns","DummyCommand","EButtonColumnWithClearFilters","EditRoleAccessAction","EmailAccount","EmaildropboxCommand","EmailDropboxSettingsAction","EmailImportAction","EmailImportAndViewTest","EmailImportBehavior","EmailImportBehaviorTest","EmbeddedModelForm","EmbeddedModelMock","EmlParse","EmlParseTest","EmlRegex","EmlRegexTest","EnactX2CRMChangesAction","EncryptedFieldsBehavior","EncryptUtil","EncryptUtilTest","ERememberFiltersBehavior","Events","EventsData","EventsTest","ExportAccountsReportAction","ExportFixtureCommand","ExportServiceReportAction","EZip","Fields","FieldsTest","FileUtil","FileUtilTest","FineDiff","FineDiffCopyOp","FineDiffDeleteOp","FineDiffInsertOp","FineDiffOps","FineDiffReplaceOp","FlowDesignerAction","FontPickerInput","Formatter","FormCode","FormCommand","FormGenerator","FormLayout","Gallery","GalleryBehavior","GalleryController","GalleryManager","GalleryPhoto","GalleryToModel","GalleryWidget","GetActionsBetweenAction","GetCredentialsException","GetRoleAccessAction","GiiModule","GMailAccount","Google_About","Google_AboutAdditionalRoleInfo","Google_AboutAdditionalRoleInfoRoleSets","Google_AboutExportFormats","Google_AboutFeatures","Google_AboutImportFormats","Google_AboutMaxUploadSizes","Google_AccessConfig","Google_Account","Google_AccountBidderLocation","Google_AccountChildLink","Google_Accounts","Google_AccountsList","Google_AchievementDefinition","Google_AchievementDefinitionsListResponse","Google_AchievementIncrementResponse","Google_AchievementRevealResponse","Google_AchievementUnlockResponse","Google_Acl","Google_AclItems","Google_AclRule","Google_AclRuleScope","Google_Activities","Google_Activity","Google_ActivityActor","Google_ActivityActorImage","Google_ActivityActorName","Google_ActivityEvents","Google_ActivityEventsParameters","Google_ActivityFeed","Google_ActivityId","Google_ActivityList","Google_ActivityObject","Google_ActivityObjectActor","Google_ActivityObjectActorImage","Google_ActivityObjectAttachments","Google_ActivityObjectAttachmentsEmbed","Google_ActivityObjectAttachmentsFullImage","Google_ActivityObjectAttachmentsImage","Google_ActivityObjectAttachmentsThumbnails","Google_ActivityObjectAttachmentsThumbnailsImage","Google_ActivityObjectPlusoners","Google_ActivityObjectReplies","Google_ActivityObjectResharers","Google_ActivityProvider","Google_AdClient","Google_AdClients","Google_AdCode","Google_Address","Google_AdexchangebuyerService","Google_AdExchangeSellerService","Google_AdSenseHostService","Google_AdsenseReportsGenerateResponse","Google_AdsenseReportsGenerateResponseHeaders","Google_AdSenseService","Google_AdStyle","Google_AdStyleColors","Google_AdStyleFont","Google_AdUnit","Google_AdUnitContentAdsSettings","Google_AdUnitContentAdsSettingsBackupOption","Google_AdUnitFeedAdsSettings","Google_AdUnitMobileContentAdsSettings","Google_AdUnits","Google_Advertiser","Google_Advertisers","Google_AggregateStats","Google_AnalyticsService","Google_AnalyticsSnapshot","Google_AnalyticsSummary","Google_AndroidpublisherService","Google_Annotation","Google_AnnotationClientVersionRanges","Google_AnnotationCurrentVersionRanges","Google_Annotationdata","Google_Annotations","Google_Annotationsdata","Google_AnonymousPlayer","googleApcCache","Google_App","Google_AppIcons","Google_Application","Google_ApplicationCategory","Google_AppList","Google_AssertionCredentials","Google_AssociationSession","Google_AttachedDisk","Google_Attachment","Google_AttachmentsListResponse","Google_AuditService","GoogleAuthenticator","Google_AuthException","Google_AuthNone","Google_Badge","Google_BadgeList","Google_BatchRequest","Google_BigqueryService","Google_Blog","Google_BloggerService","Google_BlogList","Google_BlogLocale","Google_BlogPages","Google_BlogPosts","Google_BooksAnnotationsRange","Google_Bookshelf","Google_Bookshelves","Google_BooksLayerGeoData","Google_BooksLayerGeoDataCommon","Google_BooksLayerGeoDataGeo","Google_BooksLayerGeoDataGeoBoundary","Google_BooksLayerGeoDataGeoViewport","Google_BooksLayerGeoDataGeoViewportHi","Google_BooksLayerGeoDataGeoViewportLo","Google_BooksService","Google_Bucket","Google_BucketAccessControl","Google_BucketAccessControls","Google_BucketOwner","Google_Buckets","Google_BucketWebsite","Google_CacheException","Google_CacheParser","Google_Calendar","Google_CalendarList","Google_CalendarListEntry","Google_CalendarService","Google_CcOffer","Google_CcOfferBonusRewards","Google_CcOfferDefaultFees","Google_CcOfferRewards","Google_CcOffers","Google_Change","Google_ChangeList","Google_ChangePlanRequest","Google_ChildList","Google_ChildReference","Google_Client","Google_ColorDefinition","Google_Colors","Google_Column","Google_ColumnBaseColumn","Google_ColumnList","Google_Comment","Google_CommentActor","Google_CommentActorImage","Google_CommentAuthor","Google_CommentAuthorImage","Google_CommentBlog","Google_CommentContext","Google_CommentFeed","Google_CommentInReplyTo","Google_CommentList","Google_CommentObject","Google_CommentPlusoners","Google_CommentPost","Google_CommentReply","Google_CommentReplyList","Google_Community","Google_CommunityList","Google_CommunityMembers","Google_CommunityMembershipStatus","Google_CommunityMembersList","Google_CommunityMessage","Google_CommunityMessageList","Google_CommunityPoll","Google_CommunityPollComment","Google_CommunityPollCommentList","Google_CommunityPollImage","Google_CommunityPollList","Google_CommunityPollVote","Google_CommunityTopic","Google_CommunityTopicList","Google_ComputeService","Google_ConcurrentAccessRestriction","Google_Contact","Google_ContactsListResponse","Google_ContentserviceGet","Google_Context","Google_ContextFacets","Google_Counters","Google_Creative","Google_CreativeDisapprovalReasons","Google_CreativesList","Google_CurlIO","Google_CustomChannel","Google_CustomChannels","Google_CustomChannelTargetingInfo","Google_CustomDataSource","Google_CustomDataSourceChildLink","Google_CustomDataSourceParentLink","Google_CustomDataSources","Google_Customer","Google_CustomRichMediaEvents","Google_CustomsearchService","Google_DailyUpload","Google_DailyUploadAppend","Google_DailyUploadParentLink","Google_DailyUploadRecentChanges","Google_DailyUploads","Google_Dataset","Google_DatasetAccess","Google_DatasetList","Google_DatasetListDatasets","Google_DatasetReference","Google_DateRange","Google_DeprecationStatus","Google_DetectionsListResponse","Google_DetectionsResourceItems","Google_DfareportingFile","Google_DfareportingFileUrls","Google_DfareportingService","Google_DimensionFilter","Google_DimensionValue","Google_DimensionValueList","Google_DimensionValueRequest","Google_DirectDeal","Google_DirectDealsList","Google_Disk","Google_DiskList","Google_DownloadAccesses","Google_DownloadAccessRestriction","Google_DriveFile","Google_DriveFileImageMediaMetadata","Google_DriveFileImageMediaMetadataLocation","Google_DriveFileIndexableText","Google_DriveFileLabels","Google_DriveFileThumbnail","Google_DriveService","Google_Error","Google_ErrorProto","Google_Event","Google_EventAttendee","Google_EventCreator","Google_EventDateTime","Google_EventExtendedProperties","Google_EventGadget","Google_EventOrganizer","Google_EventProducts","Google_EventReminder","Google_EventReminders","Google_Events","Google_EventSource","Google_Exception","Google_Experiment","Google_ExperimentParentLink","Google_Experiments","Google_ExperimentVariations","Google_FileCache","Google_FileList","Google_Firewall","Google_FirewallAllowed","Google_FirewallList","Google_FreebaseService","Google_FreeBusyCalendar","Google_FreeBusyGroup","Google_FreeBusyRequest","Google_FreeBusyRequestItem","Google_FreeBusyResponse","Google_FusiontablesService","Google_GaData","Google_GaDataColumnHeaders","Google_GaDataProfileInfo","Google_GaDataQuery","Google_GamesService","Google_GanService","Google_Geometry","Google_GetQueryResultsResponse","Google_Goal","Google_GoalEventDetails","Google_GoalEventDetailsEventConditions","Google_GoalParentLink","Google_Goals","Google_GoalUrlDestinationDetails","Google_GoalUrlDestinationDetailsSteps","Google_GoalVisitNumPagesDetails","Google_GoalVisitTimeOnSiteDetails","Google_Groups","Google_GroupssettingsService","Google_HttpRequest","Google_Image","Google_ImageAsset","Google_ImageList","Google_ImageRawDisk","Google_Import","Google_Input","Google_InputInput","Google_Instance","Google_InstanceAndroidDetails","Google_InstanceIosDetails","Google_InstanceList","Google_InstanceWebDetails","Google_IOException","Google_ItemScope","Google_Job","Google_JobConfiguration","Google_JobConfigurationExtract","Google_JobConfigurationLink","Google_JobConfigurationLoad","Google_JobConfigurationQuery","Google_JobConfigurationTableCopy","Google_JobList","Google_JobListJobs","Google_JobReference","Google_JobStatistics","Google_JobStatus","Google_Kernel","Google_KernelList","Google_LanguagesListResponse","Google_LanguagesResource","Google_LatitudeService","Google_Layersummaries","Google_Layersummary","Google_Leaderboard","Google_LeaderboardEntry","Google_LeaderboardListResponse","Google_LeaderboardScoreRank","Google_LeaderboardScores","Google_LicenseAssignment","Google_LicenseAssignmentInsert","Google_LicenseAssignmentList","Google_LicensingService","Google_Line","Google_LineStyle","Google_Link","Google_Links","Google_LinkSpecialOffers","Google_Location","Google_LocationFeed","Google_LocationsListResponse","Google_LoginTicket","Google_MachineType","Google_MachineTypeEphemeralDisks","Google_MachineTypeList","GoogleMaps","Google_McfData","Google_McfDataColumnHeaders","Google_McfDataProfileInfo","Google_McfDataQuery","Google_McfDataRows","Google_McfDataRowsConversionPathValue","Google_MediaFileUpload","Google_MemcacheCache","Google_MenuItem","Google_MenuValue","Google_Metadata","Google_MetadataItems","Google_MirrorService","Google_Model","Google_ModeratorService","Google_ModeratorTopicsResourcePartial","Google_ModeratorTopicsResourcePartialId","Google_ModeratorVotesResourcePartial","Google_Moment","Google_MomentsFeed","Google_MomentVerb","Google_Money","Google_Network","Google_NetworkDiagnostics","Google_NetworkInterface","Google_NetworkList","Google_Notification","Google_NotificationConfig","Google_OAuth2","Google_Oauth2Service","Google_ObjectAccessControl","Google_ObjectAccessControls","Google_Objects","Google_Operation","Google_OperationError","Google_OperationErrorErrors","Google_OperationList","Google_OperationWarnings","Google_OperationWarningsData","Google_OrkutActivityobjectsResource","Google_OrkutActivitypersonResource","Google_OrkutActivitypersonResourceImage","Google_OrkutActivitypersonResourceName","Google_OrkutAuthorResource","Google_OrkutAuthorResourceImage","Google_OrkutCommunitypolloptionResource","Google_OrkutCommunitypolloptionResourceImage","Google_OrkutCounterResource","Google_OrkutLinkResource","Google_OrkutService","Google_Output","Google_OutputOutputMulti","Google_P12Signer","Google_Page","Google_PageAuthor","Google_PageAuthorImage","Google_PageBlog","Google_PageList","Google_PagespeedonlineService","Google_ParentList","Google_ParentReference","Google_PeerChannelDiagnostics","Google_PeerSessionDiagnostics","Google_PemVerifier","Google_PeopleFeed","Google_Permission","Google_PermissionList","Google_Person","Google_PersonAgeRange","Google_PersonCover","Google_PersonCoverCoverInfo","Google_PersonCoverCoverPhoto","Google_PersonEmails","Google_PersonImage","Google_PersonName","Google_PersonOrganizations","Google_PersonPlacesLived","Google_PersonUrls","Google_Player","Google_PlayerAchievement","Google_PlayerAchievementListResponse","Google_PlayerLeaderboardScore","Google_PlayerLeaderboardScoreListResponse","Google_PlayerScore","Google_PlayerScoreListResponse","Google_PlayerScoreResponse","Google_PlayerScoreSubmissionList","Google_PlusAclentryResource","Google_PlusMomentsService","Google_PlusService","Google_Point","Google_PointStyle","Google_Polygon","Google_PolygonStyle","Google_Post","Google_PostAuthor","Google_PostAuthorImage","Google_PostBlog","Google_PostList","Google_PostLocation","Google_PostReplies","Google_PredictionService","Google_Product","Google_Products","Google_ProductsFacets","Google_ProductsFacetsBuckets","Google_ProductsPromotions","Google_ProductsPromotionsCustomFields","Google_ProductsSpelling","Google_ProductsStores","Google_Profile","Google_ProfileAttribution","Google_ProfileAttributionGeo","Google_ProfileChildLink","Google_ProfileId","Google_ProfileParentLink","Google_Profiles","Google_Project","Google_ProjectList","Google_ProjectListProjects","Google_ProjectReference","Google_Promotion","Google_PromotionBodyLines","Google_PromotionImage","Google_Property","Google_PropertyList","Google_Publisher","Google_Publishers","Google_Query","Google_QueryRequest","Google_QueryResponse","Google_Quota","Google_ReadingPosition","Google_Recipient","Google_RenewalSettings","Google_Report","Google_ReportActiveGrpCriteria","Google_ReportCriteria","Google_ReportCrossDimensionReachCriteria","Google_ReportDelivery","Google_ReportFloodlightCriteria","Google_ReportFloodlightCriteriaReportProperties","Google_ReportHeaders","Google_ReportList","Google_ReportPathToConversionCriteria","Google_ReportPathToConversionCriteriaReportProperties","Google_ReportReachCriteria","Google_ReportSchedule","Google_RequestAccess","Google_ResellerService","Google_REST","Google_Result","Google_ResultFormattedResults","Google_ResultFormattedResultsRuleResults","Google_ResultFormattedResultsRuleResultsUrlBlocks","Google_ResultFormattedResultsRuleResultsUrlBlocksHeader","Google_ResultFormattedResultsRuleResultsUrlBlocksHeaderArgs","Google_ResultFormattedResultsRuleResultsUrlBlocksUrls","Google_ResultFormattedResultsRuleResultsUrlBlocksUrlsDetails","Google_ResultFormattedResultsRuleResultsUrlBlocksUrlsDetailsArgs","Google_ResultFormattedResultsRuleResultsUrlBlocksUrlsResult","Google_ResultFormattedResultsRuleResultsUrlBlocksUrlsResultArgs","Google_ResultImage","Google_ResultLabels","Google_ResultPageStats","Google_ResultTable","Google_ResultTableColumnHeaders","Google_ResultVersion","Google_Review","Google_ReviewAuthor","Google_ReviewSource","Google_Revision","Google_RevisionCheckResponse","Google_RevisionList","Google_Room","Google_RoomAutoMatchingCriteria","Google_RoomClientAddress","Google_RoomCreateRequest","Google_RoomJoinRequest","Google_RoomLeaveDiagnostics","Google_RoomLeaveRequest","Google_RoomList","Google_RoomModification","Google_RoomP2PStatus","Google_RoomP2PStatuses","Google_RoomParticipant","Google_RoomStatus","Google_SavedAdStyle","Google_SavedAdStyles","Google_SavedReport","Google_SavedReports","Google_ScoreSubmission","Google_Search","Google_SearchSearchInformation","Google_SearchSpelling","Google_SearchUrl","Google_Seats","Google_Segment","Google_Segments","Google_SerialPortOutput","Google_Series","Google_SeriesCounters","Google_SeriesId","Google_SeriesList","Google_SeriesRules","Google_SeriesRulesSubmissions","Google_SeriesRulesVotes","Google_Service","Google_ServiceAccount","Google_ServiceException","Google_ServiceResource","Google_Setting","Google_Settings","Google_ShoppingModelCategoryJsonV1","Google_ShoppingModelDebugJsonV1","Google_ShoppingModelDebugJsonV1BackendTimes","Google_ShoppingModelProductJsonV1","Google_ShoppingModelProductJsonV1Attributes","Google_ShoppingModelProductJsonV1Author","Google_ShoppingModelProductJsonV1Images","Google_ShoppingModelProductJsonV1ImagesThumbnails","Google_ShoppingModelProductJsonV1Internal4","Google_ShoppingModelProductJsonV1Inventories","Google_ShoppingModelProductJsonV1Variants","Google_ShoppingModelRecommendationsJsonV1","Google_ShoppingModelRecommendationsJsonV1RecommendationList","Google_ShoppingService","Google_SiteVerificationService","Google_SiteVerificationWebResourceGettokenRequest","Google_SiteVerificationWebResourceGettokenRequestSite","Google_SiteVerificationWebResourceGettokenResponse","Google_SiteVerificationWebResourceListResponse","Google_SiteVerificationWebResourceResource","Google_SiteVerificationWebResourceResourceSite","Google_Snapshot","Google_SnapshotList","Google_SortedDimension","Google_Sqlresponse","Google_StorageObject","Google_StorageObjectMedia","Google_StorageObjectOwner","Google_StorageService","Google_StringCount","Google_StyleFunction","Google_StyleFunctionGradient","Google_StyleFunctionGradientColors","Google_StyleSetting","Google_StyleSettingList","Google_Submission","Google_SubmissionAttribution","Google_SubmissionCounters","Google_SubmissionGeo","Google_SubmissionId","Google_SubmissionList","Google_SubmissionParentSubmissionId","Google_SubmissionTranslations","Google_Subscription","Google_SubscriptionPlan","Google_SubscriptionPlanCommitmentInterval","Google_SubscriptionPurchase","Google_Subscriptions","Google_SubscriptionsListResponse","Google_SubscriptionTrialSettings","Google_Table","Google_TableDataList","Google_TableFieldSchema","Google_TableList","Google_TableListTables","Google_TableReference","Google_TableRow","Google_TableRowF","Google_TableSchema","Google_Tag","Google_TagId","Google_TagList","Google_Tags","Google_Task","Google_TaskLinks","Google_TaskList","Google_TaskLists","Google_TaskQueue","Google_TaskQueueAcl","Google_TaskqueueService","Google_TaskQueueStats","Google_Tasks","Google_Tasks2","Google_TasksService","Google_Template","Google_TemplateList","Google_TimelineItem","Google_TimelineListResponse","Google_TimePeriod","Google_Tokeninfo","Google_Topic","Google_TopicCounters","Google_TopicId","Google_TopicList","Google_TopicRules","Google_TopicRulesSubmissions","Google_TopicRulesVotes","Google_Training","Google_TrainingDataAnalysis","Google_TrainingModelInfo","Google_TranslateService","Google_TranslationsListResponse","Google_TranslationsResource","Google_Update","Google_Url","Google_UrlChannel","Google_UrlChannels","Google_UrlHistory","Google_UrlshortenerService","Google_User","Google_UserAction","Google_UserBlogs","Google_Userinfo","Google_UserLocale","Google_UserPicture","Google_UserProfile","Google_UserProfileList","Google_Utils","Google_Visibility","Google_Volume","Google_VolumeAccessInfo","Google_VolumeAccessInfoEpub","Google_VolumeAccessInfoPdf","Google_Volumeannotation","Google_VolumeannotationContentRanges","Google_Volumeannotations","Google_Volumes","Google_VolumeSaleInfo","Google_VolumeSaleInfoListPrice","Google_VolumeSaleInfoRetailPrice","Google_VolumeSearchInfo","Google_VolumeUserInfo","Google_VolumeVolumeInfo","Google_VolumeVolumeInfoDimensions","Google_VolumeVolumeInfoImageLinks","Google_VolumeVolumeInfoIndustryIdentifiers","Google_Vote","Google_VoteId","Google_VoteList","Google_Webfont","Google_WebfontList","Google_WebfontsService","Google_Webproperties","Google_Webproperty","Google_WebpropertyChildLink","Google_WebpropertyParentLink","Google_YouTubeAnalyticsService","Google_Zone","Google_ZoneList","Google_ZoneMaintenanceWindows","Groups","GroupsController","GroupsModule","GroupToUser","HelpCommand","HelpfulTips","History","HTML5","HTML5TreeConstructer","HTMLPurifier_AttrDef_Clone","HTMLPurifier_AttrDef_CSS","HTMLPurifier_AttrDef_CSS_AlphaValue","HTMLPurifier_AttrDef_CSS_Background","HTMLPurifier_AttrDef_CSS_BackgroundPosition","HTMLPurifier_AttrDef_CSS_Border","HTMLPurifier_AttrDef_CSS_Color","HTMLPurifier_AttrDef_CSS_Composite","HTMLPurifier_AttrDef_CSS_DenyElementDecorator","HTMLPurifier_AttrDef_CSS_Filter","HTMLPurifier_AttrDef_CSS_Font","HTMLPurifier_AttrDef_CSS_FontFamily","HTMLPurifier_AttrDef_CSS_Ident","HTMLPurifier_AttrDef_CSS_ImportantDecorator","HTMLPurifier_AttrDef_CSS_Length","HTMLPurifier_AttrDef_CSS_ListStyle","HTMLPurifier_AttrDef_CSS_Multiple","HTMLPurifier_AttrDef_CSS_Number","HTMLPurifier_AttrDef_CSS_Percentage","HTMLPurifier_AttrDef_CSS_TextDecoration","HTMLPurifier_AttrDef_CSS_URI","HTMLPurifier_AttrDef_Enum","HTMLPurifier_AttrDef_HTML_Bool","HTMLPurifier_AttrDef_HTML_Class","HTMLPurifier_AttrDef_HTML_Color","HTMLPurifier_AttrDef_HTML_FrameTarget","HTMLPurifier_AttrDef_HTML_ID","HTMLPurifier_AttrDef_HTML_Length","HTMLPurifier_AttrDef_HTML_LinkTypes","HTMLPurifier_AttrDef_HTML_MultiLength","HTMLPurifier_AttrDef_HTML_Nmtokens","HTMLPurifier_AttrDef_HTML_Pixels","HTMLPurifier_AttrDef_Integer","HTMLPurifier_AttrDef_Lang","HTMLPurifier_AttrDef_Text","HTMLPurifier_AttrDef_URI","HTMLPurifier_AttrDef_URI_Email_SimpleCheck","HTMLPurifier_AttrDef_URI_Host","HTMLPurifier_AttrDef_URI_IPv4","HTMLPurifier_AttrDef_URI_IPv6","HTMLPurifier_AttrTransform_Background","HTMLPurifier_AttrTransform_BdoDir","HTMLPurifier_AttrTransform_BgColor","HTMLPurifier_AttrTransform_BoolToCSS","HTMLPurifier_AttrTransform_Border","HTMLPurifier_AttrTransform_EnumToCSS","HTMLPurifier_AttrTransform_ImgRequired","HTMLPurifier_AttrTransform_ImgSpace","HTMLPurifier_AttrTransform_Input","HTMLPurifier_AttrTransform_Lang","HTMLPurifier_AttrTransform_Length","HTMLPurifier_AttrTransform_Name","HTMLPurifier_AttrTransform_NameSync","HTMLPurifier_AttrTransform_Nofollow","HTMLPurifier_AttrTransform_SafeEmbed","HTMLPurifier_AttrTransform_SafeObject","HTMLPurifier_AttrTransform_SafeParam","HTMLPurifier_AttrTransform_ScriptRequired","HTMLPurifier_AttrTransform_TargetBlank","HTMLPurifier_AttrTransform_Textarea","HTMLPurifier_ChildDef_Chameleon","HTMLPurifier_ChildDef_Custom","HTMLPurifier_ChildDef_Empty","HTMLPurifier_ChildDef_List","HTMLPurifier_ChildDef_Optional","HTMLPurifier_ChildDef_Required","HTMLPurifier_ChildDef_StrictBlockquote","HTMLPurifier_ChildDef_Table","HTMLPurifier_ConfigSchema","HTMLPurifier_ConfigSchema_Builder_Xml","HTMLPurifier_ConfigSchema_Exception","HTMLPurifier_CSSDefinition","HTMLPurifier_DefinitionCache_Decorator","HTMLPurifier_DefinitionCache_Decorator_Cleanup","HTMLPurifier_DefinitionCache_Decorator_Memory","HTMLPurifier_DefinitionCache_Null","HTMLPurifier_DefinitionCache_Serializer","HTMLPurifier_EntityLookup","HTMLPurifier_Exception","HTMLPurifier_Filter_ExtractStyleBlocks","HTMLPurifier_Filter_YouTube","HTMLPurifier_HTMLDefinition","HTMLPurifier_HTMLModule_Bdo","HTMLPurifier_HTMLModule_CommonAttributes","HTMLPurifier_HTMLModule_Edit","HTMLPurifier_HTMLModule_Forms","HTMLPurifier_HTMLModule_Hypertext","HTMLPurifier_HTMLModule_Iframe","HTMLPurifier_HTMLModule_Image","HTMLPurifier_HTMLModule_Legacy","HTMLPurifier_HTMLModule_List","HTMLPurifier_HTMLModule_Name","HTMLPurifier_HTMLModule_Nofollow","HTMLPurifier_HTMLModule_NonXMLCommonAttributes","HTMLPurifier_HTMLModule_Object","HTMLPurifier_HTMLModule_Presentation","HTMLPurifier_HTMLModule_Proprietary","HTMLPurifier_HTMLModule_Ruby","HTMLPurifier_HTMLModule_SafeEmbed","HTMLPurifier_HTMLModule_SafeObject","HTMLPurifier_HTMLModule_Scripting","HTMLPurifier_HTMLModule_StyleAttribute","HTMLPurifier_HTMLModule_Tables","HTMLPurifier_HTMLModule_Target","HTMLPurifier_HTMLModule_TargetBlank","HTMLPurifier_HTMLModule_Text","HTMLPurifier_HTMLModule_Tidy","HTMLPurifier_HTMLModule_Tidy_Name","HTMLPurifier_HTMLModule_Tidy_Proprietary","HTMLPurifier_HTMLModule_Tidy_Strict","HTMLPurifier_HTMLModule_Tidy_Transitional","HTMLPurifier_HTMLModule_Tidy_XHTML","HTMLPurifier_HTMLModule_Tidy_XHTMLAndHTML4","HTMLPurifier_HTMLModule_XMLCommonAttributes","HTMLPurifier_Injector_AutoParagraph","HTMLPurifier_Injector_DisplayLinkURI","HTMLPurifier_Injector_Linkify","HTMLPurifier_Injector_PurifierLinkify","HTMLPurifier_Injector_RemoveEmpty","HTMLPurifier_Injector_RemoveSpansWithoutAttributes","HTMLPurifier_Injector_SafeObject","HTMLPurifier_Language_en_x_test","HTMLPurifier_Lexer_DirectLex","HTMLPurifier_Lexer_DOMLex","HTMLPurifier_Lexer_PH5P","HTMLPurifier_Printer_ConfigForm","HTMLPurifier_Printer_ConfigForm_bool","HTMLPurifier_Printer_ConfigForm_default","HTMLPurifier_Printer_ConfigForm_NullDecorator","HTMLPurifier_Printer_CSSDefinition","HTMLPurifier_Printer_HTMLDefinition","HTMLPurifier_PropertyListIterator","HTMLPurifier_Strategy_Core","HTMLPurifier_Strategy_FixNesting","HTMLPurifier_Strategy_MakeWellFormed","HTMLPurifier_Strategy_RemoveForeignElements","HTMLPurifier_Strategy_ValidateAttributes","HTMLPurifier_StringHash","HTMLPurifier_TagTransform_Font","HTMLPurifier_TagTransform_Simple","HTMLPurifier_Token","HTMLPurifier_Token_Comment","HTMLPurifier_Token_Empty","HTMLPurifier_Token_End","HTMLPurifier_Token_Start","HTMLPurifier_Token_Tag","HTMLPurifier_Token_Text","HTMLPurifier_URIDefinition","HTMLPurifier_URIFilter_DisableExternal","HTMLPurifier_URIFilter_DisableExternalResources","HTMLPurifier_URIFilter_DisableResources","HTMLPurifier_URIFilter_HostBlacklist","HTMLPurifier_URIFilter_MakeAbsolute","HTMLPurifier_URIFilter_Munge","HTMLPurifier_URIFilter_SafeIframe","HTMLPurifier_URIScheme_data","HTMLPurifier_URIScheme_file","HTMLPurifier_URIScheme_ftp","HTMLPurifier_URIScheme_http","HTMLPurifier_URIScheme_https","HTMLPurifier_URIScheme_mailto","HTMLPurifier_URIScheme_news","HTMLPurifier_URIScheme_nntp","HTMLPurifier_VarParserException","HTMLPurifier_VarParser_Flexible","HTMLPurifier_VarParser_Native","IasPager","idna_convert","Image","Image_GD_Driver","Image_ImageMagick_Driver","Imports","InlineActionForm","InlineEmail","InlineEmailAction","InlineEmailForm","InlineEmailTest","InlineQuotes","InlineRelationships","InlineTags","JSONEmbeddedModelFieldsBehavior","JSONEmbeddedModelFieldsBehaviorTest","JSONFieldsBehavior","JSONFieldsBehaviorTest","LeadRouting","LeadRoutingBehavior","Locations","LoginForm","m130123_200915_gallery_tables","Maps","MarkdownExtra_Parser","Markdown_Parser","MarketingController","MarketingModule","Media","MediaBox","MediaController","MediaModule","MediaTest","MenuList","MessageBox","MessageCommand","MigrateCommand","MMask","MobileController","MobileModule","ModelCode","ModelCommand","ModelGenerator","ModuleCode","ModuleCommand","ModuleGenerator","Modules","names","NewContactTest","NewsletterEmailClickTrigger","NewsletterEmailOpenTrigger","NewsletterSubscribeTrigger","NewsletterUnsubscribeTrigger","NewsletterWebActivityTrigger","NLSClientScript","NoRefreshTokenException","NoteBox","Notes","Notification","NotificationsController","NoUserIdException","OnlineUsers","OpportunitiesController","OpportunitiesModule","Opportunity","PhoneNumber","PHPMailer","phpmailerException","PlancakeEmailParser","POP3","Product","ProductFeature","ProductsController","ProductsModule","Profile","ProfileChild","ProfileController","ProxyComponent","Publisher","QuickContact","Quote","QuoteProduct","QuoteProductTest","QuotesController","QuotesModule","QuoteTest","RecentItems","Record","RecordCreateTrigger","RecordDeleteTrigger","RecordInactiveTrigger","RecordTagAddTrigger","RecordTagRemoveTrigger","RecordUpdateTrigger","RecordViewChart","RecordViewTrigger","Relationships","RememberPagination","Reminders","Reports","ReportsController","ReportsModule","ResponseBehavior","ResponseUtil","Roles","RoleToPermission","RoleToUser","RoleToWorkflow","Rules","SampleDataCommand","SearchController","SearchIndexBehavior","ServiceRoutingBehavior","Services","ServicesController","ServicesModule","ServicesReportAction","Session","SessionLog","ShellCommand","ShellException","SiteController","SiteTest","SmartDataProvider","SMTP","Social","SocialController","SocialForm","SortableWidgets","SortWidg","SourceFileDownloadAction","StudioController","TagBehavior","TagCloud","Tags","TempFile","Templates","TemplatesModule","TextDiff","Text_Diff","Text_Diff3","Text_Diff3_BlockBuilder","Text_Diff3_Op","Text_Diff3_Op_copy","Text_Diff_Engine_native","Text_Diff_Engine_shell","Text_Diff_Engine_string","Text_Diff_Engine_xdiff","Text_Diff_Mapped","Text_Diff_Op","Text_Diff_Op_add","Text_Diff_Op_change","Text_Diff_Op_copy","Text_Diff_Op_delete","Text_Diff_Renderer","Text_Diff_Renderer_context","Text_Diff_Renderer_inline","Text_Diff_Renderer_unified","Text_Diff_ThreeWay","Text_Diff_ThreeWay_BlockBuilder","Text_Diff_ThreeWay_Op","Text_Diff_ThreeWay_Op_copy","Text_Highlighter_ABAP","Text_Highlighter_CPP","Text_Highlighter_CSS","Text_Highlighter_DIFF","Text_Highlighter_DTD","Text_Highlighter_Generator","Text_Highlighter_HTML","Text_Highlighter_JAVA","Text_Highlighter_JAVASCRIPT","Text_Highlighter_MYSQL","Text_Highlighter_PERL","Text_Highlighter_PHP","Text_Highlighter_PYTHON","Text_Highlighter_Renderer_Array","Text_Highlighter_Renderer_BB","Text_Highlighter_Renderer_Console","Text_Highlighter_Renderer_Html","Text_Highlighter_Renderer_HtmlTags","Text_Highlighter_Renderer_JSON","Text_Highlighter_Renderer_XML","Text_Highlighter_RUBY","Text_Highlighter_SH","Text_Highlighter_SQL","Text_Highlighter_VBSCRIPT","Text_Highlighter_XML","Text_MappedDiff","TimerTrigger","TimeZone","Tips","TopContacts","TopSites","TrackEmail","TransformedFieldStorageBehaviorTest","TranslationLogger","Trigger","TwitterFeed","UniqueAttributesValidator","UpdaterBehavior","UpdaterBehaviorTest","UpdatesForm","URI_Template_Parser","URL","User","UserChild","UserIdentity","UserLoginTrigger","UserLogoutTrigger","UsersController","UsersModule","ViewLog","was","WebActivityTrigger","WebAppCommand","WebForm","WebleadTrigger","WeblistController","WebListenerAction","WebListenerActionTest","WebTestCase","Widgets","Workflow","WorkflowBehavior","WorkflowController","WorkflowModule","WorkflowRevertStageTrigger","WorkflowStage","WorkflowStageDetails","X2AuthCache","X2AuthManager","X2BarChart","X2BubbleChart","X2Calendar","X2CalendarPermissions","X2ChangeLogBehavior","X2Chart","X2ClientScript","X2Color","X2ControllerPermissionsBehavior","X2CRMUpdateAction","X2CRMUpgradeAction","X2CronAction","X2DataColumn","X2Date","X2FixtureManager","X2Flow","X2FlowApiCall","X2FlowCampaignLaunch","X2FlowCreateAction","X2FlowCreateEvent","X2FlowCreateNotif","X2FlowCreateReminder","X2FlowEmail","X2FlowRecordComment","X2FlowRecordCreate","X2FlowRecordCreateAction","X2FlowRecordDelete","X2FlowRecordEmail","X2FlowRecordListAdd","X2FlowRecordListRemove","X2FlowRecordReassign","X2FlowRecordTag","X2FlowRecordUpdate","X2FlowSwitch","X2FlowWait","X2FlowWorkflowComplete","X2FlowWorkflowRevert","X2FlowWorkflowStart","X2FunnelChart","X2GridView","X2Info","X2LineChart","X2LinkableBehavior","X2List","X2ListCriterion","X2ListItem","X2MarketingChartModel","X2MessageSource","X2PermissionsBehavior","X2PieChart","X2PipelineChartModel","X2SalesChartModel","X2SparkChart","X2StackedBarChart","X2TimestampBehavior","X2TranslationAction","X2TranslationBehavior","X2TranslationBehaviorTest","X2UrlRule","X2WebApplication","X2WebUser","X2WidgetList","Yii","YiiBase","YiiDebug","YiiDebugToolbar","YiiDebugToolbarPanelLogging","YiiDebugToolbarPanelRequest","YiiDebugToolbarPanelServer","YiiDebugToolbarPanelSettings","YiiDebugToolbarPanelSql","YiiDebugToolbarPanelViewsRendering","YiiDebugToolbarResourceUsage","YiiDebugToolbarRoute","YiiDebugViewHelper","YiiDebugViewRenderer");
    $classWarnings = array();
    foreach($classes as $class) {
        if(class_exists($class,false)){
            $requirements['classConflict'][] = $class;
            $classWarnings[] = $class;
        }
    }
    if(!empty($classWarnings)) {
        $classWarning = installer_t('Class collisions detected. The following preexisting classes in the local PHP runtime environment will conflict with classes defined within X2CRM:');
        $classWarning .= '<ul><li>'.implode('</li><li>',$classWarnings).'</li></ul>';
        $reqMessages[3][] = $classWarning;
    }
    
}


///////////////////////////////////////////////////////////
// MEDIUM-PRIORITY: IMPORTANT FUNCTIONALITY REQUIREMENTS //
///////////////////////////////////////////////////////////
// Check remote access methods
$curl = ($requirements['extensions']['curl']=extension_loaded("curl")) && function_exists('curl_init') && function_exists('curl_exec');
if(!$curl){
	$curlMissingIssues = array(
		installer_t('Time zone widget will not work'),
		installer_t('Contact views may be inaccessible'),
		installer_t('Google integration will not work'),
		installer_t('Built-in error reporter will not work')
	);
	$reqMessages[2][] = '<a href="http://php.net/manual/book.curl.php">cURL</a>: '.$rbm.'. '.installer_t('This will result in the following issues:').'<ul><li>'.implode('</li><li>', $curlMissingIssues).'</li></ul>'.installer_t('Furthermore, please note: without this extension, the requirements check script could not check the outbound internet connection of this server.');
}

if(!(bool) ($requirements['environment']['allow_url_fopen']=@ini_get('allow_url_fopen'))){
	if(!$curl){
		$tryAccess = false;
		$reqMessages[2][] = installer_t('The PHP configuration option "allow_url_fopen" is disabled in addition to the CURL extension missing. This means there is no possible way to make HTTP requests, and thus software updates will have to be performed manually.');
	} else
		$reqMessages[1][] = installer_t('The PHP configuration option "allow_url_fopen" is disabled. CURL will be used for making all HTTP requests during updates.');
}
$requirements['environment']['updates_connection'] = 0;
$requirements['environment']['outbound_connection'] = 0;
if($tryAccess){
	if(!(bool) @file_get_contents('http://google.com')){

		$ch = curl_init('https://www.google.com');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 0);
		$response = (bool) @curl_exec($ch);
		curl_close($ch);
		if(!($requirements['environment']['outbound_connection']=(bool)$response)){
			$reqMessages[2][] = installer_t('This web server is effectively cut off from the internet; (1) no outbound network route exists, or (2) local DNS resolution is failing, or (3) this server is behind a firewall that is preventing outbound requests. Software updates will have to be performed manually, and Google integration will not work.');
		} else {
			$ch = curl_init('https://x2planet.com/installs/registry/reqCheck');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 0);
			$response = (bool) @curl_exec($ch);
			curl_close($ch);
			if(!($requirements['environment']['updates_connection']=(bool)$response)) {
				$reqMessages[2][] = installer_t('Could not reach the updates server from this web server. This may be a temporary problem. If it persists, software updates will have to be performed manaully.');
			}
		}

	}
}

// Check the ability to make database backups during updates:
$canBackup = $requirements['functions']['proc_open'] = function_exists('proc_open');
$requirements['environment']['shell'] = $canBackup;
if($canBackup){
	try{
		// Test for the availability of mysqldump:
        $descriptor = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $testProc = proc_open('mysqldump --help', $descriptor, $pipes);
        $ret = proc_close($testProc);
        unset($pipes);
        
        if($ret === 0) {
            $prog = 'mysqldump';
        } else if($ret !== 0){
            $testProc = proc_open('mysqldump.exe --help', $descriptor, $pipes);
            $ret = proc_close($testProc);
            if($ret !== 0)
                throw new CException(Yii::t('admin', 'Unable to perform database backup; the "mysqldump" utility is not available on this system.'));
            else
                $prog = 'mysqldump.exe';
        }
	}catch(Exception $e){
		$canBackup = false;
	}
    $canBackup = isset($prog);
}
if(!$canBackup){
    $requirements['environment']['shell'] = 0;
	$reqMessages[2][] = installer_t('The function proc_open and/or the "mysqldump" and "mysql" command line utilities are unavailable on this system. X2CRM will not be able to automatically make a backup of its database during software updates, or automatically restore its database in the event of a failed update.');
}
// Check the session save path:
$ssp = ini_get('session.save_path');
if(!is_writable($ssp)){
	$reqMessages[2][] = strtr(installer_t('The path defined in session.save_path ({ssp}) is not writable. Uploading files via the media module will not work.'), array('{ssp}' => $ssp));
}

////////////////////////////////////////////////////////////
// LOW PRIORITY: MISCELLANEOUS FUNCTIONALITY REQUIREMENTS //
////////////////////////////////////////////////////////////
// Check encryption methods
if(!($requirements['extensions']['openssl']=extension_loaded('openssl') && $requirements['extensions']['mcrypt']=extension_loaded('mcrypt'))) {
	$reqMessages[1][] = installer_t('The "openssl" and "mcrypt" libraries are not available. If any application credentials (i.e. email account passwords) are entered into X2CRM, they  will be stored in the database in plain text (without any encryption whatsoever). Thus, if the database is ever compromised, those passwords will be readable by unauthorized parties.');
}

// Check for Zip extension
if(!($requirements['extensions']['zip']=extension_loaded('zip'))){
	$reqMessages[1][] = '<a href="http://php.net/manual/book.zip.php">Zip</a>: '.$rbm.'. '.installer_t('This will result in the inability to import and export custom modules.');
}
// Check for fileinfo extension
if(!($requirements['extensions']['fileinfo']=extension_loaded('fileinfo'))){
	$reqMessages[1][] = '<a href="http://php.net/manual/book.fileinfo.php">Fileinfo</a>: '.$rbm.'. '.installer_t('Image previews and MIME info for uploaded files in the media module will not be available.');
}
// Check for GD exension
if(!($requirements['extensions']['gd']=extension_loaded('gd'))){
	$reqMessages[1][] = '<a href="http://php.net/manual/book.image.php">GD</a>: '.$rbm.'. '.installer_t('Security captchas will not work, and the media module will not be able to detect or display the dimensions of uploaded images.');
}

// Determine if there are messages to show and if installation is even possible
$hasMessages = false;
foreach($reqMessages as $severity=>$messages) {
	if((bool)count($messages))
		$hasMessages = true;
}
$canInstall = !(bool) count($reqMessages[3]);

///////////////////////////////
// END OF REQUIREMENTS CHECK //
///////////////////////////////

////////////////////
// COMPOSE OUTPUT //
////////////////////
$output = '';

if(!$canInstall){
	$output .= '<div style="width: 100%; text-align:center;"><h1>'.installer_t("Cannot $scenario X2CRM")."</h1></div>\n";
	$output .= "<strong>".installer_t('Unfortunately, your server does not meet the minimum system requirements;')."</strong><br />";
}else if($hasMessages){
	$output .= '<div style="width: 100%; text-align:center;"><h1>'.installer_t('Note the following:').'</h1></div>';
}else if($standalone){
	$output .= '<div style="width: 100%; text-align:center;"><h1>'.installer_t('This webserver can run X2CRM!').'</h1></div>';
}

$severityClasses = array(
	1 => 'minor',
	2 => 'major',
	3 => 'critical'
);
$severityStyles = array(
	1 => 'color:black',
	2 => 'color:#CF5A00',
	3 => 'color: #DD0000'
);

if($hasMessages){
	$output .= "\n<ul>";
	foreach($reqMessages as $severity => $messages){
		foreach($messages as $message){
			$output .= "<li style=\"{$severityStyles[$severity]}\">$message</li>";
		}
	}
	$output .= "</ul>\n";
	$output .= "<div style=\"text-align:center;\">Severity legend: ";
	foreach($severityClasses as $severity => $class) {
		$output .= "<span style=\"{$severityStyles[$severity]}\">$class</span>&nbsp;";
	}
	$output .= "<br />\n";
	if($canInstall)
		$output .= '<br />'.installer_t("All other essential requirements were met.").'&nbsp;';
	$output .= '</div><br />';
}

if($standalone){
	$imgData = 'iVBORw0KGgoAAAANSUhEUgAAAGkAAAAfCAYAAADk+ePmAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAADMlJREFUeNrs'.
			'W3lwVGUS/82RZBISyMkRwhESIISjEBJAgWRQbhiuci3AqsUVarfcWlZR1xL+2HJry9oqdNUtj0UFQdSwoELCLcoKBUgOi3AkkkyCYi4SSELOSTIzyWz3N/Ne3nszCVnd2qHKNNU1'.
			'731nf91fd/++7wUdiI4cOTKIfp4hfoJ4NPrJ39RAnEn8hsViuaLzGOhMZGTk1Pj4eAwZMqRfRX6mtrY2VFRUoKSkhI1lNrIHsYFmzJwpGnS5XP1a8jMFmUxISEyEwWgML7p+PVPP'.
			'IS4hIQGurq5fFNtaW/Hujh2q9zdef/2+knHUyJEwmUyj2ZNGh0dE/OI8qLy8HMOHD5fXze+JtHvvNz0EBweDjSSs5g86c+Ys6uvrSFlxuHbtKjZt2oTKykrk5OQI4Tg2r1mzBm++'.
			'+SY2b96MnNxcXLvqbnfw4EFMnjIFIdSO29fX18NsNnMcF/14zClTJuPEiRPyu9mcLr/zPOnUXlq71WoV5dkXs2VZtm/fjhdeeEGU79y5U8jgDzL6Mw/VkYEoHyJ1Rqp4tpKCT548'.
			'iY0bNwojHTp0COWUQF0eGa+SgZhabTZqXw8O0xkZGVi/fj0p9ppoW8HKT08XXsH9Fy9eLPpwO1ubTZQz79q1C7GxsfLalf0kWWLJ02rr6pBLm2MRjeMPPbloTr3kSf7gSlJqakqK'.
			'eK4nZbi6XEgkxZuCgtw7nATkZ/4tsZZg0qRJiKTQzMZKm5uGivIK0e8QeVVdbR2VzRXvCWPGiP6lpOizZ84I71m3dq2YT6rjdhHh4bIsPIeyLnbYMGFERlnKfv9v9rsnSUimoqKS'.
			'EyRxEOrq6oU8VZVVwls4X5rIqzgErVy1Cv+kXc27K4WMW0W7nxW5YuVKGZmyB0rr4ecFCxeqoC3X5eXlqdop63helkUgLDLM7t27sXLlKr/piGc1UKh4iVEE76Sfy699/A0iBgYj'.
			'elDwPdtyDikoKBC/Tc1NWEjKDAsLE4Jdzs93ly1YCKPRAIfTKZBOBBmMhZaeuX3N7du4fPkympqahPECjEaM9KzHZArGxW8u4vbtGsTEDBbti4uKEBYaJp5HKtbNctwoLZVl4TKj'.
			'MQDFxUVYunTp/0Q/P4VrqqvBh1nXHAoTP5feOZCHzDNFCA0JxG9WPABL2rhe2xcWFqKqqgoLFiy4bw+Uez/8EJYVK4S3+osYKLnDXZdvVy7+sZaSrQMGgx4GvU5wUnyMV7t/5/2A'.
			'Q18XYUjUAPx4qxE7My+h8nYTfrtmeo+T2yj5J09I7nFuf9O3FBKXL7dg6NBhfpWRHcoNwV2+IXheYRXe+TRPeEdocCAGBAfg2ccfxNTx3VdH331fi7f25yJ1Yizyi25BT4a0Ozph'.
			'a3fgVm0z3iYP+/rbm2i22eU+YTTevJTReOrRsaq5z58/j9ra2h4FnjNnDqKjo1FEIYtZIg5PISEh8ruyXuqjJZ6rrKwM5cQSjU9KQpKHH5o9W4yRnZMt6qRypszMTLkPh8xp06ap'.
			'xpbqeV6eX9vHF62ifNtTVurVk9YtmoiC0hpcKq6Wy/YevYLxo+YhKNCI8ppG/CMjB7HRYbCS14WGBJEhA5GSTMk8fTzWbvscza1u4xgN+u5Q0uHE8QulOJdfhoyXV2NoVKhbcefO'.
			'UQ4o7nEhycnJBNmjcP36dRzOypLLdfSPw5JEynqpj0T5+ZewL2MfARTvzcBGqZg+HUnjkwRQUI5joPPauHHjxXOWQuG8OZJeeVWAG4mk+gkTJggjsX6z7mGkNavXwNnZ6RM46CVP'.
			'6olffOIh4UVymCIPyThRQL92fHjkKoVCHToFVKTfzi4MjQ7FH9fNwp4jl2HQ6RAeGiR4+OAwpE8bJb8zc/jMOFkgEn5P3qz1fdFOg7ROnfqCPPCOLLOq3tOH+fz5c3iLDsaSgVjB'.
			'rEiJpffOrk6f88jja8J2VlamSmfac05f1uaCy6f+BYBxe1LPg4SYjNiyfgZez8jtTmbkXaUV9RTa9ALNcf6JIyPcbW7HK08vBDkZrlprMHBAkNznb5vnY+KYaFwuvoU/7zgrlzeR'.
			'p7GxHM5OsSCJtm3bhrHj1OCDz1HOTqeqHSuWFXXk8GH8esMGWTFK4vXV0dnnX/v2de9c8gyG54GBge7t6nZJtDMU9+jD1zgq3XjmPnXqFB6ZP18gTi/lk8zafnv37vVs7G7qpPX3'.
			'ZAd9t7V75hkTh2Hp7ARVRwflHfac+kYbhpH3dNid+OvvH0ZQAOckBx5fPBEbLFPxq/nJMFP+mZQQg7b2drFnOCdJHBJkFMb2Ugi92+12FTucDrdMinZzPciUc0yJ1epV7/Ks76sv'.
			'vxSIjWk6hbTlFouY10nwng0vmJ6NAQHda/dx+lfKyeMEe8LcB7s+8KBmV699hEEorGnXJm0+L5YPs31AL6vN41B44w6FOad7yxG1dxDyo4U2kgdtsDyAkUMGotXmVoQlLVEoQafT'.
			'iTYdJEgrIcXDZ60UPgPkcceNjBI7iGVQSsH5qeDaNZUMywhtaXf4iBEj5Gc6TuCZLVvUSnG5xNhl5eXda1m9Gh0d9j5dyag9Sf3OIIdBw4ULF8R5qrTEijEJiV6bTduPQUSnIv9E'.
			'RkXhwQcf6jHEe24c7h0zTUEGLJgZj6yzJZ796TYU2QBrF0/G7Kkj0NzSClnV9KN06eraVnxyolCERFOQ0X0jEBSAFeYk8jy7WwaFUtgzvDYKhaj2jg6V8qIIQc2aNQvZ2dki8V+5'.
			'ckVV7/Ksj71MotjY4QRe2sXzsaNHcfzYMdU8zz//J8QnjPHh3d56WrZ8uTAS0/79+/Hi1m1eeUzbL0sBeiSAMZvQZO/AgXfxPbiipglf5dxU5RkmJ4W8xBGRaGltFR7hq29e4S3s'.
			'+DwfdxraqL1LcKDRgKfXzyIvc1HodIp2fdnZop0mni1ZukwOO58eOKCG3C54jc1Kk2SDj2kNBoPPOrmPgviC2DxvnnhmSJ+bmyPKvGTu49q0DBf65kltFOLeO3gFkYNCRA7S0msf'.
			'X8SLG2YSLNd71X1+2oqz+RWqstHDBmELnbdCQ4zCuMpdI9HWrVspdGjzoMOtYI2VIiIjYDbPw4kTx0UIamxoUNVzH1YcX/0wXbp0CUm0e5lmzJyByZMnCc+VPELqo81KvvTEyl2y'.
			'ZClyyJM55/Eve7c0F9dr++3Zs0dcdSmJ04FvO7ju7UktdAjd/lGuyC0l5XUCgg8KNaljc4MNxy5879X3/cyrOErlfJCV2JI2Hi//YT5MBKpaODyqdk23UjhU2ii/Kdlud7jbasIZ'.
			'l6WZzfIO/pJAgraevz3JNySnT8seFhERidjhcQgbONCrjxcI8OFJLnFHaMLiJUvkMxrfmvfmSRw5tGtjdOfTBuiDJ310vBBl1U0YHDnAHQqMeoLNHXg4NR6nc7+X2x2/cIMQXDQS'.
			'hrsvSfO+q8YX2T+oxkqIi6Rc4MCOz3IISXXH38fmj/PyJA4d/N1IG1qYvYAByc836IsWL6GD6icCFmvr09LSxWcL6dD6/nvvYhEplr/OeoEET5++5KQuafz0dJyh8e+SBynn7/Lh'.
			'SUUEMji3KonlCFYciL2BQw8x88BXxfi26DZiY8JQeaeZDqAmNLZ04KXfmckYg1BaXgtr2V25/Ssf5eCNZx+mfKPD259dVl0Fidtta7VgLfGFbHNLi8pKyjONRPzJgD1GpU+F/Cmp'.
			'qcIQVVWVXvX8yWPtuvU0bob7rEfI8ZoGPWr7aL9O+NKT+xzk8lztrMbuD3b1WC/R31991Wuc5557HrFxcf/djcMpAgn7ThWJBG8tq0fUoGDKR2146tEUJMaF425DIx57ZCyFLYM8'.
			'YIvNgbc+zceNigbcrGoUl7N9Yf4c4T6V955g+aLXVzul3Ct93IFJdSmpKXjyyY0+7/K0h9Te5vF1o8A8cdJE8bX4p9w46D1r87pxYATNnyoSPXdSSvrLrmykJI/ApaIqCnWhaKYQ'.
			'NyE+BptWPYA7tXUyXLz+Qz2+KaiB3elCOx1oOZytmDsWe49dpdBo6NNN7/vblqGWwkRNTTX0vbSLjomBTm8QIcXpsMtw2qGBrhXlZQRiAnusNxJ642ukGzduwGG3y4bhz+VjE8cK'.
			'eN7e3qGaR5pbOX5IyACEE2jhHCORso+yXimTL/IlJ1Ml9WMj3R05Oj6cP5apdi0peEBwiIinep1e/uVbA0ZZqjMUJc7AgAAvtNKbUGoY36nOI/0kE9/Ss2Uy6+tqn4gZrP7L1S6y'.
			'foOjqU8D2Qh62jxXLmro3t6v5Z9BfI9ot3c0sJFeamluXqXX68PDwyMoNhr6tXOfGOh2jQBZz+g8d16j+IxFbOY/wOgn/xJf9HY6nTfZQBaLJUunrPT88f7UfjX5nRr4f1NIL/8R'.
			'YABtitvxQEn6dgAAAABJRU5ErkJggg==';
	$output .= '<img style="display:block;margin-left:278px;float:none;" src="data:image/png;base64,'.$imgData.'"><br /><br />';
    $output .= $phpInfoContent;
}

/////////
// FIN //
/////////
if(!$returnArray){
    echo strtr($document, array(
        '{headerContent}' => $phpInfoStyle,
        '{bodyContent}' => $output
    ));
    $responding = true; // We made it! No need to perform the shutdown function for fatal errors.
    restore_error_handler();
    restore_exception_handler();
}else{
    return compact('requirements','reqMessages','canInstall','hasMessages');
}
?>
