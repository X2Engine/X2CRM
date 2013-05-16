# X2CRM 3.0.1 #
5/13/2013
## Changes ##
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

## New in 3.0.1 ##
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

## Release Notes ##
### 3.0 ###
* The automation designer, while largely complete, is still in active 
  development, and thus has been deemed a "beta" feature.
* Quotes created before updating to 3.0 may display incorrect totals in email,
  print and inline views. This can be easily corrected by opening the update 
  view of the quote and saving it (even without any changes). This is due to 
  how, in previous versions, totals weren't stored in quote records, but rather 
  were re-calculated on-the-fly whereverthere they were displayed. This required 
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




# Introduction #
Welcome to  X2CRM v3.0.1!
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

