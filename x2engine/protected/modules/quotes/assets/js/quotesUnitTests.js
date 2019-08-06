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




(function () {

module("editable");

var extractCurrency = x2.LineItems.prototype.extractCurrency;

test ("line items test", function () {
    /*
    Click new line item button, assert new line item is created.
    */
    var lineItemCount = $('.quote-table').find ('tr.line-item').length;
    $('.add-line-item-button').trigger ('click');
    var newLineItemCount = $('.quote-table').find ('tr.line-item').length;
    deepEqual (lineItemCount + 1, newLineItemCount, 
        "lineItemCount not incremented after add line item button pressed.");

    /*
    Click new global adjustment button, assert new global adjustment is created and
    that subtotal and totals display properly.
    */
    var adjustmentCount = $('.quote-table').find ('tr.adjustment').length;
    var oldSubtotal = $('.quote-table').find ('.subtotal').val ();
    var oldTotal = $('.quote-table').find ('.total').val ();
    var isFirstAdjustment = $('.quote-table').find (".subtotal-row").is (":hidden");


    $('.add-adjustment-button').trigger ('click');
    var newAdjustmentCount = $('.quote-table').find ('tr.adjustment').length;
    var newSubtotal = $('.quote-table').find ('.subtotal').val ();
    var newTotal = $('.quote-table').find ('.total').val ();
    deepEqual (adjustmentCount + 1, newAdjustmentCount, 
        "adjustmentCount not incremented after add adjusmtent button pressed.");

    if (isFirstAdjustment) {
        deepEqual (newSubtotal, newTotal, 
            "new subtotal not equal to new total.");
        ok (!$('.quote-table').find (".subtotal-row").is (":hidden"), 
            "subtotal is not shown after global adjustment added.");
    } else {
        deepEqual (oldSubtotal, newSubtotal, 
            "new subtotal not equal to old subtotal.");
    }
    deepEqual (oldTotal, newTotal, 
        "adjustmentCount not incremented after add adjusmtent button pressed.");

    /*
    Change price of first line item, check new line total and subtotal against predicted 
    values.
    */
    var $firstLineItem = $('.quote-table').find ('tr.line-item').first ();

    var oldTotal = 
        extractCurrency ($('.quote-table').find ('.total'));
    var oldSubtotal = 
        extractCurrency ($('.quote-table').find ('.subtotal'));
    var newPrice = 5;

    var oldLineTotal = 
        extractCurrency ($firstLineItem.find ('input.line-item-total'));
    var quantity = $firstLineItem.find ('input.quantity').val ();
    var adjustment = $firstLineItem.find ('input.adjustment');
    var adjustmentType = $firstLineItem.find ('input.adjustment-type').val ();

    $firstLineItem.find ('input.price').val (newPrice * 100);
    $firstLineItem.find ('input.price').trigger ('change');

    var predictedLineTotal;
    if (adjustmentType === 'linear') {
        var adjustment = extractCurrency (adjustment);
        predictedLineTotal = newPrice * quantity + adjustment;
    } else {
        adjustment = adjustment.val ().replace (/&#37;/, '');
        predictedLineTotal = newPrice * quantity + newPrice * quantity * adjustment;
    }

    var predictedSubtotal = (oldSubtotal - oldLineTotal) + predictedLineTotal;

    var newLineTotal = extractCurrency ($firstLineItem.find ('input.line-item-total'));
    var newSubtotal = extractCurrency ($('.quote-table').find ('.subtotal'));

    deepEqual (newLineTotal, predictedLineTotal, 
        "predicted line total and new line total are not equal.");
    deepEqual (newSubtotal.toFixed (2), predictedSubtotal.toFixed (2), 
        "predicted subtotal and new subtotal are not equal.");
});

module("readonly");


}) ();
