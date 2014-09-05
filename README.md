# X2Engine 4.2 #

New in 4.2 (see [CHANGELOG](CHANGELOG.md) for full history):

* **Highlights**
  * Improvement to role access editor _(Professional Edition)_:
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
  * X2Flow improvements _(Professional Edition)_:
    * Improved X2Flow Remote API Call Action supports custom request headers 
    * New "Has Tags" flow condition
  * Calendar improvements:
    * New weekly agenda view
    * New customizable event subtype and status dropdowns
    * Can now customize event color dropdown
  * Importer improvements:
    * Added preset import maps to transfer records from other systems
    * Action descriptions can now be imported/exported
    * Actions associations will now be verified to ensure the type is known to X2 and that the associated record exists
    * Added a loading throbber to indicate activity
    * Added a timeout warning when max_execution_time is set to 30 seconds or less
  * Static pages can now be created from existing Docs instead of only a new Doc
  * New feature to validate email credentials from the 'Manage Apps' page
  * Improved contact lists grid view
* General changlog/developer notes
  * Patched file upload filter bypass vulnerability
  * Fixed missing link to modify Doc permissions when logged in as admin
  * Fixed issue that caused phone number links to be prepended international dialing codes unconditionally
  * Updated the web lead form to search for duplicate contacts on all custom Contact email fields
  * Fixed issue preventing Automatic Updates settings form from being saved
  * Fixed issue which caused process funnel record counts to be incorrect
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
  * [1401](http://x2software.com/index.php/bugReports/1401): Undefined index: tags 
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
