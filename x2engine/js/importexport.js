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




if (typeof x2 == 'undefined')
    x2 = {};
if (typeof x2.importer == 'undefined') {
    x2.importer = {
        batchSize: 25,
    };
}

x2.importer.interpretPreparationResult  = function(data) {
    var msg;
    var success = false;
    switch (data[0]) {
    case '0':
        msg = x2.importer.messageTranslations.success;
        success = true;
        break;
    case '1':
        msg = x2.importer.messageTranslations.failCreate + data[1] + "<br /><br />";
        success = false;
        break;
    case '2':
        msg = x2.importer.messageTranslations.failConflicting + data[1] + "<br /><br />";
        success = false;
        break;
    case '3':
        msg = x2.importer.messageTranslations.failRequired + data[1] + "<br /><br />";
        success = false;
        break;
    case '4':
        var fields = data[1];
        var confirmMsg = x2.importer.messageTranslations.confirm + fields;
        if (window.confirm(confirmMsg)) {
            msg = x2.importer.messageTranslations.success;
            success = true;
        } else {
            msg = x2.importer.messageTranslations.aborted;
            success = false;
        }
        break;
    }
    return {
        success: success,
        msg: msg
    };
}

x2.importer.showPreparationResult = function(success, msg) {
    if (success) {
        $('#import-status').show();
        $('#import-progress-bar').show();
        x2.importer.loadingThrobber = auxlib.pageLoading();
        $('#prep-status-box').css({'color':'green'});
        $('#prep-status-box').html (msg + ' ' + x2.importer.messageTranslations.begin);
        x2.importer.importData (x2.importer.batchSize);
    } else {
        $('#super-import-map-box').show();
        $('#import-map-box').hide();
        $('#import-container').show();
        $('#prep-status-box').css({'color':'red'});
        $('#prep-status-box').html(msg);
    }
}

x2.importer.prepareImport = function(){
    $('#import-container').hide();
    x2.importer.batchSize = $('#batch-size').val();
    var attributes=[];
    var keys=[];
    var forcedAttributes=[];
    var forcedValues=[];
    var comment="";
    var routing=0;
    var skipActivityFeed=0;
     
    var matchAttribute = $('#update-field').val();
     
    $('.import-attribute').each(function(){
        attributes.push ($(this).val());
        keys.push ($(this).attr('name'));
    });
    if($('#fill-fields-box').is(':checked')){
        $('.forced-attribute').each(function(){
        forcedAttributes.push($(this).val());
        });
        $('.forced-value').each(function(){
            forcedValues.push($(this).val());
        });
    }
    if($('#log-comment-box').is(':checked')){
        comment=$("#comment").val();
    }
    if($('#lead-routing-box').is(':checked')){
        routing=1;
    }
    if($('#activity-feed-box').is(':checked')){
        skipActivityFeed=1;
    }
    var createRecords = '';
    if ($('#create-records-box').is(':checked')) {
        createRecords = 'checked';
    }
    // Gather specified linked record selectors
    var linkMatchMap = {};
    $('.linkMatchSelector').each (function() {
        var field = $(this).siblings ('.import-attribute').val();
        var matchAttribute = $(this).val();
        if (matchAttribute !== '')
            linkMatchMap[field] = matchAttribute;
    });
         
    var updateRecords = '';
    if ($('#update-records-box').is(':checked')) {
        updateRecords = 'checked';
    }
     

    $.ajax({
        url:'prepareModelImport',
        type:"POST",
        data:{
            attributes:attributes,
            keys:keys,
            forcedAttributes:forcedAttributes,
            forcedValues:forcedValues,
            createRecords:createRecords,
            tags:$('input[type=text]#tags').val(),
            comment:comment,
            routing:routing,
            skipActivityFeed:skipActivityFeed,
            model: x2.importer.model,
            linkMatchMap: linkMatchMap,
            preselectedMap: (x2.importer.preselectedMap && !x2.importer.modifiedPresetMap),
            updateRecords: updateRecords,
            matchAttribute: matchAttribute
             
        },
        success:function(data){
            data=JSON.parse(data);
             
            if (updateRecords === 'checked' && typeof data['nonUniqMatchAttr'] !== 'undefined') {
                var attr = data['nonUniqMatchAttr'];
                if (! window.confirm (x2.importer.messageTranslations.nonUniqueMatch + attr)) {
                    $("#continue-box").show();
                    return;
                }
            }
             
            if (typeof data['nonUniqAssocMatchAttr'] !== 'undefined') {
                var attrs = data['nonUniqAssocMatchAttr'];
                if (! window.confirm (x2.importer.messageTranslations.nonUniqueAssocMatch + '\n\n' + attrs)) {
                    $("#continue-box").show();
                    return;
                }
            }
            var result = x2.importer.interpretPreparationResult (data);
            x2.importer.showPreparationResult (result.success, result.msg);
        },
        error:function(){
            $('#prep-status-box').css({'color':'red'});
            $('#prep-status-box').html(x2.importer.messageTranslations.aborting);
        }
    });
}

