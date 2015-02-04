# 5.0.3 #
1/30/2015

* Highlights
  * New Record Transactional View
    * Provides an alternate view of a record's action history
    * Individual widgets for each type of interaction (Calls, Emails, Actions, etc.)
  * Professional Edition Changes:
    * Charts created through the Reports Module can now be added to the profile dashboard
  * Profile dashboard and record view column widths can now be adjusted
  * Application-wide icon update
  * Email Contact X2Flow action has a new option to enable email logging and tracking
  * Added ability to export user changelog to CSV
  * Record importer now provides a progress bar
* General Changelog / Developer Notes:
  * Updated Yii to version 1.1.16
  * Fixed response handling when verifying application credentials
* Tracked Bug Fixes:
  * [1853](http://x2software.com/index.php/bugReports/1853): Undefined variable: report  
  * [1855](http://x2software.com/index.php/bugReports/1855): Undefined variable: retVal  
  * [1896](http://x2software.com/index.php/bugReports/1896): Argument 2 passed to X2Model::renderModelInput() must be an instance of Fields, null given, called in /marketing/protected/models/X2Model.php on line 2298 and defined  
  * [1899](http://x2software.com/index.php/bugReports/1899): User Report  
  * [1905](http://x2software.com/index.php/bugReports/1905): Class: Charts not found.  
  * [1994](http://x2software.com/index.php/bugReports/1994): Unable to resolve the request "accounts/view/id".  
  * [2004](http://x2software.com/index.php/bugReports/2004): CDbCommand failed to execute the SQL statement: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '' for key 'c_email2'  
  * [2007](http://x2software.com/index.php/bugReports/2007): The system is unable to find the requested action "webleadForm".  
  * [2008](http://x2software.com/index.php/bugReports/2008): CDbCommand failed to execute the SQL statement: SQLSTATE[42S22]: Column not found: 1054 Unknown column 't.actionDescription' in 'order clause'  
  * [2009](http://x2software.com/index.php/bugReports/2009): CDbCommand failed to execute the SQL statement: SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '' at line 3  
  * [2048](http://x2software.com/index.php/bugReports/2048): htmlspecialchars(): Invalid multibyte sequence in argument  
  * [2049](http://x2software.com/index.php/bugReports/2049): 
  * [2086](http://x2software.com/index.php/bugReports/2086):   
  * [2107](http://x2software.com/index.php/bugReports/2107): Property "X2ButtonColumn.name" is not defined.  
  * [2115](http://x2software.com/index.php/bugReports/2115): PublisherProductsTab and its behaviors do not have a method or closure named "renderPartial".  
  * [2119](http://x2software.com/index.php/bugReports/2119): User Report  
  * [2120](http://x2software.com/index.php/bugReports/2120): Invalid email address list




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
