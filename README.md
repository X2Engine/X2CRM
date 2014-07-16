# X2Engine 4.1 #
Follow-up point release 4.1.6
7/3/2014

New in 4.1 (see [CHANGELOG](CHANGELOG.md) for full history):

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
  *  Activity Feed:
    * _(Professional Edition) Digest emails:_ get periodic emails notifying you of what's happening in your CRM
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
  * [1204](http://x2software.com/index.php/bugReports/1204): Cannot modify header information - headers already sent 
  * [1223](http://x2software.com/index.php/bugReports/1223): Cannot modify header information - headers already sent
* Changes in 4.1.1 (5/23/2014):
  * Activity feed JS bug fixes
  * Backwards compatibility fixes for ResponseBehavior and X2LinkableBehavior
  * Bug fixes in roles
  * Improvements to webhooks: better payload composition logic + safeguards for systems w/o cURL libraries
  * Lead conversion bug fix
  * (Platinum Edition) "Raw Input" API settings option not saving properly
  * "Linkable Behavior" + custom modules backwards incompatibility
  * Web lead form not respecting when "Create Lead" option is disabled
* Changes in 4.1.2 (6/6/2014):
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
    * [1246](http://x2software.com/index.php/bugReports/1246): array\_merge() [<a href='function.array-merge'>function.array-merge</a>]: Argument #2 is not an array  
    * [1247](http://x2software.com/index.php/bugReports/1247): Class:  not found.  
    * [1264](http://x2software.com/index.php/bugReports/1264): CDbCommand failed to execute the SQL statement: SQLSTATE[42S02]: Base table or view not found
    * [1268](http://x2software.com/index.php/bugReports/1268): Trying to get property of non-object  
    * [1280](http://x2software.com/index.php/bugReports/1280): Emailed quotes not tracked properly  
    * [1295](http://x2software.com/index.php/bugReports/1295): Validation errors not shown when updating an opportunity 
* Changes in 4.1.3 (6/6/2014):
  * Tracked Bug Fixes:  
    * [1304](http://x2software.com/index.php/bugReports/1304): JS broken on "full edit page" for actions  
    * [1307](http://x2software.com/index.php/bugReports/1307): Class: AnonContact not found.  
    * [1309](http://x2software.com/index.php/bugReports/1309): Class: Reports not found.
* Changes in 4.1.4 (6/24/2014):
  * General changelog / developer notes:
    * Recognition for memory limit in requirements check script
    * Fixed action timers bug (duplicate timer records created via publisher)
    * Proper handling of completion/uncompletion of actions in X2Flow
    * Proper initial ordering of left widgets
    * Fixed bug in tagBehavior: added safeguard for no web session (i.e. in scope of web lead form submission)
    * Inline email form in the Quotes module has been expanded to work without an associated Contact
  * Tracked Bug Fixes:  
    * [1320](http://x2software.com/index.php/bugReports/1320): Importer broken  
    * [1343](http://x2software.com/index.php/bugReports/1343): User Report (XSS vulnerability)
    * [1340](http://x2software.com/index.php/bugReports/1340): User Report
* Changes in 4.1.5 (6/26/2014):
  * General changelog/developer notes:
    * Included several commits from internal tree that were missed in the previous release
    * Fixed MoneyMask bug: when unsupported currencies are in use, validation was failing
* Changes in 4.1.6 (7/3/2014): 
    * Highlights
      * New "available" lead routing option:
        * Users can set online/offline availability, i.e. when they go on vacation
        * Lead routing can be configured to respect this option, i.e. avoid assigning records to unavailable users
      * "Loading" status/visual overlay when adding fields
      * Global import/export tool now supports custom fields
      * Custom short-codes feature for templates and X2Flow: create an analogue of protected/components/x2flow/shortcodes.php in custom/ to define your own custom codes
      * Can rename media files
      * Process UI improvement: Quickly switch between processes from the funnel and pipeline views
      * (Professional Edition) Improved activity feed reports with a page to manage reports and the ability to send a test report
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
      * Fixed regex matching on imported ids
      * Added missing phone type in Field Manager
    * Tracked Bug Fixes:  
      * [1340](http://x2software.com/index.php/bugReports/1340): User Report  
      * [1345](http://x2software.com/index.php/bugReports/1345): CDbCommand failed to execute the SQL statement: SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens  
      * https://github.com/X2Engine/X2Engine/issues/28
* Changes in 4.1.7 (7/15/2014): 
    * General changelog/developer notes
      * Fixed issues in the importer which prevented fields from being set when a default field value was given
      * Fixed error in Form Editor which prevented scenario from being saved
      * Fixed bug in the Campaign Bulk Mailer which caused an incorrect error to be reported
      * Fixed bug in legacy API which incorrectly restricted search results
      * Fixed bug in process pipeline/funnel views which prevented contact records from displaying even if the current user had permission to view them
      * Fixed bug in process funnel view which prevented per-stage grid views from updating when a different process was selected






# Introduction #
Welcome to  X2Engine!
X2Engine is a next-generation,  open source social sales application for small and 
medium sized businesses.  X2Engine  was designed to  streamline  contact and sales 
actions into  one  compact blog-style user interface.  Add to this contact  and
colleague social feeds  and  sales  representatives  become  smarter  and  more
effective resulting in increased sales and higher customer satisfaction.

X2Engine is  unique  in the  crowded  Customer Relationship Management (CRM) field 
with its compact blog-style user interface. Interactive and collaborative tools 
which  users are already  familiar  with from  social networking  sites such as  
tagging,  pictures,  docs,  web pages,  group chat, discussions boards and rich 
mobile and tablet apps are combined within a  compact  and  fast  contact sales 
management application. Reps  are  able  to  make  more  sales  contacts  while 
leveraging the combined  social intelligence of peers enabling them to add more 
value to their customer interactions resulting in higher close rates. 

# Documentation and Support #
* [Community Forums](http://x2community.com/)
* [Wiki](http://wiki.x2engine.com)
* [Class Reference](http://doc.x2engine.com/)
* [Live Demo Server](http://demo.x2engine.com/)

# System Requirements #
* A web server that can execute PHP
* A password-protected MySQL database server connection, and a database on 
  which the user of the connection has full permissions rights (i.e. SELECT, 
  DROP, CREATE and UPDATE)
* PHP 5.3 or later
* PHP must be run as the same system user that owns the directory where X2Engine 
  will be installed
* The server must have internet access for automatic updates
* The server must be publicly accessible for web lead capture, service requests 
  and email tracking to work

X2Engine comes with a requirements check script, 
[requirements.php](https://x2planet.com/installs/requirements.php) (also can be 
found in x2engine/protected/components/views), which can be uploaded by itself 
to your server. Simply visit the script in your browser to see if your server 
will run X2Engine.

# Installation #
1. Upload X2Engine to the web directory of your choice. Be sure to set your FTP 
   client to use binary mode.
2. Create a new MySQL database for X2Engine to use
3. Browse to the x2engine folder and you will be redirected to the installer.
4. Fill out the form, click install, and that's it!
5. You are now ready to use X2Engine.  If you chose to install Dummy Data,  you 
   will have numerous sample records (i.e. about 1100 contacts) to play with.

# Languages #
Most of the  included language packs were produced by  copy/paste  from  Google 
Translate.  If you have any  corrections,  suggestions or custom 
language packs, please feel free to post them on www.x2community.com

We greatly appreciate your input for internationalization!


# Tips and Tricks #
X2Engine  is designed to be intuitive,  but we have included a few tips and tricks 
to get you started!
* To change the background color,  menu color,  language  or any other setting, 
  click on Profile in the top right and select 'Settings'.
* The admin's settings  can be found from the admin page,  as well as a variety 
  of other tools to help you manage the application.
* Contacts are ordered by most  recently  updated  by default,  but this can be 
  changed by clicking on one of the other attributes to sort them differently.
* It is not recommended to use the Import Data function on the admin tab UNLESS 
  you are importing data that was exported from a  prior version.  The template 
  is very finnicky and prone to bugs,  so if you do it  without  using properly 
  exported data, we take no responsibility for errors.


# Known Issues #
- The  .htaccess  file  may  cause  issues  on  some  servers.  If  you  get  a 
  500 Internal Server Error  when you  try  to load the installer,  delete  the
  .htaccess file (the application will still work without it.)
- eAccelerator may cause PHP errors on various pages  ("Invalid Opcode").  This 
  is due to a bug in eAccelerator, and can be fixed by disabling or updating
  eAccelerator. Furthermore, eAccelerator causes PHP to fail when using 
  anonymous functions. In general, it is recommended that you disable 
  eAccelerator altogether.
- Version 2 of the API will not work in a web directory that is password-protected.
  This is because there can only be one "Auth" header in HTTP requests, and the web
  server would in this case require an Auth header distinct from the one required 
  to authenticate with the API.
