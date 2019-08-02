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
 * Manages behavior of report settings forms
 */

x2.ReportForm = (function () {

    function ReportForm(argsDict) {
        var argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
        var defaultArgs = {
            settingsFormSelector: '',
            reportContainerSelector: '',
            primaryModelTypeDropDownSelector: '',
            chartCreatorSelector: '#chart-creator',
            type: '',
            translations: {
                'savedSettingsDialogTitle': '',
                'copyReportDialogTitle': 'Copy Report',
                'cancel': '',
                'saveButton': '',
            },
            saved: true
        };
        auxlib.applyArgs(this, defaultArgs, argsDict);

        this._settingsForm$ = $(this.settingsFormSelector);
        this._primaryModelType$ = $(this.primaryModelTypeDropDownSelector);
        this._reportContainer$ = $(this.reportContainerSelector);
        this._chartCreator$ = $(this.chartCreatorSelector);
        if (this.saved)
            this._savedSettings = this._settingsForm$.serialize();
        x2.X2Form.call(this, argsDict);
    }

    /**
     * Sets up report settings form submission 
     */
    ReportForm.prototype._setUpSettingsFormSubmission = function () {
        var that = this;
        // request report and display the response
        this._settingsForm$.find('[type=\"submit\"]').unbind('click._setUpSettingsFormSubmission')
                .bind('click._setUpSettingsFormSubmission', function () {

                    var throbber$ = auxlib.pageLoading();
                    $.ajax({
                        url: yii.scriptUrl + '/reports/' + that.type + 'Report?generate=1',
                        type: 'GET',
                        data: that._settingsForm$.serialize(),
                        dataType: 'json',
                        success: function (data) {
                            throbber$.remove();
                            if (data.status === 'success') {
                                that._reportContainer$.show();
                                that._reportContainer$.html(data.report);
                                x2.forms.clearErrorMessages(that._settingsForm$);
                            } else { // if (data.status === 'failure')
                                var form$ = data.form;
                                $('#content-container-inner').replaceWith(form$);
                                that._init();
                                x2.forms.setUpCollapsibles();
                            }
                        }
                    });
                    return false;
                });
    };

    ReportForm.prototype._applyPrintViewInlineStyles = function (data) {
        var table$ = $(data).find('table');
        table$.find('a').each(function () {
            $(this).remove();
        });

        table$.css('border-collapse', 'collapse');
        table$.css('width', '100%');

        table$.find('th').each(function () {
            $(this).css('border', '1px solid black');
            $(this).css('background-color', '#eee');
        });

        table$.find('td').each(function () {
            $(this).css('border', '1px solid black');
        });
    };

    /**
     * Sets up behavior of primary model type dropdown 
     */
    ReportForm.prototype._setUpPrimaryModelChangeBehavior = function () {
        var that = this;
        var select$ = $(this.primaryModelTypeDropDownSelector);

        // refresh report settings form when new primary model type is selected
        select$.unbind('change._setUpPrimaryModelChangeBehavior').
                bind('change._setUpPrimaryModelChangeBehavior', function () {

                    var data = {};
                    data[select$.attr('name')] = select$.val();
                    data[that.formModelName + "[refreshForm]"] = 1;
                    var throbber$ = auxlib.pageLoading();
                    $.ajax({
                        url: yii.scriptUrl + '/reports/reports/' + that.type + 'Report',
                        type: 'GET',
                        data: data,
                        success: function (data) {
                            var content$ = that._settingsForm$.closest('#content');
                            content$.children('#content-container-inner').remove();
                            content$.children('#report-container').html('');
                            content$.find('.page-title').first().after(data);
                            x2.forms.setUpCollapsibles();
                            throbber$.remove();
                        }
                    });
                });
    };

    /**
     * Opens save settings dialog 
     */
    ReportForm.prototype._openSaveSettingsDialog = function (copy) {
        copy = typeof copy === 'undefined' ? false : copy;
        var that = this;
        this._saveSettingsDialog$ = $('#report-settings-dialog');
        if (copy) {
            that._saveSettingsDialog$.find('input').val(
                    $.trim($('.reports-page-title h2').text()) + ' (' + that.translations.copy + ')');
        }
        if (this._saveSettingsDialog$.closest('.ui-dialog').length) {
            x2.forms.clearForm(this._saveSettingsDialog$);
            this._saveSettingsDialog$.dialog('open');
            return;
        }
        this._saveSettingsDialog$.dialog({
            title: this.translations[copy ? 'copyReportDialogTitle' : 'savedSettingsDialogTitle'],
            width: 500,
            autoOpen: true,
            buttons: [
                {
                    text: this.translations[copy ? 'copy' : 'saveButton'],
                    click: function () {
                        var name$ = that._saveSettingsDialog$.find('input');
                        var reportName = name$.val();
                        if (reportName === '') {
                            name$.addClass('error');
                        } else {
                            // that.saved refers to if the report is a saved report at all.
                            // that.isSaved() refers to whether there are unsaved changes
                            if (copy && that.saved && !that.isSaved()) {

                                $(this).dialog('close');
                                auxlib.confirm(function () {
                                    that._copyReport(reportName);
                                }, {
                                    message: that.translations.unsavedSettingsWarning,
                                    confirm: that.translations.proceedAnyway,
                                    cancel: that.translations.cancel,
                                });
                            } else if (copy) {
                                that._copyReport(reportName);
                            } else {
                                that._saveReportSettings({'Reports[name]': reportName});
                            }
                        }
                    }
                },
                {
                    text: this.translations['cancel'],
                    click: function () {
                        $(this).dialog('close');
                    }
                }
            ],
            close: function () {
                x2.forms.clearForm(this._saveSettingsDialog$);
            }
        });
    };

    ReportForm.prototype._copyReport = function (name) {
        window.location = yii.scriptUrl + '/reports/copy?id=' + this._getReportId() +
                '&name=' + window.encodeURIComponent(name);
    };

    /**
     * Converts report generation form into report settings form and submits it
     * @param string reportName
     */
    ReportForm.prototype._saveReportSettings = function (params) {
        var that = this;

        this._settingsForm$.find(':input').each(function (i, elem) {
            $(elem).attr('value', $(elem).val());
        });

        this._settingsForm$.attr('action', yii.scriptUrl + '/reports/reports/saveReport');

        var prefix = 'Reports[settings]';
        this._prefixSettingsFormNames(prefix);

        // add params
        for (var name in params) {
            this._settingsForm$.append($('<input>', {
                name: name,
                value: params[name],
                type: 'hidden'
            }));
        }

        this._settingsForm$.submit();

        this.saved = true;
    };

    /**
     * prefix each name in the form. 
     * <nameA> becomes <prefix>[<name>]
     */
    ReportForm.prototype._prefixSettingsFormNames = function (prefix) {
        this._settingsForm$.find(':input').each(function (i, elem) {
            var name = $(elem).attr('name');
            if (typeof name === 'undefined' || name === 'YII_CSRF_TOKEN' ||
                    name === x2.Widget.NAMESPACE_KEY)
                return;
            $(elem).attr('name', prefix + name.replace(/^([^\[]+)/, '[$1]'));
        });
    };

    /**
     * Undoes changes made by _prefixSettingsFormNames 
     */
    ReportForm.prototype._removeSettingsFormNamePrefixes = function (prefix) {
        var regex = new RegExp('^' + prefix.replace(/(\[|\])/g, '\\$1'));
        this._settingsForm$.find(':input').each(function (i, elem) {
            var name = $(elem).attr('name');
            if (typeof name === 'undefined')
                return;
            name = name.replace(regex, '');
            $(elem).attr('name', name.replace(/^\[([^\]]+)\]/, '$1'));
        });
    };

    ReportForm.prototype._getReportId = function () {
        return window.location.href.replace(/.*[^0-9]+([0-9]+)$/, '$1');
    };

    /**
     * Update report settings via ajax
     */
    ReportForm.prototype._updateReportSettings = function () {
        var that = this;

        this._prefixSettingsFormNames('Reports[settings]');
        var data = this._settingsForm$.serialize();
        this.serializedForm = data;
        this._removeSettingsFormNamePrefixes('Reports[settings]');

        $.ajax({
            url: yii.scriptUrl + '/reports/update/' + this._getReportId(),
            data: data,
            dataType: 'JSON',
            success: function (data) {
                x2.flashes.displayFlashes(data['flashes']);

                if (that.type === 'external')
                    window.location = yii.scriptUrl + '/reports/' + that._getReportId();
            }
        });
    };

    /**
     * Set up behavior of save settings button 
     */
    ReportForm.prototype._setUpSettingsSaveButtonBehavior = function () {
        var that = this;
        var saveSettingsButton$ = $('#report-settings-save-button');
        if (!saveSettingsButton$.length)
            return;

        saveSettingsButton$.unbind('click._setUpSettingsSaveButtonBehavior').
                bind('click._setUpSettingsSaveButtonBehavior', function () {

                    that._openSaveSettingsDialog();
                    return false;
                });
    };

    /**
     * Sets up the behavior of the quick create contact list button.
     * The form is loaded lazily. 
     */
    ReportForm.prototype.setUpQuickCreate = function () {
        $('#quick-create-list').click(function () {

            // quick create form for contact lists
            quickCreateForm = $('#quick-create-list-form');

            // Only append form if one isn't there already.
            if (quickCreateForm.find('.form').length > 0) {
                quickCreateForm.slideToggle();
                return;
            }

            // This action was modified to allow for ajax
            $.ajax({
                url: yii.scriptUrl + '/contacts/createList',
                data: {
                    ajax: 1
                },
                success: function (data) {
                    if (quickCreateForm.find('.form').length == 0) {
                        ReportForm.prototype.appendQuickCreate(data);
                    }
                }
            });
        });
    };

    /**
     * Appends the quick create form to the DOM and sets up
     * handlers on its submit function
     * @param data Data from the AJAX Request
     */
    ReportForm.prototype.appendQuickCreate = function (data) {

        // quick create form for contact lists
        quickCreateForm = $('#quick-create-list-form');
        $(data).appendTo(quickCreateForm);

        // Open the form 
        quickCreateForm.slideToggle();

        var form = quickCreateForm.find('form');

        // rename submit button to avoid collision with submit button on page. 
        var submit = form.find('#save-button').attr('id', 'contact-list-save-button');

        // Set up handler
        submit.click(function (e) {
            e.preventDefault();
            $.ajax({
                method: 'post',
                url: form.attr('action'),
                data: form.serialize(),
                dataType: 'json',
                success: function (data) {

                    // Create a new option for the newly created list
                    var option = $('<option></option>')
                            .attr('value', data.id)
                            .html(data.name);

                    // Append the new option to the select dropdown
                    $('#Campaign_listId').append(option).val(data.id);

                    // Close the form
                    quickCreateForm.slideToggle();

                    quickCreateForm.find('.form').remove();
                }
            });
        });
    };

    ReportForm.prototype._setUpCopyButtonBehavior = function () {
        var that = this;
        var reportCopyButton$ = $('#report-copy-button');
        if (!reportCopyButton$.length)
            return;
        reportCopyButton$.unbind('click').bind('click', function () {
            that._openSaveSettingsDialog(true);
        });
    };

    /**
     * Set up behavior of report update button 
     */
    ReportForm.prototype._setUpUpdateButtonBehavior = function () {
        var that = this;
        var updateButton$ = $('#report-update-button');
        if (!updateButton$.length)
            return;

        updateButton$.unbind('click._setUpUpdateButtonBehavior')
                .bind('click._setUpUpdateButtonBehavior', function () {

                    that._updateReportSettings();
                });
    };

    ReportForm.prototype._export = function () {
        var url = '/reports/reports/' + this.type + 'Report?generate=1&' +
                this.formModelName + '[export]=1' + '&' + this._settingsForm$.serialize();
        window.location.href = yii.scriptUrl + url;
    };

// If there are widgets present, we use the data widget manager
// to print all the charts with the report (Uses chart ids to fetch the report)
// Otherwise we use a different action to print the report without needing to be saved
    ReportForm.prototype._print = function () {
        if (typeof x2.dataWidgetManager === 'undefined' ||
                x2.dataWidgetManager.widgetList.length == 0) {
            var url = '/reports/printReport?' + 'type=' + this.type + '&' +
                    this._settingsForm$.serialize();
            window.open(yii.scriptUrl + url);
        } else {
            x2.dataWidgetManager.printCharts();
        }

    };

    ReportForm.prototype._chart = function () {
        x2.chartCreator.open();
        // this._chartCreator$.dialog('open');
    };

    ReportForm.prototype._email = (function () {
        var hasPrintView = false;
        return function () {
            var that = this;
            if (!hasPrintView) {
                var url = '/reports/reports/' + this.type + 'Report?generate=1&' +
                        this.formModelName + '[email]=1' + '&' + this._settingsForm$.serialize();
                $.ajax({
                    url: yii.scriptUrl + url,
                    type: 'GET',
                    success: function (data) {
                        var printView = $(data);
                        that._applyPrintViewInlineStyles(printView);
                        x2.inlineEmailOriginalBody = $(printView).html() + x2.inlineEmailOriginalBody;
                        hasPrintView = true;
                        toggleEmailForm();
                    }
                });
            } else {
                toggleEmailForm();
            }
        };
    })();

    ReportForm.prototype._setUpReportExportButtonBehavior = function () {
        var that = this;
        $('.page-title').off('click._setUpReportExportButtonBehavior', '.report-export-button')
                .on('click._setUpReportExportButtonBehavior', '.report-export-button', function () {

                    that._export();
                    return false;
                });
    };

    ReportForm.prototype._setUpReportPrintButtonBehavior = function () {
        var that = this;
        $('.page-title').off('click._setUpReportExportButtonBehavior', '.report-print-button')
                .on('click._setUpReportExportButtonBehavior', '.report-print-button', function () {

                    that._print();
                    return false;
                });
    };

    ReportForm.prototype._setUpReportChartButtonBehavior = function () {
        var that = this;
        $('.page-title').off('click._setUpReportExportButtonBehavior', '.report-email-button')
                .on('click._setUpReportExportButtonBehavior', '.report-chart-button', function () {

                    that._chart();
                    return false;
                });
    };


    ReportForm.prototype._setUpReportEmailButtonBehavior = function () {
        var that = this;
        $('.page-title').off('click._setUpReportExportButtonBehavior', '.report-email-button')
                .on('click._setUpReportExportButtonBehavior', '.report-email-button', function () {

                    that._email();
                    return false;
                });
    };

    ReportForm.prototype._setUpMinimizeButtonBehavior = function () {
        var that = this;
        $('#content .reports-page-title').first().find('#minimize-button').
                unbind('click._setUpMinimizeButtonBehavior').
                bind('click._setUpMinimizeButtonBehavior', function () {
                    $('#content-container-inner').slideToggle();
                    $(this).find('.fa').toggleClass('fa-caret-down');
                    $(this).find('.fa').toggleClass('fa-caret-left');
                    $('#content .reports-page-title').toggleClass('minimized');
                });

    }

    ReportForm.prototype._setUpInitialSerialization = function () {
        var that = this;
        setTimeout(function () {
            that._prefixSettingsFormNames('Reports[settings]');
            that.serializedForm = that._settingsForm$.serialize();
            that._removeSettingsFormNamePrefixes('Reports[settings]');
        });
    }

    ReportForm.prototype.isSaved = function () {
        var that = this;

        that._prefixSettingsFormNames('Reports[settings]');
        var data = that._settingsForm$.serialize();
        that._removeSettingsFormNamePrefixes('Reports[settings]');

        return (data == this.serializedForm)
    }

    ReportForm.prototype._init = function () {
        this._setUpSettingsFormSubmission();
        this._setUpPrimaryModelChangeBehavior();
        this._setUpSettingsSaveButtonBehavior();
        this._setUpCopyButtonBehavior();
        this._setUpUpdateButtonBehavior();
        this._setUpReportExportButtonBehavior();
        this._setUpReportChartButtonBehavior();
        this._setUpReportPrintButtonBehavior();
        this._setUpReportEmailButtonBehavior();
        this._setUpMinimizeButtonBehavior();
        this._setUpInitialSerialization();
        this.setUpQuickCreate();
    };

    return ReportForm;

})();
