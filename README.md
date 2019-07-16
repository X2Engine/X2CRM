NOTE: IF YOU ARE UPDATING FROM <7.0 YOU WILL NEED TO RE-ENTER YOUR THIRD-PARTY APP CREDENTIALS! 
THIS IS DUE TO THE REMOVAL OF MCRYPT IN PHP 7.2. FOR MORE INFO, PLEASE CONTACT customersupport@x2engine.com

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
