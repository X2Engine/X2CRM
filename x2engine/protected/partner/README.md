# X2Engine Partner Branding Agreement #

The following guidelines define permissible modifications to files, especially
those pertaining to partner-specific branding, for organizations that are
members of the X2Authorized Partner Program. Non-compliance may result
in termination of your enrollment in the program. If you have any questions
or comments, send an email to customersupport@x2engine.com or call us at
831-222-5333.

## Restrictions ##
Under all circumstances, you **MAY NOT**:

- Modify the source code of, or insert into the application's user interface,
  any content in any way that hides, obscures or makes non-visible the "*Powered
  by X2Engine*" logo in any part of the application.
- Edit anywhere other than between code delimeters in any file within the
  directory protected/partner
- Develop a custom version or substitute of the file 
  _protected/views/layouts/footer.php_
- Insert, remove or modify code delimeters in any file.
- Change the position of any existing code delimeter relative to the content of
  the file that precedes it (if a start) or that follows it (if an end).

**Code delimeters** are special markers in the branding files that have the
following appearance:

    /* @start:<name> */
or:

    /* @end:<name> */
For example:

    /* @start:footer */
# Branding Instructions #
The following describes how to add vendor-specific branding to your
organization's implementation of X2Engine. You are permitted to use this
feature if your organization is a member of the X2Engine Authorized Partner
Program.

## Getting Started ##
To begin, edit the file _protected/partner/branding_constants-custom.php_, and
set the constant _X2\_PARTNER\_DISPLAY\_BRANDING_ to "true". If you are viewing
this page within X2Engine, then this is likely already the case (that this
constant is set to true).

Next, to give your product a unique name, set the constant
_X2\_PARTNER\_PRODUCT\_NAME_ in the file to be your product's name.

## Using Template Files ##
Note the placeholder text in various places in the application. The sample
branding content is in several files ("template files") inside of the
_protected/partner_ folder, whose names end in "\_example.php"

To override the templates and insert your own branding, make copies of each of
these files but without "\_example" in their names, and then edit according to
the Branding Guidelines. For instance, you may copy "footer\_example.php to
"footer.php" and then edit the latter file.

## Setting the Product Name ##
To set the product name, define a constant,

## Limitations ##
All modifications that concern branding, with the exception of partner logos and
other images (which can be hosted where the partner may choose), must be made to
files inside of the folder protected/partner inside of X2Engine. Changes can
include arbitrary HTML, PHP, CSS and JavaScript except as described in the
_Branding Agreement_.