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
    (http://www.yiiframework.com/doc/api/1.1/CModel#errors-detail)[CActiveRecord.getErrors()]
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