x2.importer.importData = function(count) {
    $.ajax({
        url:'importModelRecords',
        type:"POST",
        data:{
            count:count,
            model: x2.importer.model
        },
        error: function (data) {
            alert (data.responseText);
        },
        success:function(data){
            data=JSON.parse(data);
            if (data[0]!=1) {
                str=data[1] + x2.importer.messageTranslations.modelsImported;
                created=JSON.parse(data[3]);
                for(type in created){
                    if(created[type]>0){
                        str += "<br />" + created[type] + " <b>" + type + "</b> "
                                + x2.importer.messageTranslations.modelsLinked;
                    }
                }
                $('#status-box').html(str);
                if(data[2]>0){
                    str = data[2] + x2.importer.messageTranslations.modelsFailed;
                    $("#failures-box").html(str);
                }
                // Increment the progress bar counter
                $('#x2-progress-bar-container-x2import').data('progressBar').incrementCount (Number(count));
                x2.importer.importData(count);
            } else {
                str = data[1] + x2.importer.messageTranslations.modelsImported;
                created=JSON.parse(data[3]);
                for(type in created){
                    if(created[type]>0){
                        str += "<br />" + created[type] + " <b>" + type + "</b> "
                                + x2.importer.messageTranslations.modelsLinked;
                    }
                }
                $('#status-box').html(str);
                if(data[2]>0){
                    str=data[2]+ x2.importer.messageTranslations.modelsFailed + ' '
                        + x2.importer.messageTranslations.clickToRecover;
                    $("#failures-box").html(str);
                    $('#download-link').click(function(e) {
                        e.preventDefault();  //stop the browser from following
                        window.location.href = x2.importer.failedRecordsUrl;
                    });
                }

                $('#continue-box').show();
                if ($('#failures-box').html().trim().length > 0) {
                    // Present a button to rollback if there were any failures
                    $('#revert-btn').show();
                }

                // Fill the progress bar
                var maxCsvLines = $('#x2-progress-bar-container-x2import').data('progressBar').getMax();
                $('#x2-progress-bar-container-x2import').data('progressBar').updateCount (maxCsvLines);

                $.ajax({
                    url:'cleanUpModelImport',
                    complete:function(){
                        var str="<strong>" + x2.importer.messageTranslations.complete + "</strong>";
                        $('#prep-status-box').html(str);
                        x2.importer.loadingThrobber.remove();
                        alert (x2.importer.messageTranslations.complete);
                    }
                });
            }
        }
    });
}

/**
 * Present a dropdown for selecting which linked model attribute is
 * used to locate the associated record
 */
x2.importer.showLinkFieldMatchSelector = function(elem) {
    var selected = $(elem).val();
    var linkedModel = x2.importer.linkFieldModelMap[selected];
    var attributeId = $(elem).attr('id');
    var dropdown = x2.importer.linkedRecordDropdowns[linkedModel];
    $(elem).parent()
        .append (dropdown)
        .append (x2.importer.linkFieldHint)
        .css ('border', '1px dashed');
    $(elem).siblings('#attr').attr ('id', attributeId + '-linkSelector');
};

x2.importer.prevRecord = function(){
    $('.record-'+record).hide();
    if (record==0) {
        record = x2.importer.numSampleRecords - 1;
    } else {
        record--;
    }
    $('.record-'+record).show();
}

x2.importer.nextRecord = function(){
    $('.record-'+record).hide();
    if (record == x2.importer.numSampleRecords - 1) {
        record=0;
    } else {
        record++;
    }
    $('.record-'+record).show();
}

x2.importer.createDropdown = function(list, ignore) {
    var sel = $(document.createElement('select'));
    $.each(list, function(key, value) {
        if ($.inArray(key, ignore) == -1) {
            sel.append('<option value=\"' + key  + '\">' + value + '</option>');
        }
    });
    return sel;
}

x2.importer.createAttrCell = function(){
    var div = $(document.createElement('div'));
    div.attr('class', 'field-row');
    var dropdown = x2.importer.createDropdown(x2.importer.attributeLabels);
    dropdown.attr('class', 'forced-attribute');
    var input = $('<input size="30" type="text" value="" class="forced-value">');
    input.attr('name', 'force-values[]');
    var link= $('<a href="#" class="del-link clean-link">[x]</a>');
    return div.append(dropdown).append(input).append(link);
}

