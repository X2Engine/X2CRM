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




/*
view data:
    object $model the Quote model
    array $products (if $readOnly is false) active products create in the products module. This is 
        used to populate the drop down menu for new line items.
    bool $readOnly indicates whether or not the line item and
        adjustment fields are editable.
    string $namespacePrefix used to prefix unique identifiers (html element ids, javascript object 
        names).
    object $module (optional) the module whose assets url should be used to retrieve resources
*/

$module = isset ($module) ? $module : $this->module;
$mini = isset ($mini) ? $mini : false;

$currency = Yii::app()->params->currency;
if (isset ($model)) {
    if (!empty ($model->currency)) {
        $currency = $model->currency;
    }
}


/*
Send a dictionary containing translations for the types of each input field.
Used for html title attributes.
*/
$titleTranslations = array( // keys correspond to CSS classes of each input field
    'product-name'=>Yii::t('quotes', '{product} Name',array(
        '{product}'=>Modules::displayName(false, "Products")
    )),
    'adjustment-name'=>Yii::t('quotes', 'Adjustment Name'),
    'price'=>Yii::t('quotes', 'Price'),
    'quantity'=>Yii::t('quotes', 'Quantity'),
    'adjustment'=>Yii::t('quotes', 'Adjustment'),
    'description'=>Yii::t('quotes', 'Comments')
);

if (!$readOnly) {
    Yii::app()->clientScript->registerScriptFile(
        Yii::app()->getBaseUrl ().'/js/ComboBox.js', CClientScript::POS_HEAD);  
    Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/comboBox.css'); 
    Yii::app()->clientScript->registerCssFiles('lineItemsCss',
        array (
            $module->assetsUrl . '/css/lineItemsMain.css',
            $module->assetsUrl . '/css/lineItemsWrite.css',
        ), false);
} else {
    Yii::app()->clientScript->registerCssFiles('lineItemsCss',
        array (
            $module->assetsUrl . '/css/lineItemsMain.css',
            $module->assetsUrl . '/css/lineItemsRead.css',
        ), false);
}

if ($mini) {
    Yii::app()->clientScript->registerCssFile(
        $module->assetsUrl.'/css/lineItemsMini.css');

}

Yii::app()->clientScript->registerScriptFile (  
    $module->assetsUrl.'/js/LineItems.js', CClientScript::POS_HEAD);

$lineItemsVarInsertionScript = '';

/*
Send information about existing products. This information is used by the client to construct the
product selection drop-down menu.
*/
if (!$readOnly) {
    foreach ($products as $prod) {
        $lineItemsVarInsertionScript .= "productNames.push (" . CJSON::encode($prod->name) . ");\n";
        $lineItemsVarInsertionScript .= "productPrices[" . CJSON::encode($prod->name) . "] = '".
            $prod->price . "';\n";
        $lineItemsVarInsertionScript .= "productDescriptions[" . CJSON::encode($prod->name) . "] = ".
            CJSON::encode($prod->description).";\n";
    }
}

