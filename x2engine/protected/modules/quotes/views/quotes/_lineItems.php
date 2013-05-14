<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

/*
data:
  $products - active products create in the products module. This is used to
    populate the drop down menu for new line items.
  $readOnly - a boolean which indicates whether or not the line item and 
    adjustment fields are editable. 
*/


/*
Used to insert an image element into the DOM. 
*/
function insertRemoveImage () {
  echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/css/gridview/delete.png' 
       . "' alt='[" . Yii::t('quotes', 'Delete Quote') . "]' class='item-delete-button'/>";
}

/*
Used to insert an image element into the DOM. 
*/
function insertArrowBothImage () {
  echo "<img src='" . Yii::app()->request->baseUrl.'/themes/x2engine/css/gridview/arrow_both.png' 
       . "' alt='[" . Yii::t('quotes', 'Move') . "]' class='handle arrow-both-handle'/>";
}

$currency = null;
if (isset ($model)) {
  if (!empty ($model->currency)) {
    $currency = $model->currency;
  } else {
    $currency = Yii::app()->params['currency'];
  }
}

// ***set global variables for the client JavaScript
$passVariablesToClientScript = "
  if (typeof x2.quotes === 'undefined') {
    x2.quotes = {};
    x2.quotes.view = 'default'; // used to determine view-dependent behavior of quotes table
  }
  x2.quotes.currency = '".$currency."';
  x2.quotes.readOnly = '".$readOnly."';
  x2.quotes.lineCounter = 0; // used to differentiate name values of input fields
";

/* 
Send an array containing product line information. This array is used by the client to build
the rows of the line items table.
*/
$passVariablesToClientScript .= "x2.quotes.productLines = [];";
foreach ($model->productLines as $item) {
  $passVariablesToClientScript .= "x2.quotes.productLines.push (".
    CJSON::encode (array ( // keys correspond to CSS classes of each input field
    'product-name'=>array ($item->formatAttribute('name'),$item->hasErrors('name')),
    'price'=>array ($item->formatAttribute('price'),$item->hasErrors('price')),
    'quantity'=>array ($item->formatAttribute('quantity'),$item->hasErrors('quantity')),
    'adjustment'=>array ($item->formatAttribute('adjustment'),$item->hasErrors('adjustment')),
    'description'=>array ($item->formatAttribute('description'),$item->hasErrors('description')),
    'adjustment-type'=>array ($item->formatAttribute('adjustmentType'),false))).
  ");";
}

/* 
Send an array containing adjustment line information. This array is used by the client to build
the rows of the line items table.
*/
$passVariablesToClientScript .= "x2.quotes.adjustmentLines = [];";
foreach ($model->adjustmentLines as $item) {
  $passVariablesToClientScript .= "x2.quotes.adjustmentLines.push (".
    CJSON::encode (array ( // keys correspond to CSS classes of each input field
    'adjustment-name'=>array ($item->formatAttribute('name'),$item->hasErrors('name')),
    'adjustment'=>array ($item->formatAttribute('adjustment'),$item->hasErrors('adjustment')),
    'description'=>array ($item->formatAttribute('description'),$item->hasErrors('description')),
    'adjustment-type'=>array ($item->formatAttribute('adjustmentType'),false))).
  ");";
}


/*
Send a dictionary containing translations for the types of each input field. 
Used for html title attributes.
*/
$titleTranslations = array( // keys correspond to CSS classes of each input field
  'product-name'=>Yii::t('quotes', 'Product Name'),
  'adjustment-name'=>Yii::t('quotes', 'Adjustment Name'),
  'price'=>Yii::t('quotes', 'Price'),
  'quantity'=>Yii::t('quotes', 'Quantity'),
  'adjustment'=>Yii::t('quotes', 'Adjustment'),
  'description'=>Yii::t('quotes', 'Comments')
);
$passVariablesToClientScript .= "x2.quotes.titleTranslations = ".CJSON::encode ($titleTranslations).";";

