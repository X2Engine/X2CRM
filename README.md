# X2Engine 4.1 #
Follow-up point release 4.1.2
6/6/2014

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
  * [1204](http://x2software.com/index.php/bugReports/1204): Cannot modify header information - headers already sent by (output started at /home3/bigmoney/public_html/knockoutmultimedia.co/crm/protected/controllers/ProfileController.php:516)  
  * [1223](http://x2software.com/index.php/bugReports/1223): Cannot modify header information - headers already sent by (output started at /home/inspirah/public_html/crm/protected/modules/actions/controllers/ActionsController.php:799)
* Changes in 4.1.1 (5/23/2014):
  * Activity feed JS bug fixes
  * Backwards compatibility fixes for ResponseBehavior and X2LinkableBehavior
  * Bug fixes in roles
  * Improvements to webhooks: better payload composition logic + safeguards for systems w/o cURL libraries
  * Lead conversion bug fix
  * (Platinum Edition) "Raw Input" API settings option not saving properly
  * "Linkable Behavior" + custom modules backwards incompatibility
  * Web lead form not respecting when "Create Lead" option is disabled
* Changes in 4.1.2 (5/30/2014):
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
    * [1268](http://x2software.com/index.php/bugReports/1268): Trying to get property of non-object  
    * [1280](http://x2software.com/index.php/bugReports/1280): Emailed quotes not tracked properly  
    * [1295](http://x2software.com/index.php/bugReports/1295): Validation errors not shown when updating an opportunity 

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


# Creating the Action Reminder Cronjob #
As we don't have access to your server, you'll need to create a cronjob to make 
the server send out action reminders. You can either do this on your own server 
or use a free service on the internet to run it for you.  All you need to do is 
have the cronjob access the url once a day to send out action reminders:

    http://www.[yourserver].com/[path to x2engine]/actions/sendReminder

# Languages #
Most of the  included language packs were produced by  copy/paste  from  Google 
Translate and copy/paste.  If you have any  corrections,  suggestions or custom 
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
  eAccelerator.
