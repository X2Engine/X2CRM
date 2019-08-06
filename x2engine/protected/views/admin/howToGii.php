<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/



?>
<h2>DEPRECATED</h2>
<h2>Reasons To Use Gii</h2>
<ul>
    <li>Your web developer is adding a new section to your website, and you need another controller</li>
    <li>You need to add new fields to a database table, and must regenerate a model in the process</li>
    <li>Advanced customization of your website</li>
</ul>


<h2>How To Use Gii</h2>
<p>
    Gii is a code generation tool provided by the Yii Framework.  It should only
    be used for expanding your application, as the default settings will not need
    it.  This is not a toy and you should not use it unless you know what you are
    doing and have a real need.  To begin, you will need to click the "Gii" link on the admin page, and
    login using your administrator password.  
</p>

<p>
    Once you pass the login page, you will see multiple options for what kind of 
    code you might want to generate.  The most useful are probably going to be Model/Form
    and CRUD.  Model will be used for regenerating the model classes after adding
    or removing a field in a database table, and CRUD is probably the most useful
    function for adding new pages.
</p>

<h3>Models</h3>
<p>
    To regenerate a model after the database schema has been changed is relatively
    simple.  All of the models that actually get called are subclasses of the models
    that Gii generates, so as long as you adhere to the naming convention, this 
    inheritance should work fine.  When creating a new model, Gii will ask for the table
    prefix and the table name.  The table prefix is x2_ and should be the default option.
    The table name would be x2_users , x2_contacts , or x2_actions depending on which schema
    you changed.  Simply type the full table name in (including prefix) to the table name field
    and Gii will auto-populate the Model name field.  Click "Preview" and then check the
    box that says "Overwrite" and click generate.  The changes you made to the database schema
    will now be reflected in the model's attributes.
</p>

<p>
    Now at this point you'll probably want to generate a new Form to reflect the change in your 
    schema.  This can be quite an issue as it will overwrite any previous Form formatting
    you had, such as drop down menus or radio buttons.  You'll need to either regenerate
    the form and replace the formatting of drop downs, or you can simply go into the code file
    and add the new code you need for the extra field(s).  I would personally recommend the 
    second option, as you can basically just copy and paste the code from another form
    element but change the variable name to what you need it to be.
</p>

<h3>CRUD</h3>
<p>
    CRUD is an acronym that stands for Create, Read, Update, and Delete.  The CRUD generator
    in Gii allows you to create a controller with the proper actions and views to use all
    of these functions on any model which you have already set up.  Be warned that if you
    use this functionality, it will overwrite any views or controllers already established
    for this particular model, as well as any forms you may have created above.
</p>

