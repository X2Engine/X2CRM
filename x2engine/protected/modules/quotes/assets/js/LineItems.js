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
 * Manages quotes line items table 
 */

if (typeof x2 === 'undefined') x2 = {};

x2.LineItems = (function () {

function LineItems (argsDict) {
    argsDict = typeof argsDict === 'undefined' ? {} : argsDict;
    var defaultArgs = {
        DEBUG: false && x2.DEBUG,
        currency: null,
        /**
         * @param bool Set to true for read-only version of the line items table
         */
        readOnly: null, 
        /**
         * @param string url of delete icon
         */
        deleteImageSource: null,
        /**
         * @param string url of sort icon
         */
        arrowBothImageSource: null,
        /**
         * @param string url of icon used for combo box 
         */
        arrowDownImageSource: null,
        /**
         * @param {array of strings} translations for column headers
         */
        titleTranslations: null,
        productNames: null,
        productPrices: null,
        productDescriptions: null,
        /**
         * @param string (optional) Used to determine view-dependent behavior of quotes table
         */
        view: 'default',
        productLines: null,
        adjustmentLines: null,
        /**
         * @param string used to prefix unique identifiers (e.g. html element ids)
         */
        namespacePrefix: null,
        /**
         * @param string url to request product options for combo box 
         */
        getItemsUrl: null,
        modelName: null
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);
    var that = this;

    this.PERCENT_KEY_CODE = 53;

    this._containerElemSelector = '#' + this.namespacePrefix + '-line-items-table';
    this._lineItemsSelector = this._containerElemSelector + ' .line-items';
    this._adjustmentsSelector = this._containerElemSelector + ' .adjustments';
    this._productMenuSelector = this._containerElemSelector + ' .product-menu';
    this._subtotalRowSelector = this._containerElemSelector + ' .subtotal-row';
    this._subtotalSelector = '#' + this.namespacePrefix + '-subtotal';
    this._totalSelector = '#' + this.namespacePrefix + '-total';

    this._lineCounter = 0; // used to differentiate name values of input fields
    this._clickedLineItem = null;


    $(function () {
        // used to instantiate moneyMask fields
        that._currencyConfig = $.extend ({}, x2.currencyInfo, {
            affixStay: true,
            allowNegative: true
        });
    });

    this._init ();
};

/*
Public static methods
*/

/*
Private static methods
*/

/*
Public instance methods
*/

LineItems.prototype.resolveName = function (name) {
    if (!this.modelName) return name;
    else return this.modelName + name.replace (/^([^[]+)/, '[$1]');
};

/*
Parameters:
    total - a Number
    subtotal - a Number which is used to calculate totalPercent adjustments
    rowElement - a row from the adjustments table in the DOM
Returns:
    a Number containing the adjusted total
*/
LineItems.prototype.applyAdjustmentToTotal = function (total, subtotal, rowElement) {
    var that = this;
    var adjustmentType =
        $(rowElement).find (".adjustment-type").val ();
    var adjustment = that.getAdjustment (rowElement, adjustmentType)

    if (adjustmentType === 'totalLinear') {
        total += adjustment;
    } else if (adjustmentType === 'totalPercent') {
        total += subtotal * (adjustment / 100.0);
    }
    return total;
};

LineItems.prototype.removePercentSign = function (adjustment) {
    var that = this;
    return adjustment.replace (/&#37;/, '');
};


/*
extract the price from the row element and convert the currency to a Number
*/
LineItems.prototype.getPrice = function (rowElement) {
    var that = this;
    return $(rowElement).find ('.price').maskMoney ('unmasked')[0];
    /*var price = $(rowElement).find (".price").toNumber(
        {region: that.currency});
    if (price.val () === '') price.val (0); // convert invalid input to 0
    price = parseFloat (price.val ());
    $(rowElement).find (".price").formatCurrency(
        {region: that.currency});
    return price;*/
};

/*
extract the adjustment from the row element and either convert the currency to
a Number or remove the percent sign
*/
LineItems.prototype.getAdjustment = function (rowElement, adjustmentType) {
    var that = this;
    var adjustment;
    if (adjustmentType === 'percent' || adjustmentType === 'totalPercent') {
        adjustment = parseFloat (that.removePercentSign (
            $(rowElement).find (".adjustment").val ()), 10);
    } else { // adjustmentType === 'linear' || adjustmentType === 'totalLinear'
        adjustment = $(rowElement).find (".adjustment").maskMoney ('unmasked')[0];
        /*adjustment = $(rowElement).find (".adjustment").toNumber(
            {region: that.currency});
        if (adjustment.val () === '') adjustment.val (0); // convert invalid input to 0
        adjustment = parseFloat (adjustment.val ());
        $(rowElement).find (".adjustment").formatCurrency(
            {region: that.currency});*/
    }
    return adjustment;
};

/*
Calculate the line total using the line item's quantity, unit price, and
adjustments
Parameter:
rowElement - the row containing the line item input fields
Returns:
    a Number containing the line total
*/
LineItems.prototype.calculateLineTotal = function (rowElement) {
    var that = this;
    var price = that.getPrice (rowElement);
    var quantity =
        parseFloat ($(rowElement).find (".quantity").val ());
    var adjustmentType =
        $(rowElement).find (".adjustment-type").val ();
    var adjustment = that.getAdjustment (rowElement, adjustmentType);
    var name =
        $(rowElement).find ("[name*='" + this.resolveName ('name') + "']").val ();

    var lineTotal = price * quantity;

    if (adjustmentType === 'linear') {
        lineTotal += adjustment;
    } else if (adjustmentType === 'percent') {
        lineTotal += lineTotal * (adjustment / 100.0);
    }

    that.DEBUG && console.log ('calculateLineTotal: ');

    that.DEBUG && console.log ('lineTotal = ');
    that.DEBUG && console.log (lineTotal);

    return lineTotal;
};

/*
Returns:
    an array containing each of the line item totals
*/
LineItems.prototype.calculateLineTotals = function () {
    var that = this;
    var lineTotals = [];
    $(that._containerElemSelector + ' .line-item').each (function (index, element) {
        lineTotals.push (that.calculateLineTotal (element));
    });
    return lineTotals;
};

/*
For each line item row in the lineItems tbody element, fills in an
entry for the line total.
Parameter:
    lineTotals - an array of Numbers, one for each line item in the line item
        table
*/
LineItems.prototype.setLineTotals = function (lineTotals) {
    var that = this;
    $(that._containerElemSelector + ' .line-item').each (function (index, element) {
        total = lineTotals.shift ();
        that.DEBUG && console.log ('setLineTotals: setting total to ' + total);
        that.DEBUG && console.log (typeof total);
        $(element).find ('.line-item-total').val (that.fmtAsDecimal (total)).maskMoney ('mask');/*.formatCurrency (
            {'region': that.currency});*/
    });
};

/*
Helper function to convert the value of an input field containing a currency to
a float, save the float, and convert the value of the input field back to a currency.
The converted currency is returned.
*/
LineItems.prototype.extractCurrency = function (element) {
    return $(element).maskMoney ('unmasked')[0];
    /*$(element).toNumber(
        {'region': that.currency});
    var currency = parseFloat ($(element).val ());
    $(element).formatCurrency(
        {region: that.currency});
    return currency;*/
};

/*
extract the line item total from the row element and convert the currency to a
Number
*/
LineItems.prototype.getLineItemTotal = function (rowElement) {
    var that = this;
    return that.extractCurrency ($(rowElement).find ('.line-item-total'));
};

/*
Calculates the subtotal by summing the line item totals from the DOM
Precondition: that.setLineTotals () has been called
Returns:
    subtotal - a Number containing the calculated subtotal
*/
LineItems.prototype.calculateSubtotal = function () {
    var that = this;
    var subtotal = 0;
    $(that._containerElemSelector + ' .line-item').each (function (index, element) {
        subtotal += that.getLineItemTotal (element);
    });
    return subtotal;
};;


/*
Displays the subtotal in the line items table in the DOM
*/
LineItems.prototype.setSubtotal = function (subtotal) {
    var that = this;
    //$(that._subtotalSelector).val(subtotal.toString ()).maskMoney ('mask');
    $(that._subtotalSelector).val(this.fmtAsDecimal (subtotal)).maskMoney ('mask');
    /*$(that._subtotalSelector).formatCurrency(
        {region: that.currency});*/
};

/**
 * Prepares input value for maskMoney.  
 * @param number number
 */
LineItems.prototype.fmtAsDecimal = function (number) {
    var that = this;
    that.DEBUG && console.log ('number = ');
    that.DEBUG && console.log (number);
    that.DEBUG && console.log (typeof number);

    if (number === Math.floor (number)) {
        number = number.toString () + '.00';
    } else if (typeof number === 'number') {
        number = number.toFixed (2).toString ();
    }
    return number;
}

/*
Displays the total in the line items table in the DOM
*/
LineItems.prototype.setTotal = function (total) {
    var that = this;
    that.DEBUG && console.log ('setTotal');
    that.DEBUG && console.log ('total = ');
    that.DEBUG && console.log (total);

    //$(that._totalSelector).val(total.toString ()).maskMoney ('mask');;
    $(that._totalSelector).val(this.fmtAsDecimal (total)).maskMoney ('mask');
    /*$(that._totalSelector).formatCurrency(
        {region: that.currency});*/
};

/*
Calculates the total by applying each of the adjustments from the DOM to the
subtotal
Parameter:
    subtotal - a Number
Returns:
    a Number containing the adjusted subtotal
*/
LineItems.prototype.calculateTotal = function (subtotal) {
    var that = this;
    var total = subtotal;
    $(that._containerElemSelector + ' .adjustment').each (function (index, element) {
        total = that.applyAdjustmentToTotal (total, subtotal, element);
    });
    return total;
};

/*
Insert a new line item row into the quotes line item table
*/
LineItems.prototype.addLineItem = function (
    fillLineItem, values /* set if fillLineItem is true */) {

    var that = this;
    if (!fillLineItem) {
        values = { // default values,
            "product-name": ['' /* default input value */, false /* validation error */],
            "price": ['0', false],
            "quantity": ['1', false],
            "adjustment": ['0', false],
            "description": ['', false],
            "adjustment-type": ['linear', false]
        }
    }

    var lineItemRow = $("<tr>", {'class': 'line-item'});

    $firstCell = lineItemRow.append ($("<td>", {'class': 'first-cell'}));
    if (!that.readOnly) {
        $firstCell.find ('td').append (
            $("<span>", {'class': 'fa fa-times x2-delete-icon item-delete-button'}),
            $("<span>", {'class': 'fa fa-sort handle arrow-both-handle'})
        );
    }
    $inputCell = lineItemRow.append ($("<td>", {'class': 'x2-2nd-child input-cell'}).append (
        $("<input>", {
            type: 'text',
            'class': 'line-item-field product-name',
            maxlength: '100',
            value: values['product-name'][0],
            name: this.resolveName ('lineitem[' + ++that._lineCounter + '][name]') 
        })
    ));
    if (!that.readOnly) {
        /*$inputCell.find ('input').after (
            $("<button>", {'class': 'x2-button product-select-button', 'type': 'button'}).
                append ($("<img>", { src: that.arrowDownImageSource })));*/
    }
    lineItemRow.append ($("<td>", {'class': 'x2-3rd-child input-cell'}).append (
        $("<input>", {
            type: 'text',
            'class': 'line-item-field price',
            value: values['price'][0],
            name: this.resolveName ('lineitem[' + that._lineCounter + '][price]')
        }))
    );
    lineItemRow.append ($("<td>", {'class': 'x2-4th-child input-cell'}).append (
        $("<input>", {
            type: 'text',
            'class': 'line-item-field quantity',
            value: values['quantity'][0],
            name: this.resolveName ('lineitem[' + that._lineCounter + '][quantity]') 
        }))
    );
    lineItemRow.append ($("<td>", {'class': 'x2-5th-child input-cell'}).append (
        $("<input>", {
            type: 'text',
            'class': 'line-item-field adjustment',
            value: values['adjustment'][0],
            name: this.resolveName ('lineitem[' + that._lineCounter + '][adjustment]') 
        }))
    );
    lineItemRow.append ($("<td>", {'class': 'x2-6th-child input-cell'}).append (
        $("<input>", {
            type: 'text',
            'class': 'line-item-field description',
            'value': values['description'][0],
            name: this.resolveName ('lineitem[' + that._lineCounter + '][description]') 
        }))
    );
    lineItemRow.append ($("<td>", {
        'class': 'input-cell line-item-field x2-7th-child'}).append (
            $("<input>", {
                type: 'text',
                'class': 'line-item-total',
                readonly: 'readonly',
                onfocus: 'this.blur();',
                name: this.resolveName ('lineitem[' + that._lineCounter + '][total]') 
            }),
            $("<input>", {
                type: 'hidden',
                'class': 'adjustment-type',
                value: values['adjustment-type'][0],
                name: this.resolveName ('lineitem[' + that._lineCounter + '][adjustmentType]') 
            }),
            $("<input>", {
                type: 'hidden',
                'class': 'line-number',
                name: this.resolveName ('lineitem[' + that._lineCounter + '][lineNumber]') 
            }))
    );

    if (fillLineItem) { // add error class if server side validation failed
        for (var inputType in values) {
            if (values[inputType][1] === true) {
                $(lineItemRow).find ("." + inputType).addClass ('error');
            }
        }
    }
    if (that.readOnly) { // make uneditable
        $(lineItemRow).find ("input").attr ({
            "readonly": "readonly",
            "onfocus": "this.blur();"
        });
    } else { // add translated title attributes
        for (var inputType in that.titleTranslations) {
            var $inputField = $(lineItemRow).find ("." + inputType);
            if ($inputField.length === 1) {
                $inputField.attr ("title", that.titleTranslations[inputType]);
            }
        }
    }

    $(that._lineItemsSelector).append (lineItemRow);

    this.maskMoney ([
        lineItemRow.find ('.adjustment'),
        lineItemRow.find ('.price'),
        lineItemRow.find ('.line-item-total')
    ]);

    if (!that.readOnly) { // set up product select menu behavior
        var productNameInput = $(lineItemRow).find ('input.product-name');
        x2.combodebug = new x2.ComboBox ({
            element: productNameInput,
            getItemsUrl: this.getItemsUrl,
            optionClick: function (option$) {
                that.selectProductFromDropDown (option$.text (), option$.data ('data-val'));
            },
            onShow: function () {
                that._clickedLineItem = $(this.element);
            }
        });
        /*$(productNameInput).autocomplete ({
            source: that.productNames,
            select: function (event, ui) { that.selectProductFromAutocomplete (event, ui); },
            open: function (evt, ui) {
                if ($(that._productMenuSelector).is (":visible")) {
                    $(that._productMenuSelector).hide ();
                }
                }
            }
        });
        that.formatAutocompleteWidget (productNameInput);*/
        $('tbody.sortable').sortable ('refresh');
    }
    if (!fillLineItem) { // format default input field values
        lineItemRow.find ('.line-item-total').val (0).maskMoney ('mask');
        if ($(that._containerElemSelector + ' .quote-table').find ('tr.line-item').length === 1 &&
            $(that._containerElemSelector + ' .quote-table').find ('tr.adjustment').length > 0) {

            $(that._subtotalRowSelector).show ();
        }
    }

    that.resetLineNums ();
};

/**
 * Simple wrapper around maskMoney instantiation function. Customizes behavior of maskMoney plugin
 * and automatically instantiates with config.
 * @param object|array elem input to convert to a money mask field 
 */
LineItems.prototype.maskMoney = function (elem) {
    var that = this;

    // gets bound to the inputs change event. If the input contains a '%' symbol, treat it as
    // a percentage: remove the maskMoney behavior. Otherwise, reformat the field as a currency.
    function changeX2MaskMoney (evt) {
        that.DEBUG && console.log ('changeX2MaskMoney');
        that.DEBUG && console.log ($(this).val ())
        if ($(this).val ().match (/%/)) {
            that.DEBUG && console.log ('destroying');
            $(this).data ('destroyed', true); // save the state so that we'll know to reinitialize
            $(this).maskMoney ('destroy');
        } else {
            if ($(this).data ('destroyed')) { 
                that.maskMoney ($(this));
            }
            $(this).data ('destroyed', false);
            $(this).maskMoney ('mask');
        }
    }

    // remove default behavior of maskMoney plugin. This prevents currency from being reformatted
    // as the user types, which would prevent the entry of '%' symbols.
    function unbindDefaultEvents (elem) {
        $(elem).unbind ('keypress.maskMoney');
        $(elem).unbind ('keydown.maskMoney');
        $(elem).unbind ('blur.maskMoney');
        $(elem).unbind ('focus.maskMoney');
        $(elem).unbind ('click.maskMoney');
        $(elem).unbind ('cut.maskMoney');
        $(elem).unbind ('paste.maskMoney');
    }

    that.DEBUG && console.log (elem);
    if (elem instanceof Array) {
        $(elem).each (function (i, elem) { 
            $(elem).unbind ('change.x2MaskMoney').bind ('change.x2MaskMoney', changeX2MaskMoney); 
            that.DEBUG && console.log (elem); 
            $(elem).maskMoney (that._currencyConfig); 
            unbindDefaultEvents (elem);
        });
    } else {
        $(elem).unbind ('change.x2MaskMoney').bind ('change.x2MaskMoney', changeX2MaskMoney);
        var elem = $(elem).maskMoney (this._currencyConfig);
        unbindDefaultEvents (elem);
        return elem;
    }
};

/*
Insert a new adjustment row into the quotes line item table
*/
LineItems.prototype.addAdjustment = function (
    fillAdjustment, values /* set if fillAdjustment is true */) {

    var that = this;
    if (!fillAdjustment) {
            values = { // default values
                "adjustment-name": ['' /* default input value */, false /* validation error */],
                "adjustment": ['0', false],
                "description": ['', false],
                "adjustment-type": ['totalLinear', false]
            }
    }

    var lineItemRow = $("<tr>", {'class': 'adjustment'});

    $firstCell = lineItemRow.append ($("<td>", {'class': 'first-cell'}));
    if (!that.readOnly) {
        $firstCell.find ('td').append (
            $("<span>", {'class': 'fa fa-times x2-delete-icon item-delete-button'}),
            $("<span>", {'class': 'fa fa-sort handle arrow-both-handle'})
        );
    }
    lineItemRow.append ($("<td>"));
    lineItemRow.append ($("<td>"));
    lineItemRow.append ($("<td>", {'class': 'input-cell x2-4th-child'}).append (
        $("<input>", {
            type: 'text',
            'class': 'line-item-field adjustment-name',
            maxlength: '100',
            value: values['adjustment-name'][0],
            name: this.resolveName ('lineitem[' + ++that._lineCounter + '][name]') }))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell x2-5th-child'}).append (
        $("<input>", {
            type: 'text',
            'class': 'line-item-field adjustment',
            value: values['adjustment'][0],
            name: this.resolveName ('lineitem[' + that._lineCounter + '][adjustment]') }))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell x2-6th-child'}).append (
        $("<input>", {
            type: 'text',
            'class': 'line-item-field description',
            value: values['description'][0],
            name: this.resolveName ('lineitem[' + that._lineCounter + '][description]') }))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell x2-7th-child'}).append (
        //$("<span></span>", {'class': 'line-item-total'}),
        $("<input>", {
            type: 'hidden',
            'class': 'adjustment-type',
            value: values['adjustment-type'][0],
            name: this.resolveName ('lineitem[' + that._lineCounter + '][adjustmentType]') }),
        $("<input>", {
            type: 'hidden',
            'class': 'line-number',
            name: this.resolveName ('lineitem[' + that._lineCounter + '][lineNumber]') }))
    );

    if (fillAdjustment) { // add error class if server side validation failed
        for (var inputType in values) {
            if (values[inputType][1] === true) {
                $(lineItemRow).find ("." + inputType).addClass ('error');
            }
        }
    }
    if (that.readOnly) { // make uneditable
        $(lineItemRow).find ("input").attr ({
            "readonly": "readonly",
            "onfocus": "this.blur();"
        });
    } else { // add translated title attributes
        for (var inputType in that.titleTranslations) {
            var $inputField = $(lineItemRow).find ("." + inputType);
            if ($inputField.length === 1) {
                $inputField.attr ("title", that.titleTranslations[inputType]);
            }
        }

    }

    $(that._adjustmentsSelector).append (lineItemRow);
    this.maskMoney (lineItemRow.find ('.adjustment'));

    if (!that.readOnly) {
        $('tbody.sortable').sortable ('refresh');
    }
    if (!fillAdjustment) { // format default input field values
        if ($(that._containerElemSelector + ' .quote-table').find ('tr.adjustment').length === 1) {
            $(that._subtotalRowSelector).show ();
        }
    }

    that.resetLineNums ();
    that.updateTotals ();
};

//LineItems.prototype.selectProductFromAutocomplete = function (event, ui) {
//    var that = this;
//    that.DEBUG && console.log ('selectProductFromAutocomplete');
//    event.preventDefault ();
//    var lineItemName = ui.item.label;
//    $(event.target).val (lineItemName);
//    var lineItemPrice = $(event.target).attr ('name').replace (/name/, 'price');
//    var lineItemDescription = $(event.target).attr ('name').replace (/name/, 'description');
//    this.element$.find ('[name="' + lineItemPrice + '"]').
//        val (that.productPrices[lineItemName]).maskMoney ('mask');
//    this.element$.find ('[name="' + lineItemDescription + '"]').
//        val (that.productDescriptions[lineItemName]);
//    that.validateName (event.target);
//    that.updateTotals ();
//    return false;
//};

//LineItems.prototype.selectProductFromDropDown = function (item) {
//    var that = this;
//    that.DEBUG && console.log ('selectProductFromDropDown');
//    var lineItemName = item.text ();
//    $(that._clickedLineItem).val (lineItemName);
//    var lineItemPrice = $(that._clickedLineItem).attr ('name').replace (/name/, 'price');
//    var lineItemDescription = 
//        $(that._clickedLineItem).attr ('name').replace (/name/, 'description');
//
//    this.element$.find ('[name="' + lineItemPrice + '"]').
//        val (that.productPrices[lineItemName]).maskMoney ('mask');
//    this.element$.find ('[name="' + lineItemDescription + '"]').
//        val (that.productDescriptions[lineItemName]);
//    that.validateName (that._clickedLineItem);
//    that.updateTotals ();
//    return false;
//};

LineItems.prototype.selectProductFromDropDown = function (name, attrs) {
    var that = this;
    that.DEBUG && console.log ('selectProductFromDropDown');
    var lineItemName = name;
    $(that._clickedLineItem).val (lineItemName);
    var lineItemPrice = $(that._clickedLineItem).attr ('name').replace (/name/, 'price');
    var lineItemDescription = 
        $(that._clickedLineItem).attr ('name').replace (/name/, 'description');

    this.element$.find ('[name="' + lineItemPrice + '"]').
        val (attrs.price).maskMoney ('mask');
    this.element$.find ('[name="' + lineItemDescription + '"]').
        val (attrs.description);
    that.validateName (that._clickedLineItem);
    that.updateTotals ();
    return false;
};

//LineItems.prototype.formatAutocompleteWidget = function (element) {
//    var that = this;
//    var widget = $(element).autocomplete ("widget");
//    $(widget).css ({
//            "font-size": "10px",
//            "max-height": "16em",
//            "overflow-y": "scroll"
//    });
//    $(window).resize (function () {
//        $(widget).hide ();
//    });
//};

/*
Sets up a combo box that allows ad-hoc line item names and selection from a
list of existing products
*/
//LineItems.prototype.setupProductSelectMenu = function () {
//    var that = this;
//    if (that.productNames) {
//        $(this._containerElemSelector + ' input.product-name').autocomplete ({
//            source: that.productNames,
//            select: function (event, ui) { 
//                return that.selectProductFromAutocomplete (event, ui); 
//            },
//            open: function (evt, ui) {
//                if ($(that._productMenuSelector).is (":visible")) {
//                    $(that._productMenuSelector).hide ();
//                }
//            }
//        });
//        $(this._containerElemSelector + ' input.product-name').each (function () {
//            that.formatAutocompleteWidget ($(this));
//        });
//    }
//
//    $(that._lineItemsSelector).on (
//        'click', '.product-select-button', function (event) {
//
//        that._clickedLineItem = $(this).siblings ('.line-item-field');
//        $(that._productMenuSelector).show ().position ({
//            my: "left top",
//            at: "left bottom",
//            of: $(this).prev ()
//        });
//        if (that.element$.closest ('.ui-dialog').length) {
//            that.element$.closest ('.ui-dialog').one ('click', function (event) {
//                var target = event.target;
//                if ($(target).closest ('.ui-menu-item').length || 
//                    $(target).is ('.ui-menu-item')) {
//
//                    // kludge to resolve menu item select event issue
//                    that.selectProductFromDropDown (
//                        ($(target).closest ('.ui-menu-item').length && 
//                        $(target).closest ('.ui-menu-item')) || $(target));
//                } 
//                $(that._productMenuSelector).hide ();
//            });
//        } else {
//            $(document).one ('click', function () {
//                $(that._productMenuSelector).hide ();
//            });
//        }
//        event.stopPropagation ();
//    });
//
//    $(that._productMenuSelector).hide ().menu ({select: function (event, ui) { 
//        if (!that.element$.closest ('.ui-dialog').length) {
//            that.selectProductFromDropDown (ui.item); 
//        }
//    }});
//
//    // kludge to prevent link behavior of menu items when line items are inside action frame
//    $(that._productMenuSelector).find ('a').attr ('href', 'javascript:void(0)');
//};

/*
Recalculate line item total, the subtotal, and the overall total
*/
LineItems.prototype.updateTotals = function () {
    var that = this;
    lineTotals = that.calculateLineTotals ();
    that.setLineTotals (lineTotals);
    var subtotal = that.calculateSubtotal ();
    if ($(that._containerElemSelector + ' .quote-table').find ('tr.adjustment').length !== 0 &&
            $(that._containerElemSelector + ' .quote-table').find ('tr.line-item').length !== 0) {
        that.setSubtotal (subtotal);
        var total = that.calculateTotal (subtotal);
        that.setTotal (total);
    } else {
        that.setTotal (subtotal);
    }

    if ($(that._containerElemSelector + ' .quote-table').children ().find ('.error').length 
        !== 0) {

        var calculationErrors = 0
        $(that._containerElemSelector + ' .quote-table').children ().find ('.error').each (
            function (index, element) {

            if (!($(element).hasClass ('product-name') || 
                $(element).hasClass ('adjustment-name'))) {

                $(element).parents ('.line-item').children ().find ('.line-item-total').val ("");
                calculationErrors++;
            }
        });
        if (calculationErrors > 0)
            $(that._totalSelector + ',' + that._subtotalSelector).val ("");
    }
};

/*
Determine and set the type of adjustment (totalLinear, totalPercent, linear,
percent) and validate the input.
*/
LineItems.prototype.checkAdjustment = function (element) {
    var that = this;
    var elemVal = $(element).val ();
    var typeElementName =
        $(element).attr ('name').replace (/adjustment/, 'adjustmentType');

    if (elemVal.match (/%/)) {

        if (this.element$.find ('[name="' + typeElementName + '"]').val ().match (/total/)) {
            this.element$.find ('[name="' + typeElementName + '"]').val ('totalPercent');
        } else {
            this.element$.find ('[name="' + typeElementName + '"]').val ('percent');
        }

        if (elemVal.match (/^\-?[0-9]+(\.[0-9]+)?%$/)) {
            $(element).removeClass ('error');
            return true;
        } else {
            $(element).addClass ('error');
            return false;
        }
    } else {
        $(element).removeClass ('error');

        if (this.element$.find ('[name="' + typeElementName + '"]').val ().match (/total/)) {
            this.element$.find ('[name="' + typeElementName + '"]').val ('totalLinear');
        } else {
            this.element$.find ('[name="' + typeElementName + '"]').val ('linear');
        }

        return true;
    }

};

LineItems.prototype.validateQuantity = function (element) {
    var that = this;
    if ($(element).val ().match (/^[0-9]+(\.[0-9]+)?$/)) {
        // limit precision
        $(element).val (parseFloat (parseFloat ($(element).val ()).toFixed (2)));
        $(element).removeClass ('error');
        return true;
    } else {
        $(element).addClass ('error');
        return false;
    }
};

LineItems.prototype.validateName = function (element) {
    var that = this;
    if ($(element).val () === "") {
        return false;
    } else {
        $(element).removeClass ('error');
        return true;
    }
};

/*
Helper function for validateAllInputs.
Parameter:
    element - a input field in the quotes table
Returns:
    An error message corresponding to the type of the input field of the given element.
*/
LineItems.prototype.getErrorMessage = function (element) {
    var that = this;
    var elemClass = $(element).attr ('class');
    var errorMessage = "";
    if (elemClass.match (/product-name/)) {
        errorMessage = "Line item name cannot be blank.";
    } else if (elemClass.match (/quantity/)) {
        if ($(element).val () === "") {
            errorMessage = "Quantity cannot be blank.";
        } else {
            errorMessage = "Quantity contains illegal characters.";
        }
    } else if (elemClass.match (/price/)) {
        errorMessage = "Price contains illegal characters.";
    } else if (elemClass.match (/adjustment-name/)) {
        errorMessage = "Adjustment Label cannot be blank.";
    } else if (elemClass.match (/adjustment/)) {
        var temporaryElem = $("<input>", {val: 50});
        var exampleCurrency = this.maskMoney ($(temporaryElem)).val ();
            //formatCurrency ({region: that.currency}).val ();
        errorMessage = "Adjustment must be a currency amount or a percentage (e.g. \"" +
            exampleCurrency + "\" or \"-50%\").";
    }
    return errorMessage;
};


/*
Helper for update button click event
*/
LineItems.prototype.validateAllInputs = function () {
    var that = this;
    $(that._containerElemSelector + ' .quote-table').find ('input').each (
        function (index, element) {

        if ($(element).val () === "" &&
                $(element).hasClass ('line-item-field') &&
                !$(element).hasClass ('description')) {
            $(element).addClass ("error");
        }
    });
    if ($('.quotes-error-summary').length !== 0) {
        $('.quotes-error-summary').remove ();
    }
    if ($(that._containerElemSelector + ' .quote-table').find ('input').hasClass ("error")) {
        $('#quotes-errors').after ($("<div>", {'class': "quotes-error-summary"}).append (
            $("<p> Please fix the following input errors: </p>"),
            $("<ul>")
        ));
        var usedErrorMessages = [];
        $(that._containerElemSelector + ' .quote-table').find ('.error').each (
            function (index, element) {

            var errorMessage = that.getErrorMessage (element);
            if ($.inArray (errorMessage, usedErrorMessages) === -1) {
                usedErrorMessages.push (errorMessage);
                $('.quotes-error-summary').find ('ul').append (
                    $("<li> " + errorMessage + " </li>"));
            }
        });
        return false;
    } else {
        return true;
    }

};

LineItems.prototype.setupValidationEvents = function () {
    var that = this;
    that.DEBUG && console.log ('setupValidationEvents');
    $(this._lineItemsSelector+','+this._adjustmentsSelector).on ('change', 
        '.line-item-field.adjustment', function (event) {

            that.DEBUG && console.log ('setupValidationEvents: change');
            that.checkAdjustment (event.target);
            that.updateTotals ();
    });
    $(that._lineItemsSelector).on ('change', '.line-item-field.quantity', function (event) {
        that.validateQuantity (event.target);
        that.updateTotals ();
    });
    $(that._lineItemsSelector).on ('change', '.line-item-field.price', function (event) {
            that.updateTotals ();
    });
    $(this._lineItemsSelector+','+this._adjustmentsSelector).on (
        'blur', '.line-item-field.product-name, .line-item-field.adjustment-name', 
        function (event) {

        that.validateName (event.target);
    });
};

LineItems.prototype.deleteAdjustment = function (element) {
    var that = this;
    $(element).parents ('.adjustment').remove ();
    if ($(that._containerElemSelector + ' .quote-table').find ('tr.adjustment').length === 0)
        $(that._subtotalRowSelector).hide ();
    that.updateTotals ();
    that.resetLineNums ();
};

LineItems.prototype.deleteLineItem = function (element) {
    var that = this;
    $(element).parents ('.line-item').remove ();
    if ($(that._containerElemSelector + ' .quote-table').find ('tr.line-item').length === 0)
        $(that._subtotalRowSelector).hide ();
    that.updateTotals ();
    that.resetLineNums ();
};

LineItems.prototype.resetLineNums = function () {
    var that = this;
    var lineNum = 1;
    $(that._containerElemSelector + ' tr.line-item').each (function (index, element) {
        $(element).find ('.line-number').val (lineNum++);
    });
    $(that._containerElemSelector + ' tr.adjustment').each (function (index, element) {
        $(element).find ('.line-number').val (lineNum++);
    });
};

/*
Used to prevent row width from collapsing when being sorted.
*/
LineItems.prototype.preserveRowWidth = function (event, sortedElement) {
    var that = this;
    sortedElement.children ().each (function (index, element) {
        $(element).width ($(element).width ());
    });
    return sortedElement;
};

/*
Populate quotes table with existing line items and adjustments
*/
LineItems.prototype.populateQuotesTable = function () {
    var that = this;
    for (var i in that.productLines) {
        that.addLineItem (true, that.productLines[i]);
    }
    for (var i in that.adjustmentLines) {
        that.addAdjustment (true, that.adjustmentLines[i]);
    }
};

LineItems.prototype.setupEditingBehavior = function () {
    var that = this;
    $(window).resize (function () {
        $(that._productMenuSelector).hide ();
    });

    //that.setupProductSelectMenu ();

    that.DEBUG && console.log ('setupEditingBehavior');
    $(that._containerElemSelector + ' .quote-table tbody.sortable').sortable ({
        handle: ".handle",
        start: function (event, ui) {
            $(that._productMenuSelector).hide ();
        },
        stop: function (evt, ui) { that.resetLineNums (evt, ui); },
        //helper: function (evt, sortedElem) { that.preserveRowWidth (evt, sortedElem); }
    });
    //$('tbody.sortable').disableSelection ();

    $(that._containerElemSelector + ' .add-adjustment-button').click (function () {
        that.addAdjustment (false);});
    $(that._containerElemSelector + ' .add-line-item-button').click (function (){
        that.addLineItem (false);});

    $(that._adjustmentsSelector).on ('click', '.item-delete-button', function (event) {
        that.deleteAdjustment (event.target);
    });
    $(that._lineItemsSelector).on ('click', '.item-delete-button', function (event) {
        that.deleteLineItem (event.target);
    });

    that.setupValidationEvents ();

    // add a line item if this is the create view
    if (that.productLines.length === 0 &&
            that.adjustmentLines.length === 0) {
        that.addLineItem ();
    } else {
        that.populateQuotesTable ();
    }

};



/*
Private instance methods
*/

LineItems.prototype._init = function () {
    var that = this;
    $(function () {
        that.element$ = $(that._containerElemSelector);
        that.maskMoney ([$(that._totalSelector), $(that._subtotalSelector)]);

        if (that.readOnly) {
            that.populateQuotesTable ();
        } else {
            that.setupEditingBehavior ();
        }

        // hide subtotal row if there aren't any adjustments
        if ($(that._containerElemSelector + ' .quote-table').find ('tr.adjustment').length === 0 ||
                $(that._containerElemSelector + '.quote-table').
                    find ('tr.line-item').length === 0) {

            $(that._subtotalRowSelector).hide ();
        }

        that.updateTotals ();
        if (that.readOnly) 
            x2.forms.disableEnableFormSubsection (that._containerElemSelector + ' .quote-table');
    });
};

return LineItems;

}) ();

