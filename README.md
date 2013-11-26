# X2CRM 3.6 #
11/21/2013

New features in this release (see [CHANGELOG](CHANGELOG.md) for full history)

* Improvements to user preferences
  * User option to disable notifications popup
  * User option to transform all phone numbers into "tel:" links for click-to-call functionality with VoIP systems
  * Options page sections remember user's preference to be open/closed
  * General options page UI improvements
* Time tracking on records using the publisher
  * New "time log" note type displays time spent on a record
  * Specify begin time, end time and duration of logged calls
* Interface for creating automatic, unattended software update cron task (available on compatible Linux/UNIX servers only)
* The ability to remove contacts from static lists via mass-update action in grid view
* "+" button to add a new account/contact on-the-fly now available in custom modules or any model with account or contact look-up fields
* Fractional quantities in quote line items
* "External/Public Base URL" setting controls how URLs to public-facing resources will be generated, i.e. for CRM systems hosted within private subnets or VPNs
* New in Professional Edition:
  * Targeted content marketing feature (beta)
    * Embeddable, dynamic content for websites tailored to each contact
    * X2Flow-based design interface allows unlimited sophistication in rules and criteria for targeted content
  * Better pattern matching in email dropbox
  * Cron table management console: one page controls all X2CRM-related server cron tasks
  * New and improved pure-JavaScript-based website activity listener and lead capture form compatible with more web browsers
* Fixed Bugs:  
  * [119](http://x2software.com/index.php/bugReports/119): is_file(): open_basedir restriction in effect. File(/usr/share/pear544/Calendar.php) is not within the allowed path(s): (/usr/wwws/users/vanwean:/usr/www/users/vanwean:/usr/home/vanwean:/usr/local/rmagic:/usr/www/users/he/_system_:/usr/share/php544:/  
  * [413](http://x2software.com/index.php/bugReports/413): CDbCommand failed to execute the SQL statement: SQLSTATE[HY093]: Invalid parameter number: parameter was not defined  
  * [462](http://x2software.com/index.php/bugReports/462): Failed to create directory ../../../../backup  
  * [469](http://x2software.com/index.php/bugReports/469): The system is unable to find the requested action "profile".  
  * [487](http://x2software.com/index.php/bugReports/487): Unable to resolve the request "view/view/view".  
  * [514](http://x2software.com/index.php/bugReports/514): Flagging one role as admin gives other roles admin access  
  * [517](http://x2software.com/index.php/bugReports/517): Undefined index: id  
  * [564](http://x2software.com/index.php/bugReports/564): The requested page does not exist.

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
