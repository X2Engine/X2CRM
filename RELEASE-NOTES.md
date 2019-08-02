# 7.1 #

1/3/2019
* General Changelog / Developer Notes
  * X2CRM is now compatible with PHP 7.1+

* Miscellaneous bug fixes
  * Fixed issue with emailing where mail servers which are not configured to use VERP can still send email
  * Removed list option from the reporting module
  * A/B campaigns now work with dynamic lists
  * Fixed issue where 'do not email' settings would get incorrectly set
  * Fixed issue where a 500 error would occur if the 'maxFileSize' attribute was not created correctly
  * Fixed issue where X2Flow would incorrectly reference a workflow ID
  * Fixed issue where logging time on a record would incorrectly calculate time spent
  * Fixed front-end with the complete stage action in X2Workflow where the note textarea was covering the stage selection dropdown

# 7.0 #
10/19/2018

* General Changelog / Developer Notes
  * Accounts, Leads and Opportunities are now listable
  * Contacts can now be converted to leads
  * Campaigns
    * A/B testing for campaigns added
    * Account, Lead and Opportunity lists can now all be used in campaigns
  * Miscellaneous bug fixes

# 6.9.3 #
1/3/2018
* Fixed unrecognized field lastModifies bug

# 6.9.2 #
12/29/2017
* Fixed email bug

# 6.9.1 #
11/20/2017
* General Changelog / Developer Notes
  * New Enterprise UI
    * New Paper-White UI default theme
    * Revamped Landing Page Designer
    * Top Menu Dropdowns
  * New X2HubServices integrations
    * Contact Twitter feed
    * LinkedIn user information autofill
    * Dropbox integration
    * Docusign Integration
  * Miscellaneous bug fixes
    * Workflow tag bug fix
    * Calendar empty tables bug fix

# 6.9 #
08/22/2017
* General Changelog / Developer Notes
  * Miscellaneous UI enhancements
    * New Green login logo
  * New Enterprise edition released
    * The enterprise edition of X2CRM is developed with enterprise users in mind
      * New colorful icons for actions (these can be seen in the activity feed)
      * New Native Code Editor
        * The code editor allows the modification of the application code through the app itself. This allows power users to customize the app like never before! The code editor can be accessed via the X2Studio tools in the admin section of the app
      * X2Workflows
        * New stock X2Workflow rules added to the workflow system
        * Restricted non-compatible actions from being attached to triggers, this is introduced in order to prevent users from making broken workflows.
  * Miscellaneous bug fixes
    * Fixed an issue with login where users would get immediately get logged out
    * Fixed a UI bug where some smaller screens would prevent parts of the app from showing.

# 6.6 #
5/17/2017
* General Changelog / Developer Notes
  * Campaign Improvements
    * Email campaigns can now be scheduled
    * Added email opened time and location to campaign launch grid
    * Improved database performance of campaign email sends
  * X2Touch Improvements
    * Added barcode scanner to import product barcode identifier
    * Mobile publisher now supports voice to text notes
  * X2Workflow Improvements
    * New Location Trigger to support general workflow operations when a Location is logged
    * Added AddToNewsletter workflow action to manage web leads on newsletter contact lists
  * Added new feature tours
  * Improved email client IMAP server compatibility, including with Dovecot, Exchange, Office365, and Rackspace
  * Added new import console command with 'rollback' operation
  * Added an admin page to locate missing records that may have been inadvertently hidden
  * New RackspaceEmail account type
  * Fixed bug in email subject replacement
  * Enabled mass execute macro on Contact list grids
  * Added option to weblead form designer to disable dupe detection by X2Identity on a per-form basis
  * X2Packager stability fixes
  * Fixed date format issue under French locale
  * Miscellaneous UI enhancements
  * Miscellaneous bug fixes