/*
Send information about existing products. This information is used by the client to construct the
product selection drop-down menu.
*/
if (!$readOnly && isset ($products)) {
  $passVariablesToClientScript .= "x2.quotes.productNames = [];" ;
  $passVariablesToClientScript .= "x2.quotes.productPrices = {};" ;
  foreach ($products as $prod) {
    $passVariablesToClientScript .= "x2.quotes.productNames.push ('" . addslashes ($prod->name) . "');\n";
    $passVariablesToClientScript .= "x2.quotes.productPrices['" . addslashes ($prod->name) . "'] = '".
                                    $prod->price . "';\n";
  }
  $passVariablesToClientScript .= "
    x2.quotes.arrowBothImageSource = '" . 
      Yii::app()->request->baseUrl.'/themes/x2engine/css/gridview/arrow_both.png' . "';
    x2.quotes.arrowDownImageSource = '" . 
      Yii::app()->request->baseUrl.'/themes/x2engine/css/gridview/arrow_down.png' . "';
    x2.quotes.deleteImageSource = '" . 
      Yii::app()->request->baseUrl.'/themes/x2engine/css/gridview/delete.png' . "';
  ";

}

Yii::app()->clientScript->registerCssFile($this->module->assetsUrl . '/css/lineItemsMain.css');
if (!$readOnly) {
  Yii::app()->clientScript->registerCssFile($this->module->assetsUrl . '/css/lineItemsWrite.css');
} else {
  Yii::app()->clientScript->registerCssFile($this->module->assetsUrl . '/css/lineItemsRead.css');
}

$debug = 0;
if (YII_DEBUG && $debug) {
  Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/qunit/qunit-1.11.0.js',
    CClientScript::POS_HEAD);
  Yii::app()->clientScript->registerCssFile(Yii::app()->request->baseUrl.'/js/qunit/qunit-1.11.0.css',
    CClientScript::POS_HEAD);
  Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl . '/js/quotesUnitTests.js',
    CClientScript::POS_HEAD);
}

echo "<script type=\"text/javascript\">\n$passVariablesToClientScript\n</script>";

?>

<?php 
if (YII_DEBUG && $debug) {
  echo "<div id='qunit'></div>"; 
  echo "<div id='qunit-fixture'></div>"; 
}
?>

<?php
  // For the create and update page, create a drop down menu for previous product
  // selection
  if (!$readOnly && isset ($products)) {
    echo "<ul id='product-menu'>";
    foreach ($products as $prod) {
      echo "<li><a href='#'>" . $prod->name . "</a></li>";
    }
    echo "</ul>";
  }
?>


<table class='quote-table'>
  <thead>
    <tr>
      <th class='first-cell'></th>
      <th class="lineitem-name"><?php echo Yii::t('products', 'Line Item'); ?></th>
      <th class="lineitem-price"><?php echo Yii::t('products', 'Unit Price'); ?></th>
      <th class="lineitem-quantity"><?php echo Yii::t('products', 'Quantity'); ?></th>
      <th class="lineitem-adjustments"><?php echo Yii::t('products', 'Adjustments'); ?></th>
      <th class="lineitem-description"><?php echo Yii::t('products', 'Comments'); ?></th>
      <th class="lineitem-total"><?php echo Yii::t('products', 'Price'); ?></th>
    </tr>
  </thead>
  <tbody id='line-items' 
   <?php if (!$readOnly) echo 'class="sortable"' ?>>
   <!-- line items will be placed here by addLineItem() in javascript -->
  </tbody>
  <tr id='subtotal-row'>
    <td class='first-cell'> </td>
    <td colspan='4'> </td>
    <td class="text-field"><span style="font-weight:bold"> Subtotal: </span></td>
    <td class="subtotal-container input-cell">
      <input type="text" readonly='readonly' onfocus='this.blur();' 
       style="font-weight:bold" id="subtotal" name="Quote[subtotal]">
      </input>
    </td>
  </tr>
  <tbody id='adjustments' 
   <?php if (!$readOnly) echo 'class="sortable"' ?>>
   <!-- adjustments will be placed here by addAdjustment() in javascript -->
  </tbody>
  <tr>
    <td class='first-cell'> </td>
    <td colspan="4"></td>
    <td class='text-field'><span style="font-weight:bold"> Total: </span></td> 
    <td class="total-container input-cell">
      <input type="text" readonly='readonly' onfocus='this.blur();' style="font-weight:bold" id="total"  name="Quote[total]">
      </input>
    </td>
  </tr>
