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
