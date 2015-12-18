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

# Introduction #
Welcome to X2CRM!
X2CRM is a next-generation,  open source social sales application for small and 
medium sized businesses.  X2CRM  was designed to  streamline  contact and sales 
actions into  one  compact blog-style user interface.  Add to this contact  and
colleague social feeds  and  sales  representatives  become  smarter  and  more
effective resulting in increased sales and higher customer satisfaction.

X2CRM is  unique  in the  crowded  Customer Relationship Management (CRM) field 
with its compact blog-style user interface. Interactive and collaborative tools 
which  users are already  familiar  with from  social networking  sites such as  
tagging,  pictures,  docs,  web pages,  group chat, discussions boards and rich 
mobile and tablet apps are combined within a  compact  and  fast  contact sales 
management application. Reps  are  able  to  make  more  sales  contacts  while 
leveraging the combined  social intelligence of peers enabling them to add more 
value to their customer interactions resulting in higher close rates. 

# Documentation and Support #
* [Community Forums](http://community.x2crm.com/)
* [Wiki](http://wiki.x2crm.com)
* [Class Reference](http://doc.x2crm.com/)
* [Live Demo Server](http://demo.x2crm.com/)

# System Requirements #
* A web server that can execute PHP
* A password-protected MySQL database server connection, and a database on 
  which the user of the connection has full permissions rights (i.e. SELECT, 
  DROP, CREATE and UPDATE)
* PHP 5.3 or later
* PHP must be run as the same system user that owns the directory where X2CRM 
  will be installed
* The server must have internet access for automatic updates
* The server must be publicly accessible for web lead capture, service requests 
  and email tracking to work

X2CRM comes with a requirements check script, 
[requirements.php](https://x2planet.com/installs/requirements.php) (also can be 
found in x2engine/protected/components/views), which can be uploaded by itself 
to your server. Simply visit the script in your browser to see if your server 
will run X2CRM.

# Installation #
1. Upload X2CRM to the web directory of your choice. Be sure to set your FTP 
   client to use binary mode.
2. Create a new MySQL database for X2CRM to use
3. Browse to the x2engine folder and you will be redirected to the installer.
4. Fill out the form, click install, and that's it!
5. You are now ready to use X2CRM.  If you chose to install Dummy Data,  you 
   will have numerous sample records (i.e. about 1100 contacts) to play with.

# Languages #
Most of the  included language packs were produced by  copy/paste  from  Google 
Translate.  If you have any  corrections,  suggestions or custom 
language packs, please feel free to post them on [community.x2crm.com](http://community.x2crm.com)

We greatly appreciate your input for internationalization!


# Tips and Tricks #
X2CRM  is designed to be intuitive,  but we have included a few tips and tricks 
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
