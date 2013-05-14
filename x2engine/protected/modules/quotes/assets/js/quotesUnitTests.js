
module("editable");
/*
Click new line item button, assert new line item is created.
*/
test ("create line item", function () {
    var lineItemCount = $('.quote-table').find ('tr.line-item').length;
    $('.add-line-item-button').trigger ('click');
    var newLineItemCount = $('.quote-table').find ('tr.line-item').length;
    deepEqual (lineItemCount + 1, newLineItemCount, 
        "lineItemCount not incremented after add line item button pressed.");
});

/*
Change price of first line item, check new line total and subtotal against predicted 
values.
*/
test ("change unit price", function () {
    var $firstLineItem = $('.quote-table').find ('tr.line-item').first ();

    var oldTotal = 
        extractCurrency ($('.quote-table').find ('#total'));
    var oldSubtotal = 
        extractCurrency ($('.quote-table').find ('#subtotal'));
    var newPrice = 5;

    var oldLineTotal = 
        extractCurrency ($firstLineItem.find ('input.line-item-total'));
    var quantity = $firstLineItem.find ('input.quantity').val ();
    var adjustment = $firstLineItem.find ('input.adjustment');
    var adjustmentType = $firstLineItem.find ('input.adjustment-type').val ();

    $firstLineItem.find ('input.price').val (newPrice);
    $firstLineItem.find ('input.price').trigger ('change');

    var predictedLineTotal;
    if (adjustmentType === 'linear') {
        var adjustment = extractCurrency (adjustment);
        predictedLineTotal = newPrice * quantity + adjustment;
    } else {
        adjustment = adjustment.val ().replace (/&#37;/, '');
        predictedLineTotal = newPrice * quantity + newPrice * quantity * adjustment;
    }
    var predictedSubtotal = (oldSubtotal - oldLineTotal) + predictedLineTotal

    var newLineTotal = extractCurrency ($firstLineItem.find ('input.line-item-total'));
    var newSubtotal = extractCurrency ($('.quote-table').find ('#subtotal'));

    deepEqual (newLineTotal, predictedLineTotal, 
        "predicted line total and new line total are not equal.");
    deepEqual (newSubtotal, predictedSubtotal, 
        "predicted subtotal and new subtotal are not equal.");
});

module("readonly");



