# 5.0.9 #
7/13/2015

* Email addresses in To, Cc, and Bcc lists in X2Flow email actions now validate
  when flows are saved or triggered. Invalid email addresses in any of these
  fields will prevent the flow from being saved (displaying a warning in the
  UI). For existing flows, X2Flow Email actions with invalid email addresses
  will be skipped (with a validation error message written to the trigger log).
* The old "User" option of the "Post to Activity Feed" flow action has been
  relabelled "Author".

# 4.2b #
8/7/2014

* Structural changes to the record view widget code breaks compatibility with 
  old custom record view widgets.

# 4.1.6b #
7/3/2014

* The "required" validation rule now applies, globally, to all instances of 
  updating as well as all instances of saving. Users can now no longer save 
  and then empty the field after going back to edit. This may disrupt existing 
  workflows that depended on this erroneous behavior.

# 4.1.2b #
5/30/2014

To update to this and all future beta releases, you must enable beta releases,
as follows:

1. Create a file "constants-custom.php" in the root level of X2Engine. You can 
   easily do this by renaming "constants-custom.example.php" to that name 
   (remove ".custom").
2. Look for the following line, and in it, change "false" to "true":
  <pre>defined('X2\_UPDATE\_BETA') or define('X2\_UPDATE\_BETA',false);</pre>


# 4.1 #
5/20/2014

* **Usernames are now case-sensitive.**
* __It has come to our attention that the very old "calendar permissions"
  feature does not behave entirely as it was originally designed.__ Granting one
  user edit permissions to another's calendar does not actually grant
  permissions to edit the event itself, except in regard to the ability to
  change the date of events via drag-and-drop. This is because event records are
  actions, and hence the ability to edit an action is subject to the access
  rules of the Actions module. This old feature (calendar permissions) was
  designed before RBAC was employed in X2Engine to permit finer control over who
  can edit what. In previous versions this would result in a bug, whereupon 
  submitting the form to edit an action would result in no response/change (a
  403 status from the server) when the user did not actually have access via
  their Actions module permissions to edit the action. In this version, editing
  of events is disabled in the UI when the action record itself cannot be
  directly edited. Please note this, to avoid confusion; granting edit access on
  a calendar will not always allow other users to actually change aspects of the
  event record such as the description, association, color, and assignment. _It
  will merely guarantee that the other user will be able to drag/drop events to
  different dates._
  

# 4.0 #
3/20/2014

* "X2Touch", the mobile layout, is no more. In its place will be a responsive,
  mobile-friendly layout.   
* The formula-parsing method used in X2Flow has changed to improve security and 
  stability. Principally, it no longer filters/sanitizes the input by blacklisting
  patterns in the input. Furthermore owing to how the input needs to be evaluated
  as PHP code, all replacement values are escaped using 
  [var_export](http://php.net/var_export). The previous design was found to be 
  problematic in numerous ways, most notably how character sequences would remain
  unquoted in the resulting code (which would cause "undefined constant" errors
  unless the end user explicitly inserted quotes around it, and somehow enforced
  the content of the referenced field never containing quotes).

# 3.7.3b #
2/14/2014

* This version, while it is mainly oriented towards fixing bugs, contains some
  extreme, far-reaching changes (for example, the refactoring of how lookup fields
  work) that may have introduced bugs that we have not seen/foreseen in our tests.
  For that reason, this release has been deemed a beta, and a stable follow-up release
  can be expected later.
* From this version forward, the name of this software is **X2Engine**. If you see
  any references to "X2CRM", "X2Contacts" or "X2EngineCRM" in UI messages, please
  report it as a bug. If you see any such references in the Wiki, or in pinned forum
  posts, or in the X2Engine User Reference Guide, please post in 
  [this thread](http://x2community.com/topic/1482-branding-consistency/) or follow
  the instructions for contacting the X2Engine team.

# 3.7.1 #
12/23/2013

* For security purposes, the web lead form no longer permits setting options via
  query parameters, with the exception of CSS (i.e. color); rather, all options
  are now stored server-side. Existing web lead forms will thus need to be updated.

# 3.7 #
12/23/2013

* In previous versions, the "greater than" and "less than" comparison operators 
  in X2Flow incorrectly resulted in the comparisons "greater than or equal to" 
  and "less than or equal to", respectively. In this version, that behavior is
  corrected, and "greater than" and "less than" strictly mean greater than and
  less than. This will change all existing flows that use these operators in
  value comparisons; the comparisons will not hold true if values being compared
  are equal.
* To accomodate flows that depend on "less than or equal to" and "greater than 
  or equal to" comparisons, new operators for these comparisons are available 
  to building X2Flow criteria. All existing flows depending on the erroneous
  behavior of "less than" and "greater than" (which held true even if the values
  being compared were equal) should be modified to use these new operators.

# 3.6.2 #
11/26/2013

* Hosting one's CRM on a different domain name than one's website will increase 
  the likelihood of public-facing resources like targeted content, web forms and
  the web tracker not working properly in some browsers. This is due to the 
  default privacy settings in such browsers, which have "third-party" cookies
  disabled. To rectify this issue, see the instructions on the "Public Info 
  Settings" page (accessed through the "Admin" page). The configurable public web
  root URL setting allows one to specify a distinct "external" base URL to use for
  public-facing resources. 
* In this release, the previous changes (in 3.6) that would have allowed generic
  applicability of the "+" button (to create new account or contact, displayed next
  to the inputs for link-type fields of those types) have been reverted. This is
  because, as it turns out, would require far more clean-up and refactoring of 
  old relationships JavaScript code that there hasn't been enough time yet to 
  perform. The "+" button was non-functional in 3.6 for this reason.

# 3.6.1 #
11/22/2013

* The targeted content embed method has been changed to resolve the previous 
  issue with Internet Explorer. As such, any embed codes generated in 3.6 will
  not work in this and future versions. To fix this issue, it will be necessary 
  to re-generate the code and use it to replace the existing embedded code on 
  your website.

# 3.6 #
11/21/2013

* The targeted content feature is designated "beta" because, in this version, 
  the embeddable code causes problems in Internet Explorer 8 and 9 when embedded
  in pages containing multiple iframes.

# 3.1 #
6/18/2013

* In the deletion action of the API, the primary key can now be specified in
  either the GET or POST parameters. This way, the "DELETE" request type can be
  used for deletion, and not just the POST type of request.

# 3.0.1 #
5/13/2013

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

# 3.0 #
5/1/2013

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

# 2.1.1 #
10/15/2012

* Note: Any existing changelog data will be preserved, but not visible in the
  changelog table. In the next update we will include code to convert this data
  to the new format.
