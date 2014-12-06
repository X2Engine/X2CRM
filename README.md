# X2Engine 5.0 #
12/5/2014

New in 5.0 (see [CHANGELOG](CHANGELOG.md) for full history):

* **Highlights**
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
