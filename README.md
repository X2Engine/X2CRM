# X2CRM 3.5 #
Point release 3.5.5: 10/16/2013

## Changes in 3.5.5 ##
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

## Changes in 3.5.2 ##
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

## Changes in 3.5, 3.5.1 ##
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

## Release Notes ##

### 3.1 ###
* In the deletion action of the API, the primary key can now be specified in
  either the GET or POST parameters. This way, the "DELETE" request type can be
  used for deletion, and not just the POST type of request.

### 3.0.1 ###
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

### 3.0 ###
* X2Flow, the automation designer, while largely complete, is still in active 
  development, and thus has been deemed a "beta" feature.
* Quotes created before updating to 3.0 may display incorrect totals in email,
  print and inline views. This can be easily corrected by opening the update 
  view of the quote and saving it (even without any changes). This is due to 
  how, in previous versions, totals weren't stored in quote records, but rather 
  were re-calculated on-the-fly wherever they were displayed. This required 
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



# Introduction #
Welcome to  X2CRM!
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
* PHP must be run as the same system user that owns the directory where X2CRM 
  will be installed
* The server must have internet access for automatic updates
* The server must be publicly accessible for web lead capture, service requests 
  and email tracking to work

X2CRM comes with a requirements check script, "requirements.php", which you can 
upload by itself to your server. Simply visit the script in your browser to see 
if your server will run X2CRM.

# Installation #
1. Upload X2Engine to the web directory of your choice. Be sure to set your FTP 
   client to use binary mode.
2. Create a new MySQL database for X2Engine to use
3. Browse to the x2engine folder and you will be redirected to the installer.
4. Fill out the form, click install, and that's it!
5. You are now ready to use X2Engine.  If you chose to install Dummy Data,  you 
   will have about 1100 contacts, 125 actions, and 30 accounts to play with.


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
  eAccelerator.
