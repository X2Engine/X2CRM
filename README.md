# X2CRM 3.7 #
Point release 3.7.1 1/23/2014

New in this release (see [CHANGELOG](CHANGELOG.md) for full history)
* Improvements to the fields manager
  * Better input validation, stability and security
  * New option to set default values for fields in new records
* Administrators can set distinct session timeouts for different user roles
* Mass update buttons added to the Actions grid view
* Default form/view will be generated automatically for new custom modules that don't yet have them
* Inline email widget included in custom module generation
* Improvements to column filters
  * Dropdown menu and boolean type fields appear as dropdowns
  * Date type fields provide the convenient datepicker widget so you don't have to type in dates manually
* Re-instated the missing "cancel" button in the star rating input widget to clear a rating field's value
* Signature replacement in campaigns; new "{signature}" placeholder will be replaced with the email signature of the assignee
* Fixed Bugs:  
  * [124](http://x2software.com/index.php/bugReports/124): Gridview filters: True/False vs. Yes/No  
  * [441](http://x2software.com/index.php/bugReports/441): Property "Profile.pageOpacity" is not defined.  
  * [719](http://x2software.com/index.php/bugReports/719): rename(protected/modules/ob\_b/views/default,protected/modules/ob\_b/views/ob\_b) [<a href='function.rename'>function.rename</a>]: Directory not empty  
  * [757](http://x2software.com/index.php/bugReports/757): CDbCommand failed to execute the SQL statement: SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'AND (type I
  * [764](http://x2software.com/index.php/bugReports/764): primary contact field in quote detail view comes out html-encoded  
  * [766](http://x2software.com/index.php/bugReports/766): Backdating actions does not affect activity feed dates  
  * [770](http://x2software.com/index.php/bugReports/770): Number of overdue actions incorrectly displayed in "My Actions" widget  
  * [833](http://x2software.com/index.php/bugReports/833): User Report
  * In Professional Edition:
    * Tag-based trigger / action now works
    * Security vulnerability in web lead form patched; see [release notes](RELEASE-NOTES.md) for full details.
    * Fixed idiosyncratic permissions behavior in the docs module; the "private delete access" permission now grants users the ability to delete their own documents
  * "Sign in as another user" option fixes the previous issue of being unable to switch users after enabling "Remember Me"
  * X2Touch restored
  * In the API, an exception is made so that the "userKey" field of Contacts is not read-only, allowing use of the API for creating properly web-tracked leads
  * Removed deprecated functions that were causing memory exhaustion errors on systems with over 10,000 account records
  * Posts marked as private are properly hidden
  * Numerous other unlisted, long-standing bugs (not recorded in the public bug tracker)


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

X2CRM comes with a requirements check script, 
[requirements.php](https://x2planet.com/installs/requirements.php) (also can be 
found in x2engine/protected/components/views), which can be uploaded by itself 
to your server. Simply visit the script in your browser to see if your server 
will run X2CRM.

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
