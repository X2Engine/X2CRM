# 6.5 #
10/7/2016
* General Changelog / Developer Notes
  * New Location tracking features
    * Log locations when Contacts open emails, visit your webpage, etc.
    * Log locations of users when they log in to the system
    * Log periodic location updates from X2Touch mobile
    * Settings page to configure and enable user location logging
  * Google Maps API Re-enabled
  * Jasper Reporting Integration
  * Two-way Google Calendar sync
  * Support for multiple user calendars
  * Miscellaneous bug fixes and improvements
* Tracked Bug Fixes:  
  * [4858](http://x2software.com/index.php/bugReports/4858): The system is unable to find the requested action "configureMyInbox".  
  * [4923](http://x2software.com/index.php/bugReports/4923): Trying to get property of non-object  
  * [5288](http://x2software.com/index.php/bugReports/5288): Undefined variable: success  

# 6.0.4 #
8/12/2016
* General Changelog / Developer Notes
  * More improvements to Admin control panel
  * Fix inline email issue on Opportunities
  * Added ability to disable Email Inbox per credential
  * Miscellaneous bug fixes

# 6.0.3 #
8/3/2016
* General Changelog / Developer Notes
  * Admin control panel UI overhaul
  * Ability to delete from mobile activity feed
  * Mobile login tokens persist longer
  * Miscellaneous bug fixes

# 6.0.2 #
7/14/2016
* General Changelog / Developer Notes
  * Action history is now accessible from mobile
  * New "Change Record" flow action allows workflows to execute on related records
  * New "Mass Execute Macro" mass action allows execution of macro type workflows from the grids
  * More improvements to web capture API, including file upload fields
  * Miscellaneous bug fixes

# 6.0.1 #
5/26/2016
* General Changelog / Developer Notes
  * X2Touch now supports persistent sessions
  * Improvements to web capture API
  * Miscellaneous bug fixes

# 6.0 #
4/26/2016
* General Changelog / Developer Notes
  * X2CRM is now fully open source
  * UI tweaks and enhancements
  * Miscellaneous bug fixes

# 5.5 #
2/25/2016
* General Changelog / Developer Notes
  * PHP7 compatibility
  * Yii updated to 1.1.17
  * PHPMailer updated to 5.2.14
  * File attachment feature added to most X2Touch modules, including custom modules
  * Added "Copy to Sent" dropdown to email client configuration to explicitly store sent messages
  * Added "Reply All" button to email client
  * New Office365 credentials type
  * Added web tracker JavaScript code export functionality
  * Fixed importer bug which created erroneous relationships
  * Automated email logging bugfixes
  * Fixed bug preventing records export download button from showing
  * Fixed email client quota issue with Office365
* Tracked Bug Fixes:  
  * [3335](http://x2software.com/index.php/bugReports/3335): CDbCommand failed to execute the SQL statement: SQLSTATE[23000]: Integrity constraint violation: 1052 Column 'id' in where clause is ambiguous  
  * [3336](http://x2software.com/index.php/bugReports/3336): Trying to get property of non-object  
  * [3348](http://x2software.com/index.php/bugReports/3348): array_combine() expects parameter 1 to be array, null given  
  * [3358](http://x2software.com/index.php/bugReports/3358): User Report  
  * [3363](http://x2software.com/index.php/bugReports/3363): Undefined variable: id  
  * [3367](http://x2software.com/index.php/bugReports/3367): Undefined index: dupeCheck  
  * [3376](http://x2software.com/index.php/bugReports/3376): User Report  
  * [3379](http://x2software.com/index.php/bugReports/3379): file_exists(): open_basedir restriction in effect. 
  * [3383](http://x2software.com/index.php/bugReports/3383): User Report  
  * [3643](http://x2software.com/index.php/bugReports/3643): User Report  

# 5.4.3 #
1/9/2016
* General Changelog / Developer Notes
  * Fixed a bug with web lead capture
  * Fixed a broken form layout in X2Touch
  * Fixed a bug in Lists

# 5.4.2 #
1/7/2016
* General Changelog / Developer Notes
  * X2Touch changes:
    * Added support for the Topics module
    * Module form layouts can now be customized from the new "Mobile App Form Editor" admin page
  * Improved error handling of automated email logging
  * Action History "Email From" filter now includes logged inbound emails
  * New option on the "Email Settings" admin page to enable List-Unsubscribe email header
  * Updated module delete functionality to clean up associated custom module summary widgets
  * Updated Process module to allow for financial information on any module type
* Tracked Bug Fixes:  
  * [3189](http://x2software.com/index.php/bugReports/3189): CDbCommand failed to execute the SQL statement: SQLSTATE[42S22]: Column not found: 1054 Unknown column 't.createDate' in 'having clause'  
  * [3283](http://x2software.com/index.php/bugReports/3283): file_exists(): open_basedir restriction in effect. File is not within the allowed path(s)
  * [3296](http://x2software.com/index.php/bugReports/3296): CDbCommand failed to execute the SQL statement: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'Admin' for key 'PRIMARY'  
  * [3300](http://x2software.com/index.php/bugReports/3300): array_flip(): Can only flip STRING and INTEGER values!  
  * [3302](http://x2software.com/index.php/bugReports/3302): CDbCommand failed to execute the SQL statement: SQLSTATE[HY000]: General error: 1366 Incorrect integer value: '' for column 'active' at row 1  
  * [3303](http://x2software.com/index.php/bugReports/3303): CDbCommand failed to execute the SQL statement: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'tags' in 'where clause'  
  * [3310](http://x2software.com/index.php/bugReports/3310): X2MergeableBehavior and its behaviors do not have a method or closure named "setMergedField".  
  * [3312](http://x2software.com/index.php/bugReports/3312): The system is unable to find the requested action "id".  
  * [3323](http://x2software.com/index.php/bugReports/3323): Trying to get property of non-object  
  * [3324](http://x2software.com/index.php/bugReports/3324): Eigenschaft "Contacts.Array ist nicht definiert."
  * [3330](http://x2software.com/index.php/bugReports/3330): Relationships labelling bug

# 5.4.1 #
12/18/2015
* General Changelog / Developer Notes
  * Fixed opened email display in action history
  * Fixed mass action menu displaying when scrolling up
  * Fixed 404 error caused by clicking on a user profile from the activity feed
  * Fixed avatars not displaying correctly
  * Fixes and improvements to X2Touch

# 5.4 #
12/17/2015

* Highlights
  * Platinum Edition Changes:
    * Added Role and permissions support to X2Packager
    * Configure user password complexity requirements
    * Failed login grid now maintains historical records
  * Professional Edition Changes:
    * Native X2CRM Android App (Beta)
      * Manage Contacts, Accounts, Opportunities and more
      * Get activity feed updates, comment on feed events, and post photos
      * View interactive Charts Dashboard
      * Access records from custom modules
    * Automatic inbound and outbound email logging in Email Inboxes
    * New email related triggers and conditions in X2Workflow
    * New email opened trigger and condition for X2Workflow
    * New export target options allowing data exports to an FTP or SSH server, Amazon S3, and Google Drive, as well as optionally compressing the export to a ZIP archive
    * Added ability to shard static assets over multiple domains
  * Process module visualization now supports any record type
  * New "Favorites" widget replaces "Top Contacts", allowing quick access to frequently used records of any type
  * New top bar customization options:
    * Add links to external URLs
    * Add links to arbitrary records inside X2CRM
  * Improved importer link field controls allowing selection of attribute to match for linked records
* General Changelog / Developer Notes
  * Added ability to rename folders in Docs module
  * Process stage order can now be edited without the risk of data loss
  * Back end code refactoring and stability improvements
  * Major translation updates to remove unused text
  * Fixed a bug where campaign text could be deleted when using inline editing
  * Fixed a bug that caused Calendar events to show up in the activity feed as "Calendar event not found"
  * Fixed a bug in module delete functionality that failed to remove associated Fields records
  * Fixed a bug in Email Inboxes when selecting and moving messages to a folder whose name uses UTF8 encoded characters
* Tracked Bug Fixes:  
  * [2956](http://x2software.com/index.php/bugReports/2956): Undefined index: notificationUsers  
  * [3046](http://x2software.com/index.php/bugReports/3046): mb_convert_encoding(): Illegal character encoding specified  
  * [3082](http://x2software.com/index.php/bugReports/3082): User Report  
  * [3084](http://x2software.com/index.php/bugReports/3084): Undefined index: wide  
  * [3085](http://x2software.com/index.php/bugReports/3085): Report settings could not be saved.  
  * [3089](http://x2software.com/index.php/bugReports/3089): Undefined index: notificationUsers  
  * [3095](http://x2software.com/index.php/bugReports/3095): Report settings could not be saved.  
  * [3099](http://x2software.com/index.php/bugReports/3099): Missing argument 1 for Google_Client::authenticate(), called in GoogleAuthenticator.php on line 146 and defined  
  * [3115](http://x2software.com/index.php/bugReports/3115): Undefined offset: 0  
  * [3131](http://x2software.com/index.php/bugReports/3131): Syntax error or access violation: 1055 Expression #1 of SELECT list is not in GROUP BY clause and contains nonaggregated column 'X2CRM.t.id' which is not functionally dependent on colu  
  * [3133](http://x2software.com/index.php/bugReports/3133): Property "Testmodule.c_Client_ID" is not defined.  
  * [3137](http://x2software.com/index.php/bugReports/3137): Missing argument 1 for Google_Client::authenticate(), called in GoogleAuthenticator.php on line 146 and defined  
  * [3150](http://x2software.com/index.php/bugReports/3150): EmailConfigException
  * [3203](http://x2software.com/index.php/bugReports/3203): fgetcsv(): delimiter must be a character  
  * [3244](http://x2software.com/index.php/bugReports/3244): Trying to get property of non-object  
  * [3255](http://x2software.com/index.php/bugReports/3255): Quick Create Form
  * [3258](http://x2software.com/index.php/bugReports/3258): No such file or directory  
  * [3259](http://x2software.com/index.php/bugReports/3259): French date picker issue
  * [3265](http://x2software.com/index.php/bugReports/3265): array_combine(): Both parameters should have at least 1 element  

# 5.3.1 #
11/5/2015

* General Changelog / Developer Notes:
  * Fixed bug which caused "Discard unsaved changes?" dialog to display incorrectly in certain cases
  * Fixed bug in module import which could break link-type fields
  * Fixed bug in records export which caused the custom delimiter/enclosure to be ignored in the resulting CSV header
  * Fixed bug in records import causing Action descriptions to be ignored if the ID field was mapped
  * Fixed bug in records import which attempted to insert ActionText even if the Actions model failed validation, resulting in a constraint violation
* Tracked Bug Fixes:  
  * [3099](http://x2software.com/index.php/bugReports/3099): Missing argument 1 for Google_Client::authenticate(), called in /var/www/html/crm/protected/components/GoogleAuthenticator.php on line 146 and defined 
  * [3115](http://x2software.com/index.php/bugReports/3115): Undefined offset: 0  

# 5.3 #
10/15/2015

* Highlights
  * Professional Edition Changes:
    * Google+ integration:
      * Google+ Profile widget allows display of Google+ Profile data on record view screens.
      * Google+ Profile search feature
    * New X2Workflow Splitter enables concurrent execution of flow branches, simplifying creation of complex flow logic.
    * Added mass actions to Email Inbox profile widget. It's now possible to delete, log, and flag emails directly from the profile page.
    * X2Workflow Remote API Call action now supports nested JSON payloads
  * Default processes can now be set on a per module basis.
  * New "Redirect URL" option in web form designer
  * Tags column now available in more module grid views, including custom modules.
  * Added ability to select and move multiple Docs or folders at a time from the Docs grid view
* General Changelog / Developer Notes:
  * The Calendar Module now only displays events and actions. Logged time, logged calls, comments, and emails will no longer show in the calendar view.
  * In order to simplify Lead conversion reporting, Leads are now preserved after conversion to Contact or Opportunity and designated as "Converted". Two new fields have been added, "Converted" and "Conversion Date", which get set automatically upon Lead conversion.
  * Fixed bug which prevented Actions from being synced with Google Calendar 
  * SASS-generated CSS has been minified. CSS customizations can be made by regenerating the CSS
    from customized SASS.
  * Updated Google PHP API client Library to version 1.1.5
  * Fixed bug which broke inline editing on Service Cases with associated Contacts.
  * Fixed bug which caused an error when attempting to create a reminder from the Action update page
  * Added back red asterisks appearing alongside required fields on record edit pages.
  * Fixed off-by-one bugs in X2Workflow Periodic Trigger scheduling
  * "Disable automatic record tagging?" option moved out of user preferences. Automatic record tagging can now be disabled globally from the "General Settings" admin page.
  * Added print view feature to Opportunities
* Tracked Bug Fixes:  
  * [2877](http://x2software.com/index.php/bugReports/2877): "Assigned To" grid filter cannot be used to retrieve multi-assigned records  
  * [2888](http://x2software.com/index.php/bugReports/2888): array_merge(): Argument #1 is not an array  
  * [2889](http://x2software.com/index.php/bugReports/2889): Invalid argument supplied for foreach()  
  * [2891](http://x2software.com/index.php/bugReports/2891): Trying to get property of non-object  
  * [2896](http://x2software.com/index.php/bugReports/2896): Unable to resolve the request "/index.php/contacts/contacts/weblead".  
  * [2931](http://x2software.com/index.php/bugReports/2931): Trying to get property of non-object  
  * [2969](http://x2software.com/index.php/bugReports/2969): AdminController cannot find the requested view "deleteDropdowns".  
  * [2972](http://x2software.com/index.php/bugReports/2972): Property "Docs.editPermissions" is not defined.  
  * [2980](http://x2software.com/index.php/bugReports/2980): Property "Contacts.private" is not defined.  
  * [2984](http://x2software.com/index.php/bugReports/2984): Undefined index: notificationUsers  

# 5.2.1 #
8/26/2015

* General Changelog / Developer Notes:
  * Fixed backwards compatibility issue affecting custom modules
  * Added public info settings validation which prevents issues with public base URI/URL formatting
  * Fixed bug which caused logged calls to display in the Actions module list view
  * Fixed bug affecting cookie-based web tracking on servers whose hostname lacked a subdomain
  * New admin option to upload a login screen logo (Professional Edition Only)

# 5.2 #
8/19/2015

* Highlights
  * Platinum Edition Changes
    * New auto-merge admin tool automatically merges duplicate records
  * Professional Edition Changes
    * New Execute Workflow sidebar widget enables execution of individual workflows created in the X2Workflow Studio Designer
    * New Email Inbox Profile Dashboard widget
    * New grid view mass actions:
      * Mass convert leads
      * Mass publish comments, actions, calls, and logged time
      * Mass create relationships
    * New importer option to update existing records on import
  * New Topics module offers an integrated discussion board
    * Create topic discussion threads
    * Relate topics to Contact and Account records
    * Tag/pin topics
  * New Profile Dashboard widgets:
    * Docs Summary
    * New Web Leads
  * New mass dedupe admin tool simplifies duplicate record management
  * Tags can now be exported
  * Docs can now be grouped inside nestable folders
  * New drag and drop media upload options and simplified email image attachment
  * New Web Activity record view widget
  * New interactive tips system provides step-by-step feature introductions 
  * Application-wide UI improvements to record view layouts
  * New Module-specific theming options
  * New Edit Global CSS admin page
  * Themes selected on the preferences page now apply to the login screen and to X2Touch
  * New preferences option to apply background image to login screen
  * Admin index organizational improvements 
* General Changelog / Developer Notes:
  * "X2Flow" has been renamed "X2Workflow"
  * Contact "Record Aliases" have been renamed "Social Profiles"
* Tracked Bug Fixes:  
  * [2631](http://x2software.com/index.php/bugReports/2631): Invalid address:   
  * [2731](http://x2software.com/index.php/bugReports/2731): import mapping name  
  * [2769](http://x2software.com/index.php/bugReports/2769): Undefined index:   
  * [2778](http://x2software.com/index.php/bugReports/2778): AdminController and its behaviors do not have a method or closure named "fixupImportedModuleDropdowns". 
  * [2862](http://x2software.com/index.php/bugReports/2862): Invalid argument supplied for foreach()  
  * [2872](http://x2software.com/index.php/bugReports/2872): fopen(/public_html/protected/data/records_export.csv): failed to open stream: Permission denied  

# 5.0.9 #
7/13/2015

* General Changelog / Developer Notes:
  * Important security updates
  * "Post to Activity Feed" flow action changes:
    * New "User" option determines the owner of the feed to which the post will be added. This allows for the creation of social posts.
    * New post "Visibility" option
  * Fixed bug which prevented  "My Actions" widget from remaining hidden after clicking the close button
  * Fixed bug which prevented products and quotes details from displaying in the Inline Relationships Widget
  * Web lead form submit button now disabled after form submission, preventing duplicate submissions
  * SMTP authentication failure now halts campaign
  * Invalid email addresses in X2Flow action menus now trigger validation warnings upon saving or triggering flows
  * Fixed Email module bug which caused forwarded email attachments to be corrupted
  * Fixed Reports bug which prevented column sort order from being saved
  * Fixed Reports bug which caused an error to occur upon report generation if the column "Action Description" of the Actions module was selected
  * Date function attributes and attributes of related records now display properly in emailed, exported, and printed reports
  * Fixed custom module bug which prevented users with "Assigned Only" access from updating and deleting custom module records to which they were assigned
  * The "Transactional View" has been renamed "List View"
  * Updated PHPMailer to version 5.2.10
* Tracked Bug Fixes:  
  * [2458](http://x2software.com/index.php/bugReports/2458): Undefined offset: 0  
  * [2611](http://x2software.com/index.php/bugReports/2611): Trying to get property of non-object  
  * [2653](http://x2software.com/index.php/bugReports/2653): Trying to get property of non-object  
  * [2656](http://x2software.com/index.php/bugReports/2656): Undefined offset: 0  
  * [2672](http://x2software.com/index.php/bugReports/2672): Undefined offset: 2  

# 5.0.8 #
6/2/2015

* General Changelog / Developer Notes:
  * Fixed issue which caused incorrect naming of uploaded media files
  * New exporter option to include merged duplicate records in export, disabled by default
  * Numerous bug fixes to global import/export tool
  * name field of related contacts created on record import will now be generated automatically from firstName and lastName fields
  * Flows now triggered when records are updated via the REST API
  * Fixed bug in X2Flow Designer which would cause flow action configuration menus to load incorrectly when quickly switching between them
  * Fixed REST API bug which would cause pagination to be disabled if max page size was set to 0
  * Placeholder values no longer submitted with X2Touch forms
  * Fixed bug in email client which prevented email conversation from being automatically appended to new email when replying
* Tracked Bug Fixes:  
  * [2422](http://x2software.com/index.php/bugReports/2422): User invitation redirects to login screen  
  * [2458](http://x2software.com/index.php/bugReports/2458): Undefined offset: 0  
  * [2530](http://x2software.com/index.php/bugReports/2530): Undefined property "Services.contactIdModel".  

# 5.0.7 #
5/12/2015

* General Changelog / Developer Notes:
  * Fixed bug in the application updater tool which would cause certain files to be incorrectly deleted on case-insensitive file systems
  * Fixed bug in flow deletion action menu link, changelog deletion button, and "Go Invisible" button related to 5.0.5 introduction of CSRF token validation
  * Fixed bug in mass update tool which prevented value for boolean type fields from being set properly
  * Automatic record tagging feature now ignores CSS color hex codes
  * API VoIP action no longer retrieves phone numbers of hidden contacts
  * X2Flow tag triggers no longer fired on record merge
  * Added web form deletion validation dialog
* Tracked Bug Fixes:  
  * [2359](http://x2software.com/index.php/bugReports/2359): Undefined index: model  
  * [2398](http://x2software.com/index.php/bugReports/2398): Property "TwitterApp.server" is not defined.  
  * [2400](http://x2software.com/index.php/bugReports/2400): Undefined offset: 11  
  * [2416](http://x2software.com/index.php/bugReports/2416): Autocomplete field broken in grid view mass update dialog for link type fields.  

# 5.0.6 #
4/16/2015

* General Changelog / Developer Notes:
  * Contact tracking key can now be set through the REST API
  * Added CSRF token validation to Google login
  * Fixed bug in the REST API search action which would cause all results to be returned if _or parameter was present
  * Fixed bug preventing X2Flow shortcodes from evaluating if present in X2Flow record attribute inputs
  * Fixed bug in X2Flow Reassign Record flow action which caused reassignment to fail
  * Leads conversion now displays error output if conversion fails due to field validation
  * Permissions bug fixes:
      * Campaigns were inaccessible if user's view permissions were set to "Only Assigned"
      * Docs delete button wouldn't display if user's delete permissions were set to "Only Assigned"
      * Users with "Only Assigned" delete permissions couldn't mass delete records through the grid view
  * Reports would fail to save if a report condition contained an unchecked check box
  * Fixed Verify Credentials feature to work with Yahoo, Outlook, Mandrill, and Sendgrid credential types
* Tracked Bug Fixes:  
  * [2329](http://x2software.com/index.php/bugReports/2329): Undefined index: webFormId  
  * [2343](http://x2software.com/index.php/bugReports/2343): Custom module summary widget links incorrectly generated  

# 5.0.5 #
3/18/2015

* General Changelog / Developer Notes:
  * Fixed CSRF vulnerability described here: http://packetstormsecurity.com/files/130820/X2Engine-5.0.4-Platinum-Edition-Cross-Site-Request-Forgery.html
* Tracked Bug Fixes:  
  * [2267](http://x2software.com/index.php/bugReports/2267): X2Flow actions which require a model param should check for presence of param before attempting to access it  
  * [2280](http://x2software.com/index.php/bugReports/2280): CDbCommand failed to execute the SQL statement: SQLSTATE[42S22]: Column not found: 1054 Unknown column 't.actionDescription' in 'order clause'  
  * [2286](http://x2software.com/index.php/bugReports/2286): CDbCommand failed to execute the SQL statement: SQLSTATE[22001]: String data, right truncated: 1406 Data too long for column 'fileName' at row 1  
  * [2290](http://x2software.com/index.php/bugReports/2290): Property "EmailInboxes.updatedBy" is not defined.  

# 5.0.5b #
3/10/2015

* Highlights
  * Professional Edition Changes
    * New Charts drilldown feature allows you to quickly get a list of records associated with charted data points
    * New scatter plot charting option 
    * New column aggregation option for time series charts
    * Improved reports print view adds new configuration options and allows inclusion of charts
    * New X2Flow Periodic Trigger allows flows to be triggered according to a schedule
  * Redesigned Web Form Designer
    * More intuitive and compact interface
    * Improved style of default web form
  * Record print view given configuration options and an improved look
  * Details of related records can now be viewed from the inline relationships widget
  * New option in transactional view widgets to display actions of related records
* General Changelog / Developer Notes:
  * The response of API calls made through X2Flow's API Call Action can now be retrieved in subsequent flow actions with the token "{returnValue}" 
  * The X2Flow trigger logs table now has a maximum record count which can be configured from the new X2Flow Settings admin page. The default maximum is 1 million records
  * Fixed bug preventing data types of columns from changing when updated through the Manage Fields page
* Tracked Bug Fixes:  
  * [2170](http://x2software.com/index.php/bugReports/2170): Trying to get property of non-object  
  * [2176](http://x2software.com/index.php/bugReports/2176): Undefined index: doNotEmailLink  
  * [2177](http://x2software.com/index.php/bugReports/2177): Trying to get property of non-object  
  * [2213](http://x2software.com/index.php/bugReports/2213): Class:  not found.  
  * [2215](http://x2software.com/index.php/bugReports/2215): could not generate checksum  
  * [2219](http://x2software.com/index.php/bugReports/2219): Undefined variable: inlineEdit  

# 5.0.4 #
2/5/2015

* General Changelog / Developer Notes:
  * Fixed bug affecting X2Flow API actions made after X2Flow Wait actions
* Tracked Bug Fixes:
  * [2149](http://x2software.com/index.php/bugReports/2149): Missing argument 2 for FieldFormatter::renderInt(), called in /opt/bitnami/apps/x2crm/htdocs/protected/components/FieldFormatter.php on line 348 and defined  
  * [2152](http://x2software.com/index.php/bugReports/2152): User Report  
  * [2153](http://x2software.com/index.php/bugReports/2153): X2Flow  Compare Attribute using "In List" with dropdown fields fails to save  
  * [2156](http://x2software.com/index.php/bugReports/2156): FieldFormatter and its behaviors do not have a method or closure named "getFields".  
  * [2158](http://x2software.com/index.php/bugReports/2158): Unable to resolve the request "actions/id/complete".  
  * [2159](http://x2software.com/index.php/bugReports/2159): Trying to get property of non-object  
  * [2160](http://x2software.com/index.php/bugReports/2160): Undefined variable: render  
  * [2161](http://x2software.com/index.php/bugReports/2161): User Report  

# 5.0.3 #
1/30/2015

* Highlights
  * Added ability to export user changelog to CSV
  * Record importer now provides a progress bar
* General Changelog / Developer Notes:
  * Fixed response handling when verifying application credentials
* Tracked Bug Fixes:
  * [1853](http://x2software.com/index.php/bugReports/1853): Undefined variable: report  
  * [1855](http://x2software.com/index.php/bugReports/1855): Undefined variable: retVal  
  * [1896](http://x2software.com/index.php/bugReports/1896): Argument 2 passed to X2Model::renderModelInput() must be an instance of Fields, null given, called in marketing/protected/models/X2Model.php on line 2298 and defined  
  * [1899](http://x2software.com/index.php/bugReports/1899): User Report  
  * [1905](http://x2software.com/index.php/bugReports/1905): Class: Charts not found.  
  * [2107](http://x2software.com/index.php/bugReports/2107): Property "X2ButtonColumn.name" is not defined.  
  * [2115](http://x2software.com/index.php/bugReports/2115): PublisherProductsTab and its behaviors do not have a method or closure named "renderPartial".  
  * [2119](http://x2software.com/index.php/bugReports/2119): User Report  
  * [2120](http://x2software.com/index.php/bugReports/2120): Invalid email address list

# 5.0.3b #
1/20/2015

* Highlights
  * New Record Transactional View
    * Provides an alternate view of a record's action history
    * Individual widgets for each type of interaction (Calls, Emails, Actions, etc.)
  * Professional Edition Changes:
    * Charts created through the Reports Module can now be added to the profile dashboard
  * Profile dashboard and record view column widths can now be adjusted
  * Email Contact X2Flow action has a new option to enable email logging and tracking
  * Application-wide icon update
* General Changelog / Developer Notes:
  * Updated Yii to version 1.1.16
* Tracked Bug Fixes:
  * [1994](http://x2software.com/index.php/bugReports/1994): Unable to resolve the request "accounts/view/id".  
  * [2004](http://x2software.com/index.php/bugReports/2004): CDbCommand failed to execute the SQL statement: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '' for key 'c_email2'  
  * [2007](http://x2software.com/index.php/bugReports/2007): The system is unable to find the requested action "webleadForm".  
  * [2008](http://x2software.com/index.php/bugReports/2008): CDbCommand failed to execute the SQL statement: SQLSTATE[42S22]: Column not found: 1054 Unknown column 't.actionDescription' in 'order clause'  
  * [2009](http://x2software.com/index.php/bugReports/2009): CDbCommand failed to execute the SQL statement: SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '' at line 3  
  * [2048](http://x2software.com/index.php/bugReports/2048): htmlspecialchars(): Invalid multibyte sequence in argument  
  * [2049](http://x2software.com/index.php/bugReports/2049): 
  * [2086](http://x2software.com/index.php/bugReports/2086):   



# 5.0.2 #
12/22/2014

* General Changelog / Developer Notes:
  * Platinum Edition Changes:
    * New login history export tool on the Advanced Security page
  * Professional Edition Changes:
    * Added a button to remove individual emails from newsletter recipient lists
    * Email Manager module renamed "Email"
    * Fixed AnonContact entries on activity feed
  * Fixed bug preventing lead related records from transferring to target record upon conversion
  * Added safeguard to prevent administrator account from being disabled
* Tracked Bug Fixes:
  * [1938](http://x2software.com/index.php/bugReports/1938): Class:  not found.  
  * [1940](http://x2software.com/index.php/bugReports/1940): imap_get_quotaroot(): c-client imap_getquotaroot failed  
  * [1941](http://x2software.com/index.php/bugReports/1941): Unable to resolve the request "x2Leads/id/convert".  
  * [1945](http://x2software.com/index.php/bugReports/1945): Inline edit checkbox fields always checked.  
  * [1952](http://x2software.com/index.php/bugReports/1952): TimeSeriesForm and its behaviors do not have a method or closure named "getName".  
  * [1955](http://x2software.com/index.php/bugReports/1955): Unable to resolve the request "charts/charts/index".  
  * [1957](http://x2software.com/index.php/bugReports/1957): Unable to resolve the request "emailInboxes/updateSharedInbox/id/sharedInboxesIndex".  
  * [1967](http://x2software.com/index.php/bugReports/1967): trim() expects parameter 1 to be string, array given  
  * [1968](http://x2software.com/index.php/bugReports/1968): TimeSeriesForm and its behaviors do not have a method or closure named "getName".  
  * [1971](http://x2software.com/index.php/bugReports/1971): TimeSeriesForm and its behaviors do not have a method or closure named "getName".  
  * [1972](http://x2software.com/index.php/bugReports/1972): Class:  not found.  
  * [1984](http://x2software.com/index.php/bugReports/1984): User Report  

# 5.0.1 #
12/9/2014

* General Changelog / Developer Notes:
  * New Outlook and Yahoo email credential types
  * Leads name field is no longer overwritten with first name and last name if name field is already set
* Tracked Bug Fixes:
  * [1887](http://x2software.com/index.php/bugReports/1887): Array to string conversion  
  * [1888](http://x2software.com/index.php/bugReports/1888): Array to string conversion  
  * [1903](http://x2software.com/index.php/bugReports/1903): Undefined variable: active  
  * [1910](http://x2software.com/index.php/bugReports/1910): Trying to get property of non-object  
  * [1920](http://x2software.com/index.php/bugReports/1920): Property "Admin.imapPollTimeout" is not defined.  
  * [1923](http://x2software.com/index.php/bugReports/1923): Undefined variable: active  
  * [1924](http://x2software.com/index.php/bugReports/1924): Property "Admin.maxFailedLogins" is not defined.  

# 5.0 #
12/5/2014

* Highlights
  * New in _Platinum Edition_
    * Advanced Security Tools
      * Ban, whitelist, or blacklist IP addresses
      * View a log of user logins and failed login attempts
      * Lock out IPs or users after a certain number of failed login attempts
  * New in _Professional Edition_
    * Integrated Email Client
      * Manage inbound and outbound emails through private and/or shared email inboxes
      * Automatically or manually Log emails to the action histories of associated records
      * View contact information via hovering tooltip
      * Create contacts and actions on the fly from email address links
    * Reports 2.0
      * New summation and rows & columns reports
      * Drill down into summation report groups
      * Improved report filtering, sorting, and column selection
      * Create reports on almost any record type
      * Report on attributes of related records
    * Charts 2.0
      * Customizable charting dashboard
      * Generate gauge, bar, line, pie, and time series charts built from data in saved reports
    * X2Packager
      * Export and package modules, custom fields, flows, themes, and more
      * Import packages to instantly inherit a pre-built X2Engine environment
    * Record merge tool
      * Interactively mass merge contact or account records from the grid view or duplicate checker
    * X2Graph
      * Explore and edit record relationships through an interactive relationships graph
      * Visualize relationships across all records simultaneously
      * Dynamically add relationship graph nodes and edges
  * New in _Open Source Edition_
    * User Interface Revamp
      * Vastly improved app themability
        * Prepackaged dark and light themes
        * Simplified theme color selection
        * Themable login screen
        * Login animation
    * X2Touch 2.0
      * Refreshed user interface
    * Inline Editing
      * Edit record fields from the record view page
    * Module Deep Rename
      * All references to the module are now fully replaced with your custom name, including in actionable events, dropdown menus, and relationships.
    * Importer 3.0
      * Automatic field detection
      * Configurable import batch size
      * Greatly reduced import time
    * Improved campaign click tracking
      * Automatically generate email redirect links
      * Track email clicks with the campaign chart and campaign progress grids
    * Record aliasing
      * Add multiple email addresses, phone numbers, and social media handles to Contact records
      * Click-to-call/chat Skype aliases
    * Twitter integration
      * New contact Twitter feed widget allows you to view the Twitter feed for any of the contact's Twitter aliases
    * Calendar event copying
      * Duplicate a Calendar event from the event tooltip
    * Duplicate checker now detects both contact and account duplicates
    * Convert leads to opportunities or contacts
* Tracked Bug Fixes:
  * [1553](http://x2software.com/index.php/bugReports/1553): "Do Not Email Page" does not save  
  * [1554](http://x2software.com/index.php/bugReports/1554): explode() expects parameter 2 to be string, array given  
  * [1555](http://x2software.com/index.php/bugReports/1555): Invalid argument supplied for foreach()  
  * [1562](http://x2software.com/index.php/bugReports/1562): The system is unable to find the requested action "www.google.com".  
  * [1565](http://x2software.com/index.php/bugReports/1565): CDbCommand failed to execute the SQL statement: SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '{"contacts\  
  * [1567](http://x2software.com/index.php/bugReports/1567): Class: AnonContact not found.  
  * [1572](http://x2software.com/index.php/bugReports/1572): Unable to resolve the request "actions/viewAll/showActions/incomplete".  
  * [1574](http://x2software.com/index.php/bugReports/1574): Trying to get property of non-object  
  * [1578](http://x2software.com/index.php/bugReports/1578): Undefined variable: users  
  * [1584](http://x2software.com/index.php/bugReports/1584): Undefined variable: newFields  
  * [1589](http://x2software.com/index.php/bugReports/1589): CDbCommand failed to execute the SQL statement: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '3' for key 'PRIMARY'  
  * [1596](http://x2software.com/index.php/bugReports/1596): Undefined variable: fmtNumber  
  * [1621](http://x2software.com/index.php/bugReports/1621): Non-static method Tags::normalizeTags() should not be called statically, assuming $this from incompatible context  
  * [1648](http://x2software.com/index.php/bugReports/1648): Invalid address:   
  * [1659](http://x2software.com/index.php/bugReports/1659): User Report  
  * [1660](http://x2software.com/index.php/bugReports/1660): Undefined index: first  
  * [1688](http://x2software.com/index.php/bugReports/1688): Undefined index: last  
  * [1697](http://x2software.com/index.php/bugReports/1697): htmlspecialchars(): Invalid multibyte sequence in argument  
  * [1706](http://x2software.com/index.php/bugReports/1706): Undefined variable: email  
  * [1808](http://x2software.com/index.php/bugReports/1808): Undefined index: fingerprint  
  * [1849](http://x2software.com/index.php/bugReports/1849): Undefined variable: l  
  * [1856](http://x2software.com/index.php/bugReports/1856): Trying to get property of non-object  
  * [1857](http://x2software.com/index.php/bugReports/1857): Undefined variable: imporMap  
  * [1876](http://x2software.com/index.php/bugReports/1876): ContactsNameBehavior and its behaviors do not have a method or closure named "setName". 

# 4.3 #
10/9/2014

* General changelog/developer notes
  * Updated calendar widget to be hidden and shown according to persistent settings
  * Fixed bug preventing tags from being added to leads
* Tracked Bug Fixes:
  * [1659](http://x2software.com/index.php/bugReports/1659): User Report  
  * [1706](http://x2software.com/index.php/bugReports/1706): Undefined variable: email  

# 4.3b #
9/26/2014

* Highlights
  * New calendar profile widget
  * The clock widget now allows you to choose from analog, digital, and digital 24-hour
* General changelog/developer notes
  * Fixed inline process widget in Opportunities module
  * Custom assignment fields now produce links in grid views
  * Importer Bugfixes
    * Action.description will now be automatically mapped
    * Fixed bug when uploading import maps
    * Added better handling of empty rows
  * Fixed purification issue with insertableAttributes
* Tracked Bug Fixes:
  * [1621](http://x2software.com/index.php/bugReports/1621): Non-static method Tags::normalizeTags() should not be called statically, assuming $this from incompatible context  
  * [1648](http://x2software.com/index.php/bugReports/1648): Invalid address:  
  * [1660](http://x2software.com/index.php/bugReports/1660): Undefined index: first  
  * [1688](http://x2software.com/index.php/bugReports/1688): Undefined index: last  
  * [1697](http://x2software.com/index.php/bugReports/1697): htmlspecialchars(): Invalid multibyte sequence in argument  

# 4.2.1 #
9/11/2014

* General changelog/developer notes
  * Fixed bug in importer preventing equal valued fields from being set
* Tracked Bug Fixes:
  * [1584](http://x2software.com/index.php/bugReports/1584): Undefined variable: newFields
  * [1589](http://x2software.com/index.php/bugReports/1589): CDbCommand failed to execute the SQL statement: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '3' for key 'PRIMARY'
  * [1596](http://x2software.com/index.php/bugReports/1596): Undefined variable: fmtNumber

# 4.2 #
9/3/2014

* Highlights
  * New feature to validate email credentials from the 'Manage Apps' page
  * General record import improvements:
    * Added a loading throbber to indicate activity
    * Added a timeout warning when max_execution_time is set to 30 seconds or less
  * Improved contact lists grid view
* General changelog/developer notes
  * Fixed bug in importer preventing action descriptions from being imported
  * Improved error reporting and handling on media upload tool
  * Improved currency validation and fixed consistency issues when changing a products currency
  * Fixed error reporting when attempting to upgrade without a key
  * _(Platinum Edition)_ Updated Fingerprint index to display a human-readable timezone string
  * Default permissions will now be created when importing a module
  * Fixed Account link type fields for Contacts and Opportunities created on the Quick Create page
  * Fixed links in 'My Actions' widget
  * Fixed bug in Google Calendar Sync which prevented calendar and action history from updating
    after publishing events or actions
* Tracked Bug Fixes:  
  * [1492](http://x2software.com/index.php/bugReports/1492): User Report  
  * [1553](http://x2software.com/index.php/bugReports/1553): "Do Not Email Page" does not save  
  * [1554](http://x2software.com/index.php/bugReports/1554): explode() expects parameter 2 to be string, array given  
  * [1555](http://x2software.com/index.php/bugReports/1555): Invalid argument supplied for foreach()  
  * [1562](http://x2software.com/index.php/bugReports/1562): The system is unable to find the requested action "www.google.com".  
  * [1565](http://x2software.com/index.php/bugReports/1565): CDbCommand failed to execute the SQL statement: SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '{"contacts\  
  * [1567](http://x2software.com/index.php/bugReports/1567): Class: AnonContact not found.  
  * [1572](http://x2software.com/index.php/bugReports/1572): Unable to resolve the request "actions/viewAll/showActions/incomplete".  
  * [1574](http://x2software.com/index.php/bugReports/1574): Trying to get property of non-object  
  * [1578](http://x2software.com/index.php/bugReports/1578): Undefined variable: users  

# 4.2b (beta) #
8/5/2014

* Highlights
  * Improvement to role access editor (Professional Edition):
    * New user interface enables more fine-grained control over role-based permissions
  * Profile widget improvements:
    * New website viewer widget
    * Leads module grid widget
    * Can now create grid widgets for custom modules
    * Can now clone, rename, and delete profile widgets
    * Persistent grid widget filters
    * Quick Contact widget now provides appropriate input fields for all required fields
  * Grid view improvements (Contacts module only):
    * Can now select all records on all pages
    * Perform updates on thousands of records at a time
  * X2Flow improvements (Professional Edition):
    * Improved X2Flow Remote API Call Action supports custom request headers 
    * New "Has Tags" flow condition
  * Calendar improvements:
    * New weekly agenda view
    * New customizable event subtype and status dropdowns
    * Can now customize event color dropdown
    * _(Platinum Edition)_ New share/export URL feature for read-only integration with calendar clients that support the iCalendar format
  * Importer improvements:
    * Added preset import maps to transfer records from other systems
    * Action descriptions can now be imported/exported
    * Actions associations will now be verified to ensure the type is known to X2 and that the associated record exists
  * Static pages can now be created from existing Docs instead of only a new Doc
  * New [access-model-by-attribute-conditions](http://wiki.x2engine.com/wiki/REST_API_Reference#Direct_Manipulation_by_Attributes) method in the REST API
  * Fields Manager Improvements
    * Sorting and filtering in the fields grid
    * New "custom" field type allows creating unique field view widgets in HTML
    * Custom and modified fields highlighted with different colors
* General changelog/developer notes
  * Patched file upload filter bypass vulnerability
  * Fixed missing link to modify Doc permissions when logged in as admin
  * Fixed issue that caused phone number links to be prepended international dialing codes unconditionally
  * Updated the web lead form to search for duplicate contacts on all custom Contact email fields
  * Fixed issue preventing Automatic Updates settings form from being saved
  * Fixed issue which caused process funnel record counts to be incorrect
* Tracked Bug Fixes:
  * [1401](http://x2software.com/index.php/bugReports/1401): Undefined index: tags 

# 4.1.7 #
7/15/2014

* General changelog/developer notes
  * Fixed issues in the importer which prevented fields from being set when a default field value was given
  * Fixed error in Form Editor which prevented scenario from being saved
  * Fixed bug in the Campaign Bulk Mailer which caused an incorrect error to be reported
  * Fixed bug in legacy API which incorrectly restricted search results
  * Fixed bug in process pipeline/funnel views which prevented contact records from displaying even if the current user had permission to view them
  * Fixed bug in process funnel view which prevented per-stage grid views from updating when a different process was selected

# 4.1.6 #
7/10/2014

* Tracked Bug Fixes:  
  * https://github.com/X2Engine/X2Engine/issues/28

# 4.1.6b2 (beta) #
7/8/2014

* Highlights
  * (Professional Edition) Improved activity feed reports with a page to manage reports and the ability to send a test report
  * Process UI improvement: Quickly switch between processes from the funnel and pipeline views
* General changelog/developer notes
  * Fixed regex matching on imported ids
  * Added missing phone type in Field Manager

# 4.1.6b (beta) #
7/3/2014

* Highlights
  * New "available" lead routing option:
    * Users can set online/offline availability, i.e. when they go on vacation
    * Lead routing can be configured to respect this option, i.e. avoid assigning records to unavailable users
  * "Loading" status/visual overlay when adding fields
  * Global import/export tool now supports custom fields
  * Custom short-codes feature for templates and X2Flow: create an analogue of protected/components/x2flow/shortcodes.php in custom/ to define your own custom codes
  * Can rename media files
  * (Professional Edition) X2Flow emails can be configured to include a customizable "Do Not Email" link
  * (Platinum Edition) Reverse IP lookup in X2Identity
* General changelog/developer notes
  * X2Flow improvements (Professional Edition):
    * Update trigger no longer fired during creation of contact lists (this was a bug)
    * Triggers in general will fire less during times when not apropos
    * Flow configuration storage field is now LONGTEXT as opposed to TEXT, allowing it to store far greater and more sophisticated flows
    * Fix to "on_list" condition 
  * Contact added as default field in Opportunities, and inline emailer can be used on opportunity views
  * Miscellaneous bug fixes in:
    * Contacts can be properly moved between time zones 
    * Activity feed events report generation
    * Actions module
    * X2Identity (Platinum Edition)
      * The first Action created for a new anonymous contact is now correctly associated with it
      * Fingerprint attributes associated with a new anonymous contact are now being saved properly from the newsletter form
      * Fingerprint record is now handled properly on conversion from an anonymous contact to a contact, previously it would be unnecessarily deleted
    * X2GridView: header not hiding properly when scrolling over the bottom of the grid
    * Publisher: Event form not properly validating when clearing the association type field
    * Inline Emailer: switching templates while viewing quotes now works as intended 
    * Updater: post-completion redirect to the wrong page
    * Permissions: users who have "admin" access to a given module can export records of that module
    * X2GridView: Grid view no longer breaks from HTML tag truncation in text-type fields
    * Calendar: group-assigned events could not be edited by group members
    * X2Studio: critical internal-use-only fields are not available for the user to accidentally enter data into
    * Importer: will not fail when CSV contains multibyte characters but no byte order mark, or invalid multibyte sequences
  * Global validation bug fix: "required" rule now respected both on update and save 
  * (Professional Edition only) License key now viewable by administrators on the app info and updater settings pages
  * User-friendly error & feedback messages on the Edit Roles page
  * Added day of week to activity feed date headers 
* Tracked Bug Fixes:  
  * [1340](http://x2software.com/index.php/bugReports/1340): User Report  
  * [1345](http://x2software.com/index.php/bugReports/1345): CDbCommand failed to execute the SQL statement: SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens  

# 4.1.5 #
6/26/2014

* General changelog/developer notes:
  * Included several commits from internal tree that were missed in the previous release
  * Fixed MoneyMask bug: when unsupported currencies are in use, validation was failing

# 4.1.4 #
6/24/2014

* General changelog/developer notes:
  * Fixed bug in tagBehavior: added safeguard for no web session (i.e. in scope of web lead form submission)
* Tracked Bug Fixes:  
  * [1340](http://x2software.com/index.php/bugReports/1340): User Report

# 4.1.4b (beta) #
6/18/2014

* General changelog / developer notes:
  * Recognition for memory limit in requirements check script
  * Fixed action timers bug (duplicate timer records created via publisher)
  * Proper handling of completion/uncompletion of actions in X2Flow
  * Profile layout bug fix
* Tracked Bug Fixes:  
  * [1320](http://x2software.com/index.php/bugReports/1320): Importer broken  
  * [1343](http://x2software.com/index.php/bugReports/1343): User Report (XSS vulnerability)

# 4.1.3 #
6/6/2014

* Tracked Bug Fixes:  
  * [1304](http://x2software.com/index.php/bugReports/1304): JS broken on "full edit page" for actions  
  * [1307](http://x2software.com/index.php/bugReports/1307): Class: AnonContact not found.  
  * [1309](http://x2software.com/index.php/bugReports/1309): Class: Reports not found.

# 4.1.2 #
6/6/2014

* General Changelog / Developer Notes:
  * Fixed inline quotes form
* Tracked Bug Fixes:  
  * [1268](http://x2software.com/index.php/bugReports/1268): Trying to get property of non-object  
  * [1280](http://x2software.com/index.php/bugReports/1280): Emailed quotes not tracked properly  
  * [1295](http://x2software.com/index.php/bugReports/1295): Validation errors not shown when updating an opportunity 

# 4.1.2b (beta) #
5/30/2014

* General Changelog / Developer Notes:
  * (Platinum Edition): Fixes to X2Identity and browser fingerprinting
    * Scalability issues in X2Identity, specifically the browser fingerprint match query
    * Automatic removal of orphaned fingerprint records
  * Fixes/improvements to user management:
    * Validation rules; both username and user aliasing
    * User alias auto-populates with username
    * User update page
  * Small bug fixes in process funnel, record import
  * Action reminder notifications now available in the publisher
  * (Professional Edition) Fixed "Email Contact" X2Flow action for non-contact/non-action record types
  * Fixed calendar bug: multiply-assigned events show up multiple times (for each user calendar)
* Tracked Bug Fixes:
  * [1246](http://x2software.com/index.php/bugReports/1246): array_merge() [<a href='function.array-merge'>function.array-merge</a>]: Argument #2 is not an array  
  * [1247](http://x2software.com/index.php/bugReports/1247): Class:  not found.  
  * [1264](http://x2software.com/index.php/bugReports/1264): CDbCommand failed to execute the SQL statement: SQLSTATE[42S02]: Base table or view not found: 1146 Table 'giertsen_x2engine.x2_cron_events' doesn't exist

# 4.1.1 #
5/23/2014

* General Changelog / Developer Notes:
  * Activity feed JS bug fixes
  * Backwards compatibility fixes for ResponseBehavior and X2LinkableBehavior
  * Bug fixes in roles
  * Improvements to webhooks: better payload composition logic + safeguards for systems w/o cURL libraries
  * Lead conversion bug fix
  * (Platinum Edition) "Raw Input" API settings option not saving properly
  * "Linkable Behavior" + custom modules backwards incompatibility
  * Web lead form not respecting when "Create Lead" option is disabled

# 4.1 #
5/20/2014

* **Highlights**:
  * _Process_ module:
    * Powerful new "pipeline" view with drag and drop functionality, showing the combined deal value in each stage
    * Sales process stage colors can be customized by the end user
    * _(Professional Edition)_ New X2Flow triggers and actions for extending _Process_ with powerful automation capabilities
  * _(Platinum Edition)_ X2Identity 2.0:
    * View anonymous website visitors and their browser fingerprint parameters
    * See when anonymous visitors return to your website
  * 2nd-generation REST API (new):
    * _(Coming Soon)_ X2Engine will be on [Zapier](https://zapier.com/)!
    * _(Platinum Edition):_ Advanced API access settings
  * _Leads_ module (new)
    * Record basic contact information before it becomes a legitimate potential sale
    * Convert to an opportunity with a button press when ready
  * Activity Feed:
    * _Digest emails:_ get periodic emails notifying you of what's happening in your CRM
    * New dedicated activity feed page
  * CSV importer:
    * New feature enabling users to save and re-use import field mappings
    * Performance improvements and bug fixes
  * User management:
    * New password reset feature
    * New username change feature
    * Full support for multiple assignment in the permissions system
  * Email Templates:
    * Support added for many modules (including custom modules), where previously only contacts and quotes were supported
    * Template variable replacement added for the "To" field
    * User setting for default email template to use for each module
* General Changelog / Developer Notes:
  * Fixed layout issue: unauthenticated users can see "Top Contacts" and "Recent Items" portlets, in addition to broken links in the top bar
  * Fixed security loophole: if session expires, the client with the cookie would still be able to make one last successful request to the server
  * The permissions system has been revamped to properly handle muliple assignment and group-wide visibility settings
  * Fixed bug: rollback deletes preexisting linked records
  * Fixed bug (Professional Edition): recurring VoIP notification popups
  * Fixed bug: empty contact list when using "primary contact" campaign generator from Accounts
  * Fixed bug: Upon deletion, a user's actions and contacts were not all getting properly reassigned.
  * Fixed bug: import fails silently when "DO NOT MAP" specified for an attribute
  * Performance improvements to the model importer (previously was taking as long as ~2s/record on systems with very large datasets)
  * Fixed bug: deleting a dropdown without updating fields that reference it breaks grid views
  * Contacts with empty names now get "#{id}" name link in grid view
  * Fixed bug causing role exceptions to be applied to incorrect stages.
  * Fixed bug preventing deal reports from being filtered by Account.
  * Fixed a bug preventing filters from working in the Actions list view.
  * Fixed issue with phone numbers not being rendered from the grid view.
  * Fixed phone number field formatting issue
  * Fixed updater bug: unnecessary catching of suppressed errors in requirements check script
* Tracked Bug Fixes:
  * [582](http://x2software.com/index.php/bugReports/582): Duplicate info going into web leads  
  * [960](http://x2software.com/index.php/bugReports/960): No es posible resolver la solicitud "product/product/view"  
  * [1051](http://x2software.com/index.php/bugReports/1051): links with # in them get converted to tag search links  
  * [1183](http://x2software.com/index.php/bugReports/1183): Contacts and its behaviors do not have a method or closure named "getChanges".  
  * [1201](http://x2software.com/index.php/bugReports/1201): Unable to resolve the request "product/product/view".  
  * [1204](http://x2software.com/index.php/bugReports/1204): Cannot modify header information - headers already sent by (output started at protected/controllers/ProfileController.php:516)  
  * [1223](http://x2software.com/index.php/bugReports/1223): Cannot modify header information - headers already sent by (output started at protected/modules/actions/controllers/ActionsController.php:799)
* Bugs fixed in 4.1.1:
  * Lead conversion PHP error
  * (Platinum Edition) "Raw Input" API settings option not saving properly
  * "Linkable Behavior" + custom modules backwards incompatibility
  * Web lead form not respecting when "Create Lead" option is disabled

# 4.0.1 #
3/31/2014

* Fixed Bugs:
  * [1080](http://x2software.com/index.php/bugReports/1080): User Report
  * [1096](http://x2software.com/index.php/bugReports/1096): web tracking links broken
  * [1097](http://x2software.com/index.php/bugReports/1097): User Report
  * [1104](http://x2software.com/index.php/bugReports/1104): AccountCampaignAction and its behaviors do not have a method or closure named "redirect".
  * [1110](http://x2software.com/index.php/bugReports/1110): User Report
  * [1112](http://x2software.com/index.php/bugReports/1112): User Report
  * [1116](http://x2software.com/index.php/bugReports/1116): is_file(): open_basedir restriction in effect. File(/usr/share/pear/Users.php) is not within the allowed path(s): (/usr/wwws/users/tikeccbcgd:/usr/www/users/tikeccbcgd:/usr/home/tikeccbcgd:/usr/local/rmagic:/usr/www/users/he/_system_:/usr/share/php:/
  * [1130](http://x2software.com/index.php/bugReports/1130): User Report
  * [1137](http://x2software.com/index.php/bugReports/1137): User Report 
  * [1143](http://x2software.com/index.php/bugReports/1143): Unable to resolve the request "bugReports/1,142".
  * [1151](http://x2software.com/index.php/bugReports/1151): The system is unable to find the requested action "profile".  
  * [1154](http://x2software.com/index.php/bugReports/1154): User Report 

# 4.0 #
3/20/2014

* New in **Platinum Edition:**
  * Browser fingerprinting system supplements web activity tracker for when contacts have cookies disabled
  * Administrators can set default themes for all users
  * The ability to import/export themes
  * The ability to import and export flows from X2Flow
  * Partner branding template (for authorized partners)
* New in **Professional Edition:**
  * Improvements to the actions publisher:
    * New "products" tab, for logging the use of products in a project or with a contact (for example)
    * New "event" tab through which calendar events associated with the record can be created
    * Which tabs it displays can be customized
* Responsive UI replaces X2Touch and makes the application more easy to use on a mobile device
* Improved Relationships widget with the ability to link to any type of record, including custom modules
* New Administrative tools:
  * Can import any data type with the power and flexibility that was previously limited to contact imports
  * New simpler data export for modules that emulates the exporter previously limited to Contacts
  * Can customize the application name and description
* FTP-based file management for compatibility with systems where files and directories are not owned by the web server (documentation coming soon)
* New look & feel including new icon-based activity feed buttons and login page
* Bug fixes to the Marketing module, updater, and more:
  * [1043](http://x2software.com/index.php/bugReports/1043): Property "Media.title" is not defined.  
  * [1091](http://x2software.com/index.php/bugReports/1091): Array to string conversion 
  * Further improvements to the security fixes discovered earlier; see ["Multiple Vulnerabilities in X2Engine"](http://x2community.com/topic/1511-multiple-vulnerabilities-in-x2engine/#entry7354) for more information

# 3.7.5 #
3/10/2014

* Fixed Bugs:
  * [995](http://x2software.com/index.php/bugReports/995): array_combine(): Both parameters should have at least 1 element
  * [996](http://x2software.com/index.php/bugReports/996): file_get_contents(): Filename cannot be empty
  * [997](http://x2software.com/index.php/bugReports/997): Property "Media.title" is not defined.
  * [998](http://x2software.com/index.php/bugReports/998): CDbCommand failed to execute the SQL statement: SQLSTATE[HY093]: Invalid parameter number: parameter was not defined
  * [999](http://x2software.com/index.php/bugReports/999): CDbCommand failed to execute the SQL statement: SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your S
  * [1009](http://x2software.com/index.php/bugReports/1009): CDbCommand failed to execute the SQL statement: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '94f072b73c'
  * [1016](http://x2software.com/index.php/bugReports/1016): Invalid argument supplied for foreach()
  * [1017](http://x2software.com/index.php/bugReports/1017): Property "X2WebApplication.settingsProfile" is not defined.
  * [1038](http://x2software.com/index.php/bugReports/1038): Unable to resolve the request "contacts/id/https//www.lplconnect.com".

# 3.7.4 #
3/4/2014

* Fixed security holes listed in ["Multiple vulnerabilities in X2Engine"](http://hauntit.blogspot.com/2014/02/en-multiple-vulnerabilities-in-x2engine.html) published on [The HauntIT Blog](http://hauntit.blogspot.com/)
* Fixed Bugs:
  * [773](http://x2software.com/index.php/bugReports/773): If a user lacks edit permission on that field but that field has a default value (like in Service Cases) the default value will not save.  
  * [947](http://x2software.com/index.php/bugReports/947): Unable to resolve the request "quotes/id/update".  
  * [948](http://x2software.com/index.php/bugReports/948): nameId field of 'Sample Quote Template' doc is null  
  * [949](http://x2software.com/index.php/bugReports/949): Template attribute of quotes is not a proper nameId ref 
  * [977](http://x2software.com/index.php/bugReports/977): CDbCommand failed to execute the SQL statement: SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect DOUBLE value: '162.210.196.131'
* Fixed unlisted bugs:
  * Campaigns issues with listId being a malformed reference to list records, and improper validation (i.e. "List cannot be blank")
  * Broken download links/extreme slowness in contacts export tool

# 3.7.3 #
2/18/2014

* Users can add custom percentage type fields via the fields manager
* Minor/unlisted bugs fixed:
  * (Professional Edition) "Record viewed" X2Flow trigger wasn't working in Contacts
  * API failures due to Profile class not being auto-loaded
  * 404 error on "convert to invoice" button in Quotes
  * Pro-only link was displayed (incorrectly) in the Marketing module
  * Backwards compatibility safeguards in link type fields migration script
* Fixed Bugs:
  * [935](http://x2software.com/index.php/bugReports/935): Unable to resolve the request "products/id/update".
  * [939](http://x2software.com/index.php/bugReports/939): No es posible resolver la solicitud "docs/view/id"

# 3.7.3b #
2/14/2014

* Multiple security vulnerabilities patched in web forms, data import/export, and docs import/export
* "Lookup" fields performance and functionality restoration overhaul:
  * Search/sort works without sorting on columns in joined tables
  * All such fields store all the necessary data to create a link, eliminating joins in grid view queries
* More robust error handling in the module importer
* Consistent branding throughout app (see [release notes](RELEASE-NOTES.md) for full details)
* Date/time picker input widget now available in relevant grid view column filters
* New "action timer sum" field type computes/displays sums of time spent on a record.
* Fields editor has the ability to create indexes on fields
* New in Professional Edition:
  * "Case Timer" has been generalized to the "action timer" and is available in most modules now
  * Action timer editing interface available to admins and users with action backdating privileges
  * Case creation via the email dropbox (experimental)
* Fixed Bugs:  
  * [254](http://x2software.com/index.php/bugReports/254): User Report  
  * [800](http://x2software.com/index.php/bugReports/800): User Report  
  * [803](http://x2software.com/index.php/bugReports/803): Unable to resolve the request "financiala33/financiala33/index".  
  * [848](http://x2software.com/index.php/bugReports/848): Undefined variable: timestamp  
  * [850](http://x2software.com/index.php/bugReports/850): Could not attach files to emails (user report)  
  * [867](http://x2software.com/index.php/bugReports/867): MyBugReportsController and its behaviors do not have a method or closure named "getDateRange".  
  * [875](http://x2software.com/index.php/bugReports/875): User Report  
  * [885](http://x2software.com/index.php/bugReports/885): User Report  
  * [888](http://x2software.com/index.php/bugReports/888): User Report
* Numerous additional bugs reported via our forums have been fixed - thanks!

# 3.7.2 #
1/24/2014

* Improved user session timeout method to fix compatibility issue with some servers
* Fixed bug in Actions.getRelevantTimestamp 
* Fixed star rating cancel button in Firefox
* Fixed bug in web lead form designer preventing tags from being saved properly
* Fixed bug in campaign mailer component that prevents user from seeing when mail is undeliverable (gives a server error instead)

# 3.7.1 #
1/23/2014

* Improvements to the fields manager
  * Better input validation, stability and security
  * New option to set default values for fields in new records
* Administrators can set distinct session timeouts for different user roles
* Mass update buttons added to the Actions grid view
* Default form/view will be generated automatically for new custom modules that don't yet have them
* Inline email widget included in custom module generation
* Improvements to column filters
  * Dropdown menu and boolean type fields appear as dropdowns
  * Date type fields provide the convenient datepicker widget so you don't have to type in dates manually
* Re-instated the missing "cancel" button in the star rating input widget to clear a rating field's value
* Signature replacement in campaigns; new "{signature}" placeholder will be replaced with the email signature of the assignee
* Fixed Bugs:  
  * [124](http://x2software.com/index.php/bugReports/124): Gridview filters: True/False vs. Yes/No  
  * [441](http://x2software.com/index.php/bugReports/441): Property "Profile.pageOpacity" is not defined.  
  * [719](http://x2software.com/index.php/bugReports/719): rename(protected/modules/ob\_b/views/default,protected/modules/ob\_b/views/ob\_b) [<a href='function.rename'>function.rename</a>]: Directory not empty  
  * [757](http://x2software.com/index.php/bugReports/757): CDbCommand failed to execute the SQL statement: SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'AND (type I
  * [764](http://x2software.com/index.php/bugReports/764): primary contact field in quote detail view comes out html-encoded  
  * [766](http://x2software.com/index.php/bugReports/766): Backdating actions does not affect activity feed dates  
  * [770](http://x2software.com/index.php/bugReports/770): Number of overdue actions incorrectly displayed in "My Actions" widget  
  * [833](http://x2software.com/index.php/bugReports/833): User Report
  * In Professional Edition:
    * Tag-based trigger / action now works
    * Security vulnerability in web lead form patched; see [release notes](RELEASE-NOTES.md) for full details.
  * "Sign in as another user" option fixes the previous issue of being unable to switch users after enabling "Remember Me"
  * X2Touch restored
  * In the API, an exception is made so that the "userKey" field of Contacts is not read-only, allowing use of the API for creating properly web-tracked leads
  * Removed deprecated functions that were causing memory exhaustion errors on systems with over 10,000 account records
  * Posts marked as private are properly hidden
  * Numerous unlisted, long-standing bugs (not recorded in the public bug tracker)

# 3.7 #
12/20/2013

* Powerful new all-in-one user home page, featuring:
  * Re-positionable sections
  * Accounts, contacts and opportunities grid views
  * User and event charts
  * Doc viewer
  * Users grid displaying active users
  * Activity feed
* Inline quotes widget now available in Services, Accounts, Opportunities and custom modules
* New lighter, cleaner look and feel
* Case timer: track time spent on service cases and easily publish 
* New campaign batch emailing method that displays real-time progress
* "Workflow" module renamed to "Process"
* Fixed Bugs:  
  * [541](http://x2software.com/index.php/bugReports/541): Invalid argument supplied for foreach()  
  * [550](http://x2software.com/index.php/bugReports/550): Invalid argument supplied for foreach()  
  * [711](http://x2software.com/index.php/bugReports/711): Property "X2Calendar.autoCompleteSource" is not defined. 

# 3.6.3 #
12/9/2013

* Fixed Bugs:  
  * [286](http://x2software.com/index.php/bugReports/286): Clicking action frame links opens in iframe  
  * [373](http://x2software.com/index.php/bugReports/373): Undefined index: RecordViewChart  
  * [376](http://x2software.com/index.php/bugReports/376): preg_match() [<a href='function.preg-match'>function.preg-match</a>]: Unknown modifier '7'  
  * [463](http://x2software.com/index.php/bugReports/463): Undefined variable: noticiation  
  * [513](http://x2software.com/index.php/bugReports/513): strpos() expects parameter 1 to be string, array given  
  * [541](http://x2software.com/index.php/bugReports/541): Invalid argument supplied for foreach()  
  * [601](http://x2software.com/index.php/bugReports/601): Unable to resolve the request "Array/Array/index".  
  * [602](http://x2software.com/index.php/bugReports/602): Unable to resolve the request "undefined/undefined/index".  
  * [603](http://x2software.com/index.php/bugReports/603): asort() expects parameter 1 to be array, boolean given  
  * [608](http://x2software.com/index.php/bugReports/608): strpos() expects parameter 1 to be string, array given  
  * [635](http://x2software.com/index.php/bugReports/635): Class:  not found.  
  * [652](http://x2software.com/index.php/bugReports/652): Property "Publisher.halfWidth" is not defined.  
  * [658](http://x2software.com/index.php/bugReports/658): Download redirect link broken

# 3.6.2 #
11/26/2013

* Changes to the web tracker allow broader browser support; see [release notes](RELEASE-NOTES.md) for details.
* Bug fixes and improvements to the publisher in the Calendar view:
  * End time field was missing.
  * Duration (hours/minutes) fields now available for better control.
* Fixed bug in Admin model: removed old references to nonexistent fields.
* Fixed a security hole in mass-record-deletion feature.

# 3.6.1 #
11/22/2013

(internal release)
* Issues in the new targeted content marketing system have been resolved.
* Corrected an API behavioral issue: contacts created via API were not invoking the "record created" trigger
* Fixed bug: global export wasn't working for "Admin" (system settings) record

# 3.6 #
11/21/2013

* Improvements to user preferences
  * User option to disable notifications popup
  * User option to transform all phone numbers into "tel:" links for click-to-call functionality with VoIP systems
  * Options page sections remember user's preference to be open/closed
  * General options page UI improvements
* Time tracking on records using the publisher
  * New "time log" note type displays time spent on a record
  * Specify begin time, end time and duration of logged calls
* Interface for creating automatic, unattended software update cron task (available on compatible Linux/UNIX servers only)
* The ability to remove contacts from static lists via mass-update action in grid view
* "+" button to add a new account/contact on-the-fly now available in custom modules or any model with account or contact look-up fields
* Fractional quantities in quote line items
* "External/Public Base URL" setting controls how URLs to public-facing resources will be generated, i.e. for CRM systems hosted within private subnets or VPNs
* New in Professional Edition:
  * Targeted content marketing feature (beta)
    * Embeddable, dynamic content for websites tailored to each contact
    * X2Flow-based design interface allows unlimited sophistication in rules and criteria for targeted content
  * Better pattern matching in email dropbox
  * Cron table management console: one page controls all X2CRM-related server cron tasks
  * New and improved pure-JavaScript-based website activity listener and lead capture form compatible with more web browsers
* Fixed Bugs:  
  * [119](http://x2software.com/index.php/bugReports/119): is_file(): open_basedir restriction in effect. File(/usr/share/pear544/Calendar.php) is not within the allowed path(s): (/usr/wwws/users/vanwean:/usr/www/users/vanwean:/usr/home/vanwean:/usr/local/rmagic:/usr/www/users/he/_system_:/usr/share/php544:/  
  * [413](http://x2software.com/index.php/bugReports/413): CDbCommand failed to execute the SQL statement: SQLSTATE[HY093]: Invalid parameter number: parameter was not defined  
  * [462](http://x2software.com/index.php/bugReports/462): Failed to create directory ../../../../backup  
  * [469](http://x2software.com/index.php/bugReports/469): The system is unable to find the requested action "profile".  
  * [487](http://x2software.com/index.php/bugReports/487): Unable to resolve the request "view/view/view".  
  * [514](http://x2software.com/index.php/bugReports/514): Flagging one role as admin gives other roles admin access  
  * [517](http://x2software.com/index.php/bugReports/517): Undefined index: id  
  * [564](http://x2software.com/index.php/bugReports/564): The requested page does not exist.

# 3.5.6 #
10/22/2013

* Improved data validation in the role editor
* Changes to the software updater:
  * Bug fixes in `FileUtil::ccopy` and far more exhaustive [unit testing](https://github.com/X2Engine/X2Engine/blob/master/x2engine/protected/tests/unit/components/util/FileUtilTest.php) of that method
  * Compatibility adjustments (that ensure relative paths used) for servers with open_basedir restriction
  * (experimental) New command line interface for unattended updates via cron
* Changes to the calendar:
   * Added visibility permissions
   * Fixed bug: events not displaying
* Numerous, miscellaneous front-end bug fixes, including but not limited to:
   * Tags; handling of special characters
   * Delay of inline email button (until after instantiation of the CKEditor instance)
   * Mass-update of rating type fields now works
* Retroactive update migration script clears up [permissions issue that caused blank action text](http://x2community.com/topic/1073-blank-comments-call-logs-emails-etc-in-the-update-to-355/) for updating from before version 3.5.5
* Fixed Bugs:  
  * [425](http://x2software.com/index.php/bugReports/425): Unable to resolve the request "list/list/view".

# 3.5.5 #
10/16/2013

* Improvements to grid views:
  * The ability to use shift+click to select ranges of records
  * Mass tagging, field updates, record reassignments and mass deletion of selected records
* Faster, more robust X2CRM updater with the ability to perform offline updates
* Administrative flash message UI
* Changes in Professional Edition:
  * The ability to add hidden fields in the web lead form editor, filled with a user-defined value (e.g. you could set "leadsource" as a hidden field with the value "web").
  * Application lock; the ability to lock the application through the administrative UI so that only administrators can access it
* Fixed Bugs:  
  * [242](http://x2software.com/index.php/bugReports/242): User Report  
  * [245](http://x2software.com/index.php/bugReports/245): Class:  not found.  
  * [256](http://x2software.com/index.php/bugReports/256): Changing static page title cause it to disappear  
  * [270](http://x2software.com/index.php/bugReports/270): User Report  
  * [287](http://x2software.com/index.php/bugReports/287): Missing Fields on Manage Notification Criteria  
  * [327](http://x2software.com/index.php/bugReports/327): Top Sites Widget Can't Edit  
  * [345](http://x2software.com/index.php/bugReports/345): Unable to resolve the request "tycoons (1)/index".  
  * [361](http://x2software.com/index.php/bugReports/361): Unable to resolve the request "list/list/view".  
  * [364](http://x2software.com/index.php/bugReports/364): Unable to resolve the request "viewContent/viewContent/view".  
  * [365](http://x2software.com/index.php/bugReports/365): Unable to resolve the request "view/view/view".  
  * [367](http://x2software.com/index.php/bugReports/367): Unable to resolve the request "flowDesigner/flowDesigner/view".  
  * [368](http://x2software.com/index.php/bugReports/368): Unable to resolve the request "list/list/view".  
  * [369](http://x2software.com/index.php/bugReports/369): Unable to resolve the request "list/list/view".  
  * [371](http://x2software.com/index.php/bugReports/371): Unable to resolve the request "download/download/view".  
  * [372](http://x2software.com/index.php/bugReports/372): Tools Column Error  
  * [392](http://x2software.com/index.php/bugReports/392): Unable to resolve the request "list/list/view".  
  * [393](http://x2software.com/index.php/bugReports/393): Unable to resolve the request "list/list/view".  
  * [395](http://x2software.com/index.php/bugReports/395): Undefined index: multi  
  * [405](http://x2software.com/index.php/bugReports/405): array_filter() expects parameter 2 to be a valid callback, no array or string given  
  * [452](http://x2software.com/index.php/bugReports/452): Unable to resolve the request "update/update/view".

# 3.5.2 #
9/20/2013

* Fully-configurable batch timeout setting controls how much actual time can be spent in campaign emailing and cron events
* Attribute replacement now works in the "Send a Test Email" feature of Campaigns
* Long-overdue data validation in Role creator
* New in X2Flow (Professional Edition only)
  * X2Flow email actions can be configured to send using SMTP accounts stored through the credentials manager (see: "Manage Apps" in the user menu)
  * Variable replacement in the X2Flow email actions works for arbitrary models
  * Insertable attribute menus in X2Flow email actions automatically match those of the model type in the trigger
  * New "unsubscribe" link short-code for X2Flow email bodies
* Fixed Bugs:  
  * [206](http://x2software.com/index.php/bugReports/206): Name improperly parsed/generated from email headers  
  * [243](http://x2software.com/index.php/bugReports/243): User Report  
  * [252](http://x2software.com/index.php/bugReports/252): X2Flow Issue with comparing two attributes  
  * [296](http://x2software.com/index.php/bugReports/296): Send a test email to actual contacts  
  * [308](http://x2software.com/index.php/bugReports/308): Cannot add "administrator" as a child of "DefaultRole". A loop has been detected. 
  * [311](http://x2software.com/index.php/bugReports/311): DbCommand failed to execute the SQL statement: SQLSTATE[HY000]: General error: 1366 Incorrect decimal value: '' for column 'dealvalue

# 3.5.1 #
9/12/2013

* Minor bug fixes

# 3.5 #
9/6/2013

* "Print Record" feature in nearly all modules shows print-friendly version of a record
* "Recently Viewed" widget now includes all record types
* Chart widget enhancements
  * New pie chart view 
  * Dynamic date ranges, i.e. "last week"
* Features in Professional Edition
  * New campaign chart
  * New cron test and log viewer in X2Flow
* Fixed Bugs:  
  * [94](http://x2software.com/index.php/bugReports/94): Array to string conversion  
  * [121](http://x2software.com/index.php/bugReports/121): "Remember Me"  
  * [135](http://x2software.com/index.php/bugReports/135): Remove deprecated "add contact" action/menu button  
  * [159](http://x2software.com/index.php/bugReports/159): Trying to get property of non-object  
  * [217](http://x2software.com/index.php/bugReports/217): X2Flow Strpos Error  
  * [219](http://x2software.com/index.php/bugReports/219): Trying to get property of non-object  
  * [225](http://x2software.com/index.php/bugReports/225): Creating default object from empty value  
  * [228](http://x2software.com/index.php/bugReports/228): Property "Gallery.galleryId" is not defined.  
  * [232](http://x2software.com/index.php/bugReports/232): Grid Views break on filter click  
  * [248](http://x2software.com/index.php/bugReports/248): CDbCommand failed to execute the SQL statement: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '14' for key 'PRIMARY'  
  * [263](http://x2software.com/index.php/bugReports/263): Email campaign template selection issues  
  * [266](http://x2software.com/index.php/bugReports/266): multi-assignment fields not preserved when returning to edit page  
  * [277](http://x2software.com/index.php/bugReports/277): CDbCommand failed to execute the SQL statement: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '1001' for key 'c\_name'

# 3.4.1 #
8/22/2013

* Miscellaneous (unlisted) bug fixes
* Image gallery (Professional Edition only) now works in Internet Explorer

# 3.4 #
8/21/2013

* New image gallery widget
* Dropdowns can be customized to allow selecting multiple values
* New activity feed chart feature: can filter data display by user
* New features in Professional Edition:
  * Rich email editing available in the email action of X2Flow
  * Cron task setup in installer
  * Formulas and variables enabled in X2Flow trigger criteria & action parameters
  * Accounts report feature: send marketing campaigns to related contacts of accounts
* Fixed bugs: 88, 93, 95, 110, 111, 118, 121, 128, 150, 166, 170, 172 and 200

# 3.3.2 #
8/6/2013

* Fixed bug in web tracker & web lead form

# 3.3.1 #
8/5/2013

* Safeguard against duplicate update server requests
* Fixed bug: incorrect created by / updated by / deleted by user

# 3.3 #
8/2/2013

* Better translations
  * Vastly more comprehensive coverage
  * Added Polish language pack
* Improved charting
  * New action history chart provides visual timelines of activity on almost anything
  * Improvements to the activity chart on the home page
* Improvements to the third-party application credentials (email passwords) manager
  * Improved, more intuitive UI
  * More concise access control logic
  * Web lead and service case forms can be configured to use credentials to send email
* New REST-ful API action for adding/removing relationships between models
* Broadcasting events now supports sending emails to any number of users
* New event feed, action history, module header and admin console icons
* Numerous bug fixes
* SSL-secured software updates

# 3.2 #
7/10/2013

* Enhancements to X2Flow Automation
  * Improved UI is more intuitive
  * You can now set time delays to run actions at a later date
  * New cronjob endpoint for time-based events
* New multi-account email system
  * You can now create and manage unlimited SMTP accounts for email integration
* Advanced Google Drive integration
  * Upload, view and access your Drive files from within X2CRM
  * Effortlessly attach files to emails
  * General improvements to Google integration
* New charting system on the home page feed lets you visualize new leads and user activity
* Numerous bug fixes

# 3.1.2 #
6/28/2013

* Improvements to theme settings
  * You can now save themes
  * Set custom gridview row colors
* Improved X2Flow Automation look and feel
* X2Flow can now use Lead Routing Rules to assign records
* Customizable header tag on web lead forms
* Numerous bug fixes

# 3.1.1 #
6/21/2013

* Fixed bug creating new windows when notifications are received
* Reverted some changes to UI

# 3.1 #
6/18/2013

* Robust new resizable grid view
* Enhancements to application UI
  * More compact layout
  * Better controls for user color schemes
* Yii Framework updated to 1.1.13
* New API action "tags" allows programmatic manipulation of tags on records
  via the API
* Improved record history filtering
* The inline email form can now be used while viewing account records
* Better support for foreign currencies in quotes & invoices
* More bug fixes

# 3.0.2 #
5/20/2013

* New Services reporting tool
* Rich text editor now available for activity feed posts and email signatures
* Bug fixes

# 3.0.1 #
5/13/2013

* Numerous bug fixes
* Can now trigger automation on user login/logout
* Docs module:
  * New basic quotes template in default app data
  * "Duplicate" button in Docs module for making copies of and customizing an
    existing document
* New in the API:
  * Can manually set creation date
  * More consistent response behavior
  * New method listUsers: gets list of users

# 3.0 #
5/1/2013

* (Professional Edition only) X2Flow automation system (beta)
  * Visual, drag-and-drop designer makes it easy to create convenient and 
    powerful automation flows
  * Automation flows can enact changes, create records, and a broad range of 
    other operations ("actions") whenever certain events ("triggers") take place
  * Supports a very extensive set of actions and triggers
* Greatly improved Actions module; streamlined, user-friendly interface
* New and improved Quotes module
  * Line items can be re-ordered after adding them
  * Can add adjustments to the total, i.e. tax and shipping; displays subtotal
    vs. total if there are adjustments
  * Support for arbitrary quote/invoice templates, which can be created and 
    designed via "Create Quote" in the Docs module, and loaded/sent via email 
    by going to the Quote's record view
* Customizable login and notification sounds

# 2.9.1 #
3/27/2013

* Additional bugfixes
* Better failsafe in updater: uses either of two remote copy methods depending on which is available

# 2.9 #
3/21/2013

* Revamped web API
  * now supports operations on any module type, including custom ones
  *  Improved stability
* More user control over the color scheme
* All new default background images
* Background fade button (lower right of screen)
* Changed to Affero GPL v3 license
* Updated CKEditor to version 4
* Spellcheck now available in CKEditor
* You can now pin activity feed items
* Enhancements to Requirement Checker on installation
* Numerous bug fixes

# 2.8.1 #
2/20/2013

* VCR controls for tag based search results
* Fixed bugs:
  * Emailing contacts
  * int/float/currency type fields
* Changelog now allows filtering by record name
* Email templates now allow variables in subject line
* "percentage" field type

# 2.8 #
2/13/2013

* Dozens of bug fixes - thanks everyone for reporting bugs using the new bug reporting tool!
* New theme and background settings
* New manual bug reporting tool
* Added some icons
* Email dropbox now creates events (Pofessional edition)
* Google Analytics integration for monitoring X2CRM usage

# 2.7.2 #
2/1/2013

* New UI look and feel, improved UI consistency
* Numerous bug fixes

# 2.7.1 #
1/25/2013

* Added an easy to use bug reporting tool
* Activity feed now remembers minimized posts
* Several bug fixes

# 2.7 #
1/23/2013

* New Activity Feed
  * See all the activity on X2CRM in one place
  * Updates in real time
  * Infinite scrolling
  * Filter by users/groups and event type
  * Social posts/comments are now integrated
  * Action reminders
  * Social posts can now be edited
* Enhancements to web tracker (Professional edition)
  * Campaign emails now support tracking links
* Widget/layout enhancements
  * Widgets can now be completely turned on/off
  * Content widgets (Tags, Relationships, etc) can be toggled and rearranged
  * New widget menu in top bar 
* Lots of new icons
* Numerous bug fixes
* Campaign email list improved
* Updated translations

# 2.5.2 #
12/28/2012

* Several bug fixes to v2.5 and the now-defunct release 2.5.1, including but not limited to:
  * Incorrect order/offset in VCR control navigation
  * Missing attribute errors when editing app settings and user profiles
  * Miscellaneous errors in the contacts view

# 2.5 #
12/18/2012

* New web tracking system (Professional edition)
  * Track using a simple embed code on your website
  * Real time notifications when a contact visits the website
* New large Google Maps page with heatmap and tag-based filtering
* You can now hide tags
* Numerous bug fixes
* Duplicate checker - major usability improvements
* New web form designer (Professional edition)
  * Service request form
  * Contact lead capture
  * Save multiple forms
  * Fully customizable fields
* Charts and reports - UI enhancements
* Notifications - improved behavior and stability
* Translations - new Dutch and Spanish packs
* Much more complete sample data
* Improved page load time on most pages
* New login page

# 2.2.1 #
11/15/2012

* Numerous improvements to Contacts importer
  * Improved UI
  * Better reliability
* WYSIWYG editor now lets you insert record attributes in emails/campaign templates
  * Fixed several bugs with editor
* Improvements to Service module
  * You can now specify the from address for Service module emails
  * Filter by Status
  * Numerous bug fixes
* Numerous other bug fixes

# 2.2 #
11/08/2012

* Service module
  * Unique Case # and fields for nature of request and service status
  * Generate a custom Web Form to let contacts request a new service case
  * Automatic response email with case # when a contact makes a service request
* Improved import tools
  * More robust global import
  * Customizable import for contacts (you can now import data in almost any
    format, and can manually map the columns to X2CRM fields.
* CKEditor has replaced TinyEditor as the docs and email editor.
  * Images can now be dragged directly into the editor from the Media widget
  * You can upload images from within the editor
* Numerous bug fixes
* You can now upgrade to the Professional Edition (on the admin page)
* Emails sent using the inline email form now detects when the user opens it
  (like campaigns do

# 2.1.1 #
10/15/2012

* Overhauled real-time notification and chat
  * Much lower server load, especially with multiple tabs
* Improved URL handling (more efficient)
* Improved changelog storage
* Big improvements to the installer
  * Real-time installation status updates
  * No more timeout errors
* Improvements to Relationships for contacts, accounts and opportunities
* Fix for all bugs related to browsers caching old javascript files
* Additional feature in Customization Framework: you can now override controller
  files by adding "My" to the class and putting the file in /custom, for example
  to override actionIndex in ContactsController you can create a class
  MyContactsController extending ContactsController and only define actionIndex.
  This class will automatically be used in place of the original file, and you
  don't have to override the entire class.
* Bug fixes for 2.1.1:
  * Installer: incomplete error reporting
  * Role manager: CSS
  * Updater / updater settings: safeguards & interval setting
  * Mobile (X2Touch) login: JavaScript errors


# 2.1 #
10/12/2012

* Overhauled real-time notification and chat
  * Much lower server load, especially with multiple tabs
* Improved URL handling (more efficient)
* Improved changelog storage
* Big improvements to the installer
  * Real-time installation status updates
  * No more timeout errors
* Numerous bug fixes
* Improvements to Relationships for contacts, accounts and opportunities
* Fix for all bugs related to browsers caching old javascript files
* Additional feature in Customization Framework: you can now override controller
  files by adding "My" to the class and putting the file in /custom, for example
  to override actionIndex in ContactsController you can create a class
  MyContactsController extending ContactsController and only define actionIndex.
  This class will automatically be used in place of the original file, and you
  don't have to override the entire class.

# 2.0 #
10/2/2012

* New and greatly improved UI
* New features in X2Touch Mobile
* Renamed Sales to Opportunities
* Improved relationships between Contacts, Accounts, and Opportunities
* Added date and user filtering to Workflow view
* Reworked the back-end of access permissions to fit with Yii roles
* Added attachments to Marketing campaigns

# 1.6.6 #
8/31/2012

* New Workflow report in Charts module
* Improved Lead Volume report
* Improved phone number search (search is now formatting-insensitive)
* Documents now auto-save (whenever you stop typing)
* New look for installer and login screen
* Added logging for failed API requests
* Numerous bug fixes
* Added various translations

# 1.6.5.1 #
8/28/2012

* Bug fix patch; corrections to the software updater

# 1.6.5 #
8/24/2012

* Powerful new web lead capture form editor
* Enhanced record tagging abilities
* New single-user lead distribution option
* Automatic phone number formatting (for US numbers)
* Reorganized admin page
* Improved search results
* Improved notification behavior
* Tons of bug fixes
* Improvements to VCR controls and grid sort/filter rememebering

# 1.6.1 #
7/25/2012

* New Tag-to-email campaing tool
* New VCR controls for lists (all contacts, user-defined lists) allows you to
  go directly to the next record without going back to the list
* New workflow stage backdating controls for admin
* Misc. bug fixes

# 1.6 #
7/18/2012

* Improvements to list builder interface
* Improvements to real-time notifications
* Popup tooltips with contact details on gridview
* Grid views now have a selector for results/page
* Enhanced default theme
* Files can now be attached to emails
* Redesigned Users menu
* Numerous bug fixes
* Enhanced support for phone numbers

# 1.5 #
6/19/2012

* New full-featured Marketing module
  * Built on dynamic or static contact lists
  * Templates with contact info insertion
  * Batch mailing system with real-time status info
  * Email open/click tracking
  * Unsubscribe links
* Major enhancements to notifications
  * Real-time notification popups
  * Customizable notification events
  * VOIP API allows automatic record lookup when a contact calls your phone
* De-duplication tool
* Google apps OAuth login
* Improvements to Google calendar integration
* New widget dashbaord (previous dashboard module is now called Charts)
* Numerous bug fixes

# 1.4 #
5/23/2012

* Numerous bug fixes
* Fine tuned the layout (background selecting works better, users can now toggle the full-width layout)
* Major improvements to global search
* Full Google Calendar integration
  * allow users to sync all there actions and events to there google calendar
* Improved Workflow widget
  * detail view for each workflow stage
  * users can now edit and backdate previous workflow stages
* User-created lists now have an export tool, using the current visible columns
* Improved performance of Contact Timezone widget
* New "Top Sites' widget allows you to save bookmarks within X2Engine

# 1.3 #
5/7/2012

* New dynamic layout (flexible width, support for screens as small as 800x600)
* Added new widgets:
  * Contact Time Zone
  * Doc Viewer
* Numerous bug fixes
* Enhancements to list builder, such as arrays of matching values, relative times, and "not empty"

# 1.2.2 #
3/23/2012

* Added View Relationships: you can now see everything linked to a given record
* Numerous bug fixes
* Enhanced custom lead routing rules

# 1.2.1 #
3/16/2012

* Added Contact Lists
  * Users can now create custom static or dynamic lists
* Tons of bug fixes, particularly related to broken links
* Users, Workflow and Groups are now standard modules (structural change)
* Calendar can now integrate with Google calendar

# 1.2.0 #
3/9/2012

* Major Structural Changes
  * Modularization of code
  * Improvements to Calendar
* Numerous bug fixes
* Code optimizations

# 1.1.1 #
2/29/2012

* Minor post-release bug fixes.

# 1.1.0 #
2/29/2012

* New Calendar module
  * View task due dates and new Event type actions
  * filter by user
* Improvements to Workflow
  * New visual workflow designer
  * Role-based permissions for each stage
  * More advanced previous stage requirements
* Enhanced action pulisher
* Dozens of bug fixes
* Improved behavior on left actions sidebar

# 1.0.1 #
2/21/2012

* Various bug fixes and small improvements

# 1.0 (GA) #
2/20/2012

* Various bug fixes
* Translations are now mostly complete (except workflow and admin page). Expect a patch with the rest this week.

# 0.9.10.1 #
2/17/2012

* Numerous Bug fixes (esp in X2Studio, Accounts and Sales)
* Products Products module
  * Keep track of products used to generate quotes
* Quotes Module
  * Use Products to generate Quotes
  * Link Quotes to Contacts
  * Generate email quote
* Added dashboard and charting module
  * Customizable reports on sales pipeline and user performance
* Enhancements to page navigation
* Updated the X2 Publisher (create actions, comments, etc)
