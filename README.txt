README

INSTRUCTIONS FOR UPDATE PLEASE READ

If you have any custom code please save it before updating and modify the new files.  We will be working on a more concrete system for preserving your code in the future.

Beta 0.9.9 Changelog

- Numerous bug fixes (esp. gridviews)
- Misc. UI tweaks/improvements
- Added advanced outgoing email config, using phpMailer (supports sendmail, qmail and SMTP)
- Added email signatures (global template based, or user defined)
- Added icons for record History view
- Reorganized some admin settings
- Added "enable background fade" setting to profile
- Rebranded app to X2CRM
- Added timezone setting in installer
- Added form layout editor (work in progress)
- Added facebook-enabled web capture form (automatically creates contact, attaches photo if available)

Welcome to X5CRM v0.9.9 (Beta 10)!  X5CRM is a next-generation, open source social sales application for small and medium sized businesses. 

X5CRM was designed to streamline contact and sales actions into one compact blog-style user interface. Add to this contact and colleague social feeds and sales representatives become smarter and more effective resulting in increased sales and higher customer satisfaction.

X5CRM is unique in the crowded Customer Relationship Management (CRM) field with its compact, blog-style user interface. Interactive and collaborative tools which users are already familiar with from social networking sites such as; tagging, pictures, docs, web pages, group chat, discussions boards and rich mobile and iPad apps are combined within a compact and fast contact sales management application. Reps are able to make more sales contacts while leveraging the combined social intelligence of peers enabling them to add more value to their customer interactions resulting in higher close rates. 

1) INSTALLATION
	
To install X5CRM, please extract and upload the X2Engine folder to your web root.  Navigate to the location in your browser and you will be redirected to the installer.  Fill out the form, click install, and that's it!  You are now ready to use X5CRM.
	
If you selected to install Dummy Data, you should have about 150 contacts and 120 actions installed, along with around 30 accounts to play around with.
	
2)  LANGUAGES

Languages are very primitive at this point, we used Google Translate and copy/paste.  If you have any corrections/suggestions/language packs, please feel free to post them on www.x2community.com  

We greatly appreciate any support with internationalization!
	
3) TIPS AND TRICKS

X5CRM is designed to be intuitive, but we have included a few tips and tricks to get you started!

-To change the background color, menu color, language or any other setting, click on the Profile (username) button in the top right and select 'Settings.'

-The admin's settings can be found from the admin page, as well as a variety of other tools to help you manage the application.

-There is an accounts module which is disabled by default and can be turned on by selecting the "Manage Menu Items" link from the admin page.

-Contacts are ordered by most recently updated by default, but this can be changed by clicking on one of the other attributes to sort them differently.

-There is an Email Dropbox which can be set up to run on your server.  To do so, you must create an email alias called "dropbox@[your_server].com" and have it forward to the email.php script in the X2Engine root folder.

Additionally, you need to go in to the email.php file and edit the following line:

require_once("/home/x3engine/public_html/x2jake/protected/config/emailConfig.php");

To the filepath to emailConfig.php on your server.
This dropbox will add any conversations you have with a contact to the comments for that contact if you CC the address. Additionally, if it doesn't recognize the the person you're talking to, it will automatically add them to the database!

*Note, this functionality has not been tested thoroughly but we believe it to be working pretty well.

-It is not recommended to use the Import Data function on the admin tab UNLESS you are importing data that was exported from a prior version.  The template is very finnicky and bug prone, so if you do it without using properly exported data, we take no responsibility for errors.
	
4) KNOWN ISSUES
-If you remove #tags from a document, the tag records themselves will not be deleted from the database.  We are working on a fix for this.

-The .htaccess file may cause issues on some servers.  If you get a 500 Internal Server Error when you try to load the installer, delete the .htaccess file, the application will work without it.