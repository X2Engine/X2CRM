<?php

Yii::import('application.models.*');
Yii::import('application.modules.quotes.models.*');

/**
 * Test of Quote methods
 *
 * @package application.tests.unit.modules.quotes.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class QuoteTest extends X2DbTestCase {

	public static function referenceFixtures() {
		return array ();
	}

	public $fixtures = array(
		'lineItems' => 'QuoteProduct',
		'quotes' => 'Quote'
	);

    public function testSetLineItems() {

            $errMsg = 'Did not throw exception when attempting to set w/invalid value for lineItems property.';
            $quote = $this->quotes('lineItems');
            ob_start();
            try {
                    $quote->lineItems = array(1);
                    $this->assertTrue(false, $errMsg);
            } catch (Exception $e) {
                    $this->assertTrue(true);
            }
            try {
                    $altQuote = new Quote();
                    $quote->lineItems = array($altQuote);
                    $this->assertTrue(false, $errMsg);
            } catch (Exception $e) {
                    $this->assertTrue(true);
            }
            ob_end_clean();
            // These exceptions should not result in the array being changed
            $this->assertEquals(9, count($quote->lineItems), 'Exceptions thrown, but lineItems changed!');
            $lineItems = array(
                    'preexist_keep_1' => array(
                            'id' => 1,
                            'lineNumber' => 1,
                            'adjustmentType' => 'linear',
                            'name' => 'test item #1 new name',
                            'currency' => 'USD',
                            'price' => 1,
                            'quantity' => 1
                    ),
                    'preexist_keep_2' => array(
                            'id' => 2,
                            'lineNumber' => 2,
                            'adjustmentType' => 'linear',
                            'name' => 'test item #2 new name',
                            'currency' => 'USD',
                            'price' => 2,
                            'quantity' => 2
                    ),
//			'preexist_delete_1' => array(
//				'id' => 3,
//				'quoteId' => 1,
//				'lineNumber' => 3,
//				'adjustmentType' => 'linear',
//				'name' => 'test item #3',
//				'currency' => 'USD',
//				'price' => 3,
//				'quantity' => 3,
//				'linePrice' => 9,
//				'lineTotal' => 14,
//			),
//			'preexist_delete_2' => array(
//				'id' => 4,
//				'quoteId' => 1,
//				'lineNumber' => 4,
//				'adjustmentType' => 'linear',
//				'name' => 'test item #4',
//				'currency' => 'USD',
//				'price' => 4,
//				'quantity' => 4,
//				'linePrice' => 16,
//				'lineTotal' => 20,
//			),
                    'preexist_keep_3' => array(
                            'id' => 5,
                            'lineNumber' => 5,
                            'adjustmentType' => 'percent',
                            'name' => 'test item #5 new name',
                            'currency' => 'USD',
                            'price' => 5,
                            'quantity' => 5,
                    ),
                    'preexist_keep_4' => array(
                            'id' => 6,
                            'lineNumber' => 6,
                            'adjustmentType' => 'linear',
                            'name' => 'test item #6 new name',
                            'currency' => 'USD',
                            'price' => 6,
                            'quantity' => 6,
                    ),
                    'preexist_keep_5' => array(
                            'id' => 7,
                            'lineNumber' => 7,
                            'adjustmentType' => 'totalPercent',
                            'name' => 'test item #7 new name',
                            'currency' => 'USD',
                            'price' => 7,
                            'quantity' => 7,
                    ),
//			'preexist_delete_3' => array(
//				'id' => 8,
//				'lineNumber' => 8,
//				'adjustmentType' => 'totalLinear',
//				'name' => 'test item #8',
//				'currency' => 'USD',
//				'price' => 8,
//				'quantity' => 8,
//				'linePrice' => 64,
//			),
                    'preexist_keep_6' => array(
                            'id' => 9,
                            'lineNumber' => 9,
                            'adjustmentType' => 'totalPercent',
                            'name' => 'test item # new name',
                            'currency' => 'USD',
                            'price' => 9,
                            'quantity' => 9,
                    ),
                    'new_1' => array(
                            'lineNumber' => 3,
                            'adjustmentType' => 'linear',
                            'name' => 'new test item #1 new name',
                            'currency' => 'USD',
                            'price' => 9,
                            'quantity' => 9,
                    ),
                    'new_2' => array(
                            'lineNumber' => 4,
                            'adjustmentType' => 'linear',
                            'name' => 'new test item #2 new name',
                            'currency' => 'USD',
                            'price' => 9,
                            'quantity' => 9,
                    ),
                    'new_3' => array(
                            'lineNumber' => 8,
                            'adjustmentType' => 'totalPercent',
                            'name' => 'new test item #3 new name',
                            'currency' => 'USD',
                            'price' => 9,
                            'quantity' => 9,
                    ),
            );
            $lineItemsNoAlias = array_values($lineItems);
//		var_dump(array_map(function($li){return $li->attributes;},$quote->lineItems));
            $quote->setLineItems($lineItemsNoAlias,true);
//		var_dump(array_map(function($li){return $li->attributes;},$quote->lineItems));
            $this->assertFalse($quote->hasLineItemErrors, "Quote has line item errors: " . CJSON::encode($quote->lineItemErrors));
//		echo "\nNew line item id:lineNumber:\n";
//		foreach ($quote->lineItems as $item)
//			echo "{$item->id}:{$item->lineNumber} ";

            $newLineItemSet = $quote->lineItems;
            $itemsByLine = array();
            $dbItemsByLine = array();

            // Check that the array in the object was set properly:
            foreach ($newLineItemSet as $item) {
                    $itemsByLine[$item->lineNumber] = $item;
            }
            foreach ($lineItems as $alias => $item) {
                    if (!array_key_exists($item['lineNumber'], $itemsByLine))
                            $this->assertTrue(false, "Item at line {$item['lineNumber']} missing from lineItems array for quote {$quote->id}.");
                    foreach ($item as $attr => $value) {
                            if ($attr != 'id')
                                    $this->assertEquals($value, $itemsByLine[$item['lineNumber']]->$attr, "Attribute value assertion failed on $alias ($attr), line {$item['lineNumber']}.");
                    }
                    $this->assertEquals($quote->id,$itemsByLine[$item['lineNumber']]->quoteId,"Failed asserting that quoteId was set properly.");
            }

            // Prepare reference arrays for database comparisons
            $newLineItemSet = QuoteProduct::model()->findAllByAttributes(array('quoteId' => $quote->id));
            $itemsByLine = array();
            $dbItemsByLine = array();
            foreach ($lineItems as $item)
                    $itemsByLine[$item['lineNumber']] = $item;
            foreach ($newLineItemSet as $item) {
                    $this->assertNotEmpty($item->lineNumber, "Item ID #{$item->id} was inserted with null line number.");
                    $dbItemsByLine[$item->lineNumber] = $item;
            }
            $deletedIds = array();
            $newLines = array();
            foreach (range(1, 3) as $pos) {
                    $deletedIds[] = (int) $this->lineItems["preexist_delete_$pos"]['id'];
                    $line = (int) $lineItems["new_$pos"]['lineNumber'];
                    $newLines[(int) $line] = "new_$pos";
                    $updateLines[(int) $line] = "preexist_keep_$pos";
            }
            foreach (range(4, 6) as $pos)
                    $updateLines[(int) $line] = "preexist_keep_$pos";

            // Check that the deleted items were deleted:
            $deleted = QuoteProduct::model()->findAllByPk($deletedIds);
            $this->assertEmpty($deleted, "Failed asserting that line items were deleted properly.");

            // Check that new items were inserted:
            $new = QuoteProduct::model()->findAllBySql("SELECT * FROM x2_quotes_products WHERE quoteId={$quote->id} AND lineNumber IN (".implode(',',array_keys($newLines)).')');
            $this->assertNotEmpty($new, "Failed asserting that any new line items were inserted.");
            $this->assertEquals(3, count($new), "Failed asserting that all line items were inserted.");
            foreach ($new as $item) {
                    $alias = $newLines[$item->lineNumber];
                    foreach ($lineItems[$alias] as $attr => $value)
                            $this->assertEquals($value, $item->$attr, "New line item at key $alias was not inserted with proper values.");
            }

            // Check that existing items were updated:
            foreach ($lineItems as $alias => $item) {
                    if (!array_key_exists($item['lineNumber'], $dbItemsByLine))
                            $this->assertTrue(false, "Item at line {$item['lineNumber']} not found in the database for quote {$quote->id}." . (array_key_exists('id', $item) ? "ID: {$item['id']}" : ''));
                    foreach ($item as $attr => $value) {
                            $this->assertEquals($value, $dbItemsByLine[$item['lineNumber']]->$attr, "Attribute value assertion failed on $alias ($attr).");
                    }
            }
    }

	public function testLineItemsBugMay2013() {
		$post = array(
			'Quote' =>
			array(
				'name' => 'Campbell\'s Cloud & Training',
				'status' => 'Won',
				'locked' => '0',
				'expirationDate' => 'Tomorrow',
				'associatedContacts_id' => '1007',
				'associatedContacts' => 'Theodore Black',
				'accountName_id' => '30',
				'accountName' => 'Evergreen Capital Partners',
				'probability' => '82',
				'assignedTo' => 'apelletier',
				'description' => '',
				'subtotal' => '',
				'total' => '$55.00',
				'template' => '',
			),
			'lineitem' =>
			array(
				2 =>
				array(
					'name' => 'Data Migration (per hour)',
					'price' => '$55.00',
					'quantity' => '1',
					'adjustment' => '$0.00',
					'description' => '',
					'total' => '$55.00',
					'adjustmentType' => 'linear',
					'lineNumber' => '1',
				),
			),
			'yt0' => 'Update',
		);
		$quote = $this->quotes('bugMay2013');
		$this->assertEquals(2,count($quote->lineItems));
		$quote->lineItems = $post['lineitem'];
		$this->assertEquals(1,count($quote->lineItems));
	}

	/**
	 * This test's purpose is solely to ensure that no PHP errors occur.
	 *
	 * Testing actual output of the {@link Quote::productTable()} function
	 * should be done by sight; it's all in how the markup is put together and
	 * how it looks when it's done.
	 */
	public function testProductTable() {
		$quote = $this->quotes('lineItems');
		$quote->productTable();
	}

}

?>
