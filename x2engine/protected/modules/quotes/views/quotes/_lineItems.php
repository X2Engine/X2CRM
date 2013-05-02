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
    $currency = $model->currency;
    $currency = Yii::app()->params['currency'];
  }
}

$orders = $model->lineItems;   // Sorting by line items is done in the magic getter/setter, so no need to sort.
if (!empty($orders)) {  
  $lineCounter = 0; // used to differentiate line items and adjustments in the DOM
  // passed to the client so that line item rows can have a uid 
  $ordersNum = count ($orders);

} else {
  $ordersNum = 0;
}

// set variables for the client JavaScript
$passVariablesToClientScript = "
  x2.quotes = {};
  x2.quotes.currency = '".$currency."';
  x2.quotes.readOnly = '".$readOnly."';
  x2.quotes.adjustmentsNum = ". count ($model->adjustmentLines) .";
  x2.quotes.lineCounter = ". $ordersNum .";
";

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


//Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl . '/js/testjs.js'); 
//Yii::app()->clientScript->registerScript(
//  'passVariablesToClient',$passVariablesToClientScript,CClientScript::POS_HEAD);
echo "<script type=\"text/javascript\">\n$passVariablesToClientScript\n</script>";

?>

<?php
  // For the create an update page, create a drop down menu for previous product
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
   <?php 
    // display each line item, place undisplayed database attributes in hidden 
    // fields, leave a table cell for the line total
    foreach ($model->productLines as $item): ?>
    <tr class='line-item'>
      <td class='first-cell'> 
        <?php 
          if (!$readOnly) {
            insertRemoveImage ();
            insertArrowBothImage ();
          }
        ?>
      </td>
      <td class='input-cell'>
        <input type="text" <?php if ($readOnly) echo "readonly='readonly' onfocus='this.blur();'" ?>
         value=<?php echo "'" . $item->renderAttribute('name') . "'" ?>
         class='line-item-field product-name'
         name=<?php echo "'lineitem[" . ++$lineCounter . "][name]'" ?>/>
        <?php 
          if (!$readOnly) {
            echo "<button type='button' class='product-select-button'>";
            echo "<img src='" . 
              Yii::app()->request->baseUrl.'/themes/x2engine/css/gridview/arrow_down.png' . 
              "' alt='[" . Yii::t('quotes', 'Select a Product') . "]'/>";
            echo "</button>";
          }
        ?>
      </td>
      <td class='input-cell'>
        <input type="text" <?php if ($readOnly) echo "readonly='readonly' onfocus='this.blur();'" ?>
         value=<?php echo "'" . $item->formatAttribute('price') . "'" ?> 
         class='line-item-field price' name=<?php echo "'lineitem[" . $lineCounter . "][price]'" ?>/>
      </td>
      <td class='input-cell'>
        <input type="text" <?php if ($readOnly) echo "readonly='readonly' onfocus='this.blur();'" ?>
         value=<?php echo "'" . $item->formatAttribute('quantity') . "'" ?> 
         class='line-item-field quantity'
         name=<?php echo "'lineitem[" . $lineCounter . "][quantity]'" ?>/>
      </td>
      <td class='input-cell'> 
        <input type="text" <?php if ($readOnly) echo "readonly='readonly' onfocus='this.blur();'" ?>
         value=<?php echo "'" . $item->formatAttribute('adjustment') . "'"?> 
         class='line-item-field adjustment' 
         name=<?php echo "'lineitem[" . $lineCounter . "][adjustment]'" ?>/>
      </td>
      <td class='input-cell'>
        <input type="text" <?php if ($readOnly) echo "readonly='readonly' onfocus='this.blur();'" ?>
         value=<?php echo "'" . $item->renderAttribute('description') . "'" ?> 
         class='line-item-field' name=<?php echo "'lineitem[" . $lineCounter . "][description]'" ?>/>
      </td>
      <td class='line-item-field text-field'>
        <input type="text" readonly='readonly' onfocus='this.blur();' class='line-item-total'
         name=<?php echo "'lineitem[" . $lineCounter . "][total]'" ?>/></input>
        <input type="hidden" value=<?php echo "'" . $item->adjustmentType . "'" ?>
         class='line-item-field adjustment-type'
         name=<?php echo "'lineitem[" . $lineCounter . "][adjustmentType]'" ?>/>
        <input type="hidden" value=<?php echo "'" . $item->lineNumber . "'" ?>
         class='line-item-field line-number'
         name=<?php echo "'lineitem[" . $lineCounter . "][lineNumber]'" ?>/>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
  <tr id='subtotal-row'>
    <td class='first-cell'> </td>
    <td colspan='4'> </td>
    <td class="text-field"><span style="font-weight:bold"> Subtotal: </span></td>
    <td class="text-field subtotal-container">
      <input type="text" readonly='readonly' onfocus='this.blur();' 
       style="font-weight:bold" id="subtotal" name="Quote[subtotal]">
      </input>
    </td>
  </tr>
  <tbody id='adjustments' 
   <?php if (!$readOnly) echo 'class="sortable"' ?>>
   <?php 
    // display each adjustment, place undisplayed database attributes in hidden 
    // fields
    foreach ($model->adjustmentLines as $adj): ?>
    <tr class='adjustment'>
      <td class='first-cell'> 
        <?php 
          if (!$readOnly) {
            insertRemoveImage();
            insertArrowBothImage ();
          }
        ?>
      </td>
      <td> </td>
      <td> </td>
      <td class='input-cell'> 
        <input type="text" <?php if ($readOnly) echo "readonly='readonly' onfocus='this.blur();'" ?>
         value=<?php echo "'" . $adj->renderAttribute('name') . "'" ?> class='line-item-field'
         name=<?php echo "'lineitem[" . ++$lineCounter . "][name]'" ?>/>
      </td>
      <td class='input-cell'> 
        <input type="text" <?php if ($readOnly) echo "readonly='readonly' onfocus='this.blur();'" ?>
         value=<?php echo "'" . $adj->formatAttribute('adjustment') . "'" ?> 
         class='line-item-field adjustment'
         name=<?php echo "'lineitem[" . $lineCounter . "][adjustment]'" ?>/>
      </td>
      <td class='input-cell'>
        <input type="text" <?php if ($readOnly) echo "readonly='readonly' onfocus='this.blur();'" ?>
         value=<?php echo "'" . $adj->renderAttribute('description') . "'" ?> 
         class='line-item-field'
         name=<?php echo "'lineitem[" . $lineCounter . "][description]'" ?>/>
      </td>
      <td class='text-field'> 
        <input type="hidden" value=<?php echo "'" . $adj->adjustmentType . "'" ?> 
         class='line-item-field adjustment-type'
         name=<?php echo "'lineitem[" . $lineCounter . "][adjustmentType]'" ?>/>
        <input type="hidden" value=<?php echo "'" . $adj->lineNumber . "'" ?> 
         class='line-item-field line-number'
         name=<?php echo "'lineitem[" . $lineCounter . "][lineNumber]'" ?>/>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
  <tr>
    <td class='first-cell'> </td>
    <td colspan="4"></td>
    <td class='text-field'><span style="font-weight:bold"> Total: </span></td> 
    <td class="text-field total-container">
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

  var debug = 1;
  
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
    //console.log ('getPrice: price = ' + price.val ());
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
      adjustment = parseInt (removePercentSign (
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
    if(isNaN(quantity))
	  quantity = 0;
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
       $(element).find ('.line-item-total').val (lineTotals.shift ()).formatCurrency (
        {'region': currencyTable[x2.quotes.currency]});
    });
  }
  
  /*
  extract the line item total from the row element and convert the currency to a 
  Number
  */
  function getLineItemTotal (rowElement) {
    var price = $(rowElement).find ('.line-item-total').toNumber(
      {'region': currencyTable[x2.quotes.currency]});
    price = parseFloat (price.val());
    $(rowElement).find ('.line-item-total').formatCurrency(
      {region: currencyTable[x2.quotes.currency]});
    return price;
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
  function addLineItem (event) {

    var lineItemRow = $("<tr>", {'class': 'line-item'});

    lineItemRow.append ($("<td>", {'class': 'first-cell'}).append (
      $("<img>", {src: x2.quotes.deleteImageSource, 'class': 'item-delete-button'}),
      $("<img>", {src: x2.quotes.arrowBothImageSource, 'class': 'handle arrow-both-handle'}))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field product-name',
        name: 'lineitem[' + ++x2.quotes.lineCounter + '][name]' }),
      $("<button>", {'class': 'product-select-button', 'type': 'button'}).append (
        $("<img>", { src: x2.quotes.arrowDownImageSource })))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field price',
        value: '0',
        name: 'lineitem[' + x2.quotes.lineCounter + '][price]' }))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field quantity',
        value: '1',
        name: 'lineitem[' + x2.quotes.lineCounter + '][quantity]' }))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field adjustment',
        value: '0',
        name: 'lineitem[' + x2.quotes.lineCounter + '][adjustment]' }))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field description',
        name: 'lineitem[' + x2.quotes.lineCounter + '][description]' }))
    );
    lineItemRow.append ($("<td>", {'class': 'line-item-field'}).append (
      $("<input>", {
        type: 'text',
        readonly: 'readonly',
        onfocus: 'this.blur ();',
        'class': 'line-item-total',
        name: 'lineitem[' + x2.quotes.lineCounter + '][total]' }),
      $("<input>", {
        type: 'hidden', 
        'class': 'line-item-field adjustment-type',
        name: 'lineitem[' + x2.quotes.lineCounter + '][adjustmentType]' }),
      $("<input>", {
        type: 'hidden', 
        'class': 'line-item-field line-number',
        name: 'lineitem[' + x2.quotes.lineCounter + '][lineNumber]' }))
    );

    $('#line-items').append (lineItemRow);

    lineItemRow.find ('.adjustment').formatCurrency (
      {region: currencyTable[x2.quotes.currency]});
    lineItemRow.find ('.price').formatCurrency (
      {'region': currencyTable[x2.quotes.currency]});
    lineItemRow.find ('.line-item-total').val (0).formatCurrency (
        {'region': currencyTable[x2.quotes.currency]});

    $('input.product-name').autocomplete ({source: x2.quotes.productNames});
    $('tbody.sortable').sortable ('refresh');
    resetLineNums ();

  }

  /*
  Insert a new adjustment row into the quotes line item table
  */
  function addAdjustment (event) {
    var lineItemRow = $("<tr>", {'class': 'adjustment'});

    lineItemRow.append ($("<td>", {'class': 'first-cell'}).append (
      $("<img>", {src: x2.quotes.deleteImageSource, 'class': 'item-delete-button'}),
      $("<img>", {src: x2.quotes.arrowBothImageSource, 'class': 'handle arrow-both-handle'}))
    );
    lineItemRow.append ($("<td>"));
    lineItemRow.append ($("<td>"));
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field product-name',
        name: 'lineitem[' + ++x2.quotes.lineCounter + '][name]' }))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field adjustment',
        value: '0',
        name: 'lineitem[' + x2.quotes.lineCounter + '][adjustment]' }))
    );
    lineItemRow.append ($("<td>", {'class': 'input-cell'}).append (
      $("<input>", {
        type: 'text', 
        'class': 'line-item-field description',
        name: 'lineitem[' + x2.quotes.lineCounter + '][description]' }))
    );
    lineItemRow.append ($("<td>", {'class': 'line-item-field'}).append (
      //$("<span></span>", {'class': 'line-item-total'}),
      $("<input>", {
        type: 'hidden', 
        'class': 'line-item-field adjustment-type',
        value: 'totalLinear',
        name: 'lineitem[' + x2.quotes.lineCounter + '][adjustmentType]' }),
      $("<input>", {
        type: 'hidden', 
        'class': 'line-item-field line-number',
        name: 'lineitem[' + x2.quotes.lineCounter + '][lineNumber]' }))
    );

    $('#adjustments').append (lineItemRow);

    lineItemRow.find ('.adjustment').formatCurrency (
      {region: currencyTable[x2.quotes.currency]});

    $('tbody.sortable').sortable ('refresh');

    if (++x2.quotes.adjustmentsNum == 1) {
      updateTotals ();
    }
    resetLineNums ();
    if (x2.quotes.adjustmentsNum == 1)
      $('#product-menu').css ('width', $('input.product-name').css ('width'));

  }

  /*
  Sets up a combo box that allows ad-hoc line item names and selection from a
  list of existing products
  */
  function setupProductSelectMenu () {
      if (x2.quotes.productNames) {
        $('input.product-name').autocomplete ({source: x2.quotes.productNames});
      }

      $('#product-menu').css ('width', $('input.product-name').css ('width'));

      $('#line-items').on (
        'click', '.product-select-button', function (event) {

        $('#product-menu').css ('width', $('input.product-name').css ('width'));

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

      $('#product-menu').hide ().menu ({select: function (event, ui) {
        event.preventDefault ();
        var lineItemName = ui.item.text ();
        $(x2.quotes.clickedLineItem).val (lineItemName);
        var lineItemPrice = $(x2.quotes.clickedLineItem).attr ('name').replace (/name/, 'price');
        $('[name="' + lineItemPrice + '"]').val (x2.quotes.productPrices[lineItemName]).
          formatCurrency ({region: currencyTable[x2.quotes.currency]});
        updateTotals ();
      }});
  }



  /*
  Recalculate line item total, the subtotal, and the overall total
  */
  function updateTotals () {
    lineTotals = calculateLineTotals ();
    setLineTotals (lineTotals);
    var subtotal = calculateSubtotal ();
    if (x2.quotes.adjustmentsNum > 0) {
      $('#subtotal-row').show ();
      setSubtotal (subtotal);
      var total = calculateTotal (subtotal);
      setTotal (total);
    } else {
      $('#subtotal-row').hide ();
      setTotal (subtotal);
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
    if ($(element).val ().match (/^[0-9]*$/)) {
      $(element).removeClass ('error');
      return true;
    } else {
      $(element).addClass ('error');
      return false;
    }
  }

  function setupValidationEvents () {
      $('#line-items, #adjustments').on ('change', '.line-item-field.adjustment', 
        function (event) {

        if (checkAdjustment (event.target)) {
          updateTotals ();
        }
      });
      $('#line-items').on ('change', '.line-item-field.quantity', function (event) {
        if (validateQuantity (event.target)) {
          updateTotals ();
        }
      });
      $('#line-items').on ('change', '.line-item-field.price', function (event) {
          updateTotals ();
      });
  }

  function deleteAdjustment (element) {
    $(element).parents ('.adjustment').remove ();
    x2.quotes.adjustmentsNum--;
    updateTotals ();
    resetLineNums ();
    if (x2.quotes.adjustmentsNum == 0)
      $('#product-menu').css ('width', $('input.product-name').css ('width'));
  }

  function deleteLineItem (element) {
    $(element).parents ('.line-item').remove ();
    updateTotals ();
    resetLineNums ();
  }

  function resetLineNums () {
    var lineNum = 1;
    $('tr.line-item').each (function (index, element) {
      //console.log ('loop 1 ' + lineNum);
      $(element).find ('.line-number').val (lineNum++);
    });
    $('tr.adjustment').each (function (index, element) {
      //console.log ('loop 2 ' + lineNum);
      $(element).find ('.line-number').val (lineNum++);
    });
  }

  function preserveRowWidth (event, sortedElement) {
    sortedElement.children ().each (function (index, element) {
      $(element).width ($(element).width ());
    });
    return sortedElement;
  }

  function setupEditingBehavior () {
      $(window).resize (function () {
        $('#product-menu').hide ();
      });

      $('tbody.sortable').sortable ({
        handle: ".handle", 
        start: function () {
          $('#product-menu').hide ();
        },
        stop: resetLineNums,
        helper: preserveRowWidth
      });
      //$('tbody.sortable').disableSelection ();

      $('.add-adjustment-button').click (addAdjustment);
      $('.add-line-item-button').click (addLineItem);

      $('#adjustments').on ('click', '.item-delete-button', function (event) {
        deleteAdjustment (event.target);
      });
      $('#line-items').on ('click', '.item-delete-button', function (event) {
        deleteLineItem (event.target);
      });

      // add a line item if this is the create view
      if (x2.quotes.lineCounter === 0) 
        addLineItem ();

      setupProductSelectMenu ();
      setupValidationEvents ();
  }


  $(function () {
    if (x2.quotes.readOnly) {

    } else {
      setupEditingBehavior ();
    }

    updateTotals ();

  });

</script>


