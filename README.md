# 5.0.5 #
3/18/2015

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
  * Fixed CSRF vulnerability described here: http://packetstormsecurity.com/files/130820/X2Engine-5.0.4-Platinum-Edition-Cross-Site-Request-Forgery.html
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
  * [2267](http://x2software.com/index.php/bugReports/2267): X2Flow actions which require a model param should check for presence of param before attempting to access it  
  * [2280](http://x2software.com/index.php/bugReports/2280): CDbCommand failed to execute the SQL statement: SQLSTATE[42S22]: Column not found: 1054 Unknown column 't.actionDescription' in 'order clause'  
  * [2286](http://x2software.com/index.php/bugReports/2286): CDbCommand failed to execute the SQL statement: SQLSTATE[22001]: String data, right truncated: 1406 Data too long for column 'fileName' at row 1  
  * [2290](http://x2software.com/index.php/bugReports/2290): Property "EmailInboxes.updatedBy" is not defined.  


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