/*
Send an array containing product line information. This array is used by the client to build
the rows of the line items table.
*/
foreach ($model->productLines as $item) {
    $lineItemsVarInsertionScript .= "productLines.push (".
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
foreach ($model->adjustmentLines as $item) {
    $lineItemsVarInsertionScript .= "adjustmentLines.push (".
        CJSON::encode (array ( // keys correspond to CSS classes of each input field
        'adjustment-name'=>array ($item->formatAttribute('name'),$item->hasErrors('name')),
        'adjustment'=>array ($item->formatAttribute('adjustment'),$item->hasErrors('adjustment')),
        'description'=>array ($item->formatAttribute('description'),$item->hasErrors('description')),
        'adjustment-type'=>array ($item->formatAttribute('adjustmentType'),false))).
    ");";
}

?>
<script>

(function () {

var productNames = [];
var productLines = [];
var adjustmentLines = [];
var productPrices = {};
var productDescriptions = {};

<?php echo $lineItemsVarInsertionScript; ?>

x2.<?php echo $namespacePrefix; ?>lineItems = new x2.LineItems ({
    currency: '<?php echo $currency; ?>',
    readOnly: <?php echo $readOnly ? 'true' : 'false'; ?>,
    deleteImageSource: '<?php echo 
        Yii::app()->request->baseUrl.'/themes/x2engine/css/gridview/delete.png'; ?>',
    arrowBothImageSource: '<?php echo 
        Yii::app()->request->baseUrl.'/themes/x2engine/css/gridview/arrow_both.png'; ?>',
    arrowDownImageSource: '<?php echo 
        Yii::app()->request->baseUrl.'/themes/x2engine/css/gridview/arrow_down.png'; ?>',
    titleTranslations: <?php echo CJSON::encode ($titleTranslations); ?>,
    productNames: productNames,
    productPrices: productPrices,
    productDescriptions: productDescriptions,
    view: 'default',
    productLines: productLines,
    adjustmentLines: adjustmentLines,
    namespacePrefix: '<?php echo $namespacePrefix; ?>',
    getItemsUrl: '<?php echo Yii::app()->createUrl ('/products/products/getItems2'); ?>',
    modelName: '<?php echo isset ($modelName) ? $modelName : ''; ?>'
});

}) ();

</script>

<?php
//if (YII_DEBUG && YII_UNIT_TESTING) {
//    Yii::app()->clientScript->registerScriptFile($module->assetsUrl . '/js/quotesUnitTests.js',
//        CClientScript::POS_END);
//}
?>

<div id="<?php echo $namespacePrefix ?>-line-items-table" class='line-items-table<?php echo $mini ? ' line-items-mini' : ''; echo $readOnly ? ' line-items-read' : ' line-items-write'; ?>'>

<?php
//if (YII_DEBUG && YII_UNIT_TESTING) {
//    echo "<div id='qunit-fixture'></div>";
//}
?>

<?php
    // For the create and update page, create a drop down menu for previous product
    // selection
    if (!$readOnly && isset ($products)) {
        echo "<ul class='product-menu'>";
        foreach ($products as $prod) {
            echo "<li><a href='#'>" . CHtml::encode($prod->name) . "</a></li>";
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
    <tbody class='line-items<?php if (!$readOnly) echo ' sortable' ?>'>
     <!-- line items will be placed here by addLineItem() in javascript -->
    </tbody>
    <tr class='subtotal-row'>
        <td class='first-cell'> </td>
        <td colspan='<?php echo $mini ? 2 : 4; ?>'> </td>
        <td class="text-field"><span style="font-weight:bold">  <?php echo Yii::t('quotes','Subtotal:');?> </span></td>
        <td class="subtotal-container input-cell">
            <input type="text" readonly='readonly' onfocus='this.blur();'
             style="font-weight:bold" id="<?php echo $namespacePrefix ?>-subtotal"  
             class='subtotal' name="Quote[subtotal]">
            </input>
        </td>
    </tr>
    <tbody class='adjustments<?php if (!$readOnly) echo ' sortable' ?>'>
     <!-- adjustments will be placed here by addAdjustment() in javascript -->
    </tbody>
    <tbody id='quote-total-section'>
    <tr>
        <td class='first-cell'> </td>
        <td colspan='<?php echo $mini ? 2 : 4; ?>'> </td>
        <td class='text-field'><span style="font-weight:bold"> <?php echo Yii::t('quotes','Total:');?> </span></td>
        <td class="total-container input-cell">
            <input type="text" readonly='readonly' onfocus='this.blur();' style="font-weight:bold" 
             id="<?php echo $namespacePrefix; ?>-total" class='total' name="Quote[total]">
            </input>
        </td>
    </tr>
    </tbody>
</table>
<?php if(!$readOnly): ?>
<button type='button' class='x2-button add-line-item-button'>+&nbsp;<?php echo Yii::t('quotes', 'Add Line Item'); ?></button>
<button type='button' class='x2-button add-adjustment-button'>+&nbsp;<?php echo Yii::t('quotes', 'Add Adjustment'); ?></button>
<?php endif; ?>


</div>