* Tracked Bug Fixes:
  * [3703](http://x2software.com/index.php/bugReports/3703): Undefined index: X2List
  * [5825](http://x2software.com/index.php/bugReports/5825): Call to a member function asa() on null
  * [5966](http://x2software.com/index.php/bugReports/5966): Trying to get property of non-object
  * [5971](http://x2software.com/index.php/bugReports/5971): Function mcrypt_create_iv() is deprecated

# 6.5.2 #
12/23/2016
* General Changelog / Developer Notes
  * X2Touch Improvements
    * Added video attachments on iOS
    * Added audio attachments on iOS and Android
  * New X2Workflow actions for creating notifications, emails, text messages, and activity feed posts with records in a specified proximity
  * Activity feed posts can now be associated with arbitrary records
  * Added Twilio account type and SMS-based two factor authentication
  * Checkin post formatting improvements
  * Added weblead form thank you text customization
  * Fixed Doc template usage with Opportunities
  * Added option to toggle geolocation functionality
  * Standard Actions can now be posted to a user's calendar
  * Added ability to relabel existing relationships
  * AnonContact webactivity visits can now be filtered from your notifications
  * Miscellaneous bug fixes

# 6.5.1 #
11/17/2016
* General Changelog / Developer Notes
  * Added open rate, click rate, and unsubscribe rate attributes to Campaigns
  * Added ability to import Contacts from mobile devices
  * Added static maps to webapp check ins
  * Added mobile audio attachments
  * Resurfaced calendar event reminders
  * Fixed broken images in Image Gallery widget
* Tracked Bug Fixes:
  * [3396](http://x2software.com/index.php/bugReports/3396): CDbCommand failed to execute the SQL statement: SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row: a foreign key constraint fails (`nevulosa_x2153`.`x2_actions`, CONSTRAINT `fk_actions_workflow_id` FOREIGN KEY
  * [5375](http://x2software.com/index.php/bugReports/5375): syntax error, unexpected '}'
  * [5391](http://x2software.com/index.php/bugReports/5391): CDbCommand failed to execute the SQL statement: SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ') AND x2_lo
  * [5416](http://x2software.com/index.php/bugReports/5416): Undefined variable: sessionTokenCookie
  * [5420](http://x2software.com/index.php/bugReports/5420): Argument 1 passed to RelationshipsBehavior::deleteRelationship() must be an instance of CActiveRecord, null given

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

* Email addresses in To, Cc, and Bcc lists in X2Flow email actions now validate
  when flows are saved or triggered. Invalid email addresses in any of these
  fields will prevent the flow from being saved (displaying a warning in the
  UI). For existing flows, X2Flow Email actions with invalid email addresses
  will be skipped (with a validation error message written to the trigger log).
* The old "User" option of the "Post to Activity Feed" flow action has been
  relabelled "Author".

# 4.2b #
8/7/2014

* Structural changes to the record view widget code breaks compatibility with 
  old custom record view widgets.

# 4.1.6b #
7/3/2014

* The "required" validation rule now applies, globally, to all instances of 
  updating as well as all instances of saving. Users can now no longer save 
  and then empty the field after going back to edit. This may disrupt existing 
  workflows that depended on this erroneous behavior.

# 4.1.2b #
5/30/2014

To update to this and all future beta releases, you must enable beta releases,
as follows:

1. Create a file "constants-custom.php" in the root level of X2Engine. You can 
   easily do this by renaming "constants-custom.example.php" to that name 
   (remove ".custom").
2. Look for the following line, and in it, change "false" to "true":
  <pre>defined('X2\_UPDATE\_BETA') or define('X2\_UPDATE\_BETA',false);</pre>


# 4.1 #
5/20/2014

* **Usernames are now case-sensitive.**
* __It has come to our attention that the very old "calendar permissions"
  feature does not behave entirely as it was originally designed.__ Granting one
  user edit permissions to another's calendar does not actually grant
  permissions to edit the event itself, except in regard to the ability to
  change the date of events via drag-and-drop. This is because event records are
  actions, and hence the ability to edit an action is subject to the access
  rules of the Actions module. This old feature (calendar permissions) was
  designed before RBAC was employed in X2Engine to permit finer control over who
  can edit what. In previous versions this would result in a bug, whereupon 
  submitting the form to edit an action would result in no response/change (a
  403 status from the server) when the user did not actually have access via
  their Actions module permissions to edit the action. In this version, editing
  of events is disabled in the UI when the action record itself cannot be
  directly edited. Please note this, to avoid confusion; granting edit access on
  a calendar will not always allow other users to actually change aspects of the
  event record such as the description, association, color, and assignment. _It
  will merely guarantee that the other user will be able to drag/drop events to
  different dates._
  

# 4.0 #
3/20/2014

* "X2Touch", the mobile layout, is no more. In its place will be a responsive,
  mobile-friendly layout.   
* The formula-parsing method used in X2Flow has changed to improve security and 
  stability. Principally, it no longer filters/sanitizes the input by blacklisting
  patterns in the input. Furthermore owing to how the input needs to be evaluated
  as PHP code, all replacement values are escaped using 
  [var_export](http://php.net/var_export). The previous design was found to be 
  problematic in numerous ways, most notably how character sequences would remain
  unquoted in the resulting code (which would cause "undefined constant" errors
  unless the end user explicitly inserted quotes around it, and somehow enforced
  the content of the referenced field never containing quotes).

# 3.7.3b #
2/14/2014

* This version, while it is mainly oriented towards fixing bugs, contains some
  extreme, far-reaching changes (for example, the refactoring of how lookup fields
  work) that may have introduced bugs that we have not seen/foreseen in our tests.
  For that reason, this release has been deemed a beta, and a stable follow-up release
  can be expected later.
* From this version forward, the name of this software is **X2Engine**. If you see
  any references to "X2CRM", "X2Contacts" or "X2EngineCRM" in UI messages, please
  report it as a bug. If you see any such references in the Wiki, or in pinned forum
  posts, or in the X2Engine User Reference Guide, please post in 
  [this thread](http://x2community.com/topic/1482-branding-consistency/) or follow
  the instructions for contacting the X2Engine team.

# 3.7.1 #
12/23/2013

* For security purposes, the web lead form no longer permits setting options via
  query parameters, with the exception of CSS (i.e. color); rather, all options
  are now stored server-side. Existing web lead forms will thus need to be updated.

# 3.7 #
12/23/2013

* In previous versions, the "greater than" and "less than" comparison operators 
  in X2Flow incorrectly resulted in the comparisons "greater than or equal to" 
  and "less than or equal to", respectively. In this version, that behavior is
  corrected, and "greater than" and "less than" strictly mean greater than and
  less than. This will change all existing flows that use these operators in
  value comparisons; the comparisons will not hold true if values being compared
  are equal.
* To accomodate flows that depend on "less than or equal to" and "greater than 
  or equal to" comparisons, new operators for these comparisons are available 
  to building X2Flow criteria. All existing flows depending on the erroneous
  behavior of "less than" and "greater than" (which held true even if the values
  being compared were equal) should be modified to use these new operators.

# 3.6.2 #
11/26/2013

* Hosting one's CRM on a different domain name than one's website will increase 
  the likelihood of public-facing resources like targeted content, web forms and
  the web tracker not working properly in some browsers. This is due to the 
  default privacy settings in such browsers, which have "third-party" cookies
  disabled. To rectify this issue, see the instructions on the "Public Info 
  Settings" page (accessed through the "Admin" page). The configurable public web
  root URL setting allows one to specify a distinct "external" base URL to use for
  public-facing resources. 
* In this release, the previous changes (in 3.6) that would have allowed generic
  applicability of the "+" button (to create new account or contact, displayed next
  to the inputs for link-type fields of those types) have been reverted. This is
  because, as it turns out, would require far more clean-up and refactoring of 
  old relationships JavaScript code that there hasn't been enough time yet to 
  perform. The "+" button was non-functional in 3.6 for this reason.

# 3.6.1 #
11/22/2013

* The targeted content embed method has been changed to resolve the previous 
  issue with Internet Explorer. As such, any embed codes generated in 3.6 will
  not work in this and future versions. To fix this issue, it will be necessary 
  to re-generate the code and use it to replace the existing embedded code on 
  your website.

# 3.6 #
11/21/2013

* The targeted content feature is designated "beta" because, in this version, 
  the embeddable code causes problems in Internet Explorer 8 and 9 when embedded
  in pages containing multiple iframes.

# 3.1 #
6/18/2013

* In the deletion action of the API, the primary key can now be specified in
  either the GET or POST parameters. This way, the "DELETE" request type can be
  used for deletion, and not just the POST type of request.

# 3.0.1 #
5/13/2013

* The API has undergone some fundamental changes in its response format:
  * It always responds in JSON-encoded objects for all actions, with the
    exception of checkPermissions, which responds with code 200, mimetype
    "text/plain" and content "true" or "false" (as it always has)
  * With the exception of the "create" and "update" actions, all actions that
    return JSON-encoded objects shall remain unchanged in terms of the structure
    of their responses.
  * The attributes of the model returned in the "update" and "create" methods
    should be in the "model" property of the response. All references to these
    actions should thus use the "model" property of the response to get the
    attributes of the model created/updated instead of treating the entire
    response object as the model.
  * API scripts that used actions which previously returned HTML pages or page
    fragments should now refer to the "message" property of the returned object
    for the content to be rendered.
  * In the APIModel class, there should now be a new "modelErrors" property,
    which stores the validation errors for each attribute of the object, returned by
    [CActiveRecord.getErrors()](http://www.yiiframework.com/doc/api/1.1/CModel#errors-detail)
    on the server. The source of this data is the "modelErrors" property of the
    response from the create and update actions.

# 3.0 #
5/1/2013

* The automation designer, while largely complete, is still in active 
  development, and thus has been deemed a "beta" feature.
* Quotes created before updating to 3.0 may display incorrect totals in email,
  print and inline views. This can be easily corrected by opening the update 
  view of the quote and saving it (even without any changes). This is due to 
  how, in previous versions, totals weren't stored in quote records, but rather 
  were re-calculated on-the-fly whereverthere they were displayed. This required 
  writing and maintaining three separate versions of the code that calculated
  totals: the default quotes update page (JavaScript), the inline quotes widget
  in the contact view (JavaScript), and in the model where the line items table 
  was generated (PHP). In order to improve the maintainability and reliability 
  of the line items code (by reducing the number of places it could fail), and 
  in keeping with the DRY (don't repeat yourself) principle, all line item 
  calculations are now performed via client-side JavaScript in the 
  "\_lineItems" view of the quotes module. The arithmetic, however, is only run 
  when a quote is created or updated. Thus, to correct the total displayed on 
  a quote, open the quote's update view so that the subtotal can be 
  recalculated, and then save it.

# 2.1.1 #
10/15/2012

* Note: Any existing changelog data will be preserved, but not visible in the
  changelog table. In the next update we will include code to convert this data
  to the new format.