</table>
<?php if(!$readOnly): ?>
<button type='button' class='x2-button add-line-item-button'>+&nbsp;<?php echo Yii::t('workflow', 'Add Line Item'); ?></button>
<button type='button' class='x2-button add-adjustment-button'>+&nbsp;<?php echo Yii::t('workflow', 'Add Adjustment'); ?></button>
<?php endif; ?>





<script>

  // translate ISO 4217 currency into i18n
  var currencyTable = {
    'USD': 'en-US',
    'EUR': 'hsb-DE',
    'GBP': 'en-GB',
    'CAD': 'en-CA',
    'JPY': 'ja-JP',
    'CNY': 'zh-CN',
    'CHF': 'de-CH',
    'INR': 'hi-IN',
    'BRL': 'pt-BR'
  };

  var debug = 0;
  
  function consoleLog(obj) {
    if (console != undefined) {
      if(console.log != undefined && debug) {
        console.log(obj);
      }
    }
  }

  /*
  Parameters:
    total - a Number
    subtotal - a Number which is used to calculate totalPercent adjustments
    rowElement - a row from the adjustments table in the DOM
  Returns:
    a Number containing the adjusted total
  */
  function applyAdjustmentToTotal (total, subtotal, rowElement) {
    var adjustmentType = 
      $(rowElement).find (".adjustment-type").val ();
    var adjustment = getAdjustment (rowElement, adjustmentType)

    if (adjustmentType === 'totalLinear') {
      total += adjustment;
    } else if (adjustmentType === 'totalPercent') {
      total += subtotal * (adjustment / 100.0);
    } 
    return total;
  }

  function removePercentSign (adjustment) {
    return adjustment.replace (/&#37;/, '');
  }


  /*
  extract the price from the row element and convert the currency to a Number
  */
  function getPrice (rowElement) {
    var price = $(rowElement).find (".price").toNumber(
      {region: currencyTable[x2.quotes.currency]});
    if (price.val () === '') price.val (0); // convert invalid input to 0
    price = parseFloat (price.val ());
    $(rowElement).find (".price").formatCurrency(
      {region: currencyTable[x2.quotes.currency]});
    return price;
  }

  /*
  extract the adjustment from the row element and either convert the currency to
  a Number or remove the percent sign
  */
  function getAdjustment (rowElement, adjustmentType) {
    var adjustment;
    if (adjustmentType === 'percent' || adjustmentType === 'totalPercent') {
      adjustment = parseFloat (removePercentSign (
        $(rowElement).find (".adjustment").val ()), 10);
    } else { // adjustmentType === 'linear' || adjustmentType === 'totalLinear'
      adjustment = $(rowElement).find (".adjustment").toNumber(
        {region: currencyTable[x2.quotes.currency]});
      if (adjustment.val () === '') adjustment.val (0); // convert invalid input to 0
      adjustment = parseFloat (adjustment.val ());
      $(rowElement).find (".adjustment").formatCurrency(
        {region: currencyTable[x2.quotes.currency]});
    }
    return adjustment;
  }

  /*
  Calculate the line total using the line item's quantity, unit price, and 
  adjustments
  Parameter:
  rowElement - the row containing the line item input fields
  Returns:
    a Number containing the line total
  */
  function calculateLineTotal (rowElement) {
    var price = getPrice (rowElement);
    var quantity = 
      parseInt ($(rowElement).find (".quantity").val (), 10);
    var adjustmentType = 
      $(rowElement).find (".adjustment-type").val ();
    var adjustment = getAdjustment (rowElement, adjustmentType);
    var name = 
      $(rowElement).find ("[name*='name']").val ();

    var lineTotal = price * quantity;

    if (adjustmentType === 'linear') {
      lineTotal += adjustment;
    } else if (adjustmentType === 'percent') {
      lineTotal += lineTotal * (adjustment / 100.0);
    } 

    return lineTotal;
  }

  /*
  Returns:
    an array containing each of the line item totals
  */
  function calculateLineTotals () {
    var lineTotals = [];
    $('.line-item').each (function (index, element) {
      lineTotals.push (calculateLineTotal (element));
    });
    return lineTotals;
  }

  /*
  For each line item row in the lineItems tbody element, fills in an 
  entry for the line total.
  Parameter:
    lineTotals - an array of Numbers, one for each line item in the line item 
      table
  */
  function setLineTotals (lineTotals) {
    $('.line-item').each (function (index, element) {
      total = lineTotals.shift (); 
      $(element).find ('.line-item-total').val (total).formatCurrency (
        {'region': currencyTable[x2.quotes.currency]});
    });
  }

  /*
  Helper function to convert the value of an input field containing a currency to 
  a float, save the float, and convert the value of the input field back to a currency.
  The converted currency is returned.
  */
  function extractCurrency (element) {
    $(element).toNumber(
      {'region': currencyTable[x2.quotes.currency]});
    var currency = parseFloat ($(element).val ());
    $(element).formatCurrency(
      {region: currencyTable[x2.quotes.currency]});
    return currency;
  }
  
  /*
  extract the line item total from the row element and convert the currency to a 
  Number
  */
  function getLineItemTotal (rowElement) {
    return extractCurrency ($(rowElement).find ('.line-item-total'));
  }

  /*
  Calculates the subtotal by summing the line item totals from the DOM
  Precondition: setLineTotals () has been called 
  Returns:
    subtotal - a Number containing the calculated subtotal
  */
  function calculateSubtotal () {
    var subtotal = 0;
    $('.line-item').each (function (index, element) {
      subtotal += getLineItemTotal (element);
    });
    return subtotal;
  };


  /*
  Displays the subtotal in the line items table in the DOM
  */
  function setSubtotal (subtotal) {
    $('#subtotal').val(subtotal.toString ());
    $('#subtotal').formatCurrency(
      {region: currencyTable[x2.quotes.currency]});
  }

  /*
  Displays the total in the line items table in the DOM
  */
  function setTotal (total) {
    $('#total').val(total.toString ());
    $('#total').formatCurrency(
      {region: currencyTable[x2.quotes.currency]});
  }

  /*
  Calculates the total by applying each of the adjustments from the DOM to the
  subtotal
  Parameter:
    subtotal - a Number 
  Returns:
    a Number containing the adjusted subtotal
  */
  function calculateTotal (subtotal) {
    var total = subtotal;
    $('.adjustment').each (function (index, element) {
      total = applyAdjustmentToTotal (total, subtotal, element);
    });
    return total;
  }

  /*
  Insert a new line item row into the quotes line item table
  */
  function addLineItem (fillLineItem, values /* set if fillLineItem is true */) {
    if (!fillLineItem) {
        values = { // default values, 
            "product-name": ['' /* default input value */, false /* validation error */],
            "price": ['0', false],
            "quantity": ['1', false],
            "adjustment": ['0', false],
            "description": ['', false],
            "adjustment-type": ['linear', false],
        }
    }

    var lineItemRow = $("<tr>", {'class': 'line-item'});

    $firstCell = lineItemRow.append ($("<td>", {'class': 'first-cell'}));
    if (!x2.quotes.readOnly) {
      $firstCell.find ('td').append (
        $("<img>", {src: x2.quotes.deleteImageSource, 'class': 'item-delete-button'}),
        $("<img>", {src: x2.quotes.arrowBothImageSource, 'class': 'handle arrow-both-handle'})
      );
    }
    $inputCell = lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field product-name',
        maxlength: '100',
        value: values['product-name'][0],
        name: 'lineitem[' + ++x2.quotes.lineCounter + '][name]' })
    ));
    if (!x2.quotes.readOnly) {
      $inputCell.find ('input').after (
        $("<button>", {'class': 'product-select-button', 'type': 'button'}).append (
          $("<img>", { src: x2.quotes.arrowDownImageSource })));
    }
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field price',
        value: values['price'][0],
        name: 'lineitem[' + x2.quotes.lineCounter + '][price]' }))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field quantity',
        value: values['quantity'][0],
        name: 'lineitem[' + x2.quotes.lineCounter + '][quantity]' }))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field adjustment',
        value: values['adjustment'][0],
        name: 'lineitem[' + x2.quotes.lineCounter + '][adjustment]' }))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field description',
        maxlength: '140',
        'value': values['description'][0],
        name: 'lineitem[' + x2.quotes.lineCounter + '][description]' }))
    );
    lineItemRow.append ($("<td>", {
      'class': 'input-cell line-item-field'}).append (
        $("<input>", {
          type: 'text',
          'class': 'line-item-total',
          readonly: 'readonly',
          onfocus: 'this.blur();',
          name: 'lineitem[' + x2.quotes.lineCounter + '][total]' }),
        $("<input>", {
          type: 'hidden', 
          'class': 'adjustment-type',
          value: values['adjustment-type'][0],
          name: 'lineitem[' + x2.quotes.lineCounter + '][adjustmentType]' }),
        $("<input>", {
          type: 'hidden', 
          'class': 'line-number',
          name: 'lineitem[' + x2.quotes.lineCounter + '][lineNumber]' }))
    );

    if (fillLineItem) { // add error class if server side validation failed
      for (var inputType in values) {
        if (values[inputType][1] === true) {
          $(lineItemRow).find ("." + inputType).addClass ('error');
        }
      }
    }
    if (x2.quotes.readOnly) { // make uneditable
      $(lineItemRow).find ("input").attr ({
        "readonly": "readonly",
        "onfocus": "this.blur();"
      });
    } else { // add translated title attributes
      for (var inputType in x2.quotes.titleTranslations) {
        var $inputField = $(lineItemRow).find ("." + inputType);
        if ($inputField.length === 1) {
          $inputField.attr ("title", x2.quotes.titleTranslations[inputType]);
        }
      }
    }

    $('#line-items').append (lineItemRow);

    if (!x2.quotes.readOnly) { // set up product select menu behavior
      var productNameInput = $(lineItemRow).find ('input.product-name');
      $(productNameInput).autocomplete ({
          source: x2.quotes.productNames,
          select: selectProductFromAutocomplete,
          open: function () {
              if ($('#product-menu').is (":visible")) {
                  $('#product-menu').hide (); 
              }
          }
      });
      formatAutocompleteWidget (productNameInput);
      $('tbody.sortable').sortable ('refresh');
    }
    if (!fillLineItem) { // format default input field values 
      lineItemRow.find ('.adjustment').formatCurrency (
        {region: currencyTable[x2.quotes.currency]});
      lineItemRow.find ('.price').formatCurrency (
        {'region': currencyTable[x2.quotes.currency]});
      lineItemRow.find ('.line-item-total').val (0).formatCurrency (
        {'region': currencyTable[x2.quotes.currency]});
      if ($('.quote-table').find ('tr.line-item').length === 1 &&
          $('.quote-table').find ('tr.adjustment').length > 0) {
        $('#subtotal-row').show ();
      }
    }

    resetLineNums ();
  }

  /*
  Insert a new adjustment row into the quotes line item table
  */
  function addAdjustment (fillAdjustment, values /* set if fillAdjustment is true */) {
    if (!fillAdjustment) {
        values = { // default values
            "adjustment-name": ['' /* default input value */, false /* validation error */],
            "adjustment": ['0', false],
            "description": ['', false],
            "adjustment-type": ['totalLinear', false],
        }
    }

    var lineItemRow = $("<tr>", {'class': 'adjustment'});

    $firstCell = lineItemRow.append ($("<td>", {'class': 'first-cell'}));
    if (!x2.quotes.readOnly) {
      $firstCell.find ('td').append (
        $("<img>", {src: x2.quotes.deleteImageSource, 'class': 'item-delete-button'}),
        $("<img>", {src: x2.quotes.arrowBothImageSource, 'class': 'handle arrow-both-handle'})
      );
    }
    lineItemRow.append ($("<td>"));
    lineItemRow.append ($("<td>"));
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field adjustment-name',
        maxlength: '100',
        value: values['adjustment-name'][0],
        name: 'lineitem[' + ++x2.quotes.lineCounter + '][name]' }))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field adjustment',
        value: values['adjustment'][0],
        name: 'lineitem[' + x2.quotes.lineCounter + '][adjustment]' }))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field description',
        maxlength: '140',
        value: values['description'][0],
        name: 'lineitem[' + x2.quotes.lineCounter + '][description]' }))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      //$("<span></span>", {'class': 'line-item-total'}),
      $("<input>", {
        type: 'hidden', 
        'class': 'adjustment-type',
        value: values['adjustment-type'][0],
        name: 'lineitem[' + x2.quotes.lineCounter + '][adjustmentType]' }),
      $("<input>", {
        type: 'hidden', 
        'class': 'line-number',
        name: 'lineitem[' + x2.quotes.lineCounter + '][lineNumber]' }))
    );

    if (fillAdjustment) { // add error class if server side validation failed
      for (var inputType in values) {
        if (values[inputType][1] === true) {
          $(lineItemRow).find ("." + inputType).addClass ('error');
        }
      }
    }
    if (x2.quotes.readOnly) { // make uneditable
      $(lineItemRow).find ("input").attr ({
        "readonly": "readonly",
        "onfocus": "this.blur();"
      });
    } else { // add translated title attributes
      for (var inputType in x2.quotes.titleTranslations) {
        var $inputField = $(lineItemRow).find ("." + inputType);
        if ($inputField.length === 1) {
          $inputField.attr ("title", x2.quotes.titleTranslations[inputType]);
        }
      }

    }

    $('#adjustments').append (lineItemRow);

    if (!x2.quotes.readOnly) {  
      $('tbody.sortable').sortable ('refresh');
    }
    if (!fillAdjustment) { // format default input field values
      lineItemRow.find ('.adjustment').formatCurrency (
      {region: currencyTable[x2.quotes.currency]});
      if ($('.quote-table').find ('tr.adjustment').length === 1) {
        $('#subtotal-row').show ();
      }
    }

    resetLineNums ();
  }

  function selectProductFromAutocomplete (event, ui) {
    event.preventDefault ();
    var lineItemName = ui.item.label;
    $(event.target).val (lineItemName);
    var lineItemPrice = $(event.target).attr ('name').replace (/name/, 'price');
    $('[name="' + lineItemPrice + '"]').val (x2.quotes.productPrices[lineItemName]).
      formatCurrency ({region: currencyTable[x2.quotes.currency]});
    validateName (event.target);
    updateTotals ();
  }

  function selectProductFromDropDown (event, ui) {
    event.preventDefault ();
    var lineItemName = ui.item.text ();
    $(x2.quotes.clickedLineItem).val (lineItemName);
    var lineItemPrice = $(x2.quotes.clickedLineItem).attr ('name').replace (/name/, 'price');
    $('[name="' + lineItemPrice + '"]').val (x2.quotes.productPrices[lineItemName]).
      formatCurrency ({region: currencyTable[x2.quotes.currency]});
    validateName (x2.quotes.clickedLineItem);
    updateTotals ();
  }

  function formatAutocompleteWidget (element) {
      var widget = $(element).autocomplete ("widget");
      $(widget).css ({
          "font-size": "10px",
          "max-height": "16em",
          "overflow-y": "scroll"
      });
      $(window).resize (function () {
        $(widget).hide ();
      });
  }

  /*
  Sets up a combo box that allows ad-hoc line item names and selection from a
  list of existing products
  */
  function setupProductSelectMenu () {
    if (x2.quotes.productNames) {
      $('input.product-name').autocomplete ({
        source: x2.quotes.productNames,
        select: selectProductFromAutocomplete,
        open: function () {
            if ($('#product-menu').is (":visible")) {
                $('#product-menu').hide (); 
            }
        }
      });
      $('input.product-name').each (function () {
        formatAutocompleteWidget ($(this));
      });
    }

    $('#line-items').on (
      'click', '.product-select-button', function (event) {

      x2.quotes.clickedLineItem = $(this).prev ().prev ();
      $('#product-menu').show ().position ({
        my: "left top",
        at: "left bottom",
        of: $(this).prev ().prev ()
      });
      $(document).one ('click', function () {
        $('#product-menu').hide ();
      });
      return false;
    });

    $('#product-menu').hide ().menu ({select: selectProductFromDropDown});
  }

  /*
  Recalculate line item total, the subtotal, and the overall total
  */
  function updateTotals () {
    lineTotals = calculateLineTotals ();
    setLineTotals (lineTotals);
    var subtotal = calculateSubtotal ();
    if ($('.quote-table').find ('tr.adjustment').length !== 0 &&
        $('.quote-table').find ('tr.line-item').length !== 0) {
      setSubtotal (subtotal);
      var total = calculateTotal (subtotal);
      setTotal (total);
    } else {
      setTotal (subtotal);
    }

    if ($('.quote-table').children ().find ('.error').length !== 0) {
      var calculationErrors = 0
      $('.quote-table').children ().find ('.error').each (function (index, element) {
        if (!($(element).hasClass ('product-name') || $(element).hasClass ('adjustment-name'))) {
          $(element).parents ('.line-item').children ().find ('.line-item-total').val ("");
          calculationErrors++;
        }
      }); 
      if (calculationErrors > 0) 
        $('#total, #subtotal').val ("");
    }
  }

  /*
  Determine and set the type of adjustment (totalLinear, totalPercent, linear, 
  percent) and validate the input.
  */
  function checkAdjustment (element) {
    var elemVal = $(element).val ();
    var typeElementName = 
      $(element).attr ('name').replace (/adjustment/, 'adjustmentType');

    if (elemVal.match (/%/)) {  

      if ($('[name="' + typeElementName + '"]').val ().match (/total/)) {
        $('[name="' + typeElementName + '"]').val ('totalPercent');
      } else {
        $('[name="' + typeElementName + '"]').val ('percent');
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

      if ($('[name="' + typeElementName + '"]').val ().match (/total/)) {
        $('[name="' + typeElementName + '"]').val ('totalLinear');
      } else {
        $('[name="' + typeElementName + '"]').val ('linear');
      }

      return true;
    }

  }

  function validateQuantity (element) {
    if ($(element).val ().match (/^[0-9]+$/)) {
      $(element).removeClass ('error');
      return true;
    } else {
      $(element).addClass ('error');
      return false;
    }
  }

  function validateName (element) {
    if ($(element).val () === "") {
      return false;
    } else {
      $(element).removeClass ('error');
      return true;
    }
  }

  /*
  Helper function for validateAllInputs. 
  Parameter:
    element - a input field in the quotes table
  Returns:
    An error message corresponding to the type of the input field of the given element.
  */
  function getErrorMessage (element) {
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
      var exampleCurrency = $(temporaryElem).
        formatCurrency ({region: currencyTable[x2.quotes.currency]}).val ();
      errorMessage = "Adjustment must be a currency amount or a percentage (e.g. \"" +
        exampleCurrency + "\" or \"-50%\").";
    }
    return errorMessage;
  }


  /*
  Helper for update button click event
  */
  function validateAllInputs () {
    $('.quote-table').find ('input').each (function (index, element) {
      if ($(element).val () === "" &&
          $(element).hasClass ('line-item-field') &&
          !$(element).hasClass ('description')) {
        $(element).addClass ("error");
      }
    });
    if ($('.quotes-error-summary').length !== 0) {
        $('.quotes-error-summary').remove ();
    }
    if ($('.quote-table').find ('input').hasClass ("error")) {
      $('div#quotes-errors').after ($("<div>", {class: "quotes-error-summary"}).append (
        $("<p> Please fix the following input errors: </p>"),
        $("<ul>")
      ));
      var usedErrorMessages = [];
      $('.quote-table').find ('.error').each (function (index, element) {
        var errorMessage = getErrorMessage (element);
        if ($.inArray (errorMessage, usedErrorMessages) === -1) {
            usedErrorMessages.push (errorMessage);
            $('.quotes-error-summary').find ('ul').append ($("<li> " + errorMessage + " </li>"));
        }
      });
      return false;
    } else {
      return true;
    }

  }

  function setupValidationEvents () {
    $('#line-items, #adjustments').on ('change', '.line-item-field.adjustment', 
      function (event) {
        checkAdjustment (event.target); 
        updateTotals ();
    });
    $('#line-items').on ('change', '.line-item-field.quantity', function (event) {
      validateQuantity (event.target);
      updateTotals ();
    });
    $('#line-items').on ('change', '.line-item-field.price', function (event) {
        updateTotals ();
    });
    $('#line-items, #adjustments').on (
      'blur', '.line-item-field.product-name, .line-item-field.adjustment-name', function (event) {
      validateName (event.target);
    });
    if (x2.quotes.view === "default") {
      consoleLog ('setup');
      $('#quote-save-button').on ('click', function (event) {
        if (validateAllInputs ()) {
          return true;
        } else {
          return false;
        }
      });
    }
  }

  function deleteAdjustment (element) {
    $(element).parents ('.adjustment').remove ();
    if ($('.quote-table').find ('tr.adjustment').length === 0) 
      $('#subtotal-row').hide ();
    updateTotals ();
    resetLineNums ();
  }

  function deleteLineItem (element) {
    $(element).parents ('.line-item').remove ();
    if ($('.quote-table').find ('tr.line-item').length === 0) 
      $('#subtotal-row').hide ();
    updateTotals ();
    resetLineNums ();
  }

  function resetLineNums () {
    var lineNum = 1;
    $('tr.line-item').each (function (index, element) {
      $(element).find ('.line-number').val (lineNum++);
    });
    $('tr.adjustment').each (function (index, element) {
      $(element).find ('.line-number').val (lineNum++);
    });
  }

  /*
  Used to prevent row width from collapsing when being sorted.
  */
  function preserveRowWidth (event, sortedElement) {
    sortedElement.children ().each (function (index, element) {
      $(element).width ($(element).width ());
    });
    return sortedElement;
  }

  /*
  Populate quotes table with existing line items and adjustments
  */
  function populateQuotesTable () {
    for (var i in x2.quotes.productLines) {
      addLineItem (true, x2.quotes.productLines[i]);
    }
    for (var i in x2.quotes.adjustmentLines) {
      addAdjustment (true, x2.quotes.adjustmentLines[i]);
    }
  }

  function setupEditingBehavior () {
    $(window).resize (function () {
      $('#product-menu').hide ();
    });

    setupProductSelectMenu ();
    
    $('tbody.sortable').sortable ({
      handle: ".handle", 
      start: function (event, ui) {
        $('#product-menu').hide ();
        if ($(ui.item).hasClass ("line-item")) {
          var widget = $(ui.item).find ("input.product-name").autocomplete ("widget");
          if (widget)
              $(widget).hide ();
        }
      },
      stop: resetLineNums,
      helper: preserveRowWidth
    });
    //$('tbody.sortable').disableSelection ();

    $('.add-adjustment-button').click (function () {addAdjustment (false);});
    $('.add-line-item-button').click (function (){addLineItem (false);});

    $('#adjustments').on ('click', '.item-delete-button', function (event) {
      deleteAdjustment (event.target);
    });
    $('#line-items').on ('click', '.item-delete-button', function (event) {
      deleteLineItem (event.target);
    });

    setupValidationEvents ();

    // add a line item if this is the create view
    if (x2.quotes.productLines.length === 0 && 
        x2.quotes.adjustmentLines.length === 0) { 
      addLineItem ();
    } else { 
      populateQuotesTable ();
    }

  }


  $(function () {
    if (x2.quotes.readOnly) {
      populateQuotesTable ();
    } else {
      setupEditingBehavior ();
    }

    if ($('.quote-table').find ('tr.adjustment').length === 0 || 
        $('.quote-table').find ('tr.line-item').length === 0) {
      $('#subtotal-row').hide ();
    }

    updateTotals ();

  });

</script>


