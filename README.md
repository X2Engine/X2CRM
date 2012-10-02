# X2EngineCRM 2.0 Changelog #
- New and greatly improved UI
- New features in X2Touch Mobile
- Renamed Sales to Opportunities
- Improved relationships between Contacts, Accounts, and Opportunities
- Added date and user filtering to Workflow view
- Reworked the back-end of access permissions to fit with Yii roles
- Added attachments to Marketing campaigns

# Introduction #
Welcome to  X2EngineCRM v2.0!  X2EngineCRM is a next-generation,  open source
social sales application for small and medium sized businesses. X2EngineCRM was 
designed to streamline  contact and sales actions into  one  compact blog-style 
user interface.  Add to this  contact  and  colleague  social feeds  and  sales 
representatives  become  smarter  and  more  effective  resulting  in increased 
sales and higher customer satisfaction.

X2EngineCRM  is  unique  in the  crowded Customer Relationship Management (CRM) 
field with its compact blog-style user interface. Interactive and collaborative 
tools which users are already familiar with from  social networking  sites such 
as  tagging,  pictures,  docs,  web pages,  group chat,  discussions boards and 
rich  mobile and tablet apps are combined within a  compact  and  fast  contact 
sales management application. Reps are able to make  more  sales contacts while 
leveraging the combined  social intelligence of peers enabling them to add more 
value to their customer interactions resulting in higher close rates. 

# Installation #
1. Extract and upload X2Engine to the web directory of your choice.
2. Create a new MySQL database for X2Engine to use
3. Navigate to the x2engine web folder in your browser and you will be redirected to the installer.
4. Fill out the form, click install, and that's it!
5. You are now ready to use X2Engine. If you chose to install Dummy Data, you will have about 1100 contacts, 125 actions, and 30 accounts to play with.
   

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
