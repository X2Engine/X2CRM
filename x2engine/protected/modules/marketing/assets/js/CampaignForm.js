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




/**
 * Static Class to handle everything on the campaign form.
 * This class is the result of making the old Campaign form code object oriented. 
 * The old code was put in the function setUpForm.
 */
x2.CampaignForm = (function() {

    function CampaignForm(argsDict) {
        var defaultArgs = {
            insertableAttributes: {}
        };

        auxlib.applyArgs (CF, defaultArgs, argsDict);
        CF.init();
    }

    // shorthand for CampaignForm
    var CF = CampaignForm;

    CampaignForm.init = function() {
        // Old Code in this function
        CF.setUpForm();

        // quick create form for contact lists and Suppression List
        CF.quickCreateForm = $('#quick-create-list-form');
        CF.quickSuppressionCreateForm = $('#quick-create-suppression-list-form');

        // Set up Quick Create Form
        CF.setUpQuickCreate();

        // Setup the button that lets the user save the template
        CF.setUpTemplateSubmit();
        
        //set up the button the hides supprestion list
        CF.setUpHide();

    };
     /**
     * make it so the suppression list is hiddin
     */
    CampaignForm.setUpHide = function (){
        $('#supButton').click(function(){
            
            var elem = document.getElementById("supButton");
            elem.style.display = "none";
            var elem = document.getElementById("supRow");
            elem.style.display = "";
        });
    }
    

    /**
     * Because some fields are added outside of the form view, 
     * We will disable those fields if they are in the form.
     */
    CampaignForm.setUpRestrictedFields = function () {
        $('.form-view').find('#Campaign_name, #Campaign_templateDropdown, #Campaign_listId').each(function(){
            $(this).closest('.formItem').css('opacity', 0.5);
            $(this).attr('id', '')
                .attr('name', '')
                .attr('disabled', 'true')
                .attr('title', "This field is used in another part of the form.");
        });
    }

    /** 
     * 'Legacy' Campaign form code
     */
    CampaignForm.setUpForm = function(){
        $("#Campaign_content").parent().css({width:"100%"});
            
            // .removeClass("formInputBox")
            // .closest(".formItem")
            // .removeClass("formItem")
            // .css("clear","both")
            // .find("label").remove();

        x2.emailEditor.setupEmailAttachments("campaign-attachments");
        $("#Campaign_template").change(function() {
            var template = $(this).val();
            if(template != "0") {
                $.ajax({
                    url: yii.baseUrl + "/index.php/docs/fullView/" + template,
                    data: {
                        json: 1,
                        replace: 1
                    },
                    dataType:"json",
                    success: function(data) {
                        window.emailEditor.setData (data.body);
                        $('input[name="Campaign[subject]"]').val(data.subject);
                        window.emailEditor.document.on(
                            "keyup",function(){ $("#Campaign_template").val("0"); });
                    }
                });
            }
        });

        function campaignTypeChangeHandler () {
            if($(this).val() == "Email") {
                $("#Campaign_sendAs").parents(".formItem").show();
                $("#Campaign_enableRedirectLinks").parents(".formItem").show();
                $("#attachments-container").show ();
            } else {
                $("#Campaign_sendAs").parents(".formItem").hide();
                $("#Campaign_enableRedirectLinks").parents(".formItem").hide();
                $("#attachments-container").hide ();
            }
        
            // give x2layout section an appropriate title, hide/show insertable attributes
            var campaignType = $("#Campaign_type").val ();  
            switch (campaignType) {
                case "Email":
                    var campaignTypeChanged = "Email" !== currCampaignType;
                    currCampaignType = campaignType;
                    if (campaignTypeChanged) CF.setUpTextEditor (false);
                    break;
                case "Call List":
                case "Physical Mail":
                    var templateTypeChanged = currCampaignType !== "Email" && campaignType === "Email";
                    currCampaignType = campaignType;
                    if (campaignTypeChanged) CF.setUpTextEditor (false);
                    break;
            }
        }
        
        var currCampaignType = "";
        $("#Campaign_type").change(function(){
            campaignTypeChangeHandler.call (this);
        });
        
        $("#Campaign_type").each(function(){
            if($(this).val() != "Email")
                $("#Campaign_sendAs").parents(".formItem").hide();
        });
        
        campaignTypeChangeHandler.call ($('#Campaign_type').get (0));
    }

    /**
     * Called from within setUpForm
     * Part of the legacy code
     */
    CampaignForm.setUpTextEditor = function(suppressInsertableAttrs) {
        if(window.emailEditor) {
            window.emailEditor.updateElement ();
            window.emailEditor.destroy(true);
        }

        if (suppressInsertableAttrs) {
            window.emailEditor = createCKEditor("Campaign_content",{
                tabIndex: 5,
                fullPage: false
            },function(){
                window.emailEditor.document.on("keyup",function(){ 
                    $("#Campaign_template").val("0"); 
                });
            });
        } else {
            window.emailEditor = createCKEditor("Campaign_content",{
                tabIndex:5,
                insertableAttributes: CF.insertableAttributes,
                fullPage: false
            },function(){
                window.emailEditor.document.on("keyup",function(){ 
                    $("#Campaign_template").val("0"); 
                });
            });
        }
    }    

    /**
     * function called on the click of contact list/suppression list button with appropriate params.
     */
    CampaignForm.setUpQuickCreate = function() {
        $('#quick-create-list').click({formId: 'quickCreateForm', formField: 'listId'}, CF.quickCreateFromFunction);
        $('#quick-create-suppression-list').click(
            {formId: 'quickSuppressionCreateForm', formField: 'suppressionListId'},
            CF.quickCreateFromFunction
        );
    }

    /**
     * Sets up the behavior of the quick create contact list/suppression list button.
     * The form is loaded lazily.
     * @param event Data from the click event
     */
    CampaignForm.quickCreateFromFunction = function(event) {
        // Only append form if one isn't there already.
        var CFformID = CF[event.data.formId];
        if (CFformID.find('.form').length > 0) {
            CFformID.slideToggle();
            return;
        }

        // This action was modified to allow for ajax
        $.ajax({
            url: yii.scriptUrl + '/contacts/createList',
            data: {
                ajax: 1
            },
            success: function(data) {
                if (CFformID.find('.form').length == 0) {
                    CF.appendQuickCreate (data, CFformID, event.data.formField);
                }
            }
        });
    }

    /**
     * Appends the quick create form to the DOM and sets up
     * handlers on its submit function
     * @param data Data from the AJAX Request
     * @param CFformID form id name
     * @param formField field name
     */
    CampaignForm.appendQuickCreate = function(data, CFformID, formField) {
        $(data).appendTo (CFformID);

        // remove the page title from the response
        CFformID.find('.page-title').remove();

        // Open the form 
        CFformID.slideToggle();

        var form = CFformID.find('form');

        // rename submit button to avoid collision with submit button on page. 
        var submit = form.find('#save-button').attr('id', 'contact-list-save-button');

        // Set up handler
        submit.click(function(e){
            e.preventDefault();
            $.ajax({
                method: 'post',
                url: form.attr('action'),
                data: form.serialize(),
                dataType: 'json',
                success: function(data) {

                    // Create a new option for the newly created list
                    var option = $('<option></option>')
                        .attr('value', data.id)
                        .html(data.name);

                    // Append the new option to the select dropdown
                    $('#Campaign_' + formField).append (option).val(data.id);

                    // Close the form
                    CFformID.slideToggle();

                    CFformID.find('.form').remove();
                }
            });
        });
    }

    /**
     * Sets up the ability to save a custom created template
     * It uses the campaign form but renames the attributes to 
     * be able to submit it to the createEmail action
     */
    CampaignForm.setUpTemplateSubmit = function() {
        $('#save-template').click(function(){

            // convert form to JSON
            var form = auxlib.formToJSON('#campaign-form');

            // function to get an attribute from the JSON
            // (formToJSON does not consider arrays)
            var key = function(k) {
                return form['Campaign[' + k + ']'];
            }

            // Build the request Docs is expecting
            // Reroute attributes
            var ajax = {
                Docs: {
                    visibility: key('visibility'),
                    name: key('name'),
                    subject: key('subject'),
                    text: key('content'),
                    assignedTo: key('assignedTo'),
                },
            };

            // If no name is provided, prompt for one. 
            if (!ajax.Docs.name) {
                ajax.Docs.name = prompt("Name of template");
            }

            // Send the request
            $.ajax({
                type: 'post',
                url: yii.scriptUrl + '/docs/createEmail?ajax=true',
                data: ajax,
                dataType: 'json',
                success: function(data) {
                    // Show success
                    x2.topFlashes.displayFlash(data.name + " Created.", 'success');
                    
                    // Append option to the dropdown of templates
                    $('<option></option>').appendTo ('#Campaign_template')
                        .attr ('value', data.id)
                        .html (data.name)
                        .parent().val (data.id);
                    
                    // Sort dropdown by lower lex, 'Custom' on top
                    var select = $('#Campaign_template');
                    select.html(select.find('option').sort(function(x, y) {
                        if ($(x).text() === 'Custom') return -1;
                        if ($(y).text() === 'Custom') return 1;
                        return $(x).text().toLowerCase() > $(y).text().toLowerCase() ? 1 : -1;
                    }));
                
                }
            });
        });
    }

    return CampaignForm;
})();
